<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();

        $settings = [
            'logo'              => '',
            'favicon'           => '',
            'title'             => 'Dự báo thời tiết hằng ngày – Nhanh chóng, Chính xác, Miễn phí',
            'site_name'         => 'ThoiTiet24h',
            'version'           => '1.0',
            'theme_color'       => '#0a74da',
            'google_analytics'  => '',
            'mail'              => 'contact@thoitiet24h.vn',
            'description'       => 'Cập nhật dự báo thời tiết hằng ngày cho toàn quốc: nhiệt độ, mưa, gió, độ ẩm, chỉ số UV. Thông tin chính xác, nhanh chóng và miễn phí.',
            'introduce'         => 'ThoiTiet24h cung cấp thông tin dự báo thời tiết chi tiết cho từng tỉnh thành trên cả nước.
        Người dùng có thể xem dự báo theo giờ, 5 ngày, 30 ngày.
        Dữ liệu được cập nhật liên tục, đảm bảo độ chính xác và tin cậy.',
            'copyright'         => '© 2025 ThoiTiet24h. All rights reserved.',
            'notification'      => 'ℹ️ Dự báo thời tiết hằng ngày – Nhanh chóng, chính xác, miễn phí cho mọi khu vực trên toàn quốc!',
            'introduct_footer'  => 'ThoiTiet24h chỉ cung cấp thông tin dự báo. Chúng tôi không chịu trách nhiệm về thiệt hại do sử dụng dữ liệu không chính xác hoặc thay đổi đột xuất từ thiên nhiên.',
        ];

        foreach ($settings as $key => $setting) {
            Setting::create([
                'key'   => $key,
                'value' => $setting,
            ]);
        }
    }
}
