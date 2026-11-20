<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import SiteHeader from '@/components/landing/SiteHeader.vue';
import { useScrollSpy } from '@/composables/useScrollSpy';
import BookingModal from '@/components/landing/BookingModal.vue';
import ContactSection from '@/components/landing/ContactSection.vue';
import AboutSection from '@/components/landing/AboutSection.vue';
import FaqSection from '@/components/landing/FaqSection.vue';
import FloatingActions from '@/components/landing/FloatingActions.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import LocationSection from '@/components/landing/LocationSection.vue';
import Preloader from '@/components/landing/Preloader.vue';
import PricingSection from '@/components/landing/PricingSection.vue';
import ReviewsSection from '@/components/landing/ReviewsSection.vue';
import RoomsSection from '@/components/landing/RoomsSection.vue';
import SideNavDots from '@/components/landing/SideNavDots.vue';
import {
    faqItems as mockFaqItems,
    mockAdditionalServices,
    mockGalleryImages,
    mockHotel,
    mockPricing,
    mockRooms,
} from '@/data/landingMock';
import { provideAdditionalServices } from '@/composables/useAdditionalServices';
import { provideCurrency } from '@/composables/useCurrency';
import { provideHotelSettings } from '@/composables/useHotelSettings';
import { localized, provideLocale } from '@/composables/useLocale';
import type {
    AdditionalService,
    CurrencyOption,
    FaqItem,
    GalleryImage,
    HotelSettings,
    PricingTable,
    Room,
} from '@/types/landing';

const props = defineProps<{
    hotel: HotelSettings;
    showDemoData: boolean;
    currencies: CurrencyOption[];
    rooms: { data: Room[] };
    faqs: { data: FaqItem[] };
    additionalServices: { data: AdditionalService[] };
    gallery: { data: GalleryImage[] };
    pricing: {
        columns: PricingTable['columns'];
        rows: { data: PricingTable['rows'] };
    };
    /** Set when this request came in through a room's own `/rooms/{slug}` URL. */
    focusRoomSlug?: string | null;
    /** The absolute URL of this exact request, for the <link rel="canonical"> tag. */
    canonicalUrl?: string | null;
}>();

const useDemoHotel = props.showDemoData && !props.hotel.tagline;
const hotel = ref<HotelSettings>({
    ...props.hotel,
    ...(useDemoHotel
        ? {
              tagline: mockHotel.tagline,
              promo_banner: mockHotel.promo_banner,
              contacts: Object.keys(props.hotel.contacts).length
                  ? props.hotel.contacts
                  : mockHotel.contacts,
          }
        : {}),
});
const rooms = ref<Room[]>(
    props.showDemoData && !props.rooms.data.length
        ? mockRooms
        : props.rooms.data,
);
const pricing = ref<PricingTable>(
    props.showDemoData &&
        !props.rooms.data.length &&
        !props.pricing.rows.data.length
        ? mockPricing
        : { columns: props.pricing.columns, rows: props.pricing.rows.data },
);
const faqs = ref<FaqItem[]>(
    props.showDemoData && !props.faqs.data.length
        ? mockFaqItems
        : props.faqs.data,
);
const additionalServices = ref<AdditionalService[]>(
    props.showDemoData && !props.additionalServices.data.length
        ? mockAdditionalServices
        : props.additionalServices.data,
);
const gallery = ref<GalleryImage[]>(
    props.showDemoData && !props.gallery.data.length
        ? mockGalleryImages
        : props.gallery.data,
);
const hasPricing = computed(
    () => pricing.value.rows.length > 0 && pricing.value.columns.length > 0,
);

provideCurrency(() => props.currencies);
provideHotelSettings(hotel);
provideAdditionalServices(additionalServices);
// A strict "Global Language Mode" (see ManageHotelSettings) pins the whole
// site to one language, overriding whatever the guest had chosen before.
const forcedLocale = computed(() => {
    if (hotel.value.locale_mode === 'ru_only') {
        return 'ru' as const;
    }

    if (hotel.value.locale_mode === 'en_only') {
        return 'en' as const;
    }

    return undefined;
});
const { t, locale } = provideLocale(forcedLocale.value);
const hasAbout = computed(
    () =>
        gallery.value.length > 0 ||
        Boolean(localized(hotel.value.about_story, locale.value)) ||
        Object.values(hotel.value.about_stats).some((stat) =>
            Boolean(localized(stat.value, locale.value)),
        ),
);
const showLocalizationControls = computed(
    () => hotel.value.locale_mode === 'multilingual',
);

// Reactive, so the nav labels translate instantly when the locale changes.
// These are short, fixed UI-dictionary labels, not the admin-edited section
// headings shown as each section's own on-page title — those can be as long
// as the admin likes, which would overflow a compact navbar, so the nav
// intentionally keeps its own short equivalents instead of reusing them.
const navLinks = computed(() => [
    { id: 'hero', label: t('navHome') },
    ...(rooms.value.length ? [{ id: 'rooms', label: t('navRooms') }] : []),
    ...(hasPricing.value ? [{ id: 'pricing', label: t('navPricing') }] : []),
    ...(hasAbout.value ? [{ id: 'about', label: t('navAbout') }] : []),
    ...(faqs.value.length ? [{ id: 'faq', label: t('navFaq') }] : []),
    { id: 'contact', label: t('navContact') },
    { id: 'location', label: t('navLocation') },
    { id: 'reviews', label: t('navReviews') },
]);

const { activeId, scrollTo } = useScrollSpy(
    navLinks.value.map((link) => link.id),
);

const showBackToTop = computed(() => activeId.value !== 'hero');
const bookingModalOpen = ref(false);

// SEO: the room this exact URL is about, if any. Its own name, description
// and photo drive the <title>/meta description/og:image below, so each
// room's `/rooms/{slug}` URL (see sitemap.xml) is distinct and indexable
// rather than a copy of the homepage's tags.
const focusRoom = computed(() =>
    props.focusRoomSlug
        ? (rooms.value.find((room) => room.slug === props.focusRoomSlug) ??
          null)
        : null,
);
// The brand's own name, in the guest's chosen language — never a hardcoded
// English fallback, so "Афалина" shows for Russian guests everywhere the
// brand appears: the <title>, the header logo and the OG/Twitter cards. Just
// the name itself, with no "hotel"/"отель" wording attached to it anywhere.
const hotelName = computed(() =>
    localized(hotel.value.hotel_name, locale.value),
);
// Empty when the admin hasn't set a tagline in the guest's language (or at
// all) — `localized()` already falls back between languages the same way
// it does for every other bilingual field.
const taglineText = computed(() =>
    localized(hotel.value.tagline, locale.value),
);
// Fully self-contained: used for the <title> tag as well as og:title and
// twitter:title. app.ts's title() callback is a pass-through, not a brand
// suffix, specifically so this can't double up into "Room — Afalina - Afalina".
const seoTitle = computed(() =>
    focusRoom.value
        ? `${localized(focusRoom.value.name, locale.value)} — ${hotelName.value}`
        : `${hotelName.value}${taglineText.value ? ` — ${taglineText.value}` : ''}`,
);
const seoDescription = computed(
    () =>
        (focusRoom.value &&
            localized(focusRoom.value.description, locale.value)) ||
        taglineText.value ||
        t('metaDefaultDescription', { hotel: hotelName.value }),
);
const seoImage = computed(
    () => focusRoom.value?.images[0] ?? hotel.value.hero_images[0] ?? null,
);
// Telegram, WhatsApp and most other apps read OpenGraph tags, not this
// locale ref, for their link preview — it's only ever as fresh as the last
// time the page was shared, not this guest's current language choice.
const ogLocale = computed(() => (locale.value === 'ru' ? 'ru_RU' : 'en_US'));

// A deep link to a room (see routes/web.php) opens the same single page
// already scrolled to it, rather than a separate, empty-feeling room page.
onMounted(() => {
    if (focusRoom.value) {
        scrollTo('rooms');
    }
});
</script>

<template>
    <div :data-hotel-season="hotel.seasonal_theme">
        <Head>
            <title>{{ seoTitle }}</title>
            <meta name="description" :content="seoDescription" />
            <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />

            <meta property="og:type" content="website" />
            <meta property="og:site_name" :content="hotelName" />
            <meta property="og:title" :content="seoTitle" />
            <meta property="og:description" :content="seoDescription" />
            <meta
                v-if="canonicalUrl"
                property="og:url"
                :content="canonicalUrl"
            />
            <meta v-if="seoImage" property="og:image" :content="seoImage" />
            <meta property="og:locale" :content="ogLocale" />

            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="seoTitle" />
            <meta name="twitter:description" :content="seoDescription" />
            <meta v-if="seoImage" name="twitter:image" :content="seoImage" />
        </Head>
        <Preloader />
        <SiteHeader
            :hotel-name="hotelName"
            :links="navLinks"
            :active-id="activeId"
            :show-localization-controls="showLocalizationControls"
            @navigate="scrollTo"
            @open-booking="bookingModalOpen = true"
        />
        <main
            class="bg-coast-paper scroll-smooth lg:h-screen lg:overflow-y-auto"
        >
            <HeroSection
                :hotel="hotel"
                :has-rooms="rooms.length > 0"
                @view-rooms="scrollTo('rooms')"
            />
            <svg
                v-if="rooms.length"
                aria-hidden="true"
                class="bg-coast-paper fill-coast-mist block h-6 w-full sm:h-9"
                viewBox="0 0 1440 48"
                preserveAspectRatio="none"
                focusable="false"
            >
                <path
                    d="M0 29C240 29 296 6 528 6S882 39 1114 39C1252 39 1332 24 1440 15V48H0Z"
                />
            </svg>
            <RoomsSection
                v-if="rooms.length"
                :hotel="hotel"
                :rooms="rooms"
                :pricing="pricing"
                :focus-slug="focusRoomSlug"
            />
            <PricingSection
                v-if="hasPricing"
                :hotel="hotel"
                :pricing="pricing"
                :additional-services="additionalServices"
            />
            <AboutSection v-if="hasAbout" :hotel="hotel" :images="gallery" />
            <FaqSection v-if="faqs.length" :hotel="hotel" :faqs="faqs" />
            <ContactSection :hotel="hotel" />
            <div
                class="landing-screen lg:bg-coast-mist lg:border-coast-line relative lg:flex lg:items-center lg:border-t"
            >
                <div
                    class="lg:max-w-landing lg:mx-auto lg:grid lg:w-full lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] lg:gap-x-10 lg:gap-y-5 lg:px-12 lg:py-12 2xl:gap-x-16"
                >
                    <LocationSection :hotel="hotel" />
                    <ReviewsSection :hotel="hotel" />
                </div>
            </div>
        </main>

        <SideNavDots
            :links="navLinks"
            :active-id="activeId"
            @navigate="scrollTo"
        />

        <FloatingActions
            :visible="showBackToTop"
            @top="scrollTo('hero')"
            @open-booking="bookingModalOpen = true"
        />
        <BookingModal v-model="bookingModalOpen" />
    </div>
</template>
