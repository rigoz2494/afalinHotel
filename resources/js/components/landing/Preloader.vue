<script setup lang="ts">
import { usePreloader } from '@/composables/usePreloader';
import { useLocale } from '@/composables/useLocale';

const { isLoading, releaseInput } = usePreloader();
const { t } = useLocale();
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
            <img
                src="/images/hotel-logo.svg"
                alt=""
                class="preloader-logo relative h-24 w-auto sm:h-32 md:h-40"
            />
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
