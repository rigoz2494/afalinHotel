<script setup lang="ts">
import { Gift } from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

const props = defineProps<{ hotel: HotelSettings }>();
const { t } = useLocale();

const activeSlide = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    if (props.hotel.hero_images.length > 1) {
        timer = setInterval(() => {
            activeSlide.value =
                (activeSlide.value + 1) % props.hotel.hero_images.length;
        }, 5000);
    }
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <section
        id="hero"
        class="relative flex min-h-screen snap-start items-center justify-center overflow-hidden bg-slate-900 lg:h-screen"
    >
        <!-- Slideshow layers: stacked, cross-fading via opacity. -->
        <div class="absolute inset-0 z-0">
            <img
                v-for="(src, index) in hotel.hero_images"
                :key="src"
                :src="src"
                alt=""
                :loading="index === 0 ? 'eager' : 'lazy'"
                decoding="async"
                class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000"
                :class="index === activeSlide ? 'opacity-100' : 'opacity-0'"
            />
        </div>

        <!-- Dimming overlay: always above the slideshow, below the content. -->
        <div class="absolute inset-0 z-10 bg-black/60"></div>

        <div
            class="animate-in fade-in slide-in-from-bottom-6 relative z-20 max-w-3xl px-4 py-24 text-center text-white duration-1000 sm:px-6"
        >
            <img
                src="/images/hotel-logo.svg"
                alt=""
                class="mx-auto mb-6 h-auto w-48 drop-shadow-lg sm:w-64 md:w-80"
            />
            <h1
                class="text-3xl font-semibold tracking-tight sm:text-5xl md:text-7xl"
            >
                {{ hotel.hotel_name }}
            </h1>
            <p
                class="mt-3 text-base text-white/85 sm:mt-6 sm:text-lg md:text-2xl"
            >
                {{ hotel.tagline }}
            </p>
            <p
                v-if="hotel.promo_banner"
                class="mx-auto mt-6 flex max-w-xl items-center justify-center gap-2 rounded-full border border-amber-300/40 bg-amber-300/15 px-4 py-1.5 text-xs text-amber-100 backdrop-blur sm:mt-8 sm:px-5 sm:py-2 sm:text-sm"
            >
                <Gift class="size-4 shrink-0 text-amber-300" />
                {{ hotel.promo_banner }}
            </p>
            <a
                href="#rooms"
                class="mt-6 inline-block rounded-full bg-white px-6 py-2.5 text-sm font-medium text-slate-900 transition hover:bg-white/90 sm:mt-8 sm:px-8 sm:py-3 sm:text-base"
            >
                {{ t('viewRooms') }}
            </a>
        </div>
    </section>
</template>
