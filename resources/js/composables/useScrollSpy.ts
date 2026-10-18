import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Tracks which section is mostly visible on screen, and which single nav
 * link should show as active.
 *
 * Location and Reviews share one viewport side by side at `lg` and up
 * (see Landing.vue), so both can be simultaneously intersecting at once.
 * When that happens, whichever one the guest actually clicked (or
 * scrolled past first, if they never clicked either) wins, rather than
 * both lighting up together - correct in the literal "both are in view"
 * sense, but it reads as broken, since from the guest's side they picked
 * one specific thing to look at.
 */
export function useScrollSpy(sectionIds: string[]) {
    const activeId = ref<string>(sectionIds[0] ?? '');
    let observer: IntersectionObserver | null = null;
    const visible = new Set<string>();
    let lastClicked: string | null = null;

    const pickActiveId = (): void => {
        if (visible.size === 0) {
            return;
        }

        if (lastClicked && visible.has(lastClicked)) {
            activeId.value = lastClicked;

            return;
        }

        activeId.value =
            sectionIds.find((id) => visible.has(id)) ?? activeId.value;
    };

    onMounted(() => {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible.add(entry.target.id);
                    } else {
                        visible.delete(entry.target.id);
                    }
                });

                pickActiveId();
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
        lastClicked = id;
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    };

    return { activeId, scrollTo };
}
