<?php

namespace App\Models;

use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlan extends Model
{
    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_recommended' => 'boolean',
        ];
    }

    protected $guarded = [];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order');
    }

    public function tierLabel(): string
    {
        return $this->tier === 'plus_plus' ? 'Plus+' : 'Plus';
    }

    public function priceFormatted(): string
    {
        return 'Rp'.number_format($this->price, 0, ',', '.');
    }

    public function periodLabel(): string
    {
        return match ($this->duration_days) {
            30 => 'bulan',
            90 => '3 bulan',
            180 => '6 bulan',
            365 => 'tahun',
            default => $this->duration_days.' hari',
        };
    }

    public function hasActiveAutoPromo(): bool
    {
        return $this->promos()->where('is_active', true)->get()->contains(fn ($promo) => $promo->isValid());
    }

    public function discountedPrice(): ?int
    {
        $promo = $this->promos()->where('is_active', true)->get()->first(fn ($promo) => $promo->isValid());

        return $promo?->applyPrice($this->price);
    }

    public function promos()
    {
        return $this->hasMany(SubscriptionPromo::class, 'plan_id');
    }
}
