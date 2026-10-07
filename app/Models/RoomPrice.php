<?php

namespace App\Models;

use Database\Factories\RoomPriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An optional, room-specific promotional discount for one pricing period. The
 * room's regular rate for that period comes from PricingPeriod's
 * modifier_percentage; this only ever stacks an additional discount on top.
 */
class RoomPrice extends Model
{
    /** @use HasFactory<RoomPriceFactory> */
    use HasFactory;

    protected $fillable = ['room_id', 'pricing_period_id', 'discount_percentage'];

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
