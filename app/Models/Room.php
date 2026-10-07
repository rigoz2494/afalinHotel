<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'capacity',
        'bed_type',
        'has_tv',
        'has_air_conditioning',
        'furniture',
        'furniture_ru',
        'images',
        'base_price',
        'discount_percentage',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'furniture' => 'array',
            'furniture_ru' => 'array',
            'images' => 'array',
            'has_tv' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Optional, room-specific promotional discounts for a given period. These
     * stack on top of the period's own modifier_percentage; see PricingService.
     *
     * @return HasMany<RoomPrice, $this>
     */
    public function discountOverrides(): HasMany
    {
        return $this->hasMany(RoomPrice::class);
    }

    /**
     * @param  Builder<Room>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Room>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
