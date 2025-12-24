<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import BulkActionsBar from '@/Components/ui/BulkActionsBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';
import { debounce } from '@/Composables/useDebouncedSearch';
import { ref, computed, watch } from 'vue';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { 
    MagnifyingGlassIcon, PlusIcon, FunnelIcon, BriefcaseIcon,
    MapPinIcon, CurrencyDollarIcon, EyeIcon, PencilIcon, TrashIcon,
    SparklesIcon, CheckCircleIcon, XCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    jobs: Object,
    countries: Array,
    categories: Array,
    filters: Object,
    stats: Object,
});

const loading = ref(false);
const toast = useToast();
const { confirmBulk, confirmDelete } = useConfirm();

const search = ref(props.filters.search || '');
const country_id = ref(props.filters.country_id || '');
const category = ref(props.filters.category || '');
const approval_status = ref(props.filters.approval_status || '');
const status = ref(props.filters.status || '');
const is_featured = ref(props.filters.is_featured || '');
const showFilters = ref(false);
const selectedJobs = ref([]);

const debouncedPerformSearch = debounce(() => performSearch(), 400);

watch(search, () => {
    debouncedPerformSearch();
});

const performSearch = () => {
    router.get(route('admin.jobs.index'), {
        search: search.value,
        country_id: country_id.value,
        category: category.value,
        approval_status: approval_status.value,
        status: status.value,
        is_featured: is_featured.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    country_id.value = '';
    category.value = '';
    approval_status.value = '';
    status.value = '';
    is_featured.value = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return search.value || country_id.value || category.value || approval_status.value || status.value || is_featured.value;
});

const toggleJobSelection = (jobId) => {
    const index = selectedJobs.value.indexOf(jobId);
    if (index > -1) {
        selectedJobs.value.splice(index, 1);
    } else {
        selectedJobs.value.push(jobId);
    }
};

const toggleAllJobs = () => {
    if (selectedJobs.value.length === props.jobs.data.length) {
        selectedJobs.value = [];
    } else {
        selectedJobs.value = props.jobs.data.map(job => job.id);
    }
};

const bulkUpdateStatus = async (newStatus) => {
    if (selectedJobs.value.length === 0) {
        toast.warning('Please select jobs to update');
        return;
    }
    
    const confirmed = await confirmBulk(newStatus === 'active' ? 'activate' : 'deactivate', selectedJobs.value.length, 'job');
    if (confirmed) {
        router.post(route('admin.jobs.bulk-update-status'), {
            job_ids: selectedJobs.value,
            status: newStatus,
        }, {
            onSuccess: () => selectedJobs.value = [],
        });
    }
};

const bulkDelete = async () => {
    if (selectedJobs.value.length === 0) {
        toast.warning('Please select jobs to delete');
        return;
    }
    
    const confirmed = await confirmBulk('delete', selectedJobs.value.length, 'job');
    if (confirmed) {
        router.post(route('admin.jobs.bulk-delete'), {
            job_ids: selectedJobs.value,
        }, {
            onSuccess: () => selectedJobs.value = [],
        });
    }
};

const bulkActions = computed(() => [
    { key: 'activate', label: 'Activate', icon: 'CheckCircleIcon', variant: 'success', handler: () => bulkUpdateStatus('active') },
    { key: 'deactivate', label: 'Deactivate', icon: 'XCircleIcon', variant: 'warning', handler: () => bulkUpdateStatus('inactive') },
    { key: 'delete', label: 'Delete', icon: 'TrashIcon', variant: 'danger', handler: bulkDelete },
])

const deleteJob = async (jobId) => {
    const confirmed = await confirmDelete('this job posting');
    if (confirmed) {
        router.delete(route('admin.jobs.destroy', jobId));
    }
};

const getCategoryColor = (cat) => {
    const colors = {
        'hospitality': 'bg-purple-100 text-purple-700',
        'construction': 'bg-orange-100 text-orange-700',
        'healthcare': 'bg-red-100 text-blue-700',
        'it': 'bg-green-100 text-green-700',
        'manufacturing': 'bg-gray-100 text-gray-700',
        'education': 'bg-red-100 text-indigo-700',
        'retail': 'bg-pink-100 text-pink-700',
        'transportation': 'bg-yellow-100 text-yellow-700',
    };
    return colors[cat.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const getJobTypeLabel = (type) => {
    const labels = {
        'full-time': 'Full Time',
        'full_time': 'Full Time',
        'part-time': 'Part Time',
        'part_time': 'Part Time',
        'contract': 'Contract',
        'temporary': 'Temporary',
        'seasonal': 'Seasonal',
        'internship': 'Internship',
    };
    return labels[type] || type.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

// Keyboard shortcuts
const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts()

const pageShortcuts = [
    { key: 'c', description: 'Create new job', action: () => router.visit(route('admin.jobs.create')) },
    { key: '/', description: 'Focus search', action: () => document.querySelector('input[type="search"], input[placeholder*="Search"]')?.focus() },
    { key: 'r', description: 'Refresh page', action: () => router.reload() },
]

registerShortcuts(pageShortcuts)
</script>

<template>
    <Head title="Manage Job Postings" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Jobs', href: null }
            ]" />
        </template>

        <PageSkeleton v-if="loading" type="index" :table-rows="8" :table-columns="5" />
        <div v-else class="min-h-screen bg-neutral-50 dark:bg-neutral-900 pb-12">
            <!-- Header -->
            <div class="rounded-2xl px-6 py-8 mb-6 text-white" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="max-w-7xl mx-auto">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white">Job Postings Management</h1>
                            <p class="mt-2 text-gray-300">Manage all job postings on the platform</p>
                        </div>
                        <Link
                            :href="route('admin.jobs.create')"
                            class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-xl font-semibold hover:bg-green-600 transition-all shadow-lg"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" />
                            Create Job
                        </Link>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mt-8">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="text-gray-300 text-sm">Total Jobs</div>
                            <div class="text-3xl font-bold text-white mt-1">{{ stats.total }}</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="text-gray-300 text-sm">Active</div>
                            <div class="text-3xl font-bold text-green-400 mt-1">{{ stats.active }}</div>
                        </div>
                        <div class="bg-yellow-500/20 backdrop-blur-sm rounded-xl p-4 border border-yellow-400/30">
                            <div class="text-yellow-300 text-sm font-medium">⏳ Pending</div>
                            <div class="text-3xl font-bold text-yellow-400 mt-1">{{ stats.pending || 0 }}</div>
                        </div>
                        <div class="bg-green-500/20 backdrop-blur-sm rounded-xl p-4 border border-green-400/30">
                            <div class="text-green-300 text-sm font-medium">✓ Approved</div>
                            <div class="text-3xl font-bold text-green-400 mt-1">{{ stats.approved || 0 }}</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="text-gray-300 text-sm">Featured</div>
                            <div class="text-3xl font-bold text-amber-400 mt-1">{{ stats.featured }}</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="text-gray-300 text-sm">Expired</div>
                            <div class="text-3xl font-bold text-red-400 mt-1">{{ stats.expired }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search jobs by title, company, or category..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent bg-white dark:bg-neutral-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
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
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country</label>
                                <select
                                    v-model="country_id"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-growth-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">All Countries</option>
                                    <option v-for="country in countries" :key="country.id" :value="country.id">
                                        {{ country.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                <select
                                    v-model="category"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-growth-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Approval Status</label>
                                <select
                                    v-model="approval_status"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-growth-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">All Approval Status</option>
                                    <option value="pending">⏳ Pending Review</option>
                                    <option value="approved">✓ Approved</option>
                                    <option value="rejected">✗ Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="status"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-growth-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Featured</label>
                                <select
                                    v-model="is_featured"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-growth-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">All Jobs</option>
                                    <option value="1">Featured Only</option>
                                    <option value="0">Non-Featured</option>
                                </select>
                            </div>
                        </div>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="mt-4 text-sm text-growth-600 hover:text-indigo-700 font-medium"
                        >
                            Clear all filters
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <BulkActionsBar
                    :count="selectedJobs.length"
                    item-label="job"
                    :actions="bulkActions"
                    @clear="selectedJobs = []"
                />

                <!-- Jobs List -->
                <div class="space-y-4">
                    <EmptyState
                        v-if="jobs.data.length === 0"
                        icon="BriefcaseIcon"
                        title="No jobs found"
                        description="Get started by creating your first job posting to attract talented candidates."
                        :action="{
                            label: 'Create Job',
                            onClick: () => router.visit(route('admin.jobs.create')),
                        }"
                    />

                    <div
                        v-for="job in jobs.data"
                        :key="job.id"
                        class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-lg transition-shadow p-6"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Checkbox -->
                            <input
                                type="checkbox"
                                :checked="selectedJobs.includes(job.id)"
                                @change="toggleJobSelection(job.id)"
                                class="mt-1 h-5 w-5 text-growth-600 rounded border-gray-300 focus:ring-growth-600"
                            />

                            <!-- Job Content -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ job.title }}</h3>
                                            
                                            <!-- Approval Status Badge -->
                                            <span v-if="job.approval_status === 'pending'" class="inline-flex items-center px-2 py-1 bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400 text-xs font-semibold rounded-full">
                                                ⏳ Pending
                                            </span>
                                            <span v-else-if="job.approval_status === 'approved'" class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400 text-xs font-semibold rounded-full">
                                                <CheckCircleIcon class="h-3 w-3 mr-1" />
                                                Approved
                                            </span>
                                            <span v-else-if="job.approval_status === 'rejected'" class="inline-flex items-center px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-400 text-xs font-semibold rounded-full">
                                                <XCircleIcon class="h-3 w-3 mr-1" />
                                                Rejected
                                            </span>
                                            
                                            <span v-if="job.is_featured" class="inline-flex items-center px-2 py-1 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 text-xs font-semibold rounded-full">
                                                <SparklesIcon class="h-3 w-3 mr-1" />
                                                Featured
                                            </span>
                                            <span v-if="job.is_active" class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400 text-xs font-semibold rounded-full">
                                                <CheckCircleIcon class="h-3 w-3 mr-1" />
                                                Active
                                            </span>
                                            <span v-else class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-full">
                                                <XCircleIcon class="h-3 w-3 mr-1" />
                                                Inactive
                                            </span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 mb-3">{{ job.company_name }}</p>
                                        
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            <div class="flex items-center">
                                                <MapPinIcon class="h-4 w-4 mr-1" />
                                                {{ job.city }}, {{ job.country?.name }}
                                            </div>
                                            <div class="flex items-center">
                                                <CurrencyDollarIcon class="h-4 w-4 mr-1" />
                                                {{ job.salary_currency }} {{ job.salary_min.toLocaleString() }} - {{ job.salary_max.toLocaleString() }}
                                            </div>
                                            <div class="flex items-center">
                                                <EyeIcon class="h-4 w-4 mr-1" />
                                                {{ job.views_count }} views
                                            </div>
                                            <!-- Processing Fee Indicator -->
                                            <div v-if="job.processing_fee && job.processing_fee > 0" class="flex items-center text-growth-600 dark:text-green-400 font-semibold">
                                                <CurrencyDollarIcon class="h-4 w-4 mr-1" />
                                                +৳{{ Number(job.processing_fee).toLocaleString() }} fee
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <span :class="getCategoryColor(job.category)" class="px-3 py-1 text-xs font-medium rounded-full">
                                                {{ job.category }}
                                            </span>
                                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-full">
                                                {{ getJobTypeLabel(job.job_type) }}
                                            </span>
                                            <span class="px-3 py-1 bg-gray-50 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-full">
                                                {{ job.applications_count }} applications
                                            </span>
                                            <span class="px-3 py-1 bg-purple-50 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-xs font-medium rounded-full">
                                                {{ job.positions_available }} positions
                                            </span>
                                            <!-- Application Fee Display -->
                                            <span v-if="job.application_fee > 0" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-full">
                                                Fee: ৳{{ Number(job.application_fee).toLocaleString() }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 ml-4">
                                        <Link
                                            :href="route('admin.jobs.show', job.id)"
                                            class="p-2 text-growth-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-xl transition-colors"
                                            title="View Details"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                        </Link>
                                        <Link
                                            :href="route('admin.jobs.edit', job.id)"
                                            class="p-2 text-growth-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-xl transition-colors"
                                            title="Edit Job"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click="deleteJob(job.id)"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                                            title="Delete Job"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="jobs.data.length > 0" class="mt-8 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-400">
                        Showing {{ jobs.from }} to {{ jobs.to }} of {{ jobs.total }} jobs
                    </div>
                    <div class="flex items-center space-x-2">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="link in jobs.links"
                            :key="link.label"
                            :href="link.url || undefined"
                            :class="[
                                'px-4 py-2 text-sm rounded-xl font-medium',
                                link.active
                                    ? 'bg-growth-600 text-white'
                                    : link.url
                                    ? 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700 border border-gray-300 dark:border-neutral-600'
                                    : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
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
