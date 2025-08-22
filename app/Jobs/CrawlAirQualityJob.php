<?php

namespace App\Jobs;

use App\Models\Province;
use App\Models\WeatherAir;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class CrawlAirQualityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $provinceId) {}

    public function handle(): void
    {
        $province = Province::with(['districts.wards'])->find($this->provinceId);
        if (!$province || !$province->url) {
            Log::warning('Skip air: province missing or no url', ['province_id' => $this->provinceId]);
            return;
        }

        // dùng time theo giờ để de-duplicate records
        $time = Carbon::now()->startOfHour();

        // Crawl tỉnh
        $this->crawlOneTarget($province->url, $province->id, null, null, $time);

        // Crawl quận/huyện
        foreach ($province->districts as $d) {
            if (!$d->url) continue;
            $this->crawlOneTarget($d->url, $province->id, $d->id, null, $time);

            // Crawl phường/xã
            foreach ($d->wards as $w) {
                if (!$w->url) continue;
                $this->crawlOneTarget($w->url, $province->id, $d->id, $w->id, $time);
            }
        }
    }

    protected function crawlOneTarget(string $url, int $provinceId, ?int $districtId, ?int $wardId, Carbon $time): void
    {
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
                $crawler = $client->request('GET', $url);

                // ===== Helpers =====
                $getText = function (Crawler $node, string $selector, ?string $default = null) {
                    try {
                        if ($node->filter($selector)->count() > 0) {
                            return trim($node->filter($selector)->text());
                        }
                    } catch (\Throwable $e) {
                    }
                    return $default;
                };
                $toFloat = function (?string $val): ?float {
                    if ($val === null) return null;
                    $num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    return is_numeric($num) ? (float)$num : null;
                };
                $normLabel = function (string $label): string {
                    $label = strip_tags($label);
                    $label = preg_replace('/\s+/', '', strtoupper($label));
                    return $label ?? '';
                };

                // ===== Category (ví dụ: "Chất lượng không khí: Tốt") =====
                $categoryFull = $getText($crawler, '.air-title', null);
                $category     = $categoryFull ? trim(preg_replace('/^.*?:\s*/u', '', $categoryFull)) : null;

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
                    'province_id' => $provinceId,
                    'district_id' => $districtId,
                    'ward_id'     => $wardId,
                    'time'        => $time,
                    'aqi'         => $aqi,
                    'category'    => $category,
                ];

                // Mỗi component là 1 khối .air-components .flex-1
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

                // ===== Upsert =====
                WeatherAir::updateOrCreate(
                    [
                        'province_id' => $provinceId,
                        'district_id' => $districtId,
                        'ward_id'     => $wardId,
                        'time'        => $time,
                    ],
                    $data
                );

                Log::info('Fetched air quality successfully', [
                    'url'         => $url,
                    'province_id' => $provinceId,
                    'district_id' => $districtId,
                    'ward_id'     => $wardId,
                    'proxy'       => $proxy,
                ]);

                return; // done 1 target
            } catch (\Throwable $e) {
                $retry++;

                // mark dead proxy & rotate
                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) {
                    $ip = $m[1];
                } elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) {
                    $ip = $m2[1];
                }
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
                    Log::error('Fetch air quality failed after retries', [
                        'url'         => $url,
                        'province_id' => $provinceId,
                        'district_id' => $districtId,
                        'ward_id'     => $wardId,
                        'proxy'       => $proxy,
                        'retry'       => $retry,
                        'message'     => $e->getMessage(),
                    ]);
                    return;
                }

                Log::warning('Retry air quality with new proxy', [
                    'url'         => $url,
                    'province_id' => $provinceId,
                    'district_id' => $districtId,
                    'ward_id'     => $wardId,
                    'proxy'       => $proxy,
                    'retry'       => $retry,
                    'error'       => $e->getMessage(),
                ]);
                sleep(1);
            }
        }
    }
}
