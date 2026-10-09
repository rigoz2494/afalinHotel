import { ref } from 'vue';

/**
 * Module-level (singleton) state, the same pattern as useBookingBasket: the
 * specific room number a guest last picked in the Section 2 "View Available
 * Rooms" modal, remembered so BookingForm can include it as a preference
 * when they submit — a guest preference, not a confirmed assignment, the
 * same way wants_balcony works.
 */
const selectedRoomNumber = ref<string | null>(null);

export function useRoomSelection() {
    const setSelectedRoomNumber = (number: string | null): void => {
        selectedRoomNumber.value = number;
    };

    return { selectedRoomNumber, setSelectedRoomNumber };
}
