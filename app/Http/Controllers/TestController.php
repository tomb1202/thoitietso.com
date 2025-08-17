<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class TestController extends Controller
{

    public function home()
    {
        $agent  = new Agent();
        
        if ($agent->isDesktop()) {
            return view('site.home');
        } else {
            return view('site.mobile.home');
        }
    }

    public function genre()
    {
        return view('site.pages.genre');
    }

    public function search()
    {
        return view('site.pages.search');
    }

    public function view()
    {
        return view('site.view');
    }
}
