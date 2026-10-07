<?php

namespace Tests\Feature;

use App\Filament\Resources\CallbackRequests\Pages\EditCallbackRequest;
use App\Filament\Resources\Rooms\Pages\CreateRoom;
use App\Models\CallbackRequest;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCurrencySymbolTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_fields_show_the_active_base_currency_symbol(): void
    {
        Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'is_base' => false]);
        Currency::factory()->create(['code' => 'RUB', 'symbol' => '₽', 'exchange_rate' => 1, 'is_base' => true]);

        $this->actingAs(User::factory()->create());

        // The promotional-discount form only ever shows a percentage now, so
        // only the room's base price field has a currency symbol to check.
        Livewire::test(CreateRoom::class)->assertSee('₽');
    }

    public function test_price_fields_fall_back_to_dollars_when_no_currency_is_configured(): void
    {
        $this->actingAs(User::factory()->create());

        $this->assertSame('$', Currency::baseSymbol());
        Livewire::test(CreateRoom::class)->assertSee('$');
    }

    public function test_a_booking_shows_the_symbol_of_the_currency_it_was_made_in(): void
    {
        Currency::factory()->base()->create(['symbol' => '$']);
        Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 0.92]);

        $booking = CallbackRequest::factory()->create([
            'currency' => 'EUR',
            'exchange_rate' => 0.92,
            'rooms' => [['room_id' => 1, 'room_name' => 'Deluxe', 'period' => null, 'currency' => 'EUR', 'base_price' => 135, 'price' => 124, 'quantity' => 1]],
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test(EditCallbackRequest::class, ['record' => $booking->getRouteKey()])
            ->assertSee('€');
    }

    public function test_base_currency_symbol_is_the_one_the_frontend_converts_from(): void
    {
        Currency::factory()->base()->create(['symbol' => '$']);
        Currency::factory()->create(['code' => 'UAH', 'symbol' => '₴', 'exchange_rate' => 41.5, 'is_base' => true]);

        $this->assertSame('₴', Currency::baseSymbol());
        $this->assertSame('UAH', Currency::base()->code);
    }
}
