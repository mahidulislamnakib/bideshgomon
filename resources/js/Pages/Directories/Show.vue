<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BuildingOffice2Icon, MapPinIcon, PhoneIcon, EnvelopeIcon,
    GlobeAltIcon, ArrowLeftIcon, ClockIcon, EyeIcon
} from '@heroicons/vue/24/outline';
import { 
    CheckBadgeIcon as CheckBadgeSolidIcon, 
    StarIcon as StarSolidIcon 
} from '@heroicons/vue/24/solid';

const props = defineProps({
    directory: Object,
    relatedDirectories: Array
});

const getCategoryColor = (color) => {
    const colors = {
        blue: '#3B82F6', indigo: '#6366F1', green: '#10B981',
        red: '#EF4444', purple: '#8B5CF6', yellow: '#F59E0B', gray: '#6B7286',
    };
    return colors[color] || '#6B7286';
};

const formatOperatingHours = (hours) => {
    if (!hours) return null;
    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    return days.map(day => ({
        day: day.charAt(0).toUpperCase() + day.slice(1, 3),
        fullDay: day.charAt(0).toUpperCase() + day.slice(1),
        hours: hours[day] || 'Closed'
    }));
};

// Check if currently open (Bangladesh Time)
const isOpenNow = (hours) => {
    if (!hours) return false;
    const now = new Date();
    // Convert to Bangladesh time (UTC+6)
    const bdTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Dhaka' }));
    const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const currentDay = dayNames[bdTime.getDay()];
    const todayHours = hours[currentDay];
    
    if (!todayHours || todayHours === 'Closed') return false;
    
    // Parse hours like "9:00 AM - 6:00 PM"
    const match = todayHours.match(/(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i);
    if (!match) return false;
    
    const toMinutes = (h, m, ampm) => {
        let hours = parseInt(h);
        if (ampm.toUpperCase() === 'PM' && hours !== 12) hours += 12;
        if (ampm.toUpperCase() === 'AM' && hours === 12) hours = 0;
        return hours * 60 + parseInt(m);
    };
    
    const openMinutes = toMinutes(match[1], match[2], match[3]);
    const closeMinutes = toMinutes(match[4], match[5], match[6]);
    const currentMinutes = bdTime.getHours() * 60 + bdTime.getMinutes();
    
    return currentMinutes >= openMinutes && currentMinutes <= closeMinutes;
};
</script>

<template>
    <Head :title="directory.name" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Header -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-4 sm:px-6">
                <div class="max-w-5xl mx-auto">
                    <Link :href="route('directories.index')" 
                        class="inline-flex items-center gap-1 text-xs text-white/70 hover:text-white mb-2">
                        <ArrowLeftIcon class="h-3 w-3" />
                        Back
                    </Link>

                    <div class="flex items-start gap-4">
                        <div class="h-14 w-14 flex items-center justify-center bg-white rounded-lg overflow-hidden flex-shrink-0">
                            <img v-if="directory.logo_url" :src="directory.logo_url" :alt="directory.name" class="h-full w-full object-cover"/>
                            <BuildingOffice2Icon v-else class="h-7 w-7 text-gray-400" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h1 class="text-lg md:text-xl font-bold text-white truncate">{{ directory.name }}</h1>
                                <CheckBadgeSolidIcon v-if="directory.is_verified" class="h-5 w-5 text-blue-300 flex-shrink-0" />
                                <StarSolidIcon v-if="directory.is_featured" class="h-5 w-5 text-yellow-300 flex-shrink-0" />
                            </div>
                            
                            <div class="flex items-center gap-3 mt-1 text-xs text-white/70">
                                <span v-if="directory.category" class="px-2 py-0.5 rounded bg-white/20 text-white">
                                    {{ directory.category.name }}
                                </span>
                                <span v-if="directory.country" class="flex items-center gap-1">
                                    <MapPinIcon class="h-3 w-3" />
                                    {{ directory.country.name }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <EyeIcon class="h-3 w-3" />
                                    {{ directory.views_count || 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-5">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Main Column -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Description -->
                        <div class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-4">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">About</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ directory.description }}</p>
                        </div>

                        <!-- Services -->
                        <div v-if="directory.services && directory.services.length > 0" 
                            class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-4">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Services</h2>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="service in directory.services" :key="service"
                                    class="px-2 py-1 rounded text-xs bg-growth-50 dark:bg-growth-900/30 text-growth-700 dark:text-growth-300">
                                    {{ service }}
                                </span>
                            </div>
                        </div>

                        <!-- Operating Hours -->
                        <div v-if="directory.operating_hours" 
                            class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <ClockIcon class="h-4 w-4 text-growth-600" />
                                    Hours
                                </h2>
                                <span v-if="isOpenNow(directory.operating_hours)" 
                                    class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full">
                                    Open Now
                                </span>
                                <span v-else 
                                    class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full">
                                    Closed
                                </span>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center">
                                <div v-for="item in formatOperatingHours(directory.operating_hours)" :key="item.day"
                                    class="text-xs py-2 rounded-lg" :class="item.hours !== 'Closed' ? 'bg-gray-50 dark:bg-neutral-700/50' : ''">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ item.day }}</div>
                                    <div class="mt-1" :class="item.hours === 'Closed' ? 'text-red-500' : 'text-gray-600 dark:text-gray-400'">
                                        <template v-if="item.hours === 'Closed'">✕</template>
                                        <template v-else>
                                            <div v-for="(part, idx) in item.hours.split(' - ')" :key="idx" class="leading-tight">
                                                {{ part }}
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image -->
                        <div v-if="directory.image_url" class="rounded-lg overflow-hidden">
                            <img :src="directory.image_url" :alt="directory.name" class="w-full h-48 object-cover" />
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-4">
                        <!-- Contact -->
                        <div class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-4">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Contact</h2>
                            <div class="space-y-2.5 text-sm">
                                <div v-if="directory.address" class="flex items-start gap-2">
                                    <MapPinIcon class="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-gray-600 dark:text-gray-400">{{ directory.address }}</span>
                                </div>
                                <div v-if="directory.phone" class="flex items-center gap-2">
                                    <PhoneIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                    <a :href="`tel:${directory.phone}`" class="text-growth-600 hover:underline">{{ directory.phone }}</a>
                                </div>
                                <div v-if="directory.email" class="flex items-center gap-2">
                                    <EnvelopeIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                    <a :href="`mailto:${directory.email}`" class="text-growth-600 hover:underline truncate">{{ directory.email }}</a>
                                </div>
                                <div v-if="directory.website" class="flex items-center gap-2">
                                    <GlobeAltIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                    <a :href="directory.website" target="_blank" class="text-growth-600 hover:underline">Website</a>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a v-if="directory.phone" :href="`tel:${directory.phone}`"
                                    class="inline-flex items-center justify-center px-3 py-2 text-xs bg-growth-600 text-white rounded-lg font-medium hover:bg-growth-700">
                                    <PhoneIcon class="h-3.5 w-3.5 mr-1" />
                                    Call
                                </a>
                                <a v-if="directory.email" :href="`mailto:${directory.email}`"
                                    class="inline-flex items-center justify-center px-3 py-2 text-xs bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200">
                                    <EnvelopeIcon class="h-3.5 w-3.5 mr-1" />
                                    Email
                                </a>
                            </div>
                        </div>

                        <!-- Map -->
                        <div v-if="directory.latitude && directory.longitude" 
                            class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-4">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Location</h2>
                            <div class="rounded overflow-hidden bg-gray-100 dark:bg-neutral-700">
                                <iframe 
                                    :src="`https://www.google.com/maps?q=${directory.latitude},${directory.longitude}&output=embed`"
                                    width="100%" height="150" style="border:0;" loading="lazy">
                                </iframe>
                            </div>
                            <a :href="`https://www.google.com/maps/dir/?api=1&destination=${directory.latitude},${directory.longitude}`" 
                                target="_blank"
                                class="mt-2 w-full inline-flex items-center justify-center px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100">
                                Get Directions
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Related -->
                <div v-if="relatedDirectories && relatedDirectories.length > 0" class="mt-8">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Related</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <Link v-for="related in relatedDirectories" :key="related.id"
                            :href="route('directories.show', related.slug)"
                            class="group bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-3 hover:shadow-md transition-all">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 flex items-center justify-center bg-gray-100 dark:bg-neutral-700 rounded overflow-hidden flex-shrink-0">
                                    <img v-if="related.logo_url" :src="related.logo_url" :alt="related.name" class="h-full w-full object-cover"/>
                                    <BuildingOffice2Icon v-else class="h-4 w-4 text-gray-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xs font-medium text-gray-900 dark:text-white group-hover:text-growth-600 truncate">
                                        {{ related.name }}
                                    </h3>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
