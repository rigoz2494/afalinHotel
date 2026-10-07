<?php

namespace Tests\Feature;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_oversized_uploads_are_scaled_down_and_stored_as_webp(): void
    {
        Storage::fake('public');

        $path = app(ImageOptimizer::class)->storeAsWebp(
            UploadedFile::fake()->image('wide.png', 3000, 1000),
            'hero-slideshow',
            1920,
        );

        $this->assertStringStartsWith('hero-slideshow/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $size = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame('image/webp', $size['mime']);
        $this->assertSame(1920, $size[0]);
        $this->assertSame(640, $size[1]);
    }

    public function test_images_already_within_the_limit_are_not_enlarged(): void
    {
        Storage::fake('public');

        $path = app(ImageOptimizer::class)->storeAsWebp(
            UploadedFile::fake()->image('small.jpg', 400, 300),
            'rooms',
            1200,
        );

        $size = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(400, $size[0]);
        $this->assertSame(300, $size[1]);
    }

    public function test_unreadable_files_are_rejected(): void
    {
        Storage::fake('public');

        $this->expectException(RuntimeException::class);

        app(ImageOptimizer::class)->storeAsWebp(
            UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
            'rooms',
            1200,
        );
    }
}
