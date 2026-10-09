<?php

namespace App\Observers;

use App\Models\CallbackRequest;
use App\Services\TranslationService;

class CallbackRequestObserver
{
    public function __construct(private TranslationService $translator) {}

    /**
     * A guest's message or special request could be written in any
     * language; a Russian-speaking manager reading the admin dashboard may
     * not understand it. This fills in the automatic Russian translation of
     * both right after the booking is saved, so the manager always has a
     * readable version alongside the guest's own words — never blocking
     * the booking itself if the translation service is unreachable.
     */
    public function created(CallbackRequest $callbackRequest): void
    {
        $updates = [];

        $translatedMessage = $this->translator->translateToRussian($callbackRequest->message);

        if ($translatedMessage !== null) {
            $updates['message_translated'] = $translatedMessage;
        }

        $translatedRequest = $this->translator->translateToRussian($callbackRequest->special_requests);

        if ($translatedRequest !== null) {
            $updates['special_requests_translated'] = $translatedRequest;
        }

        if ($updates !== []) {
            $callbackRequest->updateQuietly($updates);
        }
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
