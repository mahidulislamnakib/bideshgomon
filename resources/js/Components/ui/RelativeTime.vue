<template>
    <component
        :is="as"
        :class="['inline-flex items-center gap-1', className]"
        :title="fullDateTime"
    >
        <!-- Optional Icon -->
        <ClockIcon
            v-if="showIcon"
            :class="iconClasses"
        />
        
        <!-- Relative Time -->
        <span :class="textClasses">
            {{ relativeTimeText }}
        </span>
        
        <!-- Optional "Live" indicator -->
        <span
            v-if="live && showLiveIndicator"
            class="relative flex h-2 w-2"
        >
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
        </span>
    </component>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { ClockIcon } from '@heroicons/vue/24/outline';
import { useRelativeTime } from '@/Composables/useRelativeTime';

const props = defineProps({
    // Date value (Date object, ISO string, or timestamp)
    date: {
        type: [Date, String, Number],
        required: true
    },
    // Component wrapper type
    as: {
        type: String,
        default: 'span'
    },
    // Enable live updates
    live: {
        type: Boolean,
        default: true
    },
    // Update interval in ms (only when live=true)
    updateInterval: {
        type: Number,
        default: 60000 // 1 minute
    },
    // Short format ("2h" vs "2 hours ago")
    short: {
        type: Boolean,
        default: false
    },
    // Include seconds precision
    includeSeconds: {
        type: Boolean,
        default: false
    },
    // Max days before showing full date
    maxDays: {
        type: Number,
        default: 7
    },
    // Show clock icon
    showIcon: {
        type: Boolean,
        default: false
    },
    // Show live indicator dot
    showLiveIndicator: {
        type: Boolean,
        default: false
    },
    // Text size
    size: {
        type: String,
        default: 'sm',
        validator: (val) => ['xs', 'sm', 'md', 'lg'].includes(val)
    },
    // Additional CSS classes
    className: {
        type: String,
        default: ''
    },
    // Text color variant
    variant: {
        type: String,
        default: 'muted',
        validator: (val) => ['muted', 'default', 'primary', 'success', 'warning', 'danger'].includes(val)
    }
});

const { formatRelative, formatDateTime, startLiveUpdates, stopLiveUpdates, now } = useRelativeTime();

// Force reactivity by tracking the now value
const tick = ref(0);
let intervalId = null;

onMounted(() => {
    if (props.live) {
        intervalId = setInterval(() => {
            tick.value++;
        }, props.updateInterval);
    }
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

// Watch for live prop changes
watch(() => props.live, (newVal) => {
    if (newVal && !intervalId) {
        intervalId = setInterval(() => {
            tick.value++;
        }, props.updateInterval);
    } else if (!newVal && intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
});

const relativeTimeText = computed(() => {
    // Touch tick to trigger reactivity
    tick.value;
    
    return formatRelative(props.date, {
        shortFormat: props.short,
        includeSeconds: props.includeSeconds,
        maxDays: props.maxDays
    });
});

const fullDateTime = computed(() => {
    return formatDateTime(props.date);
});

const textClasses = computed(() => {
    const sizes = {
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg'
    };
    
    const variants = {
        muted: 'text-gray-500 dark:text-gray-400',
        default: 'text-gray-700 dark:text-gray-300',
        primary: 'text-primary-600 dark:text-primary-400',
        success: 'text-green-600 dark:text-green-400',
        warning: 'text-yellow-600 dark:text-yellow-400',
        danger: 'text-red-600 dark:text-red-400'
    };
    
    return [
        sizes[props.size] || sizes.sm,
        variants[props.variant] || variants.muted
    ];
});

const iconClasses = computed(() => {
    const sizes = {
        xs: 'h-3 w-3',
        sm: 'h-4 w-4',
        md: 'h-5 w-5',
        lg: 'h-6 w-6'
    };
    
    return [
        sizes[props.size] || sizes.sm,
        'text-gray-400 dark:text-gray-500'
    ];
});
</script>
