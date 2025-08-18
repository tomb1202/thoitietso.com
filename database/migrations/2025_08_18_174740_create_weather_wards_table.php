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
        Schema::create('weather_wards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ward_id')->index()->comment('ID phường/xã');
            $table->dateTime('forecast_time')->comment('Thời điểm dự báo');

            $table->float('temp_min')->nullable()->comment('Nhiệt độ thấp nhất (°C)');
            $table->float('temp_max')->nullable()->comment('Nhiệt độ cao nhất (°C)');
            $table->float('humidity')->nullable()->comment('Độ ẩm (%)');
            $table->float('uv_index')->nullable()->comment('Chỉ số UV');
            $table->float('wind_speed')->nullable()->comment('Tốc độ gió (km/h)');
            $table->float('wind_gust')->nullable()->comment('Gió giật (km/h)');
            $table->float('precip_mm')->nullable()->comment('Lượng mưa (mm)');
            $table->float('visibility_km')->nullable()->comment('Tầm nhìn (km)');
            $table->float('dew_point')->nullable()->comment('Điểm sương (°C)');
            $table->string('description')->nullable()->comment('Mô tả thời tiết');
            $table->string('icon')->nullable()->comment('Biểu tượng thời tiết');

            $table->time('sunrise')->nullable()->comment('Giờ mặt trời mọc');
            $table->time('sunset')->nullable()->comment('Giờ mặt trời lặn');
            $table->dateTime('run_at')->nullable()->comment('Thời điểm cào dữ liệu');
            $table->dateTime('target_time')->nullable()->comment('Thời điểm dữ liệu hướng tới');
            $table->string('source')->nullable()->comment('Nguồn dữ liệu');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_wards');
    }
};
