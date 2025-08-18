<?php

namespace App\Jobs\Province;

use App\Models\Province;
use App\Models\WeatherProvince;
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

class CrawlWeatherProvince5DaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $provinceId;

    /**
     * @param  \App\Models\Province|int  $province
     */
    public function __construct($province)
    {
        $this->provinceId = $province instanceof Province ? $province->id : (int)$province;
    }

    public function handle(): void
    {
        $province = Province::find($this->provinceId);
        if (!$province || !$province->url) {
            Log::warning('Province not found or missing URL for 5-day crawl', ['province_id' => $this->provinceId]);
            return;
        }

        $url        = rtrim($province->url, '/') . '/5-ngay-toi';
        $today      = Carbon::today();
        $minDayDate = $today->copy()->addDays(2); // chỉ lấy từ t+2 trở đi

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
                $getText = function ($node, string $selector, ?string $default = null) {
                    try {
                        if ($node->filter($selector)->count() > 0) {
                            return trim($node->filter($selector)->text());
                        }
                    } catch (\Throwable $e) {}
                    return $default;
                };

                $getAttr = function ($node, string $selector, string $attr, ?string $default = null) {
                    try {
                        if ($node->filter($selector)->count() > 0) {
                            return $node->filter($selector)->attr($attr) ?? $default;
                        }
                    } catch (\Throwable $e) {}
                    return $default;
                };

                $toFloat = function (?string $val): ?float {
                    if ($val === null) return null;
                    $num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    return is_numeric($num) ? (float)$num : null;
                };

                // Duyệt từng ngày trong khối 5 ngày
                $crawler->filter('#accordionExample details.weather-day')->each(function ($dayNode) use (
                    $province, $minDayDate, $getText, $getAttr, $toFloat
                ) {
                    // Lấy nhãn ngày ở phần summary
                    $dayLabel = $getText($dayNode, 'summary .summary-day span', '');
                    $dayDate  = null;

                    if ($dayLabel === '' || mb_stripos($dayLabel, 'Hôm nay') !== false) {
                        $dayDate = Carbon::today();
                    } else {
                        // ví dụ: "T4 20/08" -> trích "20/08"
                        if (preg_match('/(\d{1,2})\/(\d{1,2})/', $dayLabel, $m)) {
                            $dayDate = Carbon::createFromFormat('d/m', sprintf('%02d/%02d', $m[1], $m[2]))
                                ->year(Carbon::now()->year);
                        }
                    }

                    if (!($dayDate instanceof Carbon)) {
                        // fallback: thử lấy từ id data-target (vd: #detail-20-08)
                        $dataTarget = $getAttr($dayNode, 'summary.weather-summary', 'data-target', '');
                        if (preg_match('/(\d{1,2})-(\d{1,2})/', (string)$dataTarget, $m)) {
                            $dayDate = Carbon::createFromFormat('d-m', sprintf('%02d-%02d', $m[1], $m[2]))
                                ->year(Carbon::now()->year);
                        }
                    }

                    if (!($dayDate instanceof Carbon)) {
                        // không xác định được ngày -> bỏ qua
                        return;
                    }

                    // BỎ các ngày < t+2
                    if ($dayDate->lt($minDayDate)) {
                        return;
                    }

                    // Lấy sunrise/sunset (nếu có) để lưu kèm theo từng giờ
                    $sunrise = null;
                    $sunset  = null;
                    $dayNode->filter('.weather-content .weather-content-item-sun .weather-sun span')->each(function ($span, $i) use (&$sunrise, &$sunset) {
                        $txt = trim($span->text());
                        if (preg_match('/(\d{1,2}:\d{2})/', $txt, $m)) {
                            if ($i === 0) $sunrise = $m[1];
                            if ($i === 1) $sunset  = $m[1];
                        }
                    });

                    // Duyệt các block giờ bên trong ngày
                    $dayNode->filter('.weather-content .weather-summary')->each(function ($hNode) use (
                        $province, $dayDate, $sunrise, $sunset, $getText, $getAttr, $toFloat
                    ) {
                        $timeStr = trim($getText($hNode, '.summary-day', ''));
                        if ($timeStr === '' || !preg_match('/^\d{1,2}:\d{2}$/', $timeStr)) {
                            return;
                        }

                        // Tạo forecast_time = dayDate + giờ
                        try {
                            $forecastTime = $dayDate->copy()->setTimeFromTimeString($timeStr);
                        } catch (\Throwable $e) {
                            return;
                        }

                        // Lấy dữ liệu theo giờ
                        $tempMinTxt  = $getText($hNode, '.summary-temperature-min');          // "24.5°C"
                        $tempMaxTxt  = $getText($hNode, '.summary-temperature-max-value');    // "25.6°C"
                        $humidityTxt = $getText($hNode, '.summary-humidity span:last-child'); // "97%"

                        $description = $getText($hNode, '.summary-description-detail');
                        $icon        = $getAttr($hNode, '.summary-description img', 'src');
                        $windTxt     = $getText($hNode, '.summary-speed span:last-child');    // "1.16 km/giờ"

                        $tempMin  = $toFloat(str_replace('°C', '', (string)$tempMinTxt));
                        $tempMax  = $toFloat(str_replace('°C', '', (string)$tempMaxTxt));
                        $humidity = $toFloat(str_replace('%',  '', (string)$humidityTxt));

                        // Chuẩn hóa tốc độ gió (km/giờ|km/h)
                        $windTxtNorm = str_ireplace(['km/giờ', 'km/h'], '', (string)$windTxt);
                        $windSpeed   = $toFloat($windTxtNorm);

                        WeatherProvince::updateOrCreate(
                            [
                                'province_id'   => $province->id,
                                'forecast_time' => $forecastTime,
                            ],
                            [
                                'temp_min'      => $tempMin,
                                'temp_max'      => $tempMax,
                                'humidity'      => $humidity,
                                'uv_index'      => null, // trang 5 ngày thường không có UV từng giờ
                                'visibility_km' => null,
                                'description'   => $description,
                                'icon'          => $icon,
                                'wind_speed'    => $windSpeed,
                                'sunrise'       => $sunrise,
                                'sunset'        => $sunset,
                                'run_at'        => Carbon::now(),
                                'target_time'   => $forecastTime,
                                'source'        => 'thoitiet.vn',
                            ]
                        );
                    });
                });

                Log::info('Crawled 5-day (hourly from t+2) successfully', [
                    'province_id' => $province->id,
                    'province'    => $province->name,
                    'url'         => $url,
                    'proxy'       => $proxy,
                ]);
                return; // success

            } catch (\Throwable $e) {
                $retryCount++;

                // Đánh dấu proxy chết & rotate nếu có IP
                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) {
                    $ip = $m[1];
                } elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) {
                    $ip = $m2[1];
                }

                if ($ip !== 'unknown') {
                    Cache::put("proxy_dead_$ip", true, 120);
                    if (function_exists('rotateProxyIpByIp')) {
                        try { rotateProxyIpByIp($ip); } catch (\Throwable $eRotate) {
                            Log::warning('rotateProxyIpByIp failed', ['ip' => $ip, 'message' => $eRotate->getMessage()]);
                        }
                    }
                }

                if ($retryCount >= $maxRetries) {
                    Log::error('Crawl 5-day failed after max retries', [
                        'province_id' => $province->id,
                        'province'    => $province->name,
                        'url'         => $url,
                        'proxy'       => $proxy,
                        'retry'       => $retryCount,
                        'message'     => $e->getMessage(),
                    ]);
                    return;
                } else {
                    Log::warning('Retrying 5-day crawl with new proxy', [
                        'province_id' => $province->id,
                        'province'    => $province->name,
                        'url'         => $url,
                        'proxy'       => $proxy,
                        'retry'       => $retryCount,
                        'error'       => $e->getMessage(),
                    ]);
                    sleep(1);
                }
            }
        }
    }
}
