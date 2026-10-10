import type { PricingColumn, PricingRow } from '@/types/landing';

/**
 * Shared pricing-display rules for the pricing table and the room cards, so
 * both read a cell's numbers the same way.
 *
 * The season's regular rate for a cell (`rawPrice`) is computed server-side
 * from the room's base price and the period's modifier — it can be higher or
 * lower than the room's base price. An optional promotional discount
 * (`monthly_discounts`) can then apply on top of that rate.
 *
 * Case A (flat): the final price is at or above the room's base price (a
 * peak-season markup, with no promo). Shown as a plain price, with no
 * strikethrough — a guest should never feel penalized for a higher rate.
 *
 * Case B (deal): the final price is below the room's base price, whether
 * from a season markdown, a promo, or both. The base price is shown struck
 * through next to the lower final price, with an "N% off" badge.
 */

/** True for a cell the admin set a literal, non-numeric price_override text
 * on (RoomPrice::price_override) rather than a price to do arithmetic on. */
export const isPriceOverride = (
    row: PricingRow,
    column: PricingColumn,
): boolean => typeof row.prices[column.id] === 'string';

export const rawPrice = (row: PricingRow, column: PricingColumn): number => {
    const value = row.prices[column.id];

    // An override cell has nothing numeric to fall back to here — the
    // template renders it via `isPriceOverride` instead of this value.
    return typeof value === 'string'
        ? Number(row.base_price)
        : (value ?? Number(row.base_price));
};

export const cellPrice = (row: PricingRow, column: PricingColumn): number => {
    const percentage = row.monthly_discounts?.[column.id];

    return percentage
        ? Math.round(rawPrice(row, column) * (1 - percentage / 100))
        : rawPrice(row, column);
};

export const isDeal = (row: PricingRow, column: PricingColumn): boolean =>
    cellPrice(row, column) < Number(row.base_price);

/**
 * The badge's "N% off" figure. When the admin set an explicit promo
 * discount, this is exactly that number — never re-derived from a price
 * ratio, which silently breaks whenever the cell's own regular rate
 * (`rawPrice`) differs from the room's flat `base_price` (e.g. a
 * price_override cell): a 50% discount on a 5700 override (base price
 * 4700) would otherwise measure as (5700*0.5)/4700 ≈ 39% off base_price,
 * instead of the 50% the admin actually set and the guest is actually
 * charged. Only a pure season-modifier markdown — a deal with no explicit
 * discount at all — has no stored percentage to report directly, so that
 * one case alone still falls back to the price-ratio calculation.
 */
export const percentOffBase = (
    row: PricingRow,
    column: PricingColumn,
): number => {
    const explicitDiscount = row.monthly_discounts?.[column.id];

    // A negative value is a markup (see RoomPriceForm's helper text), not a
    // discount — showing it directly on a "deal" badge would be nonsensical,
    // so that case (and the undefined/zero case) falls through to the
    // ratio below, which reflects whatever combination of factors actually
    // produced a lower price.
    if (explicitDiscount && explicitDiscount > 0) {
        return explicitDiscount;
    }

    return Math.round(
        100 - (cellPrice(row, column) / Number(row.base_price)) * 100,
    );
};

/** The column with the lowest final price for a row, for a "from" teaser price. */
export const cheapestColumn = (
    row: PricingRow,
    columns: PricingColumn[],
): PricingColumn | undefined =>
    columns.reduce<PricingColumn | undefined>((cheapest, column) => {
        if (!cheapest || cellPrice(row, column) < cellPrice(row, cheapest)) {
            return column;
        }

        return cheapest;
    }, undefined);
