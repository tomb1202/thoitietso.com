<?php

namespace App\Jobs;

use App\Models\Movie;
use App\Models\MovieSource;
use App\Models\Genre;
use App\Models\Country;
use App\Models\Actor;
use App\Models\Director;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrawlMovieDetailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $movie;
    public $slug;

    public function __construct(Movie $movie, string $slug)
    {
        $this->movie = $movie;
        $this->slug = $slug;
    }

    public function handle(): void
    {
        $movie = $this->movie;
        if (!$movie) return;

        // Gọi API lấy chi tiết
        $res = Http::withOptions(['verify' => false])->get("https://nguon.vsphim.com/api/phim/{$this->slug}");
        if (!$res->successful()) return;

        $data = $res->json('movie');
        if (!$data) return;

        // Cập nhật thông tin movie
        if (!$movie->code) {
            $movie->code = generateRandomCode();
        }

        $movie->description = $data['content'] ?? null;
        $movie->duration = $data['time'] ?? null;
        $movie->trailer = $data['trailer_url'] ?? null;
        $movie->quality = $data['quality'] ?? null;
        $movie->language = $data['lang'] ?? null;
        $movie->total_episode = is_numeric($data['episode_total']) ? (int) $data['episode_total'] : null;
        $movie->view = $data['view'] ?? 0;
        $movie->imdb = is_numeric($data['tmdb']['vote_average'] ?? null) ? (float) $data['tmdb']['vote_average'] : null;
        $movie->status = $data['status'] ?? 'ongoing';
        $movie->type = $data['type'] ?? 'single';
        $movie->chieurap = $data['chieurap'] ?? 0;
        $movie->created_at_api = isset($data['created']['time']) ? date('Y-m-d H:i:s', strtotime($data['created']['time'])) : null;
        $movie->updated_at_api = isset($data['modified']['time']) ? date('Y-m-d H:i:s', strtotime($data['modified']['time'])) : null;
        $movie->is_crawl = true;
        $movie->hidden = 1;
        $movie->save();

        /** =======================
         *   Lưu Genres (Thể loại)
         *  ======================= */
        $genreIds = [];
        foreach ($data['category'] ?? [] as $genre) {
            $g = Genre::firstOrCreate(
                ['slug' => $genre['slug']],
                ['name' => $genre['name'], 'hidden' => 1]
            );
            $genreIds[] = $g->id;
        }
        $movie->genres()->sync($genreIds);

        /** =======================
         *   Lưu Countries (Quốc gia)
         *  ======================= */
        $countryIds = [];
        foreach ($data['country'] ?? [] as $country) {
            $c = Country::firstOrCreate(
                ['slug' => $country['slug']],
                ['name' => $country['name']]
            );
            $countryIds[] = $c->id;
        }
        $movie->countries()->sync($countryIds);

        /** =======================
         *   Lưu Actors (Diễn viên)
         *  ======================= */
        $actorIds = [];
        foreach ($data['actor'] ?? [] as $actorName) {
            $slug = makeSlug($actorName);
            $a = Actor::firstOrCreate(['slug' => $slug], ['name' => $actorName]);
            $actorIds[] = $a->id;
        }
        $movie->actors()->sync($actorIds);

        /** =======================
         *   Lưu Directors (Đạo diễn)
         *  ======================= */
        $directorIds = [];
        foreach ($data['director'] ?? [] as $directorName) {
            $slug = makeSlug($directorName);
            $d = Director::firstOrCreate(['slug' => $slug], ['name' => $directorName]);
            $directorIds[] = $d->id;
        }
        $movie->directors()->sync($directorIds);

        /** =======================
         *   Lưu Sources (Nguồn video)
         *  ======================= */
        $episodes = $res->json('episodes') ?? [];

        // Nếu không có server => Xoá phim và các liên kết
        if (empty($episodes)) {
            $this->deleteMovieWithRelations($movie, "Không có server.");
            return;
        }

        // Xoá source cũ trước khi lưu mới
        MovieSource::where('movie_id', $movie->id)->delete();

        foreach ($episodes as $server) {
            foreach ($server['server_data'] ?? [] as $ep) {
                // Lưu m3u8
                if (!empty($ep['link_m3u8'])) {
                    MovieSource::create([
                        'movie_id' => $movie->id,
                        'type' => 'm3u8',
                        'video' => $ep['link_m3u8'],
                        'label' => $server['server_name'] ?? null,
                        'active' => true,
                        'sort' => 0,
                    ]);
                }

                // Lưu embed
                if (!empty($ep['link_embed'])) {
                    MovieSource::create([
                        'movie_id' => $movie->id,
                        'type' => 'embed',
                        'video' => $ep['link_embed'],
                        'label' => $server['server_name'] ?? null,
                        'active' => true,
                        'sort' => 0,
                    ]);
                }
            }
        }

        // Nếu sau khi lưu vẫn không có nguồn video => Xoá phim và liên kết
        if ($movie->sources()->count() === 0) {
            $this->deleteMovieWithRelations($movie, "Không có nguồn video.");
            return;
        }
    }

    /**
     * Xoá movie kèm các quan hệ liên quan
     */
    private function deleteMovieWithRelations(Movie $movie, string $reason): void
    {
        Log::warning("Xoá phim [{$movie->title}] do {$reason}");

        // Xoá các quan hệ Many-to-Many
        $movie->genres()->detach();
        $movie->countries()->detach();
        $movie->actors()->detach();
        $movie->directors()->detach();

        // Xoá sources
        MovieSource::where('movie_id', $movie->id)->delete();

        // Xoá movie
        $movie->delete();
    }
}
