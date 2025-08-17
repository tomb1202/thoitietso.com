<?php

namespace App\Console\Commands;

use App\Jobs\CrawlMoviesBatchJob;
use Illuminate\Console\Command;

class AutoCrawlCommand extends Command
{
    protected $signature = 'auto:crawl-movies';
    protected $description = 'Cào phim từ kkphim cho trang đầu tiên (trang 1)';

    public function handle()
    {
        $startPage = 1;
        $endPage = 1;

        $this->info("Bắt đầu cào phim cho trang $startPage...");

        $this->line("→ Đã tạo job cho trang $startPage");

        $this->info("\n✅ Hoàn tất tạo job.");
    }
}
