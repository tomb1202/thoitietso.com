<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'url'
    ];

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
