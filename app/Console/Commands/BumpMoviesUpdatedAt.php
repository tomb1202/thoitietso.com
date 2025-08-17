<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Movie;
use Illuminate\Support\Facades\DB;

class BumpMoviesUpdatedAt extends Command
{
    protected $signature = 'movies:bump-updated {--limit=5}';
    protected $description = 'Chọn ngẫu nhiên N phim và cập nhật updated_at=now() để đẩy lên đầu';

    public function handle()
    {
        $limit = (int) $this->option('limit') ?: 5;

        // Lấy ngẫu nhiên ID 5 phim đủ điều kiện
        $ids = Movie::where('hidden', 0)
            ->whereHas('sources')
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->warn('Không tìm thấy phim phù hợp.');
            return Command::SUCCESS;
        }

        // Cập nhật updated_at = now()
        $affected = Movie::whereIn('id', $ids)->update(['updated_at' => now()]);

        $this->info("✅ Đã bump updated_at cho {$affected} phim: " . $ids->implode(', '));
        return Command::SUCCESS;
    }
}
