<?php

namespace App\Models;

use Database\Factories\ListMovieFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListMovie extends Model
{
    /** @use HasFactory<ListMovieFactory> */
    use HasFactory;

    protected $fillable = [
        'movie_list_id',
        'tmdb_id',
        'sort_order',
    ];

    public function movieList(): BelongsTo
    {
        return $this->belongsTo(MovieList::class);
    }
}
