import { reactive, ref } from 'vue';
import { store } from '@/actions/App/Http/Controllers/Api/V1/CallbackRequestController';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useCurrency } from '@/composables/useCurrency';
import { useLocale } from '@/composables/useLocale';
import { formatPhoneInput, isPhoneComplete } from '@/composables/usePhoneMask';
import { useRoomSelection } from '@/composables/useRoomSelection';
import { useServiceBasket } from '@/composables/useServiceBasket';

/**
 * Module-level (singleton) state, the same pattern as useBookingBasket: the
 * checkout form's own fields and submission status, shared across several
 * independent components (the input fields, the submit button, possibly
 * more) so each can live wherever the responsive layout needs it — stacked
 * in one strict order on mobile, split across two columns on desktop —
 * without prop drilling a shared form object between them.
 */
const form = reactive({ name: '', phone: '', message: '' });
const wantsBalcony = ref(false);
const specialRequests = ref('');
const status = ref<'idle' | 'sending' | 'sent' | 'error'>('idle');
const phoneError = ref<string | null>(null);
const successOverlayOpen = ref(false);
let successOverlayTimer: ReturnType<typeof setTimeout> | undefined;

export function useBookingSubmission() {
    const { items, clear } = useBookingBasket();
    const { items: serviceItems, clear: clearServices } = useServiceBasket();
    const { selected } = useCurrency();
    const { t, locale } = useLocale();
    const { selectedRoomNumber } = useRoomSelection();

    const onPhoneInput = (event: Event): void => {
        form.phone = formatPhoneInput(
            (event.target as HTMLInputElement).value,
            locale.value,
        );
        phoneError.value = null;
    };

    const submit = async (): Promise<void> => {
        if (!isPhoneComplete(form.phone)) {
            phoneError.value = t('invalidPhone');

            return;
        }

        status.value = 'sending';

        try {
            const response = await fetch(store.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    ...form,
                    wants_balcony: wantsBalcony.value,
                    special_requests: specialRequests.value || null,
                    room_number: selectedRoomNumber.value,
                    // Only an ID and a quantity for each line — no price travels
                    // in this payload at all, so there is nothing here for a
                    // tampered request to lie about. The server looks up every
                    // price itself from the room/service and the active season.
                    currency: selected.value.code,
                    rooms: items.value.map((item) => ({
                        room_id: item.roomId,
                        period: item.period,
                        quantity: item.quantity,
                    })),
                    services: serviceItems.value.map((item) => ({
                        service_id: item.serviceId,
                        quantity: item.quantity,
                    })),
                }),
            });

            if (!response.ok) {
                throw new Error(String(response.status));
            }

            Object.assign(form, { name: '', phone: '', message: '' });
            wantsBalcony.value = false;
            specialRequests.value = '';
            clear();
            clearServices();
            status.value = 'sent';

            clearTimeout(successOverlayTimer);
            successOverlayOpen.value = true;
            successOverlayTimer = setTimeout(() => {
                successOverlayOpen.value = false;
            }, 4000);
        } catch {
            status.value = 'error';
        }
    };

    return {
        form,
        wantsBalcony,
        specialRequests,
        status,
        phoneError,
        successOverlayOpen,
        onPhoneInput,
        submit,
    };
}
