<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { iconOptions } from '@/lib/iconMap';
import { Ban, Search } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

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
    items: MenuItem[];
}

const props = defineProps<{
    open: boolean;
    item?: MenuItem | null;
    groups: MenuGroup[];
    targetGroupId?: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
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

const iconSearch = ref('');

const filteredIcons = computed(() => {
    if (!iconSearch.value) return iconOptions;
    return iconOptions.filter(icon =>
        icon.name.toLowerCase().includes(iconSearch.value.toLowerCase())
    );
});

// Helper to get all items for parent selection (within the same group)
const availableParents = computed(() => {
    const currentGroupId = form.menu_group_id;
    if (!currentGroupId) return [];

    const group = props.groups.find(g => g.id === currentGroupId);
    if (!group) return [];

    const flattenMenus = (menuItems: MenuItem[], level = 0): any[] => {
        return menuItems.reduce((acc: any[], menu) => {
            // Exclude the item being edited and its children to avoid cycles
            if (props.item && menu.id === props.item.id) return acc;

            acc.push({ ...menu, level });
            if (menu.children && menu.children.length > 0) {
                acc.push(...flattenMenus(menu.children, level + 1));
            }
            return acc;
        }, []);
    };
    return flattenMenus(group.items);
});

const findItemInTree = (items: MenuItem[], id: number): boolean => {
    for (const item of items) {
        if (item.id === id) return true;
        if (item.children && findItemInTree(item.children, id)) return true;
    }
    return false;
};

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.item) {
            // Edit Mode
            let groupId = props.item.menu_group_id;
            if (!groupId) {
                // Search in groups if not explicitly set
                for (const g of props.groups) {
                    if (findItemInTree(g.items, props.item.id)) {
                        groupId = g.id;
                        break;
                    }
                }
            }

            form.menu_group_id = groupId || null;
            form.parent_id = props.item.parent_id || null;
            form.title = props.item.title;
            form.url = props.item.url || '';
            form.route = props.item.route || '';
            form.icon = props.item.icon || '';
            form.order = props.item.order;
            form.is_active = Boolean(props.item.is_active);
            form.permission_name = (props.item as any).permission_name || '';
        } else {
            // Create Mode
            form.reset();
            form.menu_group_id = props.targetGroupId || null;
            form.is_active = true;
        }
        iconSearch.value = '';
    }
});

const handleSubmit = () => {
    if (props.item) {
        form.put(`/menu-management/${props.item.id}`, {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
                form.reset();
            },
        });
    } else {
        form.post('/menu-management', {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
                form.reset();
            },
        });
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>{{ item ? 'Edit Menu Item' : 'Create Menu Item' }}</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="item-title">Title</Label>
                        <Input id="item-title" v-model="form.title" placeholder="Dashboard" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="item-url">URL</Label>
                        <Input id="item-url" v-model="form.url" placeholder="/dashboard" />
                        <InputError :message="form.errors.url" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="item-route">Route Name (Optional)</Label>
                        <Input id="item-route" v-model="form.route" placeholder="dashboard.index" />
                        <InputError :message="form.errors.route" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="item-permission">Permission (Optional)</Label>
                        <Input id="item-permission" v-model="form.permission_name" placeholder="view_dashboard" />
                        <InputError :message="form.errors.permission_name" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="item-group">Group</Label>
                    <Select v-model="form.menu_group_id">
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
                    <Label for="item-parent">Parent Menu</Label>
                    <Select v-model="form.parent_id">
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
                    <div class="grid grid-cols-8 gap-2 p-2 border rounded-md max-h-[150px] overflow-y-auto mt-2">
                        <div class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                            :class="{ 'bg-primary text-primary-foreground border-primary': !form.icon }"
                            @click="form.icon = ''" title="No Icon">
                            <Ban class="h-5 w-5" />
                        </div>
                        <div v-for="icon in filteredIcons" :key="icon.name"
                            class="flex items-center justify-center p-2 rounded-md cursor-pointer hover:bg-accent transition-colors border"
                            :class="{ 'bg-primary text-primary-foreground border-primary': form.icon === icon.name }"
                            @click="form.icon = icon.name" :title="icon.name">
                            <component :is="icon.component" class="h-5 w-5" />
                        </div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Status</Label>
                    <RadioGroup :model-value="form.is_active ? 'active' : 'inactive'"
                        @update:model-value="(val) => form.is_active = val === 'active'" class="flex gap-4">
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
                <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
                <Button @click="handleSubmit" :disabled="form.processing">{{ item ? 'Update' : 'Create' }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
