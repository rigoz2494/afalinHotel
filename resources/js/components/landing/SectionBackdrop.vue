<script setup lang="ts">
// The Pricing section's backdrop, shared by the other sections: a blurred,
// faded photo under a stone-to-amber gradient. The top and bottom fades meet
// stone-950 so neighbouring sections blend into each other while scrolling.
defineProps<{ photoId: string }>();
</script>

<template>
    <div
        class="pointer-events-none absolute inset-0 overflow-hidden"
        aria-hidden="true"
    >
        <div
            class="absolute inset-0 scale-110 bg-cover bg-center opacity-20 blur-sm"
            :style="{
                backgroundImage: `url('https://images.unsplash.com/${photoId}?auto=format&fit=crop&w=1800&q=70')`,
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
