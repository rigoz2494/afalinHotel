<script setup lang="ts">
import { Phone } from '@lucide/vue';
import { computed } from 'vue';
import { useHotelSettings } from '@/composables/useHotelSettings';
import { useLocale } from '@/composables/useLocale';

const { t } = useLocale();
const hotel = useHotelSettings();

// Hidden if the admin hasn't set a phone number in Site Settings yet,
// rather than showing a broken link.
const hotelPhoneNumber = computed(() => hotel.value.contacts.phone ?? null);
const hotelPhoneHref = computed(() =>
    hotelPhoneNumber.value
        ? `tel:${hotelPhoneNumber.value.replace(/[^+\d]/g, '')}`
        : null,
);
</script>

<template>
    <div v-if="hotelPhoneNumber">
        <div class="flex items-center gap-3">
            <div class="bg-coast-line h-px flex-1"></div>
            <span class="text-coast-muted text-xs tracking-widest uppercase">{{
                t('orDivider')
            }}</span>
            <div class="bg-coast-line h-px flex-1"></div>
        </div>
        <a
            :href="hotelPhoneHref!"
            class="border-coast-line bg-coast-mist hover:border-coast-blue relative mt-3 flex items-center gap-2.5 overflow-hidden rounded-xl border px-3.5 py-2.5 transition sm:mt-4 sm:gap-4 sm:px-5 sm:py-4"
        >
            <span
                class="bg-coast-blue relative flex size-9 shrink-0 items-center justify-center rounded-full text-white sm:size-12"
            >
                <Phone class="size-4 sm:size-6" />
            </span>
            <span class="relative min-w-0 text-left">
                <span
                    class="text-coast-ink block text-[11px] font-medium sm:text-sm"
                    >{{ t('directCallPrefix') }}</span
                >
                <span
                    class="text-coast-ink block text-sm font-bold sm:text-xl"
                    >{{ hotelPhoneNumber }}</span
                >
                <span
                    class="text-coast-muted mt-0.5 hidden text-xs sm:block sm:text-sm"
                    >{{ t('directCallSuffix') }}</span
                >
            </span>
        </a>
    </div>
</template>
