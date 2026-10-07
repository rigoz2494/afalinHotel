import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/**
 * Tracks which section is mostly visible inside a scroll container.
 */
export function useScrollSpy(
    sectionIds: string[],
    container: Ref<HTMLElement | null>,
) {
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
            { root: container.value, threshold: 0.6 },
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
