<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import { 
    MagnifyingGlassIcon,
    WalletIcon,
    UserIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    CurrencyDollarIcon,
    BanknotesIcon,
    FunnelIcon,
    XMarkIcon,
    EyeIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import { ref } from 'vue';

const props = defineProps({
    wallets: Object,
    stats: Object,
    filters: Object,
});

const { formatCurrency, formatDate } = useBangladeshFormat();
const loading = ref(false);
const showFilters = ref(false);

const searchForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
});

const hasActiveFilters = () => {
    return searchForm.value.search || searchForm.value.status;
};

const search = () => {
    router.get(route('admin.wallets.index'), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.value = { search: '', status: '' };
    search();
};

const getStatusColor = (status) => {
    return {
        'active': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'suspended': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'closed': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    }[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300';
};

const getStatusIcon = (status) => {
    return {
        'active': CheckCircleIcon,
        'suspended': ExclamationTriangleIcon,
        'closed': XCircleIcon,
    }[status] || CheckCircleIcon;
};

// Keyboard shortcuts
const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts();

// Page-specific shortcuts
const pageShortcuts = [
    { key: '/', description: 'Focus search', action: () => document.querySelector('input[type="search"], input[placeholder*="Search"]')?.focus() },
    { key: 'r', description: 'Refresh page', action: () => router.reload() },
];

registerShortcuts(pageShortcuts);
</script>

<template>
    <Head title="Wallet Management" />

    <AdminLayout>
        <PageSkeleton v-if="loading" type="index" :table-rows="8" :table-columns="5" />
        <div v-else class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Hero Header with Stats -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="walletGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#walletGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <WalletIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">Wallet Management</h1>
                            </div>
                            <p class="text-neutral-300">Manage user wallets and balances</p>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                                <span v-if="hasActiveFilters()" class="ml-1 px-2 py-0.5 bg-emerald-500 text-white text-xs rounded-full">Active</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-2xl">
                                    <WalletIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total Wallets</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.totalWallets || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-2xl">
                                    <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Active</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.activeWallets || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-2xl">
                                    <BanknotesIcon class="h-6 w-6 text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total Balance</p>
                                    <p class="text-2xl font-bold text-white">{{ formatCurrency(stats?.totalBalance || 0) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-500/20 rounded-2xl">
                                    <CurrencyDollarIcon class="h-6 w-6 text-amber-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Avg Balance</p>
                                    <p class="text-2xl font-bold text-white">{{ formatCurrency(stats?.averageBalance || 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Collapsible Filters -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filter Wallets</h3>
                            <button @click="showFilters = false" class="p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors">
                                <XMarkIcon class="h-5 w-5 text-neutral-500" />
                            </button>
                        </div>

                        <form @submit.prevent="search" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" />
                                    <input
                                        v-model="searchForm.search"
                                        type="text"
                                        placeholder="Search by user name or email..."
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status</label>
                                <select
                                    v-model="searchForm.status"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 py-2.5"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </form>

                        <div class="flex gap-3 mt-4">
                            <button
                                @click="search"
                                class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all"
                            >
                                Apply Filters
                            </button>
                            <button
                                @click="clearFilters"
                                class="px-4 py-2.5 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-xl hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </transition>

                <!-- Wallets Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="!wallets?.data || wallets.data.length === 0"
                        icon="CreditCardIcon"
                        title="No wallets found"
                        description="User wallets will appear here once users register on the platform."
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Balance</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Currency</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="wallet in wallets.data" :key="wallet.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">{{ wallet.user?.name?.charAt(0) || 'U' }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ wallet.user?.name }}</div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ wallet.user?.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(wallet.balance) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-neutral-900 dark:text-white">{{ wallet.currency }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="getStatusColor(wallet.status)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1"
                                        >
                                            <component :is="getStatusIcon(wallet.status)" class="h-3 w-3" />
                                            {{ ((wallet.status || '').charAt(0).toUpperCase() || '') + (wallet.status || '').slice(1) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ formatDate(wallet.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <Link 
                                            :href="route('admin.wallets.show', wallet.id)" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-2xl transition-colors"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="wallets?.data && wallets.data.length > 0" class="bg-neutral-50 dark:bg-neutral-900/50 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Showing <span class="font-semibold text-neutral-900 dark:text-white">{{ wallets.from || 0 }}</span>
                                to <span class="font-semibold text-neutral-900 dark:text-white">{{ wallets.to || 0 }}</span>
                                of <span class="font-semibold text-neutral-900 dark:text-white">{{ wallets.total || 0 }}</span> wallets
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="wallets.prev_page_url"
                                    :href="wallets.prev_page_url"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-2xl hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <span v-else class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-2xl cursor-not-allowed">
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </span>
                                <Link
                                    v-if="wallets.next_page_url"
                                    :href="wallets.next_page_url"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-2xl hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                                <span v-else class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-2xl cursor-not-allowed">
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </span>
                            </div>
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
