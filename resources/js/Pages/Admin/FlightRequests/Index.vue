<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    PaperAirplaneIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    UserGroupIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    EyeIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    flightRequests: {
        type: Object,
        required: true
    },
    agencies: {
        type: Array,
        required: true
    },
    filter: {
        type: String,
        default: 'all'
    },
    search: {
        type: String,
        default: ''
    },
    stats: {
        type: Object,
        required: true
    }
});

const { formatDate } = useBangladeshFormat();

const showFilters = ref(false);
const searchQuery = ref(props.search);
const selectedRequests = ref([]);
const bulkAssignAgency = ref('');

const assignModal = ref({
    show: false,
    request: null,
    agencyId: ''
});

const allSelected = computed(() => {
    return props.flightRequests.data.length > 0 && 
           selectedRequests.value.length === props.flightRequests.data.length;
});

const hasActiveFilters = computed(() => {
    return props.filter !== 'all' || searchQuery.value;
});

let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('admin.flight-requests.index'), {
        filter: props.filter,
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    router.get(route('admin.flight-requests.index'));
};

const toggleAll = () => {
    if (allSelected.value) {
        selectedRequests.value = [];
    } else {
        selectedRequests.value = props.flightRequests.data.map(r => r.id);
    }
};

const clearSelection = () => {
    selectedRequests.value = [];
    bulkAssignAgency.value = '';
};

const bulkAssign = () => {
    if (!bulkAssignAgency.value || selectedRequests.value.length === 0) return;

    router.post(route('admin.flight-requests.bulk-assign'), {
        request_ids: selectedRequests.value,
        agency_id: bulkAssignAgency.value
    }, {
        onSuccess: () => {
            clearSelection();
        }
    });
};

const showAssignModal = (request) => {
    assignModal.value = {
        show: true,
        request: request,
        agencyId: ''
    };
};

const closeAssignModal = () => {
    assignModal.value = {
        show: false,
        request: null,
        agencyId: ''
    };
};

const assignRequest = () => {
    if (!assignModal.value.agencyId) return;

    router.post(route('admin.flight-requests.assign', assignModal.value.request.id), {
        agency_id: assignModal.value.agencyId
    }, {
        onSuccess: () => {
            closeAssignModal();
        }
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        'assigned': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'quoted': 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        'completed': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'cancelled': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300';
};
</script>

<template>
    <Head title="Flight Requests Management" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- SVG Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="flightReqGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#flightReqGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-sky-500/20 to-blue-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-indigo-500/20 to-sky-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl shadow-lg">
                                <PaperAirplaneIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Flight Requests</h1>
                                <p class="mt-1 text-gray-300">Manage and assign flight requests to agencies</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                {{ showFilters ? 'Hide' : 'Show' }} Filters
                                <span v-if="hasActiveFilters" class="ml-1 px-2 py-0.5 bg-sky-500 text-white text-xs rounded-full font-semibold">
                                    Active
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Cards in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-sky-500/20 rounded-xl">
                                    <ClipboardDocumentListIcon class="h-5 w-5 text-sky-300" />
                                </div>
                                <div>
                                    <p class="text-sky-300 text-xs font-medium">Total Requests</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-xl">
                                    <ClockIcon class="h-5 w-5 text-yellow-300" />
                                </div>
                                <div>
                                    <p class="text-yellow-300 text-xs font-medium">Pending</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.pending }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-xl">
                                    <UserGroupIcon class="h-5 w-5 text-blue-300" />
                                </div>
                                <div>
                                    <p class="text-blue-300 text-xs font-medium">Assigned</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.assigned }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-xl">
                                    <DocumentTextIcon class="h-5 w-5 text-purple-300" />
                                </div>
                                <div>
                                    <p class="text-purple-300 text-xs font-medium">Quoted</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.quoted }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-xl">
                                    <CheckCircleIcon class="h-5 w-5 text-green-300" />
                                </div>
                                <div>
                                    <p class="text-green-300 text-xs font-medium">Completed</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.completed }}</p>
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
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                    <div class="relative">
                                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                        <input
                                            v-model="searchQuery"
                                            @input="handleSearch"
                                            @keyup.enter="applyFilters"
                                            type="text"
                                            placeholder="Search by reference, airport codes, or user..."
                                            class="w-full pl-10 pr-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-sky-500 dark:bg-neutral-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-end gap-3">
                                    <button
                                        v-if="hasActiveFilters"
                                        @click="clearFilters"
                                        class="px-5 py-3 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors"
                                    >
                                        <XMarkIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Tabs -->
                            <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200 dark:border-neutral-700">
                                <Link
                                    :href="route('admin.flight-requests.index')"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                                        filter === 'all'
                                            ? 'bg-sky-600 text-white shadow-sm'
                                            : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-600'
                                    ]"
                                >
                                    All
                                </Link>
                                <Link
                                    :href="route('admin.flight-requests.index', { filter: 'pending' })"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                                        filter === 'pending'
                                            ? 'bg-sky-600 text-white shadow-sm'
                                            : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-600'
                                    ]"
                                >
                                    Pending
                                </Link>
                                <Link
                                    :href="route('admin.flight-requests.index', { filter: 'assigned' })"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                                        filter === 'assigned'
                                            ? 'bg-sky-600 text-white shadow-sm'
                                            : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-600'
                                    ]"
                                >
                                    Assigned
                                </Link>
                                <Link
                                    :href="route('admin.flight-requests.index', { filter: 'quoted' })"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                                        filter === 'quoted'
                                            ? 'bg-sky-600 text-white shadow-sm'
                                            : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-600'
                                    ]"
                                >
                                    Quoted
                                </Link>
                                <Link
                                    :href="route('admin.flight-requests.index', { filter: 'completed' })"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                                        filter === 'completed'
                                            ? 'bg-sky-600 text-white shadow-sm'
                                            : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-600'
                                    ]"
                                >
                                    Completed
                                </Link>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Bulk Actions -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="selectedRequests.length > 0" class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl shadow-sm border border-blue-200 dark:border-blue-800 p-4 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
                                {{ selectedRequests.length }} request(s) selected
                            </span>
                            <div class="flex items-center gap-3">
                                <select 
                                    v-model="bulkAssignAgency" 
                                    class="px-4 py-2 border-0 ring-1 ring-blue-300 dark:ring-blue-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white text-sm"
                                >
                                    <option value="">Select agency...</option>
                                    <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                                        {{ agency.name }}
                                    </option>
                                </select>
                                <button
                                    @click="bulkAssign"
                                    :disabled="!bulkAssignAgency"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-medium transition-colors"
                                >
                                    Assign Selected
                                </button>
                                <button 
                                    @click="clearSelection" 
                                    class="px-4 py-2 border border-blue-300 dark:border-blue-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-medium transition-colors"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Requests Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left">
                                        <input
                                            type="checkbox"
                                            @change="toggleAll"
                                            :checked="allSelected"
                                            class="rounded text-sky-600 focus:ring-sky-500 dark:bg-neutral-700 dark:border-neutral-600"
                                        />
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Route</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Travel Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Budget</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Quotes</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="request in flightRequests.data" :key="request.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            v-model="selectedRequests"
                                            :value="request.id"
                                            class="rounded text-sky-600 focus:ring-sky-500 dark:bg-neutral-700 dark:border-neutral-600"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900/30 dark:to-blue-900/30 rounded-xl flex items-center justify-center">
                                                <PaperAirplaneIcon class="h-5 w-5 text-sky-600 dark:text-sky-400" />
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ request.request_reference }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ request.user?.name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ request.user?.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ request.route_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ formatDate(request.travel_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ request.formatted_budget }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(request.status)"
                                            class="px-3 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ (request?.status || '').replace('_', ' ').toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="request.assigned_to" class="text-sm text-gray-900 dark:text-white">
                                            {{ request.assigned_to.name }}
                                        </div>
                                        <div v-else class="text-sm text-gray-400 dark:text-gray-500">Not assigned</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="request.quotes_count > 0" class="px-2.5 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 rounded-full text-xs font-semibold">
                                            {{ request.quotes_count }}
                                        </span>
                                        <span v-else class="text-xs text-gray-400 dark:text-gray-500">0</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Link
                                                :href="route('admin.flight-requests.show', request.id)"
                                                class="p-2 text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-xl transition-colors"
                                                title="View"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <button
                                                v-if="request.status === 'pending'"
                                                @click="showAssignModal(request)"
                                                class="p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-xl transition-colors"
                                                title="Assign"
                                            >
                                                <UserGroupIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="!flightRequests.data || flightRequests.data.length === 0">
                                    <td colspan="8" class="px-6 py-16">
                                        <EmptyState
                                            icon="GlobeAltIcon"
                                            title="No flight requests"
                                            description="Flight requests will appear here once users submit them."
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="flightRequests.data && flightRequests.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-medium text-gray-900 dark:text-white">{{ flightRequests.from }}</span>
                                to <span class="font-medium text-gray-900 dark:text-white">{{ flightRequests.to }}</span>
                                of <span class="font-medium text-gray-900 dark:text-white">{{ flightRequests.total }}</span> requests
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="flightRequests.prev_page_url"
                                    :href="flightRequests.prev_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <Link
                                    v-if="flightRequests.next_page_url"
                                    :href="flightRequests.next_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assign Modal -->
                <div v-if="assignModal.show" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl max-w-md w-full">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assign Request</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Assign request <span class="font-medium text-gray-900 dark:text-white">{{ assignModal.request?.request_reference }}</span> to an agency
                            </p>
                            
                            <select
                                v-model="assignModal.agencyId"
                                class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-sky-500 dark:bg-neutral-700 dark:text-white mb-6"
                            >
                                <option value="">Select agency...</option>
                                <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                                    {{ agency.name }} ({{ agency.email }})
                                </option>
                            </select>

                            <div class="flex justify-end gap-3">
                                <button
                                    @click="closeAssignModal"
                                    class="px-5 py-2.5 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="assignRequest"
                                    :disabled="!assignModal.agencyId"
                                    class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-medium transition-colors"
                                >
                                    Assign
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
