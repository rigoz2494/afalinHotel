import { inject, provide, type InjectionKey, type Ref } from 'vue';
import type { HotelSettings } from '@/types/landing';

const HOTEL_KEY: InjectionKey<Ref<HotelSettings>> = Symbol('hotel');

/**
 * Provides the hotel settings ref to the whole landing page, so leaf
 * components like BookingForm (used both in Section 5 and the booking
 * modal) can read things like the contact phone number without prop
 * drilling through every parent.
 */
export function provideHotelSettings(hotel: Ref<HotelSettings>): void {
    provide(HOTEL_KEY, hotel);
}

export function useHotelSettings(): Ref<HotelSettings> {
    const hotel = inject(HOTEL_KEY);

    if (!hotel) {
        throw new Error(
            'useHotelSettings() must be used inside the landing page.',
        );
    }

    return hotel;
}
