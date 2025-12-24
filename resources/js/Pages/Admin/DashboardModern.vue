<!-- Modern Redesigned Admin Dashboard with New Design System -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    UsersIcon, CurrencyDollarIcon, BriefcaseIcon, 
    WalletIcon, ChartBarIcon, ArrowTrendingUpIcon,
    DocumentTextIcon, ShieldCheckIcon, ClipboardDocumentListIcon,
    ChartPieIcon, CogIcon, BuildingOffice2Icon, PaperAirplaneIcon,
    GlobeAltIcon, UserGroupIcon, RectangleStackIcon,
    ArrowDownTrayIcon, ArrowPathIcon
} from '@heroicons/vue/24/outline';
import PageHeader from '@/Components/Base/PageHeader.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    stats: Object,
    recentUsers: Array,
    recentTransactions: Array,
    recentBookings: Array,
    recentHotelBookings: Array,
    recentVisaApplications: Array,
    userChartData: Array,
    revenueChartData: Array,
    recentImpersonations: Array,
});

const { formatCurrency, formatDate, formatTime } = useBangladeshFormat();

const formatDateTime = (date) => {
    return `${formatDate(date)} ${formatTime(date)}`;
};

const refreshDashboard = () => {
    router.reload({ only: ['stats', 'recentUsers', 'recentTransactions', 'recentBookings'] });
};

const exportData = () => {
    console.log('Export dashboard data');
};
</script>

<template>
    <Head title="Dashboard - BideshGomon Admin" />

    <AdminLayout>
        <!-- PAGE HEADER -->
        <PageHeader
            title="Admin Dashboard"
            description="Monitor and manage your platform with real-time insights"
            :primaryAction="{
                label: 'Refresh Data',
                onClick: refreshDashboard,
                icon: ArrowPathIcon
            }"
            :secondaryActions="[
                { label: 'Export Report', onClick: exportData, icon: ArrowDownTrayIcon }
            ]"
        />

        <!-- MAIN CONTENT -->
        <div class="page-container space-y-6">
            
            <!-- CORE METRICS GRID -->
            <div class="grid-stats">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <UsersIcon class="h-6 w-6 text-growth-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Users</p>
                        <p class="stat-card-value">{{ stats.users.total.toLocaleString() }}</p>
                        <p class="stat-card-change text-green-600">
                            +{{ stats.users.today }} today · {{ stats.users.active }} active
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-emerald-100">
                        <CurrencyDollarIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Revenue</p>
                        <p class="stat-card-value">{{ formatCurrency(stats.revenue.total) }}</p>
                        <p class="stat-card-change text-gray-500">
                            {{ formatCurrency(stats.revenue.this_month) }} this month
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-cyan-100">
                        <ShieldCheckIcon class="h-6 w-6 text-cyan-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Insurance Bookings</p>
                        <p class="stat-card-value">{{ stats.services.insurance_bookings }}</p>
                        <p class="stat-card-change text-green-600">
                            +{{ stats.services.insurance_today }} today
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-indigo-100">
                        <DocumentTextIcon class="h-6 w-6 text-growth-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">CVs Created</p>
                        <p class="stat-card-value">{{ stats.services.cvs_created }}</p>
                        <p class="stat-card-change text-green-600">
                            +{{ stats.services.cvs_today }} today
                        </p>
                    </div>
                </div>
            </div>

            <!-- SERVICES METRICS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card">
                    <div class="stat-card-icon bg-orange-100">
                        <BuildingOffice2Icon class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Hotel Bookings</p>
                        <p class="stat-card-value">{{ stats.services.hotel_bookings }}</p>
                        <p class="stat-card-change text-gray-500">
                            {{ stats.services.confirmed_hotel_bookings }} confirmed
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-sky-100">
                        <PaperAirplaneIcon class="h-6 w-6 text-sky-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Flight Requests</p>
                        <p class="stat-card-value">{{ stats.services.flight_requests }}</p>
                        <p class="stat-card-change text-yellow-600">
                            {{ stats.services.pending_flight_requests }} pending
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <DocumentTextIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Visa Applications</p>
                        <p class="stat-card-value">{{ stats.services.visa_applications }}</p>
                        <p class="stat-card-change text-green-600">
                            {{ stats.services.approved_visa_applications }} approved
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-amber-100">
                        <ClipboardDocumentListIcon class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Support Tickets</p>
                        <p class="stat-card-value">{{ stats.support.total_tickets }}</p>
                        <p class="stat-card-change text-red-600">
                            {{ stats.support.open_tickets }} open · {{ stats.support.urgent_tickets }} urgent
                        </p>
                    </div>
                </div>
            </div>

            <!-- CHARTS ROW -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Registrations Chart -->
                <div class="card-base p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        User Registrations (Last 7 Days)
                    </h3>
                    <div v-if="userChartData && userChartData.length > 0" class="space-y-3">
                        <div 
                            v-for="data in userChartData" 
                            :key="data.date"
                            class="flex items-center gap-3"
                        >
                            <div class="w-20 text-sm text-gray-600">{{ data.date }}</div>
                            <div class="flex-1 bg-gray-100 rounded-full h-10 overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 to-blue-600 h-full flex items-center justify-end pr-3 text-white text-sm font-medium transition-all duration-500"
                                    :style="{ width: `${Math.max((data.count / Math.max(...userChartData.map(d => d.count), 1)) * 100, 10)}%` }"
                                >
                                    {{ data.count }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500">
                        No registration data available
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="card-base p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        Revenue (Last 7 Days)
                    </h3>
                    <div v-if="revenueChartData && revenueChartData.length > 0" class="space-y-3">
                        <div 
                            v-for="data in revenueChartData" 
                            :key="data.date"
                            class="flex items-center gap-3"
                        >
                            <div class="w-20 text-sm text-gray-600">{{ data.date }}</div>
                            <div class="flex-1 bg-gray-100 rounded-full h-10 overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-full flex items-center justify-end pr-3 text-white text-sm font-medium transition-all duration-500"
                                    :style="{ width: `${Math.max((data.amount / Math.max(...revenueChartData.map(d => d.amount), 1)) * 100, 10)}%` }"
                                >
                                    {{ formatCurrency(data.amount) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500">
                        No revenue data available
                    </div>
                </div>
            </div>

            <!-- QUICK ACCESS MANAGEMENT -->
            <div class="card-base p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Quick Access Management</h2>
                
                <!-- Document Hub -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4 flex items-center">
                        <span class="inline-block w-2 h-2 bg-red-600 rounded-full mr-2"></span>
                        Document Hub System (Priority)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link
                            :href="route('admin.master-documents.index')"
                            class="card-hover p-4 flex items-center gap-4 group"
                        >
                            <div class="p-3 bg-growth-600 rounded-lg group-hover:bg-growth-700 transition-colors">
                                <DocumentTextIcon class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Master Documents</p>
                                <p class="text-sm text-gray-600">36 documents library</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.document-categories.index')"
                            class="card-hover p-4 flex items-center gap-4 group"
                        >
                            <div class="p-3 bg-growth-600 rounded-lg group-hover:bg-growth-700 transition-colors">
                                <RectangleStackIcon class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Document Categories</p>
                                <p class="text-sm text-gray-600">8 categories</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.document-assignments.index')"
                            class="card-hover p-4 flex items-center gap-4 group"
                        >
                            <div class="p-3 bg-purple-600 rounded-lg group-hover:bg-purple-700 transition-colors">
                                <GlobeAltIcon class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Country Assignments</p>
                                <p class="text-sm text-gray-600">Document requirements</p>
                            </div>
                        </Link>
                    </div>
                </div>

                <div class="divider-horizontal"></div>

                <!-- Core Services -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Core Services</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Link
                            :href="route('admin.service-modules.index')"
                            class="card-hover p-4 flex items-center gap-4"
                        >
                            <div class="p-3 bg-emerald-100 rounded-lg">
                                <RectangleStackIcon class="h-6 w-6 text-emerald-600" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Service Modules</p>
                                <p class="text-sm text-gray-600">39 services</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.users.index')"
                            class="card-hover p-4 flex items-center gap-4"
                        >
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <UsersIcon class="h-6 w-6 text-purple-600" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">User Management</p>
                                <p class="text-sm text-gray-600">All users</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.analytics.index')"
                            class="card-hover p-4 flex items-center gap-4"
                        >
                            <div class="p-3 bg-green-100 rounded-lg">
                                <ChartPieIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Analytics</p>
                                <p class="text-sm text-gray-600">Insights & reports</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.settings.index')"
                            class="card-hover p-4 flex items-center gap-4"
                        >
                            <div class="p-3 bg-gray-100 rounded-lg">
                                <CogIcon class="h-6 w-6 text-gray-600" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Settings</p>
                                <p class="text-sm text-gray-600">Platform config</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- RECENT ACTIVITIES -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Recent Users -->
                <div class="card-base overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                    </div>
                    <div v-if="recentUsers && recentUsers.length > 0" class="divide-y divide-gray-100">
                        <div 
                            v-for="user in recentUsers" 
                            :key="user.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <p class="font-medium text-gray-900 text-sm">{{ user.name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ user.email }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(user.created_at) }}</p>
                        </div>
                    </div>
                    <div v-else class="px-6 py-12 text-center text-gray-500 text-sm">
                        No recent users
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card-base overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                    </div>
                    <div v-if="recentTransactions && recentTransactions.length > 0" class="divide-y divide-gray-100">
                        <div 
                            v-for="transaction in recentTransactions" 
                            :key="transaction.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-gray-900 text-sm">{{ transaction.wallet?.user?.name || 'Unknown' }}</p>
                                <span 
                                    class="text-sm font-bold"
                                    :class="transaction.transaction_type === 'credit' ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ transaction.transaction_type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ transaction.description }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDateTime(transaction.created_at) }}</p>
                        </div>
                    </div>
                    <div v-else class="px-6 py-12 text-center text-gray-500 text-sm">
                        No recent transactions
                    </div>
                </div>

                <!-- Recent Visa Applications -->
                <div class="card-base overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Visa Applications</h3>
                    </div>
                    <div v-if="recentVisaApplications && recentVisaApplications.length > 0" class="divide-y divide-gray-100">
                        <div 
                            v-for="application in recentVisaApplications" 
                            :key="application.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <p class="font-medium text-gray-900 text-sm">{{ application.user?.name || 'Unknown' }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ application.destination_country }} · {{ application.visa_type }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <StatusBadge :status="application.status" size="sm" />
                                <span class="text-sm font-bold text-purple-600">{{ formatCurrency(application.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-6 py-12 text-center text-gray-500 text-sm">
                        No visa applications yet
                    </div>
                </div>
            </div>

            <!-- ADMIN IMPERSONATIONS -->
            <div class="card-base overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Admin Impersonations</h3>
                    <span class="text-xs text-gray-500">Last 10 sessions</span>
                </div>
                <div v-if="recentImpersonations && recentImpersonations.length" class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Target User</th>
                                <th>Purpose</th>
                                <th>Started</th>
                                <th>Ended</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in recentImpersonations" :key="log.id">
                                <td>
                                    <span class="font-medium text-gray-900" v-if="log.impersonator">{{ log.impersonator.name }}</span>
                                    <span class="text-gray-400" v-else>Unknown</span>
                                </td>
                                <td>
                                    <span class="font-medium text-gray-900" v-if="log.target">{{ log.target.name }}</span>
                                    <span class="text-gray-400" v-else>Unknown</span>
                                </td>
                                <td>
                                    <span class="text-gray-700 truncate max-w-xs block">{{ log.purpose || '—' }}</span>
                                </td>
                                <td class="text-gray-600 text-xs">{{ formatDateTime(log.started_at) }}</td>
                                <td class="text-gray-600 text-xs">
                                    <span v-if="log.ended_at">{{ formatDateTime(log.ended_at) }}</span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td class="text-gray-600">
                                    <span v-if="log.duration_minutes !== null">{{ log.duration_minutes }} min</span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td>
                                    <StatusBadge :status="log.status" size="sm" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-12 text-center text-sm text-gray-500">
                    No impersonation sessions recorded yet.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
