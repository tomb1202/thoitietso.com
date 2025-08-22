<?php

namespace App\Jobs\Air;

use App\Models\Province;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;

class DispatchAirCrawlForProvinceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(protected int $provinceId) {}

    public function handle(): void
    {
        $province = Province::with(['districts.wards'])->find($this->provinceId);
        if (!$province || !$province->url) {
            Log::warning('Skip air dispatch: province missing or no url', ['province_id' => $this->provinceId]);
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
    }
}
