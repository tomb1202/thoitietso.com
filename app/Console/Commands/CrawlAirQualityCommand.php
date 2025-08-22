<?php

namespace App\Console\Commands;

use App\Models\Province;
use Illuminate\Console\Command;
use App\Jobs\Air\DispatchAirCrawlForProvinceJob;

class CrawlAirQualityCommand extends Command
{
    protected $signature   = 'air:crawl-all';
    protected $description = 'Queue crawl chất lượng không khí cho tất cả provinces (tỉnh + quận + phường).';

    public function handle()
    {
        $provinces = Province::whereNotNull('url')->orderBy('id')->get();

        foreach ($provinces as $p) {
            
            dispatch((new DispatchAirCrawlForProvinceJob($p->id))->onQueue('air'));
        }

        return self::SUCCESS;
    }
}
