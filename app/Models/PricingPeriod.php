<?php

namespace App\Models;

use Database\Factories\PricingPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A season/month column the pricing table can price a room against (e.g.
 * "June", "Low season"). Admin-managed so the table's columns, and which
 * ones are currently offered, never require editing a Vue template.
 *
 * `modifier_percentage` is the season's rate adjustment relative to each
 * room's own base price (e.g. -15 for a low-season discount, +20 for peak
 * season). It replaces entering an absolute price for every room in every
 * period: a room's regular rate for this period is always
 * `base_price * (1 + modifier_percentage / 100)`.
 */
class PricingPeriod extends Model
{
    /** @use HasFactory<PricingPeriodFactory> */
    use HasFactory;

    protected $fillable = ['name', 'modifier_percentage', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modifier_percentage' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<RoomPrice, $this>
     */
    public function roomPrices(): HasMany
    {
        return $this->hasMany(RoomPrice::class);
    }

    /**
     * @param  Builder<PricingPeriod>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<PricingPeriod>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
