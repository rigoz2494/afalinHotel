<script setup lang="ts">
import { useEventListener } from '@vueuse/core';
import {
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ClientOnly from '@/components/ClientOnly.vue';
import { useAmenityTags } from '@/composables/useAmenityTags';
import { useLocale } from '@/composables/useLocale';
import { useRoomSelection } from '@/composables/useRoomSelection';
import type { Room } from '@/types/landing';

const props = defineProps<{ room: Room }>();
const open = defineModel<boolean>({ required: true });
const { t } = useLocale();
const { setSelectedRoomNumber } = useRoomSelection();

const selectedUnitId = ref<number | null>(null);
const imageIndex = ref(0);
// A deliberate, brief pause before swapping in the new room's photos/tags —
// not real loading (the data is already on the page), but switching units
// instantly would flash the new content in jarringly. The skeleton below
// fills that gap instead of a blank or half-updated frame.
const isLoading = ref(false);
let loadingTimer: ReturnType<typeof setTimeout> | undefined;

const selectedUnit = computed(
    () =>
        props.room.units.find((unit) => unit.id === selectedUnitId.value) ??
        null,
);
const amenityTags = computed(() =>
    selectedUnit.value ? useAmenityTags(selectedUnit.value.amenities, t) : [],
);

const selectUnit = (id: number | null): void => {
    selectedUnitId.value = id;
    imageIndex.value = 0;

    const unit = props.room.units.find((u) => u.id === id);
    setSelectedRoomNumber(unit?.number ?? null);

    clearTimeout(loadingTimer);
    isLoading.value = true;
    loadingTimer = setTimeout(() => {
        isLoading.value = false;
    }, 350);
};

// Opens on this room type's first specific room every time, not whichever
// one happened to be selected last time the guest opened this modal.
watch(open, (isOpen) => {
    if (isOpen) {
        selectUnit(props.room.units[0]?.id ?? null);
    }
});

const stepImage = (direction: 1 | -1): void => {
    const total = selectedUnit.value?.images.length ?? 0;

    if (total === 0) {
        return;
    }

    imageIndex.value = (imageIndex.value + direction + total) % total;
};

const close = (): void => {
    open.value = false;
};

useEventListener('keydown', (event: KeyboardEvent) => {
    if (open.value && event.key === 'Escape') {
        close();
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
                    v-if="open"
                    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="t('availableRoomsModalTitle')"
                    @click.self="close"
                >
                    <div
                        class="relative max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl bg-stone-950 p-6 text-white shadow-2xl sm:p-8"
                    >
                        <button
                            type="button"
                            :aria-label="t('close')"
                            class="absolute top-4 right-4 rounded-full bg-white/10 p-2 transition hover:bg-white/20"
                            @click="close"
                        >
                            <X class="size-5" />
                        </button>
                        <h2 class="text-2xl font-semibold sm:text-3xl">
                            {{ t('availableRoomsModalTitle') }}
                        </h2>

                        <div class="mt-5 grid gap-6 lg:grid-cols-5 lg:gap-8">
                            <!-- Wide, luxury-sized photo column. -->
                            <div class="lg:col-span-3">
                                <div
                                    v-if="isLoading"
                                    class="aspect-video w-full animate-pulse rounded-xl bg-gradient-to-br from-white/10 via-white/5 to-white/10 lg:aspect-4/3"
                                ></div>
                                <Transition
                                    v-else
                                    enter-active-class="transition duration-300 ease-out"
                                    enter-from-class="opacity-0"
                                >
                                    <div
                                        v-if="selectedUnit"
                                        class="relative aspect-video w-full overflow-hidden rounded-xl bg-white/5 lg:aspect-4/3"
                                    >
                                        <img
                                            v-if="selectedUnit.images.length"
                                            :src="
                                                selectedUnit.images[imageIndex]
                                            "
                                            :alt="
                                                t('roomNumberOption', {
                                                    number: selectedUnit.number,
                                                })
                                            "
                                            class="absolute inset-0 h-full w-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                        <template
                                            v-if="
                                                selectedUnit.images.length > 1
                                            "
                                        >
                                            <button
                                                type="button"
                                                class="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-black/50 p-2 backdrop-blur hover:bg-black/70"
                                                :aria-label="t('previousPhoto')"
                                                @click="stepImage(-1)"
                                            >
                                                <ChevronLeft class="size-5" />
                                            </button>
                                            <button
                                                type="button"
                                                class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-black/50 p-2 backdrop-blur hover:bg-black/70"
                                                :aria-label="t('nextPhoto')"
                                                @click="stepImage(1)"
                                            >
                                                <ChevronRight class="size-5" />
                                            </button>
                                        </template>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Selector + details column. -->
                            <div class="lg:col-span-2">
                                <label
                                    class="block text-xs tracking-widest text-amber-300/80 uppercase"
                                >
                                    {{ t('roomNumberSelectLabel') }}
                                    <div class="relative mt-2">
                                        <select
                                            :value="selectedUnitId ?? ''"
                                            class="block w-full appearance-none rounded-lg border border-white/15 bg-stone-950 px-3 py-2.5 pr-9 text-sm font-normal tracking-normal text-white normal-case transition outline-none hover:border-amber-300/50 focus:border-amber-300"
                                            @change="
                                                selectUnit(
                                                    Number(
                                                        (
                                                            $event.target as HTMLSelectElement
                                                        ).value,
                                                    ) || null,
                                                )
                                            "
                                        >
                                            <option
                                                v-for="unit in room.units"
                                                :key="unit.id"
                                                :value="unit.id"
                                            >
                                                {{
                                                    t('roomNumberOption', {
                                                        number: unit.number,
                                                    })
                                                }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-amber-300/70"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </label>

                                <!-- Skeleton: soft, pulsing translucent
                                blocks in place of the amenity badges and
                                balcony line, while isLoading is true. -->
                                <div
                                    v-if="isLoading"
                                    class="mt-4 animate-pulse"
                                >
                                    <div class="flex flex-wrap gap-2">
                                        <div
                                            v-for="n in 5"
                                            :key="n"
                                            class="size-9 rounded-full bg-gradient-to-br from-white/10 via-white/5 to-white/10"
                                        ></div>
                                    </div>
                                    <div
                                        class="mt-4 h-4 w-2/3 rounded bg-gradient-to-r from-white/10 via-white/5 to-white/10"
                                    ></div>
                                </div>

                                <Transition
                                    v-else
                                    enter-active-class="transition duration-300 ease-out"
                                    enter-from-class="opacity-0"
                                >
                                    <div v-if="selectedUnit" class="mt-4">
                                        <ul
                                            v-if="amenityTags.length"
                                            class="flex flex-wrap gap-1.5"
                                        >
                                            <li
                                                v-for="item in amenityTags"
                                                :key="item.tag"
                                                :title="item.label"
                                                :aria-label="item.label"
                                                class="flex size-9 items-center justify-center rounded-full bg-white/10 text-amber-300"
                                            >
                                                <component
                                                    :is="item.icon"
                                                    class="size-4"
                                                />
                                            </li>
                                        </ul>

                                        <p
                                            class="mt-4 flex items-center gap-2 text-sm"
                                            :class="
                                                selectedUnit.has_balcony
                                                    ? 'text-amber-200'
                                                    : 'text-white/50'
                                            "
                                        >
                                            <CheckCircle2
                                                class="size-4 shrink-0"
                                            />
                                            {{
                                                selectedUnit.has_balcony
                                                    ? t('hasBalcony')
                                                    : t('noBalconyUnit')
                                            }}
                                        </p>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>
