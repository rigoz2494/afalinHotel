<script setup lang="ts">
import { CheckCircle2 } from '@lucide/vue';
import ClientOnly from '@/components/ClientOnly.vue';
import { useLocale } from '@/composables/useLocale';

const open = defineModel<boolean>({ required: true });

const { t } = useLocale();

const close = (): void => {
    open.value = false;
};
</script>

<template>
    <ClientOnly>
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-500 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-400 ease-in"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="open"
                    class="fixed inset-0 z-[90] flex items-center justify-center overflow-hidden bg-stone-950 px-4 text-center"
                    role="status"
                    :aria-label="t('bookingReceivedTitle')"
                    @click.self="close"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-stone-950 via-stone-950 to-amber-950/50"
                        aria-hidden="true"
                    ></div>

                    <div class="relative flex flex-col items-center">
                        <img
                            src="/images/hotel-logo.svg"
                            alt=""
                            class="success-logo mb-6 h-14 w-auto opacity-90 sm:h-20"
                        />
                        <span
                            class="success-check mb-5 flex size-16 items-center justify-center rounded-full bg-amber-300/15 ring-1 ring-amber-300/40 sm:size-20"
                        >
                            <CheckCircle2
                                :stroke-width="1.5"
                                class="size-9 text-amber-300 sm:size-11"
                            />
                        </span>
                        <h2
                            class="success-text text-2xl font-semibold text-white sm:text-3xl"
                        >
                            {{ t('bookingReceivedTitle') }}
                        </h2>
                        <p
                            class="success-text mt-3 max-w-sm text-sm text-stone-300 sm:text-base"
                        >
                            {{ t('bookingReceivedMessage') }}
                        </p>
                        <button
                            type="button"
                            class="success-text mt-8 rounded-full border border-white/20 px-6 py-2 text-xs text-white/70 transition hover:bg-white/10 hover:text-white sm:text-sm"
                            @click="close"
                        >
                            {{ t('close') }}
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>

<style scoped>
.success-logo {
    animation: success-fade-in 1s ease-out both;
}

.success-check {
    animation: success-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
}

.success-text {
    animation: success-fade-in 0.6s ease-out 0.35s both;
}

@keyframes success-fade-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes success-pop {
    from {
        opacity: 0;
        transform: scale(0.5);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .success-logo,
    .success-check,
    .success-text {
        animation: none;
    }
}
</style>
