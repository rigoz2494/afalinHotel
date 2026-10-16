<?php

namespace App\Observers;

use App\Jobs\TranslateBookingDetails;
use App\Models\CallbackRequest;
use App\Services\WebhookDispatchService;

class CallbackRequestObserver
{
    public function __construct(
        private WebhookDispatchService $webhooks,
    ) {}

    /**
     * Both side effects of a new booking call a real external API
     * (TranslationService, each configured webhook endpoint) and run as
     * queued jobs for exactly the same reason: neither should make the
     * guest's own booking confirmation wait on a third party that could
     * be slow or unreachable. This method itself does no network I/O —
     * dispatching a job onto the `database` queue is just an INSERT.
     */
    public function created(CallbackRequest $callbackRequest): void
    {
        TranslateBookingDetails::dispatch($callbackRequest);

        $this->webhooks->dispatch($callbackRequest);
    }

    /**
     * Handle the CallbackRequest "updated" event.
     */
    public function updated(CallbackRequest $callbackRequest): void
    {
        //
    }

    /**
     * Handle the CallbackRequest "deleted" event.
     */
    public function deleted(CallbackRequest $callbackRequest): void
    {
        //
    }

    /**
     * Handle the CallbackRequest "restored" event.
     */
    public function restored(CallbackRequest $callbackRequest): void
    {
        //
    }

    /**
     * Handle the CallbackRequest "force deleted" event.
     */
    public function forceDeleted(CallbackRequest $callbackRequest): void
    {
        //
    }
}
