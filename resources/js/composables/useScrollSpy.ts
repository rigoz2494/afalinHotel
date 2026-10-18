import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Tracks which section(s) are mostly visible on screen. A set, not a single
 * id: Location and Reviews share one viewport side by side at `lg` and up
 * (see Landing.vue), so both can be simultaneously intersecting at once -
 * a single "last one wins" id would make one of them permanently
 * unhighlightable.
 */
export function useScrollSpy(sectionIds: string[]) {
    const activeIds = ref<Set<string>>(
        new Set(sectionIds[0] ? [sectionIds[0]] : []),
    );
    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        const visible = new Set<string>();

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible.add(entry.target.id);
                    } else {
                        visible.delete(entry.target.id);
                    }
                });

                // Between sections (e.g. mid-scroll), nothing is past the
                // 0.6 threshold yet - keep the previous highlight rather
                // than flashing every nav link off for a moment.
                if (visible.size > 0) {
                    activeIds.value = new Set(visible);
                }
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

    return { activeIds, scrollTo };
}
