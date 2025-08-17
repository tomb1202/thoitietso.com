<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


if (!function_exists('makeSlug')) {
    function makeSlug($string)
    {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        );
        $replace = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        );
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower($string);
        return $string;
    }
}

if (!function_exists('downloadImage')) {
    /**
     * Tải ảnh từ URL và lưu vào thư mục phù hợp dưới định dạng WebP.
     *
     * @param string $url       Link ảnh gốc (.jpg, .png, ...)
     * @param string $filename  Tên file ảnh lưu (vd: abc.webp)
     * @param bool   $isThumb   true nếu là ảnh bìa, false nếu là ảnh chương
     * @return string|null      Đường dẫn tương đối (ví dụ: covers/abc.webp), hoặc null nếu lỗi
     */
    function downloadImage(string $url, string $filename, bool $isThumb = false): ?string
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->get($url);

            if (!$response->successful()) {
                return null;
            }

            // Lưu file tạm
            $tmpPath = storage_path('app/tmp_image_input_' . uniqid());
            file_put_contents($tmpPath, $response->body());

            $imageInfo = getimagesize($tmpPath);
            if (!$imageInfo) {
                @unlink($tmpPath);
                return null;
            }

            switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($tmpPath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($tmpPath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($tmpPath);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($tmpPath);
                    break;
                default:
                    @unlink($tmpPath);
                    return null;
            }

            if (!$image) {
                @unlink($tmpPath);
                return null;
            }

            if (!str_ends_with(strtolower($filename), '.webp')) {
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
            }

            $folder = $isThumb ? 'thumbs' : 'posters';
            $storageFolder = storage_path("app/public/images/{$folder}");

            if (!file_exists($storageFolder)) {
                mkdir($storageFolder, 0777, true);
                @chmod($storageFolder, 0777);
            }

            $savePath = "{$storageFolder}/{$filename}";

            imagepalettetotruecolor($image);
            imagewebp($image, $savePath, 100);

            imagedestroy($image);
            @unlink($tmpPath);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}


if (!function_exists('uploadFileAdv')) {
    function uploadFileAdv($file, $name, $folder = 'uploads')
    {
        // Get the original file extension
        $extension = $file->getClientOriginalExtension();

        // Generate a unique name for the file
        $filename = $name . '-' . uniqid() . '.' . $extension;

        // Define full folder path (storage/app/public/...)
        $folderPath = storage_path('app/public/' . $folder);

        // Ensure the directory exists and has correct permissions
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        @chmod($folderPath, 0777);

        // Save the file
        $file->storeAs('public/' . $folder, $filename);

        return $filename;
    }
}

if (!function_exists('uploadForSetting')) {
    function uploadForSetting($file, $image, $name)
    {
        $folderDir = 'public/uploads/logo/';
        $thumbName = $name . '-' . time() . '.png';

        // Đảm bảo thư mục tồn tại với quyền phù hợp
        $fullPath = storage_path('app/' . $folderDir);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        @chmod($fullPath, 0777);

        // Xử lý ảnh
        $image = Image::make($file);
        $imageStream = $image->stream('png');

        // Lưu file
        Storage::put($folderDir . $thumbName, $imageStream->__toString());

        return $thumbName;
    }
}

if (!function_exists('sourceSetting')) {
    function sourceSetting($image)
    {
        return url('storage/uploads/logo/' . $image);
    }
}

if (!function_exists('uploadImageLocal')) {
    /**
     * Upload ảnh local và convert sang .webp
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $name Tên file không chứa đuôi
     * @param bool $isThumb true nếu là thumbnail, false nếu là poster
     * @return string|null  Tên file .webp hoặc null nếu lỗi
     */
    function uploadImageLocal($file, string $name, bool $isThumb = false): ?string
    {
        try {
            $folder = $isThumb ? 'thumbs' : 'posters';
            $storageFolder = "public/images/{$folder}";
            $filename = $name . '-' . time() . '.webp';

            // Đảm bảo thư mục tồn tại
            $fullPath = storage_path("app/{$storageFolder}");
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
                @chmod($fullPath, 0777);
            }

            $image = Image::make($file)->encode('webp', 80);
            Storage::put("{$storageFolder}/{$filename}", $image->__toString());

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('generateRandomCode')) {
    function generateRandomCode(int $length = 8): string
    {
        return strtolower(substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 5)), 0, $length));
    }
}

if (!function_exists('rewrite_title')) {
    /**
     * Rewrite tiêu đề bằng Google Gemini
     */
    function rewrite_title(string $title): string
    {
        return call_gemini_api($title, 'Viết lại tiêu đề phim ngắn gọn, hấp dẫn, tự nhiên, chuẩn SEO.');
    }
}

if (!function_exists('rewrite_content')) {
    /**
     * Rewrite nội dung bằng Google Gemini
     */
    function rewrite_content(string $content): string
    {
        return call_gemini_api($content, 'Viết lại mô tả nội dung phim tự nhiên, hấp dẫn, chuẩn SEO, giữ nguyên ý chính.');
    }
}

if (!function_exists('call_gemini_api')) {
    /**
     * Hàm gọi API Gemini
     */
    function call_gemini_api(string $text, string $instruction): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = 'gemini-pro'; // Hoặc gemini-1.5-flash

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $instruction . "\n\n" . $text]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json('candidates.0.content.parts.0.text') ?? $text;
        }

        return $text; // Nếu API lỗi, trả về text gốc
    }
}

if (!function_exists('rewriteMovie')) {
    /**
     * Dịch tiêu đề & mô tả phim sang tiếng Việt, giữ mã và rút gọn tiêu đề.
     */
    function rewriteMovie(string $rawTitle, string $description): ?array
    {
        // Lấy mã phim từ tiêu đề (thường nằm đầu)
        preg_match('/^([A-Z0-9\-]+)/', $rawTitle, $matches);
        $code = $matches[1] ?? null;

        if (!$code) return null;

        // Prompt mới theo yêu cầu
        $prompt = <<<EOT
You are a Vietnamese adult movie editor.

## Task:
1. Extract the movie code from the beginning of the title: "{$code}".
2. Translate the title to Vietnamese, keeping the movie code at the beginning.
3. Make the title shorter, clearer, and more erotic if possible — but still reflect the original meaning.
4. Then translate the description to Vietnamese (if it exists).

## Input:
Original Title: {$rawTitle}
Original Description: {$description}

## Output format:
Title: [Translated and shortened Vietnamese title, starting with code]
Description: [Translated Vietnamese description]

Begin:
EOT;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => "AIzaSyBDc4uN2r0Diav3_GHgn2ZKPETyL67Q5Xo", // hoặc thay bằng key trực tiếp nếu muốn
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        usleep(1000000); // 0.5s delay

        if (!$response->successful()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'message' => data_get($response->json(), 'error.message')
            ]);
            return null;
        }

        $output = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (!$output) return null;

        $result = [
            'title' => null,
            'description' => null,
        ];

        if (preg_match('/Title:\s*(.+)/i', $output, $matches)) {
            $result['title'] = trim($matches[1]);
        }

        if (preg_match('/Description:\s*(.+)/is', $output, $matches)) {
            $result['description'] = trim($matches[1]);
        }

        return $result;
    }
}

