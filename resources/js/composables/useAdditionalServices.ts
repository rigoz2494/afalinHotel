import { inject, provide, type InjectionKey, type Ref } from 'vue';
import type { AdditionalService } from '@/types/landing';

const ADDITIONAL_SERVICES_KEY: InjectionKey<Ref<AdditionalService[]>> =
    Symbol('additionalServices');

/**
 * Provides the additional-services list to the whole landing page, so
 * BookingForm (used both in Section 5 and the booking modal) can offer them
 * as add-ons without prop drilling through every parent — the same pattern
 * as useHotelSettings.
 */
export function provideAdditionalServices(
    services: Ref<AdditionalService[]>,
): void {
    provide(ADDITIONAL_SERVICES_KEY, services);
}

export function useAdditionalServices(): Ref<AdditionalService[]> {
    const services = inject(ADDITIONAL_SERVICES_KEY);

    if (!services) {
        throw new Error(
            'useAdditionalServices() must be used inside the landing page.',
        );
    }

    return services;
}
