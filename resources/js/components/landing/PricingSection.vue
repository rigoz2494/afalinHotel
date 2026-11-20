<script setup lang="ts">
import { useEventListener } from '@vueuse/core';
import { ChevronDown, Percent, Minus, Plus, Sparkles, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ClientOnly from '@/components/ClientOnly.vue';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useCurrency } from '@/composables/useCurrency';
import { localized, useLocale } from '@/composables/useLocale';
import {
    cellPrice,
    isDeal,
    isPriceOverride,
    percentOffBase,
} from '@/composables/usePricingDisplay';
import type {
    AdditionalService,
    HotelSettings,
    PricingTable,
} from '@/types/landing';

const props = defineProps<{
    hotel: HotelSettings;
    pricing: PricingTable;
    additionalServices: AdditionalService[];
}>();

const { quantityFor, add, decrement } = useBookingBasket();
const { format } = useCurrency();
const { t, locale } = useLocale();

const servicesOpen = ref(false);

// Mobile/tablet (below lg) show one season at a time, picked here, instead
// of every column squeezed into the same cramped row — `selectedColumn`
// falls back to the first season whenever nothing's been picked yet.
const selectedPeriodId = ref<number | null>(null);
const selectedColumn = computed(
    () =>
        props.pricing.columns.find(
            (column) => column.id === selectedPeriodId.value,
        ) ?? props.pricing.columns[0],
);

useEventListener('keydown', (event: KeyboardEvent) => {
    if (servicesOpen.value && event.key === 'Escape') {
        servicesOpen.value = false;
    }
});
</script>

<template>
    <section
        id="pricing"
        class="landing-screen bg-coast-paper text-coast-ink relative flex scroll-mt-14 items-center justify-center px-4 py-10 sm:scroll-mt-16 sm:px-6 sm:py-14 lg:px-12"
    >
        <div
            class="max-w-landing relative mx-auto flex w-full flex-col gap-4 sm:gap-6"
        >
            <div>
                <Sparkles class="text-coast-blue size-5 sm:size-7 lg:size-6" />
                <h2
                    class="font-coastal-heading mt-1.5 text-xl leading-tight font-medium sm:mt-3 sm:text-3xl md:text-5xl lg:mt-1 lg:text-3xl"
                >
                    {{ localized(hotel.section_headings.pricing, locale) }}
                </h2>
                <p
                    class="text-coast-muted mt-1 text-xs sm:mt-3 sm:text-base lg:mt-1 lg:text-sm"
                >
                    {{ t('pricingIntro') }}
                </p>
            </div>

            <div class="min-w-0">
                <!--
                    Mobile/tablet (below lg): no horizontal multi-column
                    table at all — a single period picked here, and one
                    compact card per room type below, each with exactly
                    that period's price. Forcing every season and every
                    room into one cramped grid is what made this unreadable
                    on a phone; one season at a time, one card per room,
                    is the fix.
                -->
                <div v-if="selectedColumn" class="lg:hidden">
                    <label
                        class="text-coast-blue block text-xs tracking-widest uppercase"
                    >
                        {{ t('selectPeriodLabel') }}
                        <div class="relative mt-1.5">
                            <select
                                :value="selectedColumn?.id ?? ''"
                                class="coast-light-select border-coast-line text-coast-ink hover:border-coast-blue/50 focus:border-coast-blue block w-full appearance-none rounded-lg border bg-white px-3 py-2 pr-9 text-sm font-normal tracking-normal normal-case transition outline-none"
                                @change="
                                    selectedPeriodId =
                                        Number(
                                            ($event.target as HTMLSelectElement)
                                                .value,
                                        ) || null
                                "
                            >
                                <option
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    :value="column.id"
                                >
                                    {{ column.label }}
                                </option>
                            </select>
                            <ChevronDown
                                class="text-coast-blue pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2"
                                aria-hidden="true"
                            />
                        </div>
                    </label>

                    <ul class="mt-3 space-y-2">
                        <li
                            v-for="row in pricing.rows"
                            :key="row.room_id"
                            class="border-coast-line rounded-xl border bg-white p-3"
                        >
                            <p class="truncate text-sm font-semibold">
                                {{ localized(row.room_name, locale) }}
                            </p>

                            <div
                                class="mt-3 flex items-center justify-between gap-3"
                            >
                                <!--
                                    Case C (override): the admin set a literal,
                                    non-numeric price_override text instead of a
                                    number — there's no single price here to add to
                                    the basket, so this card has no Select button,
                                    just the text itself.
                                -->
                                <span
                                    v-if="isPriceOverride(row, selectedColumn)"
                                    class="text-coast-ink text-base"
                                    >{{ row.prices[selectedColumn.id] }}</span
                                >
                                <!--
                                    Case B (deal): the final price is below the room's
                                    base price. Show the base price struck through next
                                    to the lower final price, with an offer badge.
                                -->
                                <div
                                    v-else-if="isDeal(row, selectedColumn)"
                                    class="flex min-w-0 flex-wrap items-center gap-1.5"
                                >
                                    <span
                                        class="text-coast-muted text-xs line-through"
                                        >{{
                                            format(Number(row.base_price))
                                        }}</span
                                    >
                                    <span
                                        class="text-coast-blue text-base font-semibold"
                                    >
                                        {{
                                            format(
                                                cellPrice(row, selectedColumn),
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="bg-coast-blue inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold text-white"
                                        :title="
                                            t('percentOff', {
                                                pct: percentOffBase(
                                                    row,
                                                    selectedColumn,
                                                ),
                                            })
                                        "
                                    >
                                        <Percent class="size-2.5" />{{
                                            percentOffBase(row, selectedColumn)
                                        }}
                                    </span>
                                </div>
                                <!--
                                    Case A (flat): the final price is at or above the
                                    base price — a peak-season rate, with no promo.
                                -->
                                <span v-else class="text-coast-ink text-base">
                                    {{ format(cellPrice(row, selectedColumn)) }}
                                </span>

                                <!--
                                    An override cell (Case C above) has no single
                                    price to add to the basket, so it gets no
                                    Select/stepper control at all — just the text.
                                -->
                                <template
                                    v-if="!isPriceOverride(row, selectedColumn)"
                                >
                                    <button
                                        v-if="
                                            quantityFor(
                                                row.room_id,
                                                selectedColumn.label,
                                            ) === 0
                                        "
                                        type="button"
                                        class="bg-coast-blue hover:bg-coast-ink inline-flex shrink-0 items-center gap-1 rounded-full px-4 py-2 text-xs font-semibold whitespace-nowrap text-white transition"
                                        @click="
                                            add(
                                                row.room_id,
                                                localized(
                                                    row.room_name,
                                                    locale,
                                                ),
                                                selectedColumn.label,
                                                cellPrice(row, selectedColumn),
                                            )
                                        "
                                    >
                                        {{ t('select') }}
                                        <Plus class="size-3.5" />
                                    </button>
                                    <div
                                        v-else
                                        class="bg-coast-mist flex shrink-0 items-center gap-1.5 rounded-full p-1"
                                    >
                                        <button
                                            type="button"
                                            :aria-label="t('removeOne')"
                                            class="bg-coast-mist hover:bg-coast-line flex size-6 items-center justify-center rounded-full transition"
                                            @click="
                                                decrement(
                                                    row.room_id,
                                                    selectedColumn.label,
                                                )
                                            "
                                        >
                                            <Minus class="size-3" />
                                        </button>
                                        <span
                                            class="min-w-4 text-center text-sm font-semibold"
                                        >
                                            {{
                                                quantityFor(
                                                    row.room_id,
                                                    selectedColumn.label,
                                                )
                                            }}
                                        </span>
                                        <button
                                            type="button"
                                            :aria-label="t('addOneMore')"
                                            class="bg-coast-blue hover:bg-coast-ink flex size-6 items-center justify-center rounded-full text-white transition"
                                            @click="
                                                add(
                                                    row.room_id,
                                                    localized(
                                                        row.room_name,
                                                        locale,
                                                    ),
                                                    selectedColumn.label,
                                                    cellPrice(
                                                        row,
                                                        selectedColumn,
                                                    ),
                                                )
                                            "
                                        >
                                            <Plus class="size-3" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Desktop (lg+): the full season × room matrix, unchanged. -->
                <div
                    class="border-coast-line hidden rounded-2xl border bg-white shadow-sm backdrop-blur lg:block"
                >
                    <table class="w-full text-left">
                        <thead
                            class="text-coast-blue text-xs tracking-widest uppercase"
                        >
                            <tr class="border-coast-line border-b">
                                <th class="px-6 py-1.5 font-medium">
                                    {{ t('roomColumn') }}
                                </th>
                                <th
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    class="px-6 py-1.5 text-center font-medium"
                                >
                                    {{ column.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-coast-line divide-y">
                            <tr
                                v-for="row in pricing.rows"
                                :key="row.room_id"
                                class="hover:bg-coast-mist/60 transition"
                            >
                                <td
                                    class="px-6 py-2 align-middle text-base font-medium"
                                >
                                    {{ localized(row.room_name, locale) }}
                                </td>
                                <td
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    class="px-3 py-1.5 align-top"
                                >
                                    <!--
                                        A fixed min-height (`min-h-16`), not `h-full`: a
                                        percentage height on a flex child doesn't reliably
                                        resolve against a <td>'s row-equalized height across
                                        browsers. Tall enough for the deal case (struck-through
                                        price + final price + badge, possibly wrapped to two
                                        lines) plus the button/stepper below it, so every cell
                                        in the row ends up the same height, and `justify-between`
                                        reliably locks every button to the same line.
                                    -->
                                    <div
                                        class="flex min-h-[clamp(4rem,7.5svh,5.5rem)] flex-col items-start justify-between gap-1"
                                    >
                                        <!--
                                            Case C (override): the admin set a literal,
                                            non-numeric price_override text instead of a
                                            number — there's no single price here to add to
                                            the basket, so this cell has no Select button,
                                            just the text itself.
                                        -->
                                        <span
                                            v-if="isPriceOverride(row, column)"
                                            class="text-coast-ink text-base"
                                            >{{ row.prices[column.id] }}</span
                                        >
                                        <!--
                                            Case B (deal): the final price is below the room's
                                            base price — from a season markdown, a promo, or
                                            both. Show the base price struck through next to
                                            the lower final price, with an offer badge.
                                        -->
                                        <template
                                            v-else-if="isDeal(row, column)"
                                        >
                                            <div
                                                class="flex flex-wrap items-center gap-1.5"
                                            >
                                                <span
                                                    class="text-coast-muted text-sm line-through"
                                                    >{{
                                                        format(
                                                            Number(
                                                                row.base_price,
                                                            ),
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    class="text-coast-blue text-base font-semibold"
                                                >
                                                    {{
                                                        format(
                                                            cellPrice(
                                                                row,
                                                                column,
                                                            ),
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    class="bg-coast-blue inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold text-white"
                                                    :title="
                                                        t('percentOff', {
                                                            pct: percentOffBase(
                                                                row,
                                                                column,
                                                            ),
                                                        })
                                                    "
                                                >
                                                    <Percent
                                                        class="size-2.5"
                                                    />{{
                                                        percentOffBase(
                                                            row,
                                                            column,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </template>
                                        <!--
                                            Case A (flat): the final price is at or above the
                                            base price — a peak-season rate, with no promo. Shown
                                            as a plain price: no strikethrough, no "markup" note.
                                        -->
                                        <span
                                            v-else
                                            class="text-coast-ink text-base"
                                            >{{
                                                format(cellPrice(row, column))
                                            }}</span
                                        >

                                        <!--
                                            An override cell (Case C above) has no single
                                            price to add to the basket, so it gets no
                                            Select/stepper control at all — just the text.
                                        -->
                                        <template
                                            v-if="!isPriceOverride(row, column)"
                                        >
                                            <button
                                                v-if="
                                                    quantityFor(
                                                        row.room_id,
                                                        column.label,
                                                    ) === 0
                                                "
                                                type="button"
                                                class="bg-coast-blue hover:bg-coast-ink inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap text-white transition"
                                                @click="
                                                    add(
                                                        row.room_id,
                                                        localized(
                                                            row.room_name,
                                                            locale,
                                                        ),
                                                        column.label,
                                                        cellPrice(row, column),
                                                    )
                                                "
                                            >
                                                {{ t('select') }}
                                                <Plus class="size-3" />
                                            </button>
                                            <div
                                                v-else
                                                class="bg-coast-mist flex shrink-0 items-center gap-1.5 rounded-full p-1"
                                            >
                                                <button
                                                    type="button"
                                                    :aria-label="t('removeOne')"
                                                    class="bg-coast-mist hover:bg-coast-line flex size-5 items-center justify-center rounded-full transition"
                                                    @click="
                                                        decrement(
                                                            row.room_id,
                                                            column.label,
                                                        )
                                                    "
                                                >
                                                    <Minus class="size-2.5" />
                                                </button>
                                                <span
                                                    class="min-w-4 text-center text-xs font-semibold"
                                                >
                                                    {{
                                                        quantityFor(
                                                            row.room_id,
                                                            column.label,
                                                        )
                                                    }}
                                                </span>
                                                <button
                                                    type="button"
                                                    :aria-label="
                                                        t('addOneMore')
                                                    "
                                                    class="bg-coast-blue hover:bg-coast-ink flex size-5 items-center justify-center rounded-full text-white transition"
                                                    @click="
                                                        add(
                                                            row.room_id,
                                                            localized(
                                                                row.room_name,
                                                                locale,
                                                            ),
                                                            column.label,
                                                            cellPrice(
                                                                row,
                                                                column,
                                                            ),
                                                        )
                                                    "
                                                >
                                                    <Plus class="size-2.5" />
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Below lg: still a button opening the drawer (not much
                room to spare on a phone/tablet). At lg and up: there's no
                need to hide this behind a click at all, so the same list
                shows inline instead - it also gives the section real
                content to fill out that space with, rather than a mostly
                empty screen below the table on a wide monitor. -->
                <div
                    v-if="additionalServices.length"
                    class="mt-3 hidden lg:block"
                >
                    <h3
                        class="text-coast-blue text-xs font-semibold tracking-widest uppercase"
                    >
                        {{ t('additionalServicesTitle') }}
                    </h3>
                    <ul
                        class="divide-coast-line border-coast-line mt-2 divide-y rounded-xl border bg-white"
                    >
                        <li
                            v-for="service in additionalServices"
                            :key="service.id"
                            class="flex items-center justify-between gap-4 px-4 py-2 text-sm"
                        >
                            <span class="text-coast-ink">{{
                                localized(service.name, locale)
                            }}</span>
                            <span class="text-coast-blue font-semibold">{{
                                format(service.price)
                            }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Directly beneath the grid/cards, centered — a sibling
                inside this same scrollable area rather than a separate flex
                item of the outer column, so its position tracks the
                table/cards' own height instead of always sitting pinned to
                the section's bottom edge regardless of how tall they are. -->
                <div class="mt-3 flex justify-center lg:hidden">
                    <button
                        type="button"
                        class="border-coast-blue/50 text-coast-blue hover:bg-coast-mist inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition sm:gap-2 sm:px-5 sm:py-2.5 sm:text-sm"
                        @click="servicesOpen = true"
                    >
                        <Sparkles class="size-3.5 sm:size-4" />
                        {{ t('additionalServicesButton') }}
                    </button>
                </div>
            </div>
        </div>

        <ClientOnly>
            <Teleport to="body">
                <!-- A uniform, full-viewport dim — a separate element from
                the drawer panel below, not a flex wrapper around it, so the
                dimming never reads as a floating box with empty space
                around it. -->
                <Transition
                    enter-active-class="transition-opacity duration-500 ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-500 ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="servicesOpen"
                        class="fixed inset-0 z-[70] bg-black/70 backdrop-blur-sm"
                        @click="servicesOpen = false"
                    ></div>
                </Transition>

                <!-- The drawer itself: full height, flush to the right
                edge — a true slide-over, not a card floating near the
                edge — gliding in with a soft fade and scale so it carries
                some visual weight instead of just snapping into place. -->
                <Transition
                    enter-active-class="transition-all duration-500 ease-in-out"
                    enter-from-class="translate-x-full scale-95 opacity-0"
                    leave-active-class="transition-all duration-500 ease-in-out"
                    leave-to-class="translate-x-full scale-95 opacity-0"
                >
                    <div
                        v-if="servicesOpen"
                        class="bg-coast-deep fixed inset-y-0 right-0 z-[71] flex h-full w-full max-w-md flex-col overflow-y-auto p-6 text-white shadow-2xl sm:p-8"
                        role="dialog"
                        aria-modal="true"
                        :aria-label="t('additionalServicesTitle')"
                    >
                        <button
                            type="button"
                            :aria-label="t('closeAdditionalServices')"
                            class="absolute top-4 right-4 rounded-full bg-white/10 p-2 transition hover:bg-white/20"
                            @click="servicesOpen = false"
                        >
                            <X class="size-5" />
                        </button>
                        <h3 class="text-xl font-semibold sm:text-2xl">
                            {{ t('additionalServicesTitle') }}
                        </h3>
                        <p class="mt-2 text-sm text-stone-300">
                            {{ t('additionalServicesIntro') }}
                        </p>

                        <ul class="mt-5 divide-y divide-white/10">
                            <li
                                v-for="service in additionalServices"
                                :key="service.id"
                                class="flex items-center justify-between gap-4 py-3"
                            >
                                <span class="text-sm text-stone-100">
                                    {{ localized(service.name, locale) }}
                                </span>
                                <span
                                    class="text-coast-tide text-sm font-semibold"
                                >
                                    {{ format(service.price) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </Transition>
            </Teleport>
        </ClientOnly>
    </section>
</template>
