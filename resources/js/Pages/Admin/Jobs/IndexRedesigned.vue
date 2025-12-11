<template>
    <Head title="Jobs" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Job Management</h1>
                <p class="text-neutral-600 mt-2">Manage job postings and applications</p>
            </div>
            <ActionButton
                label="Post New Job"
                :href="route('admin.jobs.create')"
                variant="primary"
                size="md"
                :icon="PlusIcon"
            />
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                :icon="BriefcaseIcon"
                label="Total Jobs"
                :value="stats.total"
                :change="stats.growth"
                :description="`${stats.activeJobs} active`"
                variant="primary"
            />
            <StatsCard
                :icon="DocumentTextIcon"
                label="Applications"
                :value="stats.totalApplications"
                :change="stats.applicationGrowth"
                variant="success"
            />
            <StatsCard
                :icon="ClockIcon"
                label="Pending Review"
                :value="stats.pendingApplications"
                variant="warning"
            />
            <StatsCard
                :icon="CalendarDaysIcon"
                label="Expiring Soon"
                :value="stats.expiringSoon"
                :description="`${stats.expired} expired`"
                variant="neutral"
            />
        </div>

        <!-- Jobs Table -->
        <TableWrapper
            title="All Jobs"
            description="View and manage job postings"
            :columns="columns"
            :data="jobs.data"
            :pagination="jobs"
            searchable
            filterable
            selectable
            has-actions
            @search="handleSearch"
            @sort="handleSort"
            @select="handleSelect"
        >
            <!-- Filters Slot -->
            <template #filters>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="expired">Expired</option>
                            <option value="filled">Filled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Category</label>
                        <select
                            v-model="filters.category"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Categories</option>
                            <option value="skilled">Skilled Worker</option>
                            <option value="construction">Construction</option>
                            <option value="hospitality">Hospitality</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="it">IT/Tech</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Location</label>
                        <select
                            v-model="filters.country"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Countries</option>
                            <option value="ae">UAE</option>
                            <option value="sa">Saudi Arabia</option>
                            <option value="om">Oman</option>
                            <option value="qa">Qatar</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <ActionButton
                            label="Clear Filters"
                            variant="ghost"
                            size="md"
                            @click="clearFilters"
                            full-width
                        />
                    </div>
                </div>
            </template>

            <!-- Custom Cells -->
            <template #cell(title)="{ row }">
                <div>
                    <p class="font-medium text-neutral-900">{{ row.title }}</p>
                    <p class="text-sm text-neutral-500">{{ row.company }}</p>
                </div>
            </template>

            <template #cell(location)="{ row }">
                <div class="flex items-center gap-2">
                    <MapPinIcon class="h-4 w-4 text-neutral-400" />
                    <span class="text-sm text-neutral-700">{{ row.city }}, {{ row.country }}</span>
                </div>
            </template>

            <template #cell(salary)="{ row }">
                <div class="text-sm">
                    <p class="font-medium text-neutral-900">{{ formatSalary(row.salary_min, row.salary_max) }}</p>
                    <p class="text-neutral-500">{{ row.salary_currency }}</p>
                </div>
            </template>

            <template #cell(applications_count)="{ value }">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                    {{ value }} applications
                </span>
            </template>

            <template #cell(status)="{ value }">
                <span :class="{
                    'bg-success-100 text-success-700': value === 'active',
                    'bg-neutral-100 text-neutral-700': value === 'draft',
                    'bg-red-100 text-red-700': value === 'expired',
                    'bg-blue-100 text-blue-700': value === 'filled'
                }" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                    {{ value }}
                </span>
            </template>

            <template #cell(expires_at)="{ value }">
                <span class="text-sm text-neutral-600">{{ formatDate(value) }}</span>
            </template>

            <!-- Row Actions -->
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.jobs.show', row.id)"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                    >
                        View
                    </Link>
                    <Link
                        :href="route('admin.jobs.edit', row.id)"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('admin.jobs.applications', row.id)"
                        class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                    >
                        Applications
                    </Link>
                    <button
                        v-if="row.status === 'active'"
                        @click="closeJob(row.id)"
                        class="text-yellow-600 hover:text-yellow-700 text-sm font-medium"
                    >
                        Close
                    </button>
                    <button
                        @click="deleteJob(row.id)"
                        class="text-red-600 hover:text-red-700 text-sm font-medium"
                    >
                        Delete
                    </button>
                </div>
            </template>
        </TableWrapper>
    </DashboardShell>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardShell from '@/Layouts/DashboardShell.vue';
import StatsCard from '@/Components/UI/StatsCard.vue';
import TableWrapper from '@/Components/UI/TableWrapper.vue';
import ActionButton from '@/Components/UI/ActionButton.vue';
import {
    BriefcaseIcon,
    DocumentTextIcon,
    ClockIcon,
    CalendarDaysIcon,
    PlusIcon,
    MapPinIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    jobs: Object,
    stats: Object,
    filters: Object
});

const columns = [
    { key: 'title', label: 'Job Title', sortable: true },
    { key: 'location', label: 'Location', sortable: false },
    { key: 'salary', label: 'Salary Range', sortable: true },
    { key: 'applications_count', label: 'Applications', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'expires_at', label: 'Expires', sortable: true }
];

const filters = ref({
    status: props.filters?.status || '',
    category: props.filters?.category || '',
    country: props.filters?.country || ''
});

const handleSearch = (query) => {
    router.get(route('admin.jobs.index'), {
        search: query,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSort = ({ key, order }) => {
    router.get(route('admin.jobs.index'), {
        sort: key,
        order: order,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSelect = (selectedIds) => {
    console.log('Selected jobs:', selectedIds);
};

const applyFilters = () => {
    router.get(route('admin.jobs.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        status: '',
        category: '',
        country: ''
    };
    router.get(route('admin.jobs.index'));
};

const closeJob = (id) => {
    if (confirm('Mark this job as filled?')) {
        router.post(route('admin.jobs.close', id));
    }
};

const deleteJob = (id) => {
    if (confirm('Are you sure? This will also delete all applications.')) {
        router.delete(route('admin.jobs.destroy', id));
    }
};

const formatSalary = (min, max) => {
    if (!min && !max) return 'Negotiable';
    if (min && max) return `${min.toLocaleString()} - ${max.toLocaleString()}`;
    return min ? `From ${min.toLocaleString()}` : `Up to ${max.toLocaleString()}`;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-BD', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>
