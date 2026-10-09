import { computed, ref } from 'vue';

export type ServiceBasketItem = {
    serviceId: number;
    name: string;
    basePrice: number;
    quantity: number;
};

/**
 * Module-level (singleton) state for the extra services a guest picked while
 * filling in the booking form — the same sharing pattern as
 * useBookingBasket, kept as its own basket since a service has no room/period
 * dimension to key on.
 */
const items = ref<ServiceBasketItem[]>([]);

export function useServiceBasket() {
    const quantityFor = (serviceId: number): number =>
        items.value.find((item) => item.serviceId === serviceId)?.quantity ?? 0;

    const add = (serviceId: number, name: string, basePrice: number): void => {
        const existing = items.value.find(
            (item) => item.serviceId === serviceId,
        );

        if (existing) {
            existing.quantity += 1;
            return;
        }

        items.value.push({ serviceId, name, basePrice, quantity: 1 });
    };

    const decrement = (serviceId: number): void => {
        const index = items.value.findIndex(
            (item) => item.serviceId === serviceId,
        );

        if (index === -1) {
            return;
        }

        if (items.value[index].quantity > 1) {
            items.value[index].quantity -= 1;
        } else {
            items.value.splice(index, 1);
        }
    };

    const clear = (): void => {
        items.value = [];
    };

    const totalCount = computed(() =>
        items.value.reduce((sum, item) => sum + item.quantity, 0),
    );

    return { items, quantityFor, add, decrement, clear, totalCount };
}
