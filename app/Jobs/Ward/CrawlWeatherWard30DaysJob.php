<?php

namespace App\Jobs\Ward;

use App\Models\Ward;
use App\Models\WeatherWard;
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

class CrawlWeatherWard30DaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $wardId;

    public function __construct(int $wardId)
    {
        $this->wardId = $wardId;
    }

    public function handle(): bool
    {
        try {
            $ward = Ward::find($this->wardId);
            if (!$ward || !$ward->url) {
                Log::warning('Skip 30d: ward missing or no url', ['ward_id' => $this->wardId]);
                return false;
            }

            $url = rtrim($ward->url, '/') . '/30-ngay-toi';
            $maxRetries = 5;
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
                            'User-Agent'      => 'Mozilla/5.0',
                            'Accept-Language' => 'vi,en-US;q=0.9',
                            'Referer'         => 'https://thoitiet.vn/',
                        ],
                    ]);

                    $client = new Client($httpClient);
                    $crawler = $client->request('GET', $url);

                    $getText = fn($node, string $selector, ?string $default = null) =>
                        $node->filter($selector)->count() > 0 ? trim($node->filter($selector)->text()) : $default;

                    $getAttr = fn($node, string $selector, string $attr, ?string $default = null) =>
                        $node->filter($selector)->count() > 0 ? $node->filter($selector)->attr($attr) ?? $default : $default;

                    $toFloat = fn(?string $val): ?float =>
                        is_numeric($num = str_replace(',', '.', filter_var($val ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)))
                            ? (float)$num : null;

                    $toNumber = fn(?string $txt): ?float =>
                        preg_match('/-?\d+(?:[.,]\d+)?/u', $txt ?? '', $m)
                            ? (float)str_replace(',', '.', $m[0])
                            : null;

                    $today = Carbon::today();
                    $startSave = $today->copy()->addDays(4);
                    $nowYear = now()->year;
                    $nowMonth = now()->month;

                    $crawler->filter('.weather-day')->each(function ($dayNode) use (
                        $ward, $getText, $getAttr, $toFloat, $toNumber, $startSave, $nowYear, $nowMonth
                    ) {
                        $summaryNode = $dayNode->filter('summary.weather-summary');
                        $dataTarget = $summaryNode->count() ? $summaryNode->attr('data-target') : null;

                        if (!preg_match('/detail-(\d{2})-(\d{2})$/', (string)$dataTarget, $m)) {
                            $dayText = $getText($dayNode, '.summary-day span', '');
                            if (!preg_match('/(\d{1,2})\/(\d{1,2})/', $dayText, $m2)) return;
                            $m = [0, str_pad($m2[1], 2, '0', STR_PAD_LEFT), str_pad($m2[2], 2, '0', STR_PAD_LEFT)];
                        }

                        $d = (int)$m[1];
                        $M = (int)$m[2];
                        $year = ($M < $nowMonth && $nowMonth == 12) ? $nowYear + 1 : $nowYear;

                        $forecastDate = Carbon::create($year, $M, $d);
                        if ($forecastDate->lt($startSave)) return;

                        $tempMin = $toFloat(str_replace('°C', '', $getText($dayNode, '.summary-temperature-min')));
                        $tempMax = $toFloat(str_replace('°C', '', $getText($dayNode, '.summary-temperature-max-value')));
                        $humidity = $toFloat(str_replace('%', '', $getText($dayNode, '.summary-humidity span:last-child')));
                        $description = $getText($dayNode, '.summary-description-detail');
                        $icon = $getAttr($dayNode, '.summary-description img', 'src');
                        $windTxt = $getText($dayNode, '.summary-speed span:last-child');
                        $windSpd = $toNumber($windTxt);

                        $sunrise = $sunset = null;
                        try {
                            $sunNode = $dayNode->filter('.weather-content .weather-sun');
                            $sunrise = trim(preg_replace('/^\D*/', '', $sunNode->filter('span')->eq(0)->text('')));
                            $sunset  = trim(preg_replace('/^\D*/', '', $sunNode->filter('span')->eq(1)->text('')));
                        } catch (\Throwable) {}

                        WeatherWard::updateOrCreate(
                            [
                                'ward_id'       => $ward->id,
                                'forecast_time' => $forecastDate,
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
                                'run_at'        => now(),
                                'target_time'   => $forecastDate,
                                'source'        => 'thoitiet.vn',
                            ]
                        );
                    });

                    Log::info('✅ Crawled 30-day weather for ward', [
                        'ward_id' => $ward->id,
                        'ward'    => $ward->name,
                        'url'     => $url,
                        'proxy'   => $proxy,
                    ]);
                    return true;

                } catch (\Throwable $e) {
                    $retry++;
                    $ip = 'unknown';
                    if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];
                    elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];

                    if ($ip !== 'unknown') {
                        Cache::put("proxy_dead_$ip", true, 120);
                        if (function_exists('rotateProxyIpByIp')) {
                            try { rotateProxyIpByIp($ip); } catch (\Throwable) {}
                        }
                    }

                    if ($retry >= $maxRetries) {
                        Log::error('❌ Failed 30-day weather crawl (ward)', [
                            'ward_id' => $ward->id,
                            'url'     => $url,
                            'proxy'   => $proxy,
                            'message' => $e->getMessage(),
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
