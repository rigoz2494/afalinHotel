<script setup lang="ts">
import { MapPin } from '@lucide/vue';
import { computed } from 'vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();

// The hotel's real, fixed street address — unlike hotel.contacts (a
// free-form admin-editable bag), this one is tied to the exact Yandex
// Business listing below, so it stays a precise, known-good string here
// rather than whatever text an admin might type into a generic field.
const address = {
    en: '1-B Revolutsionnaya St., Feodosia, Republic of Crimea, 298100',
    ru: 'ул. Революционная, 1Б, г. Феодосия, Республика Крым, 298100',
};

const displayAddress = computed(() => localized(address, locale.value));

// The hotel's verified Yandex Business listing (org id 12100073003) -
// both the embedded widget below and this link point at the same real,
// Yandex-geocoded place, not a guessed pin or a generic text search.
const yandexOrgUrl = 'https://yandex.ru/maps/org/afalina/12100073003/';
const yandexMapWidgetSrc =
    'https://yandex.ru/map-widget/v1/org/afalina/12100073003/?ll=35.381746%2C45.037573&z=16';
</script>

<template>
    <section
        id="location"
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-14 text-stone-100 sm:px-6 lg:h-screen lg:snap-start lg:py-0 lg:pt-16"
    >
        <SectionBackdrop name="location" />
        <div class="relative z-10 w-full max-w-3xl text-center 2xl:max-w-5xl">
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

            <!--
                Yandex's own embeddable widget for the hotel's verified
                Business listing (org id 12100073003) - not a pin guessed
                from coordinates, so accuracy is Yandex's own live data, not
                ours. The two small overlaid links are Yandex's required
                attribution for the embed (their terms for this widget), so
                they stay, just restyled to sit cleanly over the rounded
                container instead of the raw default markup.
            -->
            <div
                class="relative mt-5 overflow-hidden rounded-xl border border-white/10 shadow-2xl sm:mt-6"
            >
                <a
                    :href="yandexOrgUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="absolute top-2 left-2 z-10 rounded bg-black/40 px-1.5 py-0.5 text-[11px] text-white/80 backdrop-blur-sm hover:text-white"
                    >Афалина</a
                >
                <iframe
                    :src="yandexMapWidgetSrc"
                    :title="t('locationMapTitle')"
                    class="h-64 w-full sm:h-80 lg:h-72 2xl:h-96"
                    loading="lazy"
                    allowfullscreen
                ></iframe>
            </div>

            <a
                :href="yandexOrgUrl"
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
