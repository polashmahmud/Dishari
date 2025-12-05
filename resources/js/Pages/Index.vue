<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import MenuDraggableList from './List.vue';
import { toast } from 'vue-sonner';
import { iconOptions } from '@/lib/iconMap';
import { Plus, Save, Ban, Search, MoreVertical, Pencil, Trash2, GripVertical } from 'lucide-vue-next';
import { VueDraggable } from 'vue-draggable-plus';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

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
const iconSearch = ref('');

// Watch for prop changes
watch(() => props.menuGroups, (newVal) => {
    groups.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

// --- Group Management ---
const isCreateGroupOpen = ref(false);
const isEditGroupOpen = ref(false);
const editingGroup = ref<MenuGroup | null>(null);

const groupForm = useForm({
    name: '',
    key: '',
    order: 0,
    is_active: true,
});

const openCreateGroupDialog = () => {
    groupForm.reset();
    isCreateGroupOpen.value = true;
};

const openEditGroupDialog = (group: MenuGroup) => {
    editingGroup.value = group;
    groupForm.name = group.name;
    groupForm.key = group.key || '';
    groupForm.order = group.order;
    groupForm.is_active = Boolean(group.is_active);
    isEditGroupOpen.value = true;
};

const createGroup = () => {
    groupForm.post('/menu-management/groups', {
        onSuccess: () => {
            isCreateGroupOpen.value = false;
            groupForm.reset();
        },
    });
};

const updateGroup = () => {
    if (!editingGroup.value) return;
    groupForm.put(`/menu-management/groups/${editingGroup.value.id}`, {
        onSuccess: () => {
            isEditGroupOpen.value = false;
            groupForm.reset();
            editingGroup.value = null;
        },
    });
};

const deleteGroup = (group: MenuGroup) => {
    if (confirm(`Are you sure you want to delete group "${group.name}"?`)) {
        router.delete(`/menu-management/groups/${group.id}`);
    }
};

// --- Item Management ---
const isCreateItemOpen = ref(false);
const isEditItemOpen = ref(false);
const editingItem = ref<MenuItem | null>(null);
const targetGroupId = ref<number | null>(null);

const itemForm = useForm({
    menu_group_id: null as number | null,
    parent_id: null as number | null,
    title: '',
    url: '',
    route: '',
    icon: '',
    order: 0,
    is_active: true,
    permission_name: '',
});

const filteredIcons = computed(() => {
    if (!iconSearch.value) return iconOptions;
    return iconOptions.filter(icon =>
        icon.name.toLowerCase().includes(iconSearch.value.toLowerCase())
    );
});

// Helper to get all items for parent selection (within the same group)
const availableParents = computed(() => {
    const currentGroupId = itemForm.menu_group_id;
    if (!currentGroupId) return [];

    const group = groups.value.find(g => g.id === currentGroupId);
    if (!group) return [];

    const flattenMenus = (menuItems: MenuItem[], level = 0): any[] => {
        return menuItems.reduce((acc: any[], menu) => {
            // Exclude the item being edited and its children to avoid cycles
            if (editingItem.value && menu.id === editingItem.value.id) return acc;

            acc.push({ ...menu, level });
            if (menu.children && menu.children.length > 0) {
                acc.push(...flattenMenus(menu.children, level + 1));
            }
            return acc;
        }, []);
    };
    return flattenMenus(group.items);
});

const openCreateItemDialog = (groupId: number) => {
    itemForm.reset();
    itemForm.menu_group_id = groupId;
    targetGroupId.value = groupId;
    iconSearch.value = '';
    isCreateItemOpen.value = true;
};

const openEditItemDialog = (item: MenuItem) => {
    editingItem.value = item;
    // Find which group this item belongs to (top level check or recursive search if needed,
    // but item.menu_group_id should be set on backend.
    // However, for nested items, menu_group_id might be null in DB but logically they belong to the root's group.
    // We need to find the root parent to know the group if menu_group_id is missing on children)

    // For simplicity, we assume we can find the group from the UI context or search the groups array
    let groupId = item.menu_group_id;
    if (!groupId) {
        // Search in groups
        for (const g of groups.value) {
            if (findItemInTree(g.items, item.id)) {
                groupId = g.id;
                break;
            }
        }
    }

    itemForm.menu_group_id = groupId || null;
    itemForm.parent_id = item.parent_id || null;
    itemForm.title = item.title;
    itemForm.url = item.url || '';
    itemForm.route = item.route || '';
    itemForm.icon = item.icon || '';
    itemForm.order = item.order;
    itemForm.is_active = Boolean(item.is_active);
    itemForm.permission_name = (item as any).permission_name || ''; // Cast if type missing

    iconSearch.value = '';
    isEditItemOpen.value = true;
};

const findItemInTree = (items: MenuItem[], id: number): boolean => {
    for (const item of items) {
        if (item.id === id) return true;
        if (item.children && findItemInTree(item.children, id)) return true;
    }
    return false;
};

const createItem = () => {
    itemForm.post('/menu-management', {
        onSuccess: () => {
            isCreateItemOpen.value = false;
            itemForm.reset();
        },
    });
};

const updateItem = () => {
    if (!editingItem.value) return;
    itemForm.put(`/menu-management/${editingItem.value.id}`, {
        onSuccess: () => {
            isEditItemOpen.value = false;
            itemForm.reset();
            editingItem.value = null;
        },
    });
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

            <!-- Create Group Dialog -->
            <Dialog v-model:open="isCreateGroupOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create Menu Group</DialogTitle>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="group-name">Name</Label>
                            <Input id="group-name" v-model="groupForm.name" placeholder="Platform" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="group-key">Key (Optional)</Label>
                            <Input id="group-key" v-model="groupForm.key" placeholder="platform" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <RadioGroup :model-value="groupForm.is_active ? 'active' : 'inactive'"
                                @update:model-value="(val) => groupForm.is_active = val === 'active'"
                                class="flex gap-4">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="group-active" value="active" />
                                    <Label for="group-active">Active</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="group-inactive" value="inactive" />
                                    <Label for="group-inactive">Inactive</Label>
                                </div>
                            </RadioGroup>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isCreateGroupOpen = false">Cancel</Button>
                        <Button @click="createGroup" :disabled="groupForm.processing">Create</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Edit Group Dialog -->
            <Dialog v-model:open="isEditGroupOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Menu Group</DialogTitle>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-group-name">Name</Label>
                            <Input id="edit-group-name" v-model="groupForm.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-group-key">Key</Label>
                            <Input id="edit-group-key" v-model="groupForm.key" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <RadioGroup :model-value="groupForm.is_active ? 'active' : 'inactive'"
                                @update:model-value="(val) => groupForm.is_active = val === 'active'"
                                class="flex gap-4">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="edit-group-active" value="active" />
                                    <Label for="edit-group-active">Active</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="edit-group-inactive" value="inactive" />
                                    <Label for="edit-group-inactive">Inactive</Label>
                                </div>
                            </RadioGroup>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isEditGroupOpen = false">Cancel</Button>
                        <Button @click="updateGroup" :disabled="groupForm.processing">Update</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Create Item Dialog -->
            <Dialog v-model:open="isCreateItemOpen">
                <DialogContent class="sm:max-w-[600px]">
                    <DialogHeader>
                        <DialogTitle>Create Menu Item</DialogTitle>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="item-title">Title</Label>
                                <Input id="item-title" v-model="itemForm.title" placeholder="Dashboard" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="item-url">URL</Label>
                                <Input id="item-url" v-model="itemForm.url" placeholder="/dashboard" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="item-route">Route Name (Optional)</Label>
                                <Input id="item-route" v-model="itemForm.route" placeholder="dashboard.index" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="item-permission">Permission (Optional)</Label>
                                <Input id="item-permission" v-model="itemForm.permission_name"
                                    placeholder="view_dashboard" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="item-parent">Parent Menu</Label>
                            <Select v-model="itemForm.parent_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="None (Root Level)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">None (Root Level)</SelectItem>
                                    <SelectItem v-for="menu in availableParents" :key="menu.id" :value="menu.id">
                                        {{ '—'.repeat(menu.level) }} {{ menu.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label>Icon</Label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="iconSearch" placeholder="Search icons..." class="pl-8" />
                            </div>
                            <div
                                class="grid grid-cols-8 gap-2 p-2 border rounded-md max-h-[150px] overflow-y-auto mt-2">
                                <div class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                    :class="{ 'bg-primary text-primary-foreground border-primary': !itemForm.icon }"
                                    @click="itemForm.icon = ''" title="No Icon">
                                    <Ban class="h-5 w-5" />
                                </div>
                                <div v-for="icon in filteredIcons" :key="icon.name"
                                    class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                    :class="{ 'bg-primary text-primary-foreground border-primary': itemForm.icon === icon.name }"
                                    @click="itemForm.icon = icon.name" :title="icon.name">
                                    <component :is="icon.component" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <RadioGroup :model-value="itemForm.is_active ? 'active' : 'inactive'"
                                @update:model-value="(val) => itemForm.is_active = val === 'active'" class="flex gap-4">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="item-active" value="active" />
                                    <Label for="item-active">Active</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="item-inactive" value="inactive" />
                                    <Label for="item-inactive">Inactive</Label>
                                </div>
                            </RadioGroup>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isCreateItemOpen = false">Cancel</Button>
                        <Button @click="createItem" :disabled="itemForm.processing">Create</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Edit Item Dialog -->
            <Dialog v-model:open="isEditItemOpen">
                <DialogContent class="sm:max-w-[600px]">
                    <DialogHeader>
                        <DialogTitle>Edit Menu Item</DialogTitle>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="edit-item-title">Title</Label>
                                <Input id="edit-item-title" v-model="itemForm.title" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit-item-url">URL</Label>
                                <Input id="edit-item-url" v-model="itemForm.url" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="edit-item-route">Route Name</Label>
                                <Input id="edit-item-route" v-model="itemForm.route" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit-item-permission">Permission</Label>
                                <Input id="edit-item-permission" v-model="itemForm.permission_name" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-item-group">Group</Label>
                            <Select v-model="itemForm.menu_group_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Group" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="group in groups" :key="group.id" :value="group.id">
                                        {{ group.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-item-parent">Parent Menu</Label>
                            <Select v-model="itemForm.parent_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="None (Root Level)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">None (Root Level)</SelectItem>
                                    <SelectItem v-for="menu in availableParents.filter(m => m.id !== editingItem?.id)"
                                        :key="menu.id" :value="menu.id">
                                        {{ '—'.repeat(menu.level) }} {{ menu.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label>Icon</Label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="iconSearch" placeholder="Search icons..." class="pl-8" />
                            </div>
                            <div
                                class="grid grid-cols-8 gap-2 p-2 border rounded-md max-h-[150px] overflow-y-auto mt-2">
                                <div class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                    :class="{ 'bg-primary text-primary-foreground border-primary': !itemForm.icon }"
                                    @click="itemForm.icon = ''" title="No Icon">
                                    <Ban class="h-5 w-5" />
                                </div>
                                <div v-for="icon in filteredIcons" :key="icon.name"
                                    class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                    :class="{ 'bg-primary text-primary-foreground border-primary': itemForm.icon === icon.name }"
                                    @click="itemForm.icon = icon.name" :title="icon.name">
                                    <component :is="icon.component" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <RadioGroup :model-value="itemForm.is_active ? 'active' : 'inactive'"
                                @update:model-value="(val) => itemForm.is_active = val === 'active'" class="flex gap-4">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="edit-item-active" value="active" />
                                    <Label for="edit-item-active">Active</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="edit-item-inactive" value="inactive" />
                                    <Label for="edit-item-inactive">Inactive</Label>
                                </div>
                            </RadioGroup>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isEditItemOpen = false">Cancel</Button>
                        <Button @click="updateItem" :disabled="itemForm.processing">Update</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
