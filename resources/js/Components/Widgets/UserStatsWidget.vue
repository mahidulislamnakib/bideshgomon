<template>
    <div :class="[utilityClasses.card, 'user-stats-widget']">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-neutral-900">User Statistics</h3>
            <button
                @click="refreshData"
                class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                :disabled="loading"
            >
                <ArrowPathIcon :class="['h-5 w-5 text-neutral-500', loading && 'animate-spin']" />
            </button>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Total Users -->
            <div class="text-center p-4 bg-primary-50 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <UsersIcon class="h-6 w-6 text-primary-600" />
                </div>
                <p class="text-2xl font-bold text-primary-700">{{ formatNumber(stats.total) }}</p>
                <p class="text-xs text-neutral-600 mt-1">Total Users</p>
            </div>

            <!-- Active Users -->
            <div class="text-center p-4 bg-success-50 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <CheckCircleIcon class="h-6 w-6 text-success-600" />
                </div>
                <p class="text-2xl font-bold text-success-700">{{ formatNumber(stats.active) }}</p>
                <p class="text-xs text-neutral-600 mt-1">Active Users</p>
            </div>

            <!-- New This Month -->
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <UserPlusIcon class="h-6 w-6 text-blue-600" />
                </div>
                <p class="text-2xl font-bold text-blue-700">{{ formatNumber(stats.newThisMonth) }}</p>
                <p class="text-xs text-neutral-600 mt-1">New This Month</p>
            </div>

            <!-- Inactive -->
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600" />
                </div>
                <p class="text-2xl font-bold text-yellow-700">{{ formatNumber(stats.inactive) }}</p>
                <p class="text-xs text-neutral-600 mt-1">Inactive Users</p>
            </div>
        </div>

        <!-- Growth Indicator -->
        <div class="p-4 bg-neutral-50 rounded-lg mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-700">Growth Rate</p>
                    <p class="text-xs text-neutral-500 mt-1">Compared to last month</p>
                </div>
                <div :class="[
                    'flex items-center gap-1 font-semibold',
                    stats.growthRate >= 0 ? 'text-success-600' : 'text-red-600'
                ]">
                    <component :is="stats.growthRate >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="h-5 w-5" />
                    <span class="text-xl">{{ Math.abs(stats.growthRate) }}%</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-3 h-2 bg-neutral-200 rounded-full overflow-hidden">
                <div
                    :class="[
                        'h-full rounded-full transition-all duration-500',
                        stats.growthRate >= 0 ? 'bg-success-500' : 'bg-red-500'
                    ]"
                    :style="{ width: `${Math.min(Math.abs(stats.growthRate), 100)}%` }"
                ></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-2">
            <Link
                :href="route('admin.users.index')"
                class="flex-1 text-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium"
            >
                View All Users
            </Link>
            <Link
                :href="route('admin.users.create')"
                class="flex items-center justify-center px-4 py-2 bg-white border border-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-50 transition-colors"
            >
                <UserPlusIcon class="h-5 w-5" />
            </Link>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    UsersIcon,
    CheckCircleIcon,
    UserPlusIcon,
    ExclamationTriangleIcon,
    ArrowPathIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            newThisMonth: 0,
            inactive: 0,
            growthRate: 0
        })
    }
});

const loading = ref(false);

const formatNumber = (num) => {
    return num.toLocaleString('en-US');
};

const refreshData = () => {
    loading.value = true;
    // Implement refresh logic
    setTimeout(() => {
        loading.value = false;
    }, 1000);
};
</script>
