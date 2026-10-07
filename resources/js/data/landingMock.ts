import type {
    FaqItem,
    HotelSettings,
    PricingTable,
    Room,
} from '@/types/landing';

const unsplash = (id: string, width = 1600): string =>
    `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${width}&q=80`;

export const mockHotel: HotelSettings = {
    hotel_name: { en: 'Afalina', ru: 'Афалина' },
    tagline: 'Quiet luxury in the heart of the city',
    promo_banner:
        'Special Offer: Book now and get a 5% discount on early bird reservations!',
    // Hero slides come only from the database (see HeroImageSeeder).
    hero_images: [],
    contacts: {
        phone: '+1 (555) 010-2030',
        email: 'stay@afalina.example',
    },
    section_headings: {
        rooms: { en: 'Our Rooms', ru: 'Наши номера' },
        pricing: {
            en: 'Seasonal Rates & Special Offers',
            ru: 'Сезонные тарифы и специальные предложения',
        },
        about: {
            en: 'A story of hospitality by the water',
            ru: 'История гостеприимства у воды',
        },
        faq: {
            en: 'Frequently Asked Questions',
            ru: 'Часто задаваемые вопросы',
        },
        contact: { en: 'Request a Callback', ru: 'Заказать обратный звонок' },
    },
};

export const mockRooms: Room[] = [
    {
        id: 1,
        slug: 'deluxe-double',
        name: 'Deluxe Double',
        description: 'Bright, calm room with city views and a plush king bed.',
        images: [
            unsplash('photo-1611892440504-42a792e24d32'),
            unsplash('photo-1618773928121-c32242e63f39'),
            unsplash('photo-1522708323590-d24dbb6b0267'),
        ],
        base_price: 180,
        amenities: {
            capacity: 2,
            bed_type: 'King bed',
            furniture: ['Work desk', 'Armchair', 'Wardrobe', 'Bedside tables'],
            has_tv: true,
            has_air_conditioning: true,
        },
    },
    {
        id: 2,
        slug: 'family-suite',
        name: 'Family Suite',
        description: 'Two-room suite with a living area, ideal for families.',
        images: [
            unsplash('photo-1582719478250-c89cae4dc85b'),
            unsplash('photo-1590490360182-c33d57733427'),
            unsplash('photo-1618773928121-c32242e63f39'),
        ],
        base_price: 290,
        amenities: {
            capacity: 4,
            bed_type: 'King + 2 singles',
            furniture: ['Sofa', 'Dining table', 'Wardrobe', 'Kids desk'],
            has_tv: true,
            has_air_conditioning: true,
        },
    },
    {
        id: 3,
        slug: 'executive-suite',
        name: 'Executive Suite',
        description: 'Top-floor suite with panoramic views and a lounge.',
        images: [
            unsplash('photo-1631049307264-da0ec9d70304'),
            unsplash('photo-1611892440504-42a792e24d32'),
            unsplash('photo-1582719478250-c89cae4dc85b'),
        ],
        base_price: 420,
        amenities: {
            capacity: 3,
            bed_type: 'Super king bed',
            furniture: [
                'Lounge sofa',
                'Walk-in wardrobe',
                'Writing desk',
                'Minibar',
            ],
            has_tv: true,
            has_air_conditioning: true,
        },
    },
];

export const mockPricing: PricingTable = {
    columns: [
        { id: 1, label: 'Low season' },
        { id: 2, label: 'High season' },
        { id: 3, label: 'Holidays' },
    ],
    rows: [
        {
            room_id: 1,
            room_name: 'Deluxe Double',
            prices: { 1: 180, 2: 240, 3: 300 },
            base_price: 180,
            monthly_discounts: {},
        },
        {
            room_id: 2,
            room_name: 'Family Suite',
            prices: { 1: 290, 2: 380, 3: 460 },
            base_price: 290,
            monthly_discounts: { 1: 10 },
        },
        {
            room_id: 3,
            room_name: 'Executive Suite',
            prices: { 1: 420, 2: 540, 3: 650 },
            base_price: 420,
            monthly_discounts: { 1: 15, 2: 5 },
        },
    ],
};

export const roomFallbackImages: string[] = [
    unsplash('photo-1618773928121-c32242e63f39'),
    unsplash('photo-1590490360182-c33d57733427'),
    unsplash('photo-1522708323590-d24dbb6b0267'),
];

export const galleryImages: { src: string; alt: string; tall: boolean }[] = [
    {
        src: unsplash('photo-1566073771259-6a8506099945', 900),
        alt: 'Lobby',
        tall: true,
    },
    {
        src: unsplash('photo-1571896349842-33c89424de2d', 900),
        alt: 'Pool',
        tall: false,
    },
    {
        src: unsplash('photo-1517248135467-4c7edcad34c4', 900),
        alt: 'Restaurant',
        tall: false,
    },
    {
        src: unsplash('photo-1542314831-068cd1dbfeeb', 900),
        alt: 'Exterior',
        tall: false,
    },
    {
        src: unsplash('photo-1520250497591-112f2f40a3f4', 900),
        alt: 'Poolside terrace',
        tall: false,
    },
];

// Mock FAQ entries; will later be managed from the admin panel.
export const faqItems: FaqItem[] = [
    {
        question: 'What time are check-in and check-out?',
        answer: 'Check-in is from 3:00 PM and check-out is until 11:00 AM. Early check-in and late check-out can be arranged, subject to availability.',
    },
    {
        question: 'Is parking available on site?',
        answer: 'Yes, we offer complimentary private parking for all registered guests, including secure overnight parking.',
    },
    {
        question: 'Do you allow pets?',
        answer: 'Well-behaved pets are welcome in select rooms for a small daily fee. Let us know in advance so we can prepare the room.',
    },
    {
        question: 'Is breakfast included in the room rate?',
        answer: 'A seasonal breakfast buffet is included with most rates and served daily from 7:00 to 10:30 AM in our restaurant.',
    },
    {
        question: 'Can I cancel or modify my reservation?',
        answer: "Reservations can be cancelled free of charge up to 48 hours before arrival. Inside that window, one night's rate may apply.",
    },
];
