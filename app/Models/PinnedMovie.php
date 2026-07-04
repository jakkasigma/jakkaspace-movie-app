<?php

namespace App\Models;

use Database\Factories\PinnedMovieFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinnedMovie extends Model
{
    /** @use HasFactory<PinnedMovieFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tmdb_id',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
