<script setup lang="ts">
import { ref } from 'vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();

// Real guest reviews, live from the hotel's verified Yandex Business
// listing (org id 12100073003) - not written here, since fabricated
// testimonials on a real hotel's site would be actively dishonest.
const yandexOrgUrl = 'https://yandex.ru/maps/org/afalina/12100073003/reviews/';
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
        class="bg-coast-mist text-coast-ink relative flex scroll-mt-14 flex-col items-center px-4 py-10 sm:scroll-mt-16 sm:px-6 sm:py-14 lg:row-span-3 lg:grid lg:grid-rows-subgrid lg:items-stretch lg:bg-transparent lg:p-0"
    >
        <div class="relative w-full max-w-2xl text-center lg:contents">
            <div class="lg:text-left">
                <h2
                    class="font-coastal-heading text-3xl leading-tight font-medium sm:text-4xl"
                >
                    {{ localized(hotel.section_headings.reviews, locale) }}
                </h2>
                <p class="text-coast-muted mt-3 text-sm sm:text-base">
                    {{ t('reviewsIntro') }}
                </p>
            </div>

            <div
                class="border-coast-line relative mt-5 overflow-hidden rounded-xl border bg-white shadow-sm sm:mt-6 lg:mt-0"
            >
                <iframe
                    :src="yandexReviewsWidgetSrc"
                    :title="t('reviewsWidgetTitle')"
                    class="block h-[70dvh] w-full sm:h-[65dvh] lg:h-[clamp(19rem,48svh,34rem)]"
                    loading="lazy"
                ></iframe>
                <button
                    v-if="!reviewsUnlocked"
                    type="button"
                    class="bg-coast-deep/20 hover:bg-coast-deep/10 absolute inset-0 flex cursor-pointer items-center justify-center text-sm font-medium text-stone-900 backdrop-blur-[1px] transition"
                    @click="reviewsUnlocked = true"
                >
                    <span
                        class="border-coast-line rounded-full border bg-white px-4 py-2 shadow-sm"
                        >{{ t('tapToUnlockReviews') }}</span
                    >
                </button>
            </div>
            <a
                :href="yandexOrgUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="border-coast-blue text-coast-blue hover:text-coast-ink mt-5 inline-block border-b pb-1 text-sm font-semibold transition sm:mt-6 lg:mt-0 lg:justify-self-start"
            >
                {{
                    locale === 'ru'
                        ? 'Читать отзывы в Яндекс Картах'
                        : 'Read reviews on Yandex Maps'
                }}
            </a>
        </div>
    </section>
</template>
