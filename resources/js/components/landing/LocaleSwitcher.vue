<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { useLocale } from '@/composables/useLocale';
import type { Locale } from '@/composables/useLocale';

const { locale, locales, setLocale, t } = useLocale();

const onChange = (event: Event): void => {
    setLocale((event.target as HTMLSelectElement).value as Locale);
};
</script>

<template>
    <div class="relative inline-flex shrink-0 items-center">
        <select
            :value="locale"
            :aria-label="t('languageLabel')"
            class="appearance-none rounded-full border border-white/20 bg-white/10 py-1.5 pr-7 pl-3 text-xs font-medium text-white transition hover:border-amber-300/50 hover:bg-amber-300/10 focus:border-amber-300 focus:outline-none sm:text-sm"
            @change="onChange"
        >
            <option
                v-for="option in locales"
                :key="option.code"
                :value="option.code"
            >
                {{ option.label }}
            </option>
        </select>
        <ChevronDown
            class="pointer-events-none absolute right-2 size-3.5 text-white/70"
            aria-hidden="true"
        />
    </div>
</template>
