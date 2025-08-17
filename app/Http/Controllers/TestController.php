<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class TestController extends Controller
{
    public function index()
    {
        return view('site.home');
    }

    public function weather()
    {
        return view('site.pages.weather');
    }

    public function topic()
    {
        return view('site.pages.topic');
    }

    public function post()
    {
        return view('site.pages.post');
    }

    public function rss()
    {
        return view('site.policies.rss');
    }

    public function introduce()
    {
        return view('site.policies.introduce');
    }

    public function contact()
    {
        return view('site.policies.contact');
    }

    public function policy()
    {
        return view('site.policies.policy');
    }

}
