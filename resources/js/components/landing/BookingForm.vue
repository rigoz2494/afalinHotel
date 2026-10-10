<script setup lang="ts">
import { Minus, Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useBookingSubmission } from '@/composables/useBookingSubmission';
import { useCurrency } from '@/composables/useCurrency';
import { useLocale } from '@/composables/useLocale';
import { useServiceBasket } from '@/composables/useServiceBasket';

const { items, add, decrement, removeItem } = useBookingBasket();
// Only the shared basket's totals are needed here — the picker UI itself
// lives in BookingExtras.vue, writing to this same singleton.
const { items: serviceItems } = useServiceBasket();
const { convert, format, formatConverted } = useCurrency();
const { t } = useLocale();
const {
    form,
    wantsBalcony,
    specialRequests,
    phoneError,
    onPhoneInput,
    submit,
} = useBookingSubmission();

// Each line is converted and rounded on its own, the same way the server
// stores it, so the total is the exact sum of what will be recorded.
const totalPrice = computed(
    () =>
        items.value.reduce(
            (sum, item) => sum + convert(item.basePrice) * item.quantity,
            0,
        ) +
        serviceItems.value.reduce(
            (sum, item) => sum + convert(item.basePrice) * item.quantity,
            0,
        ),
);

const inputClass =
    'w-full rounded-lg border border-white/25 bg-black/35 px-3.5 py-2 text-sm text-white placeholder-white/60 outline-none focus:border-amber-300 sm:px-4 sm:py-3 sm:text-base';
</script>

<template>
    <div class="space-y-3 sm:space-y-4">
        <div>
            <p
                class="mb-1.5 text-xs tracking-widest text-amber-300 uppercase sm:mb-2"
            >
                {{ t('selectedRooms') }}
            </p>

            <ul v-if="items.length" class="space-y-1.5 sm:space-y-2">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center justify-between gap-3 rounded-lg bg-white/5 px-2.5 py-1.5 sm:px-3 sm:py-2"
                >
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium sm:text-sm">
                            {{ item.roomName
                            }}<template v-if="item.period">
                                ({{ item.period }})</template
                            >
                            - {{ format(item.basePrice) }}
                        </p>
                        <p class="text-[11px] text-white/50 sm:text-xs">
                            {{ t('perNight') }}
                        </p>
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
                class="rounded-lg border border-dashed border-white/15 px-3 py-2.5 text-center text-xs text-white/50 sm:py-4 sm:text-sm"
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

        <!--
            No submit button in here: it lives in BookingSubmit.vue instead,
            linked back to this exact form via `form="booking-form"` — the
            mobile layout needs it to appear after the services picker (a
            sibling elsewhere in the DOM), which an in-form button couldn't
            reach, while `@submit.prevent` here still fires on Enter inside
            any of these fields either way.
        -->
        <form
            id="booking-form"
            class="space-y-2.5 sm:space-y-3"
            @submit.prevent="submit"
        >
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
            <label
                class="flex items-center gap-2 text-xs text-white/80 select-none sm:gap-2.5 sm:text-sm"
            >
                <input
                    v-model="wantsBalcony"
                    type="checkbox"
                    class="size-3.5 rounded border-white/25 bg-black/35 text-amber-300 focus:ring-amber-300/50 sm:size-4"
                />
                {{ t('balconyPreference') }}
            </label>
            <textarea
                v-model="specialRequests"
                rows="2"
                maxlength="2000"
                :placeholder="t('specialRequestsPlaceholder')"
                :aria-label="t('specialRequestsLabel')"
                :class="inputClass"
            ></textarea>
        </form>
    </div>
</template>
