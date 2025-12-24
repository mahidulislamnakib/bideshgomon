<template>
    <component
        :is="as"
        :class="[
            'inline-flex items-center gap-1.5 transition-all duration-200 cursor-pointer',
            'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
            variantClasses,
            copied ? 'text-green-600 dark:text-green-400' : '',
            disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
        :disabled="disabled"
        @click="handleCopy"
        :title="copied ? copiedTooltip : tooltip"
    >
        <!-- Content to display (and copy) -->
        <span v-if="showValue" :class="valueClasses">
            <slot>{{ displayValue }}</slot>
        </span>
        
        <!-- Copy Icon with animation -->
        <span class="relative flex-shrink-0" :class="iconContainerClasses">
            <transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0 scale-75"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-75"
                mode="out-in"
            >
                <CheckIcon
                    v-if="copied"
                    :class="iconClasses"
                />
                <ClipboardDocumentIcon
                    v-else
                    :class="iconClasses"
                />
            </transition>
        </span>
        
        <!-- Copied Badge (optional) -->
        <transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0 translate-x-1"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-1"
        >
            <span
                v-if="copied && showCopiedBadge"
                class="text-xs font-medium text-green-600 dark:text-green-400"
            >
                Copied!
            </span>
        </transition>
    </component>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ClipboardDocumentIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { useClipboard } from '@/Composables/useClipboard';

const props = defineProps({
    // Text value to copy
    value: {
        type: [String, Number],
        required: true
    },
    // Display value (if different from copy value)
    displayValue: {
        type: String,
        default: null
    },
    // Component wrapper type
    as: {
        type: String,
        default: 'button'
    },
    // Visual variant
    variant: {
        type: String,
        default: 'ghost',
        validator: (val) => ['ghost', 'outline', 'solid', 'link', 'icon-only'].includes(val)
    },
    // Size
    size: {
        type: String,
        default: 'sm',
        validator: (val) => ['xs', 'sm', 'md', 'lg'].includes(val)
    },
    // Show the value text
    showValue: {
        type: Boolean,
        default: true
    },
    // Show "Copied!" badge
    showCopiedBadge: {
        type: Boolean,
        default: false
    },
    // Tooltip text
    tooltip: {
        type: String,
        default: 'Click to copy'
    },
    // Tooltip when copied
    copiedTooltip: {
        type: String,
        default: 'Copied!'
    },
    // Truncate long values
    truncate: {
        type: Boolean,
        default: false
    },
    // Max width for truncation
    maxWidth: {
        type: String,
        default: '200px'
    },
    // Disabled state
    disabled: {
        type: Boolean,
        default: false
    },
    // Custom formatter function
    formatter: {
        type: Function,
        default: null
    },
    // Reset delay in ms
    resetDelay: {
        type: Number,
        default: 2000
    }
});

const emit = defineEmits(['copied', 'error']);

const { copied, error, copy } = useClipboard();

const textToCopy = computed(() => {
    const value = String(props.value);
    return props.formatter ? props.formatter(value) : value;
});

const variantClasses = computed(() => {
    const variants = {
        ghost: 'hover:bg-gray-100 dark:hover:bg-gray-700 rounded px-1.5 py-0.5 text-gray-700 dark:text-gray-300',
        outline: 'border border-gray-300 dark:border-gray-600 rounded px-2 py-1 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300',
        solid: 'bg-gray-100 dark:bg-gray-700 rounded px-2 py-1 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300',
        link: 'text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 underline-offset-2 hover:underline',
        'icon-only': 'p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400'
    };
    return variants[props.variant] || variants.ghost;
});

const iconClasses = computed(() => {
    const sizes = {
        xs: 'h-3 w-3',
        sm: 'h-4 w-4',
        md: 'h-5 w-5',
        lg: 'h-6 w-6'
    };
    return sizes[props.size] || sizes.sm;
});

const iconContainerClasses = computed(() => {
    const sizes = {
        xs: 'h-3 w-3',
        sm: 'h-4 w-4',
        md: 'h-5 w-5',
        lg: 'h-6 w-6'
    };
    return sizes[props.size] || sizes.sm;
});

const valueClasses = computed(() => {
    const classes = ['font-mono'];
    
    // Text sizes
    const sizes = {
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg'
    };
    classes.push(sizes[props.size] || sizes.sm);
    
    // Truncation
    if (props.truncate) {
        classes.push('truncate');
    }
    
    return classes;
});

async function handleCopy() {
    if (props.disabled) return;
    
    const success = await copy(textToCopy.value, props.resetDelay);
    
    if (success) {
        emit('copied', textToCopy.value);
    } else {
        emit('error', error.value);
    }
}
</script>

<style scoped>
.truncate {
    max-width: v-bind(maxWidth);
}
</style>
