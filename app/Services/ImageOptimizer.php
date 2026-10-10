<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

class ImageOptimizer
{
    /**
     * GD decodes these reliably; SVG and HEIC uploads are rejected up front.
     *
     * @var array<int, string>
     */
    public const array ACCEPTED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    private const int WEBP_QUALITY = 82;

    /**
     * Re-encodes an upload as WebP, scaled down to $maxWidth when it is wider, and
     * stores it on the public disk. Returns the path relative to that disk.
     *
     * $filenameHint, when given, is slugified into the stored filename
     * alongside its ULID — e.g. a seeder processing a source file named
     * "bath.jpg" so the resulting path still reads as a bath photo, letting
     * frontend code filter on it without needing to inspect the image
     * itself. Omitted for plain admin uploads, which keep the bare ULID.
     */
    public function storeAsWebp(File $file, string $directory, int $maxWidth, ?string $filenameHint = null): string
    {
        if (! in_array($file->getMimeType(), self::ACCEPTED_MIME_TYPES, true)) {
            throw new RuntimeException('The uploaded file is not a supported image type.');
        }

        $this->ensureEnoughMemory();

        $image = imagecreatefromstring((string) file_get_contents($file->getPathname()));

        if ($image === false) {
            throw new RuntimeException('The uploaded file could not be decoded as an image.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if (imagesx($image) > $maxWidth) {
            $image = imagescale($image, $maxWidth) ?: $image;
        }

        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);
        $webpBinary = (string) ob_get_clean();

        $slug = $filenameHint !== null ? Str::slug($filenameHint) : '';
        $path = $directory.'/'.($slug !== '' ? "{$slug}-" : '').Str::ulid().'.webp';

        Storage::disk('public')->put($path, $webpBinary);

        return $path;
    }

    /**
     * A large, high-resolution photo — straight off a modern phone or
     * camera, easily 10+ megapixels — can need well over PHP's default
     * memory_limit just for GD to decode it into an uncompressed bitmap,
     * before any resizing even happens. Raised here rather than globally,
     * so only this operation (an admin's upload, or a seeder processing a
     * batch of real photos) pays for the extra headroom.
     */
    private function ensureEnoughMemory(): void
    {
        $current = ini_get('memory_limit');

        if ($current === '-1') {
            return;
        }

        if ($this->toBytes($current) < 512 * 1024 * 1024) {
            ini_set('memory_limit', '512M');
        }
    }

    private function toBytes(string $value): int
    {
        $number = (int) $value;
        $unit = strtolower(substr($value, -1));

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
