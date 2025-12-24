<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ClipboardDocumentIcon, 
    UserGroupIcon, 
    CurrencyDollarIcon, 
    ClockIcon,
    ShareIcon,
    CheckCircleIcon 
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import { ref } from 'vue';

const props = defineProps({
    referralCode: String,
    referralLink: String,
    stats: Object,
    referrals: Array,
    recentRewards: Array,
});

const { formatCurrency, formatDate, formatTime } = useBangladeshFormat();

const copied = ref(false);

const copyToClipboard = () => {
    navigator.clipboard.writeText(props.referralLink);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const shareViaWhatsApp = () => {
    const message = `Join BideshGomon and start your journey abroad! Use my referral code: ${props.referralCode}\n\n${props.referralLink}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
};

const shareViaFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(props.referralLink)}`, '_blank');
};

const shareViaEmail = () => {
    const subject = 'Join BideshGomon - Your Journey Abroad Starts Here!';
    const body = `Hi!\n\nI'm using BideshGomon to plan my journey abroad, and I thought you might be interested too!\n\nUse my referral code: ${props.referralCode}\nOr click this link: ${props.referralLink}\n\nYou'll get rewards after registration!\n\nBest regards`;
    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
};
</script>

<template>
    <Head title="Referrals" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-950">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white">Referral Dashboard</h1>
                            <p class="text-sm text-white/80 mt-0.5">Share & earn rewards together</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('referral.referrals')"
                                class="px-4 py-2 text-sm font-medium bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors"
                            >
                                My Referrals
                            </Link>
                            <Link
                                :href="route('referral.rewards')"
                                class="px-4 py-2 text-sm font-medium bg-white text-growth-600 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                Rewards
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">
                <!-- Referral Code Card -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-6 py-6 text-center">
                        <ShareIcon class="h-8 w-8 text-white mx-auto mb-3" />
                        <h3 class="text-lg font-bold text-white mb-1">Your Referral Code</h3>
                        <p class="text-sm text-white/80">Share with friends and family to earn rewards!</p>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="p-6">
                        <!-- Large Referral Code Display -->
                        <div class="bg-growth-50 dark:bg-growth-900/20 border border-growth-200 dark:border-growth-800 rounded-xl p-6 mb-6 text-center">
                            <p class="text-3xl sm:text-4xl font-black tracking-[0.2em] text-growth-700 dark:text-growth-400 mb-1">
                                {{ referralCode }}
                            </p>
                            <p class="text-xs text-growth-600 dark:text-growth-500">Your unique referral code</p>
                        </div>

                        <!-- Referral Link -->
                        <div class="mb-6">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Share this link</label>
                            <div class="flex items-stretch bg-gray-50 dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-lg overflow-hidden">
                                <input 
                                    type="text" 
                                    :value="referralLink" 
                                    readonly 
                                    class="flex-1 bg-transparent text-gray-700 px-4 py-3.5 text-sm font-mono border-0 focus:ring-0 focus:outline-none"
                                />
                                <button
                                    @click="copyToClipboard"
                                    class="flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-growth-600 to-growth-700 text-white hover:from-growth-700 hover:to-growth-800 font-semibold transition-all duration-200 whitespace-nowrap"
                                >
                                    <CheckCircleIcon v-if="copied" class="h-5 w-5" />
                                    <ClipboardDocumentIcon v-else class="h-5 w-5" />
                                    {{ copied ? 'Copied!' : 'Copy Link' }}
                                </button>
                            </div>
                        </div>

                        <!-- Share Buttons -->
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-700 mb-4">Quick Share</p>
                            <div class="flex flex-wrap justify-center gap-3">
                                <!-- WhatsApp -->
                                <button
                                    @click="shareViaWhatsApp"
                                    class="group inline-flex items-center gap-2 px-5 py-3 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-xl font-semibold transition-all duration-200 shadow-lg shadow-green-500/20 hover:shadow-xl hover:shadow-green-500/30 transform hover:-translate-y-0.5"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    WhatsApp
                                </button>

                                <!-- Facebook -->
                                <button
                                    @click="shareViaFacebook"
                                    class="group inline-flex items-center gap-2 px-5 py-3 bg-[#1877F2] hover:bg-[#166fe5] text-white rounded-xl font-semibold transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-xl hover:shadow-blue-500/30 transform hover:-translate-y-0.5"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                    Facebook
                                </button>

                                <!-- Email -->
                                <button
                                    @click="shareViaEmail"
                                    class="group inline-flex items-center gap-2 px-5 py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-semibold transition-all duration-200 shadow-lg shadow-gray-500/20 hover:shadow-xl hover:shadow-gray-500/30 transform hover:-translate-y-0.5"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards - Modern Design -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Referrals -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Total Referrals</p>
                                <p class="text-4xl font-bold text-gray-900">{{ stats.totalReferrals }}</p>
                                <p class="text-xs text-gray-400 mt-2">Friends you've invited</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-growth-100 to-growth-200 rounded-2xl flex items-center justify-center">
                                <UserGroupIcon class="h-7 w-7 text-growth-600" />
                            </div>
                        </div>
                    </div>

                    <!-- Total Earnings -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Total Earnings</p>
                                <p class="text-4xl font-bold text-green-600">{{ formatCurrency(stats.totalEarnings) }}</p>
                                <p class="text-xs text-gray-400 mt-2">Paid rewards received</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center">
                                <CurrencyDollarIcon class="h-7 w-7 text-green-600" />
                            </div>
                        </div>
                    </div>

                    <!-- Pending Rewards -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Pending Rewards</p>
                                <p class="text-4xl font-bold text-amber-600">{{ formatCurrency(stats.pendingRewards) }}</p>
                                <p class="text-xs text-gray-400 mt-2">Awaiting approval</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center">
                                <ClockIcon class="h-7 w-7 text-amber-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Referrals -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <UserGroupIcon class="h-5 w-5 text-growth-600" />
                            Recent Referrals
                        </h3>
                        <Link 
                            :href="route('referral.referrals')"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-growth-600 hover:text-growth-700 transition-colors"
                        >
                            View All
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                    
                    <div v-if="referrals.length > 0" class="divide-y divide-gray-100">
                        <div v-for="referral in referrals" :key="referral.id" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                            <!-- Avatar -->
                            <div class="w-12 h-12 bg-gradient-to-br from-growth-400 to-growth-600 rounded-xl flex items-center justify-center text-white text-lg font-bold shadow-lg flex-shrink-0">
                                {{ referral.referred_user?.name?.charAt(0)?.toUpperCase() || '?' }}
                            </div>
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ referral.referred_user?.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ referral.referred_user?.email }}</p>
                            </div>
                            <!-- Date & Status -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs text-gray-500 mb-1">{{ formatDate(referral.created_at) }}</p>
                                <span :class="referral.is_completed ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                                    {{ referral.is_completed ? 'Completed' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
                            <UserGroupIcon class="h-8 w-8 text-gray-400" />
                        </div>
                        <p class="text-gray-500 font-medium">No referrals yet</p>
                        <p class="text-sm text-gray-400 mt-1">Share your code to get started!</p>
                    </div>
                </div>

                <!-- Recent Rewards -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <CurrencyDollarIcon class="h-5 w-5 text-green-600" />
                            Recent Rewards
                        </h3>
                        <Link 
                            :href="route('referral.rewards')"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-growth-600 hover:text-growth-700 transition-colors"
                        >
                            View All
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                    
                    <div v-if="recentRewards.length > 0" class="divide-y divide-gray-100">
                        <div v-for="reward in recentRewards" :key="reward.id" class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div :class="reward.status === 'approved' ? 'bg-green-100' : 'bg-amber-100'" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <CurrencyDollarIcon :class="reward.status === 'approved' ? 'text-green-600' : 'text-amber-600'" class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ reward.type }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(reward.created_at) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">{{ formatCurrency(reward.amount) }}</p>
                                <span :class="{
                                    'bg-green-100 text-green-700': reward.status === 'approved',
                                    'bg-amber-100 text-amber-700': reward.status === 'pending',
                                    'bg-red-100 text-red-700': reward.status === 'rejected'
                                }" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize">
                                    {{ reward.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
                            <CurrencyDollarIcon class="h-8 w-8 text-gray-400" />
                        </div>
                        <p class="text-gray-500 font-medium">No rewards yet</p>
                        <p class="text-sm text-gray-400 mt-1">Refer friends to earn rewards!</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>