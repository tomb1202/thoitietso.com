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
        Schema::create('weather', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('province_id')->comment('ID tỉnh/thành');
            $table->unsignedBigInteger('district_id')->comment('ID quận/huyện');

            $table->dateTime('forecast_time')->index()->comment('Thời điểm dự báo');

            $table->float('temp_min')->nullable()->comment('Nhiệt độ thấp nhất');
            $table->float('temp_max')->nullable()->comment('Nhiệt độ cao nhất');
            $table->float('humidity')->nullable()->comment('Độ ẩm (%)');
            $table->float('uv_index')->nullable()->comment('Chỉ số UV');
            $table->float('wind_speed')->nullable()->comment('Tốc độ gió (km/h)');
            $table->float('wind_gust')->nullable()->comment('Gió giật (km/h)');
            $table->float('precip_mm')->nullable()->comment('Lượng mưa (mm)');
            $table->float('visibility_km')->nullable()->comment('Tầm nhìn (km)');
            $table->float('dew_point')->nullable()->comment('Điểm ngưng (°C)');

            $table->string('description')->nullable()->comment('Mô tả thời tiết');
            $table->string('icon')->nullable()->comment('Biểu tượng thời tiết');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_hourlies');
    }
};
