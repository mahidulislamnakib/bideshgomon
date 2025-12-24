<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import BulkActionsBar from '@/Components/ui/BulkActionsBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { debounce } from '@/Composables/useDebouncedSearch';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { useBulkSelection } from '@/Composables/useBulkSelection';
import { useConfirm } from '@/Composables/useConfirm'
import {
    BuildingOffice2Icon,
    MagnifyingGlassIcon,
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    StarIcon,
    HomeModernIcon,
    CurrencyBangladeshiIcon,
    MapPinIcon,
    FunnelIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

const props = defineProps({
    hotels: Object,
    stats: Object,
    filters: Object,
});

const { selectedItems: selectedHotels, allSelected, toggleItem, toggleAll, isSelected, hasSelection, selectionCount, clearSelection } = useBulkSelection({
    getItems: () => props.hotels?.data || [],
})

const { confirmBulk, confirmDelete } = useConfirm()

const search = ref(props.filters.search || '');
const city = ref(props.filters.city || '');
const status = ref(props.filters.status || '');
const showFilters = ref(false);
const loading = ref(false);

const applyFilters = () => {
    router.get(route('admin.hotels.index'), {
        search: search.value,
        city: city.value,
        status: status.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedApplyFilters = debounce(() => applyFilters(), 400);

watch(search, () => {
    debouncedApplyFilters();
});

const clearFilters = () => {
    search.value = '';
    city.value = '';
    status.value = '';
    applyFilters();
};

const hasActiveFilters = () => search.value || city.value || status.value;

const deleteHotel = async (id) => {
    const confirmed = await confirmDelete('this hotel')
    if (confirmed) {
        router.delete(route('admin.hotels.destroy', id))
    }
};

const toggleStatus = (id) => {
    router.post(route('admin.hotels.toggle-status', id));
};

// Bulk actions
const bulkActivate = async () => {
    const confirmed = await confirmBulk('activate', selectionCount.value, 'hotel')
    if (confirmed) {
        router.post(route('admin.hotels.bulk-status'), {
            ids: selectedHotels.value,
            status: true,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkDeactivate = async () => {
    const confirmed = await confirmBulk('deactivate', selectionCount.value, 'hotel')
    if (confirmed) {
        router.post(route('admin.hotels.bulk-status'), {
            ids: selectedHotels.value,
            status: false,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkDelete = async () => {
    const confirmed = await confirmBulk('delete', selectionCount.value, 'hotel')
    if (confirmed) {
        router.post(route('admin.hotels.bulk-delete'), {
            ids: selectedHotels.value,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkActions = computed(() => [
    { key: 'activate', label: 'Activate', icon: 'CheckCircleIcon', variant: 'success', handler: bulkActivate },
    { key: 'deactivate', label: 'Deactivate', icon: 'XCircleIcon', variant: 'warning', handler: bulkDeactivate },
    { key: 'delete', label: 'Delete', icon: 'TrashIcon', variant: 'danger', handler: bulkDelete },
])

// Keyboard shortcuts
const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts()

const pageShortcuts = [
    { key: 'c', description: 'Create new hotel', action: () => router.visit(route('admin.hotels.create')) },
    { key: '/', description: 'Focus search', action: () => document.querySelector('input[type="search"], input[placeholder*="Search"]')?.focus() },
    { key: 'r', description: 'Refresh page', action: () => router.reload() },
]

registerShortcuts(pageShortcuts)
</script>

<template>
    <Head title="Hotel Management" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Hotels', href: null }
            ]" />
        </template>

        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <!-- Main Content -->
        <div v-else class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Modern Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Background pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>
                
                <!-- Gradient orbs -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-500/30 to-blue-600/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-purple-500/30 to-purple-400/20 rounded-full blur-3xl"></div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <BuildingOffice2Icon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-sm font-medium text-gray-300 uppercase tracking-wider">Admin</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2">Hotel Management</h1>
                            <p class="text-sm sm:text-base text-gray-300">Manage hotels and room inventory across all locations</p>
                        </div>
                        <Link
                            :href="route('admin.hotels.create')"
                            class="inline-flex items-center px-5 py-2.5 rounded-xl font-semibold gap-2 shadow-lg transition-all hover:scale-105"
                            style="background: linear-gradient(135deg, #14a800 0%, #108a00 100%); color: white;"
                        >
                            <PlusIcon class="h-5 w-5" />
                            Add Hotel
                        </Link>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/30 rounded-xl">
                                    <BuildingOffice2Icon class="h-5 w-5 text-blue-300" />
                                </div>
                                <div>
                                    <p class="text-gray-300 text-sm">Total Hotels</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-500/20 backdrop-blur-sm rounded-xl p-4 border border-green-400/30">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/30 rounded-xl">
                                    <CheckCircleIcon class="h-5 w-5 text-green-300" />
                                </div>
                                <div>
                                    <p class="text-green-300 text-sm">Active</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.active }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/30 rounded-xl">
                                    <HomeModernIcon class="h-5 w-5 text-purple-300" />
                                </div>
                                <div>
                                    <p class="text-gray-300 text-sm">Total Rooms</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.total_rooms }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-500/30 rounded-xl">
                                    <CurrencyBangladeshiIcon class="h-5 w-5 text-amber-300" />
                                </div>
                                <div>
                                    <p class="text-gray-300 text-sm">Avg Price</p>
                                    <p class="text-2xl font-bold text-white">৳{{ stats.avg_price }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Search & Filters -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            <input
                                v-model="search"
                                @keyup.enter="applyFilters"
                                @input="debouncedApplyFilters"
                                type="text"
                                placeholder="Search hotels by name..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-neutral-800 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            />
                        </div>
                        <button
                            @click="showFilters = !showFilters"
                            :class="[
                                'inline-flex items-center px-4 py-2.5 rounded-xl font-medium transition-colors',
                                hasActiveFilters()
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                    : 'bg-white dark:bg-neutral-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700'
                            ]"
                        >
                            <FunnelIcon class="h-5 w-5 mr-2" />
                            Filters
                            <span v-if="hasActiveFilters()" class="ml-2 bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full">Active</span>
                        </button>
                    </div>

                    <!-- Expanded Filters -->
                    <div v-show="showFilters" class="mt-4 bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                                <input
                                    v-model="city"
                                    type="text"
                                    placeholder="Filter by city..."
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-neutral-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select
                                    v-model="status"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-neutral-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button
                                    @click="applyFilters"
                                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-medium transition-colors"
                                >
                                    Apply
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="px-4 py-2 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-2xl transition-colors"
                                >
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Select All Header -->
                <div v-if="hotels.data.length > 0" class="bg-white dark:bg-neutral-800 rounded-xl p-4 mb-4 flex items-center gap-4 border border-neutral-100 dark:border-neutral-700">
                    <input
                        type="checkbox"
                        :checked="allSelected"
                        :indeterminate="hasSelection && !allSelected"
                        @change="toggleAll"
                        class="h-5 w-5 text-blue-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-neutral-700"
                    />
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ allSelected ? 'Deselect all' : 'Select all' }} ({{ hotels.data.length }} hotels)
                    </span>
                </div>

                <!-- Bulk Actions Bar -->
                <BulkActionsBar
                    :count="selectionCount"
                    item-label="hotel"
                    :actions="bulkActions"
                    @clear="clearSelection"
                />

                <!-- Hotels Grid -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="hotels.data.length === 0"
                        icon="BuildingOfficeIcon"
                        title="No hotels found"
                        description="Add hotels to your platform to offer accommodation booking services."
                        :action="{
                            label: 'Add Hotel',
                            onClick: () => router.visit(route('admin.hotels.create')),
                        }"
                    />

                    <!-- Hotels List -->
                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="hotel in hotels.data"
                            :key="hotel.id"
                            class="p-6 hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors"
                        >
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(hotel.id)"
                                        @change="toggleItem(hotel.id)"
                                        class="h-5 w-5 text-blue-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-neutral-700 flex-shrink-0 mt-1"
                                    />
                                    <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ hotel.name }}</h3>
                                        <span
                                            :class="hotel.is_active 
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400'"
                                            class="px-2.5 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ hotel.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <!-- Star Rating -->
                                        <div class="flex items-center gap-1">
                                            <StarIconSolid v-for="i in hotel.star_rating" :key="i" class="h-4 w-4 text-amber-400" />
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        <MapPinIcon class="h-4 w-4" />
                                        <span>{{ hotel.city }}, {{ hotel.country }}</span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span>{{ hotel.address }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-4 text-sm">
                                        <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                            <HomeModernIcon class="h-4 w-4" />
                                            {{ hotel.rooms_count }} rooms
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                            <CurrencyBangladeshiIcon class="h-4 w-4" />
                                            From ৳{{ hotel.price_per_night }}/night
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                            <StarIcon class="h-4 w-4" />
                                            {{ hotel.rating }} ({{ hotel.total_reviews }} reviews)
                                        </span>
                                    </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <Link
                                        :href="route('admin.hotels.rooms', hotel.id)"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-2xl transition-colors"
                                    >
                                        <HomeModernIcon class="h-4 w-4 mr-1" />
                                        Rooms
                                    </Link>
                                    <Link
                                        :href="route('admin.hotels.edit', hotel.id)"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                                    >
                                        <PencilIcon class="h-4 w-4 mr-1" />
                                        Edit
                                    </Link>
                                    <button
                                        @click="toggleStatus(hotel.id)"
                                        :class="hotel.is_active 
                                            ? 'text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/30' 
                                            : 'text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30'"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-2xl transition-colors"
                                    >
                                        <component :is="hotel.is_active ? XCircleIcon : CheckCircleIcon" class="h-4 w-4 mr-1" />
                                        {{ hotel.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        @click="deleteHotel(hotel.id)"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-2xl transition-colors"
                                    >
                                        <TrashIcon class="h-4 w-4 mr-1" />
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="hotels.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-neutral-900">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-medium text-gray-900 dark:text-white">{{ hotels.from }}</span> to <span class="font-medium text-gray-900 dark:text-white">{{ hotels.to }}</span> of <span class="font-medium text-gray-900 dark:text-white">{{ hotels.total }}</span> hotels
                            </div>
                            <nav class="flex gap-1">
                                <Link
                                    v-for="(link, index) in hotels.links"
                                    :key="index"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded-2xl text-sm font-medium transition-colors',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                            ? 'bg-white dark:bg-neutral-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700'
                                            : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                                    ]"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts Modal -->
        <KeyboardShortcutsModal
            v-model:show="showHelp"
            :shortcuts="pageShortcuts"
            :global-shortcuts="globalShortcuts"
        />
    </AdminLayout>
</template>
