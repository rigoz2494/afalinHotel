<script setup lang="ts">
// A blurred, faded photo under a stone-to-amber gradient, shared by every
// section that uses one. The top and bottom fades meet stone-950 so
// neighbouring sections blend into each other while scrolling.
//
// `name` maps to a fixed local file in public/images/backdrops/ (e.g.
// "about" -> /images/backdrops/about.webp) - never a live fetch from
// Unsplash or anywhere else. These are curated design assets, not
// admin-editable content, so unlike uploads they're committed as plain
// static files with stable names instead of going through
// ImageOptimizer/Storage's ULID-named uploads pipeline.
defineProps<{ name: string }>();
</script>

<template>
    <div
        class="pointer-events-none absolute inset-0 overflow-hidden"
        aria-hidden="true"
    >
        <div
            class="absolute inset-0 scale-110 bg-cover bg-center opacity-20 blur-sm"
            :style="{
                backgroundImage: `url('/images/backdrops/${name}.webp')`,
            }"
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-br from-stone-950 via-stone-950/90 to-amber-950/70"
        ></div>
        <div
            class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-stone-950 to-transparent sm:h-32"
        ></div>
        <div
            class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-stone-950 to-transparent sm:h-32"
        ></div>
    </div>
</template>
