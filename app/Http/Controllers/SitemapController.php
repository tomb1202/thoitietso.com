<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $movies = Movie::where('hidden', 0)
            ->orderBy('updated_at', 'desc')
            ->limit(500)
            ->get();

        $content = view('site.sitemap', compact('movies'))->render();

        return Response::make($content, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
