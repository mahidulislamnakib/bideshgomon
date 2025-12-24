<template>
    <AdminLayout>
        <Head title="Visa Applications" />

        <div class="page-container">
            <!-- Page Header -->
            <PageHeader
                title="Visa Applications"
                description="Manage and track all visa applications with comprehensive workflow management"
                :primaryAction="{
                    label: 'New Application',
                    onClick: () => router.visit(route('admin.visa-applications.create')),
                    icon: PlusIcon
                }"
                :secondaryActions="[
                    { label: 'Export Report', onClick: exportReport, icon: DocumentArrowDownIcon },
                    { label: 'Bulk Actions', onClick: () => showBulkActions = true, icon: ListBulletIcon }
                ]"
            />

            <!-- Statistics Grid -->
            <div class="grid-stats mb-8">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <DocumentTextIcon class="h-6 w-6 text-growth-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Applications</p>
                        <p class="stat-card-value">{{ stats.total }}</p>
                        <p class="stat-card-change text-gray-600">All time</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-yellow-100">
                        <ClockIcon class="h-6 w-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Pending Review</p>
                        <p class="stat-card-value">{{ stats.pending + stats.under_review }}</p>
                        <p class="stat-card-change text-yellow-600">Needs attention</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-green-100">
                        <CheckCircleIcon class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Approved</p>
                        <p class="stat-card-value">{{ stats.approved }}</p>
                        <p class="stat-card-change text-green-600">
                            {{ Math.round((stats.approved / stats.total) * 100) }}% approval rate
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <CurrencyDollarIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Revenue</p>
                        <p class="stat-card-value">{{ formatCurrency(stats.revenue) }}</p>
                        <p class="stat-card-change text-purple-600">This month</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="card-base mb-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <FormInput
                            v-model="search"
                            placeholder="Search by reference, name, email, or passport..."
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
                    <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select v-model="status" class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white" @change="applyFilters">
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
                            <select v-model="visaType" class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white" @change="applyFilters">
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
                            <FormInput
                                v-model="destination"
                                placeholder="Enter country"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned To</label>
                            <select v-model="assignedTo" class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white" @change="applyFilters">
                                <option value="">All Staff</option>
                                <option v-for="staff in staffMembers" :key="staff.id" :value="staff.id">
                                    {{ staff.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Status</label>
                            <select v-model="paymentStatus" class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white" @change="applyFilters">
                                <option value="">All Payments</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
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
            <div v-if="hasActiveFilters" class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Showing {{ applications.from }} to {{ applications.to }} of {{ applications.total }} results
            </div>

            <!-- Applications Table -->
            <div class="card-base overflow-hidden">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Applicant</th>
                            <th>Visa Type</th>
                            <th>Destination</th>
                            <th>Travel Date</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="applications.data.length > 0">
                        <tr v-for="application in applications.data" :key="application.id">
                            <td>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ application.application_reference }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatDate(application.created_at) }}</div>
                            </td>
                            <td>
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                        {{ getInitials(application.applicant_name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ application.applicant_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ application.applicant_email }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                            <DocumentIcon class="inline h-3 w-3 mr-1" />
                                            {{ application.passport_number }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-2xl bg-blue-50 text-blue-700 text-sm font-medium">
                                        {{ getVisaIcon(application.visa_type) }}
                                        <span class="ml-1.5 capitalize">{{ application.visa_type }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <GlobeAltIcon class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ application.destination_country }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                    <CalendarIcon class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    {{ formatDate(application.travel_date) }}
                                </div>
                            </td>
                            <td>
                                <StatusBadge
                                    :status="application.status"
                                    size="sm"
                                />
                            </td>
                            <td>
                                <StatusBadge
                                    :status="application.payment_status"
                                    size="sm"
                                />
                            </td>
                            <td>
                                <span class="text-base font-bold text-gray-900 dark:text-white">{{ formatCurrency(application.total_amount) }}</span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('admin.visa-applications.show', application.id)"
                                        class="p-2 text-growth-600 hover:bg-blue-50 rounded-2xl transition-colors"
                                        title="View Details"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <Link
                                        :href="route('admin.visa-applications.edit', application.id)"
                                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-2xl transition-colors"
                                        title="Edit"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="updateStatus(application)"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-2xl transition-colors"
                                        title="Update Status"
                                    >
                                        <CheckCircleIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <EmptyState
                    v-if="applications.data.length === 0"
                    icon="DocumentTextIcon"
                    :title="hasActiveFilters ? 'No applications match your filters' : 'No visa applications yet'"
                    :description="hasActiveFilters ? 'Try adjusting your search or filter criteria' : 'Start processing visa applications for your clients'"
                    :action="hasActiveFilters ? null : { label: 'Create Application', onClick: () => router.visit(route('admin.visa-applications.create')) }"
                />

                <!-- Pagination -->
                <div v-if="applications.data.length > 0" class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-semibold">{{ applications.from }}</span> to <span class="font-semibold">{{ applications.to }}</span> of <span class="font-semibold">{{ applications.total }}</span> applications
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in applications.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-2xl transition-colors',
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
    DocumentTextIcon,
    CheckCircleIcon,
    ClockIcon,
    CurrencyDollarIcon,
    EyeIcon,
    PencilIcon,
    CalendarIcon,
    GlobeAltIcon,
    DocumentIcon,
    DocumentArrowDownIcon,
    ListBulletIcon
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    applications: Object,
    stats: Object,
    staffMembers: {
        type: Array,
        default: () => []
    },
    filters: Object,
});

const { formatCurrency, formatDate } = useBangladeshFormat();

// State
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const visaType = ref(props.filters.visa_type || '');
const destination = ref(props.filters.destination || '');
const assignedTo = ref(props.filters.assigned_to || '');
const paymentStatus = ref(props.filters.payment_status || '');
const showFilters = ref(false);
const showBulkActions = ref(false);

// Computed
const hasActiveFilters = computed(() => {
    return !!(search.value || status.value || visaType.value || destination.value || assignedTo.value || paymentStatus.value);
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (status.value) count++;
    if (visaType.value) count++;
    if (destination.value) count++;
    if (assignedTo.value) count++;
    if (paymentStatus.value) count++;
    return count;
});

// Get initials from name
const getInitials = (name) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

// Get visa type icon
const getVisaIcon = (type) => {
    const icons = {
        tourist: '🏖️',
        student: '🎓',
        work: '💼',
        business: '🤝',
        family: '👨‍👩‍👧',
        transit: '✈️'
    };
    return icons[type] || '📄';
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
    router.get(route('admin.visa-applications.index'), {
        search: search.value,
        status: status.value,
        visa_type: visaType.value,
        destination: destination.value,
        assigned_to: assignedTo.value,
        payment_status: paymentStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    status.value = '';
    visaType.value = '';
    destination.value = '';
    assignedTo.value = '';
    paymentStatus.value = '';
    applyFilters();
};

// Update status
const updateStatus = (application) => {
    router.visit(route('admin.visa-applications.update-status', application.id));
};

// Export report
const exportReport = () => {
    router.get(route('admin.visa-applications.export'), {
        search: search.value,
        status: status.value,
        visa_type: visaType.value,
        destination: destination.value,
        assigned_to: assignedTo.value,
        payment_status: paymentStatus.value,
    }, {
        preserveScroll: true,
    });
};
</script>
