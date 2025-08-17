<?php

namespace App\Console\Commands\Xxvn;

use App\Jobs\Xxvn\CrawlDailyBatchJob;
use Illuminate\Console\Command;

class CrawlDailyCommand extends Command
{
    protected $signature = 'xxvn:daily-movies';
    protected $description = 'Tự động cào phim mới hằng ngày (chỉ page 1)';

    public function handle()
    {
        $startPage = 1;
        $endPage   = 1;

        $this->info("Bắt đầu cào phim ở trang $startPage...");

        CrawlDailyBatchJob::dispatch($startPage, $endPage);

        $this->info("Đã tạo job crawl page 1 xong.");
    }
}
