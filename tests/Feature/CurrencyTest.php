<?php

namespace Tests\Feature;

use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Currencies\Pages\EditCurrency;
use App\Filament\Widgets\BookingStatsOverview;
use App\Models\CallbackRequest;
use App\Models\Currency;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_only_one_currency_can_be_the_base_and_it_always_has_rate_one(): void
    {
        $usd = Currency::factory()->base()->create();
        $eur = Currency::factory()->create(['code' => 'EUR', 'exchange_rate' => 0.9]);

        $eur->update(['is_base' => true, 'exchange_rate' => 0.5]);

        $this->assertFalse($usd->refresh()->is_base);
        $this->assertTrue($eur->refresh()->is_base);
        $this->assertSame(1.0, $eur->exchange_rate);
    }

    public function test_the_base_currency_cannot_be_deleted_or_unmarked(): void
    {
        $usd = Currency::factory()->base()->create();

        $this->expectException(RuntimeException::class);
        $usd->delete();
    }

    public function test_unmarking_the_base_currency_directly_is_refused(): void
    {
        $usd = Currency::factory()->base()->create();

        $this->expectException(RuntimeException::class);
        $usd->update(['is_base' => false]);
    }

    public function test_pricing_api_lists_only_active_currencies_with_the_base_first(): void
    {
        Currency::factory()->create(['code' => 'EUR', 'exchange_rate' => 0.92, 'sort_order' => 2]);
        Currency::factory()->base()->create(['sort_order' => 1]);
        Currency::factory()->inactive()->create(['code' => 'JPY', 'sort_order' => 3]);

        $this->getJson(route('api.v1.pricing.index'))
            ->assertOk()
            ->assertJsonPath('currencies.0.code', 'USD')
            ->assertJsonPath('currencies.0.is_base', true)
            ->assertJsonPath('currencies.1.code', 'EUR')
            ->assertJsonCount(2, 'currencies');
    }

    public function test_the_api_exposes_each_currencys_symbol_position(): void
    {
        Currency::factory()->base()->create(['symbol' => '$', 'symbol_position' => 'before']);
        Currency::factory()->create(['code' => 'RUB', 'symbol' => '₽', 'symbol_position' => 'after', 'exchange_rate' => 92.5]);

        $this->getJson(route('api.v1.pricing.index'))
            ->assertOk()
            ->assertJsonPath('currencies.0.symbol_position', 'before')
            ->assertJsonPath('currencies.1.symbol_position', 'after');
    }

    public function test_booking_in_another_currency_locks_the_code_rate_and_converted_price(): void
    {
        Currency::factory()->base()->create();
        Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 0.92]);

        // -25% season modifier makes the regular rate $150, then a 10% promo makes it $135.
        $period = PricingPeriod::factory()->create(['name' => 'June', 'sort_order' => 1, 'modifier_percentage' => -25]);
        $room = Room::factory()->create(['is_active' => true, 'base_price' => 200, 'discount_percentage' => null]);
        RoomPrice::factory()->create(['room_id' => $room->id, 'pricing_period_id' => $period->id, 'discount_percentage' => 10]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'currency' => 'EUR',
            'rooms' => [['room_id' => $room->id, 'room_name' => 'x', 'period' => 'June', 'price' => 1, 'quantity' => 2]],
        ])->assertCreated();

        $booking = CallbackRequest::query()->firstOrFail();

        $this->assertSame('EUR', $booking->currency);
        $this->assertSame(0.92, $booking->exchange_rate);
        $this->assertEquals([[
            'room_id' => $room->id,
            'room_name' => $room->name,
            'period' => 'June',
            'currency' => 'EUR',
            'base_price' => 135,
            'price' => 124,
            'quantity' => 2,
        ]], $booking->rooms);
    }

    public function test_booking_without_a_currency_is_taken_in_the_base_currency(): void
    {
        Currency::factory()->base()->create();
        $room = Room::factory()->create(['is_active' => true, 'base_price' => 210]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'rooms' => [['room_id' => $room->id, 'room_name' => 'x', 'period' => null, 'price' => 1, 'quantity' => 1]],
        ])->assertCreated();

        $this->assertSame('USD', CallbackRequest::query()->firstOrFail()->currency);
    }

    public function test_inactive_and_unknown_currencies_are_rejected(): void
    {
        Currency::factory()->base()->create();
        Currency::factory()->inactive()->create(['code' => 'JPY']);
        $room = Room::factory()->create(['is_active' => true, 'base_price' => 100]);

        foreach (['JPY', 'XXX'] as $code) {
            $this->postJson(route('api.v1.callback-requests.store'), [
                'name' => 'Jane',
                'phone' => '5550101234',
                'currency' => $code,
                'rooms' => [['room_id' => $room->id, 'room_name' => 'x', 'period' => null, 'price' => 1, 'quantity' => 1]],
            ])->assertUnprocessable()->assertJsonValidationErrors(['currency']);
        }

        $this->assertDatabaseCount('callback_requests', 0);
    }

    public function test_admin_can_create_a_currency_with_an_uppercase_code(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateCurrency::class)
            ->fillForm([
                'code' => 'gbp',
                'symbol' => '£',
                'exchange_rate' => 0.79,
                'sort_order' => 4,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('currencies', ['code' => 'GBP', 'symbol' => '£', 'is_active' => true]);
    }

    public function test_editing_a_currency_returns_to_the_list(): void
    {
        $this->actingAs(User::factory()->create());
        $currency = Currency::factory()->create(['code' => 'EUR', 'exchange_rate' => 0.9]);

        Livewire::test(EditCurrency::class, ['record' => $currency->getRouteKey()])
            ->fillForm(['exchange_rate' => 0.95])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(CurrencyResource::getUrl('index'));

        $this->assertSame(0.95, $currency->refresh()->exchange_rate);
    }

    public function test_admin_currency_list_renders(): void
    {
        Currency::factory()->base()->create();

        $this->actingAs(User::factory()->create())
            ->get('/admin/currencies')
            ->assertOk()
            ->assertSee('USD');
    }

    public function test_revenue_is_reported_in_the_base_currency_whatever_the_booking_currency(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

        // A EUR booking: 135 base per night, shown to the guest as 124 EUR, for 2 rooms.
        CallbackRequest::factory()->create([
            'currency' => 'EUR',
            'exchange_rate' => 0.92,
            'rooms' => [['room_id' => 1, 'room_name' => 'Deluxe', 'period' => null, 'currency' => 'EUR', 'base_price' => 135, 'price' => 124, 'quantity' => 2]],
            'created_at' => now(),
        ]);

        Livewire::test(BookingStatsOverview::class)
            ->assertSee('$270');
    }
}
