<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Compass } from '@lucide/vue';
</script>

<template>
    <Head title="Page not found - Afalina" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-stone-950 px-4 py-20 text-center text-stone-100 sm:px-6"
    >
        <div
            class="absolute inset-0 bg-gradient-to-br from-stone-950 via-stone-950/95 to-amber-950/60"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 flex max-w-lg flex-col items-center">
            <img
                src="/images/hotel-logo.svg"
                alt=""
                class="mb-6 h-14 w-auto opacity-90"
            />

            <Compass
                :stroke-width="1.25"
                class="not-found-compass mb-4 size-14 text-amber-300 sm:size-16"
                aria-hidden="true"
            />

            <p
                class="text-xs tracking-widest text-amber-300/80 uppercase sm:text-sm"
            >
                Error 404
            </p>
            <h1
                class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl md:text-5xl"
            >
                This page has sailed off the map
            </h1>
            <p class="mt-4 text-sm text-stone-300 sm:text-base">
                The page you are looking for does not exist, may have been
                moved, or the link may be out of date.
            </p>

            <Link
                href="/"
                class="not-found-cta group mt-8 inline-flex items-center gap-2 rounded-full bg-amber-300 px-7 py-3 text-sm font-medium text-stone-900 transition hover:bg-amber-200 sm:text-base"
            >
                Back to the hotel
                <span
                    class="transition-transform duration-300 group-hover:translate-x-1"
                    aria-hidden="true"
                    >&rarr;</span
                >
            </Link>
        </div>
    </div>
</template>

<style scoped>
/* A slow breathing pulse, matching the preloader's logo animation. */
.not-found-compass {
    animation: not-found-breathe 3.2s ease-in-out infinite;
}

@keyframes not-found-breathe {
    0%,
    100% {
        opacity: 0.7;
        transform: rotate(-6deg) scale(0.97);
    }

    50% {
        opacity: 1;
        transform: rotate(6deg) scale(1.03);
    }
}

.not-found-cta {
    box-shadow: 0 0 0 0 rgb(252 211 77 / 0.5);
    animation: not-found-glow 2.4s ease-in-out infinite;
}

@keyframes not-found-glow {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgb(252 211 77 / 0.35);
    }

    50% {
        box-shadow: 0 0 0 10px rgb(252 211 77 / 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .not-found-compass,
    .not-found-cta {
        animation: none;
    }
}
</style>
