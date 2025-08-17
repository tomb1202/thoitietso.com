<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Jenssegers\Agent\Agent;

class HomeController extends Controller
{
    public function home()
    {
        $movies = Movie::where('hidden', 0)
            ->whereHas('sources')
            ->orderBy('updated_at', 'desc')
            ->paginate(30);

        $agent  = new Agent();
        if ($agent->isDesktop()) {
            return view('site.home', compact(
                'movies'
            ));
        } else {
            return view('site.mobile.home', compact(
                'movies'
            ));
        }
    }
}
