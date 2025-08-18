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
use Illuminate\Support\Facades\Cache;
use App\Jobs\CrawlWardsOfDistrictJob;

class CrawlDistrictsOfProvinceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $provinceId;

    public function __construct(int $provinceId)
    {
        $this->provinceId = $provinceId;
    }

    public function handle(): void
    {
        $province = Province::find($this->provinceId);

        if (!$province || !$province->url) {
            Log::warning("Province not found or missing URL", ['province_id' => $this->provinceId]);
            return;
        }

        $maxRetries = 5;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            // 1) Chọn proxy ngẫu nhiên
            $proxy = getRandomProxy();

            try {
                // 2) Tạo HttpClient với proxy + header giống trình duyệt
                $httpClient = HttpClient::create([
                    'proxy'        => $proxy,
                    'verify_peer'  => false,
                    'verify_host'  => false,
                    'timeout'      => 25,
                    'headers'      => [
                        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122 Safari/537.36',
                        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'vi,en-US;q=0.9,en;q=0.8',
                        'Referer'         => 'https://thoitiet.vn/',
                    ],
                ]);

                $client  = new Client($httpClient);

                // 3) Request trang province
                $crawler = $client->request('GET', $province->url);

                // 4) Parse danh sách quận/huyện
                $crawler->filter('.location-data .khu-vuc-lan-can a')->each(function ($aTag) use ($province) {
                    try {
                        $districtName = trim($aTag->text());
                        if ($districtName === '') {
                            return;
                        }

                        $href = $aTag->attr('href');
                        if (!$href) {
                            return;
                        }

                        $url          = str_starts_with($href, 'http') ? $href : ('https://thoitiet.vn' . $href);
                        $districtSlug = makeSlug($districtName);

                        // Geocode (district + province để chính xác hơn)
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

                        // Dispatch crawl phường/xã
                        dispatch(new CrawlWardsOfDistrictJob($district->id))->onQueue('ward');
                    } catch (\Throwable $e) {
                        Log::error('Parse/Save district failed', [
                            'province_id' => $province->id,
                            'province'    => $province->name,
                            'message'     => $e->getMessage(),
                        ]);
                    }
                });

                Log::info('Crawled districts successfully', [
                    'province_id' => $province->id,
                    'province'    => $province->name,
                    'proxy'       => $proxy,
                ]);

                // Thành công -> thoát hàm
                return;

            } catch (\Throwable $e) {
                // 5) Thất bại -> rotate proxy & retry
                $retryCount++;

                // Tách IP để đánh dấu proxy chết và rotate (định dạng user:pass@ip:port)
                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) {
                    $ip = $m[1];
                } elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) {
                    $ip = $m2[1];
                }

                if ($ip !== 'unknown') {
                    Cache::put("proxy_dead_$ip", true, 120); // 2 phút
                    try {
                        rotateProxyIpByIp($ip);
                    } catch (\Throwable $eRotate) {
                        Log::warning('rotateProxyIpByIp failed', [
                            'ip'      => $ip,
                            'message' => $eRotate->getMessage(),
                        ]);
                    }
                }

                if ($retryCount >= $maxRetries) {
                    Log::error('CrawlDistrictsOfProvinceJob failed after max retries', [
                        'province_id' => $this->provinceId,
                        'province'    => $province->name ?? null,
                        'proxy'       => $proxy,
                        'retry'       => $retryCount,
                        'message'     => $e->getMessage(),
                    ]);
                } else {
                    Log::warning('Retrying CrawlDistrictsOfProvinceJob with new proxy', [
                        'province_id' => $this->provinceId,
                        'province'    => $province->name ?? null,
                        'proxy'       => $proxy,
                        'retry'       => $retryCount,
                        'error'       => $e->getMessage(),
                    ]);
                    sleep(1);
                }
            }
        }
    }
}
