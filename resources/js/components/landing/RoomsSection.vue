<script setup lang="ts">
import {
    Armchair,
    BedDouble,
    BedSingle,
    ChevronLeft,
    ChevronRight,
    DoorClosed,
    Lamp,
    Lock,
    Percent,
    Refrigerator,
    Shirt,
    Snowflake,
    Sofa,
    Table2,
    Tv,
    Users,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { useCurrency } from '@/composables/useCurrency';
import {
    localized,
    useLocale,
    type TranslationKey,
} from '@/composables/useLocale';
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

// Room::AMENITY_TAGS on the backend is the source of truth for which keys
// are valid; this is purely presentational — one icon and one label key
// per tag, so a new tag only ever needs adding in one place on each side.
const AMENITY_ICONS: Record<string, typeof BedDouble> = {
    double_bed: BedDouble,
    twin_beds: BedSingle,
    sofa: Sofa,
    armchair: Armchair,
    table: Table2,
    nightstand: Lamp,
    chairs: Armchair,
    wardrobe: DoorClosed,
    hanger: Shirt,
    tv: Tv,
    ac: Snowflake,
    fridge: Refrigerator,
    safe_box: Lock,
};
const AMENITY_LABEL_KEYS: Record<string, TranslationKey> = {
    double_bed: 'amenityDoubleBed',
    twin_beds: 'amenityTwinBeds',
    sofa: 'amenitySofa',
    armchair: 'amenityArmchair',
    table: 'amenityTable',
    nightstand: 'amenityNightstand',
    chairs: 'amenityChairs',
    wardrobe: 'amenityWardrobe',
    hanger: 'amenityHanger',
    tv: 'amenityTv',
    ac: 'amenityAc',
    fridge: 'amenityFridge',
    safe_box: 'amenitySafeBox',
};

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
const activeRoomName = computed(() =>
    localized(activeRoom.value.name, locale.value),
);
const activeRoomDescription = computed(() =>
    localized(activeRoom.value.description, locale.value),
);
// Each tag paired with its icon and label, in the fixed vocabulary order —
// not the room's own (admin-chosen, unordered) array order — so every
// room's badge row reads in the same, familiar sequence.
const activeRoomAmenityTags = computed(() => {
    const tags = new Set(activeRoom.value.amenities.tags);

    return Object.keys(AMENITY_ICONS)
        .filter((tag) => tags.has(tag))
        .map((tag) => ({
            tag,
            icon: AMENITY_ICONS[tag],
            label: t(AMENITY_LABEL_KEYS[tag]),
        }));
});

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

// Capacity and bed type only — TV/AC/furniture are amenity tags now
// (activeRoomAmenityTags above), not separate fields.
const amenityItems = computed(() => {
    const { amenities } = activeRoom.value;

    return [
        {
            icon: Users,
            label: t('capacity'),
            value: t('guestsCount', { n: amenities.capacity }),
        },
        { icon: BedDouble, label: t('bed'), value: amenities.bed_type },
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
                        :alt="`${activeRoomName} photo ${imageIndex + 1}`"
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

        <!--
            justify-start, not justify-center: the "Previous/Next room"
            arrows sit near the top of this block, and a centered flex
            column re-centers its *whole* group whenever anything inside it
            changes height — the description (fixed below), but just as
            much the amenity badge row, which has a different tag count per
            room and disappears entirely for the one room with none at all.
            Anchoring to the top makes the arrows' position immune to any of
            that, instead of chasing every individual variable-height child.
        -->
        <div
            class="relative z-10 flex flex-1 flex-col justify-start gap-3 overflow-y-auto p-5 sm:gap-4 sm:p-8 lg:h-full lg:w-2/5 lg:gap-3 lg:p-10"
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
                <h2 class="mt-2 text-2xl font-semibold sm:text-3xl lg:text-3xl">
                    {{ activeRoomName }}
                </h2>
                <!-- Amenity tags: a fixed vocabulary, each one icon + a
                bilingual label, right under the room title. -->
                <ul
                    v-if="activeRoomAmenityTags.length"
                    class="mt-2 flex flex-wrap gap-1.5"
                >
                    <li
                        v-for="item in activeRoomAmenityTags"
                        :key="item.tag"
                        :title="item.label"
                        :aria-label="item.label"
                        class="flex size-8 items-center justify-center rounded-full bg-white/10 text-amber-300 sm:size-9"
                    >
                        <component :is="item.icon" class="size-4 sm:size-4.5" />
                    </li>
                </ul>
                <!--
                    A fixed height, not just a minimum: `line-clamp-2` caps
                    how tall this can grow and `min-h` guarantees it never
                    shrinks below two lines either, so switching rooms can't
                    shift the price/CTA row or the amenity grid below it,
                    whichever room's description happens to be shorter or
                    longer than another's.
                -->
                <p
                    class="mt-2 line-clamp-2 min-h-[2.5rem] text-sm text-white/70 sm:mt-3 sm:min-h-[3rem] sm:text-base"
                >
                    {{ activeRoomDescription }}
                </p>
                <div
                    class="mt-3 flex flex-wrap items-center justify-between gap-3 sm:mt-4 lg:mt-3"
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

            <ul class="grid grid-cols-2 gap-3 sm:gap-4 lg:gap-3">
                <li
                    v-for="item in amenityItems"
                    :key="item.label"
                    class="flex items-center gap-2.5 rounded-lg bg-white/5 p-2.5 sm:gap-3 sm:p-3 lg:p-2.5"
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
        </div>
    </section>
</template>
