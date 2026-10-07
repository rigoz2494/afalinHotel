import { onBeforeUnmount, onMounted, ref } from 'vue';

const SCROLL_KEYS = new Set([
    ' ',
    'ArrowUp',
    'ArrowDown',
    'PageUp',
    'PageDown',
    'Home',
    'End',
]);

// The preloader stays up at least this long so it never just flickers.
const MINIMUM_DISPLAY_MS = 800;
// A stalled image must never trap visitors behind the overlay.
const MAXIMUM_WAIT_MS = 4000;

/**
 * Keeps the preloader visible until the page has loaded. Wheel, touch and
 * scroll-key input is blocked while it is up; `releaseInput` restores it once
 * the overlay has finished fading out.
 */
export function usePreloader() {
    const isLoading = ref(true);

    const blockPointerScroll = (event: Event): void => {
        event.preventDefault();
    };

    const blockScrollKeys = (event: KeyboardEvent): void => {
        if (SCROLL_KEYS.has(event.key)) {
            event.preventDefault();
        }
    };

    const lockInput = (): void => {
        window.addEventListener('wheel', blockPointerScroll, {
            passive: false,
        });
        window.addEventListener('touchmove', blockPointerScroll, {
            passive: false,
        });
        window.addEventListener('keydown', blockScrollKeys);
    };

    const releaseInput = (): void => {
        window.removeEventListener('wheel', blockPointerScroll);
        window.removeEventListener('touchmove', blockPointerScroll);
        window.removeEventListener('keydown', blockScrollKeys);
    };

    onMounted(() => {
        lockInput();

        const pageLoaded = new Promise<void>((resolve) => {
            if (document.readyState === 'complete') {
                resolve();

                return;
            }

            window.addEventListener('load', () => resolve(), { once: true });
        });
        const minimumDisplay = new Promise<void>((resolve) =>
            setTimeout(resolve, MINIMUM_DISPLAY_MS),
        );
        const maximumWait = new Promise<void>((resolve) =>
            setTimeout(resolve, MAXIMUM_WAIT_MS),
        );

        void Promise.race([
            Promise.all([pageLoaded, minimumDisplay]),
            maximumWait,
        ]).then(() => {
            isLoading.value = false;
        });
    });

    onBeforeUnmount(releaseInput);

    return { isLoading, releaseInput };
}
