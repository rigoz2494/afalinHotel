<script setup lang="ts">
import { ArrowUp, KeyRound } from '@lucide/vue';
import ClientOnly from '@/components/ClientOnly.vue';
import { useBookingBasket } from '@/composables/useBookingBasket';
import { useLocale } from '@/composables/useLocale';

defineProps<{ visible: boolean }>();

defineEmits<{ top: []; openBooking: [] }>();

const { totalCount } = useBookingBasket();
const { t } = useLocale();
</script>

<template>
    <ClientOnly>
        <Teleport to="body">
            <div
                class="fixed right-4 bottom-5 z-50 flex flex-col items-end gap-3 sm:right-8 sm:bottom-8"
            >
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-2"
                    leave-active-class="transition duration-200 ease-in"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <button
                        v-if="visible"
                        type="button"
                        :aria-label="t('backToTop')"
                        class="flex size-11 items-center justify-center rounded-full bg-white/90 text-slate-900 shadow-lg backdrop-blur transition hover:bg-white sm:size-12"
                        @click="$emit('top')"
                    >
                        <ArrowUp class="size-5" />
                    </button>
                </Transition>

                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-75"
                    leave-active-class="transition duration-200 ease-in"
                    leave-to-class="opacity-0 scale-75"
                >
                    <button
                        v-if="totalCount > 0"
                        type="button"
                        :aria-label="t('completeBooking')"
                        class="relative flex size-12 items-center justify-center rounded-full bg-amber-300 text-slate-900 shadow-lg transition hover:bg-amber-200 sm:size-14"
                        @click="$emit('openBooking')"
                    >
                        <KeyRound class="size-5 sm:size-6" />
                        <span
                            class="absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full bg-sky-600 text-[11px] font-bold text-white ring-2 ring-white/80"
                        >
                            {{ totalCount }}
                        </span>
                    </button>
                </Transition>
            </div>
        </Teleport>
    </ClientOnly>
</template>
