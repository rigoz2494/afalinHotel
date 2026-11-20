<script setup lang="ts">
import { computed, ref } from 'vue';
import GalleryLightbox from '@/components/landing/GalleryLightbox.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { GalleryImage, HotelSettings } from '@/types/landing';

const props = defineProps<{ hotel: HotelSettings; images: GalleryImage[] }>();
const { t, locale } = useLocale();

// GalleryLightbox and this grid both just need a plain display string, not
// the raw {en, ru} pair — resolved once here, in the guest's own language.
// `tall` isn't needed here: that was only ever for the old fixed-row grid's
// row-span trick, and the masonry layout below flows each photo at its own
// natural height instead.
const displayImages = computed(() =>
    props.images.map((image) => ({
        src: image.src,
        alt: localized(image.alt, locale.value),
    })),
);

const lightboxIndex = ref<number | null>(null);

// The three marketing badges (e.g. "20+ Years") — label and value are both
// bilingual, set from Site Settings, not hardcoded on the page.
const stats = computed(() =>
    (['years', 'rooms', 'guests'] as const)
        .map((key) => ({
            key,
            label: localized(props.hotel.about_stats[key].label, locale.value),
            value: localized(props.hotel.about_stats[key].value, locale.value),
        }))
        .filter((stat) => stat.value),
);

// A blank line in the admin's free-text story field starts a new <p> on
// the page, the same way the old hardcoded two-paragraph bio read.
const storyParagraphs = computed(() =>
    localized(props.hotel.about_story, locale.value)
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);
</script>

<template>
    <section
        id="about"
        class="landing-screen bg-coast-paper text-coast-ink max-w-landing relative mx-auto grid scroll-mt-14 gap-6 px-4 py-10 sm:scroll-mt-16 sm:gap-8 sm:px-6 sm:py-14 lg:grid-cols-5 lg:items-center lg:px-12 2xl:grid-cols-[minmax(20rem,35rem)_minmax(0,1fr)]"
    >
        <div
            class="relative z-10 flex flex-col justify-center lg:col-span-2 2xl:col-span-1"
        >
            <p class="text-coast-blue text-sm tracking-widest uppercase">
                {{ t('navAbout') }}
            </p>
            <h2
                class="font-coastal-heading mt-3 text-3xl leading-tight font-medium sm:text-4xl md:text-5xl"
            >
                {{ localized(hotel.section_headings.about, locale) }}
            </h2>
            <p
                v-for="(paragraph, index) in storyParagraphs"
                :key="index"
                class="text-coast-muted text-sm sm:text-base"
                :class="index === 0 ? 'mt-4 sm:mt-5' : 'mt-3 sm:mt-4'"
            >
                {{ paragraph }}
            </p>
            <dl
                v-if="stats.length"
                class="mt-6 grid grid-cols-3 gap-3 text-center sm:mt-8 sm:gap-4"
            >
                <div
                    v-for="stat in stats"
                    :key="stat.key"
                    class="border-coast-line rounded-xl border bg-white p-3 sm:p-4"
                >
                    <dt class="text-coast-muted text-xs">{{ stat.label }}</dt>
                    <dd
                        class="text-coast-blue text-xl font-semibold sm:text-2xl"
                    >
                        {{ stat.value }}
                    </dd>
                </div>
            </dl>
        </div>

        <!--
            Below `lg`: a horizontal touch-swipe carousel, one photo mostly
            filling the viewport with a peek of the next — multi-column CSS
            masonry fills top-to-bottom per column, not in visual swipe
            order, which made scrolling past the first few photos feel like
            the layout was jumping around under the user's finger.
        -->
        <div
            class="relative z-10 -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 lg:hidden"
        >
            <button
                v-for="(image, index) in displayImages"
                :key="image.src"
                type="button"
                class="group block w-[78%] flex-none shrink-0 cursor-zoom-in snap-center overflow-hidden rounded-xl sm:w-[55%]"
                :aria-label="t('openPhoto', { alt: image.alt })"
                @click="lightboxIndex = index"
            >
                <img
                    :src="image.src"
                    :alt="image.alt"
                    loading="lazy"
                    decoding="async"
                    class="aspect-[4/5] h-auto w-full object-cover transition duration-500 group-hover:scale-105"
                />
            </button>
        </div>

        <div
            class="relative z-10 hidden gap-4 lg:col-span-3 lg:block lg:columns-3 2xl:col-span-1 2xl:columns-4"
        >
            <button
                v-for="(image, index) in displayImages"
                :key="image.src"
                type="button"
                class="group mb-4 block w-full cursor-zoom-in break-inside-avoid overflow-hidden rounded-xl"
                :aria-label="t('openPhoto', { alt: image.alt })"
                @click="lightboxIndex = index"
            >
                <img
                    :src="image.src"
                    :alt="image.alt"
                    loading="lazy"
                    decoding="async"
                    class="h-auto w-full object-contain transition duration-500 group-hover:scale-105"
                />
            </button>
        </div>

        <GalleryLightbox v-model="lightboxIndex" :images="displayImages" />
    </section>
</template>
