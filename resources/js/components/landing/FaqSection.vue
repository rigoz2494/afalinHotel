<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { ref } from 'vue';
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
        class="bg-coast-mist text-coast-ink relative flex scroll-mt-14 flex-col items-center px-4 py-10 sm:scroll-mt-16 sm:px-6 sm:py-14"
    >
        <div
            class="relative z-10 w-full max-w-3xl 2xl:grid 2xl:max-w-[104rem] 2xl:grid-cols-[minmax(20rem,1fr)_minmax(0,2fr)] 2xl:items-start 2xl:gap-12"
        >
            <div>
                <p
                    class="text-coast-blue text-center text-sm tracking-widest uppercase 2xl:text-left"
                >
                    {{ t('navFaq') }}
                </p>
                <h2
                    class="font-coastal-heading mt-3 text-center text-3xl leading-tight font-medium sm:text-4xl md:text-5xl 2xl:text-left"
                >
                    {{ localized(hotel.section_headings.faq, locale) }}
                </h2>
            </div>

            <div
                class="divide-coast-line border-coast-line mt-8 divide-y rounded-2xl border bg-white shadow-sm backdrop-blur sm:mt-10 2xl:mt-0"
            >
                <div v-for="(item, index) in faqs" :key="item.question">
                    <button
                        type="button"
                        class="hover:bg-coast-mist flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-medium transition sm:px-6 sm:py-5 sm:text-base"
                        :aria-expanded="openIndex === index"
                        @click="toggle(index)"
                    >
                        {{ displayQuestion(item) }}
                        <ChevronDown
                            class="text-coast-blue size-5 shrink-0 transition-transform duration-300"
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
                                class="text-coast-muted px-5 pb-4 text-sm sm:px-6 sm:pb-5 sm:text-base"
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
