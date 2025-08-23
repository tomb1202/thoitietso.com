<?php

namespace App\Console\Commands\Province;

use App\Jobs\Province\CrawlWeatherProvince5DaysJob;
use App\Models\Province;
use Illuminate\Console\Command;

class CrawlProvinceWeather5DaysCommand extends Command
{
    protected $signature = 'crawl:weather-province-5d';
    protected $description = 'Queue crawl 5-ngay-toi cho tất cả provinces (chỉ lưu từ ngày t+2).';

    public function handle()
    {
        $provinces = Province::whereNotNull('url')->orderBy('id')->get();

        if ($provinces->isEmpty()) {
            $this->warn('Không có province nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Queue {$provinces->count()} provinces lên queue 'province'...");

        foreach ($provinces as $province) {
            dispatch((new CrawlWeatherProvince5DaysJob($province->id))->onQueue('weather'));
        }

        $this->info("Đã queue xong.");
        return self::SUCCESS;
    }
}
