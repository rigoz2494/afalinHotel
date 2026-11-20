<script setup lang="ts">
import BookingExtras from '@/components/landing/BookingExtras.vue';
import BookingForm from '@/components/landing/BookingForm.vue';
import BookingSubmit from '@/components/landing/BookingSubmit.vue';
import DirectCallCta from '@/components/landing/DirectCallCta.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings }>();
const { t, locale } = useLocale();
</script>

<template>
    <section
        id="contact"
        class="landing-screen bg-coast-paper text-coast-ink relative flex scroll-mt-14 flex-col items-center justify-center overflow-hidden px-4 py-10 sm:scroll-mt-16 sm:px-6 sm:py-14 lg:py-8"
    >
        <div
            class="relative z-10 mx-auto w-full max-w-6xl space-y-3 sm:space-y-6 2xl:max-w-[104rem]"
        >
            <div class="text-center">
                <h2
                    class="font-coastal-heading text-2xl font-semibold sm:text-3xl lg:text-4xl"
                >
                    {{ localized(hotel.section_headings.contact, locale) }}
                </h2>
                <p class="text-coast-muted mt-1.5 text-xs sm:mt-2 sm:text-base">
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
                v-if="Object.keys(hotel.contacts).length"
                class="border-coast-line flex flex-col items-center gap-2.5 border-t pt-3 text-center sm:gap-5 sm:pt-6 lg:flex-row lg:justify-between lg:text-left"
            >
                <ul
                    class="text-coast-muted space-y-1 text-xs sm:text-sm lg:flex lg:flex-wrap lg:space-y-0 lg:gap-x-5 lg:gap-y-1"
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
            </div>
        </div>
    </section>
</template>
