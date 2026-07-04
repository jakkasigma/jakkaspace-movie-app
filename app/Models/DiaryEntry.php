<?php

namespace App\Models;

use Database\Factories\DiaryEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiaryEntry extends Model
{
    /** @use HasFactory<DiaryEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tmdb_id',
        'watched_at',
        'notes',
        'mood',
        'is_rewatch',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(DiaryLike::class);
    }

    protected function casts(): array
    {
        return [
            'watched_at' => 'date',
            'is_rewatch' => 'boolean',
        ];
    }
}
