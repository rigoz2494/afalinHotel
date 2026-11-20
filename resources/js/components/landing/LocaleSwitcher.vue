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
            class="coast-light-select border-coast-line text-coast-ink hover:border-coast-blue/50 hover:bg-coast-mist focus:border-coast-blue appearance-none rounded-lg border bg-white py-1.5 pr-7 pl-3 text-xs font-medium transition focus:outline-none sm:text-sm"
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
            class="text-coast-muted pointer-events-none absolute right-2 size-3.5"
            aria-hidden="true"
        />
    </div>
</template>
