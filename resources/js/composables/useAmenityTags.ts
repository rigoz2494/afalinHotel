import {
    Armchair,
    BedDouble,
    BedSingle,
    DoorClosed,
    Lamp,
    Lock,
    Refrigerator,
    Shirt,
    Snowflake,
    Sofa,
    Table2,
    Tv,
} from '@lucide/vue';
import { type TranslationKey } from '@/composables/useLocale';

/**
 * Room::AMENITY_TAGS on the backend is the source of truth for which keys
 * are valid; this is purely presentational — one icon and one label key
 * per tag, so a new tag only ever needs adding in one place on each side.
 * Shared by the room carousel and the "View Available Rooms" modal, so
 * both ever only have one place to render a tag's icon/label from.
 */
export const AMENITY_ICONS: Record<string, typeof BedDouble> = {
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

export const AMENITY_LABEL_KEYS: Record<string, TranslationKey> = {
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

export type AmenityTagItem = {
    tag: string;
    icon: typeof BedDouble;
    label: string;
};

/**
 * Each tag paired with its icon and label, in the fixed vocabulary order —
 * not a room's own (admin-chosen, unordered) array order — so every room's
 * badge row reads in the same, familiar sequence.
 */
export function useAmenityTags(
    tags: string[],
    t: (key: TranslationKey) => string,
): AmenityTagItem[] {
    const present = new Set(tags);

    return Object.keys(AMENITY_ICONS)
        .filter((tag) => present.has(tag))
        .map((tag) => ({
            tag,
            icon: AMENITY_ICONS[tag],
            label: t(AMENITY_LABEL_KEYS[tag]),
        }));
}
