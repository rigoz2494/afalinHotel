<?php

namespace App\Models;

use Database\Factories\RoomUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * A specific, numbered physical room belonging to a Room ("Room Type").
 * Its own photos and amenities — not the type's — are what the guest sees
 * once they pick this unit in the Section 2 "View Available Rooms" modal.
 *
 * @property array<int, string>|null $images
 * @property string|null $video_path
 */
class RoomUnit extends Model
{
    /** @use HasFactory<RoomUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'number',
        'images',
        'video_path',
        'amenities',
        'has_balcony',
        'sort_order',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::updated(function (RoomUnit $unit): void {
            if ($unit->wasChanged('video_path')) {
                self::deleteUnusedVideoAfterCommit($unit->getRawOriginal('video_path'));
            }
        });

        static::deleted(fn (RoomUnit $unit) => self::deleteUnusedVideoAfterCommit($unit->video_path));
    }

    private static function deleteUnusedVideoAfterCommit(?string $path): void
    {
        if ($path === null || dirname($path) !== 'room-unit-videos') {
            return;
        }

        DB::afterCommit(function () use ($path): void {
            if (! self::query()->where('video_path', $path)->exists()) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'amenities' => 'array',
            'has_balcony' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @param  Builder<RoomUnit>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<RoomUnit>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('number');
    }
}
