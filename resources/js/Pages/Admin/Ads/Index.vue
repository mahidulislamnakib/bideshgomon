<template>
    <AdminLayout>
        <Head title="Ad Management" />

        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <!-- Main Content -->
        <template v-else>
        <!-- Modern Hero Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                                <RectangleGroupIcon class="h-8 w-8 text-white" />
                            </div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                                Ad Management
                            </h1>
                        </div>
                        <p class="text-white/90 text-lg max-w-2xl">
                            Create and manage targeted advertisements across your platform with real-time analytics
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <Link
                            :href="route('admin.ads.create')"
                            class="inline-flex items-center px-6 py-3.5 bg-white hover:bg-gray-50 text-growth-600 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold gap-2"
                        >
                            <PlusIcon class="h-5 w-5" />
                            Create New Ad
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-8">
            <!-- Modern Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Ads -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                <RectangleGroupIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-xs font-semibold text-growth-600 bg-blue-50 px-3 py-1 rounded-full">Total</span>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Ads</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ stats.total }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            {{ stats.active }} active campaigns
                        </p>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                </div>

                <!-- Active Ads -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                <CheckCircleIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Live</span>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active Ads</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ stats.active }}</p>
                        <p class="text-sm text-green-600 font-medium">Currently running</p>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>
                </div>

                <!-- Impressions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                <EyeIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Views</span>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Impressions</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ formatNumber(stats.total_impressions) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">All-time views</p>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
                </div>

                <!-- Clicks & CTR -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                <CursorArrowRaysIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1 rounded-full">CTR</span>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Clicks</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ formatNumber(stats.total_clicks) }}</p>
                        <p class="text-sm font-semibold" :class="stats.avg_ctr > 2 ? 'text-green-600' : 'text-amber-600'">
                            {{ stats.avg_ctr ? stats.avg_ctr.toFixed(2) : 0 }}% avg CTR
                        </p>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-600"></div>
                </div>
            </div>

            <!-- Modern Search & Filters Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search Input -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                v-model="search"
                                @input="handleSearch"
                                type="text"
                                placeholder="Search ads by title or body..."
                                class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent transition-all"
                            />
                        </div>
                        
                        <!-- Filter Button -->
                        <button
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all font-medium"
                            :class="hasActiveFilters ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'text-gray-700'"
                        >
                            <FunnelIcon class="h-5 w-5" />
                            Filters
                            <span v-if="hasActiveFilters" class="ml-1 px-2.5 py-0.5 bg-indigo-500 text-white text-xs rounded-full font-semibold">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Filters Panel -->
                    <div v-if="showFilters" class="pt-4 border-t border-gray-200 dark:border-gray-700 animate-fadeIn">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select v-model="statusFilter" @change="applyFilters" class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-growth-600 focus:border-transparent">
                                    <option value="">All Status</option>
                                    <option value="active">🟢 Active</option>
                                    <option value="paused">⏸️ Paused</option>
                                    <option value="draft">📝 Draft</option>
                                    <option value="expired">⏰ Expired</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Placement</label>
                                <select v-model="placementFilter" @change="applyFilters" class="w-full px-4 py-2.5 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-growth-600 focus:border-transparent">
                                    <option value="">All Placements</option>
                                    <option value="sidebar">📍 Sidebar</option>
                                    <option value="inline">📄 Inline</option>
                                    <option value="empty_state">🎯 Empty State</option>
                                    <option value="banner">🎌 Banner</option>
                                    <option value="sticky_bottom">📌 Sticky Bottom</option>
                                    <option value="modal">💬 Modal</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button @click="clearFilters" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-2xl transition-all">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Info -->
            <div v-if="hasActiveFilters" class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-2xl px-4 py-2 inline-flex items-center gap-2">
                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                    Showing {{ ads.from }} to {{ ads.to }} of {{ ads.total }} results
                </p>
            </div>

            <!-- Modern Ads Grid/Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                <!-- Table Header -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Advertisement Campaigns</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Ad</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Placement</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Performance</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="ads.data.length > 0" class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="ad in ads.data" :key="ad.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-4">
                                        <!-- Ad Image -->
                                        <div v-if="ad.image_url" class="flex-shrink-0">
                                            <img
                                                :src="getImageUrl(ad.image_url)"
                                                :alt="ad.title"
                                                class="w-20 h-20 object-cover rounded-xl shadow-md hover:scale-105 transition-transform"
                                            />
                                        </div>
                                        <div v-else class="flex-shrink-0">
                                            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shadow-md">
                                                <PhotoIcon class="h-10 w-10 text-gray-400" />
                                            </div>
                                        </div>
                                        
                                        <!-- Ad Details -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 line-clamp-1">{{ ad.title }}</h3>
                                            <p v-if="ad.body" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ ad.body }}</p>
                                            <div v-if="ad.cta_text" class="inline-flex items-center gap-1.5 text-xs text-growth-600 font-medium bg-indigo-50 px-2.5 py-1 rounded-2xl">
                                                <LinkIcon class="h-3.5 w-3.5" />
                                                {{ ad.cta_text }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-2xl text-xs font-semibold bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-sm">
                                        {{ getPlacementLabel(ad.placement) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-2xl text-xs font-semibold shadow-sm"
                                        :class="{
                                            'bg-green-100 text-green-800': ad.status === 'active',
                                            'bg-yellow-100 text-yellow-800': ad.status === 'paused',
                                            'bg-gray-100 text-gray-800': ad.status === 'draft',
                                            'bg-red-100 text-red-800': ad.status === 'expired'
                                        }"
                                    >
                                        <span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5" :class="{
                                            'bg-green-600 animate-pulse': ad.status === 'active',
                                            'bg-yellow-600': ad.status === 'paused',
                                            'bg-gray-600': ad.status === 'draft',
                                            'bg-red-600': ad.status === 'expired'
                                        }"></span>
                                        {{ ad.status.charAt(0).toUpperCase() + ad.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs space-y-1">
                                        <div v-if="ad.start_at" class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                            <CalendarIcon class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                                            <span class="font-medium">{{ formatDate(ad.start_at) }}</span>
                                        </div>
                                        <div v-if="ad.end_at" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                            <span class="text-gray-400">→</span>
                                            <span>{{ formatDate(ad.end_at) }}</span>
                                        </div>
                                        <div v-if="!ad.start_at && !ad.end_at" class="text-gray-400 italic">
                                            No schedule
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1.5 text-xs">
                                                <div class="p-1 bg-purple-100 rounded">
                                                    <EyeIcon class="h-3.5 w-3.5 text-purple-600" />
                                                </div>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ formatNumber(ad.impressions) }}</span>
                                                <span class="text-gray-500 dark:text-gray-400">views</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1.5 text-xs">
                                                <div class="p-1 bg-amber-100 rounded">
                                                    <CursorArrowRaysIcon class="h-3.5 w-3.5 text-amber-600" />
                                                </div>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ formatNumber(ad.clicks) }}</span>
                                                <span class="text-gray-500 dark:text-gray-400">clicks</span>
                                            </div>
                                        </div>
                                        <div v-if="ad.impressions > 0" class="flex items-center gap-1.5 text-xs">
                                            <div class="p-1 bg-green-100 rounded">
                                                <ChartBarIcon class="h-3.5 w-3.5 text-green-600" />
                                            </div>
                                            <span class="font-bold" :class="ad.ctr > 2 ? 'text-green-600' : 'text-amber-600'">{{ ad.ctr.toFixed(2) }}%</span>
                                            <span class="text-gray-500 dark:text-gray-400">CTR</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                                        <span class="text-lg font-bold text-white">{{ ad.priority }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            v-if="ad && ad.id"
                                            :href="route('admin.ads.analytics', ad.id)"
                                            class="p-2.5 text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-2xl transition-all"
                                            title="View Analytics"
                                        >
                                            <ChartBarIcon class="h-5 w-5" />
                                        </Link>
                                        <Link
                                            v-if="ad && ad.id"
                                            :href="route('admin.ads.edit', ad.id)"
                                            class="p-2.5 text-growth-600 bg-blue-50 hover:bg-blue-100 rounded-2xl transition-all"
                                            title="Edit"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click="toggleStatus(ad)"
                                            class="p-2.5 rounded-2xl transition-all"
                                            :class="ad.status === 'active' 
                                                ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' 
                                                : 'text-green-600 bg-green-50 hover:bg-green-100'"
                                            :title="ad.status === 'active' ? 'Pause' : 'Activate'"
                                        >
                                            <PowerIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click="deleteAd(ad)"
                                            class="p-2.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-2xl transition-all"
                                            title="Delete"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="ads.data.length === 0"
                    icon="RectangleGroupIcon"
                    :title="hasActiveFilters ? 'No ads match your filters' : 'No ads created yet'"
                    :description="hasActiveFilters ? 'Try adjusting your search or filter criteria' : 'Start creating targeted ads to promote services across your platform'"
                    :action="hasActiveFilters ? null : { label: 'Create First Ad', onClick: () => router.visit(route('admin.ads.create')) }"
                />

                <!-- Pagination -->
                <div v-if="ads.data.length > 0" class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                            Showing <span class="font-bold text-growth-600">{{ ads.from }}</span> to <span class="font-bold text-growth-600">{{ ads.to }}</span> of <span class="font-bold text-growth-600">{{ ads.total }}</span> ads
                        </div>
                        <div class="flex gap-2">
                            <component
                                v-for="link in ads.links"
                                :key="link.label"
                                :is="link.url ? Link : 'span'"
                                :href="link.url || undefined"
                                :class="[
                                    'px-4 py-2 text-sm font-semibold rounded-2xl transition-all',
                                    link.active
                                        ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg scale-105'
                                        : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-100 border-2 border-gray-300 hover:border-indigo-500'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed border-2 border-gray-200'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    RectangleGroupIcon,
    CheckCircleIcon,
    EyeIcon,
    CursorArrowRaysIcon,
    ChartBarIcon,
    PencilIcon,
    TrashIcon,
    PowerIcon,
    PhotoIcon,
    LinkIcon,
    CalendarIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    ads: Object,
    stats: Object,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();

// State
const loading = ref(false);
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const placementFilter = ref(props.filters.placement || '');
const showFilters = ref(false);

// Computed
const hasActiveFilters = computed(() => {
    return !!(search.value || statusFilter.value || placementFilter.value);
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (statusFilter.value) count++;
    if (placementFilter.value) count++;
    return count;
});

// Get image URL
const getImageUrl = (url) => {
    if (url.startsWith('http')) return url;
    return `/storage/${url}`;
};

// Get placement label
const getPlacementLabel = (placement) => {
    const labels = {
        'sidebar': 'Sidebar',
        'inline': 'Inline',
        'empty_state': 'Empty State',
        'banner': 'Banner',
        'sticky_bottom': 'Sticky Bottom',
        'modal': 'Modal'
    };
    return labels[placement] || placement;
};

// Format numbers
const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num || 0;
};

// Search with debounce
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

// Apply filters
const applyFilters = () => {
    router.get(route('admin.ads.index'), {
        search: search.value,
        status: statusFilter.value,
        placement: placementFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    placementFilter.value = '';
    applyFilters();
};

// Toggle ad status
const toggleStatus = (ad) => {
    const action = ad.status === 'active' ? 'pause' : 'activate';
    if (confirm(`Are you sure you want to ${action} this ad?`)) {
        router.post(route('admin.ads.toggle-status', ad.id), {}, {
            preserveScroll: true,
        });
    }
};

// Delete ad
const deleteAd = (ad) => {
    if (confirm(`Are you sure you want to delete "${ad.title}"? This action cannot be undone.`)) {
        router.delete(route('admin.ads.destroy', ad.id), {
            preserveScroll: true,
        });
    }
};
</script>

<!-- Removed scoped styles - using Tailwind utility classes instead -->
