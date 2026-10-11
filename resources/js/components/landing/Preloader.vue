<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { usePreloader } from '@/composables/usePreloader';
import { useHotelSettings } from '@/composables/useHotelSettings';
import { useLocale } from '@/composables/useLocale';

const { isLoading, releaseInput } = usePreloader();
const { t, locale } = useLocale();
const hotel = useHotelSettings();

// Witty, premium micro-copy — purely decorative, never a real progress
// report — cycled underneath the logo while the preloader is up. Admin-
// managed (Site Settings → Preloader Loading Phrases), not hardcoded.
const microCopy = computed(() => hotel.value.preloader_phrases);
const COPY_INTERVAL_MS = 1800;

// A random index, never the same one twice in a row — so a reload doesn't
// always greet guests with the same opening phrase, and the loop doesn't
// visibly repeat itself every cycle.
const randomCopyIndex = (exclude?: number): number => {
    if (microCopy.value.length <= 1) {
        return 0;
    }

    let next = Math.floor(Math.random() * microCopy.value.length);

    while (next === exclude) {
        next = Math.floor(Math.random() * microCopy.value.length);
    }

    return next;
};

const copyIndex = ref(randomCopyIndex());
const currentCopy = computed(
    () => microCopy.value[copyIndex.value]?.[locale.value] ?? '',
);

let copyTimer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    // A guest who asked for less motion gets one calm phrase, not a cycle.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    copyTimer = setInterval(() => {
        copyIndex.value = randomCopyIndex(copyIndex.value);
    }, COPY_INTERVAL_MS);
});

onBeforeUnmount(() => {
    clearInterval(copyTimer);
});
</script>

<template>
    <Transition
        leave-active-class="transition-opacity duration-700 ease-out"
        leave-to-class="opacity-0"
        @after-leave="releaseInput"
    >
        <div
            v-if="isLoading"
            role="status"
            :aria-label="t('loading')"
            class="fixed inset-0 z-[100] flex touch-none items-center justify-center overflow-hidden bg-stone-950"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-stone-950 via-stone-950 to-amber-950/60"
                aria-hidden="true"
            ></div>
            <div class="relative flex flex-col items-center gap-5 sm:gap-6">
                <img
                    src="/images/hotel-logo.svg"
                    alt=""
                    class="preloader-logo h-24 w-auto sm:h-32 md:h-40"
                />
                <Transition
                    mode="out-in"
                    enter-active-class="transition-opacity duration-500 ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-500 ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        :key="copyIndex"
                        class="px-6 text-center text-xs font-medium tracking-[0.2em] text-amber-200/80 uppercase sm:text-sm"
                    >
                        {{ currentCopy }}
                    </p>
                </Transition>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* A slow breathing pulse with a warm gold glow, rather than a spinner. */
.preloader-logo {
    animation: preloader-breathe 2.8s ease-in-out infinite;
}

@keyframes preloader-breathe {
    0%,
    100% {
        opacity: 0.55;
        transform: scale(0.97);
        filter: drop-shadow(0 0 0 rgb(217 165 65 / 0));
    }

    50% {
        opacity: 1;
        transform: scale(1.03);
        filter: drop-shadow(0 0 24px rgb(217 165 65 / 0.45));
    }
}

@media (prefers-reduced-motion: reduce) {
    .preloader-logo {
        animation: none;
    }
}
</style>
