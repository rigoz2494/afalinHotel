import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Tracks which section is mostly visible on screen.
 */
export function useScrollSpy(sectionIds: string[]) {
    const activeId = ref<string>(sectionIds[0] ?? '');
    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        activeId.value = entry.target.id;
                    }
                });
            },
            // root: null (the browser viewport) rather than <main> - that's
            // only a clipped, viewport-sized scroll area at `lg` and up,
            // not below it, where it just grows with the page. The viewport
            // works correctly at every size: <main> fills it edge-to-edge
            // on desktop anyway (SiteHeader floats on top, fixed, rather
            // than pushing it down), so this isn't a behavior change there.
            { root: null, threshold: 0.6 },
        );

        sectionIds.forEach((id) => {
            const element = document.getElementById(id);

            if (element) {
                observer?.observe(element);
            }
        });
    });

    onBeforeUnmount(() => observer?.disconnect());

    const scrollTo = (id: string): void => {
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    };

    return { activeId, scrollTo };
}
