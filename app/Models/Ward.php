<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'url'
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function province()
    {
        return $this->hasOneThrough(Province::class, District::class, 'id', 'id', 'district_id', 'province_id');
    }
}
