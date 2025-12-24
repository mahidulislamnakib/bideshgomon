<template>
    <div class="flex items-center">
        <!-- Stacked Avatars -->
        <div class="flex -space-x-2">
            <UserAvatar
                v-for="(user, index) in displayedUsers"
                :key="user.id || index"
                :name="user.name"
                :src="user.avatar || user.image || user.photo"
                :size="size"
                ring
                class="relative"
                :style="{ zIndex: displayedUsers.length - index }"
            />
            
            <!-- Overflow Count -->
            <div
                v-if="overflowCount > 0"
                :class="[
                    'relative flex items-center justify-center rounded-full',
                    'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-200',
                    'ring-2 ring-white dark:ring-gray-800 font-medium',
                    avatarSizeClasses,
                    textSizeClasses
                ]"
                :style="{ zIndex: 0 }"
                :title="`${overflowCount} more`"
            >
                +{{ overflowCount > 99 ? '99' : overflowCount }}
            </div>
        </div>
        
        <!-- Optional Label -->
        <span
            v-if="showLabel && users.length > 0"
            class="ml-3 text-sm text-gray-600 dark:text-gray-400"
        >
            {{ labelText }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import UserAvatar from './UserAvatar.vue';

const props = defineProps({
    // Array of user objects { id, name, avatar/image/photo }
    users: {
        type: Array,
        required: true,
        default: () => []
    },
    // Maximum number of avatars to display
    max: {
        type: Number,
        default: 4
    },
    // Avatar size
    size: {
        type: String,
        default: 'md',
        validator: (val) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(val)
    },
    // Show text label with count
    showLabel: {
        type: Boolean,
        default: false
    },
    // Custom label text (uses default if not provided)
    label: {
        type: String,
        default: null
    }
});

const displayedUsers = computed(() => {
    return props.users.slice(0, props.max);
});

const overflowCount = computed(() => {
    return Math.max(0, props.users.length - props.max);
});

const labelText = computed(() => {
    if (props.label) return props.label;
    
    const count = props.users.length;
    if (count === 1) return '1 person';
    return `${count} people`;
});

const avatarSizeClasses = computed(() => {
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

const textSizeClasses = computed(() => {
    const sizes = {
        xs: 'text-[10px]',
        sm: 'text-xs',
        md: 'text-xs',
        lg: 'text-sm',
        xl: 'text-base',
        '2xl': 'text-lg'
    };
    return sizes[props.size] || sizes.md;
});
</script>
