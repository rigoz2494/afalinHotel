<?php

namespace App\Services;

use App\Jobs\DispatchBookingWebhook;
use App\Models\CallbackRequest;
use App\Models\HotelSetting;

/**
 * Fans a newly created booking out to whichever third-party channels the
 * admin has configured and switched on in Site Settings (Telegram,
 * WhatsApp, a generic corporate gateway for MAX/TamTam/MyTeam) — each one
 * its own fire-and-forget queued POST, so this never blocks the booking
 * itself and one unreachable channel never affects the others.
 */
class WebhookDispatchService
{
    public function dispatch(CallbackRequest $callbackRequest): void
    {
        $webhooks = HotelSetting::query()->where('key', 'webhooks')->first()?->value ?? [];

        if (! is_array($webhooks)) {
            return;
        }

        $payload = $this->buildPayload($callbackRequest);

        foreach ($this->activeEndpoints($webhooks) as $endpoint) {
            DispatchBookingWebhook::dispatch(
                $endpoint['url'],
                [...$payload, ...$endpoint['extra']],
                $endpoint['headers'],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(CallbackRequest $callbackRequest): array
    {
        return [
            'event' => 'booking.created',
            'booking' => [
                'id' => $callbackRequest->id,
                'guest_name' => $callbackRequest->name,
                'phone_masked' => $this->maskPhone($callbackRequest->phone),
                'room_number' => $callbackRequest->room_number,
                'rooms' => $callbackRequest->rooms,
                'services' => $callbackRequest->services,
                'total_price' => $callbackRequest->total_price,
                'currency' => $callbackRequest->currency,
                'special_requests' => $callbackRequest->special_requests,
                'submitted_at' => $callbackRequest->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * Only a channel that's both switched on and actually has an endpoint
     * URL configured gets a job — a toggle left on with a blank URL (or
     * vice versa) sends nothing rather than erroring.
     *
     * @param  array<string, mixed>  $webhooks
     * @return array<int, array{url: string, headers: array<string, string>, extra: array<string, mixed>}>
     */
    private function activeEndpoints(array $webhooks): array
    {
        $endpoints = [];

        $telegram = $webhooks['telegram'] ?? [];

        if (($telegram['active'] ?? false) && filled($telegram['url'] ?? null)) {
            $endpoints[] = [
                'url' => $telegram['url'],
                'headers' => [],
                // The admin's own router uses this to pick which Telegram
                // channel/chat a booking gets forwarded to.
                'extra' => array_filter(['channel_id' => $telegram['channel_id'] ?? null]),
            ];
        }

        $whatsapp = $webhooks['whatsapp'] ?? [];

        if (($whatsapp['active'] ?? false) && filled($whatsapp['url'] ?? null)) {
            $endpoints[] = ['url' => $whatsapp['url'], 'headers' => [], 'extra' => []];
        }

        $custom = $webhooks['custom'] ?? [];

        if (($custom['active'] ?? false) && filled($custom['url'] ?? null)) {
            $endpoints[] = [
                'url' => $custom['url'],
                'headers' => array_filter(['Authorization' => $custom['auth_header'] ?? null]),
                'extra' => [],
            ];
        }

        return $endpoints;
    }

    /**
     * Keeps the first 4 and last 2 characters (e.g. "+7916" and the last
     * two digits), masking everything between — enough for a human
     * reviewing the channel to recognize the number without it being the
     * guest's full phone number in transit to a third party.
     */
    private function maskPhone(string $phone): string
    {
        $length = mb_strlen($phone);

        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        return mb_substr($phone, 0, 4).str_repeat('*', $length - 6).mb_substr($phone, -2);
    }
}
