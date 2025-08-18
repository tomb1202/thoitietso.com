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
use Illuminate\Support\Facades\Cache;

class CrawlWardsOfDistrictJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $districtId;

    public function __construct(int $districtId)
    {
        $this->districtId = $districtId;
    }

    public function handle(): void
    {
        $district = District::find($this->districtId);
        if (!$district || !$district->url) {
            Log::warning("District not found or missing URL", ['district_id' => $this->districtId]);
            return;
        }

        $maxRetries = 5;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            $proxy = getRandomProxy();

            try {
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
                $crawler = $client->request('GET', $district->url);

                // Tìm section ".current-location" có chứa "khu vực trực thuộc"
                $section = null;
                $crawler->filter('.current-location')->each(function ($node) use (&$section) {
                    if (str_contains(mb_strtolower($node->text()), 'khu vực trực thuộc')) {
                        $section = $node;
                    }
                });

                if ($section === null) {
                    Log::warning('Không tìm thấy section chứa "khu vực trực thuộc"', [
                        'district_id' => $district->id,
                        'district'    => $district->name,
                        'url'         => $district->url,
                        'proxy'       => $proxy,
                    ]);
                    // Không coi là lỗi proxy: thoát job bình thường
                    return;
                }

                // Duyệt từng phường/xã
                $section->filter('.khu-vuc-lan-can a')->each(function ($aTag) use ($district) {
                    try {
                        $wardName = trim($aTag->text());
                        if ($wardName === '') {
                            return;
                        }

                        $href = $aTag->attr('href');
                        if (!$href) {
                            return;
                        }

                        $url      = str_starts_with($href, 'http') ? $href : ('https://thoitiet.vn' . $href);
                        $wardSlug = makeSlug($wardName);

                        // Geocode: "ward, district" để tăng chính xác
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
                    } catch (\Throwable $e) {
                        Log::error('Parse/Save ward failed', [
                            'district_id' => $district->id,
                            'district'    => $district->name,
                            'message'     => $e->getMessage(),
                        ]);
                    }
                });

                Log::info('Crawled wards successfully', [
                    'district_id' => $district->id,
                    'district'    => $district->name,
                    'proxy'       => $proxy,
                ]);

                return; // thành công -> thoát

            } catch (\Throwable $e) {
                $retryCount++;

                // Tách IP proxy (hỗ trợ cả dạng user:pass@ip:port và http://ip:port)
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
                    Log::error('CrawlWardsOfDistrictJob failed after max retries', [
                        'district_id' => $district->id,
                        'district'    => $district->name,
                        'proxy'       => $proxy,
                        'retry'       => $retryCount,
                        'message'     => $e->getMessage(),
                    ]);
                } else {
                    Log::warning('Retrying CrawlWardsOfDistrictJob with new proxy', [
                        'district_id' => $district->id,
                        'district'    => $district->name,
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
