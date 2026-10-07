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
     */
    public function storeAsWebp(File $file, string $directory, int $maxWidth): string
    {
        if (! in_array($file->getMimeType(), self::ACCEPTED_MIME_TYPES, true)) {
            throw new RuntimeException('The uploaded file is not a supported image type.');
        }

        $image = imagecreatefromstring((string) file_get_contents($file->getPathname()));

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if (imagesx($image) > $maxWidth) {
            $image = imagescale($image, $maxWidth) ?: $image;
        }

        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);
        $webpBinary = (string) ob_get_clean();

        $path = $directory.'/'.Str::ulid().'.webp';

        Storage::disk('public')->put($path, $webpBinary);

        return $path;
    }
}
