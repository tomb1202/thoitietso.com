<?php

namespace App\Jobs;

use App\Models\District;
use App\Models\Ward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrawlWardsOfDistrictJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $districtId;

    public function __construct(int $districtId)
    {
        $this->districtId = $districtId;
    }

    public function handle(): void
    {
        $district = District::find($this->districtId);
        if (!$district || !$district->url) {
            Log::warning("District not found or missing URL for ID {$this->districtId}");
            return;
        }

        try {
            $httpClient = HttpClient::create([
                'timeout'     => 20,
                'verify_peer' => false,
                'verify_host' => false,
                'headers'     => [
                    'User-Agent' => 'Mozilla/5.0',
                ],
            ]);

            $client  = new Client($httpClient);
            $crawler = $client->request('GET', $district->url);

            // Xác định khối đúng bằng class current-location có chứa "khu vực trực thuộc"
            $section = $crawler->filter('.current-location')->reduce(function ($node) {
                return str_contains($node->text(), 'khu vực trực thuộc');
            })->first();

            if (!$section) {
                Log::warning("Không tìm thấy section chứa khu vực trực thuộc cho district {$district->name}");
                return;
            }

            $section->filter('.khu-vuc-lan-can a')->each(function ($aTag) use ($district) {
                $wardName = trim($aTag->text());
                $href     = $aTag->attr('href');
                $url      = 'https://thoitiet.vn' . $href;
                $wardSlug = makeSlug($wardName);

                // Lấy lat/lng từ OpenStreetMap
                $latlng = geocodeProvinceLatLng($wardName . ', ' . $district->name);

                Ward::updateOrCreate(
                    ['code' => $wardSlug],
                    [
                        'district_id' => $district->id,
                        'name'        => $wardName,
                        'code'        => $wardSlug,
                        'url'         => $url,
                        'latitude'    => $latlng['lat'] ?? null,
                        'longitude'   => $latlng['lng'] ?? null,
                    ]
                );
            });

            Log::info("Crawled wards for district: {$district->name}");
        } catch (\Throwable $e) {
            Log::error("Failed to crawl wards for district ID {$this->districtId}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
