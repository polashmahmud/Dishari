<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { VueDraggable } from 'vue-draggable-plus';
import { Edit, Trash2, GripVertical, ChevronRight, ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';

interface Menu {
    id: number;
    title: string;
    url: string | null;
    icon: string | null;
    order: number;
    is_active: boolean;
    children: Menu[];
}

const props = defineProps<{
    group?: string;
}>();

const list = defineModel<Menu[]>({ required: true });

const emit = defineEmits<{
    (e: 'edit', menu: Menu): void;
    (e: 'delete', id: number): void;
}>();

const expandedItems = ref<number[]>([]);

const toggleExpand = (id: number) => {
    const index = expandedItems.value.indexOf(id);
    if (index === -1) {
        // Ensure children array exists when expanding
        const item = findItem(list.value, id);
        if (item && !item.children) {
            item.children = [];
        }
        expandedItems.value.push(id);
    } else {
        expandedItems.value.splice(index, 1);
    }
};

const findItem = (items: Menu[], id: number): Menu | undefined => {
    for (const item of items) {
        if (item.id === id) return item;
        if (item.children) {
            const found = findItem(item.children, id);
            if (found) return found;
        }
    }
    return undefined;
};

const isExpanded = (id: number) => expandedItems.value.includes(id);
</script>

<template>
    <VueDraggable v-model="list" :group="{ name: 'menu-group' }" class="space-y-2 min-h-[10px]" ghost-class="opacity-50"
        handle=".handle" :animation="150">
        <div v-for="element in list" :key="element.id" class="rounded-md border bg-card text-card-foreground shadow-sm">
            <div class="flex items-center p-3 gap-3">
                <div class="handle cursor-grab hover:text-primary">
                    <GripVertical class="h-5 w-5 text-muted-foreground" />
                </div>

                <div class="flex-1 flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <component :is="isExpanded(element.id) ? ChevronDown : ChevronRight"
                            class="h-4 w-4 text-muted-foreground cursor-pointer w-4 hover:text-foreground transition-colors"
                            @click="toggleExpand(element.id)" />
                        <span class="font-medium">{{ element.title }}</span>
                    </div>

                    <Badge variant="outline" class="text-xs font-normal text-muted-foreground" v-if="element.url">
                        {{ element.url }}
                    </Badge>

                    <Badge variant="outline" class="text-xs font-normal text-muted-foreground" v-if="element.icon">
                        <component :is="element.icon" class="h-3 w-3 mr-1 inline-block" v-if="element.icon" />
                        {{ element.icon }}
                    </Badge>
                </div>

                <div class="flex items-center gap-2">
                    <Badge :variant="element.is_active ? 'default' : 'secondary'" class="text-xs">
                        {{ element.is_active ? 'Active' : 'Inactive' }}
                    </Badge>

                    <div class="flex items-center border-l pl-2 ml-2 gap-1">
                        <Button size="icon" variant="ghost" class="h-8 w-8" @click="$emit('edit', element)">
                            <Edit class="h-4 w-4" />
                        </Button>
                        <Button size="icon" variant="ghost" class="h-8 w-8 hover:text-destructive"
                            @click="$emit('delete', element.id)">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <div v-if="isExpanded(element.id)" class="pl-8 pr-3 pb-3 border-t bg-muted/30 min-h-[50px]">
                <MenuDraggableList v-model="element.children" @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)" />
            </div>
        </div>
    </VueDraggable>
</template>

<script lang="ts">
export default {
    name: 'MenuDraggableList'
}
</script>
