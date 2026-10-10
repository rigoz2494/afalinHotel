<?php

namespace App\Http\Resources;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin GalleryImage
 */
class GalleryImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'src' => url(Storage::disk('public')->url($this->path)),
            'alt' => $this->alt,
            'tall' => $this->tall,
        ];
    }
}
