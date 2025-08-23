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
        Schema::create('weather_airs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('province_id')->comment('ID tỉnh/thành');
            $table->unsignedBigInteger('district_id')->nullable()->comment('ID quận/huyện');
            $table->unsignedBigInteger('ward_id')->nullable()->comment('ID xã/phường');

            $table->dateTime('time')->comment('Thời gian đo');
            $table->integer('aqi')->nullable()->comment('Chỉ số chất lượng không khí');

            // Các thành phần hóa học
            $table->float('nh3')->nullable()->comment('Nồng độ NH3');
            $table->float('no')->nullable()->comment('Nồng độ NO');
            $table->float('no2')->nullable()->comment('Nồng độ NO2');
            $table->float('so2')->nullable()->comment('Nồng độ SO2');
            $table->float('pm2_5')->nullable()->comment('Nồng độ PM2.5');
            $table->float('pm10')->nullable()->comment('Nồng độ PM10');
            $table->float('co')->nullable()->comment('Nồng độ CO');
            $table->float('o3')->nullable()->comment('Nồng độ O3');
            $table->string('level')->nullable();

            // Thêm index nếu chưa có
            $table->index(['province_id']);
            $table->index(['district_id']);
            $table->index(['ward_id']);
            $table->index(['time']);

            // Thêm unique key để tránh trùng record cùng thời điểm
            $table->unique(
                ['province_id', 'district_id', 'ward_id', 'time'],
                'wx_air_unique'
            );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_airs');
    }
};
