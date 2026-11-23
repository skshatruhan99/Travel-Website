<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'minimum_amount', 'usage_limit',
        'used_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function calculateDiscount(float $amount): float
    {
        if ($amount < (float) $this->minimum_amount) {
            return 0;
        }

        $discount = $this->type === 'fixed'
            ? min((float) $this->value, (float) $amount)
            : ((float) $this->value / 100) * (float) $amount;

        return round(max(0, $discount), 2);
    }

    public function canBeUsedForAmount(float $amount): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($amount < (float) $this->minimum_amount) {
            return false;
        }

        return true;
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}
