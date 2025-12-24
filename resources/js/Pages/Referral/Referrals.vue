<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    UserGroupIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    ClockIcon,
    EnvelopeIcon,
    PhoneIcon,
    CalendarIcon,
    GiftIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline';
import { CheckBadgeIcon } from '@heroicons/vue/24/solid';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    referrals: Object,
});

const { formatDate, formatTime } = useBangladeshFormat();

const getStatusConfig = (referral) => {
    if (referral.is_completed) {
        return {
            label: 'Completed',
            class: 'bg-green-100 text-green-700 border-green-200',
            icon: CheckBadgeIcon
        };
    }
    return {
        label: 'Pending',
        class: 'bg-amber-100 text-amber-700 border-amber-200',
        icon: ClockIcon
    };
};

const getRewardStatusConfig = (reward) => {
    if (!reward) return null;
    
    const configs = {
        approved: { label: 'Approved', class: 'bg-green-100 text-green-700' },
        pending: { label: 'Pending', class: 'bg-amber-100 text-amber-700' },
        rejected: { label: 'Rejected', class: 'bg-red-100 text-red-700' },
    };
    return configs[reward.status] || configs.pending;
};
</script>

<template>
    <Head title="My Referrals" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30">
            
            <!-- Hero Header -->
            <div class="relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-growth-600 via-growth-700 to-emerald-800"></div>
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                    <!-- Back Link -->
                    <Link
                        :href="route('referral.index')"
                        class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors mb-6 group"
                    >
                        <ArrowLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
                        <span class="font-medium">Back to Dashboard</span>
                    </Link>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                <UserGroupIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-1">My Referrals</h1>
                                <p class="text-white/80">People you've invited to Bidesh Gomon</p>
                            </div>
                        </div>
                        
                        <!-- Stats Badge -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl px-6 py-4">
                            <div class="text-center">
                                <p class="text-3xl font-bold text-white">{{ referrals.total || 0 }}</p>
                                <p class="text-sm text-white/70">Total Referrals</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                
                <!-- Referrals List -->
                <div v-if="referrals.data.length > 0" class="space-y-4">
                    <div
                        v-for="referral in referrals.data"
                        :key="referral.id"
                        class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 hover:border-growth-200 hover:shadow-lg transition-all duration-300 overflow-hidden"
                    >
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                
                                <!-- Avatar & Name -->
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-14 h-14 bg-gradient-to-br from-growth-400 to-growth-600 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-lg">
                                        {{ referral.referred_user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-bold text-gray-900 truncate">
                                            {{ referral.referred_user?.name || 'Unknown User' }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                            <span class="flex items-center gap-1 text-sm text-gray-500">
                                                <EnvelopeIcon class="h-4 w-4" />
                                                {{ referral.referred_user?.email || 'N/A' }}
                                            </span>
                                            <span v-if="referral.referred_user?.phone" class="flex items-center gap-1 text-sm text-gray-500">
                                                <PhoneIcon class="h-4 w-4" />
                                                {{ referral.referred_user?.phone }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Reward -->
                                <div class="flex flex-wrap items-center gap-3 sm:ml-4">
                                    <!-- Joined Date -->
                                    <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-3 py-2 rounded-lg">
                                        <CalendarIcon class="h-4 w-4" />
                                        <div>
                                            <span class="font-medium">{{ formatDate(referral.created_at) }}</span>
                                            <span class="text-gray-400 hidden sm:inline"> at {{ formatTime(referral.created_at) }}</span>
                                        </div>
                                    </div>

                                    <!-- Referral Status -->
                                    <div :class="[getStatusConfig(referral).class, 'flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold border']">
                                        <component :is="getStatusConfig(referral).icon" class="h-4 w-4" />
                                        {{ getStatusConfig(referral).label }}
                                    </div>

                                    <!-- Reward Status -->
                                    <div v-if="referral.reward" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium" :class="getRewardStatusConfig(referral.reward)?.class">
                                        <GiftIcon class="h-4 w-4" />
                                        Reward: {{ getRewardStatusConfig(referral.reward)?.label }}
                                    </div>
                                    <div v-else class="flex items-center gap-1.5 px-3 py-2 bg-gray-100 text-gray-500 rounded-lg text-sm">
                                        <GiftIcon class="h-4 w-4" />
                                        No Reward Yet
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-3xl bg-growth-100 flex items-center justify-center">
                        <UserPlusIcon class="h-12 w-12 text-growth-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No referrals yet</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Share your referral code with friends and family to start earning rewards!
                    </p>
                    <Link
                        :href="route('referral.index')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-semibold rounded-xl hover:from-growth-700 hover:to-growth-800 transition-all shadow-lg hover:shadow-xl"
                    >
                        <UserGroupIcon class="h-5 w-5" />
                        Go to Referral Dashboard
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="referrals.data.length > 0" class="mt-10 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-semibold text-gray-900">{{ referrals.from }}</span> to 
                        <span class="font-semibold text-gray-900">{{ referrals.to }}</span> of 
                        <span class="font-semibold text-gray-900">{{ referrals.total }}</span> referrals
                    </div>
                    <div class="flex items-center gap-1 overflow-x-auto">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="link in referrals.links"
                            :key="link.label"
                            :href="link.url || undefined"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                                link.active
                                    ? 'bg-growth-600 text-white shadow-md'
                                    : link.url
                                    ? 'bg-gray-100 text-gray-700 hover:bg-growth-50 hover:text-growth-600'
                                    : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <!-- Help Card -->
                <div class="mt-8 bg-gradient-to-r from-growth-600 to-growth-700 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <GiftIcon class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Want to earn more rewards?</h3>
                                <p class="text-white/80 text-sm">Share your referral code and earn for every friend who signs up!</p>
                            </div>
                        </div>
                        <Link
                            :href="route('referral.index')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-growth-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors shadow-lg"
                        >
                            Share Your Code
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

