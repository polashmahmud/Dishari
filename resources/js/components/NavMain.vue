<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
} from '@/components/ui/sidebar';
import NavMenuItem from '@/components/dishari/NavMenuItem.vue';

interface MenuGroup {
    id: number;
    name: string;
    key: string;
    items: any[];
}

defineProps<{
    items: MenuGroup[];
}>();
</script>

<template>
    <SidebarGroup v-for="group in items" :key="group.key || group.id" class="px-2 py-0">
        <SidebarGroupLabel v-if="group.name">{{ group.name }}</SidebarGroupLabel>

        <SidebarMenu>
            <NavMenuItem v-for="item in group.items" :key="item.title" :item="item" :level="0" />
        </SidebarMenu>
    </SidebarGroup>
</template>
