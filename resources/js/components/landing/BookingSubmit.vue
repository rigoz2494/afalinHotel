<script setup lang="ts">
import { computed } from 'vue';
import BookingSuccessOverlay from '@/components/landing/BookingSuccessOverlay.vue';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useBookingSubmission } from '@/composables/useBookingSubmission';
import { useCurrency } from '@/composables/useCurrency';
import { useLocale } from '@/composables/useLocale';
import { useRoomSelection } from '@/composables/useRoomSelection';
import { useServiceBasket } from '@/composables/useServiceBasket';

const { status, successOverlayOpen } = useBookingSubmission();
const { items: rooms } = useBookingBasket();
const { items: services } = useServiceBasket();
const { selectedRoomNumber } = useRoomSelection();
const { convert, formatConverted } = useCurrency();
const { t } = useLocale();

const roomsPerNight = computed(() =>
    rooms.value.reduce(
        (sum, room) => sum + convert(room.basePrice) * room.quantity,
        0,
    ),
);
const servicesTotal = computed(() =>
    services.value.reduce(
        (sum, service) => sum + convert(service.basePrice) * service.quantity,
        0,
    ),
);
</script>

<template>
    <div>
        <div
            v-if="rooms.length || services.length || selectedRoomNumber"
            class="border-coast-line bg-coast-mist mb-3 space-y-2 rounded-xl border p-3 text-sm sm:p-4"
        >
            <p class="text-coast-ink font-semibold">
                {{ t('bookingSummary') }}
            </p>
            <ul
                v-if="rooms.length || services.length"
                class="text-coast-muted space-y-1"
            >
                <li
                    v-for="room in rooms"
                    :key="room.key"
                    class="flex justify-between gap-3"
                >
                    <span>
                        {{ room.roomName }}
                        <template v-if="room.period">
                            ({{ room.period }})</template
                        >
                    </span>
                    <span class="shrink-0">× {{ room.quantity }}</span>
                </li>
                <li
                    v-for="service in services"
                    :key="`service-${service.serviceId}`"
                    class="flex justify-between gap-3"
                >
                    <span>{{ service.name }}</span>
                    <span class="shrink-0">× {{ service.quantity }}</span>
                </li>
            </ul>
            <p v-if="selectedRoomNumber" class="text-coast-muted">
                {{ t('bookingRequestedRoom') }} № {{ selectedRoomNumber }}
            </p>
            <dl
                v-if="rooms.length || services.length"
                class="border-coast-line text-coast-ink space-y-1 border-t pt-2"
            >
                <div v-if="rooms.length" class="flex justify-between gap-3">
                    <dt>{{ t('bookingRoomsPerNight') }}</dt>
                    <dd class="shrink-0 font-semibold">
                        {{ formatConverted(roomsPerNight) }}
                    </dd>
                </div>
                <div v-if="services.length" class="flex justify-between gap-3">
                    <dt>{{ t('bookingServicesTotal') }}</dt>
                    <dd class="shrink-0 font-semibold">
                        {{ formatConverted(servicesTotal) }}
                    </dd>
                </div>
            </dl>
        </div>
        <p class="text-coast-muted mb-2 text-xs sm:text-sm">
            {{ t('bookingConfirmationNote') }}
        </p>
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
            class="bg-coast-blue hover:bg-coast-ink w-full rounded-full px-6 py-2 text-xs font-medium text-white transition disabled:opacity-60 sm:w-auto sm:px-8 sm:py-3 sm:text-base"
        >
            {{ status === 'sending' ? t('sending') : t('sendRequest') }}
        </button>
        <p v-if="status === 'error'" class="mt-2 text-sm text-red-700">
            {{ t('sendError') }}
        </p>

        <BookingSuccessOverlay v-model="successOverlayOpen" />
    </div>
</template>
