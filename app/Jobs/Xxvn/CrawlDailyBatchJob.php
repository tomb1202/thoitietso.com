<?php

namespace App\Jobs\Xxvn;

use App\Models\Movie;
use App\Models\Genre;
use App\Models\Country;
use App\Models\Actor;
use App\Models\MovieSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrawlDailyBatchJob implements ShouldQueue
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
        $response = Http::withOptions(['verify' => false])
            ->get("https://www.xxvnapi.com/api/phim-moi-cap-nhat?page=1");

        if (!$response->successful()) {
            Log::error("Lỗi khi lấy dữ liệu từ API");
            return;
        }

        $movies = $response->json('movies') ?? [];

        // Chỉ lấy 5 phim đầu tiên
        $movies = array_slice($movies, 0, 5);

        foreach ($movies as $item) {
            $slug = $item['slug'] ?? null;
            $title = $item['name'] ?? null;
            if (!$slug || !$title) continue;

            $slug = makeSlug($slug);

            // Nếu phim đã có rồi thì bỏ qua
            if (Movie::where('slug', $slug)->exists()) {
                continue;
            }

            $datetime = now();

            $movie = Movie::create([
                'slug'              => $slug,
                'title'             => $title,
                'meta_title'        => $title,

                'title_en'          => $item['origin_name'] ?? null,
                'description'       => $item['content'] ?? null,
                'meta_description'  => $item['content'] ?? null,

                'poster_path'       => $item['thumb'] ?? null,
                'thumb_path'        => $item['thumb'] ?? null,
                'trailer'           => $item['trailer_url'] ?? null,

                'quality'           => $item['quality'] ?? null,
                'language'          => $item['lang'] ?? null,
                'duration'          => $item['time'] ?? null,

                'total_episode'     => 1,
                'type'              => $item['type'] ?? 'single',
                'status'            => $item['status'] ?? 'ongoing',

                'view'              => 0,
                'hidden'            => 0,
                'is_crawl'          => true,
                'created_at_api'    => $datetime,
                'updated_at_api'    => $datetime,
                'code'              => generateRandomCode(),
            ]);

            // Tải ảnh về và gán vào movie
            if (!empty($item['thumb_url'])) {
                $posterFilename = downloadImage($item['thumb_url'], $slug . '.webp', false);
                if ($posterFilename) {
                    $movie->poster = $posterFilename;
                    $movie->save();
                }
            }

            /** ========== Genres ========== */
            $genreIds = [];
            foreach ($item['categories'] ?? [] as $genre) {
                $g = Genre::firstOrCreate(
                    ['slug' => $genre['slug']],
                    ['name' => $genre['name'], 'hidden' => 0]
                );
                $genreIds[] = $g->id;
            }
            $movie->genres()->sync($genreIds);

            /** ========== Country ========== */
            $countryIds = [];
            if (!empty($item['country'])) {
                $c = Country::firstOrCreate(
                    ['slug' => $item['country']['slug']],
                    ['name' => $item['country']['name']]
                );
                $countryIds[] = $c->id;
            }
            $movie->countries()->sync($countryIds);

            /** ========== Actors ========== */
            $actorIds = [];
            foreach ($item['actors'] ?? [] as $actorName) {
                $slugActor = makeSlug($actorName);
                $a = Actor::firstOrCreate(['slug' => $slugActor], ['name' => $actorName]);
                $actorIds[] = $a->id;
            }
            $movie->actors()->sync($actorIds);

            /** ========== Directors (rỗng nếu không có) ========== */
            $movie->directors()->sync([]);

            /** ========== Sources ========== */
            foreach ($item['episodes'] ?? [] as $server) {
                foreach ($server['server_data'] ?? [] as $ep) {
                    if (!empty($ep['link'])) {
                        MovieSource::create([
                            'movie_id' => $movie->id,
                            'type' => str_contains($ep['link'], 'm3u8') ? 'm3u8' : 'embed',
                            'video' => $ep['link'],
                            'label' => $server['server_name'] ?? null,
                            'active' => true,
                            'sort' => 0,
                        ]);
                    }
                }
            }

            // Nếu không có source thì xoá luôn
            if ($movie->sources()->count() === 0) {
                $this->deleteMovieWithRelations($movie, 'Không có nguồn video');
            }
        }
    }


    private function deleteMovieWithRelations(Movie $movie, string $reason): void
    {
        Log::warning("Xoá phim [{$movie->title}] do {$reason}");

        $movie->genres()->detach();
        $movie->countries()->detach();
        $movie->actors()->detach();
        $movie->directors()->detach();
        MovieSource::where('movie_id', $movie->id)->delete();
        $movie->delete();
    }
}
