<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherAir extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'district_id',
        'ward_id',
        'time',
        'aqi',
        'pm10',
        'pm2_5',
        'so2',
        'no2',
        'o3',
        'co',
        'nh3',
        'no',
        'category', 
    ];
}
