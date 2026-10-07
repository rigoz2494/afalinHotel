<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Room
 */
class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            // Absolute, not relative: used as og:image on each room's own
            // sitemap URL, which platforms like Telegram fetch directly.
            'images' => collect($this->images ?? [])
                ->map(fn (string $path): string => str_starts_with($path, 'http') ? $path : url(Storage::disk('public')->url($path)))
                ->values()
                ->all(),
            'base_price' => $this->base_price,
            'amenities' => [
                'capacity' => $this->capacity,
                'bed_type' => $this->bed_type,
                'furniture' => $this->furniture,
                // Nullable: the client falls back to the English list above
                // when this hasn't been set (not seeded, or admin-added
                // without a translation yet), the same way faqs do.
                'furniture_ru' => $this->furniture_ru,
                'has_tv' => $this->has_tv,
                'has_air_conditioning' => $this->has_air_conditioning,
            ],
        ];
    }
}
