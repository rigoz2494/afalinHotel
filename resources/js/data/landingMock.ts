import type {
    AdditionalService,
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
    promo_banner: {
        en: 'Special Offer: Book now and get a 5% discount on early bird reservations!',
        ru: 'Специальное предложение: забронируйте сейчас и получите скидку 5% при раннем бронировании!',
    },
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
        name: { en: 'Deluxe Double', ru: 'Делюкс Дабл' },
        description: {
            en: 'Bright, calm room with city views and a plush king bed.',
            ru: 'Светлый, тихий номер с видом на город и большой кроватью king-size.',
        },
        images: [
            unsplash('photo-1611892440504-42a792e24d32'),
            unsplash('photo-1618773928121-c32242e63f39'),
            unsplash('photo-1522708323590-d24dbb6b0267'),
        ],
        base_price: 180,
        amenities: {
            capacity: 2,
            bed_type: 'King bed',
            tags: ['double_bed', 'table', 'nightstand', 'wardrobe', 'tv', 'ac'],
        },
        units: [
            {
                id: 101,
                number: '101',
                images: [],
                amenities: [
                    'double_bed',
                    'table',
                    'nightstand',
                    'wardrobe',
                    'tv',
                    'ac',
                ],
                has_balcony: false,
            },
            {
                id: 102,
                number: '102',
                images: [unsplash('photo-1618773928121-c32242e63f39')],
                amenities: [
                    'double_bed',
                    'table',
                    'nightstand',
                    'wardrobe',
                    'tv',
                    'ac',
                ],
                has_balcony: true,
            },
        ],
    },
    {
        id: 2,
        slug: 'family-suite',
        name: { en: 'Family Suite', ru: 'Семейный люкс' },
        description: {
            en: 'Two-room suite with a living area, ideal for families.',
            ru: 'Двухкомнатный люкс с гостиной зоной, идеальный для семей.',
        },
        images: [
            unsplash('photo-1582719478250-c89cae4dc85b'),
            unsplash('photo-1590490360182-c33d57733427'),
            unsplash('photo-1618773928121-c32242e63f39'),
        ],
        base_price: 290,
        amenities: {
            capacity: 4,
            bed_type: 'King + 2 singles',
            tags: [
                'double_bed',
                'sofa',
                'table',
                'wardrobe',
                'hanger',
                'tv',
                'ac',
            ],
        },
        units: [
            {
                id: 201,
                number: '201',
                images: [],
                amenities: [
                    'double_bed',
                    'sofa',
                    'table',
                    'wardrobe',
                    'hanger',
                    'tv',
                    'ac',
                ],
                has_balcony: true,
            },
            {
                id: 202,
                number: '202',
                images: [unsplash('photo-1590490360182-c33d57733427')],
                amenities: [
                    'double_bed',
                    'sofa',
                    'table',
                    'wardrobe',
                    'hanger',
                    'tv',
                    'ac',
                ],
                has_balcony: false,
            },
        ],
    },
    {
        id: 3,
        slug: 'executive-suite',
        name: { en: 'Executive Suite', ru: 'Люкс' },
        description: {
            en: 'Top-floor suite with panoramic views and a lounge.',
            ru: 'Люкс на верхнем этаже с панорамным видом и гостиной зоной.',
        },
        images: [
            unsplash('photo-1631049307264-da0ec9d70304'),
            unsplash('photo-1611892440504-42a792e24d32'),
            unsplash('photo-1582719478250-c89cae4dc85b'),
        ],
        base_price: 420,
        amenities: {
            capacity: 3,
            bed_type: 'Super king bed',
            tags: [
                'double_bed',
                'armchair',
                'sofa',
                'table',
                'wardrobe',
                'hanger',
                'tv',
                'ac',
                'fridge',
                'safe_box',
            ],
        },
        units: [
            {
                id: 301,
                number: '301',
                images: [unsplash('photo-1631049307264-da0ec9d70304')],
                amenities: [
                    'double_bed',
                    'armchair',
                    'sofa',
                    'table',
                    'wardrobe',
                    'hanger',
                    'tv',
                    'ac',
                    'fridge',
                    'safe_box',
                ],
                has_balcony: true,
            },
        ],
    },
];

export const mockAdditionalServices: AdditionalService[] = [
    {
        id: 1,
        name: { en: 'Extra bed (child)', ru: 'Дополнительное место (ребёнок)' },
        price: 800,
    },
    {
        id: 2,
        name: {
            en: 'Extra bed (adult)',
            ru: 'Дополнительное место (взрослый)',
        },
        price: 1200,
    },
    {
        id: 3,
        name: { en: 'Parking', ru: 'Парковка' },
        price: 300,
    },
    {
        id: 4,
        name: { en: 'Breakfast', ru: 'Завтрак' },
        price: 500,
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
            room_name: { en: 'Deluxe Double', ru: 'Делюкс Дабл' },
            prices: { 1: 180, 2: 240, 3: 300 },
            base_price: 180,
            monthly_discounts: {},
        },
        {
            room_id: 2,
            room_name: { en: 'Family Suite', ru: 'Семейный люкс' },
            prices: { 1: 290, 2: 380, 3: 460 },
            base_price: 290,
            monthly_discounts: { 1: 10 },
        },
        {
            room_id: 3,
            room_name: { en: 'Executive Suite', ru: 'Люкс' },
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
