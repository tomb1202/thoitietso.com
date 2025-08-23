<?php

namespace App\Console\Commands\Province;

use App\Jobs\Province\CrawlWeatherProvinceJob;
use Illuminate\Console\Command;
use App\Models\Province;

class CrawlProvinceWeatherCommand extends Command
{
    protected $signature = 'crawl:p-weather-province';

    protected $description = 'Crawl weather data for all provinces (theo giờ)';

    public function handle()
    {
        $provinces = Province::all();

        foreach ($provinces as $province) {
            CrawlWeatherProvinceJob::dispatch($province)->onQueue('weather');
            $this->info("Đã dispatch job cho {$province->name}");
        }
    }
}
