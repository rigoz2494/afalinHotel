<script setup lang="ts">
import { Minus, Plus } from '@lucide/vue';
import { useAdditionalServices } from '@/composables/useAdditionalServices';
import { useCurrency } from '@/composables/useCurrency';
import { localized, useLocale } from '@/composables/useLocale';
import { useServiceBasket } from '@/composables/useServiceBasket';

const {
    quantityFor: serviceQuantityFor,
    add: addService,
    decrement: decrementService,
} = useServiceBasket();
const { format } = useCurrency();
const { t, locale } = useLocale();
const additionalServices = useAdditionalServices();
</script>

<template>
    <div v-if="additionalServices.length">
        <p
            class="text-coast-blue mb-1.5 text-xs tracking-widest uppercase sm:mb-2"
        >
            {{ t('addExtraServices') }}
        </p>
        <ul class="space-y-1.5 sm:space-y-2">
            <li
                v-for="service in additionalServices"
                :key="service.id"
                class="bg-coast-mist flex items-center justify-between gap-3 rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2"
            >
                <div class="min-w-0">
                    <p class="truncate text-xs font-medium sm:text-sm">
                        {{ localized(service.name, locale) }}
                    </p>
                    <p class="text-coast-muted text-[11px] sm:text-xs">
                        {{ format(service.price) }}
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-1.5">
                    <button
                        v-if="serviceQuantityFor(service.id) === 0"
                        type="button"
                        :aria-label="t('addOneMore')"
                        class="hover:bg-coast-blue bg-coast-line flex size-6 items-center justify-center rounded-full transition hover:text-white sm:size-7"
                        @click="
                            addService(
                                service.id,
                                localized(service.name, locale),
                                service.price,
                            )
                        "
                    >
                        <Plus class="size-3 sm:size-3.5" />
                    </button>
                    <div
                        v-else
                        class="bg-coast-line flex items-center gap-1.5 rounded-full p-1 sm:gap-2 sm:p-1.5"
                    >
                        <button
                            type="button"
                            :aria-label="t('removeOne')"
                            class="bg-coast-line hover:bg-coast-mist flex size-5 items-center justify-center rounded-full transition sm:size-6"
                            @click="decrementService(service.id)"
                        >
                            <Minus class="size-3" />
                        </button>
                        <span
                            class="w-4 text-center text-xs font-semibold sm:w-5 sm:text-sm"
                            >{{ serviceQuantityFor(service.id) }}</span
                        >
                        <button
                            type="button"
                            :aria-label="t('addOneMore')"
                            class="bg-coast-blue hover:bg-coast-ink flex size-5 items-center justify-center rounded-full text-white transition sm:size-6"
                            @click="
                                addService(
                                    service.id,
                                    localized(service.name, locale),
                                    service.price,
                                )
                            "
                        >
                            <Plus class="size-3" />
                        </button>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>
