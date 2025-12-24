<template>
    <Head title="Users" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Users</h1>
                <p class="text-neutral-600 mt-2">Manage user accounts and permissions</p>
            </div>
            <ActionButton
                label="Add User"
                :href="route('admin.users.create')"
                variant="primary"
                size="md"
                :icon="UserPlusIcon"
            />
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                :icon="UsersIcon"
                label="Total Users"
                :value="stats.total"
                :change="stats.growth"
                :description="`${stats.active} active`"
                variant="primary"
            />
            <StatsCard
                :icon="CheckCircleIcon"
                label="Verified"
                :value="stats.verified"
                :change="stats.verifiedGrowth"
                variant="success"
            />
            <StatsCard
                :icon="XCircleIcon"
                label="Unverified"
                :value="stats.unverified"
                variant="warning"
            />
            <StatsCard
                :icon="ShieldCheckIcon"
                label="Admins"
                :value="stats.admins"
                variant="neutral"
            />
        </div>

        <!-- Users Table -->
        <TableWrapper
            title="All Users"
            description="View and manage user accounts"
            :columns="columns"
            :data="users.data"
            :pagination="users"
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                        <select
                            v-model="filters.role"
                            @change="applyFilters"
                            class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white"
                        >
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="agency">Agency</option>
                            <option value="consultant">Consultant</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Verified</label>
                        <select
                            v-model="filters.email_verified"
                            @change="applyFilters"
                            class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white"
                        >
                            <option value="">All</option>
                            <option value="1">Verified</option>
                            <option value="0">Unverified</option>
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
                        :src="row.avatar || '/images/default-avatar.png'"
                        :alt="row.name"
                        class="h-10 w-10 rounded-full object-cover"
                    />
                    <div>
                        <p class="font-medium text-neutral-900">{{ row.name }}</p>
                        <p class="text-sm text-neutral-500">{{ row.email }}</p>
                    </div>
                </div>
            </template>

            <template #cell(role)="{ value }">
                <span :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    value === 'admin' ? 'bg-red-100 text-red-700' :
                    value === 'agency' ? 'bg-blue-100 text-blue-700' :
                    value === 'consultant' ? 'bg-purple-100 text-purple-700' :
                    'bg-neutral-100 text-neutral-700'
                ]">
                    {{ value }}
                </span>
            </template>

            <template #cell(status)="{ value }">
                <span :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    value === 'active' ? 'bg-success-100 text-success-700' :
                    value === 'suspended' ? 'bg-red-100 text-red-700' :
                    'bg-neutral-100 text-neutral-700'
                ]">
                    {{ value }}
                </span>
            </template>

            <template #cell(email_verified_at)="{ value }">
                <span v-if="value" class="flex items-center gap-1 text-success-600">
                    <CheckCircleIcon class="h-4 w-4" />
                    <span class="text-xs font-medium">Verified</span>
                </span>
                <span v-else class="flex items-center gap-1 text-neutral-400">
                    <XCircleIcon class="h-4 w-4" />
                    <span class="text-xs font-medium">Unverified</span>
                </span>
            </template>

            <template #cell(created_at)="{ value }">
                <span class="text-sm text-neutral-600">{{ formatDate(value) }}</span>
            </template>

            <!-- Row Actions -->
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.users.show', row.id)"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                    >
                        View
                    </Link>
                    <Link
                        :href="route('admin.users.edit', row.id)"
                        class="text-growth-600 hover:text-blue-700 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="row.status !== 'suspended'"
                        @click="suspendUser(row.id)"
                        class="text-yellow-600 hover:text-yellow-700 text-sm font-medium"
                    >
                        Suspend
                    </button>
                    <button
                        @click="deleteUser(row.id)"
                        class="text-red-600 hover:text-growth-700 text-sm font-medium"
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
    UsersIcon,
    CheckCircleIcon,
    XCircleIcon,
    ShieldCheckIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object
});

const columns = [
    { key: 'name', label: 'User', sortable: true },
    { key: 'role', label: 'Role', sortable: true },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'email_verified_at', label: 'Verified', sortable: false },
    { key: 'created_at', label: 'Joined', sortable: true }
];

const filters = ref({
    role: props.filters?.role || '',
    status: props.filters?.status || '',
    email_verified: props.filters?.email_verified || ''
});

const handleSearch = (query) => {
    router.get(route('admin.users.index'), {
        search: query,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSort = ({ key, order }) => {
    router.get(route('admin.users.index'), {
        sort: key,
        order: order,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSelect = (selected) => {
    console.log('Selected users:', selected);
};

const applyFilters = () => {
    router.get(route('admin.users.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        role: '',
        status: '',
        email_verified: ''
    };
    router.get(route('admin.users.index'));
};

const suspendUser = (id) => {
    const reason = prompt('Enter suspension reason:');
    if (!reason) return;

    if (confirm('Suspend this user?')) {
        router.post(route('admin.users.suspend', id), { reason });
    }
};

const deleteUser = (id) => {
    if (confirm('Are you sure? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', id));
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
