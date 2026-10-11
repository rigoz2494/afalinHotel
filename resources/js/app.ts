import { createInertiaApp } from '@inertiajs/vue3';

void createInertiaApp({
    // A pass-through, not a brand suffix: the hotel's own name is bilingual
    // (see Landing.vue's seoTitle), so it's composed into each page's own
    // title already. This only ever applies if a page sets no <title> of
    // its own — hardcoded to the real brand name, not VITE_APP_NAME, since
    // that env value is a plain English string ("Afalina") and would leak
    // as an English tab title for a Russian guest otherwise.
    title: (title) => title || 'Афалина',
    progress: {
        color: '#4B5563',
    },
});
