<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\SearchKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class PageController extends Controller
{
    public function genre(Request $request, $slug)
    {
        $genre = Genre::where('slug', $slug)->firstOrFail();

        $query = Movie::query()
            ->whereHas('genres', fn($q) => $q->where('slug', $slug))
            ->where('hidden', 0);

        $movies = $query->orderByDesc('created_at')->paginate(30);

        $agent  = new Agent();

        if ($agent->isDesktop()) {
            return view('site.genre', compact('genre', 'movies'));
        } else {
            return view('site.mobile.genre', compact('genre', 'movies'));
        }
    }

    public function search(Request $request)
    {
        $query = Movie::query()->where('hidden', 0);

        $keyword = trim($request->keyword);

        if (!empty($keyword)) {
            SearchKeyword::updateOrCreate(
                ['keyword' => $keyword],
                ['count' => DB::raw('count + 1')]
            );
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%$keyword%")
                ->orWhere('slug', 'like', "%$keyword%");
        });

        $movies = $query->orderByDesc('created_at')->paginate(30);

        $agent = new Agent();
        if ($agent->isDesktop()) {
            return view('site.search', compact('movies', 'request', 'keyword'));
        } else {
            return view('site.mobile.search', compact('movies', 'request', 'keyword'));
        }
    }
}
