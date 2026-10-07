import { createInertiaApp } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Afalina';

void createInertiaApp({
    // A pass-through, not a brand suffix: the hotel's own name is bilingual
    // (see Landing.vue's seoTitle), so it's composed into each page's own
    // title already. Appending this static, English-only env value here too
    // would double up as "Афалина - Afalina" for Russian guests.
    title: (title) => title || appName,
    progress: {
        color: '#4B5563',
    },
});
