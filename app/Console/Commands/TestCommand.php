<?php

namespace App\Console\Commands;

use App\Jobs\Air\CrawlAirTargetJob;
use App\Models\Province;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    protected $signature = 'test:run';
    protected $description = 'Fetch 30-day weather (thoitiet.vn/30-ngay-toi) with proxy & retry.';

    public function handle()
    {
        try {
            $province = Province::with(['districts.wards'])->find(1);
            
            if (!$province || !$province->url) {
                Log::warning('Skip air dispatch: province missing or no url', ['province_id' => 1]);
                return;
            }

            $timeIso = Carbon::now()->startOfHour()->toIso8601String();
            $jobs = [];

            // province
            $jobs[] = (new CrawlAirTargetJob(
                url: $province->url,
                provinceId: $province->id,
                districtId: null,
                wardId: null,
                timeIso: $timeIso
            ))->onQueue('air');

            // districts & wards
            foreach ($province->districts as $d) {
                if ($d->url) {
                    $jobs[] = (new CrawlAirTargetJob(
                        url: $d->url,
                        provinceId: $province->id,
                        districtId: $d->id,
                        wardId: null,
                        timeIso: $timeIso
                    ))->onQueue('air');
                }
                foreach ($d->wards as $w) {
                    if ($w->url) {
                        $jobs[] = (new CrawlAirTargetJob(
                            url: $w->url,
                            provinceId: $province->id,
                            districtId: $d->id,
                            wardId: $w->id,
                            timeIso: $timeIso
                        ))->onQueue('air');
                    }
                }
            }

            // batch để chạy song song (quan sát được tiến độ)
            Bus::batch($jobs)
                ->name("air:province:{$province->id}")
                ->allowFailures()
                ->onQueue('air')
                ->dispatch();
        } catch (Exception $e) {
            dd($e);
        }
    }
}
