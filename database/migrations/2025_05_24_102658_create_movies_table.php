<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->string('url')->nullable();
            $table->text('title')->nullable();
            $table->text('title_en')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();

            $table->string('year')->nullable();
            $table->string('duration', 50)->nullable();

            $table->string('poster')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('thumb_path')->nullable();

            $table->string('trailer')->nullable();
            $table->string('quality', 50)->nullable();
            $table->string('language', 50)->nullable();

            $table->bigInteger('view')->default(0);
            $table->float('imdb')->nullable();
            $table->integer('total_episode')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->boolean('is_hot')->default(0);
            $table->boolean('hidden')->default(0);

            $table->string('type')->default('censored');
            $table->string('status')->default('active');

            $table->boolean('chieurap')->default(false);

            $table->dateTime('created_at_api')->nullable();
            $table->dateTime('updated_at_api')->nullable();
            $table->boolean('is_trending')->default(false);

            $table->boolean('is_coming')->default(false);
            $table->boolean('is_crawl')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
