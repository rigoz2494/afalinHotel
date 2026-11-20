<?php

namespace Tests\Feature;

use App\Models\HotelSetting;
use Database\Seeders\HeroImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * HeroImageSeeder reads the hotel's own real photos from
 * public/seedImgs/hero/afalina, so every test here fakes the public disk — the
 * actual WebP conversion pipeline (ImageOptimizer) is exercised for real,
 * but nothing is written to the real storage directory during a test run.
 */
class HeroImageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_converts_local_photos_to_webp_and_saves_their_paths(): void
    {
        Storage::fake('public');

        $this->seed(HeroImageSeeder::class);

        $paths = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->assertCount(2, $paths);
        $this->assertStringStartsWith('hero-slideshow/00-pool-and-hotel-', $paths[0]);
        $this->assertStringStartsWith('hero-slideshow/01-hotel-and-seafront-', $paths[1]);

        foreach ($paths as $path) {
            $this->assertStringStartsWith('hero-slideshow/', $path);
            $this->assertStringEndsWith('.webp', $path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_reseeding_preserves_existing_files(): void
    {
        Storage::fake('public');

        $this->seed(HeroImageSeeder::class);
        $firstRunPaths = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->seed(HeroImageSeeder::class);
        $secondRunPaths = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->assertSame($firstRunPaths, $secondRunPaths);

        foreach ($firstRunPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }
}
