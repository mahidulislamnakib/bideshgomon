<script setup>
import { ChevronUpIcon, ChevronDownIcon, ChevronUpDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    column: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    sortColumn: {
        type: String,
        default: ''
    },
    sortDirection: {
        type: String,
        default: 'asc'
    },
    align: {
        type: String,
        default: 'left',
        validator: (v) => ['left', 'center', 'right'].includes(v)
    }
})

const emit = defineEmits(['sort'])

const isSorted = () => props.sortColumn === props.column
const isAsc = () => isSorted() && props.sortDirection === 'asc'
const isDesc = () => isSorted() && props.sortDirection === 'desc'

const handleClick = () => {
    emit('sort', props.column)
}

const alignmentClasses = {
    left: 'justify-start',
    center: 'justify-center',
    right: 'justify-end'
}
</script>

<template>
    <th 
        class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 bg-neutral-50 dark:bg-neutral-800/50 cursor-pointer select-none hover:bg-neutral-100 dark:hover:bg-neutral-700/50 transition-colors"
        :class="{ 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20': isSorted() }"
        @click="handleClick"
    >
        <div class="flex items-center gap-1.5" :class="alignmentClasses[align]">
            <span>{{ label }}</span>
            <span class="flex-shrink-0">
                <!-- Sorted ascending -->
                <ChevronUpIcon 
                    v-if="isAsc()" 
                    class="h-4 w-4 text-primary-600 dark:text-primary-400" 
                />
                <!-- Sorted descending -->
                <ChevronDownIcon 
                    v-else-if="isDesc()" 
                    class="h-4 w-4 text-primary-600 dark:text-primary-400" 
                />
                <!-- Not sorted -->
                <ChevronUpDownIcon 
                    v-else 
                    class="h-4 w-4 text-neutral-400 dark:text-neutral-500 opacity-0 group-hover:opacity-100 transition-opacity" 
                />
            </span>
        </div>
    </th>
</template>

<style scoped>
th:hover .opacity-0 {
    opacity: 1;
}
</style>
