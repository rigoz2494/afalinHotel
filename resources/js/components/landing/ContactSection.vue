<script setup lang="ts">
import { MessageCircle, Phone, Send } from '@lucide/vue';
import BookingExtras from '@/components/landing/BookingExtras.vue';
import BookingForm from '@/components/landing/BookingForm.vue';
import BookingSubmit from '@/components/landing/BookingSubmit.vue';
import DirectCallCta from '@/components/landing/DirectCallCta.vue';
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
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-5 text-white sm:px-6 sm:py-14 lg:h-screen lg:snap-start lg:py-0 lg:pt-16"
    >
        <SectionBackdrop photo-id="photo-1520250497591-112f2f40a3f4" />
        <div
            class="relative z-10 mx-auto w-full max-w-6xl space-y-3 sm:space-y-6 lg:space-y-8"
        >
            <div class="text-center">
                <h2 class="text-2xl font-semibold sm:text-3xl lg:text-4xl">
                    {{ localized(hotel.section_headings.contact, locale) }}
                </h2>
                <p class="mt-1.5 text-xs text-white/75 sm:mt-2 sm:text-base">
                    {{ t('contactIntro') }}
                </p>
            </div>

            <!--
                Below md: a strict single-column flow (`flex flex-col`, not
                a grid at all — a bare `grid` with no explicit columns can
                still auto-place children side by side once any of them has
                intrinsic min-content width to fill, which is exactly what
                broke this on a phone before), in this exact order: selected
                rooms, the contact fields, the services picker, then the
                submit button immediately followed by the call-us card.
                At md: an explicit 2-column/2-row grid instead — each piece
                placed by `md:col-start-*`/`md:row-start-*` rather than by
                DOM order, so the desktop column grouping (services + call-us
                on the left; rooms/fields + submit on the right) can differ
                from the mobile sequence above without moving anything in
                the markup.
            -->
            <div
                class="flex w-full flex-col gap-3 md:grid md:grid-cols-2 md:items-start md:gap-x-12 md:gap-y-6"
            >
                <BookingForm class="md:col-start-2 md:row-start-1" />
                <BookingExtras class="md:col-start-1 md:row-start-1" />
                <BookingSubmit class="md:col-start-2 md:row-start-2" />
                <DirectCallCta class="md:col-start-1 md:row-start-2" />
            </div>

            <div
                class="flex flex-col items-center gap-2.5 border-t border-white/10 pt-3 text-center sm:gap-5 sm:pt-6 lg:gap-6"
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
