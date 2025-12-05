<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface MenuGroup {
    id: number;
    name: string;
    key: string | null;
    order: number;
    is_active: boolean;
}

const props = defineProps<{
    open: boolean;
    group?: MenuGroup | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    name: '',
    key: '',
    order: 0,
    is_active: true,
});

watch(() => props.group, (newGroup) => {
    if (newGroup) {
        form.name = newGroup.name;
        form.key = newGroup.key || '';
        form.order = newGroup.order;
        form.is_active = Boolean(newGroup.is_active);
    } else {
        form.reset();
        form.is_active = true;
    }
}, { immediate: true });

const handleSubmit = () => {
    if (props.group) {
        form.put(`/menu-management/groups/${props.group.id}`, {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
                form.reset();
            },
        });
    } else {
        form.post('/menu-management/groups', {
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
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ group ? 'Edit Menu Group' : 'Create Menu Group' }}</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="group-name">Name</Label>
                    <Input id="group-name" v-model="form.name" placeholder="Platform" />
                </div>
                <div class="grid gap-2">
                    <Label for="group-key">Key (Optional)</Label>
                    <Input id="group-key" v-model="form.key" placeholder="platform" />
                </div>
                <div class="grid gap-2">
                    <Label>Status</Label>
                    <RadioGroup :model-value="form.is_active ? 'active' : 'inactive'"
                        @update:model-value="(val) => form.is_active = val === 'active'" class="flex gap-4">
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
                <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
                <Button @click="handleSubmit" :disabled="form.processing">{{ group ? 'Update' : 'Create' }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
