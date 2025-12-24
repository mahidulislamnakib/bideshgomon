<template>
    <AuthenticatedLayout>
        <Head title="Compare Packages" />

        <div class="min-h-screen bg-gray-50 dark:bg-neutral-950">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Package Comparison</h1>
                    <p class="text-sm text-white/80 mt-0.5">Compare features, pricing, and benefits</p>
                    
                    <!-- Filters in Hero -->
                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <select
                            v-model="filters.service"
                            @change="applyFilters"
                            class="px-3 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50"
                        >
                            <option value="">All Services</option>
                            <option v-for="service in services" :key="service.id" :value="service.slug">
                                {{ service.name }}
                            </option>
                        </select>
                        <input
                            v-model.number="filters.min_price"
                            @change="applyFilters"
                            type="number"
                            placeholder="Min ৳"
                            class="w-24 px-3 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50"
                        />
                        <input
                            v-model.number="filters.max_price"
                            @change="applyFilters"
                            type="number"
                            placeholder="Max ৳"
                            class="w-24 px-3 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50"
                        />
                        <label class="flex items-center gap-2 text-sm text-white cursor-pointer">
                            <input
                                v-model="filters.popular_first"
                                @change="applyFilters"
                                type="checkbox"
                                class="rounded border-0 text-growth-600 focus:ring-white/50"
                            />
                            Popular First
                        </label>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearAllFilters"
                            class="px-3 py-2 text-sm font-medium text-white/80 hover:text-white transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
                <!-- Results Count -->
                <p class="text-xs text-gray-500 dark:text-gray-400 py-4">{{ packages.length }} packages found</p>

                <!-- No Results -->
                <div v-if="packages.length === 0" class="text-center py-12">
                    <FunnelIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No packages found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters</p>
                    <button
                        @click="clearAllFilters"
                        class="mt-4 px-4 py-2 text-sm font-medium text-growth-600 hover:text-growth-700"
                    >
                        Clear Filters
                    </button>
                </div>

                <!-- Comparison Table (Desktop) -->
                <div v-else class="hidden lg:block bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="sticky left-0 z-10 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                                    Feature
                                </th>
                                <th
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    class="px-6 py-3 text-center min-w-[250px]"
                                >
                                    <div class="space-y-2">
                                        <!-- Badge -->
                                        <span
                                            v-if="pkg.badge_text"
                                            :class="getBadgeClass(pkg.badge_color)"
                                            class="inline-block text-xs font-bold px-2 py-1 rounded-full"
                                        >
                                            {{ pkg.badge_text }}
                                        </span>

                                        <!-- Package Name -->
                                        <h3 class="text-lg font-bold text-gray-900">{{ pkg.name }}</h3>

                                        <!-- Service -->
                                        <p class="text-xs text-gray-500">{{ pkg.service_module.name }}</p>

                                        <!-- Price -->
                                        <div class="text-2xl font-bold text-growth-600">
                                            ৳{{ pkg.price.toLocaleString() }}
                                            <span v-if="pkg.price_unit !== 'fixed'" class="text-sm text-gray-500">
                                                / {{ pkg.price_unit.replace('_', ' ') }}
                                            </span>
                                        </div>

                                        <!-- Discount -->
                                        <div v-if="pkg.original_price" class="text-sm">
                                            <span class="line-through text-gray-400">৳{{ pkg.original_price.toLocaleString() }}</span>
                                            <span class="ml-2 text-green-600 font-medium">{{ pkg.discount_percentage }}% OFF</span>
                                        </div>

                                        <!-- Spots Left -->
                                        <div v-if="pkg.spots_left !== null && pkg.spots_left <= 10" class="text-xs text-red-600 font-medium">
                                            Only {{ pkg.spots_left }} spots left!
                                        </div>

                                        <!-- CTA Button -->
                                        <Link
                                            :href="route('packages.show', pkg.slug)"
                                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-growth-600 hover:bg-growth-700 w-full"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Features Row -->
                            <tr v-for="(feature, index) in allFeatures" :key="index">
                                <td class="sticky left-0 z-10 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r">
                                    {{ feature }}
                                </td>
                                <td
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    class="px-6 py-4 whitespace-nowrap text-center"
                                >
                                    <CheckCircleIcon
                                        v-if="pkg.features && pkg.features.includes(feature)"
                                        class="h-6 w-6 text-green-500 mx-auto"
                                    />
                                    <XCircleIcon
                                        v-else
                                        class="h-6 w-6 text-gray-300 mx-auto"
                                    />
                                </td>
                            </tr>

                            <!-- Specifications Rows (if present) -->
                            <template v-if="hasSpecifications">
                                <tr class="bg-gray-50">
                                    <td colspan="100" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Specifications
                                    </td>
                                </tr>
                                <tr v-for="(spec, index) in uniqueSpecKeys" :key="`spec-${index}`">
                                    <td class="sticky left-0 z-10 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r">
                                        {{ spec }}
                                    </td>
                                    <td
                                        v-for="pkg in packages"
                                        :key="`spec-${pkg.id}`"
                                        class="px-6 py-4 text-center text-sm text-gray-700"
                                    >
                                        {{ getSpecValue(pkg, spec) || '-' }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden space-y-6">
                    <div
                        v-for="pkg in packages"
                    :key="pkg.id"
                    class="bg-white rounded-lg shadow-sm overflow-hidden"
                >
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-sky-50 to-sky-100 p-6">
                        <span
                            v-if="pkg.badge_text"
                            :class="getBadgeClass(pkg.badge_color)"
                            class="inline-block text-xs font-bold px-2 py-1 rounded-full mb-2"
                        >
                            {{ pkg.badge_text }}
                        </span>
                        <h3 class="text-xl font-bold text-gray-900">{{ pkg.name }}</h3>
                        <p class="text-sm text-gray-600">{{ pkg.service_module.name }}</p>
                        
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-growth-600">
                                ৳{{ pkg.price.toLocaleString() }}
                                <span v-if="pkg.price_unit !== 'fixed'" class="text-base text-gray-500">
                                    / {{ pkg.price_unit.replace('_', ' ') }}
                                </span>
                            </div>
                            <div v-if="pkg.original_price" class="mt-1">
                                <span class="line-through text-gray-400">৳{{ pkg.original_price.toLocaleString() }}</span>
                                <span class="ml-2 text-green-600 font-medium">{{ pkg.discount_percentage }}% OFF</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <!-- Features -->
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Features</h4>
                        <ul class="space-y-2 mb-6">
                            <li
                                v-for="(feature, index) in pkg.features"
                                :key="index"
                                class="flex items-start space-x-2"
                            >
                                <CheckCircleIcon class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                                <span class="text-sm text-gray-700">{{ feature }}</span>
                            </li>
                        </ul>

                        <!-- Spots Left -->
                        <div v-if="pkg.spots_left !== null && pkg.spots_left <= 10" class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                            <p class="text-sm text-red-800 font-medium text-center">
                                ⚠️ Only {{ pkg.spots_left }} spots left!
                            </p>
                        </div>

                        <!-- CTA -->
                        <Link
                            :href="route('packages.show', pkg.slug)"
                            class="block text-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-growth-600 hover:bg-growth-700"
                        >
                            View Full Details
                        </Link>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    FunnelIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    packages: Array,
    services: Array,
    allFeatures: Array,
    filters: Object,
});

const filters = ref({
    service: props.filters.service || '',
    min_price: props.filters.min_price || null,
    max_price: props.filters.max_price || null,
    popular_first: props.filters.popular_first || false,
});

const hasActiveFilters = computed(() => {
    return filters.value.service || filters.value.min_price || filters.value.max_price;
});

const hasSpecifications = computed(() => {
    return props.packages.some(pkg => pkg.specifications && Object.keys(pkg.specifications).length > 0);
});

const uniqueSpecKeys = computed(() => {
    const keys = new Set();
    props.packages.forEach(pkg => {
        if (pkg.specifications) {
            Object.keys(pkg.specifications).forEach(key => keys.add(key));
        }
    });
    return Array.from(keys);
});

function applyFilters() {
    router.get(route('packages.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilter(filterKey) {
    filters.value[filterKey] = filterKey === 'popular_first' ? false : null;
    applyFilters();
}

function clearAllFilters() {
    filters.value = {
        service: '',
        min_price: null,
        max_price: null,
        popular_first: false,
    };
    applyFilters();
}

function getServiceName(slug) {
    const service = props.services.find(s => s.slug === slug);
    return service ? service.name : slug;
}

function getBadgeClass(color) {
    const colors = {
        blue: 'bg-sky-100 text-sky-800',
        green: 'bg-green-100 text-green-800',
        purple: 'bg-purple-100 text-purple-800',
        red: 'bg-red-100 text-red-800',
        yellow: 'bg-yellow-100 text-yellow-800',
    };
    return colors[color] || colors.blue;
}

function getSpecValue(pkg, specKey) {
    return pkg.specifications?.[specKey] || null;
}
</script>
