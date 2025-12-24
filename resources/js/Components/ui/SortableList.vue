<template>
    <div :class="['sortable-list', containerClass]">
        <TransitionGroup
            :name="animated ? 'sortable' : ''"
            tag="div"
            :class="listClass"
        >
            <div
                v-for="(item, index) in modelValue"
                :key="getItemKey(item, index)"
                :draggable="!disabled"
                :class="[
                    'sortable-item relative transition-all duration-200',
                    itemClass,
                    isBeingDragged(index) ? 'opacity-50 scale-[0.98]' : '',
                    isDraggedOver(index) ? 'ring-2 ring-primary-500 ring-offset-2' : '',
                    disabled ? 'cursor-default' : 'cursor-move'
                ]"
                @dragstart="(e) => handleDragStart(e, item, index)"
                @dragend="handleDragEnd"
                @dragover="(e) => handleDragOver(e, index)"
                @dragleave="handleDragLeave"
                @drop="(e) => onDrop(e, index)"
            >
                <!-- Drop indicator - before -->
                <div
                    v-if="getDropPosition(index) === 'before'"
                    :class="[
                        'absolute left-0 right-0 h-0.5 bg-primary-500 -top-1',
                        horizontal ? 'w-0.5 h-full -left-1 top-0' : ''
                    ]"
                />
                
                <!-- Drag Handle (optional) -->
                <div
                    v-if="showHandle"
                    class="drag-handle flex-shrink-0 cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <slot name="handle">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                        </svg>
                    </slot>
                </div>
                
                <!-- Item Content -->
                <slot :item="item" :index="index" :is-dragging="isBeingDragged(index)">
                    {{ item }}
                </slot>
                
                <!-- Drop indicator - after -->
                <div
                    v-if="getDropPosition(index) === 'after'"
                    :class="[
                        'absolute left-0 right-0 h-0.5 bg-primary-500 -bottom-1',
                        horizontal ? 'w-0.5 h-full -right-1 top-0 left-auto' : ''
                    ]"
                />
            </div>
        </TransitionGroup>
        
        <!-- Empty State -->
        <div
            v-if="modelValue.length === 0"
            :class="['text-center py-8 text-gray-500 dark:text-gray-400', emptyClass]"
        >
            <slot name="empty">
                <p>No items to display</p>
            </slot>
        </div>
    </div>
</template>

<script setup>
import { useDragAndDrop, reorderArray } from '@/Composables/useDragAndDrop';

const props = defineProps({
    // Array of items to sort
    modelValue: {
        type: Array,
        required: true
    },
    // Key field for items (or function)
    itemKey: {
        type: [String, Function],
        default: 'id'
    },
    // Disable drag and drop
    disabled: {
        type: Boolean,
        default: false
    },
    // Show drag handle
    showHandle: {
        type: Boolean,
        default: false
    },
    // Horizontal layout
    horizontal: {
        type: Boolean,
        default: false
    },
    // Enable transition animation
    animated: {
        type: Boolean,
        default: true
    },
    // Container CSS class
    containerClass: {
        type: String,
        default: ''
    },
    // List wrapper CSS class
    listClass: {
        type: String,
        default: 'space-y-2'
    },
    // Item CSS class
    itemClass: {
        type: String,
        default: 'bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex items-center gap-3'
    },
    // Empty state CSS class
    emptyClass: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'reorder']);

const {
    isDragging,
    draggedIndex,
    dragOverIndex,
    handleDragStart,
    handleDragEnd,
    handleDragOver,
    handleDragLeave,
    isBeingDragged,
    isDraggedOver,
    getDropPosition
} = useDragAndDrop({
    disabled: props.disabled,
    handleClass: props.showHandle ? 'drag-handle' : null
});

function getItemKey(item, index) {
    if (typeof props.itemKey === 'function') {
        return props.itemKey(item);
    }
    return item[props.itemKey] ?? index;
}

function onDrop(event, targetIndex) {
    event.preventDefault();
    
    const sourceIndex = draggedIndex.value;
    
    if (sourceIndex === -1 || sourceIndex === targetIndex) {
        return;
    }
    
    // Reorder the array
    const newItems = reorderArray(props.modelValue, sourceIndex, targetIndex);
    
    // Emit events
    emit('update:modelValue', newItems);
    emit('reorder', {
        items: newItems,
        from: sourceIndex,
        to: targetIndex,
        item: props.modelValue[sourceIndex]
    });
}
</script>

<style scoped>
.sortable-move {
    transition: transform 0.3s ease;
}

.sortable-enter-active,
.sortable-leave-active {
    transition: all 0.3s ease;
}

.sortable-enter-from,
.sortable-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

.sortable-leave-active {
    position: absolute;
}
</style>
