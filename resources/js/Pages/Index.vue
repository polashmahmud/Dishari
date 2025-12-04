<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import MenuDraggableList from '../Components/MenuDraggableList.vue';
import { toast } from 'vue-sonner';
import { iconOptions } from '@/iconMap';
import { Plus, Save, Ban } from 'lucide-vue-next';

interface Menu {
    id: number;
    title: string;
    url: string | null;
    icon: string | null;
    order: number;
    is_active: boolean;
    children: Menu[];
    parent_id?: number | null;
}

interface Props {
    menuList: Menu[];
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

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingMenu = ref<Menu | null>(null);
const menus = ref<Menu[]>(JSON.parse(JSON.stringify(props.menuList)));

// Watch for prop changes to update local state
watch(() => props.menuList, (newVal) => {
    menus.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

const form = useForm({
    parent_id: null as number | null,
    title: '',
    url: '',
    icon: '',
    order: 0,
    is_active: true,
});

// Helper to flatten menus for the parent selector
const allMenus = computed(() => {
    const flattenMenus = (menuItems: Menu[], level = 0): any[] => {
        return menuItems.reduce((acc: any[], menu) => {
            acc.push({ ...menu, level });
            if (menu.children && menu.children.length > 0) {
                acc.push(...flattenMenus(menu.children, level + 1));
            }
            return acc;
        }, []);
    };
    return flattenMenus(props.menuList);
});

const openCreateDialog = () => {
    form.reset();
    isCreateDialogOpen.value = true;
};

const openEditDialog = (menu: Menu) => {
    editingMenu.value = menu;
    form.parent_id = menu.parent_id || null;
    form.title = menu.title;
    form.url = menu.url || '';
    form.icon = menu.icon || '';
    form.order = menu.order;
    form.is_active = Boolean(menu.is_active);
    isEditDialogOpen.value = true;
};

const createMenu = () => {
    form.post('/menu-management', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            form.reset();
        },
    });
};

const updateMenu = () => {
    if (!editingMenu.value) return;

    form.put(`/menu-management/${editingMenu.value.id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            form.reset();
            editingMenu.value = null;
        },
    });
};

const deleteMenu = (id: number) => {
    if (confirm('Are you sure you want to delete this menu?')) {
        router.delete(`/menu-management/${id}`);
    }
};

const saveOrder = () => {
    const flattenForSave = (items: Menu[], parentId: number | null = null): any[] => {
        let result: any[] = [];
        items.forEach((item, index) => {
            result.push({
                id: item.id,
                parent_id: parentId,
                order: index + 1,
            });
            if (item.children && item.children.length > 0) {
                result = result.concat(flattenForSave(item.children, item.id));
            }
        });
        return result;
    };

    const payload = flattenForSave(menus.value);

    router.post('/menu-management/update-order', { menus: payload }, {
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
                    <p class="text-muted-foreground">Manage your application menus. Drag and drop to reorder.</p>
                </div>

                <div class="flex gap-2">
                    <Button variant="outline" @click="saveOrder">
                        <Save class="mr-2 h-4 w-4" />
                        Save Order
                    </Button>
                    <Dialog v-model:open="isCreateDialogOpen">
                        <DialogTrigger as-child>
                            <Button @click="openCreateDialog">
                                <Plus class="mr-2 h-4 w-4" />
                                Create Menu
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[525px]">
                            <DialogHeader>
                                <DialogTitle>Create Menu</DialogTitle>
                                <DialogDescription>
                                    Add a new menu item to your application.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="title">Title</Label>
                                    <Input id="title" v-model="form.title" placeholder="Dashboard" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="url">URL</Label>
                                    <Input id="url" v-model="form.url" placeholder="/dashboard" />
                                </div>
                                <div class="grid gap-2">
                                    <Label>Icon</Label>
                                    <div
                                        class="grid grid-cols-6 gap-2 p-2 border rounded-md max-h-[200px] overflow-y-auto">
                                        <div class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                            :class="{ 'bg-primary text-primary-foreground border-primary': !form.icon }"
                                            @click="form.icon = ''" title="No Icon">
                                            <Ban class="h-5 w-5" />
                                        </div>
                                        <div v-for="icon in iconOptions" :key="icon.name"
                                            class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                            :class="{ 'bg-primary text-primary-foreground border-primary': form.icon === icon.name }"
                                            @click="form.icon = icon.name" :title="icon.name">
                                            <component :is="icon.component" class="h-5 w-5" />
                                        </div>
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="parent">Parent Menu</Label>
                                    <Select v-model="form.parent_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="None (Root Level)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">None (Root Level)</SelectItem>
                                            <SelectItem v-for="menu in allMenus" :key="menu.id" :value="menu.id">
                                                {{ '—'.repeat(menu.level) }} {{ menu.title }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model:checked="form.is_active" />
                                    <Label for="is_active">Active</Label>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button variant="outline" @click="isCreateDialogOpen = false">Cancel</Button>
                                <Button @click="createMenu" :disabled="form.processing">Create</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Menus</CardTitle>
                    <CardDescription>Drag items to reorder or nest them.</CardDescription>
                </CardHeader>
                <CardContent>
                    <MenuDraggableList v-model="menus" @edit="openEditDialog" @delete="deleteMenu" />

                    <div v-if="menus.length === 0" class="text-center py-8 text-muted-foreground">
                        No menus found. Create your first menu to get started.
                    </div>
                </CardContent>
            </Card>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="sm:max-w-[525px]">
                    <DialogHeader>
                        <DialogTitle>Edit Menu</DialogTitle>
                        <DialogDescription>
                            Update the menu item details.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-title">Title</Label>
                            <Input id="edit-title" v-model="form.title" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-url">URL</Label>
                            <Input id="edit-url" v-model="form.url" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Icon</Label>
                            <div class="grid grid-cols-6 gap-2 p-2 border rounded-md max-h-[200px] overflow-y-auto">
                                <div class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                    :class="{ 'bg-primary text-primary-foreground border-primary': !form.icon }"
                                    @click="form.icon = ''" title="No Icon">
                                    <Ban class="h-5 w-5" />
                                </div>
                                <div v-for="icon in iconOptions" :key="icon.name"
                                    class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                                    :class="{ 'bg-primary text-primary-foreground border-primary': form.icon === icon.name }"
                                    @click="form.icon = icon.name" :title="icon.name">
                                    <component :is="icon.component" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-parent">Parent Menu</Label>
                            <Select v-model="form.parent_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="None (Root Level)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">None (Root Level)</SelectItem>
                                    <SelectItem v-for="menu in allMenus.filter(m => m.id !== editingMenu?.id)"
                                        :key="menu.id" :value="menu.id">
                                        {{ '—'.repeat(menu.level) }} {{ menu.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Switch id="edit-is_active" v-model:checked="form.is_active" />
                            <Label for="edit-is_active">Active</Label>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isEditDialogOpen = false">Cancel</Button>
                        <Button @click="updateMenu" :disabled="form.processing">Update</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
