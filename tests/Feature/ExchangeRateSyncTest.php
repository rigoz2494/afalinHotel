<?php

namespace Tests\Feature;

use App\Exceptions\ExchangeRateSyncException;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Models\Currency;
use App\Models\User;
use App\Services\ExchangeRateSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ExchangeRateSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_updates_active_currencies_relative_to_the_detected_base(): void
    {
        Currency::factory()->create(['code' => 'UAH', 'symbol' => '₴', 'exchange_rate' => 1, 'is_base' => true]);
        $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'exchange_rate' => 1]);
        $eur = Currency::factory()->inactive()->create(['code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 1]);

        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'UAH',
                'rates' => ['UAH' => 1, 'USD' => 0.0241, 'EUR' => 0.0219],
            ]),
        ]);

        $result = app(ExchangeRateSyncService::class)->syncFromBase();

        $this->assertSame(['base' => 'UAH', 'updated' => 1], $result);
        $this->assertSame(0.0241, $usd->refresh()->exchange_rate);
        // Inactive currencies are left alone.
        $this->assertSame(1.0, $eur->refresh()->exchange_rate);

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/latest/UAH'));
    }

    public function test_sync_fails_clearly_when_no_base_currency_is_configured(): void
    {
        $this->expectException(ExchangeRateSyncException::class);

        app(ExchangeRateSyncService::class)->syncFromBase();
    }

    public function test_sync_fails_clearly_when_the_api_reports_an_unsupported_code(): void
    {
        Currency::factory()->base()->create(['code' => 'XXX']);

        Http::fake(['open.er-api.com/*' => Http::response(['result' => 'error', 'error-type' => 'unsupported-code'])]);

        $this->expectException(ExchangeRateSyncException::class);

        app(ExchangeRateSyncService::class)->syncFromBase();
    }

    public function test_sync_fails_clearly_when_the_request_fails(): void
    {
        Currency::factory()->base()->create();

        Http::fake(['open.er-api.com/*' => Http::response([], 500)]);

        $this->expectException(ExchangeRateSyncException::class);

        app(ExchangeRateSyncService::class)->syncFromBase();
    }

    public function test_admin_can_trigger_the_sync_from_the_currencies_list_and_sees_a_success_notification(): void
    {
        Currency::factory()->create(['code' => 'UAH', 'exchange_rate' => 1, 'is_base' => true]);
        $usd = Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1]);

        Http::fake(['open.er-api.com/*' => Http::response([
            'result' => 'success', 'base_code' => 'UAH', 'rates' => ['UAH' => 1, 'USD' => 0.0241],
        ])]);

        $this->actingAs(User::factory()->create());

        Livewire::test(ListCurrencies::class)
            ->callAction('syncExchangeRates')
            ->assertNotified('Exchange rates updated');

        $this->assertSame(0.0241, $usd->refresh()->exchange_rate);
    }

    public function test_admin_sees_a_failure_notification_when_the_api_is_unreachable(): void
    {
        Currency::factory()->base()->create(['code' => 'UAH']);

        Http::fake(['open.er-api.com/*' => Http::response([], 500)]);

        $this->actingAs(User::factory()->create());

        Livewire::test(ListCurrencies::class)
            ->callAction('syncExchangeRates')
            ->assertNotified('Exchange rate update failed');
    }
}
