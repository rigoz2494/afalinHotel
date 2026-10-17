<script setup lang="ts">
import { computed, ref } from 'vue';
import GalleryLightbox from '@/components/landing/GalleryLightbox.vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
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
    (['years', 'rooms', 'guests'] as const).map((key) => ({
        key,
        label: localized(props.hotel.about_stats[key].label, locale.value),
        value: localized(props.hotel.about_stats[key].value, locale.value),
    })),
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
        class="relative grid min-h-screen gap-6 overflow-hidden bg-stone-950 px-4 pt-24 pb-10 text-stone-100 sm:gap-8 sm:px-6 lg:h-screen lg:snap-start lg:grid-cols-5 lg:px-12 lg:pb-8"
    >
        <SectionBackdrop photo-id="photo-1571896349842-33c89424de2d" />
        <div class="relative z-10 flex flex-col justify-center lg:col-span-2">
            <p class="text-sm tracking-widest text-amber-300 uppercase">
                {{ t('navAbout') }}
            </p>
            <h2
                class="mt-3 text-3xl leading-tight font-semibold sm:text-4xl md:text-5xl"
            >
                {{ localized(hotel.section_headings.about, locale) }}
            </h2>
            <p
                v-for="(paragraph, index) in storyParagraphs"
                :key="index"
                class="text-sm text-stone-300 sm:text-base"
                :class="index === 0 ? 'mt-4 sm:mt-5' : 'mt-3 sm:mt-4'"
            >
                {{ paragraph }}
            </p>
            <dl
                class="mt-6 grid grid-cols-3 gap-3 text-center sm:mt-8 sm:gap-4"
            >
                <div
                    v-for="stat in stats"
                    :key="stat.key"
                    class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4"
                >
                    <dt class="text-xs text-stone-300">{{ stat.label }}</dt>
                    <dd
                        class="text-xl font-semibold text-amber-300 sm:text-2xl"
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

        <!--
            `lg` and up: a native CSS multi-column flow, not a fixed-row
            grid — each photo keeps its own natural aspect ratio and the
            columns stagger organically, Pinterest-style, instead of every
            photo being cropped to fit identical cells. `overflow-y-auto` is
            needed because the section is pinned to exactly one screen
            (`lg:h-screen` plus `overflow-hidden`) and a masonry column's
            total height isn't guaranteed to fit that.
        -->
        <div
            class="relative z-10 hidden gap-4 lg:col-span-3 lg:block lg:h-full lg:columns-3 lg:overflow-y-auto"
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
