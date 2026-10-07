<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { ref } from 'vue';
import SectionBackdrop from '@/components/landing/SectionBackdrop.vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { FaqItem, HotelSettings } from '@/types/landing';

defineProps<{ hotel: HotelSettings; faqs: FaqItem[] }>();
const { t, locale } = useLocale();

const openIndex = ref<number | null>(0);

const toggle = (index: number): void => {
    openIndex.value = openIndex.value === index ? null : index;
};

// Falls back to the English text until the admin translates a given FAQ.
const displayQuestion = (item: FaqItem): string =>
    (locale.value === 'ru' ? item.question_ru : null) || item.question;
const displayAnswer = (item: FaqItem): string =>
    (locale.value === 'ru' ? item.answer_ru : null) || item.answer;
</script>

<template>
    <section
        id="faq"
        class="relative flex min-h-screen snap-start flex-col items-center justify-center overflow-hidden bg-stone-950 px-4 py-20 text-stone-100 sm:px-6 lg:h-screen lg:py-0 lg:pt-16"
    >
        <SectionBackdrop photo-id="photo-1542314831-068cd1dbfeeb" />
        <div class="relative z-10 w-full max-w-3xl">
            <p
                class="text-center text-sm tracking-widest text-amber-300 uppercase"
            >
                {{ t('navFaq') }}
            </p>
            <h2
                class="mt-3 text-center text-3xl leading-tight font-semibold sm:text-4xl md:text-5xl"
            >
                {{ localized(hotel.section_headings.faq, locale) }}
            </h2>

            <div
                class="mt-8 divide-y divide-white/10 rounded-2xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur sm:mt-10"
            >
                <div v-for="(item, index) in faqs" :key="item.question">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-medium transition hover:bg-white/10 sm:px-6 sm:py-5 sm:text-base"
                        :aria-expanded="openIndex === index"
                        @click="toggle(index)"
                    >
                        {{ displayQuestion(item) }}
                        <ChevronDown
                            class="size-5 shrink-0 text-amber-300 transition-transform duration-300"
                            :class="openIndex === index ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        class="grid transition-[grid-template-rows] duration-300 ease-in-out"
                        :style="{
                            gridTemplateRows:
                                openIndex === index ? '1fr' : '0fr',
                        }"
                    >
                        <div class="overflow-hidden">
                            <p
                                class="px-5 pb-4 text-sm text-stone-300 sm:px-6 sm:pb-5 sm:text-base"
                            >
                                {{ displayAnswer(item) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
