<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Movie;
use Carbon\Carbon;

class RandomizeMovie extends Command
{
    protected $signature = 'movies:randomize';
    protected $description = 'Random created_at và updated_at cho các phim có hidden = 0';

    public function handle()
    {
        $this->info("Bắt đầu random timestamps trong 1 ngày gần đây cho phim (hidden = 0)...");

        $movies = Movie::where('hidden', 0)->get();

        if ($movies->isEmpty()) {
            $this->warn("Không có phim nào để random!");
            return;
        }

        $bar = $this->output->createProgressBar($movies->count());
        $bar->start();

        foreach ($movies as $movie) {
            $randomCreated = Carbon::now()
                ->subHours(rand(1, 24))
                ->setMinute(rand(0, 59))
                ->setSecond(rand(0, 59));

            $randomUpdated = $randomCreated->copy()
                ->addHours(rand(0, 12))
                ->setMinute(rand(0, 59))
                ->setSecond(rand(0, 59));

            if ($randomUpdated->lt($randomCreated)) {
                $randomUpdated = $randomCreated->copy()->addHours(rand(1, 6));
            }

            $movie->created_at = $randomCreated;
            $movie->updated_at = $randomUpdated;
            $movie->saveQuietly();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Đã random timestamps cho {$movies->count()} phim trong vòng 1 ngày gần đây!");
    }
}
