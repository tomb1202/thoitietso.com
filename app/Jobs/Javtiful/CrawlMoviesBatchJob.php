<?php

namespace App\Jobs\Javtiful;

use App\Models\Movie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;

class CrawlMoviesBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startPage;
    protected $endPage;

    public function __construct($startPage, $endPage)
    {
        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    public function handle(): void
    {
        $httpClient = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
            'timeout' => 20,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://javtiful.com/',
            ],
        ]);

        $client = new Client($httpClient);

        for ($page = $this->startPage; $page >= $this->endPage; $page--) {
            $link = "https://javtiful.com/videos/?page={$page}";

            try {
                $crawler = $client->request('GET', $link);

                $crawler->filter('#videos .col.pb-3')->each(function ($node) {
                    try {
                        $titleNode = $node->filter('a.video-link');
                        $title = $titleNode->attr('title');
                        $href = $titleNode->attr('href');
                        $slugRaw = basename(parse_url($href, PHP_URL_PATH));
                        $slug = makeSlug($slugRaw);

                        $img = $node->filter('img')->first();
                        $imgSrc = $img->attr('data-src') ?? $img->attr('src');

                        $descNode = $node->filter('.text-muted')->first();
                        $description = $descNode->count() ? $descNode->text() : '';

                        if (!$title || !$slug || !$imgSrc) return;

                        DB::transaction(function () use ($title, $slug, $imgSrc, $slugRaw, $href, $description) {
                            $datetime = now();

                            // === Call Gemini to rewrite ===
                            // $rewrite = rewriteMovie($slugRaw, $title, $description);
                            // $titleRewrite = $rewrite['title'] ?? $title;
                            // $descriptionRewrite = $rewrite['description'] ?? $description;

                            $movie = Movie::firstOrCreate(
                                ['slug' => $slug],
                                [
                                    'title' => $title,
                                    'url' => $href,
                                    'title_en' => $title,
                                    'poster_path' => $imgSrc,
                                    'thumb_path' => $imgSrc,
                                    'year' => null,
                                    'type' => 'series',
                                    'is_crawl' => false,
                                    'created_at_api' => $datetime,
                                    'updated_at_api' => $datetime,
                                    'hidden' => 1,
                                    'description' => $title,
                                ]
                            );

                            if (!$movie->wasRecentlyCreated) return;

                            // Tải ảnh
                            if (!empty($imgSrc)) {
                                $posterFilename = downloadImage($imgSrc, $slug . '.webp', false);
                                if ($posterFilename) $movie->poster = $posterFilename;
                            }

                            $movie->save();

                            // Dispatch crawl chi tiết
                            CrawlMovieDetailJob::dispatch($movie, $slugRaw);
                        });
                    } catch (\Throwable $e) {
                        Log::error('Lỗi xử lý item phim', ['message' => $e->getMessage()]);
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Lỗi khi crawl trang', [
                    'link' => $link,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
