<?php

namespace App\Models;

use Database\Factories\ReviewCommentFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewComment extends Model
{
    /** @use HasFactory<ReviewCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_id',
        'parent_id',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ReviewComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReviewComment::class, 'parent_id');
    }

    public function getAllReplies(): Collection
    {
        return $this->replies()->with('user')->get()->sortBy('created_at');
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
