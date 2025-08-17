<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Movie;
use App\Models\Genre;
use Carbon\Carbon;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for home, genres, and watch links';
    private string $base = 'https://xxvn.ink';

    public function handle()
    {
        $this->info("Đang tạo sitemap.xml...");

        $urls = [];

        // 1) Trang chủ
        $urls[] = $this->makeUrl($this->abs('/'));

        $genres = Genre::where('hidden', 0)->where('slug', '!=', '')->get();
        foreach ($genres as $genre) {
            $urls[] = $this->makeUrl($this->abs('/genre/' . $this->e($genre->slug)), $genre->updated_at);
        }

        // 3) Movies -> watch/{slug}
        $movies = Movie::where('hidden', 0)->where('slug', '!=', '')->get();
        foreach ($movies as $movie) {
            $urls[] = $this->makeUrl($this->abs('/watch/' . $this->e($movie->slug)), $movie->updated_at);
        }

        // Xuất XML
        $sitemapContent = $this->generateSitemap($urls);
        Storage::disk('public')->put('sitemaps/sitemap.xml', $sitemapContent);

        $this->info("Sitemap đã tạo: storage/app/public/sitemaps/sitemap.xml");
    }

    private function abs(string $path): string
    {
        return rtrim($this->base, '/') . '/' . ltrim($path, '/');
    }

    private function e(string $segment): string
    {
        // Encode từng segment cho an toàn URL (slug thường đã safe, nhưng cứ chắc cú)
        return rawurlencode($segment);
    }

    private function makeUrl($loc, $lastmod = null, $changefreq = 'daily', $priority = '0.8')
    {
        return [
            'loc'        => htmlspecialchars($loc, ENT_XML1, 'UTF-8'),
            'lastmod'    => $lastmod ? Carbon::parse($lastmod)->toDateString() : Carbon::now()->toDateString(),
            'changefreq' => $changefreq,
            'priority'   => $priority
        ];
    }

    private function generateSitemap($urls)
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= "  <url>" . PHP_EOL;
            $xml .= "    <loc>{$url['loc']}</loc>" . PHP_EOL;
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>" . PHP_EOL;
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>" . PHP_EOL;
            $xml .= "    <priority>{$url['priority']}</priority>" . PHP_EOL;
            $xml .= "  </url>" . PHP_EOL;
        }

        $xml .= '</urlset>' . PHP_EOL;
        return $xml;
    }
}
