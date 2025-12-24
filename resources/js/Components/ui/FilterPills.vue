<template>
    <div
        v-if="filters.length > 0 || alwaysShow"
        :class="['flex flex-wrap items-center gap-2', containerClass]"
    >
        <!-- Label -->
        <span
            v-if="label"
            class="text-sm font-medium text-gray-600 dark:text-gray-400"
        >
            {{ label }}
        </span>
        
        <!-- Filter Pills -->
        <TransitionGroup
            name="filter-pill"
            tag="div"
            class="flex flex-wrap items-center gap-2"
        >
            <div
                v-for="filter in filters"
                :key="filter.key"
                :class="[
                    'inline-flex items-center gap-1.5 rounded-full text-sm font-medium',
                    'transition-all duration-200',
                    sizeClasses,
                    getFilterColorClasses(filter)
                ]"
            >
                <!-- Icon -->
                <component
                    v-if="filter.icon"
                    :is="filter.icon"
                    :class="iconSizeClasses"
                />
                
                <!-- Label & Value -->
                <span class="flex items-center gap-1">
                    <span v-if="filter.label" class="opacity-75">{{ filter.label }}:</span>
                    <span class="font-semibold">{{ formatValue(filter) }}</span>
                </span>
                
                <!-- Remove Button -->
                <button
                    v-if="removable"
                    @click="removeFilter(filter)"
                    :class="[
                        'rounded-full p-0.5 transition-colors',
                        'hover:bg-black/10 dark:hover:bg-white/10',
                        'focus:outline-none focus:ring-2 focus:ring-offset-1',
                        getRemoveButtonClasses(filter)
                    ]"
                    :title="`Remove ${filter.label || filter.key} filter`"
                >
                    <XMarkIcon :class="removeIconClasses" />
                </button>
            </div>
        </TransitionGroup>
        
        <!-- Clear All Button -->
        <button
            v-if="showClearAll && filters.length > 1"
            @click="clearAll"
            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline-offset-2 hover:underline transition-colors"
        >
            {{ clearAllLabel }}
        </button>
        
        <!-- Filter Count Badge -->
        <span
            v-if="showCount && filters.length > 0"
            class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
        >
            {{ filters.length }} {{ filters.length === 1 ? 'filter' : 'filters' }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    // Array of active filters
    // Each filter: { key, value, label?, icon?, color?, formatter? }
    filters: {
        type: Array,
        required: true,
        default: () => []
    },
    // Label before pills
    label: {
        type: String,
        default: null
    },
    // Allow removing individual filters
    removable: {
        type: Boolean,
        default: true
    },
    // Show "Clear all" button
    showClearAll: {
        type: Boolean,
        default: true
    },
    // Clear all button text
    clearAllLabel: {
        type: String,
        default: 'Clear all'
    },
    // Show filter count badge
    showCount: {
        type: Boolean,
        default: false
    },
    // Always show container (even when empty)
    alwaysShow: {
        type: Boolean,
        default: false
    },
    // Size variant
    size: {
        type: String,
        default: 'md',
        validator: (val) => ['sm', 'md', 'lg'].includes(val)
    },
    // Default color for pills
    defaultColor: {
        type: String,
        default: 'gray'
    },
    // Container CSS class
    containerClass: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['remove', 'clear']);

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-sm',
        lg: 'px-3 py-1.5 text-base'
    };
    return sizes[props.size] || sizes.md;
});

const iconSizeClasses = computed(() => {
    const sizes = {
        sm: 'h-3 w-3',
        md: 'h-4 w-4',
        lg: 'h-5 w-5'
    };
    return sizes[props.size] || sizes.md;
});

const removeIconClasses = computed(() => {
    const sizes = {
        sm: 'h-3 w-3',
        md: 'h-3.5 w-3.5',
        lg: 'h-4 w-4'
    };
    return sizes[props.size] || sizes.md;
});

const colorMap = {
    gray: {
        bg: 'bg-gray-100 dark:bg-gray-700',
        text: 'text-gray-700 dark:text-gray-200',
        ring: 'focus:ring-gray-400'
    },
    primary: {
        bg: 'bg-primary-100 dark:bg-primary-900/50',
        text: 'text-primary-700 dark:text-primary-300',
        ring: 'focus:ring-primary-400'
    },
    blue: {
        bg: 'bg-blue-100 dark:bg-blue-900/50',
        text: 'text-blue-700 dark:text-blue-300',
        ring: 'focus:ring-blue-400'
    },
    green: {
        bg: 'bg-green-100 dark:bg-green-900/50',
        text: 'text-green-700 dark:text-green-300',
        ring: 'focus:ring-green-400'
    },
    yellow: {
        bg: 'bg-yellow-100 dark:bg-yellow-900/50',
        text: 'text-yellow-700 dark:text-yellow-300',
        ring: 'focus:ring-yellow-400'
    },
    red: {
        bg: 'bg-red-100 dark:bg-red-900/50',
        text: 'text-red-700 dark:text-red-300',
        ring: 'focus:ring-red-400'
    },
    purple: {
        bg: 'bg-purple-100 dark:bg-purple-900/50',
        text: 'text-purple-700 dark:text-purple-300',
        ring: 'focus:ring-purple-400'
    },
    indigo: {
        bg: 'bg-indigo-100 dark:bg-indigo-900/50',
        text: 'text-indigo-700 dark:text-indigo-300',
        ring: 'focus:ring-indigo-400'
    },
    pink: {
        bg: 'bg-pink-100 dark:bg-pink-900/50',
        text: 'text-pink-700 dark:text-pink-300',
        ring: 'focus:ring-pink-400'
    }
};

function getFilterColorClasses(filter) {
    const color = filter.color || props.defaultColor;
    const scheme = colorMap[color] || colorMap.gray;
    return `${scheme.bg} ${scheme.text}`;
}

function getRemoveButtonClasses(filter) {
    const color = filter.color || props.defaultColor;
    const scheme = colorMap[color] || colorMap.gray;
    return scheme.ring;
}

function formatValue(filter) {
    if (filter.formatter && typeof filter.formatter === 'function') {
        return filter.formatter(filter.value);
    }
    
    // Handle arrays
    if (Array.isArray(filter.value)) {
        return filter.value.join(', ');
    }
    
    // Handle booleans
    if (typeof filter.value === 'boolean') {
        return filter.value ? 'Yes' : 'No';
    }
    
    // Handle dates
    if (filter.value instanceof Date) {
        return filter.value.toLocaleDateString('en-GB');
    }
    
    return filter.value;
}

function removeFilter(filter) {
    emit('remove', filter);
}

function clearAll() {
    emit('clear');
}
</script>

<style scoped>
.filter-pill-move {
    transition: all 0.3s ease;
}

.filter-pill-enter-active {
    transition: all 0.2s ease-out;
}

.filter-pill-leave-active {
    transition: all 0.15s ease-in;
    position: absolute;
}

.filter-pill-enter-from {
    opacity: 0;
    transform: scale(0.8);
}

.filter-pill-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
