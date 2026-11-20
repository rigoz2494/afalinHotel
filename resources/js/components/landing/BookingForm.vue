<script setup lang="ts">
import { Minus, Plus, Trash2 } from '@lucide/vue';
import CustomCheckbox from '@/components/landing/CustomCheckbox.vue';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useBookingSubmission } from '@/composables/useBookingSubmission';
import { useCurrency } from '@/composables/useCurrency';
import { useLocale } from '@/composables/useLocale';

const { items, add, decrement, removeItem } = useBookingBasket();
const { format } = useCurrency();
const { t } = useLocale();
const {
    form,
    wantsBalcony,
    specialRequests,
    phoneError,
    onPhoneInput,
    submit,
} = useBookingSubmission();

const inputClass =
    'w-full rounded-lg border border-coast-line bg-white px-3.5 py-2 text-sm text-coast-ink placeholder-coast-muted outline-none focus:border-coast-blue sm:px-4 sm:py-3 sm:text-base';
</script>

<template>
    <div class="space-y-3 sm:space-y-4">
        <div>
            <p
                class="text-coast-blue mb-1.5 text-xs tracking-widest uppercase sm:mb-2"
            >
                {{ t('selectedRooms') }}
            </p>

            <ul v-if="items.length" class="space-y-1.5 sm:space-y-2">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="bg-coast-mist flex items-center justify-between gap-3 rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2"
                >
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium sm:text-sm">
                            {{ item.roomName
                            }}<template v-if="item.period">
                                ({{ item.period }})</template
                            >
                            - {{ format(item.basePrice) }}
                        </p>
                        <p class="text-coast-muted text-[11px] sm:text-xs">
                            {{ t('perNight') }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <button
                            type="button"
                            :aria-label="t('decreaseQuantity')"
                            class="bg-coast-line hover:bg-coast-mist flex size-6 items-center justify-center rounded-full transition"
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
                            class="bg-coast-blue hover:bg-coast-ink flex size-6 items-center justify-center rounded-full text-white transition"
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
                            class="text-coast-muted ml-1 transition hover:text-red-700"
                            @click="removeItem(item.key)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </li>
            </ul>
            <p
                v-else
                class="border-coast-line text-coast-muted rounded-lg border border-dashed px-3 py-2.5 text-center text-xs sm:py-4 sm:text-sm"
            >
                {{ t('noRoomsSelected') }}
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
            <p v-if="phoneError" class="text-xs text-red-700">
                {{ phoneError }}
            </p>
            <textarea
                v-model="form.message"
                rows="2"
                :placeholder="t('messagePlaceholder')"
                :class="inputClass"
            ></textarea>
            <label
                class="text-coast-muted flex items-center gap-2 text-xs select-none sm:gap-2.5 sm:text-sm"
            >
                <CustomCheckbox v-model="wantsBalcony" />
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
