<?php

namespace App\Console\Commands\District;

use App\Jobs\District\CrawlWeatherDistrict30DaysJob;
use App\Models\District;
use Illuminate\Console\Command;

class CrawlDistrictWeather30DaysCommand extends Command
{
    protected $signature   = 'weather:district-crawl-30d';
    protected $description = 'Queue crawl 30-ngay-toi cho TẤT CẢ districts (chỉ lưu từ ngày t+4).';

    public function handle()
    {
        $districts = District::orderBy('id')->get();

        if ($districts->isEmpty()) {
            $this->warn('Không có district nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Đang queue " . $districts->count() . " districts lên queue 'district'...");
        $this->output->progressStart($districts->count());

        foreach ($districts as $district) {
            dispatch((new CrawlWeatherDistrict30DaysJob($district->id))->onQueue('district'));
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("🎉 Đã queue xong tất cả jobs.");
        return self::SUCCESS;
    }
}
