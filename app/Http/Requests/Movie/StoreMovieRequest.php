<?php

namespace App\Http\Requests\Movie;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'bail|required|string|max:255',
            'title_en'    => 'nullable|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'hidden'      => 'nullable|in:0,1',

            'poster'      => 'nullable|file|image|max:5000',
            'thumbnail'   => 'nullable|file|image|max:5000',

            'genre_ids'   => 'nullable|array',
            'genre_ids.*' => 'integer|exists:genres,id',

            // ✅ Thêm validate cho link m3u8 và embed
            'm3u8'        => 'nullable|string|max:1000',
            'embed'       => 'nullable|string|max:1000',

            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề phim.',
            'poster.image'   => 'Poster phải là một ảnh.',
            'thumbnail.image'=> 'Thumbnail phải là một ảnh.',

            'genre_ids.*.exists' => 'Thể loại không hợp lệ.',

            // ✅ Thông báo lỗi cho m3u8 và embed
            'm3u8.max'      => 'Link m3u8 không được vượt quá 1000 ký tự.',
            'embed.max'     => 'Link embed không được vượt quá 1000 ký tự.',

            'meta_title.max' => 'Meta title không được vượt quá 255 ký tự.',
            'meta_description.max' => 'Meta description không được vượt quá 500 ký tự.',
        ];
    }
}
