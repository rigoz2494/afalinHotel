<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use App\Services\ImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo as FinderFileInfo;
use Symfony\Component\HttpFoundation\File\File as HttpFile;

/**
 * Populates the site-wide photo gallery (About section) from the hotel's
 * own real photos in public/seedImgs/hotel_imgs/gallery/ — not mock
 * placeholders. Every image file directly inside that folder becomes one
 * gallery entry; there are no room-numbered subfolders to walk here,
 * unlike RoomUnitSeeder.
 */
class GalleryImageSeeder extends Seeder
{
    private const string PHOTOS_ROOT = 'seedImgs/hotel_imgs/gallery';

    private const string STORAGE_DIRECTORY = 'gallery';

    /**
     * Matched case-insensitively — see imageFiles() below — so a camera or
     * phone export's ".JPG"/".PNG" is picked up exactly like ".jpg"/".png".
     *
     * @var array<int, string>
     */
    private const array IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function run(ImageOptimizer $images): void
    {
        $root = public_path(self::PHOTOS_ROOT);

        if (! File::isDirectory($root)) {
            $this->command?->warn('No real gallery photos found at public/'.self::PHOTOS_ROOT.' — skipping gallery seeding.');

            return;
        }

        $files = $this->imageFiles($root);

        if ($files === []) {
            $this->command?->warn('public/'.self::PHOTOS_ROOT.' exists but has no .jpg/.jpeg/.png files — skipping gallery seeding.');

            return;
        }

        // Reseeding (without a full migrate:fresh) must not pile up
        // duplicate rows or leave the previous run's webp files orphaned
        // on disk — there's no stable natural key to updateOrCreate on,
        // since every conversion gets its own fresh ULID.
        GalleryImage::query()->delete();
        Storage::disk('public')->deleteDirectory(self::STORAGE_DIRECTORY);
        File::ensureDirectoryExists(Storage::disk('public')->path(self::STORAGE_DIRECTORY));

        $processed = 0;

        foreach ($files as $index => $file) {
            $this->command?->info("Processing general gallery photo: {$file->getFilename()}");

            // 'tall' isn't set here — GalleryImage derives it itself from
            // the stored photo's real orientation whenever path changes.
            GalleryImage::create([
                'path' => $images->storeAsWebp(
                    new HttpFile($file->getPathname()),
                    self::STORAGE_DIRECTORY,
                    1800,
                    $file->getFilenameWithoutExtension(),
                ),
                'alt' => $this->humanize($file->getFilenameWithoutExtension()),
                'sort_order' => $index,
                'is_active' => true,
            ]);

            $processed++;
        }

        $this->command?->info("Gallery seeding complete: {$processed} photo(s) converted to WebP and imported.");
    }

    /**
     * @return array<int, FinderFileInfo>
     */
    private function imageFiles(string $root): array
    {
        return collect(File::files($root))
            ->filter(fn (FinderFileInfo $file): bool => in_array(Str::lower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortBy(fn (FinderFileInfo $file): string => $file->getFilename())
            ->values()
            ->all();
    }

    /**
     * @return array{en: string, ru: null}
     */
    private function humanize(string $filename): array
    {
        $label = Str::of($filename)
            ->replace(['-', '_'], ' ')
            ->squish()
            ->title()
            ->toString();

        // There's no Russian translation to derive from a filename — left
        // null, same as a freshly-created record the admin hasn't filled
        // in yet, rather than guessing or duplicating the English label.
        return ['en' => $label, 'ru' => null];
    }
}
