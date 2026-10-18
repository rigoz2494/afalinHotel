<script setup lang="ts">
import { ref } from 'vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();

// Real guest reviews, live from the hotel's verified Yandex Business
// listing (org id 12100073003) - not written here, since fabricated
// testimonials on a real hotel's site would be actively dishonest.
const yandexOrgUrl = 'https://yandex.ru/maps/org/afalina/12100073003/';
const yandexReviewsWidgetSrc =
    'https://yandex.ru/maps-reviews-widget/12100073003?comments';

// Same reasoning as LocationSection's map overlay: this is a cross-origin
// iframe with its own internal scroll (the comment list), which can
// swallow a swipe meant for the page instead. Tap-to-unlock first.
const reviewsUnlocked = ref(false);
</script>

<template>
    <section
        id="reviews"
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-14 text-stone-100 sm:px-6 lg:h-full lg:py-0 lg:pt-16"
    >
        <SectionBackdrop name="reviews" />
        <div
            class="relative z-10 w-full max-w-2xl text-center lg:max-h-full lg:max-w-md lg:overflow-y-auto lg:py-2"
        >
            <p class="text-sm tracking-widest text-amber-300 uppercase">
                {{ t('navReviews') }}
            </p>
            <h2
                class="mt-3 text-3xl leading-tight font-semibold sm:text-4xl md:text-5xl"
            >
                {{ localized(hotel.section_headings.reviews, locale) }}
            </h2>
            <p class="mt-3 text-sm text-stone-300 sm:text-base">
                {{ t('reviewsIntro') }}
            </p>

            <!--
                Yandex's own review widget for the hotel's verified Business
                listing - real, live guest reviews, not anything written
                here. The widget itself is a light card (Yandex's own
                styling, not restylable from here), so it's framed to read
                as an intentional bright highlight against the dark section
                rather than a clashing default embed.
            -->
            <div
                class="relative mt-5 overflow-hidden rounded-xl border border-white/10 bg-white shadow-2xl sm:mt-6"
            >
                <iframe
                    :src="yandexReviewsWidgetSrc"
                    :title="t('reviewsWidgetTitle')"
                    class="h-[48vh] w-full sm:h-[52vh] lg:h-[55vh]"
                    loading="lazy"
                ></iframe>
                <button
                    v-if="!reviewsUnlocked"
                    type="button"
                    class="absolute inset-0 flex cursor-pointer items-center justify-center bg-stone-950/20 text-sm font-medium text-stone-900 backdrop-blur-[1px] transition hover:bg-stone-950/10"
                    @click="reviewsUnlocked = true"
                >
                    <span class="rounded-full bg-white px-4 py-2 shadow-lg">{{
                        t('tapToUnlockReviews')
                    }}</span>
                </button>
            </div>
            <a
                :href="yandexOrgUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-2 inline-block text-xs text-white/40 hover:text-white/70"
            >
                {{
                    locale === 'ru'
                        ? 'Афалина на карте Феодосии — Яндекс Карты'
                        : 'Afalina on the map of Feodosia — Yandex Maps'
                }}
            </a>
        </div>
    </section>
</template>
