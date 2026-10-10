<?php

namespace Database\Factories;

use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryImage>
 */
class GalleryImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words = fake()->words(2, true);

        return [
            // Not a real file — fine for most tests, but GalleryImage's own
            // `saving` hook (which derives `tall` from the real stored photo)
            // will find nothing there and leave `tall` false either way.
            'path' => 'gallery/'.fake()->uuid().'.webp',
            'alt' => ['en' => ucfirst($words), 'ru' => ucfirst($words)],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
