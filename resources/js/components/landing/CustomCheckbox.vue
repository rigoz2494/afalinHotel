<script setup lang="ts">
import { Check } from '@lucide/vue';

// A real <input type="checkbox"> underneath, fully unstyled
// (appearance-none) and stretched to fill this box — keyboard, label-click
// and screen-reader behavior all come for free that way, rather than
// reimplementing them on a styled div/button.
const checked = defineModel<boolean>({ required: true });
</script>

<template>
    <span
        class="relative inline-flex size-5 shrink-0 items-center justify-center rounded border border-white/25 bg-black/35 transition-all duration-200 has-checked:border-amber-300 has-checked:bg-amber-300"
    >
        <input
            v-model="checked"
            type="checkbox"
            class="absolute inset-0 size-full cursor-pointer appearance-none"
        />
        <Check
            class="pointer-events-none size-3.5 text-slate-900 transition-all duration-200"
            :class="checked ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"
        />
    </span>
</template>
