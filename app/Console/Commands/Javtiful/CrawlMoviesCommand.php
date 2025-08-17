<?php

namespace App\Console\Commands\Javtiful;

use App\Jobs\Javtiful\CrawlMoviesBatchJob;
use Illuminate\Console\Command;

class CrawlMoviesCommand extends Command
{
    protected $signature = 'javtiful:movies {--page=100}';
    protected $description = 'Tự động chia nhỏ việc cào phim từ kkphim thành các job 5 trang';

    public function handle()
    {
        $startPage = (int) $this->option('page');
        $pagesPerJob = 5;

        $this->info("Bắt đầu cào phim từ trang $startPage về 1, mỗi job xử lý $pagesPerJob trang...");

        for ($batchStart = $startPage; $batchStart >= 1; $batchStart -= $pagesPerJob) {
            $batchEnd = max($batchStart - $pagesPerJob + 1, 1);
            CrawlMoviesBatchJob::dispatch($batchStart, $batchEnd);
            $this->line("→ Đã tạo job cho trang $batchStart đến $batchEnd");
        }

        $this->info("\n✅ Hoàn tất tạo các job.");
    }
}
