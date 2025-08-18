<?php

namespace App\Jobs;

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

class CrawlWeatherProvinceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $province;

    public function __construct($province)
    {
        $this->province = $province;
    }

    public function handle()
    {
        $province = $this->province;
        try {
            if (!$province || !$province->url) {
                return false;
            }

            $url = rtrim($province->url, '/') . '/theo-gio';

            // ===== Proxy + Retry giống flow crawl comic =====
            $maxRetries = 5;
            $retryCount = 0;

            while ($retryCount < $maxRetries) {
                // Lấy proxy ngẫu nhiên (nếu helper không tồn tại thì để null)
                $proxy = function_exists('getRandomProxy') ? getRandomProxy() : null;

                try {
                    $httpClient = HttpClient::create([
                        // proxy dạng http://user:pass@ip:port  |  http(s)://ip:port
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

                    // ===== Helper nhỏ gọn để lấy text/float an toàn =====
                    $getText = function ($node, string $selector, ?string $default = null) {
                        try {
                            if ($node->filter($selector)->count() > 0) {
                                return trim($node->filter($selector)->text());
                            }
                        } catch (\Throwable $e) { /* ignore */
                        }
                        return $default;
                    };

                    $getAttr = function ($node, string $selector, string $attr, ?string $default = null) {
                        try {
                            if ($node->filter($selector)->count() > 0) {
                                return $node->filter($selector)->attr($attr) ?? $default;
                            }
                        } catch (\Throwable $e) { /* ignore */
                        }
                        return $default;
                    };

                    $toFloat = function (?string $val): ?float {
                        if ($val === null) return null;
                        $num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        return is_numeric($num) ? (float)$num : null;
                    };

                    // ===== Giữ currentDate & lastHour ngoài vòng each, truyền by reference =====
                    $currentDate = null;   // Carbon ngày hiện tại của block giờ
                    $lastHour    = null;   // int giờ trước đó để xử lý qua ngày

                    $crawler->filter('.weather-day')->each(function ($node) use ($province, &$currentDate, &$lastHour, $getText, $getAttr, $toFloat) {
                        $summary = $getText($node, '.summary-day span', '');

                        // 1) Nếu là dòng tiêu đề ngày: "19/08"
                        if (preg_match('/^\d{1,2}\/\d{1,2}$/', $summary)) {
                            $currentDate = Carbon::createFromFormat('d/m', $summary)->year(Carbon::now()->year);
                            $lastHour    = null; // reset chuỗi giờ
                            return;
                        }

                        // 2) Nếu là dòng giờ: "01:00" ...
                        $hourStr = trim($summary);
                        if ($hourStr === '') {
                            return;
                        }

                        // Nếu chưa có currentDate (trường hợp site không render header ngày), mặc định là hôm nay
                        if (!($currentDate instanceof Carbon)) {
                            $currentDate = Carbon::today();
                        }

                        // Tạo forecastTime từ currentDate + giờ
                        try {
                            $forecastTime = $currentDate->copy()->setTimeFromTimeString($hourStr);
                        } catch (\Throwable $e) {
                            // format giờ lạ -> bỏ qua
                            return;
                        }

                        // Xử lý qua ngày (nếu dãy giờ reset về 00-01 sau 23h)
                        $hourInt = (int)explode(':', $hourStr)[0];
                        if ($lastHour !== null && $hourInt < $lastHour) {
                            // qua ngày mới
                            $forecastTime->addDay();
                            // cập nhật currentDate để các giờ phía sau đi theo ngày mới
                            $currentDate = $forecastTime->copy()->startOfDay();
                        }
                        $lastHour = $hourInt;

                        // ===== Extract data =====
                        $tempMinTxt  = $getText($node, '.summary-temperature-min');           // "24°C"
                        $tempMaxTxt  = $getText($node, '.summary-temperature-max-value');     // "31°C"
                        $humidityTxt = $getText($node, '.summary-humidity span:last-child');  // "82%"

                        // Mô tả & icon & gió
                        $description = $getText($node, '.summary-description-detail');
                        $icon        = $getAttr($node, '.summary-description img', 'src');
                        $windTxt     = $getText($node, '.summary-speed span:last-child');     // "12 km/h" hoặc số

                        // UV & Tầm nhìn (có thể không có)
                        $uvTxt         = $getText($node, '.weather-content-item:contains("UV") span:last-child');
                        $visibilityTxt = $getText($node, '.weather-content-item:contains("Tầm nhìn") span:last-child'); // "10 km"

                        $tempMin  = $toFloat(str_replace('°C', '', (string)$tempMinTxt));
                        $tempMax  = $toFloat(str_replace('°C', '', (string)$tempMaxTxt));
                        $humidity = $toFloat(str_replace('%',  '', (string)$humidityTxt));
                        $windSpd  = $toFloat((string)$windTxt);

                        $uvIndex = $uvTxt !== null ? trim($uvTxt) : null;
                        $visibilityKm = null;
                        if ($visibilityTxt !== null) {
                            $visibilityKm = $toFloat(str_replace([' km', 'km'], '', $visibilityTxt));
                        }

                        // ===== Upsert =====
                        WeatherProvince::updateOrCreate(
                            [
                                'province_id'   => $province->id,
                                'forecast_time' => $forecastTime,
                            ],
                            [
                                'temp_min'      => $tempMin,
                                'temp_max'      => $tempMax,
                                'humidity'      => $humidity,

                                'uv_index'      => $uvIndex,
                                'visibility_km' => $visibilityKm,
                                'description'   => $description,

                                'icon'          => $icon,
                                'wind_speed'    => $windSpd,
                                'run_at'        => Carbon::now(),

                                'target_time'   => $forecastTime,
                                'source'        => 'thoitiet.vn',
                            ]
                        );
                    });

                    Log::info('Fetched hourly weather successfully', [
                        'province_id' => $province->id,
                        'province'    => $province->name ?? null,
                        'url'         => $url,
                        'proxy'       => $proxy,
                    ]);

                    return true;
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
                        Cache::put("proxy_dead_$ip", true, 120); // 2 phút
                        if (function_exists('rotateProxyIpByIp')) {
                            try {
                                rotateProxyIpByIp($ip);
                            } catch (\Throwable $eRotate) {
                                Log::warning('rotateProxyIpByIp failed', ['ip' => $ip, 'message' => $eRotate->getMessage()]);
                            }
                        }
                    }

                    if ($retryCount >= $maxRetries) {
                        Log::error('Fetch hourly weather failed after max retries', [
                            'province_id' => $province->id,
                            'province'    => $province->name ?? null,
                            'url'         => $url,
                            'proxy'       => $proxy,
                            'retry'       => $retryCount,
                            'message'     => $e->getMessage(),
                        ]);
                        return false;
                    } else {
                        Log::warning('Retrying hourly weather with new proxy', [
                            'province_id' => $province->id,
                            'province'    => $province->name ?? null,
                            'url'         => $url,
                            'proxy'       => $proxy,
                            'retry'       => $retryCount,
                            'error'       => $e->getMessage(),
                        ]);
                        sleep(1);
                    }
                }
            }

            return false;
        } catch (Exception $e) {
            // Giữ kiểu debug mạnh như bạn muốn
            dd($e);
        }
    }
}
