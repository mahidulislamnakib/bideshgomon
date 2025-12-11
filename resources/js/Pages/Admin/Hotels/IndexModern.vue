<template>
    <AdminLayout>
        <Head title="Hotel Management" />

        <div class="page-container">
            <!-- Page Header -->
            <PageHeader
                title="Hotel Management"
                description="Manage hotels, rooms, and accommodation inventory across destinations"
                :primaryAction="{
                    label: 'Add Hotel',
                    onClick: () => router.visit(route('admin.hotels.create')),
                    icon: PlusIcon
                }"
                :secondaryActions="[
                    { label: 'Room Categories', onClick: () => router.visit(route('admin.hotel-rooms.categories')), icon: Squares2X2Icon },
                    { label: 'Export List', onClick: exportHotels, icon: DocumentArrowDownIcon }
                ]"
            />

            <!-- Statistics Grid -->
            <div class="grid-stats mb-8">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <BuildingOffice2Icon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Hotels</p>
                        <p class="stat-card-value">{{ stats.total }}</p>
                        <p class="stat-card-change text-gray-600">{{ stats.active }} active</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-green-100">
                        <CheckCircleIcon class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Active Hotels</p>
                        <p class="stat-card-value">{{ stats.active }}</p>
                        <p class="stat-card-change text-green-600">
                            {{ Math.round((stats.active / stats.total) * 100) }}% active
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <HomeIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Rooms</p>
                        <p class="stat-card-value">{{ stats.total_rooms }}</p>
                        <p class="stat-card-change text-purple-600">Across all hotels</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-yellow-100">
                        <CurrencyDollarIcon class="h-6 w-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Avg Price/Night</p>
                        <p class="stat-card-value">{{ formatCurrency(stats.avg_price) }}</p>
                        <p class="stat-card-change text-yellow-600">Per room</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="card-base mb-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <FormInput
                            v-model="search"
                            placeholder="Search hotels by name, city, or address..."
                            :icon="MagnifyingGlassIcon"
                            class="flex-1"
                            @input="handleSearch"
                            @enter="applyFilters"
                        />
                        <button
                            @click="showFilters = !showFilters"
                            class="btn-secondary flex items-center justify-center gap-2"
                        >
                            <FunnelIcon class="h-5 w-5" />
                            Filters
                            <span v-if="hasActiveFilters" class="ml-1 px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-semibold">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Filters Panel -->
                    <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <FormInput
                                v-model="city"
                                placeholder="Enter city name"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="statusFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white" @change="applyFilters">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Star Rating</label>
                            <select v-model="starRating" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white" @change="applyFilters">
                                <option value="">All Ratings</option>
                                <option value="5">5 Star</option>
                                <option value="4">4 Star</option>
                                <option value="3">3 Star</option>
                                <option value="2">2 Star</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button @click="clearFilters" class="btn-secondary w-full">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Info -->
            <div v-if="hasActiveFilters" class="mb-4 text-sm text-gray-600">
                Showing {{ hotels.from }} to {{ hotels.to }} of {{ hotels.total }} results
            </div>

            <!-- Hotels List -->
            <div class="space-y-4">
                <div
                    v-for="hotel in hotels.data"
                    :key="hotel.id"
                    class="card-hover p-6"
                >
                    <div class="flex flex-col lg:flex-row gap-6">
                        <!-- Hotel Image -->
                        <div class="flex-shrink-0">
                            <img
                                v-if="hotel.featured_image"
                                :src="hotel.featured_image"
                                :alt="hotel.name"
                                class="w-full lg:w-48 h-48 object-cover rounded-lg shadow-md"
                            />
                            <div v-else class="w-full lg:w-48 h-48 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center">
                                <BuildingOffice2Icon class="h-16 w-16 text-blue-400" />
                            </div>
                        </div>

                        <!-- Hotel Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-xl font-bold text-gray-900">{{ hotel.name }}</h3>
                                        <StatusBadge
                                            :status="hotel.is_active ? 'active' : 'inactive'"
                                            size="sm"
                                        />
                                    </div>
                                    
                                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                                        <MapPinIcon class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ hotel.address }}, {{ hotel.city }}, {{ hotel.country }}</span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 mb-4">
                                        <!-- Star Rating -->
                                        <div class="flex items-center gap-1">
                                            <div class="flex">
                                                <StarIcon
                                                    v-for="star in 5"
                                                    :key="star"
                                                    :class="star <= hotel.star_rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                                                    class="h-5 w-5"
                                                />
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ hotel.star_rating }} Star</span>
                                        </div>

                                        <!-- Guest Rating -->
                                        <div v-if="hotel.rating" class="flex items-center gap-1 text-sm">
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded font-semibold">
                                                ★ {{ hotel.rating }}
                                            </span>
                                            <span class="text-gray-600">({{ hotel.total_reviews }} reviews)</span>
                                        </div>

                                        <!-- Room Count -->
                                        <div class="flex items-center gap-1 text-sm text-gray-600">
                                            <HomeIcon class="h-4 w-4" />
                                            <span class="font-medium">{{ hotel.rooms_count }} rooms</span>
                                        </div>
                                    </div>

                                    <!-- Amenities -->
                                    <div v-if="hotel.amenities && hotel.amenities.length > 0" class="flex flex-wrap gap-2 mb-4">
                                        <span
                                            v-for="amenity in hotel.amenities.slice(0, 5)"
                                            :key="amenity"
                                            class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-lg"
                                        >
                                            {{ amenity }}
                                        </span>
                                        <span v-if="hotel.amenities.length > 5" class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-lg">
                                            +{{ hotel.amenities.length - 5 }} more
                                        </span>
                                    </div>

                                    <!-- Price -->
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-bold text-red-600">{{ formatCurrency(hotel.price_per_night) }}</span>
                                        <span class="text-sm text-gray-600">per night</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-col gap-2">
                                    <Link
                                        :href="route('admin.hotels.rooms', hotel.id)"
                                        class="btn-primary text-center whitespace-nowrap"
                                    >
                                        <HomeIcon class="h-4 w-4 inline mr-1" />
                                        Rooms
                                    </Link>
                                    <Link
                                        :href="route('admin.hotels.edit', hotel.id)"
                                        class="btn-secondary text-center whitespace-nowrap"
                                    >
                                        <PencilIcon class="h-4 w-4 inline mr-1" />
                                        Edit
                                    </Link>
                                    <button
                                        @click="toggleStatus(hotel.id)"
                                        class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors whitespace-nowrap"
                                        :class="hotel.is_active 
                                            ? 'border-yellow-200 text-yellow-700 bg-yellow-50 hover:bg-yellow-100' 
                                            : 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100'"
                                    >
                                        {{ hotel.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        @click="deleteHotel(hotel.id)"
                                        class="px-4 py-2 text-sm font-medium rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100 transition-colors"
                                    >
                                        <TrashIcon class="h-4 w-4 inline mr-1" />
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <EmptyState
                v-if="hotels.data.length === 0"
                icon="BuildingOffice2Icon"
                :title="hasActiveFilters ? 'No hotels match your filters' : 'No hotels yet'"
                :description="hasActiveFilters ? 'Try adjusting your search or filter criteria' : 'Start adding hotels to your accommodation inventory'"
                :action="hasActiveFilters ? null : { label: 'Add First Hotel', onClick: () => router.visit(route('admin.hotels.create')) }"
            />

            <!-- Pagination -->
            <div v-if="hotels.data.length > 0" class="mt-6 card-base">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-semibold">{{ hotels.from }}</span> to <span class="font-semibold">{{ hotels.to }}</span> of <span class="font-semibold">{{ hotels.total }}</span> hotels
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in hotels.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                link.active
                                    ? 'bg-red-600 text-white shadow-sm'
                                    : link.url
                                    ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            ]"
                            :disabled="!link.url"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    BuildingOffice2Icon,
    CheckCircleIcon,
    HomeIcon,
    CurrencyDollarIcon,
    MapPinIcon,
    StarIcon,
    PencilIcon,
    TrashIcon,
    Squares2X2Icon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    hotels: Object,
    stats: Object,
    filters: Object,
});

const { formatCurrency } = useBangladeshFormat();

// State
const search = ref(props.filters.search || '');
const city = ref(props.filters.city || '');
const statusFilter = ref(props.filters.status || '');
const starRating = ref(props.filters.star_rating || '');
const showFilters = ref(false);

// Computed
const hasActiveFilters = computed(() => {
    return !!(search.value || city.value || statusFilter.value || starRating.value);
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (city.value) count++;
    if (statusFilter.value) count++;
    if (starRating.value) count++;
    return count;
});

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
    router.get(route('admin.hotels.index'), {
        search: search.value,
        city: city.value,
        status: statusFilter.value,
        star_rating: starRating.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    city.value = '';
    statusFilter.value = '';
    starRating.value = '';
    applyFilters();
};

// Toggle hotel status
const toggleStatus = (id) => {
    router.post(route('admin.hotels.toggle-status', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by flash message
        }
    });
};

// Delete hotel
const deleteHotel = (id) => {
    if (confirm('Are you sure you want to delete this hotel? This will also delete all associated rooms and bookings.')) {
        router.delete(route('admin.hotels.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Export hotels
const exportHotels = () => {
    router.get(route('admin.hotels.export'), {
        search: search.value,
        city: city.value,
        status: statusFilter.value,
        star_rating: starRating.value,
    }, {
        preserveScroll: true,
    });
};
</script>
