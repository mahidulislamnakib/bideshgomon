<template>
    <AdminLayout>
        <Head :title="`Analytics: ${ad.title}`" />

        <!-- Modern Hero Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col gap-6">
                    <!-- Back Button -->
                    <Link :href="route('admin.ads.index')" class="inline-flex items-center gap-2 text-white/90 hover:text-white transition-colors w-fit group">
                        <ChevronLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
                        Back to Ads
                    </Link>
                    
                    <!-- Header Content -->
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                            <ChartBarIcon class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">Ad Analytics</h1>
                            <p class="text-white/90 text-lg">{{ ad.title }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-8">
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Impressions -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl">
                            <EyeIcon class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Total Impressions</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ formatNumber(ad.impressions) }}</p>
                </div>

                <!-- Total Clicks -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl">
                            <CursorArrowRaysIcon class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Total Clicks</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ formatNumber(ad.clicks) }}</p>
                </div>

                <!-- CTR -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl">
                            <ArrowTrendingUpIcon class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Click-Through Rate</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ formatCTR(ad.ctr) }}%</p>
                </div>

                <!-- Status -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl">
                            <CheckCircleIcon class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Status</h3>
                    <span :class="{
                        'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold': true,
                        'bg-green-100 text-green-800': ad.status === 'active',
                        'bg-yellow-100 text-yellow-800': ad.status === 'paused',
                        'bg-gray-100 text-gray-800': ad.status === 'draft',
                        'bg-red-100 text-red-800': ad.status === 'expired'
                    }">
                        {{ ad.status.charAt(0).toUpperCase() + ad.status.slice(1) }}
                    </span>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Impressions Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg">
                            <EyeIcon class="h-4 w-4 text-white" />
                        </div>
                        Impressions (Last 30 Days)
                    </h3>
                    <div v-if="impressionsByDay.length > 0" class="space-y-2">
                        <div v-for="item in impressionsByDay" :key="item.date" class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 w-24">{{ formatDate(item.date) }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-purple-500 to-purple-600 h-full flex items-center px-3 rounded-full transition-all"
                                    :style="{ width: `${(item.count / maxImpressions) * 100}%` }"
                                >
                                    <span class="text-xs font-semibold text-white">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500">
                        No impression data yet
                    </div>
                </div>

                <!-- Clicks Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg">
                            <CursorArrowRaysIcon class="h-4 w-4 text-white" />
                        </div>
                        Clicks (Last 30 Days)
                    </h3>
                    <div v-if="clicksByDay.length > 0" class="space-y-2">
                        <div v-for="item in clicksByDay" :key="item.date" class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 w-24">{{ formatDate(item.date) }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-amber-500 to-orange-600 h-full flex items-center px-3 rounded-full transition-all"
                                    :style="{ width: `${(item.count / maxClicks) * 100}%` }"
                                >
                                    <span class="text-xs font-semibold text-white">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500">
                        No click data yet
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Pages -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg">
                            <DocumentTextIcon class="h-4 w-4 text-white" />
                        </div>
                        Top Pages
                    </h3>
                    <div v-if="topPages.length > 0" class="space-y-3">
                        <div v-for="(page, index) in topPages" :key="page.page" class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white text-xs font-bold">
                                {{ index + 1 }}
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">{{ page.page || 'Unknown' }}</span>
                            <span class="text-sm font-semibold text-gray-600">{{ page.count }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No page data yet
                    </div>
                </div>

                <!-- Device Breakdown -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg">
                            <DevicePhoneMobileIcon class="h-4 w-4 text-white" />
                        </div>
                        Device Types
                    </h3>
                    <div v-if="deviceBreakdown.length > 0" class="space-y-4">
                        <div v-for="device in deviceBreakdown" :key="device.device_type" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 capitalize">{{ device.device_type || 'Unknown' }}</span>
                                <span class="text-sm font-semibold text-gray-600">{{ device.count }} ({{ getPercentage(device.count) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-green-500 to-emerald-600 h-full rounded-full transition-all"
                                    :style="{ width: `${getPercentage(device.count)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No device data yet
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ChevronLeftIcon,
    ChartBarIcon,
    EyeIcon,
    CursorArrowRaysIcon,
    ArrowTrendingUpIcon,
    CheckCircleIcon,
    DocumentTextIcon,
    DevicePhoneMobileIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    ad: Object,
    impressionsByDay: Array,
    clicksByDay: Array,
    topPages: Array,
    deviceBreakdown: Array,
});

// Calculate max values for chart scaling
const maxImpressions = computed(() => {
    if (props.impressionsByDay.length === 0) return 1;
    return Math.max(...props.impressionsByDay.map(item => item.count));
});

const maxClicks = computed(() => {
    if (props.clicksByDay.length === 0) return 1;
    return Math.max(...props.clicksByDay.map(item => item.count));
});

// Calculate total impressions for percentage
const totalImpressions = computed(() => {
    return props.deviceBreakdown.reduce((sum, device) => sum + device.count, 0);
});

// Format number
const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num || 0;
};

// Format CTR with null safety
const formatCTR = (ctr) => {
    if (ctr === null || ctr === undefined || isNaN(ctr)) {
        return '0.00';
    }
    return Number(ctr).toFixed(2);
};

// Format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

// Get percentage
const getPercentage = (count) => {
    if (totalImpressions.value === 0) return 0;
    return ((count / totalImpressions.value) * 100).toFixed(1);
};
</script>
