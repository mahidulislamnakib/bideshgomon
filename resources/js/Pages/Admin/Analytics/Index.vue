<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    ChartBarIcon,
    UsersIcon,
    CurrencyDollarIcon,
    BriefcaseIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ArrowDownTrayIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';
import RhythmicCard from '@/Components/Rhythmic/RhythmicCard.vue';
import AnimatedSection from '@/Components/Rhythmic/AnimatedSection.vue';

const props = defineProps({
    period: String,
    userStats: Object,
    revenueStats: Object,
    jobStats: Object,
    serviceStats: Object,
    userGrowthChart: Array,
    revenueChart: Array,
    topCountries: Array,
    topJobCategories: Array,
    applicationStatusDistribution: Object,
    revenueGrowth: Number,
    userGrowthRate: Number,
    currentMonthRevenue: Number,
    previousMonthRevenue: Number,
});

const selectedPeriod = ref(props.period);

const changePeriod = (period) => {
    selectedPeriod.value = period;
    router.get(route('admin.analytics.index'), { period }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportReport = (type) => {
    window.location.href = route('admin.analytics.export', {
        type,
        period: selectedPeriod.value,
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(amount || 0);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US').format(num || 0);
};

const maxRevenueValue = computed(() => {
    if (!props.revenueChart || props.revenueChart.length === 0) return 0;
    return Math.max(...props.revenueChart.map(d => d.amount || 0), 1);
});

const maxUserValue = computed(() => {
    if (!props.userGrowthChart || props.userGrowthChart.length === 0) return 0;
    return Math.max(...props.userGrowthChart.map(d => d.count || 0), 1);
});

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    reviewed: 'bg-red-100 text-growth-600',
    shortlisted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    hired: 'bg-purple-100 text-purple-800',
};
</script>

<template>
    <Head title="Analytics & Reports" />

    <AdminLayout>
        <!-- Modern Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
            <!-- Animated background pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Gradient orbs -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-primary-500/30 to-primary-600/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-accent-500/30 to-accent-400/20 rounded-full blur-3xl"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                <ChartBarIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-300 uppercase tracking-wider">Analytics</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2">
                            Analytics & Reports
                        </h1>
                        <p class="text-sm sm:text-base text-gray-300">
                            Comprehensive platform insights and metrics with real-time data
                        </p>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 border border-white/20">
                        <CalendarIcon class="h-5 w-5 text-gray-300" />
                        <select
                            v-model="selectedPeriod"
                            @change="changePeriod(selectedPeriod)"
                            class="px-4 py-2 bg-white text-gray-900 dark:text-white rounded-2xl border-0 focus:ring-2 focus:ring-green-500 font-semibold text-sm"
                        >
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <!-- Growth Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <!-- Revenue Growth Card -->
                <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #14a800 0%, #108a00 100%);">
                                    <component
                                        :is="revenueGrowth >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                        class="h-6 w-6 text-white"
                                    />
                                </div>
                                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Revenue Growth</h3>
                            </div>
                            <div class="flex items-baseline gap-2 mb-4">
                                <span
                                    :class="[
                                        'text-3xl md:text-4xl font-bold',
                                        revenueGrowth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
                                    ]"
                                >
                                    {{ revenueGrowth > 0 ? '+' : '' }}{{ revenueGrowth }}%
                                </span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">vs last month</span>
                            </div>
                            <div class="pt-4 border-t border-neutral-100 dark:border-neutral-700 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500 dark:text-neutral-400">Current Month</span>
                                    <span class="font-semibold text-neutral-900 dark:text-white">{{ formatCurrency(currentMonthRevenue) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500 dark:text-neutral-400">Previous Month</span>
                                    <span class="font-semibold text-neutral-900 dark:text-white">{{ formatCurrency(previousMonthRevenue) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Growth Card -->
                <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                    <component
                                        :is="userGrowthRate >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                        class="h-6 w-6 text-white"
                                    />
                                </div>
                                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">User Growth</h3>
                            </div>
                            <div class="flex items-baseline gap-2 mb-4">
                                <span
                                    :class="[
                                        'text-3xl md:text-4xl font-bold',
                                        userGrowthRate >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
                                    ]"
                                >
                                    {{ userGrowthRate > 0 ? '+' : '' }}{{ userGrowthRate }}%
                                </span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">vs last month</span>
                            </div>
                            <div class="pt-4 border-t border-neutral-100 dark:border-neutral-700">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-neutral-500 dark:text-neutral-400">New Users</p>
                                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatNumber(userStats.new_users) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Active Users</p>
                                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatNumber(userStats.active_users) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Total Users -->
                <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-accent-200 dark:hover:border-accent-800 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Users</p>
                            <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ formatNumber(userStats.total_users) }}</p>
                        </div>
                        <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <UsersIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ formatNumber(userStats.verified_users) }}</span> verified
                        </p>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Revenue</p>
                            <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ formatCurrency(revenueStats.total_revenue) }}</p>
                        </div>
                        <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #14a800 0%, #108a00 100%);">
                            <CurrencyDollarIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ formatNumber(revenueStats.total_transactions) }}</span> transactions
                        </p>
                    </div>
                </div>

                <!-- Job Postings -->
                <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-purple-200 dark:hover:border-purple-800 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Job Postings</p>
                            <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ formatNumber(jobStats.total_postings) }}</p>
                        </div>
                        <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                            <BriefcaseIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            <span class="font-semibold text-green-600 dark:text-green-400">{{ formatNumber(jobStats.active_postings) }}</span> active
                        </p>
                    </div>
                </div>

                <!-- Applications -->
                <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-cyan-200 dark:hover:border-cyan-800 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Applications</p>
                            <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ formatNumber(jobStats.total_applications) }}</p>
                        </div>
                        <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                            <ChartBarIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ jobStats.application_rate }}</span> per job
                        </p>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue Chart -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-2xl" style="background-color: rgba(20, 168, 0, 0.1);">
                                <CurrencyDollarIcon class="h-4 w-4" style="color: #14a800;" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Revenue Trend (Last 30 Days)</h3>
                            </div>
                        </div>
                        <button
                            @click="exportReport('revenue')"
                            class="text-sm font-medium flex items-center gap-1 px-3 py-1.5 rounded-2xl hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
                            style="color: #14a800;"
                        >
                            <ArrowDownTrayIcon class="h-4 w-4" />
                            Export
                        </button>
                    </div>
                    <div class="p-5 md:p-6">
                        <div v-if="revenueChart && revenueChart.length > 0" class="relative">
                            <div class="overflow-x-auto">
                                <div class="h-64 flex items-end gap-1 border-l border-b border-neutral-200 dark:border-neutral-600 pl-2 pb-12" :style="{ minWidth: revenueChart.length * 32 + 'px' }">
                                    <div
                                        v-for="(data, index) in revenueChart"
                                        :key="index"
                                        class="flex flex-col items-center group relative"
                                        style="min-width: 28px; flex: 0 0 auto;"
                                    >
                                        <div
                                            :style="{
                                                height: maxRevenueValue > 0 ? `${Math.max((data.amount / maxRevenueValue) * 100, 8)}%` : '8%',
                                                background: 'linear-gradient(180deg, #14a800 0%, #108a00 100%)'
                                            }"
                                            class="w-full rounded-t transition-all relative shadow-sm cursor-pointer hover:opacity-80"
                                        >
                                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-neutral-900 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 shadow-lg">
                                                {{ formatCurrency(data.amount) }}
                                            </div>
                                        </div>
                                        <span class="absolute top-full mt-1 text-[10px] text-neutral-500 dark:text-neutral-400 whitespace-nowrap origin-top-left transform rotate-45" style="left: 50%; transform: rotate(45deg) translateX(-30%);" :title="data.date">{{ data.date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-64 flex items-center justify-center border border-dashed border-neutral-300 dark:border-neutral-600 rounded-2xl bg-neutral-50 dark:bg-neutral-700/50">
                            <div class="text-center">
                                <ChartBarIcon class="h-12 w-12 text-neutral-400 mx-auto mb-2" />
                                <p class="text-neutral-500 dark:text-neutral-400 text-sm">No revenue data available</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Growth Chart -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-2xl" style="background-color: rgba(59, 130, 246, 0.1);">
                                <UsersIcon class="h-4 w-4" style="color: #3b82f6;" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">User Registrations (Last 30 Days)</h3>
                            </div>
                        </div>
                        <button
                            @click="exportReport('users')"
                            class="text-sm font-medium flex items-center gap-1 px-3 py-1.5 rounded-2xl hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
                            style="color: #3b82f6;"
                        >
                            <ArrowDownTrayIcon class="h-4 w-4" />
                            Export
                        </button>
                    </div>
                    <div class="p-5 md:p-6">
                        <div v-if="userGrowthChart && userGrowthChart.length > 0" class="relative">
                            <div class="overflow-x-auto">
                                <div class="h-64 flex items-end gap-1 border-l border-b border-neutral-200 dark:border-neutral-600 pl-2 pb-12" :style="{ minWidth: userGrowthChart.length * 32 + 'px' }">
                                    <div
                                        v-for="(data, index) in userGrowthChart"
                                        :key="index"
                                        class="flex flex-col items-center group relative"
                                        style="min-width: 28px; flex: 0 0 auto;"
                                    >
                                        <div
                                            :style="{
                                                height: maxUserValue > 0 ? `${Math.max((data.count / maxUserValue) * 100, 8)}%` : '8%',
                                                background: 'linear-gradient(180deg, #3b82f6 0%, #2563eb 100%)'
                                            }"
                                            class="w-full rounded-t transition-all relative shadow-sm cursor-pointer hover:opacity-80"
                                        >
                                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-neutral-900 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 shadow-lg">
                                                {{ data.count }} users
                                            </div>
                                        </div>
                                        <span class="absolute top-full mt-1 text-[10px] text-neutral-500 dark:text-neutral-400 whitespace-nowrap origin-top-left transform rotate-45" style="left: 50%; transform: rotate(45deg) translateX(-30%);" :title="data.date">{{ data.date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-64 flex items-center justify-center border border-dashed border-neutral-300 dark:border-neutral-600 rounded-2xl bg-neutral-50 dark:bg-neutral-700/50">
                            <div class="text-center">
                                <UsersIcon class="h-12 w-12 text-neutral-400 mx-auto mb-2" />
                                <p class="text-neutral-500 dark:text-neutral-400 text-sm">No user data available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Insights Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top Countries -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Top Countries</h3>
                    </div>
                    <div class="p-5 md:p-6">
                        <div class="space-y-3">
                            <div
                                v-for="(country, index) in topCountries"
                                :key="index"
                                class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-700 last:border-0"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center text-xs font-bold text-neutral-600 dark:text-neutral-300">{{ index + 1 }}</span>
                                    <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ country.country }}</span>
                                </div>
                                <span class="text-sm font-bold" style="color: #14a800;">{{ formatNumber(country.count) }}</span>
                            </div>
                            <div v-if="!topCountries || topCountries.length === 0" class="text-center py-4 text-neutral-500 dark:text-neutral-400 text-sm">
                                No country data available
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Job Categories -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Top Job Categories</h3>
                        <button
                            @click="exportReport('jobs')"
                            class="p-1.5 rounded-2xl hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
                            style="color: #8b5cf6;"
                        >
                            <ArrowDownTrayIcon class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="p-5 md:p-6">
                        <div class="space-y-3">
                            <div
                                v-for="(category, index) in topJobCategories"
                                :key="index"
                                class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-700 last:border-0"
                            >
                                <span class="text-sm font-medium text-neutral-900 dark:text-white capitalize">{{ category.category }}</span>
                                <span class="text-sm font-bold" style="color: #8b5cf6;">{{ formatNumber(category.job_count) }}</span>
                            </div>
                            <div v-if="!topJobCategories || topJobCategories.length === 0" class="text-center py-4 text-neutral-500 dark:text-neutral-400 text-sm">
                                No category data available
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Status Distribution -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Application Status</h3>
                    </div>
                    <div class="p-5 md:p-6">
                        <div class="space-y-3">
                            <div
                                v-for="(count, status) in applicationStatusDistribution"
                                :key="status"
                                class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-700 last:border-0"
                            >
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-xs font-semibold capitalize',
                                        statusColors[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
                                    ]"
                                >
                                    {{ status }}
                                </span>
                                <span class="text-sm font-bold text-neutral-900 dark:text-white">{{ formatNumber(count) }}</span>
                            </div>
                            <div v-if="!applicationStatusDistribution || Object.keys(applicationStatusDistribution).length === 0" class="text-center py-4 text-neutral-500 dark:text-neutral-400 text-sm">
                                No status data available
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </AdminLayout>
</template>
