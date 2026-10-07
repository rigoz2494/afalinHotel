<?php

namespace Tests\Feature;

use App\Filament\Resources\PricingPeriods\Pages\CreatePricingPeriod;
use App\Filament\Resources\PricingPeriods\Pages\EditPricingPeriod;
use App\Filament\Resources\PricingPeriods\PricingPeriodResource;
use App\Filament\Resources\RoomPrices\Pages\CreateRoomPrice;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Exercises the actual Filament pages, not just the PHP class files — a
 * namespace or import mistake in a form/table schema only surfaces once
 * the page is rendered, not at lint time.
 */
class PricingPeriodResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_pricing_periods(): void
    {
        PricingPeriod::factory()->create(['name' => 'June']);

        $this->actingAs(User::factory()->create())
            ->get('/admin/pricing-periods')
            ->assertOk()
            ->assertSee('June');
    }

    public function test_admin_can_create_a_pricing_period(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePricingPeriod::class)
            ->fillForm([
                'name' => 'August',
                'sort_order' => 5,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pricing_periods', [
            'name' => 'August',
            'sort_order' => 5,
        ]);
    }

    public function test_admin_can_edit_a_pricing_period_and_is_returned_to_the_list(): void
    {
        $period = PricingPeriod::factory()->create(['name' => 'Draft name']);

        Livewire::test(EditPricingPeriod::class, ['record' => $period->getRouteKey()])
            ->fillForm(['name' => 'Published name'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(PricingPeriodResource::getUrl('index'));

        $this->assertSame('Published name', $period->refresh()->name);
    }

    public function test_room_price_create_form_only_offers_active_pricing_periods(): void
    {
        PricingPeriod::factory()->create(['name' => 'Active Month']);
        PricingPeriod::factory()->inactive()->create(['name' => 'Retired Month']);
        Room::factory()->create();

        $this->actingAs(User::factory()->create());

        // `preload()` on the Select means its options are embedded in the
        // initial render, so this is an observable, black-box check of
        // what the admin actually sees — not a reach into internals.
        Livewire::test(CreateRoomPrice::class)
            ->assertSee('Active Month')
            ->assertDontSee('Retired Month');
    }
}
