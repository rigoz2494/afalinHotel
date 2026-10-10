<?php

namespace App\Models;

use Database\Factories\GalleryImageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * One real photo in the site-wide gallery shown on the About section —
 * lobby, pool, exterior, ... — not tied to a specific room.
 */
class GalleryImage extends Model
{
    /** @use HasFactory<GalleryImageFactory> */
    use HasFactory;

    protected $fillable = ['path', 'alt', 'sort_order', 'is_active'];

    /**
     * `tall` is never admin-entered — whether a photo spans two grid rows
     * on the About section's masonry layout is derived from the real,
     * already-stored photo's own orientation, the moment its path changes.
     * This runs the same way whether the record came from the Filament
     * admin's upload or from GalleryImageSeeder.
     */
    protected static function booted(): void
    {
        static::saving(function (GalleryImage $image): void {
            if (! $image->isDirty('path') || ! Storage::disk('public')->exists($image->path)) {
                return;
            }

            $size = getimagesize(Storage::disk('public')->path($image->path));
            $image->tall = $size !== false && $size[1] > $size[0];
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alt' => 'array',
            'tall' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<GalleryImage>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<GalleryImage>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
