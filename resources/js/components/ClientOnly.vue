<script setup lang="ts">
import { onMounted, ref } from 'vue';

// `<Teleport>` content is dropped entirely during SSR (Inertia's default
// server render calls `renderToString(app)` with no context argument, so
// `ctx.teleports` is never collected or injected into the response). The
// client then hydrates against nothing and immediately inserts real DOM
// for it, which Vue reports as a hydration mismatch — harmless, but noisy.
//
// Rendering nothing (a `v-if="false"` placeholder comment) on both the
// server AND the client's first paint keeps the two in sync, then reveals
// the real content right after mount as an ordinary reactive update
// instead of as part of hydration.
const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});
</script>

<template>
    <slot v-if="isMounted" />
</template>
