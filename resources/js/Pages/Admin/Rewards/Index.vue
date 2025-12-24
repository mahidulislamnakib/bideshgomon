<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import { 
    MagnifyingGlassIcon,
    CheckCircleIcon, 
    XCircleIcon,
    CurrencyDollarIcon,
    ClockIcon,
    ChartBarIcon,
    GiftIcon,
    FunnelIcon,
    XMarkIcon,
    BanknotesIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import { ref } from 'vue';

const props = defineProps({
    rewards: Object,
    filters: Object,
    globalStats: {
        type: Object,
        default: () => ({
            totalRewards: 0,
            pendingCount: 0,
            pendingAmount: 0,
            approvedCount: 0,
            approvedAmount: 0,
            totalAmount: 0
        })
    },
});

const { formatCurrency, formatDate, formatTime } = useBangladeshFormat();
const loading = ref(false);
const showFilters = ref(false);

const searchForm = useForm({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
});

const hasActiveFilters = () => {
    return searchForm.search || searchForm.status;
};

const search = () => {
    searchForm.get(route('admin.rewards.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    search();
};

const approveReward = (rewardId) => {
    if (confirm('Are you sure you want to approve this reward? This will credit the user\'s wallet.')) {
        router.post(route('admin.rewards.approve', rewardId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message handled by backend
            }
        });
    }
};

const rejectReward = (rewardId) => {
    const reason = prompt('Enter rejection reason (optional):');
    if (reason !== null) { // User clicked OK (even if empty)
        router.post(route('admin.rewards.reject', rewardId), {
            reason: reason
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message handled by backend
            }
        });
    }
};

const getStatusColor = (status) => {
    return {
        'approved': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'paid': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    }[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300';
};
</script>

<template>
    <Head title="Reward Management" />

    <AdminLayout>
        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <div v-else class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Hero Header with Stats -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="rewardGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#rewardGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-yellow-500/20 to-amber-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <GiftIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">Reward Management</h1>
                            </div>
                            <p class="text-neutral-300">Manage referral rewards and approvals</p>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                                <span v-if="hasActiveFilters()" class="ml-1 px-2 py-0.5 bg-amber-500 text-white text-xs rounded-full">Active</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-2xl">
                                    <ChartBarIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total Rewards</p>
                                    <p class="text-2xl font-bold text-white">{{ globalStats?.totalRewards || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-2xl">
                                    <ClockIcon class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Pending</p>
                                    <p class="text-2xl font-bold text-white">{{ globalStats?.pendingCount || 0 }}</p>
                                    <p class="text-xs text-yellow-300">{{ formatCurrency(globalStats?.pendingAmount || 0) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-2xl">
                                    <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Approved</p>
                                    <p class="text-2xl font-bold text-white">{{ globalStats?.approvedCount || 0 }}</p>
                                    <p class="text-xs text-green-300">{{ formatCurrency(globalStats?.approvedAmount || 0) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-2xl">
                                    <BanknotesIcon class="h-6 w-6 text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total Paid</p>
                                    <p class="text-2xl font-bold text-white">{{ formatCurrency(globalStats?.totalAmount || 0) }}</p>
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
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filter Rewards</h3>
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
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-amber-500 focus:ring-amber-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status</label>
                                <select
                                    v-model="searchForm.status"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-amber-500 focus:ring-amber-500 py-2.5"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </form>

                        <div class="flex gap-3 mt-4">
                            <button
                                @click="search"
                                :disabled="searchForm.processing"
                                class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-medium rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all disabled:opacity-50"
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

                <!-- Rewards Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="!rewards?.data || rewards.data.length === 0"
                        icon="GiftIcon"
                        title="No rewards found"
                        :description="hasActiveFilters() ? 'Try adjusting your filters' : 'Rewards from referrals will appear here once users start referring others.'"
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="reward in rewards.data" :key="reward.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">{{ reward.user?.name?.charAt(0) || 'U' }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ reward.user?.name }}</div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ reward.user?.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ reward.type }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ reward.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(reward.amount) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="getStatusColor(reward.status)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ ((reward.status || '').charAt(0).toUpperCase() || '') + (reward.status || '').slice(1) }}
                                        </span>
                                        <div v-if="reward.status === 'rejected' && reward.metadata?.rejection_reason" class="text-xs text-red-600 dark:text-red-400 mt-1">
                                            {{ reward.metadata.rejection_reason }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-neutral-900 dark:text-white">{{ formatDate(reward.created_at) }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatTime(reward.created_at) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div v-if="reward.status === 'pending'" class="flex items-center justify-end gap-2">
                                            <button
                                                @click="approveReward(reward.id)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-2xl transition-colors"
                                            >
                                                <CheckCircleIcon class="h-4 w-4" />
                                                Approve
                                            </button>
                                            <button
                                                @click="rejectReward(reward.id)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-colors"
                                            >
                                                <XCircleIcon class="h-4 w-4" />
                                                Reject
                                            </button>
                                        </div>
                                        <span v-else class="text-sm text-neutral-400 dark:text-neutral-500">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="rewards?.data && rewards.data.length > 0" class="bg-neutral-50 dark:bg-neutral-900/50 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Showing <span class="font-semibold text-neutral-900 dark:text-white">{{ rewards.from || 0 }}</span>
                                to <span class="font-semibold text-neutral-900 dark:text-white">{{ rewards.to || 0 }}</span>
                                of <span class="font-semibold text-neutral-900 dark:text-white">{{ rewards.total || 0 }}</span> rewards
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="rewards.prev_page_url"
                                    :href="rewards.prev_page_url"
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
                                    v-if="rewards.next_page_url"
                                    :href="rewards.next_page_url"
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
    </AdminLayout>
</template>
