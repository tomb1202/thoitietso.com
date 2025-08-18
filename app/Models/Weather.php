<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'district_id',
        'forecast_time',
        'temp_min',
        'temp_max',
        'humidity',
        'uv_index',
        'wind_speed',
        'wind_gust',
        'precip_mm',
        'visibility_km',
        'dew_point',
        'description',
        'icon',
    ];
}
