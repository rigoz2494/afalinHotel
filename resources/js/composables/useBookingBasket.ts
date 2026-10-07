import { computed, ref } from 'vue';

export type BasketItem = {
    key: string;
    roomId: number;
    roomName: string;
    period: string | null;
    // Per-night price in the hotel's base currency, after any discount. It is
    // converted to the guest's selected currency only when displayed or submitted.
    basePrice: number;
    quantity: number;
};

/**
 * Module-level (singleton) state: every component that calls this
 * composable shares the same reactive basket array, so Rooms/Pricing
 * (writers) and the booking form + floating button (readers) stay in
 * sync with no prop drilling or store library needed.
 */
const items = ref<BasketItem[]>([]);

// Same room selected for a different month/price is a separate line.
const keyFor = (roomId: number, period: string | null): string =>
    `${roomId}:${period ?? 'base'}`;

export function useBookingBasket() {
    const quantityFor = (roomId: number, period: string | null): number =>
        items.value.find((item) => item.key === keyFor(roomId, period))
            ?.quantity ?? 0;

    const add = (
        roomId: number,
        roomName: string,
        period: string | null,
        basePrice: number,
    ): void => {
        const key = keyFor(roomId, period);
        const existing = items.value.find((item) => item.key === key);

        if (existing) {
            existing.quantity += 1;
            return;
        }

        items.value.push({
            key,
            roomId,
            roomName,
            period,
            basePrice,
            quantity: 1,
        });
    };

    const decrement = (roomId: number, period: string | null): void => {
        const key = keyFor(roomId, period);
        const index = items.value.findIndex((item) => item.key === key);

        if (index === -1) {
            return;
        }

        if (items.value[index].quantity > 1) {
            items.value[index].quantity -= 1;
        } else {
            items.value.splice(index, 1);
        }
    };

    const removeItem = (key: string): void => {
        items.value = items.value.filter((item) => item.key !== key);
    };

    const clear = (): void => {
        items.value = [];
    };

    const totalCount = computed(() =>
        items.value.reduce((sum, item) => sum + item.quantity, 0),
    );

    return {
        items,
        quantityFor,
        add,
        decrement,
        removeItem,
        clear,
        totalCount,
    };
}
