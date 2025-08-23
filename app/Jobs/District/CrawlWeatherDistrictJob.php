<?php

namespace App\Jobs\District;

use App\Models\WeatherDistrict;
use Carbon\Carbon;
use Exception;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;

class CrawlWeatherDistrictJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $district;

    public function __construct($district)
    {
        $this->district = $district;
    }

    public function handle()
    {
        $district = $this->district;
        try {
            if (!$district || !$district->url) return false;

            $url = rtrim($district->url, '/') . '/theo-gio';
            $maxRetries = 5;
            $retryCount = 0;

            while ($retryCount < $maxRetries) {
                $proxy = function_exists('getRandomProxy') ? getRandomProxy() : null;

                try {
                    $httpClient = HttpClient::create([
                        'proxy' => $proxy,
                        'verify_peer' => false,
                        'verify_host' => false,
                        'timeout' => 25,
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0',
                            'Accept-Language' => 'vi,en-US;q=0.9',
                            'Referer' => 'https://thoitiet.vn/',
                        ],
                    ]);

                    $client = new Client($httpClient);
                    $crawler = $client->request('GET', $url);

                    $getText = fn($node, $sel, $def = null) => $node->filter($sel)->count() ? trim($node->filter($sel)->text()) : $def;
                    $getAttr = fn($node, $sel, $attr, $def = null) => $node->filter($sel)->count() ? $node->filter($sel)->attr($attr) : $def;
                    $toFloat = fn($val) => is_numeric($num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) ? (float)$num : null;

                    // Mặt trời mọc/lặn
                    $sunrise = null;
                    $sunset = null;
                    $crawler->filter('.weather-sun span')->each(function ($span, $i) use (&$sunrise, &$sunset) {
                        $text = trim($span->text());
                        if (preg_match('/\d{1,2}:\d{2}/', $text, $m)) {
                            if ($i === 0) $sunrise = $m[0];
                            if ($i === 1) $sunset = $m[0];
                        }
                    });

                    $currentDate = null;
                    $lastHour = null;

                    $crawler->filter('.weather-day')->each(function ($node) use ($district, &$currentDate, &$lastHour, $sunrise, $sunset, $getText, $getAttr, $toFloat) {
                        $summary = $getText($node, '.summary-day span', '');

                        if (preg_match('/^\d{1,2}\/\d{1,2}$/', $summary)) {
                            $currentDate = Carbon::createFromFormat('d/m', $summary)->year(Carbon::now()->year);
                            $lastHour = null;
                            return;
                        }

                        $hourStr = trim($summary);
                        if ($hourStr === '') return;
                        if (!($currentDate instanceof Carbon)) $currentDate = Carbon::today();

                        try {
                            $forecastTime = $currentDate->copy()->setTimeFromTimeString($hourStr);
                        } catch (\Throwable) {
                            return;
                        }

                        $hourInt = (int)explode(':', $hourStr)[0];
                        if ($lastHour !== null && $hourInt < $lastHour) {
                            $forecastTime->addDay();
                            $currentDate = $forecastTime->copy()->startOfDay();
                        }
                        $lastHour = $hourInt;

                        $tempMin = $toFloat(str_replace('°C', '', $getText($node, '.summary-temperature-min')));
                        $tempMax = $toFloat(str_replace('°C', '', $getText($node, '.summary-temperature-max-value')));
                        $humidity = $toFloat(str_replace('%', '', $getText($node, '.summary-humidity span:last-child')));

                        $description = $getText($node, '.summary-description-detail');
                        $icon = $getAttr($node, '.summary-description img', 'src');
                        $windSpd = $toFloat($getText($node, '.summary-speed span:last-child'));
                        $uvIndex = $getText($node, '.weather-content-item:contains("UV") span:last-child');
                        $visibility = $toFloat(str_replace([' km', 'km'], '', $getText($node, '.weather-content-item:contains("Tầm nhìn") span:last-child')));

                        WeatherDistrict::updateOrCreate(
                            [
                                'district_id'    => $district->id,
                                'forecast_time'  => $forecastTime,
                            ],
                            [
                                'temp_min'       => $tempMin,
                                'temp_max'       => $tempMax,
                                'humidity'       => $humidity,
                                'uv_index'       => $uvIndex,
                                'visibility_km'  => $visibility,
                                'description'    => $description,
                                'icon'           => $icon,
                                'wind_speed'     => $windSpd,
                                'sunrise'        => $sunrise,
                                'sunset'         => $sunset,
                                'run_at'         => Carbon::now(),
                                'target_time'    => $forecastTime,
                                'source'         => 'thoitiet.vn',
                            ]
                        );
                    });

                    Log::info('✅ Fetched hourly weather for district', [
                        'district_id' => $district->id,
                        'url'         => $url,
                        'proxy'       => $proxy,
                    ]);

                    return true;

                } catch (\Throwable $e) {
                    $retryCount++;

                    $ip = preg_match('/@([\d\.]+):/', (string)$proxy, $m) ? $m[1] :
                          (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2) ? $m2[1] : 'unknown');

                    if ($ip !== 'unknown') Cache::put("proxy_dead_$ip", true, 120);

                    if (function_exists('rotateProxyIpByIp')) {
                        try { rotateProxyIpByIp($ip); } catch (\Throwable $eRotate) {}
                    }

                    if ($retryCount >= $maxRetries) {
                        Log::error('❌ Failed hourly weather for district after retries', [
                            'district_id' => $district->id,
                            'proxy'       => $proxy,
                            'message'     => $e->getMessage(),
                        ]);
                        return false;
                    }

                    sleep(1);
                }
            }

            return false;
        } catch (Exception $e) {
            dd($e);
        }
    }
}
