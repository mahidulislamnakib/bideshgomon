<template>
    <Head title="Agencies" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Agencies</h1>
                <p class="text-neutral-600 mt-2">Manage recruitment agencies and verification requests</p>
            </div>
            <ActionButton
                label="Add Agency"
                :href="route('admin.agencies.create')"
                variant="primary"
                size="md"
                :icon="PlusIcon"
            />
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                :icon="BuildingOfficeIcon"
                label="Total Agencies"
                :value="stats.total"
                :change="stats.growth"
                :description="`${stats.newThisMonth} new this month`"
                variant="primary"
            />
            <StatsCard
                :icon="CheckBadgeIcon"
                label="Verified"
                :value="stats.verified"
                :change="stats.verifiedGrowth"
                variant="success"
            />
            <StatsCard
                :icon="ClockIcon"
                label="Pending Verification"
                :value="stats.pending"
                variant="warning"
            />
            <StatsCard
                :icon="XCircleIcon"
                label="Rejected"
                :value="stats.rejected"
                variant="neutral"
            />
        </div>

        <!-- Agencies Table -->
        <TableWrapper
            title="All Agencies"
            description="View and manage recruitment agencies"
            :columns="columns"
            :data="agencies.data"
            :pagination="agencies"
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
                            <option value="suspended">Suspended</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Verification</label>
                        <select
                            v-model="filters.verification"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Type</label>
                        <select
                            v-model="filters.type"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Types</option>
                            <option value="recruitment">Recruitment</option>
                            <option value="consulting">Consulting</option>
                            <option value="manpower">Manpower</option>
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
            <template #cell(name)="{ row }">
                <div class="flex items-center gap-3">
                    <img
                        :src="row.logo || '/images/default-agency.png'"
                        :alt="row.name"
                        class="h-10 w-10 rounded object-cover border border-neutral-200"
                    />
                    <div>
                        <p class="font-medium text-neutral-900">{{ row.name }}</p>
                        <p class="text-sm text-neutral-500">{{ row.email }}</p>
                    </div>
                </div>
            </template>

            <template #cell(type)="{ value }">
                <span :class="{
                    'bg-blue-100 text-blue-700': value === 'recruitment',
                    'bg-purple-100 text-purple-700': value === 'consulting',
                    'bg-orange-100 text-orange-700': value === 'manpower',
                    'bg-neutral-100 text-neutral-700': !value
                }" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                    {{ value || 'N/A' }}
                </span>
            </template>

            <template #cell(verification_status)="{ value }">
                <div class="flex items-center gap-2">
                    <CheckBadgeIcon v-if="value === 'verified'" class="h-5 w-5 text-success-600" />
                    <ClockIcon v-else-if="value === 'pending'" class="h-5 w-5 text-yellow-600" />
                    <XCircleIcon v-else class="h-5 w-5 text-neutral-400" />
                    <span :class="{
                        'bg-success-100 text-success-700': value === 'verified',
                        'bg-yellow-100 text-yellow-700': value === 'pending',
                        'bg-red-100 text-red-700': value === 'rejected',
                        'bg-neutral-100 text-neutral-700': !value
                    }" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                        {{ value || 'Unverified' }}
                    </span>
                </div>
            </template>

            <template #cell(status)="{ value }">
                <span :class="{
                    'bg-success-100 text-success-700': value === 'active',
                    'bg-red-100 text-red-700': value === 'suspended',
                    'bg-neutral-100 text-neutral-700': value === 'inactive'
                }" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                    {{ value }}
                </span>
            </template>

            <template #cell(created_at)="{ value }">
                <span class="text-sm text-neutral-600">{{ formatDate(value) }}</span>
            </template>

            <!-- Row Actions -->
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.agencies.show', row.id)"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                    >
                        View
                    </Link>
                    <Link
                        :href="route('admin.agencies.edit', row.id)"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="row.verification_status === 'pending'"
                        @click="verifyAgency(row.id)"
                        class="text-success-600 hover:text-success-700 text-sm font-medium"
                    >
                        Verify
                    </button>
                    <button
                        v-if="row.status !== 'suspended'"
                        @click="suspendAgency(row.id)"
                        class="text-yellow-600 hover:text-yellow-700 text-sm font-medium"
                    >
                        Suspend
                    </button>
                    <button
                        @click="deleteAgency(row.id)"
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
    BuildingOfficeIcon,
    CheckBadgeIcon,
    ClockIcon,
    XCircleIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    agencies: Object,
    stats: Object,
    filters: Object
});

const columns = [
    { key: 'name', label: 'Agency', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'verification_status', label: 'Verification', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Joined', sortable: true }
];

const filters = ref({
    status: props.filters?.status || '',
    verification: props.filters?.verification || '',
    type: props.filters?.type || ''
});

const handleSearch = (query) => {
    router.get(route('admin.agencies.index'), {
        search: query,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSort = ({ key, order }) => {
    router.get(route('admin.agencies.index'), {
        sort: key,
        order: order,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSelect = (selectedIds) => {
    console.log('Selected agencies:', selectedIds);
};

const applyFilters = () => {
    router.get(route('admin.agencies.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        status: '',
        verification: '',
        type: ''
    };
    router.get(route('admin.agencies.index'));
};

const verifyAgency = (id) => {
    if (confirm('Verify this agency?')) {
        router.post(route('admin.agencies.verify', id));
    }
};

const suspendAgency = (id) => {
    const reason = prompt('Reason for suspension:');
    if (!reason) return;

    if (confirm('Suspend this agency?')) {
        router.post(route('admin.agencies.suspend', id), { reason });
    }
};

const deleteAgency = (id) => {
    if (confirm('Are you sure? This action cannot be undone.')) {
        router.delete(route('admin.agencies.destroy', id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-BD', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>
