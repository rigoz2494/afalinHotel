<?php

namespace Tests\Feature;

use App\Jobs\DispatchBookingWebhook;
use App\Models\HotelSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * CallbackRequestObserver fans a newly created booking out to whichever
 * third-party channels (Telegram, WhatsApp, a generic corporate gateway)
 * the admin has configured and switched on in Site Settings — each one
 * its own queued DispatchBookingWebhook job, never a direct, blocking
 * HTTP call from the request itself.
 */
class WebhookDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_job_is_dispatched_when_no_webhook_is_configured(): void
    {
        Queue::fake();

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '+79161234567',
        ])->assertCreated();

        Queue::assertNothingPushed();
    }

    public function test_an_active_telegram_webhook_receives_its_own_job_with_a_masked_phone_number(): void
    {
        HotelSetting::factory()->create([
            'key' => 'webhooks',
            'value' => [
                'telegram' => ['url' => 'https://router.example.com/telegram', 'channel_id' => 'chan-1', 'active' => true],
            ],
        ]);

        Queue::fake([DispatchBookingWebhook::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '+79161234567',
        ])->assertCreated();

        Queue::assertPushed(DispatchBookingWebhook::class, function (DispatchBookingWebhook $job): bool {
            return $job->url === 'https://router.example.com/telegram'
                && $job->payload['channel_id'] === 'chan-1'
                && $job->payload['booking']['guest_name'] === 'Jane'
                && $job->payload['booking']['phone_masked'] === '+791******67';
        });
    }

    public function test_a_configured_but_inactive_webhook_receives_no_job(): void
    {
        HotelSetting::factory()->create([
            'key' => 'webhooks',
            'value' => [
                'telegram' => ['url' => 'https://router.example.com/telegram', 'channel_id' => 'chan-1', 'active' => false],
            ],
        ]);

        Queue::fake();

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '+79161234567',
        ])->assertCreated();

        Queue::assertNothingPushed();
    }

    public function test_an_active_toggle_with_no_url_receives_no_job(): void
    {
        HotelSetting::factory()->create([
            'key' => 'webhooks',
            'value' => [
                'whatsapp' => ['url' => null, 'active' => true],
            ],
        ]);

        Queue::fake();

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '+79161234567',
        ])->assertCreated();

        Queue::assertNothingPushed();
    }

    public function test_every_active_channel_gets_its_own_job(): void
    {
        HotelSetting::factory()->create([
            'key' => 'webhooks',
            'value' => [
                'telegram' => ['url' => 'https://router.example.com/telegram', 'channel_id' => 'chan-1', 'active' => true],
                'whatsapp' => ['url' => 'https://router.example.com/whatsapp', 'active' => true],
                'custom' => ['url' => 'https://router.example.com/custom', 'auth_header' => 'Bearer secret-token', 'active' => true],
            ],
        ]);

        Queue::fake([DispatchBookingWebhook::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '+79161234567',
        ])->assertCreated();

        Queue::assertPushed(DispatchBookingWebhook::class, 3);

        Queue::assertPushed(DispatchBookingWebhook::class, fn (DispatchBookingWebhook $job): bool => $job->url === 'https://router.example.com/custom'
            && $job->headers['Authorization'] === 'Bearer secret-token');
    }
}
