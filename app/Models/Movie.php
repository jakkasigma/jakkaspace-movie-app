<?php

namespace App\Models;

use Database\Factories\MovieFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    /** @use HasFactory<MovieFactory> */
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'title',
        'original_title',
        'poster_path',
        'backdrop_path',
        'release_date',
    ];

    public function diaryEntries(): HasMany
    {
        return $this->hasMany(DiaryEntry::class, 'tmdb_id', 'tmdb_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tmdb_id', 'tmdb_id');
    }

    public function watchHistories(): HasMany
    {
        return $this->hasMany(WatchHistory::class, 'tmdb_id', 'tmdb_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'tmdb_id', 'tmdb_id');
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class, 'tmdb_id', 'tmdb_id');
    }

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }
}
