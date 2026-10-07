import {
    computed,
    inject,
    type ComputedRef,
    type InjectionKey,
    onMounted,
    provide,
    ref,
} from 'vue';
import type { CurrencyOption } from '@/types/landing';

export type CurrencyContext = {
    currencies: ComputedRef<CurrencyOption[]>;
    selected: ComputedRef<CurrencyOption>;
    select: (code: string) => void;
    /** Converts a base-currency amount into the selected currency, in whole units. */
    convert: (baseAmount: number) => number;
    /** Formats a base-currency amount in the selected currency, e.g. "€1,234". */
    format: (baseAmount: number) => string;
    /** Formats an amount that is already in the selected currency. */
    formatConverted: (amount: number) => string;
};

const CURRENCY_KEY: InjectionKey<CurrencyContext> = Symbol('currency');
const STORAGE_KEY = 'hotel-currency';

// Used only when no currency has been configured in the admin panel.
const FALLBACK_CURRENCY: CurrencyOption = {
    code: 'USD',
    symbol: '$',
    symbol_position: 'before',
    exchange_rate: 1,
    is_base: true,
};

// Module-level formatter: created once, and it holds no per-user state, so it is
// safe to share during server-side rendering.
const amountFormatter = new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0,
});

/**
 * Provides the guest's currency choice to the whole landing page. It is created
 * in the page component, so the choice is per page instance and never shared
 * between server-rendered requests.
 */
export function provideCurrency(
    source: () => CurrencyOption[],
): CurrencyContext {
    const currencies = computed<CurrencyOption[]>(() => {
        const list = source();

        return list.length ? list : [FALLBACK_CURRENCY];
    });

    const baseCurrency = computed(
        () =>
            currencies.value.find((currency) => currency.is_base) ??
            currencies.value[0],
    );

    const selectedCode = ref<string>(baseCurrency.value.code);

    const selected = computed<CurrencyOption>(
        () =>
            currencies.value.find(
                (currency) => currency.code === selectedCode.value,
            ) ?? baseCurrency.value,
    );

    const select = (code: string): void => {
        selectedCode.value = code;

        try {
            localStorage.setItem(STORAGE_KEY, code);
        } catch {
            // Storage can be blocked (private mode); the choice still applies for this visit.
        }
    };

    // Restore the guest's last choice only after mount, so server and client
    // markup match on the first render.
    onMounted(() => {
        let stored: string | null = null;

        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch {
            stored = null;
        }

        if (stored && currencies.value.some((c) => c.code === stored)) {
            selectedCode.value = stored;
        }
    });

    // Placement is per currency (admin-configurable), not per locale: "$100"
    // versus "100 ₴" is a currency convention, independent of the UI language.
    const formatConverted = (amount: number): string => {
        const number = amountFormatter.format(amount);

        return selected.value.symbol_position === 'after'
            ? `${number} ${selected.value.symbol}`
            : `${selected.value.symbol}${number}`;
    };

    const convert = (baseAmount: number): number =>
        Math.round(baseAmount * selected.value.exchange_rate);

    const context: CurrencyContext = {
        currencies,
        selected,
        select,
        convert,
        format: (baseAmount) => formatConverted(convert(baseAmount)),
        formatConverted,
    };

    provide(CURRENCY_KEY, context);

    return context;
}

export function useCurrency(): CurrencyContext {
    const context = inject(CURRENCY_KEY);

    if (!context) {
        throw new Error('useCurrency() must be used inside the landing page.');
    }

    return context;
}
