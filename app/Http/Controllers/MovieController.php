<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class MovieController extends Controller
{
    public function watch($slug, $code = null)
    {
        $movie = Movie::where('slug', $slug)
            ->with(['genres', 'sources'])
            ->firstOrFail();

        // Lấy ID các genres của phim hiện tại
        $genreIds = $movie->genres->pluck('id')->toArray();

        // Lấy related movies từ cache (10 phút)
        $relatedMovies = Cache::remember("related_movies_{$movie->id}", now()->addMinutes(10), function () use ($genreIds, $movie) {
            return Movie::whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('genres.id', $genreIds);
            })
                ->where('id', '!=', $movie->id)
                ->where('hidden', 0)
                ->inRandomOrder()
                ->limit(15)
                ->get();
        });

        $agent = new Agent();
        $isIOS = $agent->is('iPhone') || $agent->is('iPad');

        if ($agent->isDesktop()) {
            return view('site.view', [
                'movie' => $movie,
                'relatedMovies' => $relatedMovies,
                'isIOS' => $isIOS,
            ]);
        } else {
            return view('site.mobile.view', [
                'movie' => $movie,
                'relatedMovies' => $relatedMovies,
                'isIOS' => $isIOS,
            ]);
        }
    }
}
