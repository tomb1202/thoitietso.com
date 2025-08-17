<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class TestController extends Controller
{

    public function index()
    {
        return view('site.master');
    }

    public function temp()
    {
        return view('site.temp');
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
