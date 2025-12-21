<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import AdDisplay from '@/Components/AdDisplay.vue';
import { 
    SparklesIcon, 
    CurrencyDollarIcon, 
    TrophyIcon, 
    ShieldCheckIcon, 
    ClockIcon, 
    LightBulbIcon, 
    GlobeAltIcon, 
    DocumentTextIcon,
    UserCircleIcon,
    AcademicCapIcon,
    BriefcaseIcon,
    DocumentCheckIcon,
    ChevronRightIcon,
    FireIcon,
    ArrowRightIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    profileCompletion: Number,
    recentActivity: Array,
    suggestions: Array,
    recommendedServices: Array,
    topReferrers: Array,
    userRank: [Number, null],
    availableServices: Array,
    extendedProfileServices: Array
});

// Icon mapping for dynamic service icons
const iconMap = {
    'sparkles': SparklesIcon,
    'currency': CurrencyDollarIcon,
    'trophy': TrophyIcon,
    'shield': ShieldCheckIcon,
    'clock': ClockIcon,
    'lightbulb': LightBulbIcon,
    'globe': GlobeAltIcon,
    'document': DocumentTextIcon,
    'user': UserCircleIcon,
    'academic': AcademicCapIcon,
    'briefcase': BriefcaseIcon,
    'check': DocumentCheckIcon,
    'fire': FireIcon
};

// Get icon component by name
const getIcon = (iconName) => iconMap[iconName?.toLowerCase()] || DocumentCheckIcon;

// Profile completion status
const completionStatus = computed(() => {
    if (props.profileCompletion >= 80) return { color: 'emerald', text: 'Excellent', bgClass: 'bg-emerald-100', textClass: 'text-emerald-600', barClass: 'bg-emerald-500' };
    if (props.profileCompletion >= 50) return { color: 'amber', text: 'Good Progress', bgClass: 'bg-amber-100', textClass: 'text-amber-600', barClass: 'bg-amber-500' };
    return { color: 'red', text: 'Needs Work', bgClass: 'bg-red-100', textClass: 'text-red-600', barClass: 'bg-red-500' };
});

// CORE PROFILE shortcuts
const profileShortcuts = [
    { 
        name: 'Edit Profile', 
        icon: 'user', 
        route: 'profile.edit', 
        description: 'Update your information',
        gradient: 'from-emerald-500 to-emerald-600'
    },
    { 
        name: 'Education', 
        icon: 'academic', 
        route: 'profile.edit', 
        params: { section: 'education' }, 
        badge: props.stats?.education_count, 
        description: 'Academic records',
        gradient: 'from-blue-500 to-blue-600'
    },
    { 
        name: 'Work Experience', 
        icon: 'briefcase', 
        route: 'profile.edit', 
        params: { section: 'experience' }, 
        badge: props.stats?.experience_count, 
        description: 'Employment history',
        gradient: 'from-purple-500 to-purple-600'
    }
];

// Platform services with colors
const platformServices = computed(() => {
    if (!props.availableServices?.length) return [];
    
    const serviceColors = {
        'wallet': { bg: 'bg-emerald-50', text: 'text-emerald-600', iconBg: 'bg-emerald-100' },
        'referrals': { bg: 'bg-pink-50', text: 'text-pink-600', iconBg: 'bg-pink-100' },
        'ai-profile-assessment': { bg: 'bg-purple-50', text: 'text-purple-600', iconBg: 'bg-purple-100' },
        'cv-builder': { bg: 'bg-indigo-50', text: 'text-indigo-600', iconBg: 'bg-indigo-100' },
        'document-scanner': { bg: 'bg-cyan-50', text: 'text-cyan-600', iconBg: 'bg-cyan-100' },
        'public-profile': { bg: 'bg-teal-50', text: 'text-teal-600', iconBg: 'bg-teal-100' },
        'payments': { bg: 'bg-amber-50', text: 'text-amber-600', iconBg: 'bg-amber-100' },
        'support': { bg: 'bg-rose-50', text: 'text-rose-600', iconBg: 'bg-rose-100' },
    };
    
    return props.availableServices.map(s => {
        const colors = serviceColors[s.slug] || { bg: 'bg-gray-50', text: 'text-gray-600', iconBg: 'bg-gray-100' };
        return {
            name: s.name,
            icon: s.icon,
            route: s.route || 'services.show',
            params: s.route_params,
            description: s.description,
            is_featured: s.is_featured,
            ...colors
        };
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50/50">
            <!-- Hero Header Section -->
            <div class="bg-gradient-to-br from-upwork-green via-upwork-green to-emerald-600 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Welcome Text -->
                        <div>
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2">
                                Welcome back! 👋
                            </h1>
                            <p class="text-white/80 text-base sm:text-lg max-w-xl">
                                Your gateway to global opportunities. Track your progress and manage your journey.
                            </p>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-3">
                            <Link 
                                :href="route('services.index')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-upwork-green font-semibold rounded-full hover:bg-gray-100 transition-all shadow-lg shadow-black/10"
                            >
                                <GlobeAltIcon class="w-5 h-5" />
                                Explore Services
                            </Link>
                            <Link 
                                :href="route('profile.edit')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 text-white font-semibold rounded-full hover:bg-white/30 transition-all backdrop-blur-sm"
                            >
                                <UserCircleIcon class="w-5 h-5" />
                                Edit Profile
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Profile Completion Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 col-span-2 lg:col-span-1 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center', completionStatus.bgClass]">
                                    <CheckCircleIcon :class="['w-6 h-6', completionStatus.textClass]" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Profile</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ profileCompletion }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div 
                                :class="['h-full rounded-full transition-all duration-500', completionStatus.barClass]"
                                :style="{ width: profileCompletion + '%' }"
                            ></div>
                        </div>
                        <p :class="['text-xs mt-2', completionStatus.textClass]">
                            {{ completionStatus.text }}
                        </p>
                    </div>

                    <!-- Education Count -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <AcademicCapIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Education</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.education_count || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Experience Count -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                                <BriefcaseIcon class="w-6 h-6 text-purple-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Experience</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.experience_count || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Applications Count -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                                <DocumentCheckIcon class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Applications</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.applications_count || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Ad -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                <AdDisplay 
                    placement="banner" 
                    page="dashboard"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column - Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Profile Management Section -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-base font-bold text-gray-900">Profile Management</h2>
                                        <p class="text-xs text-gray-500 mt-0.5">Keep your profile up to date</p>
                                    </div>
                                    <Link :href="route('profile.edit')" class="text-sm text-upwork-green hover:text-upwork-green-dark font-semibold flex items-center gap-1">
                                        View All
                                        <ArrowRightIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <Link 
                                        v-for="service in profileShortcuts" 
                                        :key="service.route"
                                        :href="route(service.route, service.params || {})"
                                        class="group relative p-5 rounded-xl border-2 border-gray-100 hover:border-upwork-green/50 hover:shadow-lg transition-all bg-gray-50/50 hover:bg-white overflow-hidden"
                                    >
                                        <div class="relative z-10">
                                            <div :class="['w-10 h-10 rounded-lg bg-gradient-to-br flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-md', service.gradient]">
                                                <component 
                                                    :is="getIcon(service.icon)" 
                                                    class="h-5 w-5 text-white"
                                                />
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-upwork-green transition-colors">
                                                {{ service.name }}
                                            </h3>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ service.description }}</p>
                                            <span 
                                                v-if="service.badge" 
                                                class="inline-block mt-3 px-2.5 py-1 text-xs font-semibold bg-gray-200 text-gray-700 rounded-full"
                                            >
                                                {{ service.badge }} records
                                            </span>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Suggestions Section -->
                        <div v-if="suggestions && suggestions.length > 0" class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl border border-amber-200/50 overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                        <LightBulbIcon class="h-5 w-5 text-amber-600" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-gray-900">Improve Your Profile</h2>
                                        <p class="text-sm text-gray-600">Complete these to stand out</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div 
                                        v-for="suggestion in suggestions" 
                                        :key="suggestion.title"
                                        class="flex items-center justify-between p-4 bg-white rounded-xl border border-amber-100 group hover:border-amber-300 transition-colors"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                                <component :is="getIcon(suggestion.icon)" class="h-5 w-5 text-amber-600" />
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-900 text-sm">{{ suggestion.title }}</h3>
                                                <p class="text-xs text-gray-500">{{ suggestion.message }}</p>
                                            </div>
                                        </div>
                                        <Link 
                                            v-if="suggestion.action_route"
                                            :href="route(suggestion.action_route)" 
                                            class="px-4 py-2 bg-upwork-green text-white text-sm font-semibold rounded-full hover:bg-upwork-green-dark transition-colors"
                                        >
                                            {{ suggestion.action_text }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Platform Services Grid -->
                        <div v-if="platformServices.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-lg font-bold text-gray-900">Services & Tools</h2>
                                        <p class="text-sm text-gray-500 mt-0.5">Quick access to platform features</p>
                                    </div>
                                    <Link :href="route('services.index')" class="text-sm text-upwork-green hover:text-upwork-green-dark font-semibold flex items-center gap-1">
                                        Browse All
                                        <ArrowRightIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <Link 
                                        v-for="service in platformServices" 
                                        :key="service.name"
                                        :href="route(service.route, service.params || {})"
                                        class="group p-4 rounded-xl border-2 transition-all hover:shadow-md"
                                        :class="[
                                            service.is_featured 
                                                ? 'border-orange-200 bg-gradient-to-br from-orange-50 to-amber-50 hover:border-orange-400' 
                                                : 'border-gray-100 bg-gray-50/50 hover:border-upwork-green/50 hover:bg-white'
                                        ]"
                                    >
                                        <div :class="['w-10 h-10 rounded-lg flex items-center justify-center mb-3', service.iconBg]">
                                            <component 
                                                :is="getIcon(service.icon)" 
                                                class="h-5 w-5"
                                                :class="service.text"
                                            />
                                        </div>
                                        <h3 class="font-semibold text-sm text-gray-900 group-hover:text-upwork-green transition-colors">
                                            {{ service.name }}
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ service.description }}</p>
                                        <div v-if="service.is_featured" class="mt-2">
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-orange-600">
                                                <FireIcon class="w-3 h-3" />
                                                Featured
                                            </span>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- CTA Card -->
                        <div class="bg-gradient-to-br from-upwork-green to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
                            <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center mb-4 backdrop-blur-sm">
                                <GlobeAltIcon class="h-8 w-8 text-white" />
                            </div>
                            <h3 class="text-xl font-bold mb-2">Explore Visa Services</h3>
                            <p class="text-sm text-white/80 mb-5 leading-relaxed">
                                Discover visa services, travel insurance, flight bookings, and more for your journey.
                            </p>
                            <Link 
                                :href="route('services.index')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-upwork-green rounded-full font-semibold text-sm hover:bg-gray-100 transition-colors shadow-lg"
                            >
                                Browse Services
                                <ChevronRightIcon class="h-4 w-4" />
                            </Link>
                        </div>

                        <!-- Sidebar Ad -->
                        <AdDisplay 
                            placement="sidebar" 
                            page="dashboard"
                        />

                        <!-- Leaderboard Section -->
                        <div v-if="topReferrers && topReferrers.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                        <TrophyIcon class="h-5 w-5 text-amber-600" />
                                    </div>
                                    <h2 class="text-lg font-bold text-gray-900">Top Referrers</h2>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="space-y-2">
                                    <div 
                                        v-for="(referrer, index) in topReferrers.slice(0, 5)" 
                                        :key="referrer.id" 
                                        class="flex items-center justify-between p-3 rounded-xl transition-colors"
                                        :class="userRank && userRank.user_id === referrer.id ? 'bg-emerald-50 border border-emerald-100' : 'bg-gray-50 hover:bg-gray-100'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <span class="text-lg w-6 text-center">
                                                <span v-if="index === 0">🥇</span>
                                                <span v-else-if="index === 1">🥈</span>
                                                <span v-else-if="index === 2">🥉</span>
                                                <span v-else class="text-sm font-bold text-gray-400">{{ index + 1 }}</span>
                                            </span>
                                            <span class="font-medium text-sm text-gray-900">{{ referrer.name }}</span>
                                        </div>
                                        <span class="text-sm font-bold text-upwork-green bg-emerald-100 px-2.5 py-1 rounded-full">
                                            {{ referrer.total_referrals }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>