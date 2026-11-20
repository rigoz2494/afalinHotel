<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Compass } from '@lucide/vue';
</script>

<template>
    <Head title="Page not found - Afalina" />

    <div
        class="bg-coast-deep relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-20 text-center text-white sm:px-6"
    >
        <div
            class="from-coast-deep via-coast-deep to-coast-blue/60 absolute inset-0 bg-linear-to-br"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 flex max-w-lg flex-col items-center">
            <img
                src="/images/afalina-logo.webp"
                alt=""
                class="bg-coast-paper mb-6 h-16 w-auto rounded-lg p-1.5"
            />

            <Compass
                :stroke-width="1.25"
                class="not-found-compass text-coast-tide mb-4 size-14 sm:size-16"
                aria-hidden="true"
            />

            <p
                class="text-coast-tide text-xs tracking-widest uppercase sm:text-sm"
            >
                Error 404
            </p>
            <h1
                class="font-coastal-heading mt-3 text-3xl tracking-tight sm:text-4xl md:text-5xl"
            >
                This page has sailed off the map
            </h1>
            <p class="mt-4 text-sm text-stone-300 sm:text-base">
                The page you are looking for does not exist, may have been
                moved, or the link may be out of date.
            </p>

            <Link
                href="/"
                class="not-found-cta group bg-coast-tide text-coast-ink mt-8 inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-medium transition hover:bg-sky-100 sm:text-base"
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
