<?php

namespace App\Observers;

use App\Models\CallbackRequest;
use App\Services\TranslationService;

class CallbackRequestObserver
{
    public function __construct(private TranslationService $translator) {}

    /**
     * A guest's special request could be written in any language; a
     * Russian-speaking manager reading the admin dashboard may not
     * understand it. This fills in the automatic Russian translation right
     * after the booking is saved, so the manager always has a readable
     * version underneath the guest's own words — never blocking the
     * booking itself if the translation service is unreachable.
     */
    public function created(CallbackRequest $callbackRequest): void
    {
        $translated = $this->translator->translateToRussian($callbackRequest->special_requests);

        if ($translated !== null) {
            $callbackRequest->updateQuietly(['special_requests_translated' => $translated]);
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
