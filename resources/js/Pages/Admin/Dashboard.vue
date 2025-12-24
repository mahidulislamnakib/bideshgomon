<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import AnimatedStat from '@/Components/ui/AnimatedStat.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { 
    UsersIcon, CurrencyDollarIcon, BriefcaseIcon, 
    WalletIcon, ChartBarIcon, ArrowTrendingUpIcon,
    DocumentTextIcon, ShieldCheckIcon, ClipboardDocumentListIcon,
    ChartPieIcon, CogIcon, BuildingOffice2Icon, PaperAirplaneIcon,
    GlobeAltIcon, UserGroupIcon, RectangleStackIcon, ArrowUpIcon,
    ArrowDownIcon, CalendarDaysIcon, ClockIcon, BellAlertIcon,
    ExclamationTriangleIcon, CheckCircleIcon, EyeIcon, SparklesIcon,
    ArrowRightIcon, FireIcon, ArrowPathIcon, Bars3BottomLeftIcon,
    TicketIcon, AcademicCapIcon, HeartIcon, StarIcon,
    MagnifyingGlassIcon, FunnelIcon, XMarkIcon
} from '@heroicons/vue/24/outline';
import { 
    UsersIcon as UsersIconSolid,
    ChartBarIcon as ChartBarIconSolid 
} from '@heroicons/vue/24/solid';

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

const loading = ref(false);

// Current time
const currentTime = ref(new Date());
onMounted(() => {
    setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

const greeting = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 18) return 'Good Afternoon';
    return 'Good Evening';
});

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('en-US', { 
        weekday: 'long',
        month: 'long', 
        day: 'numeric',
        year: 'numeric'
    });
});

const formatCurrency = (amount) => {
    return `৳${parseFloat(amount || 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
};

const formatCurrencyFull = (amount) => {
    return `৳${parseFloat(amount || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const formatTimeAgo = (date) => {
    const now = new Date();
    const past = new Date(date);
    const diff = Math.floor((now - past) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
};

// Calculate max value for charts
const maxUserCount = computed(() => {
    if (!props.userChartData?.length) return 1;
    return Math.max(...props.userChartData.map(d => d.count), 1);
});

const maxRevenueAmount = computed(() => {
    if (!props.revenueChartData?.length) return 1;
    return Math.max(...props.revenueChartData.map(d => d.amount), 1);
});

// Alerts calculation
const alerts = computed(() => {
    const items = [];
    if (props.stats?.support?.urgent_tickets > 0) {
        items.push({ type: 'urgent', message: `${props.stats.support.urgent_tickets} urgent tickets need attention`, icon: ExclamationTriangleIcon });
    }
    if (props.stats?.services?.pending_flight_requests > 5) {
        items.push({ type: 'warning', message: `${props.stats.services.pending_flight_requests} pending flight requests`, icon: PaperAirplaneIcon });
    }
    if (props.stats?.appointments?.pending_appointments > 0) {
        items.push({ type: 'info', message: `${props.stats.appointments.pending_appointments} appointments awaiting confirmation`, icon: CalendarDaysIcon });
    }
    return items;
});

// Quick stats for mini cards
const quickStats = computed(() => [
    { label: 'Open Tickets', value: props.stats?.support?.open_tickets || 0, color: 'amber' },
    { label: 'Pending Appointments', value: props.stats?.appointments?.pending_appointments || 0, color: 'blue' },
    { label: 'Active Campaigns', value: props.stats?.campaigns?.active_campaigns || 0, color: 'purple' },
    { label: 'High Risk Profiles', value: props.stats?.assessments?.high_risk_profiles || 0, color: 'red' },
]);

// Quick links organized by category
const quickLinks = [
    { 
        category: 'Document Hub', 
        color: 'emerald',
        links: [
            { name: 'Master Documents', route: 'admin.master-documents.index', icon: DocumentTextIcon, count: '36' },
            { name: 'Document Categories', route: 'admin.document-categories.index', icon: RectangleStackIcon, count: '8' },
            { name: 'Country Assignments', route: 'admin.document-assignments.index', icon: GlobeAltIcon },
        ]
    },
    { 
        category: 'Travel Services', 
        color: 'blue',
        links: [
            { name: 'Visa Applications', route: 'admin.visa-applications.index', icon: DocumentTextIcon, badge: props.stats?.services?.pending_visa_applications },
            { name: 'Flight Requests', route: 'admin.flight-requests.index', icon: PaperAirplaneIcon, badge: props.stats?.services?.pending_flight_requests },
            { name: 'Hotel Bookings', route: 'admin.hotel-bookings.index', icon: BuildingOffice2Icon },
            { name: 'Hotels', route: 'admin.hotels.index', icon: BuildingOffice2Icon },
        ]
    },
    { 
        category: 'Employment', 
        color: 'rose',
        links: [
            { name: 'Job Postings', route: 'admin.jobs.index', icon: BriefcaseIcon },
            { name: 'Job Applications', route: 'admin.applications.index', icon: ClipboardDocumentListIcon, badge: props.stats?.jobs?.pending_applications },
        ]
    },
    { 
        category: 'System', 
        color: 'gray',
        links: [
            { name: 'Users', route: 'admin.users.index', icon: UsersIcon },
            { name: 'Service Modules', route: 'admin.service-modules.index', icon: RectangleStackIcon },
            { name: 'Analytics', route: 'admin.analytics.index', icon: ChartPieIcon },
            { name: 'Settings', route: 'admin.settings.index', icon: CogIcon },
        ]
    },
];

// Keyboard shortcuts
const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts()

// Page-specific shortcuts
const pageShortcuts = [
    { key: 'r', description: 'Refresh dashboard', action: () => router.reload() },
]

registerShortcuts(pageShortcuts)
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <PageSkeleton v-if="loading" type="dashboard" :stats-count="4" />
        <div v-else class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Modern Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated background pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>
                
                <!-- Gradient orbs -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-primary-500/30 to-primary-600/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-growth-500/30 to-growth-400/20 rounded-full blur-3xl"></div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Left: Greeting -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <ChartBarIconSolid class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-sm font-medium text-gray-300 uppercase tracking-wider">Admin Dashboard</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2">{{ greeting }}, Admin</h1>
                            <p class="text-sm sm:text-base text-gray-300">Here's what's happening with your platform today.</p>
                        </div>
                        
                        <!-- Right: Time & Quick Stats -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 flex-shrink-0">
                            <!-- Live Time -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 border border-white/20 min-w-[180px]">
                                <div class="flex items-center gap-3">
                                    <ClockIcon class="h-5 w-5 text-gray-300 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xl sm:text-2xl font-bold text-white tabular-nums whitespace-nowrap">{{ formattedTime }}</p>
                                        <p class="text-xs sm:text-sm text-gray-300 truncate">{{ formattedDate }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Today's Highlight -->
                            <div class="bg-green-500/20 backdrop-blur-sm rounded-2xl px-4 py-3 border border-green-400/30 min-w-[160px]">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-500/30 rounded-2xl flex-shrink-0">
                                        <ArrowTrendingUpIcon class="h-5 w-5 text-green-300" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xl sm:text-2xl font-bold text-white tabular-nums whitespace-nowrap">{{ formatCurrency(stats?.revenue?.today || 0) }}</p>
                                        <p class="text-xs sm:text-sm text-green-300">Today's Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alerts Banner -->
                    <div v-if="alerts.length > 0" class="mt-6 flex flex-wrap gap-3">
                        <div 
                            v-for="(alert, index) in alerts" 
                            :key="index"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm border',
                                alert.type === 'urgent' ? 'bg-red-500/20 text-red-200 border-red-400/30' :
                                alert.type === 'warning' ? 'bg-amber-500/20 text-amber-200 border-amber-400/30' :
                                'bg-blue-500/20 text-blue-200 border-blue-400/30'
                            ]"
                        >
                            <component :is="alert.icon" class="h-4 w-4" />
                            {{ alert.message }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                <!-- Primary Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    <!-- Total Users -->
                    <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-accent-200 dark:hover:border-accent-800 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Users</p>
                                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.users?.total || 0" :duration="1200" />
                                </p>
                                <div class="mt-2 flex items-center gap-1">
                                    <span class="inline-flex items-center gap-0.5 text-sm font-semibold text-success-600 dark:text-success-400">
                                        <ArrowUpIcon class="h-3 w-3" />
                                        +{{ stats?.users?.today || 0 }}
                                    </span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">today</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                <UsersIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ stats?.users?.active || 0 }}</span> active this month
                            </p>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Revenue</p>
                                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.revenue?.total || 0" prefix="৳" :duration="1500" />
                                </p>
                                <div class="mt-2 flex items-center gap-1">
                                    <span class="inline-flex items-center gap-0.5 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                        <ArrowUpIcon class="h-3 w-3" />
                                        {{ formatCurrency(stats?.revenue?.today || 0) }}
                                    </span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">today</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #14a800 0%, #108a00 100%);">
                                <CurrencyDollarIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ formatCurrency(stats?.revenue?.this_month || 0) }}</span> this month
                            </p>
                        </div>
                    </div>

                    <!-- Visa Applications -->
                    <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-info-200 dark:hover:border-info-800 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Visa Applications</p>
                                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.services?.visa_applications || 0" :duration="1200" :delay="200" />
                                </p>
                                <div class="mt-2 flex items-center gap-1">
                                    <span class="inline-flex items-center gap-0.5 text-sm font-semibold text-success-600 dark:text-success-400">
                                        <ArrowUpIcon class="h-3 w-3" />
                                        +{{ stats?.services?.visa_applications_today || 0 }}
                                    </span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">today</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                <DocumentTextIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold text-success-600 dark:text-success-400">{{ stats?.services?.approved_visa_applications || 0 }}</span> approved
                            </p>
                        </div>
                    </div>

                    <!-- Wallet Balance -->
                    <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-warning-200 dark:hover:border-warning-800 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Wallet Balance</p>
                                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.wallet?.total_balance || 0" prefix="৳" :duration="1500" :delay="300" />
                                </p>
                                <div class="mt-2 flex items-center gap-1">
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ (stats?.wallet?.total_transactions || 0).toLocaleString() }} transactions</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <WalletIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold text-warning-600 dark:text-warning-400">{{ stats?.wallet?.pending_withdrawals || 0 }}</span> pending withdrawals
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Secondary Stats Row -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4">
                    <!-- Hotel Bookings -->
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-2xl" style="background-color: #fef3c7;">
                                <BuildingOffice2Icon class="h-5 w-5" style="color: #d97706;" />
                            </div>
                            <div>
                                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.services?.hotel_bookings || 0" :duration="1000" :delay="400" />
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Hotels</p>
                            </div>
                        </div>
                    </div>

                    <!-- Flight Requests -->
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-2xl" style="background-color: #dbeafe;">
                                <PaperAirplaneIcon class="h-5 w-5" style="color: #2563eb;" />
                            </div>
                            <div>
                                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.services?.flight_requests || 0" :duration="1000" :delay="500" />
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Flights</p>
                            </div>
                        </div>
                    </div>

                    <!-- Insurance -->
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-2xl" style="background-color: #d1fae5;">
                                <ShieldCheckIcon class="h-5 w-5" style="color: #059669;" />
                            </div>
                            <div>
                                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.services?.insurance_bookings || 0" :duration="1000" :delay="600" />
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Insurance</p>
                            </div>
                        </div>
                    </div>

                    <!-- CVs Created -->
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-2xl" style="background-color: #dbeafe;">
                                <DocumentTextIcon class="h-5 w-5" style="color: #2563eb;" />
                            </div>
                            <div>
                                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.services?.cvs_created || 0" :duration="1000" :delay="700" />
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">CVs</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support Tickets -->
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-2xl" style="background-color: #fee2e2;">
                                <TicketIcon class="h-5 w-5" style="color: #dc2626;" />
                            </div>
                            <div>
                                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.support?.total_tickets || 0" :duration="1000" :delay="800" />
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Tickets</p>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments -->
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-2xl" style="background-color: #dcfce7;">
                                <CalendarDaysIcon class="h-5 w-5" style="color: #16a34a;" />
                            </div>
                            <div>
                                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                                    <AnimatedStat :value="stats?.appointments?.total_appointments || 0" :duration="1000" :delay="900" />
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Appointments</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts & Activities Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- User Registrations Chart -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-2xl" style="background-color: #dbeafe;">
                                    <UsersIcon class="h-4 w-4" style="color: #2563eb;" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">User Registrations</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Last 7 days</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 md:p-6">
                            <div v-if="userChartData && userChartData.length > 0" class="space-y-3">
                                <div 
                                    v-for="data in userChartData" 
                                    :key="data.date"
                                    class="group"
                                >
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-neutral-600 dark:text-neutral-400">{{ data.date }}</span>
                                        <span class="font-semibold text-neutral-900 dark:text-white tabular-nums">{{ data.count }}</span>
                                    </div>
                                    <div class="h-2 bg-neutral-100 dark:bg-neutral-700 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-gradient-to-r from-accent-500 to-accent-600 rounded-full transition-all duration-700 ease-out"
                                            :style="{ width: `${(data.count / maxUserCount) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                                <UsersIcon class="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p class="text-sm">No registration data available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Chart -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-2xl" style="background-color: #dcfce7;">
                                    <CurrencyDollarIcon class="h-4 w-4" style="color: #14a800;" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Revenue</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Last 7 days</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-primary-600 dark:text-primary-400 tabular-nums">{{ formatCurrency(stats?.revenue?.this_month || 0) }}</span>
                        </div>
                        <div class="p-5 md:p-6">
                            <div v-if="revenueChartData && revenueChartData.length > 0" class="space-y-3">
                                <div 
                                    v-for="data in revenueChartData" 
                                    :key="data.date"
                                    class="group"
                                >
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-neutral-600 dark:text-neutral-400">{{ data.date }}</span>
                                        <span class="font-semibold text-neutral-900 dark:text-white tabular-nums">{{ formatCurrency(data.amount) }}</span>
                                    </div>
                                    <div class="h-2 bg-neutral-100 dark:bg-neutral-700 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-700 ease-out"
                                            :style="{ width: `${(data.amount / maxRevenueAmount) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                                <CurrencyDollarIcon class="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p class="text-sm">No revenue data available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats & Metrics -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 md:px-6 py-3 border-b border-neutral-100 dark:border-neutral-700">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-2xl" style="background-color: #d1fae5;">
                                    <ChartPieIcon class="h-4 w-4" style="color: #059669;" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Quick Metrics</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Platform overview</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 md:p-6 grid grid-cols-2 gap-3 md:gap-4">
                            <!-- Visa Revenue -->
                            <div class="bg-gradient-to-br from-info-50 to-info-100 dark:from-info-900/20 dark:to-info-800/20 rounded-xl p-3 md:p-4">
                                <p class="text-xs font-semibold text-info-600 dark:text-info-400 uppercase tracking-wide">Visa Revenue</p>
                                <p class="text-lg md:text-xl font-bold text-info-900 dark:text-info-100 mt-1 tabular-nums">{{ formatCurrency(stats?.services?.visa_revenue_month || 0) }}</p>
                                <p class="text-xs text-info-600/70 dark:text-info-300/70 mt-1">This month</p>
                            </div>
                            
                            <!-- Hotel Revenue -->
                            <div class="bg-gradient-to-br from-warning-50 to-warning-100 dark:from-warning-900/20 dark:to-warning-800/20 rounded-xl p-3 md:p-4">
                                <p class="text-xs font-semibold text-warning-600 dark:text-warning-400 uppercase tracking-wide">Hotel Revenue</p>
                                <p class="text-lg md:text-xl font-bold text-warning-900 dark:text-warning-100 mt-1 tabular-nums">{{ formatCurrency(stats?.services?.hotel_revenue_month || 0) }}</p>
                                <p class="text-xs text-warning-600/70 dark:text-warning-300/70 mt-1">This month</p>
                            </div>
                            
                            <!-- Assessments -->
                            <div class="bg-gradient-to-br from-accent-50 to-accent-100 dark:from-accent-900/20 dark:to-accent-800/20 rounded-xl p-3 md:p-4">
                                <p class="text-xs font-semibold text-accent-600 dark:text-accent-400 uppercase tracking-wide">Avg Assessment</p>
                                <p class="text-lg md:text-xl font-bold text-accent-900 dark:text-accent-100 mt-1 tabular-nums">{{ stats?.assessments?.average_score || 0 }}%</p>
                                <p class="text-xs text-accent-600/70 dark:text-accent-300/70 mt-1">{{ stats?.assessments?.total_assessments || 0 }} total</p>
                            </div>
                            
                            <!-- Public Profiles -->
                            <div class="bg-gradient-to-br from-growth-50 to-growth-100 dark:from-growth-900/20 dark:to-growth-800/20 rounded-xl p-3 md:p-4">
                                <p class="text-xs font-semibold text-growth-600 dark:text-growth-400 uppercase tracking-wide">Public Profiles</p>
                                <p class="text-lg md:text-xl font-bold text-growth-900 dark:text-growth-100 mt-1 tabular-nums">{{ stats?.public_profiles?.total_public || 0 }}</p>
                                <p class="text-xs text-growth-600/70 dark:text-growth-300/70 mt-1">{{ stats?.public_profiles?.total_views || 0 }} views</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Access Links -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-neutral-100 dark:bg-neutral-700 rounded-2xl">
                                    <Bars3BottomLeftIcon class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <h3 class="font-semibold text-neutral-900 dark:text-white">Quick Access</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 md:p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="section in quickLinks" :key="section.category" class="space-y-3">
                            <h4 :class="[
                                'text-xs font-semibold uppercase tracking-wider',
                                section.color === 'emerald' ? 'text-primary-600 dark:text-primary-400' :
                                section.color === 'blue' ? 'text-info-600 dark:text-info-400' :
                                section.color === 'rose' ? 'text-error-600 dark:text-error-400' :
                                'text-neutral-600 dark:text-neutral-400'
                            ]">{{ section.category }}</h4>
                            <div class="space-y-1">
                                <Link
                                    v-for="link in section.links"
                                    :key="link.name"
                                    :href="route(link.route)"
                                    class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors group"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <component :is="link.icon" class="h-4 w-4 text-neutral-400 group-hover:text-neutral-600 dark:group-hover:text-neutral-300" />
                                        <span class="text-sm text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-white">{{ link.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span v-if="link.badge" class="px-2 py-0.5 text-xs font-semibold bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400 rounded-full">{{ link.badge }}</span>
                                        <span v-else-if="link.count" class="text-xs text-neutral-400">{{ link.count }}</span>
                                        <ArrowRightIcon class="h-3 w-3 text-neutral-300 group-hover:text-neutral-500 dark:group-hover:text-neutral-400 opacity-0 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
                    <!-- Recent Users -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 py-3 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <UsersIcon class="h-3.5 w-3.5" style="color: #3b82f6;" />
                                Recent Users
                            </h3>
                            <Link :href="route('admin.users.index')" class="text-xs font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400">View all</Link>
                        </div>
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-700 max-h-80 overflow-y-auto">
                            <div 
                                v-for="user in recentUsers?.slice(0, 5)" 
                                :key="user.id"
                                class="px-5 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-accent-500 to-primary-500 flex items-center justify-center text-white text-xs font-semibold">
                                        {{ user.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ user.name }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ user.email }}</p>
                                    </div>
                                    <span class="text-xs text-neutral-400">{{ formatTimeAgo(user.created_at) }}</span>
                                </div>
                            </div>
                            <div v-if="!recentUsers?.length" class="px-5 py-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                                No recent users
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 py-3 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <WalletIcon class="h-3.5 w-3.5" style="color: #14a800;" />
                                Transactions
                            </h3>
                            <Link :href="route('admin.wallets.index')" class="text-xs font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400">View all</Link>
                        </div>
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-700 max-h-80 overflow-y-auto">
                            <div 
                                v-for="tx in recentTransactions?.slice(0, 5)" 
                                :key="tx.id"
                                class="px-5 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ tx.wallet?.user?.name || 'Unknown' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ tx.description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p :class="[
                                            'text-sm font-semibold tabular-nums',
                                            tx.transaction_type === 'credit' ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'
                                        ]">
                                            {{ tx.transaction_type === 'credit' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                        </p>
                                        <p class="text-xs text-neutral-400">{{ formatTimeAgo(tx.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!recentTransactions?.length" class="px-5 py-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                                No transactions
                            </div>
                        </div>
                    </div>

                    <!-- Recent Hotel Bookings -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 py-3 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <BuildingOffice2Icon class="h-3.5 w-3.5" style="color: #f59e0b;" />
                                Hotel Bookings
                            </h3>
                            <Link :href="route('admin.hotel-bookings.index')" class="text-xs font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400">View all</Link>
                        </div>
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-700 max-h-80 overflow-y-auto">
                            <div 
                                v-for="booking in recentHotelBookings?.slice(0, 5)" 
                                :key="booking.id"
                                class="px-5 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ booking.user?.name || 'Unknown' }}</p>
                                    <span :class="[
                                        'px-2 py-0.5 text-xs font-semibold rounded-full',
                                        booking.status === 'confirmed' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' :
                                        booking.status === 'pending' ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' :
                                        booking.status === 'cancelled' ? 'bg-error-100 text-error-700 dark:bg-error-900/30 dark:text-error-400' :
                                        'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-400'
                                    ]">{{ booking.status }}</span>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ booking.hotel?.name || 'Hotel' }}</p>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-xs text-neutral-400">{{ booking.booking_reference }}</span>
                                    <span class="text-sm font-semibold text-warning-600 dark:text-warning-400 tabular-nums">{{ formatCurrency(booking.total_amount) }}</span>
                                </div>
                            </div>
                            <div v-if="!recentHotelBookings?.length" class="px-5 py-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                                No hotel bookings
                            </div>
                        </div>
                    </div>

                    <!-- Recent Visa Applications -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                        <div class="px-5 py-3 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <DocumentTextIcon class="h-3.5 w-3.5" style="color: #3b82f6;" />
                                Visa Applications
                            </h3>
                            <Link :href="route('admin.visa-applications.index')" class="text-xs font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400">View all</Link>
                        </div>
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-700 max-h-80 overflow-y-auto">
                            <div 
                                v-for="app in recentVisaApplications?.slice(0, 5)" 
                                :key="app.id"
                                class="px-5 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ app.user?.name || 'Unknown' }}</p>
                                    <span :class="[
                                        'px-2 py-0.5 text-xs font-semibold rounded-full',
                                        app.status === 'approved' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' :
                                        app.status === 'submitted' ? 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400' :
                                        app.status === 'under_review' ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' :
                                        app.status === 'rejected' ? 'bg-error-100 text-error-700 dark:bg-error-900/30 dark:text-error-400' :
                                        'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-400'
                                    ]">{{ app.status?.replace(/_/g, ' ') }}</span>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ app.destination_country }} · {{ app.visa_type }}</p>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-xs text-neutral-400">{{ app.application_reference }}</span>
                                    <span class="text-sm font-semibold text-info-600 dark:text-info-400 tabular-nums">{{ formatCurrency(app.total_amount) }}</span>
                                </div>
                            </div>
                            <div v-if="!recentVisaApplications?.length" class="px-5 py-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                                No visa applications
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Impersonation Audit Log -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-2xl" style="background-color: #fee2e2;">
                                <ShieldCheckIcon class="h-4 w-4" style="color: #dc2626;" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Admin Impersonation Audit</h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Security monitoring - Last 10 sessions</p>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table v-if="recentImpersonations?.length" class="min-w-full">
                            <thead class="bg-neutral-50 dark:bg-neutral-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Admin</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Target User</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Purpose</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Started</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Duration</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="log in recentImpersonations" :key="log.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/30">
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-neutral-900 dark:text-white text-sm" v-if="log.impersonator">{{ log.impersonator.name }}</span>
                                        <span class="text-neutral-400 text-sm" v-else>Unknown</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-neutral-900 dark:text-white text-sm" v-if="log.target">{{ log.target.name }}</span>
                                        <span class="text-neutral-400 text-sm" v-else>Unknown</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 max-w-xs truncate">{{ log.purpose || '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{{ formatDateTime(log.started_at) }}</td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span v-if="log.duration_minutes !== null">{{ log.duration_minutes }} min</span>
                                        <span v-else class="text-neutral-400">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                            log.status === 'ended' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' :
                                            'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                        ]">{{ log.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                            <ShieldCheckIcon class="h-12 w-12 mx-auto mb-3 opacity-30" />
                            <p class="text-sm">No impersonation sessions recorded</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts Modal -->
        <KeyboardShortcutsModal
            v-model:show="showHelp"
            :shortcuts="pageShortcuts"
            :global-shortcuts="globalShortcuts"
        />
    </AdminLayout>
</template>
