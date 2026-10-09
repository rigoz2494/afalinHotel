<script setup lang="ts">
import { MessageCircle, Phone, Send } from '@lucide/vue';
import BookingForm from '@/components/landing/BookingForm.vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();

const messengers = [
    { label: 'Telegram', icon: Send, class: 'hover:bg-sky-500' },
    { label: 'WhatsApp', icon: MessageCircle, class: 'hover:bg-green-500' },
    { label: 'Viber', icon: Phone, class: 'hover:bg-purple-500' },
];
</script>

<template>
    <section
        id="contact"
        class="relative flex min-h-screen snap-start flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-10 text-white sm:px-6 sm:py-14 lg:h-screen lg:py-0 lg:pt-16"
    >
        <SectionBackdrop photo-id="photo-1520250497591-112f2f40a3f4" />
        <div
            class="relative z-10 w-full max-w-xl space-y-4 sm:space-y-6 lg:space-y-8"
        >
            <div class="text-center">
                <h2 class="text-2xl font-semibold sm:text-3xl lg:text-4xl">
                    {{ localized(hotel.section_headings.contact, locale) }}
                </h2>
                <p class="mt-1.5 text-xs text-white/75 sm:mt-2 sm:text-base">
                    {{ t('contactIntro') }}
                </p>
            </div>

            <BookingForm />

            <div
                class="flex flex-col items-center gap-3 border-t border-white/10 pt-4 text-center sm:gap-5 sm:pt-6 lg:gap-6"
            >
                <ul
                    class="space-y-1 text-xs text-white/80 sm:text-sm lg:text-base"
                >
                    <li v-for="(value, key) in hotel.contacts" :key="key">
                        <span class="capitalize">{{ key }}</span
                        >:
                        <a
                            v-if="key === 'phone'"
                            :href="`tel:${value.replace(/[^+\d]/g, '')}`"
                            class="underline-offset-2 hover:underline"
                            >{{ value }}</a
                        >
                        <template v-else>{{ value }}</template>
                    </li>
                </ul>

                <div class="flex gap-2.5 sm:gap-3">
                    <a
                        v-for="messenger in messengers"
                        :key="messenger.label"
                        href="#"
                        :aria-label="messenger.label"
                        class="flex size-9 items-center justify-center rounded-full bg-white/10 transition sm:size-11"
                        :class="messenger.class"
                    >
                        <component
                            :is="messenger.icon"
                            class="size-4 sm:size-5"
                        />
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
