<?php

namespace App\Console\Commands\Province;

use App\Jobs\District\CrawlWeatherDistrictJob;
use App\Models\District;
use Illuminate\Console\Command;
use App\Models\Province;

class CrawlDistrictWeatherCommand extends Command
{
    protected $signature = 'crawl:d-weather-province';

    protected $description = 'Crawl weather data for all provinces (theo giờ)';

    public function handle()
    {
        $districts = District::all();

        foreach ($districts as $district) {
            CrawlWeatherDistrictJob::dispatch($district)->onQueue('weather');
            $this->info("Đã dispatch job cho {$district->name}");
        }
    }
}
