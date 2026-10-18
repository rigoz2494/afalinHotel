<script setup lang="ts">
import { MapPin } from '@lucide/vue';
import { computed } from 'vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();

// The hotel's real, fixed street address — unlike hotel.contacts (a
// free-form admin-editable bag), this one also drives the Yandex Maps
// link below, so it stays a precise, known-good string here rather than
// whatever text an admin might type into a generic field.
const address = {
    en: '1-B Revolutsionnaya St., Feodosia, Republic of Crimea, 298100',
    ru: 'ул. Революционная, 1Б, г. Феодосия, Республика Крым, 298100',
};

const displayAddress = computed(() => localized(address, locale.value));

// Yandex's own live geocoder resolves this address when the link is
// opened, rather than an embedded map pinned to coordinates guessed or
// hardcoded here — accuracy for a real street address matters too much
// to risk getting wrong with an unverified pin.
const yandexMapsHref = computed(
    () => `https://yandex.ru/maps/?text=${encodeURIComponent(address.ru)}`,
);
</script>

<template>
    <section
        id="location"
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-14 text-stone-100 sm:px-6 lg:h-screen lg:snap-start lg:py-0 lg:pt-16"
    >
        <SectionBackdrop name="location" />
        <div class="relative z-10 w-full max-w-3xl text-center 2xl:max-w-4xl">
            <p class="text-sm tracking-widest text-amber-300 uppercase">
                {{ t('navLocation') }}
            </p>
            <h2
                class="mt-3 text-3xl leading-tight font-semibold sm:text-4xl md:text-5xl"
            >
                {{ localized(hotel.section_headings.location, locale) }}
            </h2>
            <p class="mt-3 text-sm text-stone-300 sm:text-base">
                {{ t('locationIntro') }}
            </p>

            <div
                class="mt-6 flex items-center justify-center gap-2.5 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/90 backdrop-blur sm:mt-8 sm:text-base"
            >
                <MapPin class="size-5 shrink-0 text-amber-300" />
                {{ displayAddress }}
            </div>

            <a
                :href="yandexMapsHref"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-5 inline-flex items-center gap-2 rounded-full bg-amber-300 px-6 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-200 sm:mt-6"
            >
                <MapPin class="size-4" />
                {{ t('openInYandexMaps') }}
            </a>
        </div>
    </section>
</template>
