<?php

namespace App\Console\Commands;

use App\Jobs\CrawlAirJob;
use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CrawlAirQualityCommand extends Command
{
    protected $signature   = 'air:crawl-all';
    protected $description = 'Queue crawl chất lượng không khí cho tất cả provinces (tỉnh + quận + phường).';

    public function handle()
    {

        $provinces = Province::with(['districts.wards'])->get();

        foreach ($provinces as $province) {
            if (!$province || !$province->url) {
                Log::warning('Skip test: province missing or no url', ['province_id' => 1]);
                $this->error("Province không tồn tại hoặc chưa có URL.");
                return self::FAILURE;
            }

            dispatch((new CrawlAirJob($province->id))->onQueue('air'));
            $this->info("✅ Dispatched: Province - {$province->name}");

            foreach ($province->districts as $district) {
                if ($district->url) {
                    dispatch((new CrawlAirJob($province->id, $district->id))->onQueue('district'));
                    $this->info("✅ Dispatched: District - {$district->name}");
                }

                foreach ($district->wards as $ward) {
                    if ($ward->url) {
                        dispatch((new CrawlAirJob($province->id, $district->id, $ward->id))->onQueue('ward'));
                        $this->info("✅ Dispatched: Ward - {$ward->name}");
                    }
                }
            }

            $this->info("🎉 Đã dispatch xong toàn bộ job crawl không khí cho tỉnh: {$province->name}");

            return self::SUCCESS;
        }
    }
}
