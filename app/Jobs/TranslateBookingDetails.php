<?php

namespace App\Jobs;

use App\Models\CallbackRequest;
use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Off the request/response cycle: a guest's message or special_requests
 * could be written in any language, and translating it calls a real
 * external API (TranslationService, a free, no-key service with no
 * uptime guarantee) that can take several seconds or time out — never
 * something the guest's own booking confirmation should wait on.
 */
class TranslateBookingDetails implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public readonly CallbackRequest $callbackRequest) {}

    public function handle(TranslationService $translator): void
    {
        $updates = [];

        $translatedMessage = $translator->translateToRussian($this->callbackRequest->message);

        if ($translatedMessage !== null) {
            $updates['message_translated'] = $translatedMessage;
        }

        $translatedRequest = $translator->translateToRussian($this->callbackRequest->special_requests);

        if ($translatedRequest !== null) {
            $updates['special_requests_translated'] = $translatedRequest;
        }

        if ($updates !== []) {
            $this->callbackRequest->updateQuietly($updates);
        }
    }
}
