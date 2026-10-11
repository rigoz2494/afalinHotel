export type HotelSettings = {
    // Both languages are always present, like section_headings below.
    hotel_name: { en: string; ru: string };
    // null when the admin hasn't set one in either language; once any text
    // is set, both keys are present — see normalizedPromoBanner's `localized()`
    // note just below, which applies the same way here.
    tagline: { en: string | null; ru: string | null } | null;
    // null hides the banner entirely; once any text is set, both keys are
    // present — see the `localized()` helper for the "ru falls back to en"
    // behavior if only one language was filled in.
    promo_banner: { en: string | null; ru: string | null } | null;
    hero_images: string[];
    contacts: Record<string, string>;
    // Both languages are always present; see the `localized()` helper.
    section_headings: Record<string, { en: string; ru: string }>;
    // The three About Us marketing badges (e.g. "20+ Years"). Both label
    // and value are bilingual, like section_headings above.
    about_stats: Record<
        'years' | 'rooms' | 'guests',
        {
            label: { en: string; ru: string };
            value: { en: string; ru: string };
        }
    >;
    // The About Us section's own story text. A blank line between
    // paragraphs in the admin's text starts a new one on the page — see
    // the `localized()` helper.
    about_story: { en: string; ru: string };
    // The preloader's playful, purely decorative status phrases — one is
    // picked at random on every load, never the same one twice in a row.
    preloader_phrases: { en: string; ru: string }[];
    // 'multilingual' (default) lets guests choose; 'ru_only'/'en_only'
    // locks the whole site to one language and currency — see
    // SiteHeader.vue (hides the switchers) and useLocale.ts (forces the
    // active locale).
    locale_mode: 'multilingual' | 'ru_only' | 'en_only';
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
        // Both languages are always present, like Room.name above.
        bed_type: { en: string; ru: string };
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

/** A real hotel photo in the site-wide gallery (About section). */
export type GalleryImage = {
    src: string;
    // Both languages are always present, like Room.name above.
    alt: { en: string | null; ru: string | null };
    // A portrait-oriented photo spans two grid rows on the masonry layout.
    tall: boolean;
};

export type CurrencyOption = {
    code: string;
    symbol: string;
    // Where the symbol sits relative to the number: "$100" vs "100 ₴".
    symbol_position: 'before' | 'after';
    exchange_rate: number;
    is_base: boolean;
};
