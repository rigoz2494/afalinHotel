<script setup lang="ts">
import { MapPin } from '@lucide/vue';
import { computed, ref } from 'vue';
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

// The map is a cross-origin iframe, so there's no way to tell its own
// pan/zoom gestures apart from a scroll of the page itself from here -
// a finger (or scroll wheel) passing over it while scrolling the site
// gets eaten by the map instead. This transparent tap-to-unlock overlay
// is the standard fix: it owns the gesture first (so the page scrolls
// normally), and steps aside for the real map underneath once the guest
// deliberately taps it.
const mapUnlocked = ref(false);
</script>

<template>
    <section
        id="location"
        class="bg-coast-paper text-coast-ink relative flex scroll-mt-14 flex-col items-center px-4 py-10 sm:scroll-mt-16 sm:px-6 sm:py-14 lg:row-span-3 lg:grid lg:grid-rows-subgrid lg:items-stretch lg:bg-transparent lg:p-0"
    >
        <div class="relative w-full max-w-3xl text-center lg:contents">
            <div class="lg:text-left">
                <h2
                    class="font-coastal-heading text-3xl leading-tight font-medium sm:text-4xl"
                >
                    {{ localized(hotel.section_headings.location, locale) }}
                </h2>
                <p class="text-coast-muted mt-3 text-sm sm:text-base">
                    {{ t('locationIntro') }}
                </p>

                <div
                    class="text-coast-muted mt-3 flex items-center justify-center gap-2 text-sm lg:justify-start"
                >
                    <MapPin class="text-coast-blue size-5 shrink-0" />
                    {{ displayAddress }}
                </div>
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
                class="border-coast-line relative mt-5 overflow-hidden rounded-xl border bg-white shadow-sm sm:mt-6 lg:mt-0"
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
                    class="block h-64 w-full sm:h-80 lg:h-[clamp(19rem,48svh,34rem)]"
                    loading="lazy"
                    allowfullscreen
                ></iframe>
                <button
                    v-if="!mapUnlocked"
                    type="button"
                    class="bg-coast-deep/20 hover:bg-coast-deep/10 text-coast-ink absolute inset-0 flex cursor-pointer items-center justify-center text-sm font-medium backdrop-blur-[1px] transition"
                    @click="mapUnlocked = true"
                >
                    <span
                        class="border-coast-line rounded-full border bg-white px-4 py-2 shadow-sm"
                        >{{ t('tapToUnlockMap') }}</span
                    >
                </button>
            </div>

            <a
                :href="yandexOrgUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="border-coast-blue text-coast-blue hover:text-coast-ink mt-5 inline-flex items-center gap-2 border-b pb-1 text-sm font-semibold transition sm:mt-6 lg:mt-0 lg:justify-self-start"
            >
                <MapPin class="size-4" />
                {{ t('openInYandexMaps') }}
            </a>
        </div>
    </section>
</template>
