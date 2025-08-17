<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\MovieSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MovieService
{
    public function storeMovie(array $data): ?Movie
    {
        DB::beginTransaction();
        try {
            // Tạo Movie
            $movie = new Movie();
            $movie->title            = $data['title'];
            $movie->title_en         = $data['title_en'] ?? null;
            $movie->slug             = $data['slug'] ?? Str::slug($data['title']);
            $movie->code             = $data['code'];
            $movie->description      = $data['description'] ?? null;
            $movie->hidden           = $data['hidden'] ?? 0;
            $movie->poster           = $data['poster'] ?? null;
            $movie->thumbnail        = $data['thumbnail'] ?? null;
            $movie->meta_title       = $data['meta_title'] ?? null;
            $movie->meta_description = $data['meta_description'] ?? null;
            $movie->save();

            // Đồng bộ thể loại
            $movie->genres()->sync($data['genre_ids'] ?? []);

            // Tạo MovieSource trực tiếp từ m3u8 và embed
            if (!empty($data['m3u8'])) {
                MovieSource::create([
                    'movie_id' => $movie->id,
                    'video'    => $data['m3u8'],
                    'type'     => 'm3u8',
                    'label'    => 'M3U8',
                    'sort'     => 0,
                    'active'   => true,
                ]);
            }

            if (!empty($data['embed'])) {
                MovieSource::create([
                    'movie_id' => $movie->id,
                    'video'    => $data['embed'],
                    'type'     => 'embed',
                    'label'    => 'Embed',
                    'sort'     => 1,
                    'active'   => true,
                ]);
            }

            DB::commit();
            return $movie;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return null;
        }
    }

    public function updateMovie($id, array $data): bool
    {
        try {
            $movie = Movie::find($id);
            if (!$movie) {
                return false;
            }

            DB::beginTransaction();

            // Cập nhật thông tin chính
            $movie->title            = $data['title'];
            $movie->title_en         = $data['title_en'] ?? null;
            $movie->slug             = $data['slug'] ?? Str::slug($data['title']);
            $movie->description      = $data['description'] ?? null;
            $movie->hidden           = $data['hidden'] ?? 0;
            $movie->poster           = $data['poster'] ?? $movie->poster;
            $movie->thumbnail        = $data['thumbnail'] ?? $movie->thumbnail;
            $movie->meta_title       = $data['meta_title'] ?? null;
            $movie->meta_description = $data['meta_description'] ?? null;
            $movie->save();

            // Đồng bộ quan hệ thể loại
            $movie->genres()->sync($data['genre_ids'] ?? []);

            // Xoá toàn bộ MovieSource cũ
            MovieSource::where('movie_id', $movie->id)->delete();

            // Thêm MovieSource mới từ m3u8 & embed (nếu có)
            if (!empty($data['m3u8'])) {
                MovieSource::create([
                    'movie_id' => $movie->id,
                    'video'    => $data['m3u8'],
                    'type'     => 'm3u8',
                    'label'    => 'M3U8',
                    'sort'     => 0,
                    'active'   => true,
                ]);
            }

            if (!empty($data['embed'])) {
                MovieSource::create([
                    'movie_id' => $movie->id,
                    'video'    => $data['embed'],
                    'type'     => 'embed',
                    'label'    => 'Embed',
                    'sort'     => 1,
                    'active'   => true,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return false;
        }
    }
}
