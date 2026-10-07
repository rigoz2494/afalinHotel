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
        class="relative flex min-h-screen snap-start flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-20 text-white sm:px-6 lg:h-screen lg:py-0 lg:pt-16"
    >
        <SectionBackdrop photo-id="photo-1520250497591-112f2f40a3f4" />
        <div class="relative z-10 w-full max-w-xl space-y-6 sm:space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-semibold sm:text-4xl">
                    {{ localized(hotel.section_headings.contact, locale) }}
                </h2>
                <p class="mt-2 text-sm text-white/75 sm:text-base">
                    {{ t('contactIntro') }}
                </p>
            </div>

            <BookingForm />

            <div
                class="flex flex-col items-center gap-5 border-t border-white/10 pt-6 text-center sm:gap-6"
            >
                <ul class="space-y-1 text-sm text-white/80 sm:text-base">
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

                <div class="flex gap-3">
                    <a
                        v-for="messenger in messengers"
                        :key="messenger.label"
                        href="#"
                        :aria-label="messenger.label"
                        class="flex size-11 items-center justify-center rounded-full bg-white/10 transition"
                        :class="messenger.class"
                    >
                        <component :is="messenger.icon" class="size-5" />
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
