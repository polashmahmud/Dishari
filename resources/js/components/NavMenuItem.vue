<script setup lang="ts">
import {
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
} from '@/components/ui/sidebar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

defineOptions({
    name: 'NavMenuItem',
});

const props = defineProps<{
    item: NavItem;
    level?: number;
}>();

const page = usePage();

const hasChildren = computed(() => {
    return props.item.items && props.item.items.length > 0;
});

const isSubItem = computed(() => (props.level || 0) > 0);

const isItemActive = (item: NavItem): boolean => {
    if (item.href && urlIsActive(item.href, page.url)) return true;
    return item.items?.some(child => isItemActive(child)) ?? false;
};

const shouldExpand = computed(() => {
    return isItemActive(props.item);
});

const iconComponent = computed(() => {
    return props.item.icon || null;
});
</script>

<template>
    <SidebarMenuItem v-if="!isSubItem">
        <template v-if="hasChildren">
            <Collapsible class="group/collapsible" :default-open="shouldExpand">
                <CollapsibleTrigger as-child>
                    <SidebarMenuButton :tooltip="item.title">
                        <component :is="iconComponent" v-if="iconComponent" />
                        <span>{{ item.title }}</span>
                        <ChevronRight
                            class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        <NavMenuItem v-for="child in item.items" :key="child.id || child.title" :item="child"
                            :level="(level || 0) + 1" />
                    </SidebarMenuSub>
                </CollapsibleContent>
            </Collapsible>
        </template>
        <template v-else>
            <SidebarMenuButton as-child :is-active="item.href ? urlIsActive(item.href, page.url) : false"
                :tooltip="item.title">
                <Link v-if="item.href" :href="item.href">
                <component :is="iconComponent" v-if="iconComponent" />
                <span>{{ item.title }}</span>
                </Link>
                <span v-else class="flex items-center gap-2 w-full">
                    <component :is="iconComponent" v-if="iconComponent" />
                    <span>{{ item.title }}</span>
                </span>
            </SidebarMenuButton>
        </template>
    </SidebarMenuItem>

    <SidebarMenuSubItem v-else>
        <template v-if="hasChildren">
            <Collapsible class="group/collapsible" :default-open="shouldExpand">
                <CollapsibleTrigger as-child>
                    <SidebarMenuSubButton>
                        <component :is="iconComponent" v-if="iconComponent" />
                        <span>{{ item.title }}</span>
                        <ChevronRight
                            class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuSubButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        <NavMenuItem v-for="child in item.items" :key="child.id || child.title" :item="child"
                            :level="(level || 0) + 1" />
                    </SidebarMenuSub>
                </CollapsibleContent>
            </Collapsible>
        </template>
        <template v-else>
            <SidebarMenuSubButton as-child :is-active="item.href ? urlIsActive(item.href, page.url) : false">
                <Link v-if="item.href" :href="item.href">
                <component :is="iconComponent" v-if="iconComponent" />
                <span>{{ item.title }}</span>
                </Link>
                <span v-else class="flex items-center gap-2 w-full">
                    <component :is="iconComponent" v-if="iconComponent" />
                    <span>{{ item.title }}</span>
                </span>
            </SidebarMenuSubButton>
        </template>
    </SidebarMenuSubItem>
</template>
