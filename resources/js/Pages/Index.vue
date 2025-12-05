<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Plus, Save, Pencil, Trash2, GripVertical } from 'lucide-vue-next';
import { VueDraggable } from 'vue-draggable-plus';
import MenuDraggableList from './List.vue';
import GroupDialog from './GroupDialog.vue';
import ItemDialog from './ItemDialog.vue';

interface MenuItem {
    id: number;
    title: string;
    url: string | null;
    route: string | null;
    icon: string | null;
    order: number;
    is_active: boolean;
    children: MenuItem[];
    parent_id?: number | null;
    menu_group_id?: number | null;
}

interface MenuGroup {
    id: number;
    name: string;
    key: string | null;
    order: number;
    is_active: boolean;
    items: MenuItem[];
}

interface Props {
    menuGroups: MenuGroup[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Menu Management',
        href: '/menu-management',
    }
];

const groups = ref<MenuGroup[]>(JSON.parse(JSON.stringify(props.menuGroups)));

// Watch for prop changes
watch(() => props.menuGroups, (newVal) => {
    groups.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

// --- Group Management ---
const isGroupDialogOpen = ref(false);
const editingGroup = ref<MenuGroup | null>(null);

const openCreateGroupDialog = () => {
    editingGroup.value = null;
    isGroupDialogOpen.value = true;
};

const openEditGroupDialog = (group: MenuGroup) => {
    editingGroup.value = group;
    isGroupDialogOpen.value = true;
};

const deleteGroup = (group: MenuGroup) => {
    if (confirm(`Are you sure you want to delete group "${group.name}"?`)) {
        router.delete(`/menu-management/groups/${group.id}`);
    }
};

// --- Item Management ---
const isItemDialogOpen = ref(false);
const editingItem = ref<MenuItem | null>(null);
const targetGroupId = ref<number | null>(null);

const openCreateItemDialog = (groupId: number) => {
    editingItem.value = null;
    targetGroupId.value = groupId;
    isItemDialogOpen.value = true;
};

const openEditItemDialog = (item: MenuItem) => {
    editingItem.value = item;
    targetGroupId.value = null;
    isItemDialogOpen.value = true;
};

const deleteItem = (id: number) => {
    if (confirm('Are you sure you want to delete this menu item?')) {
        router.delete(`/menu-management/${id}`);
    }
};

// --- Saving Order ---
const saveOrder = () => {
    const flattenItems = (items: MenuItem[], parentId: number | null, groupId: number): any[] => {
        let result: any[] = [];
        items.forEach((item, index) => {
            result.push({
                id: item.id,
                parent_id: parentId,
                menu_group_id: parentId ? null : groupId, // Only roots need group id
                order: index + 1,
            });
            if (item.children && item.children.length > 0) {
                result = result.concat(flattenItems(item.children, item.id, groupId));
            }
        });
        return result;
    };

    const groupPayload = groups.value.map((g, index) => ({
        id: g.id,
        order: index + 1
    }));

    let itemsPayload: any[] = [];
    groups.value.forEach(group => {
        itemsPayload = itemsPayload.concat(flattenItems(group.items, null, group.id));
    });

    router.post('/menu-management/update-order', {
        groups: groupPayload,
        items: itemsPayload
    }, {
        onSuccess: () => {
            toast.success('Menu order saved successfully');
        },
        onError: () => {
            toast.error('Failed to save menu order');
        }
    });
};
</script>

<template>

    <Head title="Menu Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Menu Management</h2>
                    <p class="text-muted-foreground">Manage menu groups and items.</p>
                </div>

                <div class="flex gap-2">
                    <Button variant="outline" @click="saveOrder">
                        <Save class="mr-2 h-4 w-4" />
                        Save Order
                    </Button>
                    <Button @click="openCreateGroupDialog">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Group
                    </Button>
                </div>
            </div>

            <!-- Groups List -->
            <VueDraggable v-model="groups" handle=".group-handle" :animation="150" class="space-y-6">
                <div v-for="group in groups" :key="group.id">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="flex items-center gap-2">
                                <div class="group-handle cursor-grab hover:text-primary">
                                    <GripVertical class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg font-semibold">{{ group.name }}</CardTitle>
                                    <CardDescription v-if="group.key">Key: {{ group.key }}</CardDescription>
                                </div>
                                <Badge :variant="group.is_active ? 'default' : 'secondary'" class="ml-2">
                                    {{ group.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="ghost" size="icon" @click="openEditGroupDialog(group)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive"
                                    @click="deleteGroup(group)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-4">
                            <MenuDraggableList v-model="group.items" group="menu-items" @edit="openEditItemDialog"
                                @delete="deleteItem" />
                            <div v-if="group.items.length === 0"
                                class="text-center py-4 text-muted-foreground text-sm border-2 border-dashed rounded-md mt-2">
                                No items in this group.
                            </div>
                        </CardContent>
                        <CardFooter class="border-t pt-4">
                            <Button variant="ghost" size="sm" class="w-full" @click="openCreateItemDialog(group.id)">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Item to {{ group.name }}
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </VueDraggable>

            <div v-if="groups.length === 0" class="text-center py-12 text-muted-foreground">
                No menu groups found. Create a group to get started.
            </div>

            <!-- Group Dialog -->
            <GroupDialog v-model:open="isGroupDialogOpen" :group="editingGroup" @success="isGroupDialogOpen = false" />

            <!-- Item Dialog -->
            <ItemDialog v-model:open="isItemDialogOpen" :item="editingItem" :groups="groups"
                :target-group-id="targetGroupId" @success="isItemDialogOpen = false" />
        </div>
    </AppLayout>
</template>
