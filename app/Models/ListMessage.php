<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListMessage extends Model
{
    protected $fillable = [
        'movie_list_id',
        'user_id',
        'message',
        'type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function movieList(): BelongsTo
    {
        return $this->belongsTo(MovieList::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
