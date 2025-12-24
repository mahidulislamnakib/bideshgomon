<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import BulkActionsBar from '@/Components/ui/BulkActionsBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { useBulkSelection } from '@/Composables/useBulkSelection';
import { useConfirm } from '@/Composables/useConfirm';
import { debounce } from '@/Composables/useDebouncedSearch';
import SortableHeader from '@/Components/ui/SortableHeader.vue';
import { ref, computed, watch } from 'vue';
import {
    MagnifyingGlassIcon,
    DocumentTextIcon,
    CalendarIcon,
    UserIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    EyeIcon,
    CurrencyDollarIcon,
    FunnelIcon,
    GlobeAltIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    applications: Object,
    stats: Object,
    staffMembers: Array,
    filters: Object,
});

const loading = ref(false);

const { selectedItems: selectedApplications, allSelected, toggleItem, toggleAll, isSelected, hasSelection, selectionCount, clearSelection } = useBulkSelection({
    getItems: () => props.applications?.data || [],
})

const { confirmBulk } = useConfirm()

const bulkApprove = async () => {
    const confirmed = await confirmBulk('approve', selectionCount.value, 'application')
    if (confirmed) {
        router.post(route('admin.visa-applications.bulk-status'), {
            ids: selectedApplications.value,
            status: 'approved',
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkReject = async () => {
    const confirmed = await confirmBulk('reject', selectionCount.value, 'application')
    if (confirmed) {
        router.post(route('admin.visa-applications.bulk-status'), {
            ids: selectedApplications.value,
            status: 'rejected',
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkUnderReview = async () => {
    const confirmed = await confirmBulk('mark as under review', selectionCount.value, 'application')
    if (confirmed) {
        router.post(route('admin.visa-applications.bulk-status'), {
            ids: selectedApplications.value,
            status: 'under_review',
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const handleExport = () => {
    window.location.href = route('admin.visa-applications.export', {
        ids: selectedApplications.value.join(','),
    })
}

const bulkActions = computed(() => [
    { key: 'approve', label: 'Approve', icon: 'CheckCircleIcon', variant: 'success', handler: bulkApprove },
    { key: 'reject', label: 'Reject', icon: 'XCircleIcon', variant: 'danger', handler: bulkReject },
    { key: 'review', label: 'Under Review', variant: 'warning', handler: bulkUnderReview },
])

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const visaType = ref(props.filters?.visa_type || '');
const destination = ref(props.filters?.destination || '');
const assignedTo = ref(props.filters?.assigned_to || '');
const paymentStatus = ref(props.filters?.payment_status || '');
const showFilters = ref(false);

// Sorting state
const sortColumn = ref(props.filters?.sort || '');
const sortDirection = ref(props.filters?.direction || 'asc');

const sortBy = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    applyFilters();
};

const applyFilters = () => {
    router.get(route('admin.visa-applications.index'), {
        search: search.value,
        status: status.value,
        visa_type: visaType.value,
        destination: destination.value,
        assigned_to: assignedTo.value,
        payment_status: paymentStatus.value,
        sort: sortColumn.value || undefined,
        direction: sortColumn.value ? sortDirection.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    visaType.value = '';
    destination.value = '';
    assignedTo.value = '';
    paymentStatus.value = '';
    applyFilters();
};

// Debounced search
const debouncedApplyFilters = debounce(() => applyFilters(), 400)

watch(search, () => {
    debouncedApplyFilters()
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-BD', {
        minimumFractionDigits: 0,
    }).format(amount || 0);
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const statusColors = {
    submitted: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    under_review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    documents_requested: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    completed: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
};

const paymentColors = {
    paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    unpaid: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
};

const hasActiveFilters = computed(() => {
    return search.value || status.value || visaType.value || destination.value || assignedTo.value || paymentStatus.value;
});

// Keyboard shortcuts
const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts()

const pageShortcuts = [
    { key: '/', description: 'Focus search', action: () => document.querySelector('input[type="search"], input[placeholder*="Search"]')?.focus() },
    { key: 'r', description: 'Refresh page', action: () => router.reload() },
    { key: 'f', description: 'Toggle filters', action: () => { showFilters.value = !showFilters.value } },
]

registerShortcuts(pageShortcuts)
</script>

<template>
    <Head title="Visa Applications" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Visa Applications', href: null }
            ]" />
        </template>

        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <!-- Main Content -->
        <div v-else class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- SVG Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="visaGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#visaGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-blue-500/20 to-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg">
                                <GlobeAltIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Visa Applications</h1>
                                <p class="mt-1 text-gray-300">Manage and track all visa applications</p>
                            </div>
                        </div>
                        <button
                            @click="showFilters = !showFilters"
                            class="mt-4 md:mt-0 inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                        >
                            <FunnelIcon class="h-5 w-5" />
                            {{ showFilters ? 'Hide' : 'Show' }} Filters
                        </button>
                    </div>

                    <!-- Stats Cards in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-gray-500/20 rounded-2xl">
                                    <DocumentTextIcon class="h-5 w-5 text-gray-300" />
                                </div>
                                <div>
                                    <p class="text-gray-300 text-xs font-medium">Total</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-2xl">
                                    <ClockIcon class="h-5 w-5 text-blue-300" />
                                </div>
                                <div>
                                    <p class="text-blue-300 text-xs font-medium">Pending</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.pending || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-2xl">
                                    <DocumentTextIcon class="h-5 w-5 text-yellow-300" />
                                </div>
                                <div>
                                    <p class="text-yellow-300 text-xs font-medium">Under Review</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.under_review || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-500/20 rounded-2xl">
                                    <CheckCircleIcon class="h-5 w-5 text-emerald-300" />
                                </div>
                                <div>
                                    <p class="text-emerald-300 text-xs font-medium">Approved</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.approved || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-500/20 rounded-2xl">
                                    <XCircleIcon class="h-5 w-5 text-red-300" />
                                </div>
                                <div>
                                    <p class="text-red-300 text-xs font-medium">Rejected</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.rejected || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-2xl">
                                    <CurrencyDollarIcon class="h-5 w-5 text-purple-300" />
                                </div>
                                <div>
                                    <p class="text-purple-300 text-xs font-medium">Revenue</p>
                                    <p class="text-lg font-bold text-white">৳{{ formatCurrency(stats?.revenue) }}</p>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Reference, name, email, passport..."
                                        class="w-full pl-10 pr-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="status"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="documents_requested">Documents Requested</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visa Type</label>
                                <select
                                    v-model="visaType"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Types</option>
                                    <option value="tourist">Tourist</option>
                                    <option value="student">Student</option>
                                    <option value="work">Work</option>
                                    <option value="business">Business</option>
                                    <option value="family">Family</option>
                                    <option value="transit">Transit</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Destination</label>
                                <input
                                    v-model="destination"
                                    type="text"
                                    placeholder="Enter country"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned To</label>
                                <select
                                    v-model="assignedTo"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Staff</option>
                                    <option v-for="staff in staffMembers" :key="staff.id" :value="staff.id">
                                        {{ staff.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Status</label>
                                <select
                                    v-model="paymentStatus"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Payments</option>
                                    <option value="paid">Paid</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="applyFilters"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors"
                            >
                                Apply Filters
                            </button>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="px-5 py-2.5 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors flex items-center gap-2"
                            >
                                <XMarkIcon class="h-4 w-4" />
                                Clear All
                            </button>
                        </div>
                    </div>
                </transition>

                <BulkActionsBar
                    :count="selectionCount"
                    item-label="application"
                    :actions="bulkActions"
                    :show-export="true"
                    @clear="clearSelection"
                    @export="handleExport"
                />

                <!-- Applications Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            :indeterminate="hasSelection && !allSelected"
                                            @change="toggleAll"
                                            class="h-5 w-5 text-blue-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-neutral-700"
                                        />
                                    </th>
                                    <SortableHeader 
                                        column="application_reference" 
                                        label="Reference" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <SortableHeader 
                                        column="applicant_name" 
                                        label="Applicant" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Visa Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Destination</th>
                                    <SortableHeader 
                                        column="travel_date" 
                                        label="Travel Date" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Payment</th>
                                    <SortableHeader 
                                        column="total_amount" 
                                        label="Amount" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="application in applications.data" :key="application.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            :checked="isSelected(application.id)"
                                            @change="toggleItem(application.id)"
                                            class="h-5 w-5 text-blue-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-neutral-700"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl flex items-center justify-center">
                                                <GlobeAltIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div class="ml-3">
                                                <div class="font-semibold text-gray-900 dark:text-white text-sm">{{ application.application_reference }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(application.created_at) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ application.applicant_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ application.applicant_email }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500">{{ application.passport_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ application.visa_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ application.destination_country }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(application.travel_date) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-3 py-1 text-xs font-semibold rounded-full capitalize',
                                                statusColors[application.status] || 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-gray-300'
                                            ]"
                                        >
                                            {{ (application?.status || '').replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-3 py-1 text-xs font-semibold rounded-full capitalize',
                                                paymentColors[application.payment_status] || 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-gray-300'
                                            ]"
                                        >
                                            {{ application.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">৳{{ formatCurrency(application.total_amount) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link
                                            :href="route('admin.visa-applications.show', application.id)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-2xl transition-colors font-medium text-sm"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                            View
                                        </Link>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="!applications.data || applications.data.length === 0">
                                    <td colspan="10" class="px-6 py-16">
                                        <EmptyState
                                            icon="GlobeAltIcon"
                                            title="No visa applications"
                                            description="Visa applications will appear here once users submit them."
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="applications.data && applications.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-medium text-gray-900 dark:text-white">{{ applications.from }}</span>
                                to <span class="font-medium text-gray-900 dark:text-white">{{ applications.to }}</span>
                                of <span class="font-medium text-gray-900 dark:text-white">{{ applications.total }}</span> applications
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="applications.prev_page_url"
                                    :href="applications.prev_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <Link
                                    v-if="applications.next_page_url"
                                    :href="applications.next_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                            </div>
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
