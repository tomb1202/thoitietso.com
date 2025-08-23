<?php

namespace App\Jobs;

use App\Models\Province;
use App\Models\WeatherAir;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class CrawlAirJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 2;
    public $backoff = 5;

    public function __construct(
        protected int $provinceId,
        protected ?int $districtId = null,
        protected ?int $wardId = null
    ) {}

    public function handle(): void
    {
        $province = Province::with(['districts.wards'])->find($this->provinceId);
        if (!$province) {
            Log::warning('Skip air dispatch: province missing', ['province_id' => $this->provinceId]);
            return;
        }

        // Determine URL theo cấp
        $url = null;
        if ($this->wardId) {
            $url = $province->districts
                ->flatMap->wards
                ->firstWhere('id', $this->wardId)?->url;
        } elseif ($this->districtId) {
            $url = $province->districts
                ->firstWhere('id', $this->districtId)?->url;
        } else {
            $url = $province->url;
        }

        if (!$url) {
            Log::warning('Skip air dispatch: missing URL for location', [
                'province_id' => $this->provinceId,
                'district_id' => $this->districtId,
                'ward_id'     => $this->wardId,
            ]);
            return;
        }

        $time = Carbon::now()->startOfHour();

        $maxRetries = 2;
        $retry = 0;

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
                $crawler = $client->request('GET', $url);

                // helpers
                $getText = fn(Crawler $node, string $selector, ?string $default = null)
                    => $node->filter($selector)->count() ? trim($node->filter($selector)->text()) : $default;

                $toFloat = fn(?string $val)
                    => is_numeric($num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) ? (float)$num : null;

                $normLabel = fn(string $label)
                    => preg_replace('/\s+/', '', strtoupper(strip_tags($label)));

                $categoryFull = $getText($crawler, '.air-title', null);
                $level     = $categoryFull ? trim(preg_replace('/^.*?:\s*/u', '', $categoryFull)) : null;

                $aqi = null;

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
                    'level'       => $level,
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

                return;
            } catch (\Throwable $e) {
                $retry++;

                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];
                elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) $ip = $m2[1];

                if ($ip !== 'unknown') {
                    Cache::put("proxy_dead_$ip", true, 120);
                    if (function_exists('rotateProxyIpByIp')) {
                        try {
                            rotateProxyIpByIp($ip);
                        } catch (\Throwable $eRotate) {
                            Log::warning('rotateProxyIpByIp failed', ['ip' => $ip, 'message' => $eRotate->getMessage()]);
                        }
                    }
                }

                if ($retry >= $maxRetries) {
                    Log::error('Air FAILED', [
                        'url'         => $url,
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
                    'url'         => $url,
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
