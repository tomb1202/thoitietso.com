<?php

namespace App\Jobs;

use App\Models\WeatherProvince;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlWeatherProvinceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $provinceId;
    protected $provinceSlug;

    public function __construct($provinceId, $provinceSlug)
    {
        $this->provinceId = $provinceId;
        $this->provinceSlug = $provinceSlug;
    }

    public function handle()
    {
        $client = new Client();
        $url = "https://thoitiet.vn/{$this->provinceSlug}/theo-gio";

        $crawler = $client->request('GET', $url);

        $crawler->filter('.weather-day')->each(function ($node) {
            $summary = $node->filter('.summary-day span')->text();

            // Xác định xem là ngày hay giờ
            if (preg_match('/\d{2}\/\d{2}/', $summary)) {
                // ngày, vd "19/08"
                $currentDate = Carbon::createFromFormat('d/m', trim($summary))
                    ->year(Carbon::now()->year);
            } else {
                // giờ, vd "01:00"
                $hour = trim($summary);
                if (!isset($currentDate)) return; // skip nếu chưa có ngày

                $forecastTime = $currentDate->copy()->setTimeFromTimeString($hour);

                // Extract data
                $tempMin = (float) str_replace('°C', '', $node->filter('.summary-temperature-min')->text());
                $tempMax = (float) str_replace('°C', '', $node->filter('.summary-temperature-max-value')->text());
                $humidity = (float) str_replace('%', '', $node->filter('.summary-humidity span')->last()->text());
                $description = $node->filter('.summary-description-detail')->text();
                $icon = $node->filter('.summary-description img')->attr('src');
                $windSpeed = (float) filter_var($node->filter('.summary-speed span')->last()->text(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                // chi tiết bên trong (UV, visibility)
                $uvIndex = $node->filter('.weather-content-item:contains("UV") span')->last()->text(null);
                $visibility = $node->filter('.weather-content-item:contains("Tầm nhìn") span')->last()->text(null);

                WeatherProvince::updateOrCreate(
                    [
                        'province_id' => $this->provinceId,
                        'forecast_time' => $forecastTime,
                    ],
                    [
                        'temp_min' => $tempMin,
                        'temp_max' => $tempMax,
                        'humidity' => $humidity,
                        'uv_index' => $uvIndex ?? null,
                        'visibility_km' => isset($visibility) ? (float) str_replace(' km', '', $visibility) : null,
                        'description' => $description,
                        'icon' => $icon,
                        'wind_speed' => $windSpeed,
                        'run_at' => Carbon::now(),
                        'target_time' => $forecastTime,
                        'source' => 'thoitiet.vn',
                    ]
                );
            }
        });
    }
}
