<?php

namespace App\Console\Commands;

use Exception;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestDownloadImage extends Command
{
    protected $signature = 'test:run';
    protected $description = 'Test tải ảnh từ URL và lưu bằng helper downloadImage()';

    public function handle()
    {

        $page = 1;
        $response = Http::withOptions(['verify' => false])
            ->get("https://phimapi.com/v1/api/danh-sach/hoat-hinh?page={$page}");

        if (!$response->successful()) {
            Log::error("Lỗi khi lấy trang $page");
        }

        $items = $response->json('data') ?? [];

        dd($items['items']);

        try {

            $link = "https://hdtodayz.to/watch-movie/watch-wwi-chronicles-against-all-odds-hd-125788.11163643";
            $client = new Client();
            $crawler = $client->request('GET', $link);

            $watch = $crawler->filter('.breadcrumb-item.active')->each(function ($node) {
                return $node->text();
            });


            dd($watch);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
