<?php

namespace App\Console\Commands;

use App\Jobs\CrawlAirJob;
use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    protected $signature = 'test:run';
    protected $description = 'Test crawl chất lượng không khí cho 1 province (id = 1) và các cấp con';

    public function handle()
    {
        $province = Province::with(['districts.wards'])->find(1);

        if (!$province || !$province->url) {
            Log::warning('Skip test: province missing or no url', ['province_id' => 1]);
            $this->error("Province không tồn tại hoặc chưa có URL.");
            return self::FAILURE;
        }

        // Crawl cấp tỉnh
        dispatch((new CrawlAirJob($province->id))->onQueue('air'));
        $this->info("✅ Dispatched: Province - {$province->name}");

        // Crawl cấp quận (nếu có URL)
        foreach ($province->districts as $district) {
            if ($district->url) {
                dispatch((new CrawlAirJob($province->id, $district->id))->onQueue('district'));
                $this->info("✅ Dispatched: District - {$district->name}");
            }
            // Crawl cấp phường (nếu có URL)
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
