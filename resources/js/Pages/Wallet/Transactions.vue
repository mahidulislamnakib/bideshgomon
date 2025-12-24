<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import { ref } from 'vue';
import {
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ClockIcon,
    ChevronLeftIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    XMarkIcon,
    DocumentTextIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const { formatCurrency, formatDate, formatTime } = useBangladeshFormat();

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');

const filterTransactions = () => {
    router.get(route('wallet.transactions'), {
        search: search.value,
        type: type.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    type.value = '';
    filterTransactions();
};

const getTransactionIcon = (transactionType) => {
    return transactionType === 'credit' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon;
};

const getTransactionColor = (transactionType) => {
    return transactionType === 'credit' ? 'text-green-600' : 'text-red-600';
};

const getTransactionBg = (transactionType) => {
    return transactionType === 'credit' ? 'bg-green-100' : 'bg-red-100';
};

const getTransactionSign = (transactionType) => {
    return transactionType === 'credit' ? '+' : '-';
};

const getStatusBadge = (status) => {
    const badges = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Transaction History" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-950">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white">Transaction History</h1>
                            <p class="text-sm text-white/80 mt-0.5">View all your wallet transactions</p>
                        </div>
                        <Link
                            :href="route('wallet.index')"
                            class="px-4 py-2 text-sm font-medium bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors"
                        >
                            Back to Wallet
                        </Link>
                    </div>
                    
                    <!-- Filters in Hero -->
                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <input
                            v-model="search"
                            @keyup.enter="filterTransactions"
                            type="text"
                            placeholder="Search transactions..."
                            class="w-48 md:w-64 px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50 placeholder-gray-500"
                        />
                        <select
                            v-model="type"
                            @change="filterTransactions"
                            class="px-3 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50"
                        >
                            <option value="">All Types</option>
                            <option value="credit">Credit</option>
                            <option value="debit">Debit</option>
                        </select>
                        <button
                            @click="filterTransactions"
                            class="px-4 py-2 text-sm font-medium bg-white text-growth-600 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            Search
                        </button>
                        <button
                            v-if="search || type"
                            @click="clearFilters"
                            class="px-3 py-2 text-sm font-medium text-white/80 hover:text-white transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
                <!-- Results Count -->
                <p class="text-xs text-gray-500 dark:text-gray-400 py-4">{{ transactions?.total || 0 }} transactions found</p>

                <!-- Transactions List -->
                <div v-if="transactions.data.length > 0" class="space-y-3">
                    <!-- Transaction Cards -->
                    <div 
                        v-for="transaction in transactions.data" 
                        :key="transaction.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start sm:items-center gap-4">
                            <!-- Icon -->
                            <div 
                                class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center"
                                :class="getTransactionBg(transaction.transaction_type)"
                            >
                                <component 
                                    :is="getTransactionIcon(transaction.transaction_type)"
                                    class="h-6 w-6"
                                    :class="getTransactionColor(transaction.transaction_type)"
                                />
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-gray-900 truncate">{{ transaction.description }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-500">
                                                {{ formatDate(transaction.created_at) }} • {{ formatTime(transaction.created_at) }}
                                            </span>
                                            <span 
                                                class="px-2 py-0.5 text-xs font-medium rounded-full capitalize"
                                                :class="getStatusBadge(transaction.status)"
                                            >
                                                {{ transaction.status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p 
                                            class="text-lg sm:text-xl font-bold"
                                            :class="getTransactionColor(transaction.transaction_type)"
                                        >
                                            {{ getTransactionSign(transaction.transaction_type) }}{{ formatCurrency(transaction.amount) }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Balance: {{ formatCurrency(transaction.balance_after) }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Reference Type -->
                                <div v-if="transaction.reference_type" class="mt-2 pt-2 border-t border-gray-100">
                                    <span class="text-xs text-gray-500">
                                        Reference: <span class="font-medium capitalize">{{ (transaction?.reference_type || '').replace('_', ' ') }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-gray-600">
                                Showing <span class="font-semibold">{{ transactions.from }}</span> to 
                                <span class="font-semibold">{{ transactions.to }}</span> of 
                                <span class="font-semibold">{{ transactions.total }}</span> transactions
                            </p>
                            <div class="flex gap-2">
                                <Link 
                                    v-if="transactions.prev_page_url" 
                                    :href="transactions.prev_page_url"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                                >
                                    ← Previous
                                </Link>
                                <span v-else class="px-4 py-2 bg-gray-50 text-gray-400 rounded-lg font-medium cursor-not-allowed">
                                    ← Previous
                                </span>
                                <Link 
                                    v-if="transactions.next_page_url" 
                                    :href="transactions.next_page_url"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                                >
                                    Next →
                                </Link>
                                <span v-else class="px-4 py-2 bg-gray-50 text-gray-400 rounded-lg font-medium cursor-not-allowed">
                                    Next →
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 sm:p-16 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                        <DocumentTextIcon class="h-10 w-10 text-indigo-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Transactions Found</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                        {{ search || type ? 'No transactions match your search criteria.' : 'Your transaction history will appear here once you make your first transaction.' }}
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <button 
                            v-if="search || type"
                            @click="clearFilters"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors"
                        >
                            <ArrowPathIcon class="h-5 w-5" />
                            Clear Filters
                        </button>
                        <Link 
                            :href="route('wallet.index')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors"
                        >
                            <ChevronLeftIcon class="h-5 w-5" />
                            Back to Wallet
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
