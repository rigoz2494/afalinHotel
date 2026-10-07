<?php

namespace App\Http\Resources;

use App\Models\HotelSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property array<string, mixed> $resource
 */
class HotelSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Absolute, not relative: these are shared as og:image previews on
        // Telegram/WhatsApp, which fetch the image directly and can't
        // resolve a path relative to the page.
        $heroImages = collect($this->resource['hero_images'] ?? [])
            ->map(fn (string $path): string => str_starts_with($path, 'http') ? $path : url(Storage::disk('public')->url($path)))
            ->values()
            ->all();

        return [
            // Both "en" and "ru" are always sent, like section_headings
            // below, so the guest's chosen language always shows the hotel's
            // real name, never a blank or a generic placeholder.
            'hotel_name' => HotelSetting::defaultedHotelName($this->resource['hotel_name'] ?? null),
            'tagline' => $this->resource['tagline'] ?? null,
            // {en, ru} once any text is set, null (hidden) otherwise — see
            // HotelSetting::normalizedPromoBanner().
            'promo_banner' => HotelSetting::normalizedPromoBanner($this->resource['promo_banner'] ?? null),
            'hero_images' => $heroImages,
            'contacts' => $this->resource['contacts'] ?? [],
            // Any section/locale the admin hasn't set yet falls back to a
            // sensible default, so the page never renders a blank title. Both
            // "en" and "ru" are always sent; the client picks one instantly.
            'section_headings' => HotelSetting::defaultedSectionHeadings($this->resource['section_headings'] ?? []),
        ];
    }
}
