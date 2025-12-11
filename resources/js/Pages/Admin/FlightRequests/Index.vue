<template>
    <AdminLayout>
        <Head title="Flight Requests Management" />

        <div class="page-container">
            <!-- Page Header -->
            <PageHeader
                title="Flight Requests"
                description="Manage and assign flight requests to agencies"
                icon="PaperAirplaneIcon"
            />

            <!-- Statistics Grid -->
            <div class="grid-stats mb-8">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <ClipboardDocumentListIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Requests</p>
                        <p class="stat-card-value">{{ stats.total }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-yellow-100">
                        <ClockIcon class="h-6 w-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Pending</p>
                        <p class="stat-card-value">{{ stats.pending }}</p>
                        <p class="stat-card-change text-yellow-600">Awaiting assignment</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <UserGroupIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Assigned</p>
                        <p class="stat-card-value">{{ stats.assigned }}</p>
                        <p class="stat-card-change text-blue-600">To agencies</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <DocumentTextIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Quoted</p>
                        <p class="stat-card-value">{{ stats.quoted }}</p>
                        <p class="stat-card-change text-purple-600">Offers received</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-green-100">
                        <CheckCircleIcon class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Completed</p>
                        <p class="stat-card-value">{{ stats.completed }}</p>
                        <p class="stat-card-change text-green-600">Successfully booked</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="card-base mb-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <FormInput
                            v-model="searchQuery"
                            placeholder="Search by reference, airport codes, or user..."
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
                            <span v-if="filter !== 'all'" class="ml-1 px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-semibold">
                                1
                            </span>
                        </button>
                    </div>

                    <!-- Filter Tabs -->
                    <div v-if="showFilters" class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                        <Link
                            :href="route('admin.flight-requests.index')"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                filter === 'all'
                                    ? 'bg-red-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            All
                        </Link>
                        <Link
                            :href="route('admin.flight-requests.index', { filter: 'pending' })"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                filter === 'pending'
                                    ? 'bg-red-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            Pending
                        </Link>
                        <Link
                            :href="route('admin.flight-requests.index', { filter: 'assigned' })"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                filter === 'assigned'
                                    ? 'bg-red-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            Assigned
                        </Link>
                        <Link
                            :href="route('admin.flight-requests.index', { filter: 'quoted' })"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                filter === 'quoted'
                                    ? 'bg-red-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            Quoted
                        </Link>
                        <Link
                            :href="route('admin.flight-requests.index', { filter: 'completed' })"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                filter === 'completed'
                                    ? 'bg-red-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            Completed
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedRequests.length > 0" class="card-base mb-6 bg-blue-50 border-blue-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">
                        {{ selectedRequests.length }} request(s) selected
                    </span>
                    <div class="flex items-center gap-3">
                        <select v-model="bulkAssignAgency" class="input-base text-sm">
                            <option value="">Select agency...</option>
                            <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                                {{ agency.name }}
                            </option>
                        </select>
                        <button
                            @click="bulkAssign"
                            :disabled="!bulkAssignAgency"
                            class="btn-primary text-sm"
                        >
                            Assign Selected
                        </button>
                        <button @click="clearSelection" class="btn-secondary text-sm">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card-base overflow-hidden">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="w-12">
                                <input
                                    type="checkbox"
                                    @change="toggleAll"
                                    :checked="allSelected"
                                    class="rounded text-red-600 focus:ring-red-500"
                                />
                            </th>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Route</th>
                            <th>Travel Date</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Quotes</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="request in flightRequests.data" :key="request.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        v-model="selectedRequests"
                                        :value="request.id"
                                        class="w-4 h-4 text-brand-red-600 border-gray-300 rounded focus:ring-brand-red-600"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ request.request_reference }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ request.user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ request.user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ request.route_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(request.travel_date) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ request.formatted_budget }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="request.status_color"
                                        class="px-2 py-1 rounded-full text-xs font-medium border"
                                    >
                                        {{ (request?.status || '').replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="request.assigned_to" class="text-sm text-gray-900">
                                        {{ request.assigned_to.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-400">Not assigned</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="request.quotes_count > 0" class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                        {{ request.quotes_count }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">0</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link
                                        :href="route('admin.flight-requests.show', request.id)"
                                        class="text-brand-red-600 hover:text-red-900"
                                    >
                                        View
                                    </Link>
                                    <button
                                        v-if="request.status === 'pending'"
                                        @click="showAssignModal(request)"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        Assign
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="flightRequests.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Showing {{ flightRequests.from }} to {{ flightRequests.to }} of {{ flightRequests.total }} requests
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in flightRequests.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 border rounded-lg text-sm',
                                    link.active
                                        ? 'bg-brand-red-600 text-white border-brand-red-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Requests Table Card -->

            <!-- Assign Modal -->
                <div v-if="assignModal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Request</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Assign request {{ assignModal.request?.request_reference }} to an agency
                        </p>
                        
                        <select
                            v-model="assignModal.agencyId"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600 focus:border-transparent mb-4"
                        >
                            <option value="">Select agency...</option>
                            <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                                {{ agency.name }} ({{ agency.email }})
                            </option>
                        </select>

                        <div class="flex justify-end space-x-3">
                            <button
                                @click="closeAssignModal"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Cancel
                            </button>
                            <button
                                @click="assignRequest"
                                :disabled="!assignModal.agencyId"
                                class="px-4 py-2 bg-brand-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50"
                            >
                                Assign
                            </button>
                        </div>
                    </div>
                </div>
            <!-- End Assign Modal -->
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
    MagnifyingGlassIcon,
    FunnelIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    UserGroupIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    EyeIcon,
    PaperAirplaneIcon
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

const searchQuery = ref(props.search);
const selectedRequests = ref([]);
const bulkAssignAgency = ref('');
const showFilters = ref(false);

const assignModal = ref({
    show: false,
    request: null,
    agencyId: ''
});

const allSelected = computed(() => {
    return props.flightRequests.data.length > 0 && 
           selectedRequests.value.length === props.flightRequests.data.length;
});

// Search with debounce
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.flight-requests.index'), {
            filter: props.filter,
            search: searchQuery.value
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 500);
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
</script>
