import {
    computed,
    inject,
    type ComputedRef,
    type InjectionKey,
    onMounted,
    provide,
    ref,
} from 'vue';

export type Locale = 'en' | 'ru';

export type LocaleContext = {
    locale: ComputedRef<Locale>;
    locales: { code: Locale; label: string }[];
    setLocale: (locale: Locale) => void;
    /** Translates a key, replacing any `{name}` placeholders with `params.name`. */
    t: (
        key: TranslationKey,
        params?: Record<string, string | number>,
    ) => string;
};

/**
 * All UI chrome that lives in these Vue components: navigation, buttons,
 * aria-labels, form fields and status messages. This is not a full site
 * translation system — section headings, room/FAQ content and contact
 * details are admin-edited text stored once in the database, in whichever
 * language the admin wrote it, and are not translated by this dictionary.
 */
const translations = {
    // Navigation (header, mobile drawer, side dots)
    // Short on purpose: these drive the compact navbar (see SiteHeader.vue),
    // which has no room for the longer, admin-edited section headings.
    navHome: { en: 'Home', ru: 'Главная' },
    navRooms: { en: 'Rooms', ru: 'Номера' },
    navPricing: { en: 'Prices', ru: 'Цены' },
    navAbout: { en: 'About Us', ru: 'О нас' },
    navFaq: { en: 'FAQ', ru: 'Вопросы' },
    navContact: { en: 'Contacts', ru: 'Контакты' },
    localizationControls: {
        en: 'Localization controls',
        ru: 'Настройки языка и валюты',
    },
    languageLabel: { en: 'Language', ru: 'Язык' },
    currencyLabel: { en: 'Currency', ru: 'Валюта' },
    loading: { en: 'Loading', ru: 'Загрузка' },
    toggleNavigationMenu: {
        en: 'Toggle navigation menu',
        ru: 'Открыть меню навигации',
    },
    navigationMenu: { en: 'Navigation menu', ru: 'Меню навигации' },
    menuLabel: { en: 'Menu', ru: 'Меню' },
    sectionNavigation: {
        en: 'Section navigation',
        ru: 'Навигация по разделам',
    },
    goTo: { en: 'Go to {label}', ru: 'Перейти к {label}' },

    // Hero
    viewRooms: { en: 'View rooms', ru: 'Смотреть номера' },

    // Rooms carousel
    roomCounter: { en: 'Room {n} / {total}', ru: 'Номер {n} из {total}' },
    previousPhoto: { en: 'Previous photo', ru: 'Предыдущее фото' },
    nextPhoto: { en: 'Next photo', ru: 'Следующее фото' },
    showPhoto: { en: 'Show photo {n}', ru: 'Показать фото {n}' },
    previousRoom: { en: 'Previous room', ru: 'Предыдущий номер' },
    nextRoom: { en: 'Next room', ru: 'Следующий номер' },
    viewPricesAvailability: {
        en: 'View Prices & Availability',
        ru: 'Смотреть цены и наличие',
    },
    capacity: { en: 'Capacity', ru: 'Вместимость' },
    guestsCount: { en: '{n} guests', ru: '{n} гостей' },
    bed: { en: 'Bed', ru: 'Кровать' },
    // Amenity tags: a fixed vocabulary (see Room::AMENITY_TAGS), each
    // rendered as one minimalist icon badge under the room title.
    amenityDoubleBed: { en: 'Double bed', ru: 'Двуспальная кровать' },
    amenityTwinBeds: { en: 'Twin beds', ru: 'Раздельные кровати' },
    amenitySofa: { en: 'Sofa', ru: 'Диван' },
    amenityArmchair: { en: 'Armchair', ru: 'Кресло' },
    amenityTable: { en: 'Table', ru: 'Стол' },
    amenityNightstand: { en: 'Nightstand', ru: 'Тумба' },
    amenityChairs: { en: 'Chairs', ru: 'Стулья' },
    amenityWardrobe: { en: 'Wardrobe', ru: 'Шкаф-купе' },
    amenityHanger: { en: 'Coat rack', ru: 'Напольная вешалка' },
    amenityTv: { en: 'TV', ru: 'Телевизор' },
    amenityAc: { en: 'AC', ru: 'Кондиционер' },
    amenityFridge: { en: 'Fridge', ru: 'Холодильник' },
    amenitySafeBox: { en: 'Safe', ru: 'Сейф' },

    // Pricing table
    pricingIntro: {
        en: 'Transparent nightly rates for every season. Pick a room and a month below to add it to your booking — direct bookings also include complimentary breakfast, late check-out and 10% off stays of five nights or more.',
        ru: 'Прозрачные цены за ночь на каждый сезон. Выберите номер и месяц ниже, чтобы добавить его в бронирование — прямые бронирования также включают завтрак, поздний выезд и скидку 10% при проживании от пяти ночей.',
    },
    directBookingPerk: {
        en: 'Direct booking perk: free airport transfer on your first stay.',
        ru: 'Бонус за прямое бронирование: бесплатный трансфер из аэропорта в первую поездку.',
    },
    swipeHint: {
        en: 'Swipe to see all seasons →',
        ru: 'Смахните, чтобы увидеть все сезоны →',
    },
    roomColumn: { en: 'Room', ru: 'Номер' },
    from: { en: 'from', ru: 'от' },
    perNight: { en: 'per night', ru: 'за ночь' },
    slashPerNight: { en: '/ night', ru: '/ за ночь' },
    total: { en: 'Total:', ru: 'Итого:' },
    select: { en: 'Select', ru: 'Выбрать' },
    percentOff: { en: '{pct}% off', ru: 'Скидка {pct}%' },
    selectedRooms: { en: 'Selected rooms', ru: 'Выбранные номера' },
    noRoomsSelected: {
        en: 'No rooms selected yet — pick a room and month in the Pricing section.',
        ru: 'Пока нет выбранных номеров — выберите номер и месяц в разделе цен.',
    },
    decreaseQuantity: { en: 'Decrease quantity', ru: 'Уменьшить количество' },
    increaseQuantity: { en: 'Increase quantity', ru: 'Увеличить количество' },
    removeRoom: { en: 'Remove room', ru: 'Удалить номер' },
    removeOne: { en: 'Remove one', ru: 'Убрать один' },
    addOneMore: { en: 'Add one more', ru: 'Добавить ещё' },

    // About
    aboutBioOne: {
        en: '{hotel} opened its doors with a simple idea: every guest should feel like a welcome friend. For over two decades our team has combined warm, attentive service with calm, thoughtfully designed spaces.',
        ru: '{hotel} открыл свои двери с простой идеей: каждый гость должен чувствовать себя как дома. Уже более двадцати лет наша команда сочетает тёплый, внимательный сервис со спокойными, продуманными пространствами.',
    },
    aboutBioTwo: {
        en: 'From the sunlit lobby to the poolside terrace and our seasonal restaurant, every corner of the grounds is made for slowing down and enjoying your stay.',
        ru: 'От светлого лобби до террасы у бассейна и сезонного ресторана — каждый уголок создан для того, чтобы вы могли отдохнуть и насладиться пребыванием.',
    },
    statYears: { en: 'Years', ru: 'Лет' },
    statRooms: { en: 'Rooms', ru: 'Номеров' },
    statGuests: { en: 'Guests', ru: 'Гостей' },
    openPhoto: { en: 'Open photo: {alt}', ru: 'Открыть фото: {alt}' },

    // Contact
    contactIntro: {
        en: 'Pick your rooms above, then leave your details and our team will call you back shortly.',
        ru: 'Выберите номера выше и оставьте свои данные — мы вам скоро перезвоним.',
    },

    // Floating actions
    backToTop: { en: 'Back to top', ru: 'Наверх' },
    completeBooking: { en: 'Complete booking', ru: 'Завершить бронирование' },

    // Booking modal
    completeBookingRequestAria: {
        en: 'Complete your booking request',
        ru: 'Завершите запрос на бронирование',
    },
    close: { en: 'Close', ru: 'Закрыть' },
    completeYourBooking: {
        en: 'Complete Your Booking',
        ru: 'Завершите бронирование',
    },
    reviewSelectedRooms: {
        en: 'Review your selected rooms and send us your details.',
        ru: 'Проверьте выбранные номера и оставьте свои данные.',
    },

    // Booking form
    namePlaceholder: { en: 'Your name', ru: 'Ваше имя' },
    phonePlaceholder: {
        en: 'Phone, e.g. +1 555 010 2030',
        ru: 'Телефон, напр. +7 915 000 00 00',
    },
    messagePlaceholder: {
        en: 'Message (optional)',
        ru: 'Сообщение (необязательно)',
    },
    sending: { en: 'Sending…', ru: 'Отправка…' },
    sendRequest: { en: 'Send request', ru: 'Отправить заявку' },
    sentThanks: {
        en: "Thanks! We'll call you shortly.",
        ru: 'Спасибо! Мы скоро вам позвоним.',
    },
    sendError: {
        en: 'Something went wrong. Check your details and try again.',
        ru: 'Что-то пошло не так. Проверьте данные и попробуйте снова.',
    },
    invalidPhone: {
        en: 'Please enter a complete phone number.',
        ru: 'Пожалуйста, введите полный номер телефона.',
    },
    balconyPreference: {
        en: 'Preference: room with a balcony',
        ru: 'Пожелание: номер с балконом',
    },

    // Direct call
    directCallPrefix: {
        en: 'Or call us at',
        ru: 'Или позвоните нам по номеру',
    },
    directCallSuffix: {
        en: "and we'll discuss everything you need.",
        ru: 'и мы обсудим все интересующие вас вопросы.',
    },

    // Success overlay
    bookingReceivedTitle: { en: 'Booking received', ru: 'Заявка получена' },
    bookingReceivedMessage: {
        en: 'Thank you — a manager will connect with you shortly to confirm the details.',
        ru: 'Спасибо — менеджер скоро свяжется с вами, чтобы уточнить детали.',
    },

    // SEO meta description — used only when the admin hasn't set a tagline,
    // so the page never ships a blank one.
    metaDefaultDescription: {
        en: 'Book your stay at {hotel} — premium rooms, transparent seasonal pricing and instant booking.',
        ru: 'Забронируйте номер в {hotel} — премиальные номера, прозрачные сезонные цены и мгновенное бронирование.',
    },
} as const satisfies Record<string, Record<Locale, string>>;

export type TranslationKey = keyof typeof translations;

const LOCALE_KEY: InjectionKey<LocaleContext> = Symbol('locale');
const STORAGE_KEY = 'hotel-locale';
const LOCALES: { code: Locale; label: string }[] = [
    { code: 'en', label: 'EN' },
    { code: 'ru', label: 'RU' },
];

/**
 * Provides the guest's language choice to the whole landing page, the same
 * way `provideCurrency` does. Created once per page instance, so it never
 * leaks between server-rendered requests.
 */
export function provideLocale(): LocaleContext {
    const locale = ref<Locale>('en');

    const setLocale = (next: Locale): void => {
        locale.value = next;

        try {
            localStorage.setItem(STORAGE_KEY, next);
        } catch {
            // Storage can be blocked (private mode); the choice still applies for this visit.
        }
    };

    // Restore the guest's last choice only after mount, so server and client
    // markup match on the first render.
    onMounted(() => {
        let stored: string | null = null;

        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch {
            stored = null;
        }

        if (stored === 'en' || stored === 'ru') {
            locale.value = stored;
        }
    });

    const t = (
        key: TranslationKey,
        params?: Record<string, string | number>,
    ): string => {
        const template = translations[key][locale.value];

        if (!params) {
            return template;
        }

        return template.replace(/\{(\w+)\}/g, (_match, name: string) =>
            String(params[name] ?? ''),
        );
    };

    const context: LocaleContext = {
        locale: computed(() => locale.value),
        locales: LOCALES,
        setLocale,
        t,
    };

    provide(LOCALE_KEY, context);

    return context;
}

/**
 * Picks the active language from an admin-entered, per-locale value (e.g. a
 * section heading), falling back to English and then to an empty string if
 * even that is missing.
 */
export function localized(
    value: Partial<Record<Locale, string>> | null | undefined,
    locale: Locale,
): string {
    return value?.[locale] || value?.en || '';
}

export function useLocale(): LocaleContext {
    const context = inject(LOCALE_KEY);

    if (!context) {
        throw new Error('useLocale() must be used inside the landing page.');
    }

    return context;
}
