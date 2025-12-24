<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    MapIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    ArrowDownTrayIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    LinkIcon,
    CheckCircleIcon,
    ClockIcon,
    BoltIcon,
    ClipboardDocumentIcon,
    ArrowTopRightOnSquareIcon,
    XMarkIcon,
    MinusCircleIcon,
    PlusCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    routes: Object,
    stats: Object,
});

const searchQuery = ref('');
const methodFilter = ref('');
const routeTypeFilter = ref('');
const expandedCategories = ref(Object.keys(props.routes));
const testedRoutes = ref(new Set(JSON.parse(localStorage.getItem('testedRoutes') || '[]')));
const isTesting = ref(false);
const showFilters = ref(false);

const filteredRoutes = computed(() => {
    let filtered = { ...props.routes };

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = Object.fromEntries(
            Object.entries(filtered).map(([category, routes]) => [
                category,
                routes.filter(route =>
                    route.uri.toLowerCase().includes(query) ||
                    route.name?.toLowerCase().includes(query) ||
                    route.action.toLowerCase().includes(query) ||
                    category.toLowerCase().includes(query)
                )
            ]).filter(([_, routes]) => routes.length > 0)
        );
    }

    if (methodFilter.value) {
        filtered = Object.fromEntries(
            Object.entries(filtered).map(([category, routes]) => [
                category,
                routes.filter(route => route.methods.includes(methodFilter.value))
            ]).filter(([_, routes]) => routes.length > 0)
        );
    }

    if (routeTypeFilter.value) {
        filtered = Object.fromEntries(
            Object.entries(filtered).map(([category, routes]) => [
                category,
                routes.filter(route => {
                    switch (routeTypeFilter.value) {
                        case 'static': return !route.has_params;
                        case 'dynamic': return route.has_params;
                        case 'testable': return !route.has_params && route.methods.includes('GET');
                        case 'tested': return testedRoutes.value.has(route.uri);
                        case 'untested': return !testedRoutes.value.has(route.uri) && !route.has_params && route.methods.includes('GET');
                        default: return true;
                    }
                })
            ]).filter(([_, routes]) => routes.length > 0)
        );
    }

    return filtered;
});

const totalFilteredRoutes = computed(() => {
    return Object.values(filteredRoutes.value).flat().length;
});

const testableFilteredCount = computed(() => {
    return Object.values(filteredRoutes.value)
        .flat()
        .filter(route => !route.has_params && route.methods.includes('GET'))
        .length;
});

const hasActiveFilters = computed(() => {
    return searchQuery.value || methodFilter.value || routeTypeFilter.value;
});

const toggleCategory = (category) => {
    const index = expandedCategories.value.indexOf(category);
    if (index > -1) {
        expandedCategories.value.splice(index, 1);
    } else {
        expandedCategories.value.push(category);
    }
};

const collapseAll = () => {
    expandedCategories.value = [];
};

const expandAll = () => {
    expandedCategories.value = Object.keys(filteredRoutes.value);
};

const markAsTested = (uri) => {
    testedRoutes.value.add(uri);
    localStorage.setItem('testedRoutes', JSON.stringify([...testedRoutes.value]));
};

const unmarkAsTested = (uri) => {
    testedRoutes.value.delete(uri);
    localStorage.setItem('testedRoutes', JSON.stringify([...testedRoutes.value]));
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        // Could add toast notification here
    });
};

const exportToJson = () => {
    const data = {
        exported_at: new Date().toISOString(),
        total_routes: props.stats?.total_routes || 0,
        categories: Object.keys(props.routes).length,
        routes: props.routes
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `admin-sitemap-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

const clearFilters = () => {
    searchQuery.value = '';
    methodFilter.value = '';
    routeTypeFilter.value = '';
};

const testCategoryLinks = (categoryRoutes) => {
    const testableRoutes = categoryRoutes.filter(
        route => !route.has_params && route.methods.includes('GET')
    );

    if (testableRoutes.length === 0) return;

    if (confirm(`Test ${testableRoutes.length} links from this category?`)) {
        testableRoutes.slice(0, 5).forEach((route, index) => {
            setTimeout(() => {
                window.open(route.url, '_blank');
                markAsTested(route.uri);
            }, index * 300);
        });

        if (testableRoutes.length > 5) {
            alert(`Opened first 5 links. ${testableRoutes.length - 5} remaining.`);
        }
    }
};

const testFilteredLinks = () => {
    if (isTesting.value) return;

    const testableRoutes = Object.values(filteredRoutes.value)
        .flat()
        .filter(route => !route.has_params && route.methods.includes('GET'));

    if (testableRoutes.length === 0) return;

    const maxToOpen = Math.min(testableRoutes.length, 10);
    if (confirm(`Test ${maxToOpen} of ${testableRoutes.length} filtered links?`)) {
        isTesting.value = true;
        
        testableRoutes.slice(0, maxToOpen).forEach((route, index) => {
            setTimeout(() => {
                window.open(route.url, '_blank');
                markAsTested(route.uri);
                
                if (index === maxToOpen - 1) {
                    setTimeout(() => {
                        isTesting.value = false;
                    }, 500);
                }
            }, index * 400);
        });
    }
};

const getMethodColor = (methods) => {
    if (methods.includes('GET')) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
    if (methods.includes('POST')) return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
    if (methods.includes('PUT')) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    if (methods.includes('DELETE')) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
    return 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300';
};
</script>

<template>
    <Head title="Admin Sitemap" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- SVG Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="sitemapGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#sitemapGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-teal-500/20 to-cyan-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-2xl shadow-lg">
                                <MapIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Admin Sitemap</h1>
                                <p class="mt-1 text-gray-300">Complete list of all admin routes - Test and verify all links</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                            </button>
                            <button
                                @click="exportToJson"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                            >
                                <ArrowDownTrayIcon class="h-5 w-5" />
                                Export
                            </button>
                            <button
                                @click="collapseAll"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                            >
                                <MinusCircleIcon class="h-5 w-5" />
                                Collapse
                            </button>
                            <button
                                @click="expandAll"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-cyan-600 text-white rounded-xl font-semibold hover:from-teal-600 hover:to-cyan-700 transition-all shadow-lg"
                            >
                                <PlusCircleIcon class="h-5 w-5" />
                                Expand All
                            </button>
                        </div>
                    </div>

                    <!-- Stats Cards in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-teal-500/20 rounded-xl">
                                    <MapIcon class="h-5 w-5 text-teal-300" />
                                </div>
                                <div>
                                    <p class="text-teal-300 text-xs font-medium">Total Routes</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total_routes || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-xl">
                                    <LinkIcon class="h-5 w-5 text-green-300" />
                                </div>
                                <div>
                                    <p class="text-green-300 text-xs font-medium">Categories</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.categories || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-cyan-500/20 rounded-xl">
                                    <BoltIcon class="h-5 w-5 text-cyan-300" />
                                </div>
                                <div>
                                    <p class="text-cyan-300 text-xs font-medium">Static Routes</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.without_params || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-xl">
                                    <BoltIcon class="h-5 w-5 text-purple-300" />
                                </div>
                                <div>
                                    <p class="text-purple-300 text-xs font-medium">Dynamic</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.with_params || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-500/20 rounded-xl">
                                    <CheckCircleIcon class="h-5 w-5 text-amber-300" />
                                </div>
                                <div>
                                    <p class="text-amber-300 text-xs font-medium">Tested</p>
                                    <p class="text-2xl font-bold text-white">{{ testedRoutes.size }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <!-- Filters Panel -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Routes</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search by URI, name, action..."
                                        class="w-full pl-10 pr-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-teal-500 dark:bg-neutral-700 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">HTTP Method</label>
                                <select
                                    v-model="methodFilter"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-teal-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Methods</option>
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Route Type</label>
                                <select
                                    v-model="routeTypeFilter"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-teal-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Types</option>
                                    <option value="static">Static Only</option>
                                    <option value="dynamic">Dynamic Only</option>
                                    <option value="testable">Testable Only</option>
                                    <option value="tested">Tested Only</option>
                                    <option value="untested">Untested Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-semibold text-gray-900 dark:text-white">{{ totalFilteredRoutes }}</span>
                                of <span class="font-semibold text-gray-900 dark:text-white">{{ stats?.total_routes || 0 }}</span> routes
                            </p>
                            <div class="flex gap-3">
                                <button
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    class="px-4 py-2 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors"
                                >
                                    Clear Filters
                                </button>
                                <button
                                    @click="testFilteredLinks"
                                    :disabled="testableFilteredCount === 0 || isTesting"
                                    class="px-5 py-2 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-medium transition-colors"
                                >
                                    <span v-if="isTesting" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Testing...
                                    </span>
                                    <span v-else>Test {{ testableFilteredCount }} Links</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Routes by Category -->
                <div class="space-y-4">
                    <div
                        v-for="(categoryRoutes, category) in filteredRoutes"
                        :key="category"
                        class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden"
                    >
                        <!-- Category Header -->
                        <button 
                            @click="toggleCategory(category)"
                            class="w-full bg-gray-50 dark:bg-neutral-900/50 px-6 py-4 border-b border-gray-200 dark:border-neutral-700 hover:bg-gray-100 dark:hover:bg-neutral-700/50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <ChevronRightIcon
                                        :class="[
                                            'h-5 w-5 text-gray-500 transition-transform',
                                            expandedCategories.includes(category) ? 'rotate-90' : ''
                                        ]"
                                    />
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ category }}</h2>
                                    <span class="px-2.5 py-1 bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 rounded-full text-xs font-semibold">
                                        {{ categoryRoutes.length }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="flex items-center gap-1.5 text-green-600 dark:text-green-400">
                                        <CheckCircleIcon class="h-4 w-4" />
                                        {{ categoryRoutes.filter(r => !r?.has_params && r.methods.includes('GET')).length }} testable
                                    </span>
                                    <span class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                                        <ClockIcon class="h-4 w-4" />
                                        {{ categoryRoutes.filter(r => testedRoutes.has(r.uri)).length }} tested
                                    </span>
                                    <span class="flex items-center gap-1.5 text-purple-600 dark:text-purple-400">
                                        <BoltIcon class="h-4 w-4" />
                                        {{ categoryRoutes.filter(r => r?.has_params).length }} dynamic
                                    </span>
                                </div>
                            </div>
                        </button>

                        <!-- Routes List -->
                        <div v-if="expandedCategories.includes(category)" class="divide-y divide-gray-100 dark:divide-neutral-700">
                            <div
                                v-for="route in categoryRoutes"
                                :key="route.uri"
                                :class="[
                                    'px-6 py-4 transition-colors',
                                    testedRoutes.has(route.uri) ? 'bg-green-50 dark:bg-green-900/10 hover:bg-green-100 dark:hover:bg-green-900/20' : 'hover:bg-gray-50 dark:hover:bg-neutral-700/50'
                                ]"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                                            <span :class="['px-2.5 py-1 text-xs font-semibold rounded-xl', getMethodColor(route.methods)]">
                                                {{ route.methods }}
                                            </span>
                                            <span v-if="route.name" class="text-sm text-gray-600 dark:text-gray-400 font-mono">
                                                {{ route.name }}
                                            </span>
                                            <span v-if="route.has_params" class="px-2 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 text-xs font-medium rounded-xl flex items-center gap-1">
                                                <BoltIcon class="h-3 w-3" />
                                                Dynamic
                                            </span>
                                            <span v-if="testedRoutes.has(route.uri)" class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 text-xs font-medium rounded-xl flex items-center gap-1">
                                                <CheckCircleIcon class="h-3 w-3" />
                                                Tested
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2 mb-2">
                                            <code class="text-sm font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-neutral-700 px-3 py-1.5 rounded-xl">
                                                {{ route.uri }}
                                            </code>
                                            <button
                                                @click="copyToClipboard(route.uri)"
                                                class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-neutral-600"
                                                title="Copy URI"
                                            >
                                                <ClipboardDocumentIcon class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                            {{ route.action }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a
                                            v-if="!route.has_params && route.methods.includes('GET')"
                                            :href="route.url"
                                            target="_blank"
                                            @click="markAsTested(route.uri)"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-50 hover:bg-teal-100 dark:bg-teal-900/30 dark:hover:bg-teal-900/50 text-teal-700 dark:text-teal-300 text-sm font-medium rounded-xl transition-colors"
                                        >
                                            <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                            Test
                                        </a>
                                        <button
                                            v-if="testedRoutes.has(route.uri)"
                                            @click="unmarkAsTested(route.uri)"
                                            class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-neutral-600"
                                            title="Unmark as tested"
                                        >
                                            <XMarkIcon class="h-4 w-4" />
                                        </button>
                                        <span v-if="route.has_params || !route.methods.includes('GET')" class="text-xs text-gray-400 italic px-3 py-2">
                                            {{ route.has_params ? 'Needs params' : 'Not GET' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="Object.keys(filteredRoutes).length === 0" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 px-6 py-16 text-center">
                        <div class="p-4 bg-teal-100 dark:bg-teal-900/30 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                            <MapIcon class="h-8 w-8 text-teal-600 dark:text-teal-400" />
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No routes found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or filters</p>
                        <button
                            @click="clearFilters"
                            class="mt-4 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium transition-colors"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
