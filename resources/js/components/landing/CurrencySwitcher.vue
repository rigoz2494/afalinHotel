<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { useCurrency } from '@/composables/useCurrency';
import { useLocale } from '@/composables/useLocale';

const { currencies, selected, select } = useCurrency();
const { t } = useLocale();

const onChange = (event: Event): void => {
    select((event.target as HTMLSelectElement).value);
};
</script>

<template>
    <div class="relative inline-flex shrink-0 items-center">
        <select
            :value="selected.code"
            :aria-label="t('currencyLabel')"
            class="coast-light-select border-coast-line text-coast-ink hover:border-coast-blue/50 hover:bg-coast-mist focus:border-coast-blue appearance-none rounded-lg border bg-white py-1.5 pr-7 pl-3 text-xs font-medium transition focus:outline-none sm:text-sm"
            @change="onChange"
        >
            <option
                v-for="currency in currencies"
                :key="currency.code"
                :value="currency.code"
            >
                {{ currency.code }} {{ currency.symbol }}
            </option>
        </select>
        <ChevronDown
            class="text-coast-muted pointer-events-none absolute right-2 size-3.5"
            aria-hidden="true"
        />
    </div>
</template>
