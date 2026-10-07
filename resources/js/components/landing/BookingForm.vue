<script setup lang="ts">
import { Minus, Phone, Plus, Trash2 } from '@lucide/vue';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import BookingSuccessOverlay from '@/components/landing/BookingSuccessOverlay.vue';
import { store } from '@/actions/App/Http/Controllers/Api/V1/CallbackRequestController';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useCurrency } from '@/composables/useCurrency';
import { useHotelSettings } from '@/composables/useHotelSettings';
import { useLocale } from '@/composables/useLocale';
import { formatPhoneInput, isPhoneComplete } from '@/composables/usePhoneMask';

const emit = defineEmits<{ submitted: [] }>();

const { items, add, decrement, removeItem, clear } = useBookingBasket();
const { convert, format, formatConverted, selected } = useCurrency();
const { t, locale } = useLocale();
const hotel = useHotelSettings();

// The direct-call fallback: hidden if the admin hasn't set a phone number
// in Site Settings yet, rather than showing a broken link.
const hotelPhoneNumber = computed(() => hotel.value.contacts.phone ?? null);
const hotelPhoneHref = computed(() =>
    hotelPhoneNumber.value
        ? `tel:${hotelPhoneNumber.value.replace(/[^+\d]/g, '')}`
        : null,
);

const form = reactive({ name: '', phone: '', message: '' });
const status = ref<'idle' | 'sending' | 'sent' | 'error'>('idle');
const phoneError = ref<string | null>(null);

const successOverlayOpen = ref(false);
let successOverlayTimer: ReturnType<typeof setTimeout> | undefined;

onBeforeUnmount(() => {
    clearTimeout(successOverlayTimer);
});

// The parent modal closes once the success message's own lifetime is over —
// whether that's its ~4s auto-hide or the guest dismissing it early by hand —
// rather than on a second, separately-guessed timer of its own that could
// close the modal (and tear the overlay down with it) before the guest has
// had a chance to read it.
watch(successOverlayOpen, (isOpen, wasOpen) => {
    if (wasOpen && !isOpen) {
        emit('submitted');
    }
});

const onPhoneInput = (event: Event): void => {
    form.phone = formatPhoneInput(
        (event.target as HTMLInputElement).value,
        locale.value,
    );
    phoneError.value = null;
};

// Each line is converted and rounded on its own, the same way the server
// stores it, so the total is the exact sum of what will be recorded.
const totalPrice = computed(() =>
    items.value.reduce(
        (sum, item) => sum + convert(item.basePrice) * item.quantity,
        0,
    ),
);

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
                // The currency and its converted prices are locked into the booking.
                currency: selected.value.code,
                rooms: items.value.map((item) => ({
                    room_id: item.roomId,
                    room_name: item.roomName,
                    period: item.period,
                    price: convert(item.basePrice),
                    quantity: item.quantity,
                })),
            }),
        });

        if (!response.ok) {
            throw new Error(String(response.status));
        }

        Object.assign(form, { name: '', phone: '', message: '' });
        clear();
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

const inputClass =
    'w-full rounded-lg border border-white/25 bg-black/35 px-4 py-2.5 text-sm text-white placeholder-white/60 outline-none focus:border-amber-300 sm:py-3 sm:text-base';
</script>

<template>
    <div class="space-y-4">
        <div>
            <p class="mb-2 text-xs tracking-widest text-amber-300 uppercase">
                {{ t('selectedRooms') }}
            </p>

            <ul v-if="items.length" class="space-y-2">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center justify-between gap-3 rounded-lg bg-white/5 px-3 py-2"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">
                            {{ item.roomName
                            }}<template v-if="item.period">
                                ({{ item.period }})</template
                            >
                            - {{ format(item.basePrice) }}
                        </p>
                        <p class="text-xs text-white/50">{{ t('perNight') }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <button
                            type="button"
                            :aria-label="t('decreaseQuantity')"
                            class="flex size-6 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20"
                            @click="decrement(item.roomId, item.period)"
                        >
                            <Minus class="size-3" />
                        </button>
                        <span class="w-5 text-center text-sm font-semibold">{{
                            item.quantity
                        }}</span>
                        <button
                            type="button"
                            :aria-label="t('increaseQuantity')"
                            class="flex size-6 items-center justify-center rounded-full bg-amber-300 text-slate-900 transition hover:bg-amber-200"
                            @click="
                                add(
                                    item.roomId,
                                    item.roomName,
                                    item.period,
                                    item.basePrice,
                                )
                            "
                        >
                            <Plus class="size-3" />
                        </button>
                        <button
                            type="button"
                            :aria-label="t('removeRoom')"
                            class="ml-1 text-white/40 transition hover:text-red-300"
                            @click="removeItem(item.key)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </li>
            </ul>
            <p
                v-else
                class="rounded-lg border border-dashed border-white/15 px-3 py-4 text-center text-sm text-white/50"
            >
                {{ t('noRoomsSelected') }}
            </p>

            <p
                v-if="items.length"
                class="mt-2 text-right text-sm text-white/70"
            >
                {{ t('total') }}
                <span class="font-semibold text-amber-300">{{
                    formatConverted(totalPrice)
                }}</span>
                {{ t('slashPerNight') }}
            </p>
        </div>

        <form class="space-y-3" @submit.prevent="submit">
            <input
                v-model="form.name"
                required
                maxlength="100"
                :placeholder="t('namePlaceholder')"
                :class="inputClass"
            />
            <input
                :value="form.phone"
                type="tel"
                inputmode="tel"
                required
                :placeholder="t('phonePlaceholder')"
                :class="inputClass"
                @input="onPhoneInput"
            />
            <p v-if="phoneError" class="text-xs text-red-300">
                {{ phoneError }}
            </p>
            <textarea
                v-model="form.message"
                rows="2"
                :placeholder="t('messagePlaceholder')"
                :class="inputClass"
            ></textarea>
            <button
                type="submit"
                :disabled="status === 'sending'"
                class="w-full rounded-full bg-amber-300 px-8 py-2.5 text-sm font-medium text-slate-900 transition hover:bg-amber-200 disabled:opacity-60 sm:w-auto sm:py-3 sm:text-base"
            >
                {{ status === 'sending' ? t('sending') : t('sendRequest') }}
            </button>
            <p
                v-if="hotelPhoneNumber"
                class="flex flex-wrap items-center gap-1.5 text-xs text-white/60 sm:text-sm"
            >
                <Phone class="size-3.5 shrink-0 text-amber-300" />
                {{ t('directCallPrefix') }}
                <a
                    :href="hotelPhoneHref!"
                    class="font-medium text-amber-300 underline-offset-2 hover:underline"
                    >{{ hotelPhoneNumber }}</a
                >
                {{ t('directCallSuffix') }}
            </p>
            <p v-if="status === 'error'" class="text-sm text-red-300">
                {{ t('sendError') }}
            </p>
        </form>

        <BookingSuccessOverlay v-model="successOverlayOpen" />
    </div>
</template>
