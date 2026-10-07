<script setup lang="ts">
import { useEventListener } from '@vueuse/core';
import { ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { computed } from 'vue';
import ClientOnly from '@/components/ClientOnly.vue';
import { useLocale } from '@/composables/useLocale';

const props = defineProps<{
    images: { src: string; alt: string }[];
}>();
const { t } = useLocale();

const index = defineModel<number | null>({ required: true });

const current = computed(() =>
    index.value === null ? null : props.images[index.value],
);

const close = (): void => {
    index.value = null;
};

const step = (direction: 1 | -1): void => {
    if (index.value === null) {
        return;
    }

    const total = props.images.length;
    index.value = (index.value + direction + total) % total;
};

// No explicit `window` target: see the comment in SiteHeader.vue.
useEventListener('keydown', (event: KeyboardEvent) => {
    if (index.value === null) {
        return;
    }

    if (event.key === 'Escape') {
        close();
    } else if (event.key === 'ArrowLeft') {
        step(-1);
    } else if (event.key === 'ArrowRight') {
        step(1);
    }
});
</script>

<template>
    <ClientOnly>
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="current"
                    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="current.alt"
                    @click.self="close"
                >
                    <button
                        type="button"
                        class="absolute top-5 right-5 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/25"
                        :aria-label="t('close')"
                        @click="close"
                    >
                        <X class="size-6" />
                    </button>
                    <button
                        type="button"
                        class="absolute left-4 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/25 md:left-8"
                        :aria-label="t('previousPhoto')"
                        @click="step(-1)"
                    >
                        <ChevronLeft class="size-7" />
                    </button>

                    <figure
                        class="flex max-h-full max-w-6xl flex-col items-center"
                    >
                        <Transition
                            mode="out-in"
                            enter-active-class="transition duration-200"
                            enter-from-class="opacity-0"
                            leave-active-class="transition duration-150"
                            leave-to-class="opacity-0"
                        >
                            <img
                                :key="current.src"
                                :src="current.src"
                                :alt="current.alt"
                                loading="lazy"
                                decoding="async"
                                class="max-h-[80vh] rounded-lg object-contain shadow-2xl"
                            />
                        </Transition>
                        <figcaption class="mt-4 text-center text-white/80">
                            {{ current.alt }}
                            <span class="ml-2 text-sm text-white/40">
                                {{ (index ?? 0) + 1 }} / {{ images.length }}
                            </span>
                        </figcaption>
                    </figure>

                    <button
                        type="button"
                        class="absolute right-4 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/25 md:right-8"
                        :aria-label="t('nextPhoto')"
                        @click="step(1)"
                    >
                        <ChevronRight class="size-7" />
                    </button>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>
