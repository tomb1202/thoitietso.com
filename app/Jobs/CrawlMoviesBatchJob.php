<?php

namespace App\Jobs;

use App\Jobs\CrawlMovieDetailJob;
use App\Models\Movie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrawlMoviesBatchJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $startPage;
    protected $endPage;

    public function __construct($startPage, $endPage)
    {
        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    public function handle()
    {
        for ($page = $this->startPage; $page >= $this->endPage; $page--) {
            $response = Http::withOptions(['verify' => false])
                ->get("https://nguon.vsphim.com/api/danh-sach/phim-moi-cap-nhat?page={$page}");

            if (!$response->successful()) {
                Log::error("Lỗi khi lấy trang $page từ API");
                continue;
            }

            $data = $response->json();

            $items = $data['items'] ?? [];
            $pathImage = $data['pathImage'] ?? null;

            foreach ($items as $item) {
                $name = $item['name'] ?? null;
                if (!$name) continue;

                $slug = makeSlug($name);
                $datetime = isset($item['modified']['time']) ? date('Y-m-d H:i:s', strtotime($item['modified']['time'])) : now();

                // Lấy link ảnh
                $posterUrl = $item['poster_url'] ?? null;
                $thumbUrl  = $item['thumb_url'] ?? null;

                // Tạo mới phim nếu chưa có (atomic)
                $movie = Movie::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $name,
                        'title_en' => $item['origin_name'] ?? null,
                        'poster_path' => $posterUrl,
                        'thumb_path' => $thumbUrl,
                        'year' => $item['year'] ?? null,
                        'type' => $item['tmdb']['type'] ?? 'series',
                        'is_crawl' => false,
                        'created_at_api' => $datetime,
                        'updated_at_api' => $datetime,
                        'hidden' => 1
                    ]
                );

                // Nếu phim đã tồn tại → bỏ qua
                if (!$movie->wasRecentlyCreated) {
                    continue;
                }

                // Tải hình ảnh về server
                if (!empty($posterUrl)) {
                    $posterFilename = downloadImage($posterUrl, $slug . '.webp', false);
                    if ($posterFilename) $movie->poster = $posterFilename;
                }

                $movie->save();

                // Dispatch job crawl chi tiết
                CrawlMovieDetailJob::dispatch($movie, $item['slug']);
            }
        }
    }
}
