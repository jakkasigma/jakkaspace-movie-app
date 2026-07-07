<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionPromo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'show_popup' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscription_promo_user')
            ->withPivot(['plan_id', 'original_price', 'discounted_price', 'code_used', 'applied_at'])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function canUseBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->doesntExist();
    }

    public function applyPrice(int $originalPrice): int
    {
        if ($this->type === 'percent') {
            return max(0, (int) round($originalPrice * (1 - $this->value / 100)));
        }

        return max(0, $originalPrice - $this->value);
    }
}
