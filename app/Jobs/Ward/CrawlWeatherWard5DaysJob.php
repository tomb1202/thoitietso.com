<?php

namespace App\Jobs\Ward;

use App\Models\Ward;
use App\Models\WeatherWard;
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

class CrawlWeatherWard5DaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $wardId;

    public function __construct($ward)
    {
        $this->wardId = $ward instanceof Ward ? $ward->id : (int)$ward;
    }

    public function handle(): void
    {
        $ward = Ward::with('district')->find($this->wardId);

        if (!$ward || !$ward->url) {
            Log::warning('Ward not found or missing URL for 5-day crawl', ['ward_id' => $this->wardId]);
            return;
        }

        $url        = rtrim($ward->url, '/') . '/5-ngay-toi';
        $today      = Carbon::today();
        $minDayDate = $today->copy()->addDays(2);

        $maxRetries = 5;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
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

                // Helpers
                $getText = fn($node, string $selector, ?string $default = null)
                    => $node->filter($selector)->count() > 0 ? trim($node->filter($selector)->text()) : $default;

                $getAttr = fn($node, string $selector, string $attr, ?string $default = null)
                    => $node->filter($selector)->count() > 0 ? $node->filter($selector)->attr($attr) ?? $default : $default;

                $toFloat = fn(?string $val): ?float
                    => is_numeric($num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) ? (float)$num : null;

                $crawler->filter('#accordionExample details.weather-day')->each(function ($dayNode) use (
                    $ward, $minDayDate, $getText, $getAttr, $toFloat
                ) {
                    $dayLabel = $getText($dayNode, 'summary .summary-day span', '');
                    $dayDate  = null;

                    if ($dayLabel === '' || mb_stripos($dayLabel, 'Hôm nay') !== false) {
                        $dayDate = Carbon::today();
                    } elseif (preg_match('/(\d{1,2})\/(\d{1,2})/', $dayLabel, $m)) {
                        $dayDate = Carbon::createFromFormat('d/m', sprintf('%02d/%02d', $m[1], $m[2]))->year(now()->year);
                    }

                    if (!$dayDate instanceof Carbon) {
                        $dataTarget = $getAttr($dayNode, 'summary.weather-summary', 'data-target', '');
                        if (preg_match('/(\d{1,2})-(\d{1,2})/', $dataTarget, $m)) {
                            $dayDate = Carbon::createFromFormat('d-m', sprintf('%02d-%02d', $m[1], $m[2]))->year(now()->year);
                        }
                    }

                    if (!$dayDate instanceof Carbon || $dayDate->lt($minDayDate)) return;

                    $sunrise = $sunset = null;
                    $dayNode->filter('.weather-content .weather-content-item-sun .weather-sun span')->each(function ($span, $i) use (&$sunrise, &$sunset) {
                        $txt = trim($span->text());
                        if (preg_match('/(\d{1,2}:\d{2})/', $txt, $m)) {
                            if ($i === 0) $sunrise = $m[1];
                            if ($i === 1) $sunset  = $m[1];
                        }
                    });

                    $dayNode->filter('.weather-content .weather-summary')->each(function ($hNode) use (
                        $ward, $dayDate, $sunrise, $sunset, $getText, $getAttr, $toFloat
                    ) {
                        $timeStr = trim($getText($hNode, '.summary-day', ''));
                        if (!preg_match('/^\d{1,2}:\d{2}$/', $timeStr)) return;

                        $forecastTime = $dayDate->copy()->setTimeFromTimeString($timeStr);

                        $tempMinTxt  = $getText($hNode, '.summary-temperature-min');
                        $tempMaxTxt  = $getText($hNode, '.summary-temperature-max-value');
                        $humidityTxt = $getText($hNode, '.summary-humidity span:last-child');
                        $description = $getText($hNode, '.summary-description-detail');
                        $icon        = $getAttr($hNode, '.summary-description img', 'src');
                        $windTxt     = $getText($hNode, '.summary-speed span:last-child');

                        $tempMin  = $toFloat(str_replace('°C', '', $tempMinTxt));
                        $tempMax  = $toFloat(str_replace('°C', '', $tempMaxTxt));
                        $humidity = $toFloat(str_replace('%',  '', $humidityTxt));
                        $windSpeed = $toFloat(str_ireplace(['km/giờ', 'km/h'], '', $windTxt));

                        WeatherWard::updateOrCreate(
                            [
                                'ward_id'       => $ward->id,
                                'forecast_time' => $forecastTime,
                            ],
                            [
                                'district_id'   => $ward->district_id,
                                'temp_min'      => $tempMin,
                                'temp_max'      => $tempMax,
                                'humidity'      => $humidity,
                                'uv_index'      => null,
                                'visibility_km' => null,
                                'description'   => $description,
                                'icon'          => $icon,
                                'wind_speed'    => $windSpeed,
                                'sunrise'       => $sunrise,
                                'sunset'        => $sunset,
                                'run_at'        => now(),
                                'target_time'   => $forecastTime,
                                'source'        => 'thoitiet.vn',
                            ]
                        );
                    });
                });

                Log::info('Crawled 5-day forecast for ward', [
                    'ward_id' => $ward->id,
                    'ward'    => $ward->name,
                    'url'     => $url,
                    'proxy'   => $proxy,
                ]);

                return;
            } catch (\Throwable $e) {
                $retryCount++;

                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];
                elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) $ip = $m2[1];

                if ($ip !== 'unknown') {
                    Cache::put("proxy_dead_$ip", true, 120);
                    if (function_exists('rotateProxyIpByIp')) {
                        try { rotateProxyIpByIp($ip); } catch (\Throwable $eRotate) {
                            Log::warning('rotateProxyIpByIp failed', ['ip' => $ip, 'message' => $eRotate->getMessage()]);
                        }
                    }
                }

                if ($retryCount >= $maxRetries) {
                    Log::error('Failed to crawl ward forecast after max retries', [
                        'ward_id' => $ward->id,
                        'ward'    => $ward->name,
                        'url'     => $url,
                        'proxy'   => $proxy,
                        'retry'   => $retryCount,
                        'message' => $e->getMessage(),
                    ]);
                    return;
                }

                Log::warning('Retrying ward crawl', [
                    'ward_id' => $ward->id,
                    'ward'    => $ward->name,
                    'proxy'   => $proxy,
                    'retry'   => $retryCount,
                    'error'   => $e->getMessage(),
                ]);

                sleep(1);
            }
        }
    }
}
