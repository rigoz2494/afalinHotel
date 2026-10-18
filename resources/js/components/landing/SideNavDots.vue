<script setup lang="ts">
import { useLocale } from '@/composables/useLocale';

defineProps<{
    links: { id: string; label: string }[];
    activeId: string;
}>();

defineEmits<{ navigate: [id: string] }>();

const { t } = useLocale();
</script>

<template>
    <nav
        class="fixed top-1/2 right-5 z-40 hidden -translate-y-1/2 flex-col items-center gap-4 md:flex"
        :aria-label="t('sectionNavigation')"
    >
        <button
            v-for="link in links"
            :key="link.id"
            type="button"
            class="group relative flex items-center justify-center p-1.5"
            :aria-label="t('goTo', { label: link.label })"
            :aria-current="activeId === link.id ? 'true' : undefined"
            @click="$emit('navigate', link.id)"
        >
            <span
                class="rounded-full bg-white/40 transition-all duration-300 group-hover:bg-amber-200"
                :class="
                    activeId === link.id ? 'size-2.5 bg-amber-300' : 'size-1.5'
                "
            ></span>
            <span
                class="pointer-events-none absolute right-full mr-3 rounded-md bg-slate-950/90 px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
            >
                {{ link.label }}
            </span>
        </button>
    </nav>
</template>
