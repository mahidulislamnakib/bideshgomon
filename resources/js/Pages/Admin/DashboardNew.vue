<template>
    <Head title="Admin Dashboard" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900">Dashboard</h1>
            <p class="text-neutral-600 mt-2">Welcome back! Here's what's happening with your platform today.</p>
        </div>

        <!-- Key Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                :icon="UsersIcon"
                label="Total Users"
                :value="stats.users.total"
                :change="stats.users.growthRate"
                :description="`${stats.users.newToday} new today`"
                variant="primary"
                :action="{ label: 'View all', href: route('admin.users.index') }"
            />

            <StatsCard
                :icon="CurrencyDollarIcon"
                label="Revenue"
                :value="formatCurrency(stats.revenue.total)"
                unit=""
                :change="stats.revenue.growthRate"
                :description="`${formatCurrency(stats.revenue.thisMonth)} this month`"
                variant="success"
                :action="{ label: 'View details', href: route('admin.wallets.index') }"
            />

            <StatsCard
                :icon="DocumentTextIcon"
                label="Applications"
                :value="stats.applications.total"
                :change="stats.applications.growthRate"
                :description="`${stats.applications.pending} pending approval`"
                variant="warning"
                :action="{ label: 'View all', href: route('admin.visa-applications.index') }"
            />

            <StatsCard
                :icon="BuildingOfficeIcon"
                label="Active Agencies"
                :value="stats.agencies.active"
                :change="stats.agencies.growthRate"
                :description="`${stats.agencies.total} total registered`"
                variant="neutral"
                :action="{ label: 'Manage', href: route('admin.agencies.index') }"
            />
        </div>

        <!-- Main Content Grid (2 columns on desktop) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column (2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Service Usage Chart -->
                <ChartCard
                    title="Service Usage Overview"
                    subtitle="Last 30 days"
                    :height="300"
                    :legend="[
                        { label: 'Visa Applications', color: '#e4282b', value: stats.services.visas },
                        { label: 'Hotel Bookings', color: '#64ac44', value: stats.services.hotels },
                        { label: 'Flight Requests', color: '#3b82f6', value: stats.services.flights }
                    ]"
                    :actions="[
                        { label: 'Download Report', value: 'download' },
                        { label: 'View Details', value: 'details' }
                    ]"
                    @action="handleChartAction"
                >
                    <template #chart>
                        <!-- Placeholder for actual chart library (Chart.js, ApexCharts, etc.) -->
                        <div class="flex items-center justify-center h-full text-neutral-400">
                            <div class="text-center">
                                <ChartBarIcon class="h-16 w-16 mx-auto mb-2" />
                                <p class="text-sm">Chart will be rendered here</p>
                                <p class="text-xs text-neutral-500 mt-1">Integrate Chart.js or ApexCharts</p>
                            </div>
                        </div>
                    </template>
                </ChartCard>

                <!-- Recent Activities -->
                <RecentActivitiesWidget :activities="recentActivities" />
            </div>

            <!-- Right Column (1/3 width) -->
            <div class="space-y-6">
                <!-- User Stats Widget -->
                <UserStatsWidget :stats="userStats" />

                <!-- Quick Actions -->
                <QuickActionsWidget />
            </div>
        </div>

        <!-- Data Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Users -->
            <TableWrapper
                title="Recent Users"
                description="Newly registered users"
                :columns="userColumns"
                :data="recentUsers"
                :searchable="false"
                :filterable="false"
                :selectable="false"
                :has-actions="true"
            >
                <template #cell(status)="{ value }">
                    <span :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        value === 'active' ? 'bg-success-100 text-success-700' : 'bg-neutral-100 text-neutral-700'
                    ]">
                        {{ value }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('admin.users.show', row.id)"
                            class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                        >
                            View
                        </Link>
                    </div>
                </template>
            </TableWrapper>

            <!-- Recent Transactions -->
            <TableWrapper
                title="Recent Transactions"
                description="Latest wallet transactions"
                :columns="transactionColumns"
                :data="recentTransactions"
                :searchable="false"
                :filterable="false"
                :selectable="false"
                :has-actions="false"
            >
                <template #cell(amount)="{ value }">
                    <span class="font-semibold text-neutral-900">{{ formatCurrency(value) }}</span>
                </template>

                <template #cell(type)="{ value }">
                    <span :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        value === 'credit' ? 'bg-success-100 text-success-700' : 'bg-red-100 text-red-700'
                    ]">
                        {{ value }}
                    </span>
                </template>
            </TableWrapper>
        </div>
    </DashboardShell>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardShell from '@/Layouts/DashboardShell.vue';
import StatsCard from '@/Components/UI/StatsCard.vue';
import ChartCard from '@/Components/UI/ChartCard.vue';
import TableWrapper from '@/Components/UI/TableWrapper.vue';
import UserStatsWidget from '@/Components/Widgets/UserStatsWidget.vue';
import QuickActionsWidget from '@/Components/Widgets/QuickActionsWidget.vue';
import RecentActivitiesWidget from '@/Components/Widgets/RecentActivitiesWidget.vue';
import {
    UsersIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    BuildingOfficeIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            users: { total: 0, newToday: 0, growthRate: 0 },
            revenue: { total: 0, thisMonth: 0, growthRate: 0 },
            applications: { total: 0, pending: 0, growthRate: 0 },
            agencies: { active: 0, total: 0, growthRate: 0 },
            services: { visas: 0, hotels: 0, flights: 0 }
        })
    },
    recentUsers: {
        type: Array,
        default: () => []
    },
    recentTransactions: {
        type: Array,
        default: () => []
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    userStats: {
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

// Table columns
const userColumns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'created_at', label: 'Joined', sortable: true }
];

const transactionColumns = [
    { key: 'user', label: 'User', sortable: false },
    { key: 'type', label: 'Type', sortable: false },
    { key: 'amount', label: 'Amount', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true }
];

// Helpers
const formatCurrency = (amount) => {
    return `৳${parseFloat(amount).toLocaleString('en-BD', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    })}`;
};

const handleChartAction = (action) => {
    console.log('Chart action:', action);
    // Implement download or details view
};
</script>
