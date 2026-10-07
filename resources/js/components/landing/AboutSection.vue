<script setup lang="ts">
import { ref } from 'vue';
import GalleryLightbox from '@/components/landing/GalleryLightbox.vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { localized, useLocale } from '@/composables/useLocale';
import { galleryImages } from '@/data/landingMock';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();

const lightboxIndex = ref<number | null>(null);
</script>

<template>
    <section
        id="about"
        class="relative grid min-h-screen snap-start gap-6 overflow-hidden bg-stone-950 px-4 pt-24 pb-10 text-stone-100 sm:gap-8 sm:px-6 lg:h-screen lg:grid-cols-5 lg:px-12 lg:pb-8"
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
            <p class="mt-4 text-sm text-stone-300 sm:mt-5 sm:text-base">
                {{
                    t('aboutBioOne', {
                        hotel: localized(hotel.hotel_name, locale),
                    })
                }}
            </p>
            <p class="mt-3 text-sm text-stone-300 sm:mt-4 sm:text-base">
                {{ t('aboutBioTwo') }}
            </p>
            <dl
                class="mt-6 grid grid-cols-3 gap-3 text-center sm:mt-8 sm:gap-4"
            >
                <div
                    class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4"
                >
                    <dt class="text-xs text-stone-300">{{ t('statYears') }}</dt>
                    <dd
                        class="text-xl font-semibold text-amber-300 sm:text-2xl"
                    >
                        20+
                    </dd>
                </div>
                <div
                    class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4"
                >
                    <dt class="text-xs text-stone-300">{{ t('statRooms') }}</dt>
                    <dd
                        class="text-xl font-semibold text-amber-300 sm:text-2xl"
                    >
                        48
                    </dd>
                </div>
                <div
                    class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4"
                >
                    <dt class="text-xs text-stone-300">
                        {{ t('statGuests') }}
                    </dt>
                    <dd
                        class="text-xl font-semibold text-amber-300 sm:text-2xl"
                    >
                        30k
                    </dd>
                </div>
            </dl>
        </div>

        <div
            class="relative z-10 grid auto-rows-[140px] grid-cols-2 gap-3 sm:auto-rows-[170px] lg:col-span-3 lg:h-full lg:auto-rows-auto lg:grid-cols-3 lg:grid-rows-2"
        >
            <button
                v-for="(image, index) in galleryImages"
                :key="image.src"
                type="button"
                class="group relative min-h-0 cursor-zoom-in overflow-hidden rounded-xl"
                :class="image.tall ? 'row-span-2' : ''"
                :aria-label="t('openPhoto', { alt: image.alt })"
                @click="lightboxIndex = index"
            >
                <img
                    :src="image.src"
                    :alt="image.alt"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <span
                    class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2 text-left text-xs text-white sm:p-3 sm:text-sm"
                >
                    {{ image.alt }}
                </span>
            </button>
        </div>

        <GalleryLightbox v-model="lightboxIndex" :images="galleryImages" />
    </section>
</template>
