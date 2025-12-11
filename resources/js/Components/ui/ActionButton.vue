<template>
    <component
        :is="componentType"
        :href="href"
        :type="type"
        :disabled="disabled || loading"
        :class="buttonClasses"
        @click="handleClick"
    >
        <!-- Loading Spinner -->
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>

        <!-- Icon (left) -->
        <component
            v-if="icon && iconPosition === 'left'"
            :is="icon"
            :class="iconClasses"
        />

        <!-- Label -->
        <span>{{ label }}</span>

        <!-- Icon (right) -->
        <component
            v-if="icon && iconPosition === 'right'"
            :is="icon"
            :class="iconClasses"
        />

        <!-- Badge (if provided) -->
        <span
            v-if="badge"
            class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full bg-white bg-opacity-30"
        >
            {{ badge }}
        </span>
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    href: {
        type: String,
        default: null // If null, renders as button
    },
    type: {
        type: String,
        default: 'button' // button, submit
    },
    variant: {
        type: String,
        default: 'primary', // primary, secondary, success, danger, ghost
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'ghost'].includes(value)
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    icon: {
        type: Object,
        default: null
    },
    iconPosition: {
        type: String,
        default: 'left', // left, right
        validator: (value) => ['left', 'right'].includes(value)
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    badge: {
        type: [String, Number],
        default: null
    },
    fullWidth: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['click']);

const componentType = computed(() => {
    return props.href ? Link : 'button';
});

const handleClick = (event) => {
    if (!props.href) {
        emit('click', event);
    }
};

// Size classes
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base'
    };
    return sizes[props.size];
});

// Icon size classes
const iconClasses = computed(() => {
    const sizes = {
        sm: 'h-4 w-4',
        md: 'h-5 w-5',
        lg: 'h-6 w-6'
    };
    return sizes[props.size];
});

// Variant classes
const variantClasses = computed(() => {
    const variants = {
        primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-sm',
        secondary: 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50 focus:ring-neutral-500',
        success: 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500 shadow-sm',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
        ghost: 'bg-transparent text-neutral-700 hover:bg-neutral-100 focus:ring-neutral-500'
    };
    return variants[props.variant];
});

const buttonClasses = computed(() => [
    // Base
    'inline-flex items-center justify-center gap-2',
    'font-medium rounded-lg',
    'transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    
    // Size
    sizeClasses.value,
    
    // Variant
    variantClasses.value,
    
    // States
    {
        'opacity-50 cursor-not-allowed': props.disabled || props.loading,
        'w-full': props.fullWidth
    }
]);
</script>
