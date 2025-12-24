<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    MagnifyingGlassIcon, FunnelIcon, ClipboardDocumentListIcon,
    UserCircleIcon, BriefcaseIcon, CalendarIcon, DocumentTextIcon,
    CheckCircleIcon, XCircleIcon, ClockIcon, EyeIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    applications: Object,
    jobs: Array,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');
const job_id = ref(props.filters.job_id || '');
const status = ref(props.filters.status || '');
const payment_status = ref(props.filters.payment_status || '');
const showFilters = ref(false);
const selectedApplications = ref([]);

const performSearch = () => {
    router.get(route('admin.applications.index'), {
        search: search.value,
        job_id: job_id.value,
        status: status.value,
        payment_status: payment_status.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    job_id.value = '';
    status.value = '';
    payment_status.value = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return search.value || job_id.value || status.value || payment_status.value;
});

const toggleApplicationSelection = (appId) => {
    const index = selectedApplications.value.indexOf(appId);
    if (index > -1) {
        selectedApplications.value.splice(index, 1);
    } else {
        selectedApplications.value.push(appId);
    }
};

const bulkUpdateStatus = (newStatus) => {
    if (selectedApplications.value.length === 0) {
        alert('Please select applications to update');
        return;
    }
    
    const notes = prompt('Add admin notes (optional):');
    
    if (confirm(`Update ${selectedApplications.value.length} application(s) to ${newStatus}?`)) {
        router.post(route('admin.applications.bulk-update-status'), {
            application_ids: selectedApplications.value,
            status: newStatus,
            admin_notes: notes || null,
        }, {
            onSuccess: () => selectedApplications.value = [],
        });
    }
};

const exportApplications = () => {
    const params = new URLSearchParams({
        job_id: job_id.value,
        status: status.value,
    });
    window.location.href = route('admin.applications.export') + '?' + params.toString();
};

const getStatusBadge = (appStatus) => {
    const badges = {
        'pending': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
        'reviewed': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
        'shortlisted': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
        'rejected': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
        'hired': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
    };
    return badges[appStatus] || 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300';
};

const getStatusIcon = (appStatus) => {
    switch(appStatus) {
        case 'hired': return CheckCircleIcon;
        case 'rejected': return XCircleIcon;
        case 'pending': return ClockIcon;
        default: return ClipboardDocumentListIcon;
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};
</script>

<template>
    <Head title="Job Applications Management" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Header -->
            <div class="relative overflow-hidden px-4 py-8 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, #10b981 0%, transparent 50%), radial-gradient(circle at 75% 75%, #0d9488 0%, transparent 50%);"></div>
                </div>
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white">Job Applications</h1>
                            <p class="mt-2 text-gray-300">Review and manage all job applications</p>
                        </div>
                        <button
                            @click="exportApplications"
                            class="inline-flex items-center px-6 py-3 bg-growth-600 text-white rounded-2xl font-semibold hover:bg-growth-700 transition-all shadow-sm"
                        >
                            <ArrowDownTrayIcon class="h-5 w-5 mr-2" />
                            Export CSV
                        </button>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-8">
                        <div class="bg-white/10 backdrop-blur border border-white/20 rounded-xl p-3">
                            <div class="text-gray-300 text-xs font-medium">Total</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ stats.total }}</div>
                        </div>
                        <div class="bg-yellow-500/20 backdrop-blur border border-yellow-400/30 rounded-xl p-3">
                            <div class="text-yellow-300 text-xs font-medium">Pending</div>
                            <div class="text-2xl font-bold text-yellow-100 mt-1">{{ stats.pending }}</div>
                        </div>
                        <div class="bg-blue-500/20 backdrop-blur border border-blue-400/30 rounded-xl p-3">
                            <div class="text-blue-300 text-xs font-medium">Reviewed</div>
                            <div class="text-2xl font-bold text-blue-100 mt-1">{{ stats.reviewed }}</div>
                        </div>
                        <div class="bg-purple-500/20 backdrop-blur border border-purple-400/30 rounded-xl p-3">
                            <div class="text-purple-300 text-xs font-medium">Shortlisted</div>
                            <div class="text-2xl font-bold text-purple-100 mt-1">{{ stats.shortlisted }}</div>
                        </div>
                        <div class="bg-red-500/20 backdrop-blur border border-red-400/30 rounded-xl p-3">
                            <div class="text-red-300 text-xs font-medium">Rejected</div>
                            <div class="text-2xl font-bold text-red-100 mt-1">{{ stats.rejected }}</div>
                        </div>
                        <div class="bg-green-500/20 backdrop-blur border border-green-400/30 rounded-xl p-3">
                            <div class="text-green-300 text-xs font-medium">Hired</div>
                            <div class="text-2xl font-bold text-green-100 mt-1">{{ stats.hired }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <!-- Search & Filters -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input
                                v-model="search"
                                @keyup.enter="performSearch"
                                type="text"
                                placeholder="Search by applicant name or email..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent"
                            />
                        </div>
                        <button
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <FunnelIcon class="h-5 w-5 mr-2" />
                            Filters
                            <span v-if="hasActiveFilters" class="ml-2 px-2 py-0.5 bg-growth-600 text-white text-xs rounded-full">Active</span>
                        </button>
                    </div>

                    <!-- Expandable Filters -->
                    <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job Posting</label>
                                <select
                                    v-model="job_id"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Jobs</option>
                                    <option v-for="job in jobs" :key="job.id" :value="job.id">
                                        {{ job.title }} - {{ job.company_name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="status"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="shortlisted">Shortlisted</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="hired">Hired</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Status</label>
                                <select
                                    v-model="payment_status"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Payments</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="mt-4 text-sm text-growth-600 hover:text-growth-700 font-medium"
                        >
                            Clear all filters
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedApplications.length > 0" class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-4 mb-6 flex items-center justify-between">
                    <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">{{ selectedApplications.length }} application(s) selected</span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkUpdateStatus('reviewed')"
                            class="px-4 py-2 bg-growth-600 text-white text-sm rounded-2xl hover:bg-growth-700 transition-colors"
                        >
                            Mark Reviewed
                        </button>
                        <button
                            @click="bulkUpdateStatus('shortlisted')"
                            class="px-4 py-2 bg-purple-600 text-white text-sm rounded-2xl hover:bg-purple-700 transition-colors"
                        >
                            Shortlist
                        </button>
                        <button
                            @click="bulkUpdateStatus('rejected')"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-2xl hover:bg-growth-700 transition-colors"
                        >
                            Reject
                        </button>
                        <button
                            @click="bulkUpdateStatus('hired')"
                            class="px-4 py-2 bg-green-600 text-white text-sm rounded-2xl hover:bg-green-700 transition-colors"
                        >
                            Mark Hired
                        </button>
                    </div>
                </div>

                <!-- Applications List -->
                <div class="space-y-4">
                    <div v-if="applications.data.length === 0" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-12 text-center">
                        <ClipboardDocumentListIcon class="h-16 w-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No applications found</h3>
                        <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters</p>
                    </div>

                    <div
                        v-for="app in applications.data"
                        :key="app.id"
                        class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-lg transition-shadow p-6"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Checkbox -->
                            <input
                                type="checkbox"
                                :checked="selectedApplications.includes(app.id)"
                                @change="toggleApplicationSelection(app.id)"
                                class="mt-1 h-5 w-5 text-growth-600 rounded border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 focus:ring-growth-600"
                            />

                            <!-- Application Content -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ app.user?.name || 'Unknown User' }}</h3>
                                            <span :class="getStatusBadge(app.status)" class="px-3 py-1 text-xs font-semibold rounded-full flex items-center gap-1">
                                                <component :is="getStatusIcon(app.status)" class="h-3 w-3" />
                                                {{ app.status }}
                                            </span>
                                            <span v-if="app.payment_status === 'paid'" class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                                ? Paid
                                            </span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">{{ app.user?.email }}</p>
                                        
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            <div class="flex items-center">
                                                <BriefcaseIcon class="h-4 w-4 mr-1" />
                                                {{ app.job_posting?.title }}
                                            </div>
                                            <div class="flex items-center">
                                                <CalendarIcon class="h-4 w-4 mr-1" />
                                                Applied {{ formatDate(app.submitted_at || app.created_at) }}
                                            </div>
                                            <div v-if="app.reviewed_at" class="flex items-center">
                                                <CheckCircleIcon class="h-4 w-4 mr-1" />
                                                Reviewed {{ formatDate(app.reviewed_at) }}
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="px-3 py-1 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-full">
                                                {{ app.job_posting?.company_name }}
                                            </span>
                                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">
                                                {{ app.job_posting?.country?.name }}
                                            </span>
                                            <span v-if="app.user_cv" class="px-3 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full flex items-center gap-1">
                                                <DocumentTextIcon class="h-3 w-3" />
                                                CV Attached
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <Link
                                        :href="route('admin.applications.show', app.id)"
                                        class="p-2 text-growth-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-2xl transition-colors"
                                        title="View Details"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="applications.data.length > 0" class="mt-8 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ applications.from }} to {{ applications.to }} of {{ applications.total }} applications
                    </div>
                    <div class="flex items-center space-x-2">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="link in applications.links"
                            :key="link.label"
                            :href="link.url || undefined"
                            :class="[
                                'px-4 py-2 text-sm rounded-2xl font-medium',
                                link.active
                                    ? 'bg-growth-600 text-white'
                                    : link.url
                                    ? 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-700 border border-gray-300 dark:border-neutral-600'
                                    : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
