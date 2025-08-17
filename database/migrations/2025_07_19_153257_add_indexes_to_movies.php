<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            // Nếu chưa có thì thêm index
            $table->index('is_trending');
            $table->index('hidden');
            $table->index('type');
            $table->index('is_coming');
            $table->index('created_at');
            $table->index('updated_at');

            // Nếu bạn thường query theo nhiều cột kết hợp thì có thể tạo composite index
            $table->index(['is_trending', 'hidden', 'type']); // cho trendingMovies, trendingTVShows
            $table->index(['is_coming', 'hidden']);           // cho comingSoonMovies
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['is_trending']);
            $table->dropIndex(['hidden']);
            $table->dropIndex(['type']);
            $table->dropIndex(['is_coming']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);

            $table->dropIndex(['is_trending', 'hidden', 'type']);
            $table->dropIndex(['is_coming', 'hidden']);
        });
    }
};
