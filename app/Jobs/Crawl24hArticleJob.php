<?php

namespace App\Jobs\News;

use App\Models\Article;
use App\Models\Genre;
use App\Models\Tag;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class Crawl24hArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $backoff = 5;

    public function __construct(
        public string $url,
        public ?string $fallbackTitle = null,
        public ?string $fallbackExcerpt = null,
        public ?string $fallbackThumb = null
    ) {}

    public function handle(): void
    {
        $maxRetries = 5;
        $retry = 0;

        // Đảm bảo có Genre trước
        $genre = Genre::firstOrCreate(
            ['slug' => 'du-bao-thoi-tiet'],
            ['name' => 'Dự báo thời tiết', 'description' => 'Tin thời tiết tổng hợp từ 24h']
        );

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
                $crawler = $client->request('GET', $this->url);

                $getText = fn(Crawler $node, string $sel, ?string $def = null)
                => $node->filter($sel)->count() ? trim($node->filter($sel)->text()) : $def;

                $getAttr = fn(Crawler $node, string $sel, string $attr, ?string $def = null)
                => $node->filter($sel)->count() ? ($node->filter($sel)->attr($attr) ?? $def) : $def;

                $title   = $getText($crawler, '#article_title', $this->fallbackTitle);
                $excerpt = $getText($crawler, '#article_sapo', $this->fallbackExcerpt);

                // Nội dung chính (HTML)
                $contentNode = $crawler->filter('.cate-24h-foot-arti-deta-info');
                $contentHtml = $contentNode->count() ? $contentNode->html() : null;

                // Meta từ <head>
                $metaTitle       = $getText($crawler, 'title', $title);
                $metaDescription = $getAttr($crawler, 'meta[name="description"]', 'content', $excerpt);
                $metaKeywords    = $getAttr($crawler, 'meta[name="keywords"]', 'content');

                // Ảnh: ưu tiên og:image; fallback ảnh truyền từ list
                $thumb = $getAttr($crawler, 'meta[property="og:image"]', 'content', $this->fallbackThumb);

                // Author & nguồn/copyright (các data-src là Base64)
                $authorEnc = $getAttr($crawler, '#origin_name_full', 'data-src');
                $author    = $authorEnc ? (base64_decode($authorEnc) ?: null) : null;

                $copyUrlEnc = $getAttr($crawler, '#url_origin_full', 'data-src');
                $copyright  = $copyUrlEnc ? (base64_decode($copyUrlEnc) ?: null) : null;

                // Thời gian xuất bản
                $timeTxt = $getText($crawler, '.source-time-art24h .time_partners', null);
                // ví dụ: " - 24/08/2025 05:45 AM (GMT+7)"
                $publishedAt = null;
                if ($timeTxt && preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2}\s*(AM|PM))/i', $timeTxt, $m)) {
                    $publishedAt = Carbon::createFromFormat('d/m/Y h:i A', "{$m[1]} {$m[2]}", 'Asia/Ho_Chi_Minh');
                }

                // Upsert bài theo URL (tránh trùng)
                $article = Article::updateOrCreate(
                    ['url' => $this->url],
                    [
                        'genre_id'         => $genre->id,
                        'title'            => $title,
                        'slug'             => $this->uniqueSlug($title),
                        'excerpt'          => $excerpt,
                        'content'          => $contentHtml,
                        'thumbnail'        => $thumb,
                        'meta_title'       => $metaTitle,
                        'meta_description' => $metaDescription,
                        'meta_keywords'    => $metaKeywords,
                        'highlight'        => 0,
                        'hidden'           => 0,
                        'copyright'        => $copyright,
                        'author'           => $author,
                        'copy_at'          => optional($publishedAt)->toDateTimeString(),
                        'published_at'     => $publishedAt,
                    ]
                );

                // ===== Tags =====
                // Trong chi tiết: .cate-24h-foot-arti-deta-tags a (vd: "Tin bão")
                $tagNames = [];
                $crawler->filter('.cate-24h-foot-arti-deta-tags a')->each(function ($a) use (&$tagNames) {
                    $name = trim($a->text(''));
                    if ($name !== '') $tagNames[] = $name;
                });
                $tagNames = array_values(array_unique($tagNames));

                if (!empty($tagNames)) {
                    $tagIds = [];
                    foreach ($tagNames as $name) {
                        $slug = Str::slug($name);
                        /** @var Tag $tag */
                        $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
                        $tagIds[] = $tag->id;
                    }
                    // đồng bộ pivot
                    $article->tags()->syncWithoutDetaching($tagIds);
                }

                Log::info('Saved article from 24h', [
                    'id'    => $article->id,
                    'title' => mb_substr($article->title, 0, 120),
                    'url'   => $this->url,
                    'proxy' => $proxy,
                ]);

                return;
            } catch (\Throwable $e) {
                $retry++;

                // mark dead proxy & rotate
                $ip = 'unknown';
                if (preg_match('/@([\d\.]+):/', (string)$proxy, $m)) $ip = $m[1];
                elseif (preg_match('/^https?:\/\/([\d\.]+):/', (string)$proxy, $m2)) $ip = $m2[1];
                if ($ip !== 'unknown') {
                    cache()->put("proxy_dead_$ip", true, 120);
                    if (function_exists('rotateProxyIpByIp')) {
                        try {
                            rotateProxyIpByIp($ip);
                        } catch (\Throwable) {
                        }
                    }
                }

                if ($retry >= $maxRetries) {
                    Log::error('Save article failed after retries', ['url' => $this->url, 'err' => $e->getMessage()]);
                    return;
                }
                Log::warning('Retry article detail', ['url' => $this->url, 'retry' => $retry, 'err' => $e->getMessage()]);
                sleep(1);
            }
        }
    }

    private function uniqueSlug(?string $title): string
    {
        $base = Str::slug($title ?? Str::random(8));
        $slug = $base;
        $i = 1;
        while (Article::where('slug', $slug)->where('url', '!=', $this->url)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
