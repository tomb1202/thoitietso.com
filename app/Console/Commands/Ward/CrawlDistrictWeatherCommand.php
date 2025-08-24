<?php

namespace App\Console\Commands\Ward;

use App\Jobs\Ward\CrawlWeatherWardJob;
use App\Models\Ward;
use Illuminate\Console\Command;

class CrawlWardWeatherCommand extends Command
{
    protected $signature = 'crawl:weather-ward';
    protected $description = 'Crawl weather data for all wards (theo giờ)';

    public function handle()
    {
        $wards = Ward::all();

        foreach ($wards as $ward) {
            CrawlWeatherWardJob::dispatch($ward)->onQueue('weather');
            $this->info("Đã dispatch job cho {$ward->name}");
        }
    }
}
