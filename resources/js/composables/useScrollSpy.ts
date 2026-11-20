import { useEventListener } from '@vueuse/core';
import { onMounted, ref } from 'vue';

export function useScrollSpy(sectionIds: string[]) {
    const activeId = ref<string>(sectionIds[0] ?? '');
    let lastClicked: string | null = null;

    const pickActiveId = (): void => {
        const viewportHeight = window.innerHeight;
        const lastSection = sectionIds.at(-1);
        const lastBounds = lastSection
            ? document.getElementById(lastSection)?.getBoundingClientRect()
            : undefined;

        if (
            lastSection &&
            lastBounds &&
            lastBounds.bottom <= viewportHeight + 1
        ) {
            const clickedBounds = lastClicked
                ? document.getElementById(lastClicked)?.getBoundingClientRect()
                : undefined;

            activeId.value =
                lastClicked &&
                clickedBounds &&
                clickedBounds.top === lastBounds.top
                    ? lastClicked
                    : lastSection;

            return;
        }

        const activationLine = Math.min(180, viewportHeight * 0.25);
        const matchingSections = sectionIds.filter((id) => {
            const bounds = document.getElementById(id)?.getBoundingClientRect();

            return (
                bounds &&
                bounds.top <= activationLine &&
                bounds.bottom > activationLine
            );
        });

        // Location and Reviews can sit side by side. Respect the link the
        // guest chose when both sections occupy the same place on screen.
        activeId.value =
            lastClicked && matchingSections.includes(lastClicked)
                ? lastClicked
                : (matchingSections[0] ?? activeId.value);
    };

    onMounted(pickActiveId);
    useEventListener('scroll', pickActiveId, { capture: true, passive: true });
    useEventListener('resize', pickActiveId);

    const scrollTo = (id: string): void => {
        lastClicked = id;
        activeId.value = id;
        document.getElementById(id)?.scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)')
                .matches
                ? 'instant'
                : 'smooth',
        });
    };

    return { activeId, scrollTo };
}
