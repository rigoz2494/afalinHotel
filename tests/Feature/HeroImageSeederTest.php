<?php

namespace Tests\Feature;

use App\Models\HotelSetting;
use Database\Seeders\HeroImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * HeroImageSeeder reads the hotel's own real photos from
 * public/seedImgs/hero, so every test here fakes the public disk — the
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

        $this->assertCount(5, $paths);

        foreach ($paths as $path) {
            $this->assertStringStartsWith('hero-slideshow/', $path);
            $this->assertStringEndsWith('.webp', $path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_reseeding_replaces_the_previous_runs_files_instead_of_piling_up(): void
    {
        Storage::fake('public');

        $this->seed(HeroImageSeeder::class);
        $firstRunPaths = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->seed(HeroImageSeeder::class);
        $secondRunPaths = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->assertCount(5, $secondRunPaths);

        foreach ($firstRunPaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
