<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar',
        'avatar_url',
        'google_id',
        'is_private',
        'has_password',
        'subscription_tier',
        'subscribed_at',
        'expires_at',
        'theme_id',
        'is_admin',
        'is_banned',
        'banned_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function diaryEntries(): HasMany
    {
        return $this->hasMany(DiaryEntry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function watchHistories(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function movieLists(): HasMany
    {
        return $this->hasMany(MovieList::class);
    }

    public function listMemberships(): HasMany
    {
        return $this->hasMany(ListMember::class);
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function reviewLikes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    public function diaryLikes(): HasMany
    {
        return $this->hasMany(DiaryLike::class);
    }

    public function pinnedMovies(): HasMany
    {
        return $this->hasMany(PinnedMovie::class)->orderBy('sort_order');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isPlus(): bool
    {
        return in_array($this->subscription_tier, ['plus', 'plus_plus'], true)
            && $this->expires_at !== null
            && now()->lessThan($this->expires_at);
    }

    public function isPlusPlus(): bool
    {
        return $this->subscription_tier === 'plus_plus'
            && $this->expires_at !== null
            && now()->lessThan($this->expires_at);
    }

    public function maxLists(): int
    {
        if ($this->isPlusPlus()) {
            return 15;
        }
        if ($this->isPlus()) {
            return 7;
        }

        return 0;
    }

    public function maxPublicLists(): int
    {
        if ($this->isPlusPlus()) {
            return 8;
        }
        if ($this->isPlus()) {
            return 4;
        }

        return 0;
    }

    public function maxPrivateLists(): int
    {
        if ($this->isPlusPlus()) {
            return 7;
        }
        if ($this->isPlus()) {
            return 3;
        }

        return 0;
    }

    public function maxPinned(): int
    {
        if ($this->isPlusPlus()) {
            return 12;
        }

        return 6;
    }

    public function canUploadCover(): bool
    {
        return $this->isPlusPlus();
    }

    public function maxMoviesPerList(): int
    {
        if ($this->isPlusPlus()) {
            return -1;
        } // unlimited
        if ($this->isPlus()) {
            return 100;
        }

        return 0;
    }

    public function subscriptionTransactions(): HasMany
    {
        return $this->hasMany(SubscriptionTransaction::class);
    }

    public function subscriptionPromos(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPromo::class, 'subscription_promo_user')
            ->withPivot(['plan_id', 'original_price', 'discounted_price', 'code_used', 'applied_at']);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_members')
            ->withPivot('joined_at')
            ->orderByDesc('updated_at');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_private' => 'boolean',
            'has_password' => 'boolean',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'subscription_tier' => 'string',
            'subscribed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
