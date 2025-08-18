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
            $table->unsignedBigInteger('district_id')->comment('ID quận/huyện');

            $table->dateTime('time')->comment('Thời gian đo');
            $table->integer('aqi')->nullable()->comment('Chỉ số chất lượng không khí (AQI)');
            $table->float('pm10')->nullable()->comment('PM10 (µg/m³)');
            $table->float('pm2_5')->nullable()->comment('PM2.5 (µg/m³)');
            $table->float('so2')->nullable()->comment('SO₂ (ppb)');
            $table->float('no2')->nullable()->comment('NO₂ (ppb)');
            $table->float('o3')->nullable()->comment('O₃ (ppb)');
            $table->float('co')->nullable()->comment('CO (ppm)');
            $table->string('category')->nullable()->comment('Mức đánh giá chất lượng không khí');

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
