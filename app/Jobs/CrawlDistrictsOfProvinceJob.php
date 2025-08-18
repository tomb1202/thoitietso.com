<?php

namespace App\Jobs;

use App\Models\Province;
use App\Models\District;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Jobs\CrawlWardsOfDistrictJob;

class CrawlDistrictsOfProvinceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $provinceId;

    public function __construct(int $provinceId)
    {
        $this->provinceId = $provinceId;
    }

    public function handle(): void
    {
        $province = Province::find($this->provinceId);
        if (!$province || !$province->url) {
            Log::warning("Province not found or missing URL for ID {$this->provinceId}");
            return;
        }

        try {
            $httpClient = HttpClient::create([
                'timeout'     => 20,
                'verify_peer' => false,
                'verify_host' => false,
                'headers'     => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $client  = new Client($httpClient);
            $crawler = $client->request('GET', $province->url);

            $crawler->filter('.location-data .khu-vuc-lan-can a')->each(function ($aTag) use ($province) {
                $districtName = trim($aTag->text());
                $href         = $aTag->attr('href');
                $url          = 'https://thoitiet.vn' . $href;
                $districtSlug = makeSlug($districtName);

                // Geocode theo OSM (district + province để tăng độ chính xác)
                $latlng = geocodeProvinceLatLng($districtName . ', ' . $province->name);

                // Lưu/Update district
                $district = District::updateOrCreate(
                    ['code' => $districtSlug],
                    [
                        'province_id' => $province->id,
                        'name'        => $districtName,
                        'code'        => $districtSlug,
                        'url'         => $url,
                        'latitude'    => $latlng['lat'] ?? null,
                        'longitude'   => $latlng['lng'] ?? null,
                    ]
                );

                dispatch(new CrawlWardsOfDistrictJob($district->id))->onQueue('ward');
            });

            Log::info("Crawled districts for province: {$province->name}");
        } catch (\Throwable $e) {
            Log::error("Failed to crawl districts for province ID {$this->provinceId}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
