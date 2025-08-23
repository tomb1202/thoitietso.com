<?php

namespace App\Console\Commands\District;

use App\Jobs\District\CrawlWeatherDistrict5DaysJob;
use App\Models\District;
use Illuminate\Console\Command;

class CrawlDistrisctWeather5DaysCommand extends Command
{
    protected $signature = 'crawl:weather-district-5d';
    protected $description = 'Queue crawl 5-ngay-toi cho tất cả district (chỉ lưu từ ngày t+2).';

    public function handle()
    {
        $districts = District::whereNotNull('url')->orderBy('id')->get();

        if ($districts->isEmpty()) {
            $this->warn('Không có district nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Queue {$districts->count()} districts lên queue 'weather'...");

        foreach ($districts as $district) {
            dispatch((new CrawlWeatherDistrict5DaysJob($district->id))->onQueue('weather'));
        }

        $this->info("Đã queue xong.");
        return self::SUCCESS;
    }
}
