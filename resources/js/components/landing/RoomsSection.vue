<script setup lang="ts">
import {
    BedDouble,
    ChevronLeft,
    ChevronRight,
    Percent,
    Users,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import RoomUnitModal from '@/components/landing/RoomUnitModal.vue';
import { useAmenityTags } from '@/composables/useAmenityTags';
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

// A target-sized, varied gallery for this room type's own card — not just
// its single type-level photo, and not any one specific room's whole
// gallery either. Round-robins one photo at a time across every specific
// numbered room of this type (never a bathroom shot, matched by "bath" in
// the path, e.g. a source file literally named bath.jpg) until it has
// GALLERY_TARGET photos or every room's pool is exhausted. A type with
// several numbered rooms ends up with real variety from each of them; a
// type with only one keeps drawing from that single room until the target
// is met (or it runs out).
const GALLERY_TARGET = 5;

const showcaseImages = computed(() => {
    const perUnitPhotos = activeRoom.value.units.map((unit) =>
        unit.images.filter((src) => !src.toLowerCase().includes('bath')),
    );

    const pool: string[] = [];

    for (
        let round = 0;
        pool.length < GALLERY_TARGET &&
        perUnitPhotos.some((photos) => photos.length > round);
        round++
    ) {
        for (const photos of perUnitPhotos) {
            if (photos[round] && pool.length < GALLERY_TARGET) {
                pool.push(photos[round]);
            }
        }
    }

    // No units seeded yet (or none with their own photos) — fall back to
    // the room type's own type-level photo, filtered the same way.
    if (pool.length === 0) {
        return activeRoom.value.images.filter(
            (src) => !src.toLowerCase().includes('bath'),
        );
    }

    return pool;
});
const activeRoomName = computed(() =>
    localized(activeRoom.value.name, locale.value),
);
const activeRoomDescription = computed(() =>
    localized(activeRoom.value.description, locale.value),
);
const activeRoomAmenityTags = computed(() =>
    useAmenityTags(activeRoom.value.amenities.tags, t),
);

const unitModalOpen = ref(false);

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
        {
            icon: BedDouble,
            label: t('bed'),
            value: localized(amenities.bed_type, locale.value),
        },
    ];
});

const stepRoom = (direction: 1 | -1): void => {
    const total = props.rooms.length;
    activeIndex.value = (activeIndex.value + direction + total) % total;
};

const stepImage = (direction: 1 | -1): void => {
    const total = showcaseImages.value.length;

    if (total === 0) {
        return;
    }

    imageIndex.value = (imageIndex.value + direction + total) % total;
};
</script>

<template>
    <section
        id="rooms"
        class="landing-screen bg-coast-mist text-coast-ink flex scroll-mt-14 flex-col justify-center sm:scroll-mt-16"
    >
        <div class="max-w-landing mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="py-8 sm:py-12 lg:px-4">
                <h2
                    class="font-coastal-heading mb-5 text-3xl leading-tight font-medium sm:text-5xl"
                >
                    {{ localized(hotel.section_headings.rooms, locale) }}
                </h2>
                <div
                    class="grid items-start gap-5 sm:gap-8 lg:grid-cols-[1.5fr_1fr] lg:items-center 2xl:items-start"
                >
                    <div v-if="showcaseImages.length" class="min-w-0">
                        <div
                            class="bg-coast-line relative aspect-video overflow-hidden rounded-xl lg:aspect-auto lg:h-[clamp(19rem,calc(100svh-17rem),min(37rem,42vw))]"
                        >
                            <Transition
                                mode="out-in"
                                enter-active-class="transition-opacity duration-500"
                                enter-from-class="opacity-0"
                                leave-active-class="transition-opacity duration-500"
                                leave-to-class="opacity-0"
                            >
                                <img
                                    :key="`${activeRoom.id}-${imageIndex}`"
                                    :src="showcaseImages[imageIndex]"
                                    :alt="`${activeRoomName} photo ${imageIndex + 1}`"
                                    class="absolute inset-0 h-full w-full object-cover"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </Transition>
                            <template v-if="showcaseImages.length > 1">
                                <button
                                    type="button"
                                    class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-black/50 p-1.5 text-white backdrop-blur hover:bg-black/70 sm:left-4 sm:p-2"
                                    :aria-label="t('previousPhoto')"
                                    @click="stepImage(-1)"
                                >
                                    <ChevronLeft class="size-4 sm:size-5" />
                                </button>
                                <button
                                    type="button"
                                    class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-black/50 p-1.5 text-white backdrop-blur hover:bg-black/70 sm:right-4 sm:p-2"
                                    :aria-label="t('nextPhoto')"
                                    @click="stepImage(1)"
                                >
                                    <ChevronRight class="size-4 sm:size-5" />
                                </button>
                            </template>
                        </div>
                        <div
                            v-if="showcaseImages.length > 1"
                            class="mt-2 flex gap-1.5 overflow-x-auto pb-1 sm:gap-2"
                        >
                            <button
                                v-for="(src, index) in showcaseImages"
                                :key="src + index"
                                type="button"
                                class="h-11 w-16 shrink-0 overflow-hidden rounded-md border-2 transition sm:h-14 sm:w-20"
                                :class="
                                    index === imageIndex
                                        ? 'border-coast-blue'
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

                    <div class="flex min-w-0 flex-col gap-5">
                        <div>
                            <div class="mt-1 flex items-center justify-between">
                                <p
                                    class="text-coast-blue text-xs tracking-widest uppercase sm:text-sm"
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
                                        class="bg-coast-mist hover:bg-coast-line rounded-full p-2"
                                        :aria-label="t('previousRoom')"
                                        @click="stepRoom(-1)"
                                    >
                                        <ChevronLeft class="size-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="bg-coast-mist hover:bg-coast-line rounded-full p-2"
                                        :aria-label="t('nextRoom')"
                                        @click="stepRoom(1)"
                                    >
                                        <ChevronRight class="size-4" />
                                    </button>
                                </div>
                            </div>
                            <h3
                                class="font-coastal-heading mt-2 text-2xl font-medium sm:text-3xl lg:text-3xl"
                            >
                                {{ activeRoomName }}
                            </h3>
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
                                    class="bg-coast-mist text-coast-blue flex size-8 items-center justify-center rounded-full sm:size-9"
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-4 sm:size-4.5"
                                    />
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
                                class="text-coast-muted mt-2 line-clamp-2 min-h-[2.5rem] text-sm sm:mt-3 sm:min-h-[3rem] sm:text-base"
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
                                    <span
                                        class="text-coast-muted text-sm line-through"
                                        >{{
                                            format(
                                                Number(activeRoom.base_price),
                                            )
                                        }}</span
                                    >
                                    <span
                                        class="text-coast-blue font-semibold"
                                        >{{ format(bestPrice) }}</span
                                    >
                                    <span
                                        class="bg-coast-blue inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold text-white"
                                        :title="
                                            t('percentOff', {
                                                pct: bestPercentOff,
                                            })
                                        "
                                    >
                                        <Percent class="size-2.5" />{{
                                            bestPercentOff
                                        }}
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
                                    v-if="pricing.columns.length"
                                    type="button"
                                    class="bg-coast-blue hover:bg-coast-ink rounded-full px-5 py-2 text-xs font-semibold text-white transition sm:text-sm"
                                    @click="goToPricing"
                                >
                                    {{ t('viewPricesAvailability') }}
                                </button>
                            </div>
                            <!-- Opens the modal below, listing this room type's own
                specific numbered rooms. -->
                            <button
                                v-if="activeRoom.units.length"
                                type="button"
                                class="border-coast-blue/50 text-coast-blue hover:bg-coast-mist mt-3 w-full rounded-full border px-5 py-2 text-xs font-semibold transition sm:mt-4 sm:w-auto sm:text-sm lg:mt-3"
                                @click="unitModalOpen = true"
                            >
                                {{ t('viewAvailableRooms') }}
                            </button>
                        </div>

                        <div class="border-coast-line border-t pt-4">
                            <p
                                class="text-coast-blue mb-3 text-xs tracking-widest uppercase"
                            >
                                {{ t('roomDetailsHeading') }}
                            </p>
                            <ul class="grid grid-cols-2 gap-3">
                                <li
                                    v-for="item in amenityItems"
                                    :key="item.label"
                                    class="flex items-center gap-2.5 rounded-lg bg-white/65 p-2.5 sm:gap-3 sm:p-3"
                                >
                                    <component
                                        :is="item.icon"
                                        class="text-coast-blue size-5 shrink-0 sm:size-6"
                                    />
                                    <div>
                                        <p
                                            class="text-coast-muted text-[11px] sm:text-xs"
                                        >
                                            {{ item.label }}
                                        </p>
                                        <p class="text-xs sm:text-sm">
                                            {{ item.value }}
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <RoomUnitModal v-model="unitModalOpen" :room="activeRoom" />
    </section>
</template>
