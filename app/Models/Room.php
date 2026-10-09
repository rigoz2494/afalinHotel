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

    /**
     * The fixed vocabulary of amenity tags the admin can pick from, and the
     * only values `amenities` is ever expected to hold — each maps to one
     * minimalist icon and a bilingual label on the frontend (see
     * resources/js/composables/useLocale.ts and RoomsSection.vue).
     *
     * @var array<int, string>
     */
    public const array AMENITY_TAGS = [
        'double_bed', 'twin_beds', 'sofa', 'armchair', 'table', 'nightstand',
        'chairs', 'wardrobe', 'hanger', 'tv', 'ac', 'fridge', 'safe_box',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'capacity',
        'bed_type',
        'amenities',
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
            'name' => 'array',
            'description' => 'array',
            'amenities' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Optional, room-specific price or discount override for a given period.
     * The room's regular rate for that period otherwise comes from
     * PricingPeriod's modifier_percentage; see PricingService.
     *
     * @return HasMany<RoomPrice, $this>
     */
    public function discountOverrides(): HasMany
    {
        return $this->hasMany(RoomPrice::class);
    }

    /**
     * The specific, numbered physical rooms of this type (e.g. "101",
     * "102"), each with its own photos and exact amenities — see RoomUnit.
     *
     * @return HasMany<RoomUnit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(RoomUnit::class);
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
