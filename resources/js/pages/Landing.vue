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
import Preloader from '@/components/landing/Preloader.vue';
import PricingSection from '@/components/landing/PricingSection.vue';
import RoomsSection from '@/components/landing/RoomsSection.vue';
import SideNavDots from '@/components/landing/SideNavDots.vue';
import {
    faqItems as mockFaqItems,
    mockAdditionalServices,
    mockHotel,
    mockPricing,
    mockRooms,
    roomFallbackImages,
} from '@/data/landingMock';
import { provideAdditionalServices } from '@/composables/useAdditionalServices';
import { provideCurrency } from '@/composables/useCurrency';
import { provideHotelSettings } from '@/composables/useHotelSettings';
import { localized, provideLocale } from '@/composables/useLocale';
import type {
    AdditionalService,
    CurrencyOption,
    FaqItem,
    HotelSettings,
    PricingTable,
    Room,
} from '@/types/landing';

const props = defineProps<{
    hotel: Partial<HotelSettings>;
    currencies: CurrencyOption[];
    rooms: { data: Room[] };
    faqs: { data: FaqItem[] };
    additionalServices: { data: AdditionalService[] };
    pricing: {
        columns: PricingTable['columns'];
        rows: { data: PricingTable['rows'] };
    };
    /** Set when this request came in through a room's own `/rooms/{slug}` URL. */
    focusRoomSlug?: string | null;
    /** The absolute URL of this exact request, for the <link rel="canonical"> tag. */
    canonicalUrl?: string | null;
}>();

// Fall back to mock data until the database is seeded. `tagline` is the
// signal, not `hotel_name`: the latter is always present and truthy once
// defaulted server-side (see HotelSettingsResource), even on a fresh install.
const isHotelSeeded = Boolean(props.hotel?.tagline);
const hotel = ref<HotelSettings>({
    ...mockHotel,
    ...Object.fromEntries(
        Object.entries(props.hotel ?? {}).filter(([, value]) =>
            Array.isArray(value) ? value.length > 0 : value,
        ),
    ),
    // An empty banner in the database means "no promo", not "use the mock".
    promo_banner: isHotelSeeded
        ? (props.hotel.promo_banner ?? null)
        : mockHotel.promo_banner,
});
const rooms = ref<Room[]>(
    (props.rooms.data.length ? props.rooms.data : mockRooms).map(
        (room, index) => ({
            ...room,
            images: room.images.length
                ? room.images
                : [roomFallbackImages[index % roomFallbackImages.length]],
        }),
    ),
);
const pricing = ref<PricingTable>(
    props.pricing.rows.data.length
        ? { columns: props.pricing.columns, rows: props.pricing.rows.data }
        : mockPricing,
);
const faqs = ref<FaqItem[]>(
    props.faqs.data.length ? props.faqs.data : mockFaqItems,
);
const additionalServices = ref<AdditionalService[]>(
    props.additionalServices.data.length
        ? props.additionalServices.data
        : mockAdditionalServices,
);

provideCurrency(() => props.currencies);
provideHotelSettings(hotel);
provideAdditionalServices(additionalServices);
const { t, locale } = provideLocale();

// Reactive, so the nav labels translate instantly when the locale changes.
// These are short, fixed UI-dictionary labels, not the admin-edited section
// headings shown as each section's own on-page title — those can be as long
// as the admin likes, which would overflow a compact navbar, so the nav
// intentionally keeps its own short equivalents instead of reusing them.
const navLinks = computed(() => [
    { id: 'hero', label: t('navHome') },
    { id: 'rooms', label: t('navRooms') },
    { id: 'pricing', label: t('navPricing') },
    { id: 'about', label: t('navAbout') },
    { id: 'faq', label: t('navFaq') },
    { id: 'contact', label: t('navContact') },
]);

const scroller = ref<HTMLElement | null>(null);
const { activeId, scrollTo } = useScrollSpy(
    navLinks.value.map((link) => link.id),
    scroller,
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
// Fully self-contained: used for the <title> tag as well as og:title and
// twitter:title. app.ts's title() callback is a pass-through, not a brand
// suffix, specifically so this can't double up into "Room — Afalina - Afalina".
const seoTitle = computed(() =>
    focusRoom.value
        ? `${localized(focusRoom.value.name, locale.value)} — ${hotelName.value}`
        : `${hotelName.value}${hotel.value.tagline ? ` — ${hotel.value.tagline}` : ''}`,
);
const seoDescription = computed(
    () =>
        (focusRoom.value &&
            localized(focusRoom.value.description, locale.value)) ||
        hotel.value.tagline ||
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
    <div>
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
            @navigate="scrollTo"
        />
        <main
            ref="scroller"
            class="h-screen snap-y snap-mandatory overflow-y-scroll scroll-smooth"
        >
            <HeroSection :hotel="hotel" />
            <RoomsSection
                :hotel="hotel"
                :rooms="rooms"
                :pricing="pricing"
                :focus-slug="focusRoomSlug"
            />
            <PricingSection
                :hotel="hotel"
                :pricing="pricing"
                :additional-services="additionalServices"
            />
            <AboutSection :hotel="hotel" />
            <FaqSection :hotel="hotel" :faqs="faqs" />
            <ContactSection :hotel="hotel" />
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
