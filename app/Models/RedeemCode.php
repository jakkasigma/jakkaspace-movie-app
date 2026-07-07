<?php

namespace App\Models;

use Database\Factories\RedeemCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RedeemCode extends Model
{
    /** @use HasFactory<RedeemCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'tier',
        'duration_days',
        'discount_type',
        'discount_value',
        'plan_id',
        'popup_title',
        'popup_message',
        'max_uses',
        'used_count',
        'is_active',
        'created_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'duration_days' => 'integer',
            'discount_value' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function redeemers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'redeem_code_user')
            ->withPivot('redeemed_at');
    }

    public function isFreeAccess(): bool
    {
        return $this->type === 'free_access';
    }

    public function isPromo(): bool
    {
        return $this->type === 'promo';
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at !== null && now()->greaterThan($this->expires_at)) {
            return false;
        }

        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function applyPrice(int $originalPrice): int
    {
        if ($this->discount_type === 'percent') {
            return max(0, (int) round($originalPrice * (1 - $this->discount_value / 100)));
        }

        return max(0, $originalPrice - $this->discount_value);
    }
}
