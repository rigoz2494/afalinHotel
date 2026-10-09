<script setup lang="ts">
import { useEventListener } from '@vueuse/core';
import { Percent, Minus, Plus, Sparkles, X } from '@lucide/vue';
import { ref } from 'vue';
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

defineProps<{
    hotel: HotelSettings;
    pricing: PricingTable;
    additionalServices: AdditionalService[];
}>();

const { quantityFor, add, decrement } = useBookingBasket();
const { format } = useCurrency();
const { t, locale } = useLocale();

const servicesOpen = ref(false);

useEventListener('keydown', (event: KeyboardEvent) => {
    if (servicesOpen.value && event.key === 'Escape') {
        servicesOpen.value = false;
    }
});
</script>

<template>
    <section
        id="pricing"
        class="relative flex min-h-screen snap-start items-center overflow-hidden bg-stone-950 px-4 py-20 text-stone-100 sm:px-6 lg:h-screen lg:py-0 lg:pt-16"
    >
        <div
            class="absolute inset-0 scale-110 bg-cover bg-center opacity-20 blur-sm"
            style="
                background-image: url('https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1800&q=70');
            "
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-br from-stone-950 via-stone-950/90 to-amber-950/70"
        ></div>

        <div
            class="relative mx-auto grid w-full max-w-6xl items-center gap-8 sm:gap-10 lg:grid-cols-5 lg:gap-6"
        >
            <div class="lg:col-span-2">
                <Sparkles class="size-7 text-amber-300 sm:size-8 lg:size-6" />
                <h2
                    class="mt-3 text-3xl leading-tight font-semibold sm:mt-4 sm:text-4xl md:text-5xl lg:mt-2 lg:text-3xl"
                >
                    {{ localized(hotel.section_headings.pricing, locale) }}
                </h2>
                <p
                    class="mt-3 text-sm text-stone-300 sm:mt-4 sm:text-base lg:mt-2 lg:text-sm"
                >
                    {{ t('pricingIntro') }}
                </p>
                <div
                    class="mt-5 rounded-xl border border-amber-300/30 bg-amber-300/10 p-3 text-xs text-amber-100 sm:mt-6 sm:p-4 sm:text-sm lg:mt-3 lg:p-3"
                >
                    {{ t('directBookingPerk') }}
                </div>
            </div>

            <div class="lg:col-span-3">
                <p class="mb-2 text-xs text-stone-400 sm:hidden">
                    {{ t('swipeHint') }}
                </p>
                <div
                    class="overflow-x-auto rounded-2xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur"
                >
                    <table class="w-full min-w-[640px] text-left">
                        <thead
                            class="text-[11px] tracking-widest text-amber-300 uppercase sm:text-xs"
                        >
                            <tr class="border-b border-white/10">
                                <th
                                    class="px-4 py-3.5 font-medium sm:px-6 sm:py-4 lg:py-2.5"
                                >
                                    {{ t('roomColumn') }}
                                </th>
                                <th
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    class="px-4 py-3.5 text-center font-medium sm:px-6 sm:py-4 lg:py-2.5"
                                >
                                    {{ column.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr
                                v-for="row in pricing.rows"
                                :key="row.room_id"
                                class="transition hover:bg-white/5"
                            >
                                <td
                                    class="px-4 py-4 align-middle text-base font-medium sm:px-6 sm:py-5 sm:text-base lg:py-3"
                                >
                                    {{ localized(row.room_name, locale) }}
                                </td>
                                <td
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    class="px-3 py-3 align-top sm:px-4 sm:py-3.5 lg:px-3 lg:py-2"
                                >
                                    <!--
                                        A fixed min-height, not `h-full`: a percentage height
                                        on a flex child doesn't reliably resolve against a
                                        <td>'s row-equalized height across browsers, which is
                                        why the "Select" button used to sit right under the
                                        price content (jumping up or down depending on whether
                                        a deal badge pushed that content taller) instead of
                                        staying pinned to the bottom. Tall enough for the
                                        deal case (struck-through price + final price + badge,
                                        possibly wrapped to two lines) plus the button/stepper
                                        below it, so every cell in the row — with or without a
                                        deal badge — ends up the same height, and `justify-between`
                                        can then reliably lock every button to the same line.
                                        Shorter on desktop (lg:min-h-20): six real room rows need
                                        to fit one viewport there, and the wider column means the
                                        deal badge's content wraps far less often than on mobile.
                                    -->
                                    <div
                                        class="flex min-h-28 flex-col items-start justify-between gap-2 sm:min-h-32 lg:min-h-20 lg:gap-1.5"
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
                                            class="text-base text-stone-200 sm:text-lg lg:text-base"
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
                                                class="flex flex-wrap items-center gap-2 lg:gap-1.5"
                                            >
                                                <span
                                                    class="text-sm text-stone-400 line-through sm:text-base lg:text-sm"
                                                    >{{
                                                        format(
                                                            Number(
                                                                row.base_price,
                                                            ),
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    class="text-base font-semibold text-amber-300 sm:text-lg lg:text-base"
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
                                                    class="inline-flex items-center gap-1 rounded-full bg-amber-300 px-2 py-1 text-[11px] font-bold text-stone-900 sm:px-2.5 sm:py-1 sm:text-xs lg:gap-0.5 lg:px-1.5 lg:py-0.5 lg:text-[10px]"
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
                                                        class="size-3 lg:size-2.5"
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
                                            class="text-base text-stone-200 sm:text-lg lg:text-base"
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
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-300 px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-stone-900 transition hover:bg-amber-200 sm:px-4 sm:py-2 sm:text-sm lg:gap-1 lg:px-2.5 lg:py-1 lg:text-xs"
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
                                                <Plus
                                                    class="size-3.5 sm:size-4 lg:size-3"
                                                />
                                            </button>
                                            <div
                                                v-else
                                                class="flex items-center gap-2 rounded-full bg-white/10 p-1.5 sm:gap-2 sm:p-1.5 lg:gap-1.5 lg:p-1"
                                            >
                                                <button
                                                    type="button"
                                                    :aria-label="t('removeOne')"
                                                    class="flex size-7 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20 sm:size-7 lg:size-5"
                                                    @click="
                                                        decrement(
                                                            row.room_id,
                                                            column.label,
                                                        )
                                                    "
                                                >
                                                    <Minus
                                                        class="size-3.5 sm:size-3.5 lg:size-2.5"
                                                    />
                                                </button>
                                                <span
                                                    class="min-w-5 text-center text-sm font-semibold sm:min-w-5 sm:text-sm lg:min-w-4 lg:text-xs"
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
                                                    class="flex size-7 items-center justify-center rounded-full bg-amber-300 text-stone-900 transition hover:bg-amber-200 sm:size-7 lg:size-5"
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
                                                    <Plus
                                                        class="size-3.5 sm:size-3.5 lg:size-2.5"
                                                    />
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button
                    type="button"
                    class="mt-4 inline-flex items-center gap-2 rounded-full border border-amber-300/50 px-5 py-2.5 text-sm font-semibold text-amber-200 transition hover:bg-amber-300/10"
                    @click="servicesOpen = true"
                >
                    <Sparkles class="size-4" />
                    {{ t('additionalServicesButton') }}
                </button>
            </div>
        </div>

        <ClientOnly>
            <Teleport to="body">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="servicesOpen"
                        class="fixed inset-0 z-[70] flex items-center justify-end bg-black/70 p-4 backdrop-blur-sm"
                        role="dialog"
                        aria-modal="true"
                        :aria-label="t('additionalServicesTitle')"
                        @click.self="servicesOpen = false"
                    >
                        <div
                            class="relative max-h-[90vh] w-full max-w-sm overflow-y-auto rounded-2xl bg-stone-950 p-6 text-white shadow-2xl sm:p-8"
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
                                        class="text-sm font-semibold text-amber-300"
                                    >
                                        {{ format(service.price) }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </ClientOnly>
    </section>
</template>
