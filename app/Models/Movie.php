<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'url',
        'title',
        'title_en',
        'slug',
        'description',
        'year',
        'duration',
        'poster',
        'thumbnail',
        'poster_path',
        'thumb_path',
        'trailer',
        'quality',
        'language',
        'view',
        'imdb',
        'total_episode',
        'chieurap',
        'created_at_api',
        'updated_at_api',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_hot',
        'hidden',
        'type',
        'status',
        'is_trending',
        'is_coming',
        'is_crawl'
    ];


    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genres');
    }

    public function tags()
    {
        return $this->belongsToMany(Genre::class, 'movie_tags');
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'movie_countries');
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'movie_actors', 'movie_id', 'actor_id');
    }

    public function directors()
    {
        return $this->belongsToMany(Director::class, 'movie_directors', 'movie_id', 'director_id');
    }

    public function favoredByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function sources()
    {
        return $this->hasMany(MovieSource::class);
    }
}
