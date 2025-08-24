<?php

namespace App\Console\Commands\Ward;

use App\Jobs\Ward\CrawlWeatherWard5DaysJob;
use App\Models\Ward;
use Illuminate\Console\Command;

class CrawlWardWeather5DaysCommand extends Command
{
    protected $signature = 'crawl:weather-ward-5d';
    protected $description = 'Queue crawl 5-ngay-toi cho tất cả ward (chỉ lưu từ ngày t+2).';

    public function handle()
    {
        $wards = Ward::whereNotNull('url')->orderBy('id')->get();

        if ($wards->isEmpty()) {
            $this->warn('Không có ward nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Queue {$wards->count()} wards lên queue 'weather'...");

        foreach ($wards as $ward) {
            dispatch((new CrawlWeatherWard5DaysJob($ward->id))->onQueue('weather'));
        }

        $this->info("Đã queue xong.");
        return self::SUCCESS;
    }
}
