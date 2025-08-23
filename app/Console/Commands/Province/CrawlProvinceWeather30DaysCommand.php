<?php

namespace App\Console\Commands\Province;

use App\Jobs\Province\CrawlWeatherProvince30DaysJob;
use App\Models\Province;
use Illuminate\Console\Command;

class CrawlProvinceWeather30DaysCommand extends Command
{
    protected $signature   = 'crawl:weather-province-30d';
    protected $description = 'Queue crawl 30-ngay-toi cho TẤT CẢ provinces (chỉ lưu từ ngày t+4).';

    public function handle()
    {
        $provinces = Province::orderBy('id')->get();

        if ($provinces->isEmpty()) {
            $this->warn('Không có province nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Đang queue " . $provinces->count() . " provinces lên queue 'province'...");
        $this->output->progressStart($provinces->count());

        foreach ($provinces as $province) {
            dispatch((new CrawlWeatherProvince30DaysJob($province->id))->onQueue('weather'));
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("Đã queue xong tất cả jobs.");
        return self::SUCCESS;
    }
}
