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
}>();

const emit = defineEmits<{ navigate: [id: string] }>();
const { t } = useLocale();

const mobileMenuOpen = ref(false);

const navigate = (id: string): void => {
    mobileMenuOpen.value = false;
    emit('navigate', id);
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
            class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-black/40 text-white backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-3 px-4 sm:h-16 sm:px-6"
            >
                <a
                    href="#hero"
                    class="flex min-w-0 shrink-0 items-center gap-2 text-base font-semibold tracking-wide sm:text-lg"
                    @click.prevent="navigate('hero')"
                >
                    <img
                        src="/images/hotel-logo.svg"
                        alt=""
                        class="h-7 w-auto shrink-0 sm:h-10"
                    />
                    <span class="truncate">{{ hotelName }}</span>
                </a>

                <nav class="hidden gap-1 text-sm md:flex md:gap-1.5">
                    <a
                        v-for="link in links"
                        :key="link.id"
                        :href="`#${link.id}`"
                        class="rounded-full px-3 py-1.5 whitespace-nowrap transition md:px-4"
                        :class="
                            activeId === link.id
                                ? 'bg-white text-slate-900'
                                : 'text-white/75 hover:bg-white/10 hover:text-white'
                        "
                        @click.prevent="navigate(link.id)"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <div
                    class="hidden items-center gap-1.5 sm:flex"
                    :aria-label="t('localizationControls')"
                >
                    <LocaleSwitcher />
                    <CurrencySwitcher />
                </div>

                <button
                    type="button"
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20 md:hidden"
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
                        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden"
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
                        class="fixed inset-y-0 right-0 z-40 flex w-[78%] max-w-xs flex-col gap-1 bg-slate-950/98 px-5 pt-24 pb-10 shadow-2xl md:hidden"
                        :aria-label="t('navigationMenu')"
                    >
                        <p
                            class="mb-2 px-3 text-[11px] tracking-widest text-white/40 uppercase"
                        >
                            {{ t('menuLabel') }}
                        </p>
                        <div
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
                                    ? 'bg-white text-slate-900'
                                    : 'text-white/80 hover:bg-white/10 hover:text-white'
                            "
                            @click.prevent="navigate(link.id)"
                        >
                            {{ link.label }}
                        </a>
                    </nav>
                </Transition>
            </Teleport>
        </ClientOnly>
    </div>
</template>
