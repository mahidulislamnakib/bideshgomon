<template>
    <Popover class="relative" v-slot="{ open }">
        <!-- Bell Button -->
        <PopoverButton
            :class="[
                'relative p-2 rounded-lg transition-colors duration-200',
                'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                'hover:bg-gray-100 dark:hover:bg-gray-800',
                'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                open ? 'bg-gray-100 dark:bg-gray-800' : ''
            ]"
        >
            <BellIcon class="h-6 w-6" />
            
            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                :class="[
                    'absolute -top-0.5 -right-0.5 flex items-center justify-center',
                    'min-w-[18px] h-[18px] px-1 rounded-full',
                    'bg-red-500 text-white text-xs font-bold',
                    'animate-pulse'
                ]"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
            
            <!-- Ping animation for new notifications -->
            <span
                v-if="hasNew"
                class="absolute top-0 right-0 flex h-3 w-3"
            >
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
        </PopoverButton>
        
        <!-- Dropdown Panel -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-1 opacity-0"
        >
            <PopoverPanel
                :class="[
                    'absolute z-50 mt-2 w-80 sm:w-96 rounded-xl',
                    'bg-white dark:bg-gray-800 shadow-xl',
                    'ring-1 ring-black ring-opacity-5 dark:ring-gray-700',
                    'overflow-hidden',
                    position === 'left' ? 'left-0' : 'right-0'
                ]"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <span
                            v-if="unreadCount > 0"
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ unreadCount }} unread
                        </span>
                        <button
                            v-if="notifications.length > 0"
                            @click="markAllRead"
                            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium"
                        >
                            Mark all read
                        </button>
                    </div>
                </div>
                
                <!-- Notifications List -->
                <div
                    :class="[
                        'max-h-96 overflow-y-auto',
                        notifications.length === 0 ? '' : 'divide-y divide-gray-100 dark:divide-gray-700'
                    ]"
                >
                    <!-- Empty State -->
                    <div
                        v-if="notifications.length === 0"
                        class="py-12 text-center"
                    >
                        <BellSlashIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ emptyMessage }}
                        </p>
                    </div>
                    
                    <!-- Notification Items -->
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleClick(notification)"
                        :class="[
                            'relative px-4 py-3 cursor-pointer transition-colors duration-150',
                            'hover:bg-gray-50 dark:hover:bg-gray-700/50',
                            !notification.read ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''
                        ]"
                    >
                        <!-- Unread Indicator -->
                        <div
                            v-if="!notification.read"
                            class="absolute left-1.5 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-primary-500"
                        />
                        
                        <div class="flex gap-3">
                            <!-- Icon or Avatar -->
                            <div class="flex-shrink-0">
                                <div
                                    v-if="notification.icon"
                                    :class="[
                                        'flex items-center justify-center w-10 h-10 rounded-full',
                                        getIconBgClass(notification.type)
                                    ]"
                                >
                                    <component
                                        :is="notification.icon"
                                        :class="['h-5 w-5', getIconColorClass(notification.type)]"
                                    />
                                </div>
                                <img
                                    v-else-if="notification.avatar"
                                    :src="notification.avatar"
                                    :alt="notification.title"
                                    class="w-10 h-10 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    :class="[
                                        'flex items-center justify-center w-10 h-10 rounded-full',
                                        getIconBgClass(notification.type)
                                    ]"
                                >
                                    <BellIcon :class="['h-5 w-5', getIconColorClass(notification.type)]" />
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p
                                    :class="[
                                        'text-sm',
                                        !notification.read 
                                            ? 'font-medium text-gray-900 dark:text-white' 
                                            : 'text-gray-700 dark:text-gray-300'
                                    ]"
                                >
                                    {{ notification.title }}
                                </p>
                                <p
                                    v-if="notification.message"
                                    class="mt-0.5 text-sm text-gray-500 dark:text-gray-400 line-clamp-2"
                                >
                                    {{ notification.message }}
                                </p>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ formatTime(notification.time) }}
                                </p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex-shrink-0 flex items-start">
                                <button
                                    v-if="!notification.read"
                                    @click.stop="markAsRead(notification)"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    title="Mark as read"
                                >
                                    <CheckIcon class="h-4 w-4" />
                                </button>
                                <button
                                    v-if="dismissible"
                                    @click.stop="dismiss(notification)"
                                    class="p-1 text-gray-400 hover:text-red-500"
                                    title="Dismiss"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div
                    v-if="showViewAll && notifications.length > 0"
                    class="border-t border-gray-100 dark:border-gray-700"
                >
                    <Link
                        v-if="viewAllHref"
                        :href="viewAllHref"
                        class="block w-full px-4 py-3 text-center text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        {{ viewAllLabel }}
                    </Link>
                    <button
                        v-else
                        @click="$emit('view-all')"
                        class="block w-full px-4 py-3 text-center text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        {{ viewAllLabel }}
                    </button>
                </div>
            </PopoverPanel>
        </transition>
    </Popover>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import {
    BellIcon,
    BellSlashIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    // Array of notification objects
    // { id, title, message?, icon?, avatar?, type?, time, read, href? }
    notifications: {
        type: Array,
        default: () => []
    },
    // Panel title
    title: {
        type: String,
        default: 'Notifications'
    },
    // Empty state message
    emptyMessage: {
        type: String,
        default: 'No notifications yet'
    },
    // Show "View all" link
    showViewAll: {
        type: Boolean,
        default: true
    },
    // View all href
    viewAllHref: {
        type: String,
        default: null
    },
    // View all label
    viewAllLabel: {
        type: String,
        default: 'View all notifications'
    },
    // Allow dismissing notifications
    dismissible: {
        type: Boolean,
        default: true
    },
    // Show ping animation for new notifications
    hasNew: {
        type: Boolean,
        default: false
    },
    // Dropdown position
    position: {
        type: String,
        default: 'right',
        validator: (val) => ['left', 'right'].includes(val)
    }
});

const emit = defineEmits(['click', 'mark-read', 'mark-all-read', 'dismiss', 'view-all']);

const unreadCount = computed(() => {
    return props.notifications.filter(n => !n.read).length;
});

function getIconBgClass(type) {
    const classes = {
        success: 'bg-green-100 dark:bg-green-900/30',
        warning: 'bg-yellow-100 dark:bg-yellow-900/30',
        error: 'bg-red-100 dark:bg-red-900/30',
        info: 'bg-blue-100 dark:bg-blue-900/30',
        default: 'bg-gray-100 dark:bg-gray-700'
    };
    return classes[type] || classes.default;
}

function getIconColorClass(type) {
    const classes = {
        success: 'text-green-600 dark:text-green-400',
        warning: 'text-yellow-600 dark:text-yellow-400',
        error: 'text-red-600 dark:text-red-400',
        info: 'text-blue-600 dark:text-blue-400',
        default: 'text-gray-600 dark:text-gray-400'
    };
    return classes[type] || classes.default;
}

function formatTime(time) {
    if (!time) return '';
    
    const date = time instanceof Date ? time : new Date(time);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short'
    });
}

function handleClick(notification) {
    emit('click', notification);
    
    // Auto mark as read on click
    if (!notification.read) {
        emit('mark-read', notification);
    }
}

function markAsRead(notification) {
    emit('mark-read', notification);
}

function markAllRead() {
    emit('mark-all-read');
}

function dismiss(notification) {
    emit('dismiss', notification);
}
</script>
