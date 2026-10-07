<script setup lang="ts">
import { Percent, Minus, Plus, Sparkles } from '@lucide/vue';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useCurrency } from '@/composables/useCurrency';
import { localized, useLocale } from '@/composables/useLocale';
import {
    cellPrice,
    isDeal,
    percentOffBase,
} from '@/composables/usePricingDisplay';
import type { HotelSettings, PricingTable } from '@/types/landing';

defineProps<{ hotel: HotelSettings; pricing: PricingTable }>();

const { quantityFor, add, decrement } = useBookingBasket();
const { format } = useCurrency();
const { t, locale } = useLocale();
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
            class="relative mx-auto grid w-full max-w-6xl items-center gap-8 sm:gap-10 lg:grid-cols-5"
        >
            <div class="lg:col-span-2">
                <Sparkles class="size-7 text-amber-300 sm:size-8" />
                <h2
                    class="mt-3 text-3xl leading-tight font-semibold sm:mt-4 sm:text-4xl md:text-5xl"
                >
                    {{ localized(hotel.section_headings.pricing, locale) }}
                </h2>
                <p class="mt-3 text-sm text-stone-300 sm:mt-4 sm:text-base">
                    {{ t('pricingIntro') }}
                </p>
                <div
                    class="mt-5 rounded-xl border border-amber-300/30 bg-amber-300/10 p-3 text-xs text-amber-100 sm:mt-6 sm:p-4 sm:text-sm"
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
                    <table class="w-full min-w-[560px] text-left">
                        <thead
                            class="text-[10px] tracking-widest text-amber-300 uppercase sm:text-xs"
                        >
                            <tr class="border-b border-white/10">
                                <th
                                    class="px-4 py-3 font-medium sm:px-6 sm:py-4"
                                >
                                    {{ t('roomColumn') }}
                                </th>
                                <th
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    class="px-4 py-3 font-medium sm:px-6 sm:py-4"
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
                                    class="px-4 py-3 text-sm font-medium sm:px-6 sm:py-5 sm:text-base"
                                >
                                    {{ row.room_name }}
                                </td>
                                <td
                                    v-for="column in pricing.columns"
                                    :key="column.id"
                                    class="px-2 py-2 align-top sm:px-3 sm:py-3"
                                >
                                    <div
                                        class="flex h-full flex-col items-start justify-between gap-1.5"
                                    >
                                        <!--
                                            Case B (deal): the final price is below the room's
                                            base price — from a season markdown, a promo, or
                                            both. Show the base price struck through next to
                                            the lower final price, with an offer badge.
                                        -->
                                        <template v-if="isDeal(row, column)">
                                            <div
                                                class="flex flex-wrap items-center gap-1.5"
                                            >
                                                <span
                                                    class="text-xs text-stone-400 line-through sm:text-sm"
                                                    >{{
                                                        format(
                                                            Number(
                                                                row.base_price,
                                                            ),
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    class="font-semibold text-amber-300"
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
                                                    class="inline-flex items-center gap-0.5 rounded-full bg-amber-300 px-1.5 py-0.5 text-[9px] font-bold text-stone-900 sm:text-[10px]"
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
                                            class="text-sm text-stone-200 sm:text-base"
                                            >{{
                                                format(cellPrice(row, column))
                                            }}</span
                                        >

                                        <button
                                            v-if="
                                                quantityFor(
                                                    row.room_id,
                                                    column.label,
                                                ) === 0
                                            "
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-300 px-2.5 py-1 text-[11px] font-semibold whitespace-nowrap text-stone-900 transition hover:bg-amber-200 sm:text-xs"
                                            @click="
                                                add(
                                                    row.room_id,
                                                    row.room_name,
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
                                            class="flex items-center gap-1.5 rounded-full bg-white/10 p-1"
                                        >
                                            <button
                                                type="button"
                                                :aria-label="t('removeOne')"
                                                class="flex size-5 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20"
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
                                                class="min-w-4 text-center text-[11px] font-semibold sm:text-xs"
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
                                                :aria-label="t('addOneMore')"
                                                class="flex size-5 items-center justify-center rounded-full bg-amber-300 text-stone-900 transition hover:bg-amber-200"
                                                @click="
                                                    add(
                                                        row.room_id,
                                                        row.room_name,
                                                        column.label,
                                                        cellPrice(row, column),
                                                    )
                                                "
                                            >
                                                <Plus class="size-2.5" />
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</template>
