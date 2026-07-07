<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tmdb_id',
        'rating',
        'body',
        'has_spoiler',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReviewComment::class);
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'has_spoiler' => 'boolean',
        ];
    }

    public function getParsedBodyAttribute(): string
    {
        // Escape HTML first for security
        $escaped = e($this->body);

        // Find and replace @mentions with links
        $parsed = preg_replace_callback(
            '/\B@(\w+)\b/',
            function ($matches) {
                $username = $matches[1];
                $url = route('profile.show', $username);

                return '<a href="'.$url.'" class="mention">@'.$username.'</a>';
            },
            $escaped
        );

        return $parsed ?? $escaped;
    }
}
