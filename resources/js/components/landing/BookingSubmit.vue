<script setup lang="ts">
import BookingSuccessOverlay from '@/components/landing/BookingSuccessOverlay.vue';
import { useBookingSubmission } from '@/composables/useBookingSubmission';
import { useLocale } from '@/composables/useLocale';

const { status, successOverlayOpen } = useBookingSubmission();
const { t } = useLocale();
</script>

<template>
    <div>
        <!--
            `form="booking-form"` ties this button to BookingForm.vue's
            <form> by id, even though that form lives in a different
            component entirely — clicking this still submits exactly that
            form (and fires its `@submit.prevent`), so moving this button
            wherever the responsive layout needs it never breaks the
            actual submission.
        -->
        <button
            type="submit"
            form="booking-form"
            :disabled="status === 'sending'"
            class="w-full rounded-full bg-amber-300 px-6 py-2 text-xs font-medium text-slate-900 transition hover:bg-amber-200 disabled:opacity-60 sm:w-auto sm:px-8 sm:py-3 sm:text-base"
        >
            {{ status === 'sending' ? t('sending') : t('sendRequest') }}
        </button>
        <p v-if="status === 'error'" class="mt-2 text-sm text-red-300">
            {{ t('sendError') }}
        </p>

        <BookingSuccessOverlay v-model="successOverlayOpen" />
    </div>
</template>
