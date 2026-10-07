<?php

namespace Tests\Feature;

use App\Models\HotelSetting;
use Database\Seeders\HeroImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroImageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_stores_downloaded_slides_and_saves_their_local_paths(): void
    {
        Storage::fake('public');
        Http::fake(['images.unsplash.com/*' => Http::response('jpeg-bytes', 200)]);

        $this->seed(HeroImageSeeder::class);

        $expectedPaths = [
            'hero-slideshow/mock-1.jpg',
            'hero-slideshow/mock-2.jpg',
            'hero-slideshow/mock-3.jpg',
            'hero-slideshow/mock-4.jpg',
            'hero-slideshow/mock-5.jpg',
        ];

        $this->assertSame($expectedPaths, HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value);

        foreach ($expectedPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_failed_download_leaves_the_stored_hero_images_unchanged(): void
    {
        Storage::fake('public');
        Http::fake(['images.unsplash.com/*' => Http::response('', 500)]);
        HotelSetting::query()->create(['key' => 'hero_images', 'value' => ['hero-slideshow/old.jpg']]);

        $this->expectException(RequestException::class);

        try {
            $this->seed(HeroImageSeeder::class);
        } finally {
            $this->assertSame(
                ['hero-slideshow/old.jpg'],
                HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value,
            );
        }
    }
}
