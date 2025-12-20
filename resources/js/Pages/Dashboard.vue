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
    FireIcon
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    profileCompletion: Number,
    recentActivity: Array,
    suggestions: Array,  // Profile completion suggestions (context-aware)
    recommendedServices: Array,
    topReferrers: Array,
    userRank: [Number, null],
    availableServices: Array, // platform_tool and support services for dashboard
    extendedProfileServices: Array // No longer used - kept for backward compatibility
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

// Get icon component by name, fallback to DocumentCheckIcon
const getIcon = (iconName) => iconMap[iconName?.toLowerCase()] || DocumentCheckIcon;

// Profile completion color
const completionColor = computed(() => {
    if (props.profileCompletion >= 80) return 'bg-green-500';
    if (props.profileCompletion >= 50) return 'bg-orange-500';
    return 'bg-red-500';
});

// CORE PROFILE: Always visible, hardcoded (not from database)
// These are essential profile actions that every user needs
const profileShortcuts = [
    { 
        name: 'Edit Profile', 
        icon: 'user', 
        route: 'profile.edit', 
        description: 'Update your basic information',
        color: 'emerald',
        bgColor: 'bg-emerald-100',
        textColor: 'text-emerald-600',
        hoverColor: 'group-hover:text-emerald-700'
    },
    { 
        name: 'Education', 
        icon: 'academic', 
        route: 'profile.edit', 
        params: { section: 'education' }, 
        badge: props.stats?.education_count, 
        description: 'Manage academic records',
        color: 'blue',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600',
        hoverColor: 'group-hover:text-blue-700'
    },
    { 
        name: 'Work Experience', 
        icon: 'briefcase', 
        route: 'profile.edit', 
        params: { section: 'experience' }, 
        badge: props.stats?.experience_count, 
        description: 'Track employment history',
        color: 'purple',
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600',
        hoverColor: 'group-hover:text-purple-700'
    }
];

// PLATFORM SERVICES: Utilities and tools available to users
// Shown in dashboard for easy access (wallet, cv builder, ai assessment, etc.)
const platformServices = computed(() => {
    if (!props.availableServices?.length) return [];
    
    // Color mapping for different service types
    const serviceColors = {
        'wallet': { bg: 'bg-green-100', text: 'text-green-600' },
        'referrals': { bg: 'bg-pink-100', text: 'text-pink-600' },
        'ai-profile-assessment': { bg: 'bg-purple-100', text: 'text-purple-600' },
        'cv-builder': { bg: 'bg-indigo-100', text: 'text-indigo-600' },
        'document-scanner': { bg: 'bg-cyan-100', text: 'text-cyan-600' },
        'public-profile': { bg: 'bg-teal-100', text: 'text-teal-600' },
        'payments': { bg: 'bg-amber-100', text: 'text-amber-600' },
        'support': { bg: 'bg-rose-100', text: 'text-rose-600' },
        'appointments': { bg: 'bg-violet-100', text: 'text-violet-600' },
        'faqs': { bg: 'bg-sky-100', text: 'text-sky-600' },
        'events': { bg: 'bg-orange-100', text: 'text-orange-600' }
    };
    
    return props.availableServices.map(s => {
        const colors = serviceColors[s.slug] || { bg: 'bg-gray-100', text: 'text-gray-600' };
        return {
            name: s.name,
            icon: s.icon,
            route: s.route || 'services.show',
            params: s.route_params,
            description: s.description,
            color: s.is_featured ? 'orange' : 'gray',
            is_featured: s.is_featured,
            service_type: s.service_type,
            bgColor: s.is_featured ? 'bg-orange-100' : colors.bg,
            textColor: s.is_featured ? 'text-orange-600' : colors.text
        };
    });
});

// Get color classes for buttons
const getColorClasses = (color) => {
    const colors = {
        emerald: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500',
        blue: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        orange: 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500',
        green: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        purple: 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500',
        gray: 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500'
    };
    return colors[color] || colors.emerald;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6 lg:py-10 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Welcome Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-upwork-dark">Welcome back!</h1>
                    <p class="text-gray-600 mt-1">Here's an overview of your account and activities.</p>
                </div>

                <!-- Stats Grid - Upwork Style -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Profile Completion Card -->
                    <div class="upwork-card p-5 col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-600">Profile Strength</span>
                            <span class="text-2xl font-bold" :class="profileCompletion >= 80 ? 'text-upwork-green' : 'text-orange-500'">
                                {{ profileCompletion }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
                            <div 
                                :class="completionColor" 
                                class="h-2 rounded-full transition-all duration-500"
                                :style="{ width: profileCompletion + '%' }"
                            ></div>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ profileCompletion >= 80 ? 'Great progress!' : 'Complete to improve visibility' }}
                        </p>
                    </div>

                    <!-- Education Count -->
                    <div class="upwork-card p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <AcademicCapIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <span class="text-2xl font-bold text-upwork-dark">{{ stats?.education_count || 0 }}</span>
                        </div>
                        <span class="text-sm text-gray-600">Education Records</span>
                    </div>

                    <!-- Experience Count -->
                    <div class="upwork-card p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <BriefcaseIcon class="w-5 h-5 text-purple-600" />
                            </div>
                            <span class="text-2xl font-bold text-upwork-dark">{{ stats?.experience_count || 0 }}</span>
                        </div>
                        <span class="text-sm text-gray-600">Work Experience</span>
                    </div>

                    <!-- Applications or Referrals -->
                    <div class="upwork-card p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <DocumentCheckIcon class="w-5 h-5 text-green-600" />
                            </div>
                            <span class="text-2xl font-bold text-upwork-dark">{{ stats?.applications_count || 0 }}</span>
                        </div>
                        <span class="text-sm text-gray-600">Applications</span>
                    </div>
                </div>

                <!-- Banner Ad -->
                <AdDisplay 
                    placement="banner" 
                    page="dashboard"
                />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    
                    <!-- Left Column - Profile Management -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Profile Management Section -->
                        <div class="upwork-card p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-bold text-upwork-dark">Profile Management</h2>
                                <Link :href="route('profile.edit')" class="text-sm text-upwork-green hover:text-upwork-green-dark font-medium">
                                    View All →
                                </Link>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <Link 
                                    v-for="service in profileShortcuts" 
                                    :key="service.route"
                                    :href="route(service.route, service.params || {})"
                                    class="group p-4 rounded-xl border border-gray-100 hover:border-upwork-green hover:shadow-md transition-all bg-gray-50 hover:bg-white"
                                >
                                    <div class="flex flex-col items-center text-center">
                                        <div class="p-3 rounded-xl mb-3 group-hover:scale-110 transition-transform" :class="service.bgColor">
                                            <component 
                                                :is="getIcon(service.icon)" 
                                                class="h-6 w-6" 
                                                :class="service.textColor"
                                            />
                                        </div>
                                        <h3 class="font-semibold text-gray-900 group-hover:text-upwork-green transition-colors text-sm">
                                            {{ service.name }}
                                        </h3>
                                        <span 
                                            v-if="service.badge" 
                                            class="mt-2 px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full"
                                        >
                                            {{ service.badge }} records
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Suggestions Section (Context-Aware Profile Completion) -->
                        <div v-if="suggestions && suggestions.length > 0" class="upwork-card p-6 border-l-4 border-yellow-400">
                            <div class="flex items-center gap-2 mb-4">
                                <LightBulbIcon class="h-5 w-5 text-yellow-500" />
                                <h2 class="text-lg font-bold text-upwork-dark">Improve Your Profile</h2>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Complete these sections to strengthen your profile</p>
                            <div class="space-y-3">
                                <div 
                                    v-for="suggestion in suggestions" 
                                    :key="suggestion.title"
                                    class="flex items-center justify-between p-4 bg-yellow-50 rounded-xl group hover:bg-yellow-100 transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-yellow-100 rounded-lg">
                                            <component :is="getIcon(suggestion.icon)" class="h-5 w-5 text-yellow-600" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 text-sm">{{ suggestion.title }}</h3>
                                            <p class="text-xs text-gray-600">{{ suggestion.message }}</p>
                                        </div>
                                    </div>
                                    <Link 
                                        v-if="suggestion.action_route"
                                        :href="route(suggestion.action_route)" 
                                        class="upwork-btn-primary text-xs px-3 py-1.5"
                                    >
                                        {{ suggestion.action_text }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Platform Services Grid -->
                        <div v-if="platformServices.length > 0" class="upwork-card p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-bold text-upwork-dark">Your Services & Tools</h2>
                                <Link :href="route('services.index')" class="text-sm text-upwork-green hover:text-upwork-green-dark font-medium">
                                    Browse All →
                                </Link>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                <Link 
                                    v-for="service in platformServices" 
                                    :key="service.name"
                                    :href="route(service.route, service.params || {})"
                                    class="group p-4 rounded-xl border transition-all hover:shadow-md"
                                    :class="service.is_featured ? 'border-orange-200 bg-orange-50 hover:bg-orange-100' : 'border-gray-100 bg-gray-50 hover:bg-white hover:border-upwork-green'"
                                >
                                    <div class="p-2 rounded-lg inline-block mb-3" :class="service.bgColor">
                                        <component 
                                            :is="getIcon(service.icon)" 
                                            class="h-5 w-5"
                                            :class="service.textColor"
                                        />
                                    </div>
                                    <h3 class="font-semibold text-sm text-gray-900 group-hover:text-upwork-green transition-colors">
                                        {{ service.name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ service.description }}</p>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- CTA Card - Browse Services -->
                        <div class="upwork-card p-6 bg-gradient-to-br from-upwork-green to-upwork-green-dark text-white">
                            <div class="p-3 bg-white/20 rounded-xl inline-block mb-4">
                                <GlobeAltIcon class="h-8 w-8 text-white" />
                            </div>
                            <h3 class="text-lg font-bold mb-2">Explore Visa Services</h3>
                            <p class="text-sm text-white/80 mb-4">
                                Discover visa services, travel insurance, flight bookings, and more.
                            </p>
                            <Link 
                                :href="route('services.index')"
                                class="inline-flex items-center px-4 py-2 bg-white text-upwork-green rounded-full font-semibold text-sm hover:bg-gray-100 transition-colors"
                            >
                                Browse Services
                                <ChevronRightIcon class="h-4 w-4 ml-1" />
                            </Link>
                        </div>

                        <!-- Sidebar Ad -->
                        <AdDisplay 
                            placement="sidebar" 
                            page="dashboard"
                        />

                        <!-- Leaderboard Section -->
                        <div v-if="topReferrers && topReferrers.length > 0" class="upwork-card p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <TrophyIcon class="h-5 w-5 text-yellow-500" />
                                <h2 class="text-lg font-bold text-upwork-dark">Top Referrers</h2>
                            </div>
                            <div class="space-y-3">
                                <div 
                                    v-for="(referrer, index) in topReferrers.slice(0, 5)" 
                                    :key="referrer.id" 
                                    class="flex items-center justify-between p-3 rounded-lg"
                                    :class="userRank && userRank.user_id === referrer.id ? 'bg-upwork-lightest' : 'bg-gray-50'"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg">
                                            <span v-if="index === 0">🥇</span>
                                            <span v-else-if="index === 1">🥈</span>
                                            <span v-else-if="index === 2">🥉</span>
                                            <span v-else class="text-sm font-medium text-gray-500">{{ index + 1 }}</span>
                                        </span>
                                        <span class="font-medium text-sm text-gray-900">{{ referrer.name }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-upwork-green">{{ referrer.total_referrals }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
