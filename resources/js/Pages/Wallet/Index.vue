<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import RhythmicCard from '@/Components/Rhythmic/RhythmicCard.vue';
import StatusBadge from '@/Components/Rhythmic/StatusBadge.vue';
import FlowButton from '@/Components/Rhythmic/FlowButton.vue';
import AnimatedSection from '@/Components/Rhythmic/AnimatedSection.vue';
import PaymentGatewaySelector from '@/Components/PaymentGatewaySelector.vue';
import {
    PlusCircleIcon,
    MinusCircleIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
    ChartBarIcon,
    DocumentArrowDownIcon,
    ChevronLeftIcon,
    TrophyIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    wallet: Object,
    balance: [Number, String],
    formattedBalance: String,
    recentTransactions: Array,
    totalIn: [Number, String],
    totalOut: [Number, String],
    transactionCount: Number,
    topReferrers: Array,
    userRank: Object,
});

const { formatCurrency, formatDate, formatTime } = useBangladeshFormat();

const showAddFunds = ref(false);
const showWithdraw = ref(false);
const agreeToTerms = ref(false);

const addFundsForm = useForm({
    amount: '',
    gateway: null,
});

const withdrawForm = useForm({
    amount: '',
    account_number: '',
    account_type: 'Bank',
});

const getTransactionColor = (type) => {
    return type === 'credit' ? 'text-green-600' : 'text-red-600';
};

const getTransactionSign = (type) => {
    return type === 'credit' ? '+' : '-';
};

const getTransactionIcon = (type) => {
    return type === 'credit' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon;
};

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const submitAddFunds = () => {
    addFundsForm.post(route('wallet.add-funds'), {
        onSuccess: () => {
            addFundsForm.reset();
            showAddFunds.value = false;
        },
    });
};

const submitWithdraw = () => {
    withdrawForm.post(route('wallet.withdraw'), {
        onSuccess: () => {
            withdrawForm.reset();
            showWithdraw.value = false;
        },
    });
};
</script>

<template>
    <Head title="My Wallet" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-950">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white">My Wallet</h1>
                            <p class="text-sm text-white/80 mt-0.5">Manage your balance and transactions</p>
                        </div>
                        <Link
                            :href="route('wallet.transactions')"
                            class="px-4 py-2 text-sm font-medium bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors"
                        >
                            View All Transactions
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-4">
                <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 border border-gray-200 dark:border-neutral-700 shadow-sm">
                    <!-- Status Badge -->
                    <div class="flex justify-between items-start mb-4">
                        <StatusBadge :status="wallet.status === 'active' ? 'active' : 'inactive'" />
                        <span class="text-xs text-gray-500 dark:text-gray-400">#{{ wallet.id }}</span>
                    </div>

                    <!-- Balance Display -->
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Available Balance</p>
                        <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-1">{{ formattedBalance }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Bangladeshi Taka (BDT)</p>
                        
                        <!-- Zero Balance Hint -->
                        <div v-if="balance === 0 || balance === '0'" class="mt-4 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <p class="text-xs text-amber-800 dark:text-amber-300">
                                💡 <span class="font-semibold">Get Started:</span> Add funds to your wallet to start using our services
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-3 mt-6">
                        <FlowButton
                            @click="showAddFunds = true"
                            variant="primary"
                            size="lg"
                            class="w-full"
                        >
                            <template #icon-left>
                                <PlusCircleIcon class="h-5 w-5" />
                            </template>
                            Add Funds
                        </FlowButton>
                        <FlowButton
                            @click="showWithdraw = true"
                            variant="secondary"
                            size="lg"
                            class="w-full"
                        >
                            <template #icon-left>
                                <MinusCircleIcon class="h-5 w-5" />
                            </template>
                            Withdraw
                        </FlowButton>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700">
                        <ArrowTrendingUpIcon class="h-5 w-5 text-green-600 mb-2" />
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total In</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalIn || 0) }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700">
                        <ArrowTrendingDownIcon class="h-5 w-5 text-red-600 mb-2" />
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Out</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalOut || 0) }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700">
                        <ChartBarIcon class="h-5 w-5 text-growth-600 mb-2" />
                        <p class="text-xs text-gray-500 dark:text-gray-400">Transactions</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ transactionCount || 0 }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Bar -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 mb-6">
                <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <Link 
                            :href="route('wallet.transactions')"
                            class="flex items-center justify-center gap-2 py-2 px-3 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <ClockIcon class="h-4 w-4" />
                            History
                        </Link>
                        <Link 
                            :href="route('referral.index')"
                            class="flex items-center justify-center gap-2 py-2 px-3 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <TrophyIcon class="h-4 w-4" />
                            Leaderboard
                        </Link>
                        <button 
                            @click="$inertia.reload({ only: ['wallet', 'recentTransactions'] })"
                            class="flex items-center justify-center gap-2 py-2 px-3 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <ArrowPathIcon class="h-4 w-4" />
                            Refresh
                        </button>
                        <button 
                            class="flex items-center justify-center gap-2 py-2 px-3 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Section -->
            <div v-if="topReferrers && topReferrers.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 mb-6">
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-growth-600 to-teal-600 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <TrophyIcon class="h-5 w-5 text-white" />
                                <div>
                                    <h2 class="text-sm font-semibold text-white">Top Referrers</h2>
                                    <p class="text-xs text-white/70">This month's leaderboard</p>
                                </div>
                            </div>
                            <Link 
                                :href="route('referral.index')"
                                class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-medium rounded-lg transition-colors"
                            >
                                View All
                            </Link>
                        </div>
                    </div>
                    <!-- Leaderboard List -->
                    <div class="p-4">
                        <div class="space-y-2">
                            <div 
                                v-for="(referrer, index) in topReferrers.slice(0, 5)" 
                                :key="referrer.id" 
                                class="flex items-center justify-between p-3 rounded-lg transition-colors"
                                :class="userRank && userRank.user_id === referrer.id ? 'bg-growth-50 dark:bg-growth-900/20 border border-growth-200 dark:border-growth-800' : 'bg-gray-50 dark:bg-neutral-700 hover:bg-gray-100 dark:hover:bg-neutral-600'"
                            >
                                <div class="flex items-center gap-3">
                                    <!-- Rank Badge -->
                                    <div class="w-6 h-6 flex items-center justify-center">
                                        <span v-if="index === 0" class="text-lg">🥇</span>
                                        <span v-else-if="index === 1" class="text-lg">🥈</span>
                                        <span v-else-if="index === 2" class="text-lg">🥉</span>
                                        <span v-else class="w-6 h-6 rounded-full bg-gray-200 dark:bg-neutral-600 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300">{{ index + 1 }}</span>
                                    </div>
                                    <!-- User Info -->
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-growth-400 to-growth-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ referrer.name?.charAt(0)?.toUpperCase() || '?' }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ referrer.name }}</span>
                                        <span v-if="userRank && userRank.user_id === referrer.id" class="px-1.5 py-0.5 bg-growth-100 dark:bg-growth-900/50 text-growth-700 dark:text-growth-300 text-xs font-medium rounded">You</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-growth-600 bg-growth-100 dark:bg-growth-900/50 px-2 py-1 rounded">
                                    {{ referrer.total_referrals }} referrals
                                </span>
                            </div>
                        </div>
                        
                        <!-- User's Rank if not in top 5 -->
                        <div v-if="userRank && userRank.rank > 5" class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-growth-50 border-2 border-growth-200">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full bg-growth-200 flex items-center justify-center text-sm font-bold text-growth-700">{{ userRank.rank }}</span>
                                    <span class="font-medium text-gray-900">Your Rank</span>
                                </div>
                                <span class="text-sm font-bold text-growth-600 bg-growth-100 px-3 py-1.5 rounded-full">
                                    {{ userRank.referral_count }} referrals
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                    <Link
                        :href="route('wallet.transactions')"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    >
                        View All
                    </Link>
                </div>

                <!-- Transaction List -->
                <div v-if="recentTransactions.length > 0" class="space-y-3">
                    <RhythmicCard
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        variant="light"
                        size="md"
                        :hover-lift="true"
                        class="animate-fadeIn"
                    >
                        <template #default>
                            <div class="flex items-start space-x-3">
                            <!-- Icon -->
                            <div
                                class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center"
                                :class="transaction.transaction_type === 'credit' ? 'bg-green-100' : 'bg-red-100'"
                            >
                                <component
                                    :is="getTransactionIcon(transaction.transaction_type)"
                                    class="h-6 w-6"
                                    :class="transaction.transaction_type === 'credit' ? 'text-green-600' : 'text-red-600'"
                                />
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ transaction.description }}</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs text-gray-500">{{ formatDate(transaction.created_at) }}</span>
                                    <span class="text-xs text-gray-500">•</span>
                                    <StatusBadge :status="transaction.status" size="sm" />
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="flex-shrink-0 text-right">
                                <p
                                    class="text-lg font-bold"
                                    :class="getTransactionColor(transaction.transaction_type)"
                                >
                                    {{ getTransactionSign(transaction.transaction_type) }}{{ formatCurrency(transaction.amount) }}
                                </p>
                            </div>
                        </div>
                        </template>
                    </RhythmicCard>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl p-12 sm:p-16 text-center shadow-sm">
                    <ClockIcon class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                    <p class="text-gray-500 font-medium mb-2">No transactions yet</p>
                    <p class="text-sm text-gray-400">Your transaction history will appear here</p>
                </div>
            </div>
        </div>

        <!-- Add Funds Modal -->
        <div
            v-if="showAddFunds"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 animate-fadeIn"
            @click.self="showAddFunds = false"
        >
            <div class="bg-ocean-50 rounded-t-3xl sm:rounded-3xl w-full sm:max-w-md max-h-[85vh] sm:max-h-[90vh] overflow-y-auto shadow-rhythmic-2xl animate-slideIn">
                <div class="p-rhythm-lg pb-[calc(1.5rem+env(safe-area-inset-bottom,0px))]">
                    <!-- Modal Header with gradient accent -->
                    <div class="flex items-center gap-3 mb-rhythm-lg">
                        <div class="w-12 h-12 rounded-xl bg-growth-600 flex items-center justify-center shadow-rhythmic-md">
                            <PlusCircleIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-display font-bold text-gray-900">Add Funds</h3>
                            <p class="text-sm text-gray-600">Choose a payment method</p>
                        </div>
                    </div>

                <form @submit.prevent="submitAddFunds" class="space-y-rhythm-lg">
                    <!-- Amount Input Card -->
                    <RhythmicCard variant="light" size="md">
                        <template #default>
                            <label class="block text-sm font-semibold text-gray-700 mb-rhythm-sm">Amount (BDT)</label>
                            <input
                                v-model="addFundsForm.amount"
                                type="number"
                                min="100"
                                max="100000"
                                placeholder="Enter amount (min ৳100)"
                                class="w-full px-rhythm-md py-rhythm-md text-lg border-2 border-ocean-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-ocean-500 transition-all bg-white"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-rhythm-xs flex items-center gap-1">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-growth-600"></span>
                                Minimum: ৳100 • Maximum: ৳100,000
                            </p>
                        </template>
                    </RhythmicCard>

                    <!-- Payment Gateway Selector -->
                    <div v-if="addFundsForm.amount >= 100" class="animate-fadeIn">
                        <label class="block text-sm font-semibold text-gray-800 mb-rhythm-sm flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-growth-600"></span>
                            Select Payment Gateway
                        </label>
                        <PaymentGatewaySelector
                            :amount="Number(addFundsForm.amount)"
                            v-model="addFundsForm.gateway"
                            @update:agreeToTerms="agreeToTerms = $event"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-rhythm-sm pt-rhythm-md pb-rhythm-xs">
                        <FlowButton
                            type="button"
                            @click="showAddFunds = false"
                            variant="secondary"
                            size="lg"
                            class="flex-1 min-h-[48px]"
                        >
                            Cancel
                        </FlowButton>
                        <FlowButton
                            type="submit"
                            :disabled="addFundsForm.processing || !addFundsForm.gateway || !agreeToTerms || addFundsForm.amount < 100"
                            :loading="addFundsForm.processing"
                            variant="primary"
                            size="lg"
                            class="flex-1 min-h-[48px]"
                        >
                            {{ addFundsForm.processing ? 'Processing...' : 'Proceed to Payment' }}
                        </FlowButton>
                    </div>
                </form>
                </div>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div
            v-if="showWithdraw"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 animate-fadeIn"
            @click.self="showWithdraw = false"
        >
            <div class="bg-sunrise-50 rounded-t-3xl sm:rounded-3xl w-full sm:max-w-md max-h-[85vh] sm:max-h-[90vh] overflow-y-auto shadow-rhythmic-2xl animate-slideIn">
                <div class="p-rhythm-lg pb-[calc(1.5rem+env(safe-area-inset-bottom,0px))]">
                    <!-- Modal Header with gradient accent -->
                    <div class="flex items-center gap-3 mb-rhythm-lg">
                        <div class="w-12 h-12 rounded-xl bg-sunrise-500 flex items-center justify-center shadow-rhythmic-md">
                            <MinusCircleIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-display font-bold text-gray-900">Withdraw Funds</h3>
                            <p class="text-sm text-gray-600">Transfer to your account</p>
                        </div>
                    </div>

                <form @submit.prevent="submitWithdraw" class="space-y-rhythm-md">
                    <!-- Withdraw Form Card -->
                    <RhythmicCard variant="light" size="md">
                        <template #default>
                            <div class="space-y-rhythm-md">
                                <!-- Amount Input -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-rhythm-sm">Amount (BDT)</label>
                                    <input
                                        v-model="withdrawForm.amount"
                                        type="number"
                                        placeholder="Enter amount"
                                        :max="balance"
                                        class="w-full px-rhythm-md py-rhythm-md text-lg border-2 border-sunrise-200 rounded-xl focus:ring-2 focus:ring-sunrise-500 focus:border-sunrise-500 transition-all bg-white"
                                        required
                                    />
                                    <p class="text-xs text-gray-500 mt-rhythm-xs flex items-center gap-1">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-growth-500"></span>
                                        Available: {{ formattedBalance }}
                                    </p>
                                </div>

                                <!-- Account Type -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-rhythm-sm">Withdraw To</label>
                                    <select
                                        v-model="withdrawForm.account_type"
                                        class="w-full px-rhythm-md py-rhythm-md border-2 border-sunrise-200 rounded-xl focus:ring-2 focus:ring-sunrise-500 focus:border-sunrise-500 transition-all bg-white"
                                    >
                                        <option value="Bank">🏦 Bank Account</option>
                                        <option value="bKash">💰 bKash</option>
                                        <option value="Nagad">📱 Nagad</option>
                                        <option value="Rocket">🚀 Rocket</option>
                                    </select>
                                </div>

                                <!-- Account Number -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-rhythm-sm">Account Number</label>
                                    <input
                                        v-model="withdrawForm.account_number"
                                        type="text"
                                        placeholder="Enter account number"
                                        class="w-full px-rhythm-md py-rhythm-md border-2 border-sunrise-200 rounded-xl focus:ring-2 focus:ring-sunrise-500 focus:border-sunrise-500 transition-all bg-white"
                                        required
                                    />
                                </div>
                            </div>
                        </template>
                    </RhythmicCard>

                    <!-- Action Buttons -->
                    <div class="flex gap-rhythm-sm pt-rhythm-md pb-rhythm-xs">
                        <FlowButton
                            type="button"
                            @click="showWithdraw = false"
                            variant="secondary"
                            size="lg"
                            class="flex-1 min-h-[48px]"
                        >
                            Cancel
                        </FlowButton>
                        <FlowButton
                            type="submit"
                            :disabled="withdrawForm.processing"
                            :loading="withdrawForm.processing"
                            variant="primary"
                            size="lg"
                            class="flex-1 min-h-[48px]"
                        >
                            {{ withdrawForm.processing ? 'Processing...' : 'Withdraw' }}
                        </FlowButton>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
