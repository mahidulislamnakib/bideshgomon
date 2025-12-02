<template>
    <AuthenticatedLayout>
        <Head title="Compare Packages" />

        <!-- Header -->
        <div class="bg-gradient-to-r from-sky-600 to-sky-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h1 class="text-3xl font-bold mb-2">Package Comparison</h1>
                <p class="text-sky-100">Compare features, pricing, and benefits to find your perfect package</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Service Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
                        <select
                            v-model="filters.service"
                            @change="applyFilters"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-brand-red-600"
                        >
                            <option value="">All Services</option>
                            <option v-for="service in services" :key="service.id" :value="service.slug">
                                {{ service.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Min Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Price</label>
                        <input
                            v-model.number="filters.min_price"
                            @change="applyFilters"
                            type="number"
                            placeholder="0"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-brand-red-600"
                        />
                    </div>

                    <!-- Max Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Price</label>
                        <input
                            v-model.number="filters.max_price"
                            @change="applyFilters"
                            type="number"
                            placeholder="100000"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-brand-red-600"
                        />
                    </div>

                    <!-- Popular First -->
                    <div class="flex items-end">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input
                                v-model="filters.popular_first"
                                @change="applyFilters"
                                type="checkbox"
                                class="rounded border-gray-300 text-brand-red-600 focus:ring-brand-red-600"
                            />
                            <span class="text-sm font-medium text-gray-700">Popular First</span>
                        </label>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div v-if="hasActiveFilters" class="mt-4 flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Active filters:</span>
                    <button
                        v-if="filters.service"
                        @click="clearFilter('service')"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800"
                    >
                        {{ getServiceName(filters.service) }}
                        <XMarkIcon class="h-3 w-3 ml-1" />
                    </button>
                    <button
                        v-if="filters.min_price"
                        @click="clearFilter('min_price')"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800"
                    >
                        Min: ৳{{ filters.min_price.toLocaleString() }}
                        <XMarkIcon class="h-3 w-3 ml-1" />
                    </button>
                    <button
                        v-if="filters.max_price"
                        @click="clearFilter('max_price')"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800"
                    >
                        Max: ৳{{ filters.max_price.toLocaleString() }}
                        <XMarkIcon class="h-3 w-3 ml-1" />
                    </button>
                    <button
                        @click="clearAllFilters"
                        class="text-sm text-brand-red-600 hover:text-sky-700 font-medium"
                    >
                        Clear All
                    </button>
                </div>
            </div>

            <!-- No Results -->
            <div v-if="packages.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                <FunnelIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No packages found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters</p>
                <button
                    @click="clearAllFilters"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700"
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
                                        <div class="text-2xl font-bold text-brand-red-600">
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
                                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700 w-full"
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
                            <div class="text-3xl font-bold text-brand-red-600">
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
                            class="block text-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700"
                        >
                            View Full Details
                        </Link>
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
