<?php

namespace Database\Seeders;

use App\Models\HotelSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads the placeholder hero slides onto the public disk and records their
 * relative paths, so the Filament file field can show, reorder and delete them
 * like any upload. Existing files are reused, so re-seeding is safe.
 */
class HeroImageSeeder extends Seeder
{
    public const string DIRECTORY = 'hero-slideshow';

    /**
     * Unsplash photo IDs in slideshow order: lobby, pool, restaurant,
     * exterior, poolside terrace.
     *
     * @var array<int, string>
     */
    private const array PHOTO_IDS = [
        'photo-1566073771259-6a8506099945',
        'photo-1571896349842-33c89424de2d',
        'photo-1517248135467-4c7edcad34c4',
        'photo-1542314831-068cd1dbfeeb',
        'photo-1520250497591-112f2f40a3f4',
    ];

    public function run(): void
    {
        $paths = [];

        foreach (self::PHOTO_IDS as $index => $photoId) {
            $path = sprintf('%s/mock-%d.jpg', self::DIRECTORY, $index + 1);

            if (! Storage::disk('public')->exists($path)) {
                $response = Http::timeout(60)
                    ->get("https://images.unsplash.com/{$photoId}?auto=format&fit=crop&w=2200&q=80")
                    ->throw();

                Storage::disk('public')->put($path, $response->body());
            }

            $paths[] = $path;
        }

        HotelSetting::query()->updateOrCreate(['key' => 'hero_images'], ['value' => $paths]);
    }
}
