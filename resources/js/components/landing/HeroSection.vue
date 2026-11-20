<script setup lang="ts">
import { ArrowRight, Gift } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { localized, useLocale } from '@/composables/useLocale';
import type { HotelSettings } from '@/types/landing';

const props = defineProps<{ hotel: HotelSettings; hasRooms: boolean }>();
defineEmits<{ viewRooms: [] }>();
const { t, locale } = useLocale();

// null (hidden) when the admin hasn't set a promo in either language;
// otherwise the guest's own language, falling back to English if only
// that one was filled in — see HotelSetting::normalizedPromoBanner().
const promoBanner = computed(() =>
    props.hotel.promo_banner
        ? localized(props.hotel.promo_banner, locale.value)
        : null,
);

const activeSlide = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    if (props.hotel.hero_images.length > 1) {
        timer = setInterval(() => {
            activeSlide.value =
                (activeSlide.value + 1) % props.hotel.hero_images.length;
        }, 5000);
    }
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <section id="hero" class="bg-coast-paper text-coast-ink pt-14 sm:pt-16">
        <div
            class="mx-auto max-w-[clamp(96rem,76vw,122rem)] px-4 pb-6 sm:px-6 sm:pb-8 lg:px-8"
        >
            <div
                class="flex flex-wrap items-baseline justify-between gap-x-8 gap-y-3 py-6 sm:py-8 lg:px-4"
            >
                <h1
                    class="font-coastal-heading text-[clamp(2.25rem,4.4vw,4.25rem)] leading-[1.08] font-medium tracking-tight"
                >
                    {{
                        localized(hotel.tagline, locale) ||
                        localized(hotel.hotel_name, locale)
                    }}
                </h1>
                <a
                    v-if="hasRooms"
                    href="#rooms"
                    class="border-coast-blue text-coast-blue hover:text-coast-ink inline-flex shrink-0 items-center gap-2 border-b pb-1 text-sm font-semibold transition sm:text-base"
                    @click.prevent="$emit('viewRooms')"
                >
                    {{ t('viewRooms') }}
                    <ArrowRight class="size-5" aria-hidden="true" />
                </a>
            </div>
            <div
                v-if="hotel.hero_images.length"
                class="bg-coast-mist relative aspect-[16/10] overflow-hidden rounded-xl sm:aspect-[12/5] lg:aspect-[16/5]"
            >
                <img
                    v-for="(src, index) in hotel.hero_images"
                    :key="src"
                    :src="src"
                    alt=""
                    :loading="index === 0 ? 'eager' : 'lazy'"
                    decoding="async"
                    class="absolute inset-0 h-full w-full object-cover object-center transition-opacity duration-1000 motion-reduce:transition-none lg:object-[50%_32%]"
                    :class="index === activeSlide ? 'opacity-100' : 'opacity-0'"
                />
            </div>
            <p
                v-if="promoBanner"
                class="text-coast-muted mt-3 flex items-start gap-2 text-sm lg:px-4"
            >
                <Gift
                    class="text-coast-blue mt-0.5 size-4 shrink-0"
                    aria-hidden="true"
                />
                {{ promoBanner }}
            </p>
        </div>
    </section>
</template>
