<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'type',
        'label',
        'video',
        'active',
        'sort'
    ];
}
