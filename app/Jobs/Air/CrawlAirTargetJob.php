<?php

namespace App\Jobs\Air;

use App\Models\WeatherAir;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Goutte\Client;
use Illuminate\Bus\Batchable;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class CrawlAirTargetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    // cấu hình retry của queue (khác retry HTTP bên trong)
    public $tries = 2;
    public $backoff = 5;

    public function __construct(
        protected string $url,
        protected int $provinceId,
        protected ?int $districtId,
        protected ?int $wardId,
        protected string $timeIso // truyền time ở dispatcher để dedup
    ) {}

    public function handle(): void
    {
        $time = Carbon::parse($this->timeIso);

        $maxRetries = 5;
        $retry      = 0;

        while ($retry < $maxRetries) {
            $proxy = function_exists('getRandomProxy') ? getRandomProxy() : null;

            try {
                $httpClient = HttpClient::create([
                    'proxy'        => $proxy,
                    'verify_peer'  => false,
                    'verify_host'  => false,
                    'timeout'      => 25,
                    'headers'      => [
                        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122 Safari/537.36',
                        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'vi,en-US;q=0.9,en;q=0.8',
                        'Referer'         => 'https://thoitiet.vn/',
                    ],
                ]);

                $client  = new Client($httpClient);
                $crawler = $client->request('GET', $this->url);

                // helpers
                $getText = function (Crawler $node, string $selector, ?string $default = null) {
                    try {
                        if ($node->filter($selector)->count() > 0) {
                            return trim($node->filter($selector)->text());
                        }
                    } catch (\Throwable $e) {}
                    return $default;
                };
                $toFloat = function (?string $val): ?float {
                    if ($val === null) return null;
                    $num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    return is_numeric($num) ? (float)$num : null;
                };
                $normLabel = function (string $label): string {
                    $label = strip_tags($label);
                    $label = preg_replace('/\s+/', '', strtoupper($label)); // CO,NH3,NO,NO2,O3,PM10,PM25,SO2
                    return $label ?? '';
                };

                // category
                $categoryFull = $getText($crawler, '.air-title', null);
                $category     = $categoryFull ? trim(preg_replace('/^.*?:\s*/u', '', $categoryFull)) : null;

                $aqi = null; // chưa thấy số AQI trong HTML mẫu

                $fields = [
                    'CO'   => 'co',
                    'NH3'  => 'nh3',
                    'NO'   => 'no',
                    'NO2'  => 'no2',
                    'O3'   => 'o3',
                    'PM10' => 'pm10',
                    'PM25' => 'pm2_5',
                    'SO2'  => 'so2',
                ];

                $data = [
                    'province_id' => $this->provinceId,
                    'district_id' => $this->districtId,
                    'ward_id'     => $this->wardId,
                    'time'        => $time,
                    'aqi'         => $aqi,
                    'category'    => $category,
                ];

                if ($crawler->filter('.air-components .flex-1')->count() > 0) {
                    $crawler->filter('.air-components .flex-1')->each(function (Crawler $node) use (&$data, $fields, $normLabel, $toFloat) {
                        $labelRaw = $node->filter('span')->first()->html('');
                        $label    = $normLabel($labelRaw);
                        $valueTxt = trim($node->filter('.text-white')->text(''));
                        $value    = $toFloat($valueTxt);
                        if (isset($fields[$label])) {
                            $data[$fields[$label]] = $value;
                        }
                    });
                }

                WeatherAir::updateOrCreate(
                    [
                        'province_id' => $this->provinceId,
                        'district_id' => $this->districtId,
                        'ward_id'     => $this->wardId,
                        'time'        => $time,
                    ],
                    $data
                );

                Log::info('Air OK', [
                    'url'         => $this->url,
                    'province_id' => $this->provinceId,
                    'district_id' => $this->districtId,
                    'ward_id'     => $this->wardId,
                    'proxy'       => $proxy,
                ]);

                return;
            } catch (\Throwable $e) {
                $retry++;

                // mark dead proxy & rotate
                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];
                elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) $ip = $m2[1];

                if ($ip !== 'unknown') {
                    Cache::put("proxy_dead_$ip", true, 120);
                    if (function_exists('rotateProxyIpByIp')) {
                        try { rotateProxyIpByIp($ip); }
                        catch (\Throwable $eRotate) {
                            Log::warning('rotateProxyIpByIp failed', ['ip' => $ip, 'message' => $eRotate->getMessage()]);
                        }
                    }
                }

                if ($retry >= $maxRetries) {
                    Log::error('Air FAILED', [
                        'url'         => $this->url,
                        'province_id' => $this->provinceId,
                        'district_id' => $this->districtId,
                        'ward_id'     => $this->wardId,
                        'proxy'       => $proxy,
                        'retry'       => $retry,
                        'message'     => $e->getMessage(),
                    ]);
                    return;
                }

                Log::warning('Air RETRY', [
                    'url'         => $this->url,
                    'province_id' => $this->provinceId,
                    'district_id' => $this->districtId,
                    'ward_id'     => $this->wardId,
                    'proxy'       => $proxy,
                    'retry'       => $retry,
                    'error'       => $e->getMessage(),
                ]);

                sleep(1);
            }
        }
    }
}
