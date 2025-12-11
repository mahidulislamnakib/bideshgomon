<template>
    <div :class="['stats-card', utilityClasses.card, 'hover:shadow-lg transition-shadow duration-300']">
        <!-- Icon -->
        <div class="flex items-start justify-between mb-4">
            <div :class="[
                'flex-shrink-0 p-3 rounded-lg',
                iconBackgroundColor
            ]">
                <component :is="icon" :class="['h-6 w-6', iconColor]" />
            </div>
            
            <!-- Change Indicator (if provided) -->
            <div v-if="change !== undefined" :class="[
                'flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full',
                changeIndicatorClass
            ]">
                <component :is="changeIcon" class="h-3 w-3" />
                <span>{{ Math.abs(change) }}%</span>
            </div>
        </div>

        <!-- Label -->
        <div class="mb-1">
            <h3 class="text-sm font-medium text-neutral-600">{{ label }}</h3>
        </div>

        <!-- Value -->
        <div class="flex items-baseline gap-2">
            <p :class="[
                'text-3xl font-bold',
                valueColor
            ]">
                {{ formattedValue }}
            </p>
            <span v-if="unit" class="text-sm text-neutral-500 font-medium">{{ unit }}</span>
        </div>

        <!-- Description (optional) -->
        <p v-if="description" class="mt-2 text-xs text-neutral-500">
            {{ description }}
        </p>

        <!-- Footer Action (optional) -->
        <div v-if="action" class="mt-4 pt-4 border-t border-neutral-200">
            <Link 
                :href="action.href"
                class="text-sm font-medium text-primary-600 hover:text-primary-700 flex items-center gap-1 transition-colors"
            >
                {{ action.label }}
                <ArrowRightIcon class="h-4 w-4" />
            </Link>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowUpIcon, ArrowDownIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';

const props = defineProps({
    icon: {
        type: Object,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    unit: {
        type: String,
        default: ''
    },
    change: {
        type: Number,
        default: undefined // e.g., 12.5 for +12.5%, -5 for -5%
    },
    description: {
        type: String,
        default: ''
    },
    variant: {
        type: String,
        default: 'primary', // primary, success, warning, danger, neutral
        validator: (value) => ['primary', 'success', 'warning', 'danger', 'neutral'].includes(value)
    },
    action: {
        type: Object,
        default: null // { label: 'View details', href: '/...' }
    }
});

// Format large numbers with commas
const formattedValue = computed(() => {
    if (typeof props.value === 'number') {
        return props.value.toLocaleString('en-US');
    }
    return props.value;
});

// Icon background color based on variant
const iconBackgroundColor = computed(() => {
    const colors = {
        primary: 'bg-primary-100',
        success: 'bg-success-100',
        warning: 'bg-yellow-100',
        danger: 'bg-red-100',
        neutral: 'bg-neutral-100'
    };
    return colors[props.variant];
});

// Icon color based on variant
const iconColor = computed(() => {
    const colors = {
        primary: 'text-primary-600',
        success: 'text-success-600',
        warning: 'text-yellow-600',
        danger: 'text-red-600',
        neutral: 'text-neutral-600'
    };
    return colors[props.variant];
});

// Value color based on variant
const valueColor = computed(() => {
    const colors = {
        primary: 'text-neutral-900',
        success: 'text-success-700',
        warning: 'text-yellow-700',
        danger: 'text-red-700',
        neutral: 'text-neutral-900'
    };
    return colors[props.variant];
});

// Change indicator styling
const changeIcon = computed(() => {
    return props.change >= 0 ? ArrowUpIcon : ArrowDownIcon;
});

const changeIndicatorClass = computed(() => {
    if (props.change >= 0) {
        return 'bg-success-100 text-success-700';
    }
    return 'bg-red-100 text-red-700';
});
</script>

