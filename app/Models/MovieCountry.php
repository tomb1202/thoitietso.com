<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieCountry extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'country_id'
    ];
}
