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
            // {en, ru} once any text is set, null otherwise — see
            // HotelSetting::normalizedTagline().
            'tagline' => HotelSetting::normalizedTagline($this->resource['tagline'] ?? null),
            // {en, ru} once any text is set, null (hidden) otherwise — see
            // HotelSetting::normalizedPromoBanner().
            'promo_banner' => HotelSetting::normalizedPromoBanner($this->resource['promo_banner'] ?? null),
            'hero_images' => $heroImages,
            'contacts' => $this->resource['contacts'] ?? [],
            // Any section/locale the admin hasn't set yet falls back to a
            // sensible default, so the page never renders a blank title. Both
            // "en" and "ru" are always sent; the client picks one instantly.
            'section_headings' => HotelSetting::defaultedSectionHeadings($this->resource['section_headings'] ?? []),
            // The three About Us marketing badges (label + value, each
            // bilingual) — see HotelSetting::defaultedAboutStats().
            'about_stats' => HotelSetting::defaultedAboutStats($this->resource['about_stats'] ?? []),
            // The About Us section's own story text, both languages always
            // sent — see HotelSetting::defaultedAboutStory().
            'about_story' => HotelSetting::defaultedAboutStory($this->resource['about_story'] ?? null),
            // The preloader's playful status phrases — see
            // HotelSetting::defaultedPreloaderPhrases().
            'preloader_phrases' => HotelSetting::defaultedPreloaderPhrases($this->resource['preloader_phrases'] ?? null),
            // 'multilingual' (default), 'ru_only' or 'en_only' — see
            // CallbackRequestService::resolveCurrency() for the matching
            // server-side currency lock.
            'locale_mode' => $this->resource['locale_mode'] ?? 'multilingual',
        ];
    }
}
