export type HotelSettings = {
    // Both languages are always present, like section_headings below.
    hotel_name: { en: string; ru: string };
    tagline: string | null;
    // null hides the banner entirely; once any text is set, both keys are
    // present — see the `localized()` helper for the "ru falls back to en"
    // behavior if only one language was filled in.
    promo_banner: { en: string | null; ru: string | null } | null;
    hero_images: string[];
    contacts: Record<string, string>;
    // Both languages are always present; see the `localized()` helper.
    section_headings: Record<string, { en: string; ru: string }>;
};

export type Room = {
    id: number;
    slug: string;
    // Both languages are always present, like hotel_name above.
    name: { en: string; ru: string };
    description: { en: string; ru: string };
    images: string[];
    base_price: number | string;
    amenities: {
        capacity: number;
        bed_type: string;
        // A fixed vocabulary of tags (see Room::AMENITY_TAGS on the
        // backend), each rendered as one icon + bilingual label — see
        // the `amenity*` keys in useLocale.ts.
        tags: string[];
    };
    // The specific, numbered physical rooms of this type — see RoomUnit.
    units: RoomUnit[];
};

/** A specific, numbered physical room belonging to a Room ("Room Type"). */
export type RoomUnit = {
    id: number;
    number: string;
    // Falls back to the room type's own photos/amenities when this
    // specific room hasn't been given its own — see RoomUnitResource.
    images: string[];
    amenities: string[];
    has_balcony: boolean;
};

/** A standalone upsell with a flat, non-seasonal price. */
export type AdditionalService = {
    id: number;
    name: { en: string; ru: string };
    price: number;
};

export type PricingColumn = {
    id: number;
    label: string;
};

export type PricingRow = {
    room_id: number;
    // Both languages are always present, like Room.name above.
    room_name: { en: string; ru: string };
    // Keyed by the column's PricingPeriod id, not its (editable) label. A
    // room without a price row for some active period simply has no entry
    // for that key. A string value is a literal display override set by
    // the admin (RoomPrice::price_override), not a price to do math on.
    prices: Record<number, number | string>;
    base_price: number | string;
    monthly_discounts?: Record<number, number>;
};

export type PricingTable = {
    columns: PricingColumn[];
    rows: PricingRow[];
};

export type FaqItem = {
    question: string;
    answer: string;
    // Admin-entered via the "Translate to Russian" action; null until set.
    question_ru?: string | null;
    answer_ru?: string | null;
};

export type CurrencyOption = {
    code: string;
    symbol: string;
    // Where the symbol sits relative to the number: "$100" vs "100 ₴".
    symbol_position: 'before' | 'after';
    exchange_rate: number;
    is_base: boolean;
};
