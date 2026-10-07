<?php

namespace App\Models;

use Database\Factories\RoomPriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An optional, room-specific override for one pricing period: either a
 * promotional discount stacked on top of PricingPeriod's modifier_percentage,
 * or — when the real rate doesn't fit that formula closely enough —  a
 * literal `price_override` that replaces the computed price outright.
 */
class RoomPrice extends Model
{
    /** @use HasFactory<RoomPriceFactory> */
    use HasFactory;

    protected $fillable = ['room_id', 'pricing_period_id', 'discount_percentage', 'price_override'];

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return BelongsTo<PricingPeriod, $this>
     */
    public function pricingPeriod(): BelongsTo
    {
        return $this->belongsTo(PricingPeriod::class);
    }
}
