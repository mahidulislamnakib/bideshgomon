<template>
    <div :class="[utilityClasses.card, 'recent-activities-widget h-full flex flex-col']">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-neutral-900">Recent Activities</h3>
            <Link
                :href="route('admin.analytics.dashboard')"
                class="text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors"
            >
                View All
            </Link>
        </div>

        <!-- Activities List -->
        <div v-if="activities.length > 0" class="flex-1 overflow-y-auto space-y-4">
            <div
                v-for="activity in activities"
                :key="activity.id"
                class="flex gap-4 pb-4 border-b border-neutral-100 last:border-0"
            >
                <!-- Icon -->
                <div :class="[
                    'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                    getActivityColorClass(activity.type).bg
                ]">
                    <component :is="getActivityIcon(activity.type)" :class="['h-5 w-5', getActivityColorClass(activity.type).text]" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-neutral-900">{{ activity.title }}</p>
                    <p class="text-sm text-neutral-600 mt-1">{{ activity.description }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs text-neutral-500">{{ activity.time }}</span>
                        <span v-if="activity.user" class="text-xs text-neutral-400">•</span>
                        <span v-if="activity.user" class="text-xs text-neutral-500">{{ activity.user }}</span>
                    </div>
                </div>

                <!-- Badge (if applicable) -->
                <div v-if="activity.badge" :class="[
                    'flex-shrink-0 px-2 py-1 rounded-full text-xs font-semibold',
                    getBadgeColorClass(activity.badge.type)
                ]">
                    {{ activity.badge.text }}
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex-1 flex flex-col items-center justify-center py-12">
            <ClockIcon class="h-12 w-12 text-neutral-300 mb-3" />
            <h3 class="text-sm font-medium text-neutral-900">No recent activities</h3>
            <p class="text-sm text-neutral-500 mt-1">Activities will appear here as they occur</p>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import {
    UserPlusIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    CurrencyDollarIcon,
    ShieldCheckIcon,
    ClockIcon,
    BellAlertIcon
} from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';

const props = defineProps({
    activities: {
        type: Array,
        default: () => []
        // Example structure:
        // [{
        //   id: 1,
        //   type: 'user_registered',
        //   title: 'New user registered',
        //   description: 'John Doe just signed up',
        //   time: '2 minutes ago',
        //   user: 'System',
        //   badge: { type: 'success', text: 'New' }
        // }]
    }
});

const getActivityIcon = (type) => {
    const icons = {
        user_registered: UserPlusIcon,
        application_submitted: DocumentTextIcon,
        application_approved: CheckCircleIcon,
        application_rejected: XCircleIcon,
        payment_received: CurrencyDollarIcon,
        verification_completed: ShieldCheckIcon,
        system_alert: BellAlertIcon
    };
    return icons[type] || ClockIcon;
};

const getActivityColorClass = (type) => {
    const colors = {
        user_registered: { bg: 'bg-blue-100', text: 'text-growth-600' },
        application_submitted: { bg: 'bg-yellow-100', text: 'text-yellow-600' },
        application_approved: { bg: 'bg-success-100', text: 'text-success-600' },
        application_rejected: { bg: 'bg-red-100', text: 'text-red-600' },
        payment_received: { bg: 'bg-green-100', text: 'text-green-600' },
        verification_completed: { bg: 'bg-purple-100', text: 'text-purple-600' },
        system_alert: { bg: 'bg-orange-100', text: 'text-orange-600' }
    };
    return colors[type] || { bg: 'bg-neutral-100', text: 'text-neutral-600' };
};

const getBadgeColorClass = (type) => {
    const colors = {
        success: 'bg-success-100 text-success-700',
        warning: 'bg-yellow-100 text-yellow-700',
        danger: 'bg-red-100 text-red-700',
        info: 'bg-blue-100 text-blue-700',
        neutral: 'bg-neutral-100 text-neutral-700'
    };
    return colors[type] || colors.neutral;
};
</script>
