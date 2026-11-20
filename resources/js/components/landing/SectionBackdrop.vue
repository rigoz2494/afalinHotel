<script setup lang="ts">
// A faded photograph behind a navy gradient.
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
            class="from-coast-deep via-coast-deep/90 to-coast-blue/70 absolute inset-0 bg-gradient-to-br"
        ></div>
        <div
            class="from-coast-deep absolute inset-x-0 top-0 h-24 bg-gradient-to-b to-transparent sm:h-32"
        ></div>
        <div
            class="from-coast-deep absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t to-transparent sm:h-32"
        ></div>
    </div>
</template>
