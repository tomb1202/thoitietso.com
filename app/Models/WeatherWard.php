<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherWard extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
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
        'sunrise',
        'sunset',
        'run_at',
        'target_time',
        'source',
    ];
}
