<?php

namespace Tests\Feature;

use App\Http\Resources\AdditionalServiceResource;
use App\Models\AdditionalService;
use App\Models\Room;
use App\Services\AdditionalServiceService;
use Database\Seeders\AdditionalServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdditionalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeder_installs_the_four_real_services(): void
    {
        $this->seed(AdditionalServiceSeeder::class);

        $this->assertSame(4, AdditionalService::query()->count());
        $this->assertSame(800.0, AdditionalService::query()->where('name->en', 'Extra bed (child)')->firstOrFail()->price);
    }

    public function test_only_active_services_are_listed_in_display_order(): void
    {
        AdditionalService::factory()->create(['name' => ['en' => 'Second', 'ru' => 'Второй'], 'sort_order' => 2]);
        AdditionalService::factory()->create(['name' => ['en' => 'First', 'ru' => 'Первый'], 'sort_order' => 1]);
        AdditionalService::factory()->inactive()->create(['sort_order' => 0]);

        $services = app(AdditionalServiceService::class)->listActive();

        $this->assertCount(2, $services);
        $this->assertSame('First', $services->first()->name['en']);
    }

    public function test_the_api_resource_exposes_the_bilingual_name_and_price(): void
    {
        $service = AdditionalService::factory()->create([
            'name' => ['en' => 'Parking', 'ru' => 'Парковка'],
            'price' => 300,
        ]);

        $resolved = AdditionalServiceResource::make($service)->resolve();

        $this->assertSame('Parking', $resolved['name']['en']);
        $this->assertSame('Парковка', $resolved['name']['ru']);
        $this->assertEquals(300, $resolved['price']);
    }

    public function test_the_landing_page_receives_active_additional_services(): void
    {
        Room::factory()->create();
        AdditionalService::factory()->create(['name' => ['en' => 'Breakfast', 'ru' => 'Завтрак']]);
        AdditionalService::factory()->inactive()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('additionalServices.data', 1)
                ->where('additionalServices.data.0.name.en', 'Breakfast'));
    }
}
