<script setup lang="ts">
import { useEventListener } from '@vueuse/core';
import { X } from '@lucide/vue';
import ClientOnly from '@/components/ClientOnly.vue';
import BookingForm from '@/components/landing/BookingForm.vue';
import { useLocale } from '@/composables/useLocale';

const open = defineModel<boolean>({ required: true });
const { t } = useLocale();

const close = (): void => {
    open.value = false;
};

// Fires once the success overlay's own lifetime is over (its ~4s auto-hide,
// or the guest closing it by hand) — see BookingForm.vue — so this modal
// never disappears out from under that message while it's still showing.
// The short extra wait lets the overlay's own 400ms fade-out finish first,
// instead of this modal unmounting underneath it mid-transition.
const handleSubmitted = (): void => {
    setTimeout(close, 400);
};

// No explicit `window` target: see the comment in SiteHeader.vue.
useEventListener('keydown', (event: KeyboardEvent) => {
    if (open.value && event.key === 'Escape') {
        close();
    }
});
</script>

<template>
    <ClientOnly>
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="open"
                    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="t('completeBookingRequestAria')"
                    @click.self="close"
                >
                    <div
                        class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-sky-950 p-6 text-white shadow-2xl sm:p-8"
                    >
                        <button
                            type="button"
                            :aria-label="t('close')"
                            class="absolute top-4 right-4 rounded-full bg-white/10 p-2 transition hover:bg-white/20"
                            @click="close"
                        >
                            <X class="size-5" />
                        </button>
                        <h2 class="text-2xl font-semibold sm:text-3xl">
                            {{ t('completeYourBooking') }}
                        </h2>
                        <p class="mt-1 text-sm text-white/60">
                            {{ t('reviewSelectedRooms') }}
                        </p>
                        <BookingForm
                            class="mt-6"
                            @submitted="handleSubmitted"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>
