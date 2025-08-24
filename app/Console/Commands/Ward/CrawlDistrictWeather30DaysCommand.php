<?php

namespace App\Console\Commands\Ward;

use App\Jobs\Ward\CrawlWeatherWard30DaysJob;
use App\Models\Ward;
use Illuminate\Console\Command;

class CrawlWardWeather30DaysCommand extends Command
{
    protected $signature   = 'crawl:weather-ward-30d';
    protected $description = 'Queue crawl 30-ngay-toi cho TẤT CẢ wards (chỉ lưu từ ngày t+4).';

    public function handle()
    {
        $wards = Ward::orderBy('id')->get();

        if ($wards->isEmpty()) {
            $this->warn('Không có ward nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Đang queue " . $wards->count() . " wards lên queue 'ward'...");
        $this->output->progressStart($wards->count());

        foreach ($wards as $ward) {
            dispatch((new CrawlWeatherWard30DaysJob($ward->id))->onQueue('ward'));
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("🎉 Đã queue xong tất cả jobs.");
        return self::SUCCESS;
    }
}
