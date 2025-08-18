<?php

namespace App\Console\Commands;

use App\Jobs\Province\CrawlWeatherProvince5DaysJob;
use App\Models\Province;
use Illuminate\Console\Command;

class CrawlProvinceWeather5DaysCommand extends Command
{
    protected $signature = 'weather:crawl-5d 
                              {--chunk=200} 
                              {--with-null-url}';
    protected $description = 'Queue crawl 5-ngay-toi cho TẤT CẢ provinces (chỉ lưu từ ngày t+2).';

    public function handle()
    {
        $queue        = 'province';
        $chunkSize    = max(1, (int) $this->option('chunk'));
        $withNullUrl  = (bool) $this->option('with-null-url');

        $query = Province::orderBy('id');
        if (!$withNullUrl) {
            $query->whereNotNull('url');
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('Không có province nào để queue.');
            return self::SUCCESS;
        }

        $this->info("Queue {$total} provinces trên queue '{$queue}' (chunk={$chunkSize})...");
        $output = $this->output;
        $output->progressStart($total);

        $dispatched = 0;

        $query->chunkById($chunkSize, function ($provinces) use ($queue, $output, &$dispatched) {
            foreach ($provinces as $p) {
                dispatch((new CrawlWeatherProvince5DaysJob($p->id))->onQueue($queue));
                $dispatched++;
                $output->progressAdvance();
            }
        });

        $output->progressFinish();
        $this->info("Đã queue {$dispatched}/{$total} jobs lên '{$queue}'.");
        return self::SUCCESS;
    }
}
