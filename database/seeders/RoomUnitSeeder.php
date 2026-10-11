<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomUnit;
use App\Services\ImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Generates the specific, numbered rooms from the hotel's own real photos
 * in public/seedImgs/hotel_imgs — not mock placeholders. Each top-level
 * folder there is one room type's real gallery; each folder inside it is
 * one specific physical room, named after the room's own number (the "№"
 * prefix and any letter the hotel uses to tell two same-numbered rooms in
 * different wings apart, e.g. "№29А", are kept exactly as found). Every
 * JPG inside a room's folder is re-encoded to WebP and stored on the
 * public disk, the same way an admin's own upload through RoomUnitForm
 * would be — nothing here is a separate, parallel code path.
 */
class RoomUnitSeeder extends Seeder
{
    private const string PHOTOS_ROOT = 'seedImgs/hotel_imgs';

    /**
     * Matched by the real folder name the hotel actually provided — not
     * parsed or guessed from a naming convention, since "1k standar" (sic)
     * and similar are simply what the folders are called, typos included.
     *
     * @var array<string, string>
     */
    private const array CATEGORY_FOLDERS = [
        '1k standar' => 'standart-odnokomnatnyj-2-h-mestnyj',
        '1k standart_seporated' => 'standart-odnokomnatnyj-2-h-mestnyj-razdelnye-krovati',
        '1k widened' => 'rasshirennyj-odnokomnatnyj-3-h-mestnyj',
        '2k family' => 'semejnyj-dvuhkomnatnyj-3-h-mestnyj',
        '2k suite' => 'lyuks-dvuhkomnatnyj-3-h-mestnyj',
    ];

    public function run(ImageOptimizer $images): void
    {
        $root = public_path(self::PHOTOS_ROOT);

        if (! is_dir($root)) {
            $this->command?->warn('No real room photos found at public/'.self::PHOTOS_ROOT.' — skipping specific-room seeding.');

            return;
        }

        foreach (self::CATEGORY_FOLDERS as $folder => $roomSlug) {
            $categoryPath = "{$root}/{$folder}";

            if (! is_dir($categoryPath)) {
                $this->command?->warn("Expected photo folder \"{$folder}\" not found — skipping {$roomSlug}.");

                continue;
            }

            $room = Room::query()->where('slug', $roomSlug)->first();

            if ($room === null) {
                continue;
            }

            $signaturePhoto = null;

            foreach ($this->unitFolders($categoryPath) as $index => $unitFolder) {
                // "№29А" -> "29А": the room's real number, letter suffix
                // kept as-is since that's how the hotel itself tells two
                // same-numbered rooms in different wings apart.
                $number = Str::after($unitFolder, '№');
                $webpPaths = $this->jpgFiles("{$categoryPath}/{$unitFolder}")
                    ->map(fn (string $path): string => $images->storeAsWebp(
                        new File($path),
                        'rooms',
                        1200,
                        pathinfo($path, PATHINFO_FILENAME),
                    ))
                    ->all();

                // The room type's own single showcase photo for the homepage
                // carousel — the first living-area shot found across its
                // units, never one of their "bath.jpg" bathroom photos.
                if ($signaturePhoto === null) {
                    $signaturePhoto = collect($webpPaths)->first(
                        fn (string $path): bool => ! Str::contains($path, 'bath', ignoreCase: true),
                    );
                }

                RoomUnit::updateOrCreate(
                    ['room_id' => $room->id, 'number' => $number],
                    [
                        'images' => $webpPaths,
                        // Nothing in a folder of plain photos says which
                        // specific room has a balcony or which exact
                        // furniture tags apply — left for the admin to set
                        // by hand; an empty array here already falls back
                        // to the room type's own (see RoomUnitResource).
                        'amenities' => [],
                        'has_balcony' => false,
                        'sort_order' => $index,
                        'is_active' => true,
                    ],
                );
            }

            if ($signaturePhoto !== null) {
                $room->update(['images' => [$signaturePhoto]]);
            }
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function unitFolders(string $categoryPath): Collection
    {
        return collect(scandir($categoryPath) ?: [])
            ->reject(fn (string $name): bool => in_array($name, ['.', '..'], true))
            ->filter(fn (string $name): bool => is_dir("{$categoryPath}/{$name}"))
            ->sort()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function jpgFiles(string $unitPath): Collection
    {
        return collect(scandir($unitPath) ?: [])
            ->filter(fn (string $name): bool => Str::endsWith(Str::lower($name), ['.jpg', '.jpeg']))
            ->sort()
            ->map(fn (string $name): string => "{$unitPath}/{$name}")
            ->values();
    }
}
