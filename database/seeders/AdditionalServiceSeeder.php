<?php

namespace Database\Seeders;

use App\Models\AdditionalService;
use Illuminate\Database\Seeder;

class AdditionalServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Flat prices, not seasonal — this is what "Дополнительное место"
        // (Extra Bed Space) really always was, now modeled as what it is
        // instead of forced into the pricing matrix as a room row with no
        // single number a season modifier could compute for it.
        $services = [
            ['name' => ['en' => 'Extra bed (child)', 'ru' => 'Дополнительное место (ребёнок)'], 'price' => 800],
            ['name' => ['en' => 'Extra bed (adult)', 'ru' => 'Дополнительное место (взрослый)'], 'price' => 1200],
            ['name' => ['en' => 'Parking', 'ru' => 'Парковка'], 'price' => 300],
            ['name' => ['en' => 'Breakfast', 'ru' => 'Завтрак'], 'price' => 500],
        ];

        foreach ($services as $index => $service) {
            AdditionalService::updateOrCreate(['name->en' => $service['name']['en']], [
                'name' => $service['name'],
                'price' => $service['price'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
