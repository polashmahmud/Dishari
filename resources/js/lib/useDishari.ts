import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { iconMap } from './iconMap';

export function useDishari() {
    const page = usePage();

    // main menus computed property
    const menus = computed(() => {
        const rawMenus = page.props.dishari as any[] || [];
        return rawMenus.map(menu => transformMenu(menu));
    });

    // internal helper function (logic will be hidden here)
    const transformMenu = (menu: any): any => {
        return {
            ...menu,
            // 1. Icon String to Component conversion
            icon: menu.icon && iconMap[menu.icon] ? iconMap[menu.icon] : undefined,

            // 2. Recursively handle children
            items: (menu.children && menu.children.length > 0)
                ? menu.children.map((child: any) => transformMenu(child))
                : undefined,
        };
    };

    return {
        menus
    };
}
