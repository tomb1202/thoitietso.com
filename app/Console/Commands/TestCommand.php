<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\MovieSource;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $movie = Movie::find(4);

        $link = $movie->url;

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

        $crawler = $client->request('GET', $link);

        $crawler->filter('#video-section')->each(function ($node) use ($movie) {
            try {
                $movie->quality = 'FHD';
                $movie->view = rand(1300, 50000);
                $movie->status = 'active';
                $movie->is_crawl = true;
                $movie->hidden = 1;
                $movie->save();

                /** ====== GENRES ====== */
                $genreIds = [];
                $node->filter('.video-details__label:contains("Category")')->each(function ($catNode) use (&$genreIds) {
                    $catNode->nextAll()->filter('a')->each(function ($a) use (&$genreIds) {
                        $name = trim($a->text());
                        $slug = makeSlug($name);

                        $genre = \App\Models\Genre::firstOrCreate(
                            ['slug' => $slug],
                            ['name' => $name, 'hidden' => 1]
                        );

                        $genreIds[] = $genre->id;
                    });
                });


                $movie->genres()->sync($genreIds);

                /** ====== TAGS ====== */
                $tagIds = [];

                $node->filter('.video-details__item')->each(function ($item) use (&$tagIds) {
                    $label = trim($item->filter('.video-details__label')->text());

                    if (Str::lower($label) === 'tags') {
                        $item->filter('.video-details__item_links a')->each(function ($a) use (&$tagIds) {
                            $name = trim($a->text());
                            $slug = makeSlug($name);

                            if (!$slug) return;

                            try {
                                $tag = \App\Models\Tag::firstOrCreate(
                                    ['slug' => $slug],
                                    ['name' => $name, 'hidden' => 1]
                                );
                            } catch (\Throwable $e) {
                                // Nếu bị lỗi duplicate thì lấy lại
                                $tag = \App\Models\Tag::where('slug', $slug)->first();
                            }

                            if ($tag) {
                                $tagIds[] = $tag->id;
                            }
                        });
                    }
                });

                $movie->tags()->sync($tagIds);


                /** =======================
                 *   Lưu Diễn Viên (Actors)
                 *  ======================= */
                $actorIds = [];
                $node->filter('.video-details__label:contains("Actress")')->each(function ($label) use (&$actorIds) {
                    $label->nextAll()->filter('a')->each(function ($a) use (&$actorIds) {
                        $name = trim($a->filter('span')->text());
                        $slug = makeSlug($name);
                        $avatar = $a->filter('img')->attr('src') ?? null;

                        $actor = \App\Models\Actor::firstOrCreate(
                            ['slug' => $slug],
                            ['name' => $name, 'avatar' => $avatar]
                        );

                        $actorIds[] = $actor->id;
                    });
                });
                $movie->actors()->sync($actorIds);
            } catch (\Throwable $e) {
                Log::error('Lỗi xử lý detail phim', ['message' => $e->getMessage()]);
            }
        });

        /** =======================
         *   Lưu nguồn video (Embed)
         *  ======================= */
        $embedLink = null;

        $crawler->filter('button.share-btn')->each(function ($button) use (&$embedLink) {
            $embedLink = $button->attr('data-embed-url');
        });

        // Xoá source cũ trước khi lưu
        MovieSource::where('movie_id', $movie->id)->delete();

        // Nếu không tìm được link → xoá luôn phim và quan hệ
        if (!$embedLink) {
            $this->deleteMovieWithRelations($movie, "Không có nguồn video.");
            return;
        }

        // Lưu embed
        MovieSource::create([
            'movie_id' => $movie->id,
            'type' => 'embed',
            'video' => $embedLink,
            'label' => 'embed',
            'active' => true,
            'sort' => 0,
        ]);
    }
}
