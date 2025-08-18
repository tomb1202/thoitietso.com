<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\Province;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\CrawlDistrictsOfProvinceJob;

class CrawlAddressCommand extends Command
{
    protected $signature = 'crawl:address';
    protected $description = 'Crawl regions and provinces from thoitiet.vn';

    public function handle()
    {
        $url         = 'https://thoitiet.vn/';
        $proxy       = getRandomProxy();
        $maxRetries  = 5;
        $retryCount  = 0;

        while ($retryCount < $maxRetries) {
            try {
                $httpClient = HttpClient::create([
                    'proxy'        => $proxy,
                    'verify_peer'  => false,
                    'verify_host'  => false,
                    'timeout'      => 25,
                    'headers'      => [
                        'User-Agent' => 'Mozilla/5.0',
                        'Accept'     => 'text/html',
                        'Referer'    => 'https://truyenfull.vision/',
                    ],
                ]);

                $client  = new Client($httpClient);
                $crawler = $client->request('GET', $url);

                $crawler->filter('.dropdown-menu .col-megamenu')->each(function ($regionNode) {
                    $regionName = trim($regionNode->filter('h6.title')->text());
                    $regionSlug = makeSlug($regionName);

                    DB::transaction(function () use ($regionName, $regionSlug, $regionNode) {
                        $region = Region::updateOrCreate(
                            ['code' => $regionSlug],
                            ['name' => $regionName, 'code' => $regionSlug]
                        );

                        $regionNode->filter('ul.mega-submenu li a')->each(function ($aTag) use ($region) {
                            $provinceName = trim($aTag->text());
                            $provinceSlug = makeSlug($provinceName);
                            $provinceUrl  = 'https://thoitiet.vn' . $aTag->attr('href');

                            $province = Province::updateOrCreate(
                                ['code' => $provinceSlug],
                                [
                                    'region_id' => $region->id,
                                    'name'      => $provinceName,
                                    'code'      => $provinceSlug,
                                    'url'       => $provinceUrl,
                                ]
                            );

                            $latlng = geocodeProvinceLatLng($provinceName);

                            if ($latlng) {
                                $province->update([
                                    'latitude'  => $latlng['lat'],
                                    'longitude' => $latlng['lng'],
                                ]);
                            } else {
                                Log::warning("Không lấy được tọa độ từ Nominatim cho: {$provinceName}");
                            }

                            // Dispatch job để cào District
                            dispatch(new CrawlDistrictsOfProvinceJob($province->id));
                        });
                    });
                });

                $this->info('✅ Cào xong Region + Province (và đã dispatch Job cào District)');
                return;
            } catch (\Throwable $e) {
                $retryCount++;

                preg_match('/@([\d\.]+):/', $proxy, $match);
                $ip = $match[1] ?? 'unknown';

                if ($ip !== 'unknown') {
                    cache()->put("proxy_dead_$ip", true, 120);
                    rotateProxyIpByIp($ip);
                }

                Log::error("❌ Lỗi lần {$retryCount}: " . $e->getMessage());
                sleep(1);
            }
        }

        $this->error('⛔ Hết số lần retry, command failed.');
    }
}
