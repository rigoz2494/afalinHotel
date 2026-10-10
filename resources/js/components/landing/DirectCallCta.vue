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
            <div class="h-px flex-1 bg-white/15"></div>
            <span class="text-xs tracking-widest text-white/40 uppercase">{{
                t('orDivider')
            }}</span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>
        <a
            :href="hotelPhoneHref!"
            class="relative mt-3 flex items-center gap-2.5 overflow-hidden rounded-2xl border-2 border-amber-300/70 bg-amber-300/10 px-3.5 py-2.5 transition hover:bg-amber-300/20 sm:mt-4 sm:gap-4 sm:px-5 sm:py-4"
        >
            <span
                class="pointer-events-none absolute inset-0 animate-pulse rounded-2xl ring-2 ring-amber-300/40"
            ></span>
            <span
                class="relative flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-300 text-slate-900 sm:size-12"
            >
                <Phone class="size-4 sm:size-6" />
            </span>
            <span class="relative min-w-0 text-left">
                <span
                    class="block text-[11px] font-medium text-amber-200/90 sm:text-sm"
                    >{{ t('directCallPrefix') }}</span
                >
                <span class="block text-sm font-bold text-white sm:text-xl">{{
                    hotelPhoneNumber
                }}</span>
                <span
                    class="mt-0.5 hidden text-xs text-white/60 sm:block sm:text-sm"
                    >{{ t('directCallSuffix') }}</span
                >
            </span>
        </a>
    </div>
</template>
