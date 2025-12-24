<template>
    <div
        :class="[
            'relative inline-flex items-center justify-center flex-shrink-0 overflow-hidden',
            sizeClasses,
            rounded ? 'rounded-full' : 'rounded-lg',
            !src && !hasSlot ? colorClasses : '',
            ring ? ringClasses : ''
        ]"
        :title="name"
    >
        <!-- Image -->
        <img
            v-if="src && !imageError"
            :src="src"
            :alt="name"
            class="w-full h-full object-cover"
            @error="imageError = true"
        />
        
        <!-- Initials Fallback -->
        <span
            v-else-if="!hasSlot"
            :class="['font-medium text-white select-none', textSizeClasses]"
        >
            {{ initials }}
        </span>
        
        <!-- Custom Slot Content -->
        <slot v-else />
        
        <!-- Status Indicator -->
        <span
            v-if="status"
            :class="[
                'absolute bottom-0 right-0 block rounded-full ring-2 ring-white dark:ring-gray-800',
                statusSizeClasses,
                statusColorClasses
            ]"
        />
        
        <!-- Badge -->
        <span
            v-if="badge !== undefined"
            :class="[
                'absolute -top-1 -right-1 flex items-center justify-center rounded-full',
                'bg-red-500 text-white text-xs font-bold',
                badgeSizeClasses
            ]"
        >
            {{ typeof badge === 'number' && badge > 99 ? '99+' : badge }}
        </span>
    </div>
</template>

<script setup>
import { ref, computed, useSlots } from 'vue';

const props = defineProps({
    // User name for generating initials
    name: {
        type: String,
        default: ''
    },
    // Image source URL
    src: {
        type: String,
        default: null
    },
    // Size variant
    size: {
        type: String,
        default: 'md',
        validator: (val) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(val)
    },
    // Whether to use rounded-full (true) or rounded-lg (false)
    rounded: {
        type: Boolean,
        default: true
    },
    // Status indicator: 'online', 'offline', 'busy', 'away'
    status: {
        type: String,
        default: null,
        validator: (val) => [null, 'online', 'offline', 'busy', 'away'].includes(val)
    },
    // Badge count (for notifications)
    badge: {
        type: [Number, String],
        default: undefined
    },
    // Ring/border around avatar
    ring: {
        type: Boolean,
        default: false
    },
    // Custom color for initials background (overrides auto-color)
    color: {
        type: String,
        default: null
    }
});

const slots = useSlots();
const imageError = ref(false);

const hasSlot = computed(() => !!slots.default);

// Generate initials from name
const initials = computed(() => {
    if (!props.name) return '?';
    
    const parts = props.name.trim().split(/\s+/);
    if (parts.length === 1) {
        return parts[0].charAt(0).toUpperCase();
    }
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
});

// Generate consistent color based on name
const colorClasses = computed(() => {
    if (props.color) {
        return props.color;
    }
    
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-yellow-500',
        'bg-red-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-teal-500',
        'bg-orange-500',
        'bg-cyan-500'
    ];
    
    // Generate hash from name for consistent color
    let hash = 0;
    const str = props.name || 'default';
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    
    return colors[Math.abs(hash) % colors.length];
});

// Size classes for the container
const sizeClasses = computed(() => {
    const sizes = {
        xs: 'h-6 w-6',
        sm: 'h-8 w-8',
        md: 'h-10 w-10',
        lg: 'h-12 w-12',
        xl: 'h-16 w-16',
        '2xl': 'h-20 w-20'
    };
    return sizes[props.size] || sizes.md;
});

// Text size for initials
const textSizeClasses = computed(() => {
    const sizes = {
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-sm',
        lg: 'text-base',
        xl: 'text-lg',
        '2xl': 'text-xl'
    };
    return sizes[props.size] || sizes.md;
});

// Status indicator size
const statusSizeClasses = computed(() => {
    const sizes = {
        xs: 'h-1.5 w-1.5',
        sm: 'h-2 w-2',
        md: 'h-2.5 w-2.5',
        lg: 'h-3 w-3',
        xl: 'h-3.5 w-3.5',
        '2xl': 'h-4 w-4'
    };
    return sizes[props.size] || sizes.md;
});

// Status indicator color
const statusColorClasses = computed(() => {
    const colors = {
        online: 'bg-green-500',
        offline: 'bg-gray-400',
        busy: 'bg-red-500',
        away: 'bg-yellow-500'
    };
    return colors[props.status] || '';
});

// Badge size
const badgeSizeClasses = computed(() => {
    const sizes = {
        xs: 'h-3 w-3 text-[8px]',
        sm: 'h-4 w-4 text-[9px]',
        md: 'h-5 w-5 text-[10px]',
        lg: 'h-5 w-5 text-xs',
        xl: 'h-6 w-6 text-xs',
        '2xl': 'h-7 w-7 text-sm'
    };
    return sizes[props.size] || sizes.md;
});

// Ring classes
const ringClasses = computed(() => {
    return 'ring-2 ring-white dark:ring-gray-800';
});
</script>
