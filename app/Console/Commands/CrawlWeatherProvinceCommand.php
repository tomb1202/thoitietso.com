<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CrawlWeatherProvinceJob;
use App\Models\Province;

class CrawlWeatherProvinceCommand extends Command
{
    /**
     * Tên command
     */
    protected $signature = 'crawl:weather-province';

    /**
     * Mô tả
     */
    protected $description = 'Crawl weather data for all provinces (theo giờ)';

    /**
     * Thực thi
     */
    public function handle()
    {
        $provinces = Province::all(); // giả sử có bảng provinces

        foreach ($provinces as $province) {
            CrawlWeatherProvinceJob::dispatch($province->id, $province->slug)
                ->onQueue('weather'); // queue riêng
            $this->info("Đã dispatch job cho {$province->name}");
        }
    }
}
