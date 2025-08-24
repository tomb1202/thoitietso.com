<?php

namespace App\Console\Commands\News;

use App\Jobs\News\Crawl24hArticleJob;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;

class Crawl24hWeatherNewsCommand extends Command
{
    protected $signature   = 'news:24h-weather';
    protected $description = 'Crawl 24h - chuyên mục Dự báo thời tiết, dispatch 1 job/bài.';

    public function handle(): int
    {
        $url = 'https://www.24h.com.vn/du-bao-thoi-tiet-c568.html';

        $maxRetries = 5; $retry = 0;

        while ($retry < $maxRetries) {
            $proxy = function_exists('getRandomProxy') ? getRandomProxy() : null;

            try {
                $httpClient = HttpClient::create([
                    'proxy'        => $proxy,
                    'verify_peer'  => false,
                    'verify_host'  => false,
                    'timeout'      => 25,
                    'headers'      => [
                        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122 Safari/537.36',
                        'Accept-Language' => 'vi,en-US;q=0.9',
                        'Referer'         => 'https://www.24h.com.vn/',
                    ],
                ]);
                $client  = new Client($httpClient);
                $crawler = $client->request('GET', $url);

                $items = [];

                // Box lớn phía trên
                $crawler->filter('article.ltd-news-big')->each(function ($node) use (&$items) {
                    $link = $node->filter('a')->first()->attr('href') ?? null;
                    if (!$link) return;
                    $title = trim($node->filter('h3 a')->text(''));
                    $excerpt = trim($node->filter('.ltd-news-big-sum p')->text(''));
                    $thumb = $node->filter('figure img')->count()
                        ? ($node->filter('figure img')->attr('data-original') ?? $node->filter('figure img')->attr('src'))
                        : null;

                    $items[] = compact('link','title','excerpt','thumb');
                });

                // List bài phía dưới
                $crawler->filter('.cate-24h-foot-home-latest .cate-24h-foot-home-latest-list__box')->each(function ($node) use (&$items) {
                    $a = $node->filter('a')->first();
                    if (!$a->count()) return;
                    $link = $a->attr('href');
                    $title = trim($node->filter('h3 a')->text(''));
                    $excerpt = trim($node->filter('.cate-24h-foot-home-latest-list__sum')->text(''));
                    $img = $node->filter('figure img');
                    $thumb = $img->count() ? ($img->attr('data-original') ?? $img->attr('src')) : null;

                    $items[] = compact('link','title','excerpt','thumb');
                });

                if (empty($items)) {
                    $this->warn('Không tìm thấy item nào.');
                    return self::SUCCESS;
                }

                foreach ($items as $it) {
                    // Mỗi bài 1 job; truyền kèm title/excerpt/thumb để fallback
                    Crawl24hArticleJob::dispatch(
                        $it['link'],
                        $it['title'] ?? null,
                        $it['excerpt'] ?? null,
                        $it['thumb'] ?? null
                    )->onQueue('news');
                }

                $this->info("Đã dispatch " . count($items) . " bài vào queue 'news'.");
                return self::SUCCESS;

            } catch (\Throwable $e) {
                $retry++;

                // proxy mark dead + rotate
                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];
                elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) $ip = $m2[1];
                if ($ip !== 'unknown') {
                    cache()->put("proxy_dead_$ip", true, 120);
                    if (function_exists('rotateProxyIpByIp')) { try { rotateProxyIpByIp($ip); } catch (\Throwable) {} }
                }

                Log::warning('Retry list page 24h', ['retry'=>$retry, 'err'=>$e->getMessage()]);
                if ($retry >= $maxRetries) {
                    Log::error('List page 24h failed after retries', ['err'=>$e->getMessage()]);
                    return self::FAILURE;
                }
                sleep(1);
            }
        }

        return self::FAILURE;
    }
}
