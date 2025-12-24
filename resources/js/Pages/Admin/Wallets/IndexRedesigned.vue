<template>
    <Head title="Wallets" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Wallet Management</h1>
                <p class="text-neutral-600 mt-2">Monitor and manage user wallets and transactions</p>
            </div>
            <ActionButton
                label="Export Report"
                variant="secondary"
                size="md"
                :icon="ArrowDownTrayIcon"
                @click="exportReport"
            />
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                :icon="CurrencyDollarIcon"
                label="Total Balance"
                :value="formatCurrency(stats.totalBalance)"
                unit=""
                :change="stats.balanceGrowth"
                :description="`৳${formatNumber(stats.todayBalance)} today`"
                variant="success"
            />
            <StatsCard
                :icon="ArrowTrendingUpIcon"
                label="Total Credits"
                :value="formatCurrency(stats.totalCredits)"
                unit=""
                :change="stats.creditGrowth"
                variant="primary"
            />
            <StatsCard
                :icon="ArrowTrendingDownIcon"
                label="Total Debits"
                :value="formatCurrency(stats.totalDebits)"
                unit=""
                :change="stats.debitGrowth"
                variant="warning"
            />
            <StatsCard
                :icon="WalletIcon"
                label="Active Wallets"
                :value="stats.activeWallets"
                :description="`${stats.totalWallets} total`"
                variant="neutral"
            />
        </div>

        <!-- Wallets Table -->
        <TableWrapper
            title="User Wallets"
            description="View and manage user wallet balances"
            :columns="columns"
            :data="wallets.data"
            :pagination="wallets"
            searchable
            filterable
            has-actions
            @search="handleSearch"
            @sort="handleSort"
        >
            <!-- Filters Slot -->
            <template #filters>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Balance Range</label>
                        <select
                            v-model="filters.balanceRange"
                            @change="applyFilters"
                            class="w-full rounded-2xl border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Balances</option>
                            <option value="zero">Zero Balance</option>
                            <option value="positive">Positive Balance</option>
                            <option value="high">High Balance (>৳10,000)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Activity</label>
                        <select
                            v-model="filters.activity"
                            @change="applyFilters"
                            class="w-full rounded-2xl border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All</option>
                            <option value="active">Active (7 days)</option>
                            <option value="inactive">Inactive (30+ days)</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <ActionButton
                            label="Clear Filters"
                            variant="ghost"
                            size="md"
                            @click="clearFilters"
                            full-width
                        />
                    </div>
                </div>
            </template>

            <!-- Custom Cells -->
            <template #cell(user)="{ row }">
                <div class="flex items-center gap-3">
                    <img
                        :src="row.user?.avatar || '/images/default-avatar.png'"
                        :alt="row.user?.name"
                        class="h-10 w-10 rounded-full object-cover"
                    />
                    <div>
                        <p class="font-medium text-neutral-900">{{ row.user?.name }}</p>
                        <p class="text-sm text-neutral-500">{{ row.user?.email }}</p>
                    </div>
                </div>
            </template>

            <template #cell(balance)="{ value }">
                <span :class="[
                    'font-bold',
                    value > 0 ? 'text-success-700' : value < 0 ? 'text-red-700' : 'text-neutral-600'
                ]">
                    {{ formatCurrency(value) }}
                </span>
            </template>

            <template #cell(total_credits)="{ value }">
                <span class="text-success-600 font-medium">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell(total_debits)="{ value }">
                <span class="text-red-600 font-medium">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell(updated_at)="{ value }">
                <span class="text-sm text-neutral-600">{{ formatDate(value) }}</span>
            </template>

            <!-- Row Actions -->
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.wallets.show', row.id)"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                    >
                        View
                    </Link>
                    <button
                        @click="creditWallet(row)"
                        class="text-success-600 hover:text-success-700 text-sm font-medium"
                    >
                        Credit
                    </button>
                    <button
                        @click="debitWallet(row)"
                        class="text-red-600 hover:text-growth-700 text-sm font-medium"
                    >
                        Debit
                    </button>
                </div>
            </template>
        </TableWrapper>
    </DashboardShell>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardShell from '@/Layouts/DashboardShell.vue';
import StatsCard from '@/Components/UI/StatsCard.vue';
import TableWrapper from '@/Components/UI/TableWrapper.vue';
import ActionButton from '@/Components/UI/ActionButton.vue';
import {
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    WalletIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    wallets: Object,
    stats: Object,
    filters: Object
});

const columns = [
    { key: 'user', label: 'User', sortable: false },
    { key: 'balance', label: 'Balance', sortable: true },
    { key: 'total_credits', label: 'Total Credits', sortable: true },
    { key: 'total_debits', label: 'Total Debits', sortable: true },
    { key: 'updated_at', label: 'Last Activity', sortable: true }
];

const filters = ref({
    balanceRange: props.filters?.balanceRange || '',
    activity: props.filters?.activity || ''
});

const handleSearch = (query) => {
    router.get(route('admin.wallets.index'), {
        search: query,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSort = ({ key, order }) => {
    router.get(route('admin.wallets.index'), {
        sort: key,
        order: order,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const applyFilters = () => {
    router.get(route('admin.wallets.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        balanceRange: '',
        activity: ''
    };
    router.get(route('admin.wallets.index'));
};

const creditWallet = (wallet) => {
    const amount = prompt(`Credit amount for ${wallet.user?.name}:`);
    if (!amount || isNaN(amount)) return;

    const note = prompt('Note (optional):');

    if (confirm(`Credit ৳${amount} to ${wallet.user?.name}?`)) {
        router.post(route('admin.wallets.credit', wallet.id), {
            amount: parseFloat(amount),
            note: note
        });
    }
};

const debitWallet = (wallet) => {
    const amount = prompt(`Debit amount for ${wallet.user?.name}:`);
    if (!amount || isNaN(amount)) return;

    const note = prompt('Note (optional):');

    if (confirm(`Debit ৳${amount} from ${wallet.user?.name}?`)) {
        router.post(route('admin.wallets.debit', wallet.id), {
            amount: parseFloat(amount),
            note: note
        });
    }
};

const exportReport = () => {
    window.location.href = route('admin.wallets.export');
};

const formatCurrency = (amount) => {
    return `৳${parseFloat(amount || 0).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
};

const formatNumber = (num) => {
    return parseFloat(num || 0).toLocaleString('en-BD');
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-BD', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>
