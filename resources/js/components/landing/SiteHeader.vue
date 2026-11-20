<script setup lang="ts">
import { useEventListener } from '@vueuse/core';
import { Menu, X } from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
import ClientOnly from '@/components/ClientOnly.vue';
import CurrencySwitcher from '@/components/landing/CurrencySwitcher.vue';
import LocaleSwitcher from '@/components/landing/LocaleSwitcher.vue';
import { useLocale } from '@/composables/useLocale';

const props = defineProps<{
    hotelName: string | null;
    links: { id: string; label: string }[];
    activeId: string;
    /** False in a strict "Global Language Mode" — see Landing.vue. */
    showLocalizationControls: boolean;
}>();

const emit = defineEmits<{
    navigate: [id: string];
    openBooking: [];
}>();
const { t } = useLocale();

const mobileMenuOpen = ref(false);

const navigate = (id: string): void => {
    mobileMenuOpen.value = false;
    emit('navigate', id);
};

const openBooking = (): void => {
    mobileMenuOpen.value = false;
    emit('openBooking');
};

// The user may scroll (e.g. via the floating button) while the mobile
// menu is open; close it so it doesn't linger over the new section.
watch(
    () => props.activeId,
    () => {
        mobileMenuOpen.value = false;
    },
);

// Lock background scroll while the full-screen menu is open.
watch(mobileMenuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
});

// No explicit `window` target: @vueuse/core defaults to `window` itself
// via its own SSR-guarded fallback. Referencing the bare `window`
// identifier here would crash during SSR, since it doesn't exist in Node.
useEventListener('keydown', (event: KeyboardEvent) => {
    if (mobileMenuOpen.value && event.key === 'Escape') {
        mobileMenuOpen.value = false;
    }
});
</script>

<template>
    <div>
        <header
            class="border-coast-line bg-coast-paper/95 text-coast-ink fixed inset-x-0 top-0 z-50 border-b backdrop-blur-md"
        >
            <div
                class="max-w-landing mx-auto flex h-14 items-center justify-between gap-2 px-4 sm:h-16 sm:gap-3 sm:px-6 lg:px-8"
            >
                <a
                    href="#hero"
                    class="flex shrink-0 items-center"
                    @click.prevent="navigate('hero')"
                >
                    <img
                        src="/images/afalina-logo.webp"
                        :alt="hotelName ?? 'Afalina'"
                        class="h-10 w-auto sm:h-12"
                    />
                </a>

                <!-- min-w-0 lets this shrink below its content's width (the
                flex default would otherwise force the whole header wider
                than the viewport); overflow-x-auto is a last-resort safety
                net if labels are ever still too long to fit even so. -->
                <nav
                    class="hidden min-w-0 flex-1 justify-center-safe gap-0.5 overflow-x-auto text-sm xl:flex"
                >
                    <a
                        v-for="link in links"
                        :key="link.id"
                        :href="`#${link.id}`"
                        class="shrink-0 border-b-2 px-2.5 py-1.5 whitespace-nowrap transition"
                        :aria-current="
                            activeId === link.id ? 'location' : undefined
                        "
                        :class="
                            activeId === link.id
                                ? 'border-coast-blue text-coast-blue'
                                : 'text-coast-ink hover:text-coast-blue border-transparent'
                        "
                        @click.prevent="navigate(link.id)"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <div
                    v-if="showLocalizationControls"
                    class="hidden shrink-0 items-center gap-1.5 sm:flex"
                    :aria-label="t('localizationControls')"
                >
                    <LocaleSwitcher />
                    <CurrencySwitcher />
                </div>

                <button
                    type="button"
                    class="bg-coast-blue hover:bg-coast-ink hidden shrink-0 rounded-lg px-4 py-2 text-sm font-semibold text-white transition xl:block"
                    @click="openBooking"
                >
                    {{ t('bookNow') }}
                </button>

                <button
                    type="button"
                    class="bg-coast-mist hover:bg-coast-line flex size-10 shrink-0 items-center justify-center rounded-lg transition xl:hidden"
                    :aria-expanded="mobileMenuOpen"
                    :aria-label="t('toggleNavigationMenu')"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 rotate-45"
                        leave-active-class="transition duration-150 ease-in"
                        leave-to-class="opacity-0 rotate-45"
                    >
                        <X v-if="mobileMenuOpen" key="close" class="size-5" />
                        <Menu v-else key="open" class="size-5" />
                    </Transition>
                </button>
            </div>
        </header>

        <ClientOnly>
            <Teleport to="body">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="mobileMenuOpen"
                        class="bg-coast-deep/50 fixed inset-0 z-40 backdrop-blur-sm xl:hidden"
                        @click="mobileMenuOpen = false"
                    ></div>
                </Transition>

                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-x-full"
                    leave-active-class="transition duration-200 ease-in"
                    leave-to-class="translate-x-full"
                >
                    <nav
                        v-if="mobileMenuOpen"
                        class="bg-coast-paper text-coast-ink fixed inset-y-0 right-0 z-40 flex w-[78%] max-w-xs flex-col gap-1 overflow-y-auto px-5 pt-20 pb-6 shadow-2xl xl:hidden"
                        :aria-label="t('navigationMenu')"
                    >
                        <div
                            v-if="showLocalizationControls"
                            class="mb-4 flex items-center gap-1.5"
                            :aria-label="t('localizationControls')"
                        >
                            <LocaleSwitcher />
                            <CurrencySwitcher />
                        </div>
                        <a
                            v-for="link in links"
                            :key="link.id"
                            :href="`#${link.id}`"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium tracking-wide transition"
                            :class="
                                activeId === link.id
                                    ? 'bg-coast-mist text-coast-blue font-semibold'
                                    : 'hover:bg-coast-mist'
                            "
                            @click.prevent="navigate(link.id)"
                        >
                            {{ link.label }}
                        </a>
                        <button
                            type="button"
                            class="bg-coast-blue hover:bg-coast-ink mt-4 rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition"
                            @click="openBooking"
                        >
                            {{ t('bookNow') }}
                        </button>
                    </nav>
                </Transition>
            </Teleport>
        </ClientOnly>
    </div>
</template>
