<?php

namespace Database\Seeders;

use App\Models\HotelSetting;
use App\Services\ImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\SplFileInfo as FinderFileInfo;
use Symfony\Component\HttpFoundation\File\File as HttpFile;

/**
 * Converts the hotel's own hero slideshow photos — real local files in
 * public/seedImgs/hero/, never a live download from a seed — into WebP
 * and records their paths, the same way an admin's own upload through
 * Site Settings would (see ManageHotelSettings' hero_images FileUpload:
 * same 'hero-slideshow' directory, same 1920px width). Seeding this app
 * never needs outbound network access.
 */
class HeroImageSeeder extends Seeder
{
    private const string PHOTOS_ROOT = 'seedImgs/hero';

    private const string STORAGE_DIRECTORY = 'hero-slideshow';

    public function run(ImageOptimizer $images): void
    {
        $root = public_path(self::PHOTOS_ROOT);

        if (! File::isDirectory($root)) {
            $this->command?->warn('No hero slideshow photos found at public/'.self::PHOTOS_ROOT.' — skipping.');

            return;
        }

        // Reseeding (without a full migrate:fresh) must not pile up the
        // previous run's webp files — there's no stable natural key to
        // reuse, since every conversion gets its own fresh ULID.
        Storage::disk('public')->deleteDirectory(self::STORAGE_DIRECTORY);

        $paths = collect(File::files($root))
            ->sortBy(fn (FinderFileInfo $file): string => $file->getFilename())
            ->map(fn (FinderFileInfo $file): string => $images->storeAsWebp(
                new HttpFile($file->getPathname()),
                self::STORAGE_DIRECTORY,
                1920,
                $file->getFilenameWithoutExtension(),
            ))
            ->values()
            ->all();

        HotelSetting::query()->updateOrCreate(['key' => 'hero_images'], ['value' => $paths]);
    }
}
