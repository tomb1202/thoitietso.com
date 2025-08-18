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

class CrawlWeatherProvince30DaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $provinceId;

    public function __construct(int $provinceId)
    {
        $this->provinceId = $provinceId;
    }

    public function handle(): bool
    {
        try {
            $province = Province::find($this->provinceId);
            if (!$province || !$province->url) {
                Log::warning('Skip 30d: province missing or no url', ['province_id' => $this->provinceId]);
                return false;
            }

            $url = rtrim($province->url, '/') . '/30-ngay-toi';

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

                    // Chuẩn hoá số từ chuỗi (hỗ trợ 1.62 và 1,62)
                    $toNumber = function (?string $txt): ?float {
                        if ($txt === null) return null;
                        if (preg_match('/-?\d+(?:[.,]\d+)?/u', $txt, $m)) {
                            $num = str_replace(',', '.', $m[0]);
                            return is_numeric($num) ? (float)$num : null;
                        }
                        return null;
                    };

                    $toFloat = function (?string $val): ?float {
                        if ($val === null) return null;
                        $val = str_replace(',', '.', $val);
                        $num = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        return is_numeric($num) ? (float)$num : null;
                    };

                    $today      = Carbon::today();
                    $startSave  = $today->copy()->addDays(4); // chỉ lưu từ T+4
                    $nowYear    = (int)Carbon::now()->year;
                    $nowMonth   = (int)Carbon::now()->month;

                    // Duyệt từng ngày
                    $crawler->filter('.weather-day')->each(function ($dayNode) use ($province, $getText, $getAttr, $toFloat, $toNumber, $startSave, $nowYear, $nowMonth) {
                        // Lấy id dạng "detail-19-08" từ summary hoặc content
                        $summaryNode = $dayNode->filter('summary.weather-summary');
                        $dataTarget  = $summaryNode->count() ? $summaryNode->attr('data-target') : null; // "#detail-19-08"
                        $contentId   = ltrim((string)$dataTarget, '#');

                        if (!$contentId || !preg_match('/detail-(\d{2})-(\d{2})$/', $contentId, $m)) {
                            // fallback: đọc "T4 20/08" trong .summary-day span
                            $dayText = $getText($dayNode, '.summary-day span', '');
                            if (preg_match('/(\d{1,2})\/(\d{1,2})/', $dayText, $m2)) {
                                $m = [0, str_pad($m2[1], 2, '0', STR_PAD_LEFT), str_pad($m2[2], 2, '0', STR_PAD_LEFT)];
                            } else {
                                return; // không xác định được ngày
                            }
                        }

                        $d = (int)$m[1];
                        $M = (int)$m[2];
                        $year = $nowYear;

                        // Xử lý qua năm (ví dụ đang 12 mà trang có 01)
                        if ($M < $nowMonth && $nowMonth === 12) {
                            $year = $nowYear + 1;
                        }

                        $forecastDate = Carbon::create($year, $M, $d, 0, 0, 0);

                        // Chỉ lưu từ T+4 trở đi
                        if ($forecastDate->lt($startSave)) {
                            return;
                        }

                        // ===== Summary =====
                        $tempMinTxt  = $getText($dayNode, '.summary-temperature-min');                // "24.2°C"
                        $tempMaxTxt  = $getText($dayNode, '.summary-temperature-max-value');          // "29.1°C"
                        $humidityTxt = $getText($dayNode, '.summary-humidity span:last-child');       // "83%"
                        $description = $getText($dayNode, '.summary-description-detail');
                        $icon        = $getAttr($dayNode, '.summary-description img', 'src');

                        // WIND: lấy từ summary, fallback detail
                        $windTxt = null;

                        // 1) Summary
                        try {
                            if ($dayNode->filter('.summary-speed span')->count() > 0) {
                                $windTxt = trim($dayNode->filter('.summary-speed span')->last()->text());
                            }
                        } catch (\Throwable $e) {}

                        // 2) Fallback: detail item có <h6>Gió</h6> -> lấy <h3>
                        if (!$windTxt) {
                            try {
                                $dayNode->filter('.weather-content .weather-content-item')->each(function ($n) use (&$windTxt) {
                                    $label = '';
                                    try { $label = trim($n->filter('h6')->text('')); } catch (\Throwable $e) {}
                                    if ($label !== '' && mb_stripos($label, 'Gió') !== false) {
                                        try {
                                            $val = trim($n->filter('h3')->text(''));
                                            if ($val !== '') {
                                                $windTxt = $val;
                                            }
                                        } catch (\Throwable $e) {}
                                    }
                                });
                            } catch (\Throwable $e) {}
                        }

                        // Sunrise/Sunset trong detail
                        $sunrise = $sunset = null;
                        try {
                            if ($dayNode->filter('.weather-content .weather-sun')->count()) {
                                $sunNode    = $dayNode->filter('.weather-content .weather-sun');
                                $sunriseTxt = trim(preg_replace('/^\D*/', '', (string)$sunNode->filter('span')->eq(0)->text()));
                                $sunsetTxt  = trim(preg_replace('/^\D*/', '', (string)$sunNode->filter('span')->eq(1)->text()));
                                $sunrise    = $sunriseTxt !== '' ? $sunriseTxt : null;
                                $sunset     = $sunsetTxt  !== '' ? $sunsetTxt  : null;
                            }
                        } catch (\Throwable $e) {}

                        // convert các số
                        $tempMin  = $toFloat(str_replace('°C', '', (string)$tempMinTxt));
                        $tempMax  = $toFloat(str_replace('°C', '', (string)$tempMaxTxt));
                        $humidity = $toFloat(str_replace('%',  '', (string)$humidityTxt));
                        $windSpd  = $toNumber($windTxt); // chuẩn hoá "1.62 km/giờ" / "1,62 km/giờ"

                        // Upsert cho mỗi NGÀY lúc 00:00
                        WeatherProvince::updateOrCreate(
                            [
                                'province_id'   => $province->id,
                                'forecast_time' => $forecastDate, // 00:00
                            ],
                            [
                                'temp_min'      => $tempMin,
                                'temp_max'      => $tempMax,
                                'humidity'      => $humidity,
                                'description'   => $description,
                                'icon'          => $icon,
                                'wind_speed'    => $windSpd,
                                'sunrise'       => $sunrise,
                                'sunset'        => $sunset,
                                'run_at'        => Carbon::now(),
                                'target_time'   => $forecastDate,
                                'source'        => 'thoitiet.vn',
                            ]
                        );
                    });

                    Log::info('Fetched 30-day weather successfully', [
                        'province_id' => $province->id,
                        'province'    => $province->name,
                        'url'         => $url,
                        'proxy'       => $proxy,
                    ]);

                    return true;
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
                        Log::error('Fetch 30-day weather failed after retries', [
                            'province_id' => $province->id,
                            'province'    => $province->name,
                            'url'         => $url,
                            'proxy'       => $proxy,
                            'retry'       => $retry,
                            'message'     => $e->getMessage(),
                        ]);
                        return false;
                    }

                    Log::warning('Retry 30-day weather with new proxy', [
                        'province_id' => $province->id,
                        'province'    => $province->name,
                        'url'         => $url,
                        'proxy'       => $proxy,
                        'retry'       => $retry,
                        'error'       => $e->getMessage(),
                    ]);
                    sleep(1);
                }
            }

            return false;
        } catch (Exception $e) {
            dd($e);
        }
    }
}
