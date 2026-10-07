export type HotelSettings = {
    // Both languages are always present, like section_headings below.
    hotel_name: { en: string; ru: string };
    tagline: string | null;
    promo_banner: string | null;
    hero_images: string[];
    contacts: Record<string, string>;
    // Both languages are always present; see the `localized()` helper.
    section_headings: Record<string, { en: string; ru: string }>;
};

export type Room = {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    images: string[];
    base_price: number | string;
    amenities: {
        capacity: number;
        bed_type: string;
        furniture: string[];
        has_tv: boolean;
        has_air_conditioning: boolean;
    };
};

export type PricingColumn = {
    id: number;
    label: string;
};

export type PricingRow = {
    room_id: number;
    room_name: string;
    // Keyed by the column's PricingPeriod id, not its (editable) label.
    // A room without a price row for some active period simply has no
    // entry for that key.
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
