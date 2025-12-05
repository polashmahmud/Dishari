import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { iconMap } from './iconMap';

export function useDishari() {
    const page = usePage();

    // main menus computed property
    const menus = computed(() => {
        const rawGroups = page.props.dishari as any[] || [];
        return rawGroups.map(group => transformGroup(group));
    });

    const transformGroup = (group: any): any => {
        return {
            ...group,
            // Groups have 'items' from the backend
            items: (group.items && group.items.length > 0)
                ? group.items.map((item: any) => transformItem(item))
                : [],
        };
    };

    const transformItem = (item: any): any => {
        return {
            ...item,
            // 1. Icon String to Component conversion
            icon: item.icon && iconMap[item.icon] ? iconMap[item.icon] : undefined,

            // 2. Recursively handle items (backend sends 'items' now, not 'children')
            items: (item.items && item.items.length > 0)
                ? item.items.map((child: any) => transformItem(child))
                : undefined,
        };
    };

    return {
        menus
    };
}
