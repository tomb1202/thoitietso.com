<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function dowloadFlags(){
        $countries = Country::all(); // Lấy tất cả quốc gia từ bảng countries
        $savePath = public_path('flags'); // Đường dẫn lưu file

        if (!file_exists($savePath)) {
            mkdir($savePath, 0755, true); // Tạo thư mục nếu chưa tồn tại
        }

        foreach ($countries as $country) {
            $countryCode = strtoupper($country->code); // Code viết hoa
            $url = "https://flagsapi.com/{$countryCode}/flat/64.png";
            $fileName = strtolower($countryCode) . '.png';
            $filePath = "{$savePath}/{$fileName}";

            try {
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    file_put_contents($filePath, $response->body());
                    // $this->info("Downloaded: {$fileName}");
                } else {
                    Log::error('Dowload flag error', ['code' => $country->code]);
                }
            } catch (\Exception $e) {
                $this->error("Error downloading {$fileName}: {$e->getMessage()}");
            }
        }

        $this->info('All flags downloaded!');
    }
}
