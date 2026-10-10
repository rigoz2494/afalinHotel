<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * One queued job per configured channel (Telegram, WhatsApp, a custom
 * corporate gateway) — each POST runs on its own, off the request/response
 * cycle, so a slow or unreachable endpoint on one channel never delays the
 * booking itself or any of the other channels.
 */
class DispatchBookingWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 15;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $url,
        public readonly array $payload,
        public readonly array $headers = [],
    ) {}

    public function handle(): void
    {
        $response = Http::timeout(10)
            ->withHeaders($this->headers)
            ->asJson()
            ->post($this->url, $this->payload);

        if ($response->failed()) {
            Log::warning('Booking webhook delivery failed.', [
                'url' => $this->url,
                'status' => $response->status(),
            ]);
        }
    }
}
