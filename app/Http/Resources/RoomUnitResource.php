<?php

namespace App\Http\Resources;

use App\Models\RoomUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin RoomUnit
 */
class RoomUnitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Falls back to the room type's own photos/tags when this specific
        // room hasn't been given its own — the type's are still accurate,
        // just not as specific as this room's own would be.
        $images = collect($this->images ?: $this->room->images ?? [])
            ->map(fn (string $path): string => str_starts_with($path, 'http') ? $path : url(Storage::disk('public')->url($path)))
            ->values()
            ->all();

        return [
            'id' => $this->id,
            'number' => $this->number,
            'images' => $images,
            'amenities' => $this->amenities ?: ($this->room->amenities ?? []),
            'has_balcony' => $this->has_balcony,
        ];
    }
}
