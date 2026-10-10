<?php

namespace App\Services;

use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Collection;

class GalleryService
{
    /**
     * @return Collection<int, GalleryImage>
     */
    public function listActive(): Collection
    {
        return GalleryImage::query()->active()->ordered()->get();
    }
}
