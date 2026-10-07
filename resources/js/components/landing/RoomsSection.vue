<script setup lang="ts">
import {
    Armchair,
    BedDouble,
    Check,
    ChevronLeft,
    ChevronRight,
    Percent,
    Snowflake,
    Tv,
    Users,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { useCurrency } from '@/composables/useCurrency';
import { localized, useLocale } from '@/composables/useLocale';
import {
    cellPrice,
    cheapestColumn,
    isDeal,
    percentOffBase,
} from '@/composables/usePricingDisplay';
import type { HotelSettings, PricingTable, Room } from '@/types/landing';

const props = defineProps<{
    hotel: HotelSettings;
    rooms: Room[];
    pricing: PricingTable;
    /** A room's slug, when this page was opened through its own `/rooms/{slug}` URL. */
    focusSlug?: string | null;
}>();

const { format } = useCurrency();
const { t, locale } = useLocale();

// Opens directly on the room this URL is about, instead of always the first
// one — the deep link from sitemap.xml would otherwise land on the wrong room.
const initialIndex = props.focusSlug
    ? Math.max(
          props.rooms.findIndex((room) => room.slug === props.focusSlug),
          0,
      )
    : 0;
const activeIndex = ref(initialIndex);
const imageIndex = ref(0);
const activeRoom = computed(() => props.rooms[activeIndex.value]);

// The cheapest active month for this room, shown as the card's teaser price.
// Falls back to the plain base price if there is no matching pricing row
// (e.g. a mock room with no seasonal data).
const activeRoomPricingRow = computed(() =>
    props.pricing.rows.find((row) => row.room_id === activeRoom.value.id),
);
const bestColumn = computed(() => {
    const row = activeRoomPricingRow.value;

    return row ? cheapestColumn(row, props.pricing.columns) : undefined;
});
const bestPrice = computed(() => {
    const row = activeRoomPricingRow.value;
    const column = bestColumn.value;

    return row && column
        ? cellPrice(row, column)
        : Number(activeRoom.value.base_price);
});
const bestIsDeal = computed(() => {
    const row = activeRoomPricingRow.value;
    const column = bestColumn.value;

    return Boolean(row && column && isDeal(row, column));
});
const bestPercentOff = computed(() => {
    const row = activeRoomPricingRow.value;
    const column = bestColumn.value;

    return row && column ? percentOffBase(row, column) : 0;
});

// Prices and discounts live only in the Pricing section now, so booking
// always starts there.
const goToPricing = (): void => {
    document.getElementById('pricing')?.scrollIntoView({ behavior: 'smooth' });
};

watch(activeIndex, () => {
    imageIndex.value = 0;
});

const amenityItems = computed(() => {
    const { amenities } = activeRoom.value;

    return [
        {
            icon: Users,
            label: t('capacity'),
            value: t('guestsCount', { n: amenities.capacity }),
        },
        { icon: BedDouble, label: t('bed'), value: amenities.bed_type },
        {
            icon: Tv,
            label: t('tv'),
            value: amenities.has_tv ? t('yes') : t('no'),
        },
        {
            icon: Snowflake,
            label: t('airConditioning'),
            value: amenities.has_air_conditioning ? t('yes') : t('no'),
        },
    ];
});

const stepRoom = (direction: 1 | -1): void => {
    const total = props.rooms.length;
    activeIndex.value = (activeIndex.value + direction + total) % total;
};

const stepImage = (direction: 1 | -1): void => {
    const total = activeRoom.value.images.length;

    if (total === 0) {
        return;
    }

    imageIndex.value = (imageIndex.value + direction + total) % total;
};
</script>

<template>
    <section
        id="rooms"
        class="relative flex min-h-screen snap-start flex-col overflow-hidden bg-stone-950 pt-16 text-white lg:h-screen lg:flex-row"
    >
        <SectionBackdrop photo-id="photo-1517248135467-4c7edcad34c4" />
        <div
            class="relative z-10 flex h-[42vh] flex-col sm:h-[48vh] lg:h-full lg:w-3/5"
        >
            <div class="relative flex-1 overflow-hidden">
                <Transition
                    mode="out-in"
                    enter-active-class="transition-opacity duration-500"
                    enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-500"
                    leave-to-class="opacity-0"
                >
                    <img
                        :key="`${activeRoom.id}-${imageIndex}`"
                        :src="activeRoom.images[imageIndex]"
                        :alt="`${activeRoom.name} photo ${imageIndex + 1}`"
                        class="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                </Transition>
                <template v-if="activeRoom.images.length > 1">
                    <button
                        type="button"
                        class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-black/50 p-1.5 backdrop-blur hover:bg-black/70 sm:left-4 sm:p-2"
                        :aria-label="t('previousPhoto')"
                        @click="stepImage(-1)"
                    >
                        <ChevronLeft class="size-4 sm:size-5" />
                    </button>
                    <button
                        type="button"
                        class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-black/50 p-1.5 backdrop-blur hover:bg-black/70 sm:right-4 sm:p-2"
                        :aria-label="t('nextPhoto')"
                        @click="stepImage(1)"
                    >
                        <ChevronRight class="size-4 sm:size-5" />
                    </button>
                </template>
            </div>
            <div
                v-if="activeRoom.images.length > 1"
                class="flex gap-1.5 overflow-x-auto bg-black/60 p-1.5 sm:gap-2 sm:p-2"
            >
                <button
                    v-for="(src, index) in activeRoom.images"
                    :key="src + index"
                    type="button"
                    class="h-11 w-16 shrink-0 overflow-hidden rounded-md border-2 transition sm:h-14 sm:w-20"
                    :class="
                        index === imageIndex
                            ? 'border-amber-300'
                            : 'border-transparent opacity-60 hover:opacity-100'
                    "
                    :aria-label="t('showPhoto', { n: index + 1 })"
                    @click="imageIndex = index"
                >
                    <img
                        :src="src"
                        alt=""
                        loading="lazy"
                        decoding="async"
                        class="h-full w-full object-cover"
                    />
                </button>
            </div>
        </div>

        <div
            class="relative z-10 flex flex-1 flex-col justify-center gap-4 overflow-y-auto p-5 sm:gap-5 sm:p-8 lg:h-full lg:w-2/5 lg:p-14"
        >
            <div>
                <p
                    class="text-[11px] tracking-widest text-amber-300/70 uppercase"
                >
                    {{ localized(hotel.section_headings.rooms, locale) }}
                </p>
                <div class="mt-1 flex items-center justify-between">
                    <p
                        class="text-xs tracking-widest text-amber-300 uppercase sm:text-sm"
                    >
                        {{
                            t('roomCounter', {
                                n: activeIndex + 1,
                                total: rooms.length,
                            })
                        }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-full bg-white/10 p-2 hover:bg-white/20"
                            :aria-label="t('previousRoom')"
                            @click="stepRoom(-1)"
                        >
                            <ChevronLeft class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-full bg-white/10 p-2 hover:bg-white/20"
                            :aria-label="t('nextRoom')"
                            @click="stepRoom(1)"
                        >
                            <ChevronRight class="size-4" />
                        </button>
                    </div>
                </div>
                <h2 class="mt-2 text-2xl font-semibold sm:text-3xl lg:text-4xl">
                    {{ activeRoom.name }}
                </h2>
                <p class="mt-2 text-sm text-white/70 sm:mt-3 sm:text-base">
                    {{ activeRoom.description }}
                </p>
                <div
                    class="mt-3 flex flex-wrap items-center justify-between gap-3 sm:mt-4"
                >
                    <p
                        v-if="bestIsDeal"
                        class="flex flex-wrap items-baseline gap-1.5 text-lg sm:text-xl"
                    >
                        {{ t('from') }}
                        <span class="text-sm text-white/50 line-through">{{
                            format(Number(activeRoom.base_price))
                        }}</span>
                        <span class="font-semibold text-amber-300">{{
                            format(bestPrice)
                        }}</span>
                        <span
                            class="inline-flex items-center gap-0.5 rounded-full bg-amber-300 px-1.5 py-0.5 text-[10px] font-bold text-stone-900"
                            :title="t('percentOff', { pct: bestPercentOff })"
                        >
                            <Percent class="size-2.5" />{{ bestPercentOff }}
                        </span>
                        {{ t('slashPerNight') }}
                    </p>
                    <!--
                        Case A (flat): the cheapest available month is still at or
                        above the base price (a peak-season rate), so no strikethrough
                        or "markup" is shown — just the plain final price.
                    -->
                    <p v-else class="text-lg sm:text-xl">
                        {{ t('from') }}
                        <span class="font-semibold">{{
                            format(bestPrice)
                        }}</span>
                        {{ t('slashPerNight') }}
                    </p>
                    <button
                        type="button"
                        class="rounded-full bg-amber-300 px-5 py-2 text-xs font-semibold text-slate-900 transition hover:bg-amber-200 sm:text-sm"
                        @click="goToPricing"
                    >
                        {{ t('viewPricesAvailability') }}
                    </button>
                </div>
            </div>

            <ul class="grid grid-cols-2 gap-3 sm:gap-4">
                <li
                    v-for="item in amenityItems"
                    :key="item.label"
                    class="flex items-center gap-2.5 rounded-lg bg-white/5 p-2.5 sm:gap-3 sm:p-3"
                >
                    <component
                        :is="item.icon"
                        class="size-5 shrink-0 text-amber-300 sm:size-6"
                    />
                    <div>
                        <p class="text-[11px] text-white/70 sm:text-xs">
                            {{ item.label }}
                        </p>
                        <p class="text-xs sm:text-sm">{{ item.value }}</p>
                    </div>
                </li>
            </ul>

            <div>
                <p class="mb-2 flex items-center gap-2 text-xs text-white/50">
                    <Armchair class="size-4 text-amber-300" />
                    {{ t('furniture') }}
                </p>
                <ul class="flex flex-wrap gap-2">
                    <li
                        v-for="item in activeRoom.amenities.furniture"
                        :key="item"
                        class="flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-0.5 text-xs sm:px-3 sm:py-1 sm:text-sm"
                    >
                        <Check class="size-3.5 text-amber-300" />
                        {{ item }}
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
