<!-- Modern Redesigned Users Management Page -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    UsersIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    CheckCircleIcon,
    XCircleIcon,
    ShieldCheckIcon,
    ArrowDownTrayIcon,
    EyeIcon,
    NoSymbolIcon,
    UserPlusIcon,
    PencilIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();

const search = ref(props.filters.search || '');
const selectedUsers = ref([]);
const showFilters = ref(false);
const filters = ref({
    role: props.filters.role || '',
    status: props.filters.status || '',
    email_verified: props.filters.email_verified || '',
    country_id: props.filters.country_id || '',
});

const hasFilters = computed(() => {
    return search.value || filters.value.role || filters.value.status || 
           filters.value.email_verified || filters.value.country_id;
});

const toggleSelectAll = () => {
    if (selectedUsers.value.length === props.users.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map(u => u.id);
    }
};

const toggleSelect = (userId) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
};

const searchUsers = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        ...filters.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    filters.value = {
        role: '',
        status: '',
        email_verified: '',
        country_id: '',
    };
    router.get(route('admin.users.index'));
};

const bulkSuspend = () => {
    if (selectedUsers.value.length === 0) {
        alert('Please select users to suspend');
        return;
    }

    const reason = prompt('Enter suspension reason:');
    if (!reason) return;

    if (confirm(`Suspend ${selectedUsers.value.length} user(s)?`)) {
        router.post(route('admin.users.bulk-suspend'), {
            user_ids: selectedUsers.value,
            reason: reason,
        }, {
            onSuccess: () => {
                selectedUsers.value = [];
            },
        });
    }
};

const bulkUnsuspend = () => {
    if (selectedUsers.value.length === 0) {
        alert('Please select users to unsuspend');
        return;
    }

    if (confirm(`Unsuspend ${selectedUsers.value.length} user(s)?`)) {
        router.post(route('admin.users.bulk-unsuspend'), {
            user_ids: selectedUsers.value,
        }, {
            onSuccess: () => {
                selectedUsers.value = [];
            },
        });
    }
};

const exportUsers = () => {
    window.location.href = route('admin.users.export', {
        search: search.value,
        ...filters.value,
    });
};

// Impersonation helpers
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role?.slug === 'admin');
const impersonating = computed(() => page.props.auth?.user?.impersonating);

const canImpersonate = (user) => {
    if (!isAdmin.value) return false;
    if (impersonating.value) return false;
    return user.role?.slug !== 'admin';
};

const impersonateUser = (user) => {
    if (!canImpersonate(user)) return;
    
    const purpose = prompt(`Impersonate ${user.name}?\n\nPlease provide a reason (required for audit trail):`);
    if (!purpose || purpose.trim() === '') {
        return;
    }
    
    router.post(route('admin.users.impersonate', user.id), {
        purpose: purpose.trim()
    }, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('dashboard'));
        },
        onError: (errors) => {
            console.error('Impersonation failed:', errors);
            alert('Failed to impersonate user. ' + (errors.purpose || 'Please try again.'));
        }
    });
};
</script>

<template>
    <Head title="User Management - BideshGomon Admin" />

    <AdminLayout>
        <!-- PAGE HEADER -->
        <PageHeader
            title="User Management"
            description="Manage and moderate all platform users, roles, and permissions"
            :primaryAction="{
                label: 'Add User',
                onClick: () => router.visit(route('admin.users.create')),
                icon: UserPlusIcon
            }"
            :secondaryActions="[
                { label: 'Export CSV', onClick: exportUsers, icon: ArrowDownTrayIcon }
            ]"
        />

        <!-- MAIN CONTENT -->
        <div class="page-container space-y-6">
            
            <!-- STATS GRID -->
            <div class="grid-stats">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <UsersIcon class="h-6 w-6 text-growth-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Users</p>
                        <p class="stat-card-value">{{ stats.total.toLocaleString() }}</p>
                        <p class="stat-card-change text-gray-500">
                            All registered users
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-emerald-100">
                        <CheckCircleIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Active Users</p>
                        <p class="stat-card-value">{{ stats.active.toLocaleString() }}</p>
                        <p class="stat-card-change text-emerald-600">
                            {{ Math.round((stats.active / stats.total) * 100) }}% of total
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-amber-100">
                        <XCircleIcon class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Unverified</p>
                        <p class="stat-card-value">{{ stats.unverified.toLocaleString() }}</p>
                        <p class="stat-card-change text-amber-600">
                            Pending verification
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-red-100">
                        <NoSymbolIcon class="h-6 w-6 text-red-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Suspended</p>
                        <p class="stat-card-value">{{ stats.suspended.toLocaleString() }}</p>
                        <p class="stat-card-change text-red-600">
                            Requires action
                        </p>
                    </div>
                </div>
            </div>

            <!-- SEARCH & FILTERS CARD -->
            <div class="card-base p-6">
                <div class="space-y-4">
                    <!-- Search Row -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <FormInput
                                v-model="search"
                                placeholder="Search by name, email, or phone..."
                                :icon="MagnifyingGlassIcon"
                                @enter="searchUsers"
                            />
                        </div>
                        <select v-model="filters.role" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="agency">Agency</option>
                            <option value="consultant">Consultant</option>
                        </select>
                        <select v-model="filters.status" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <!-- Collapsible Advanced Filters -->
                    <div v-if="showFilters" class="pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <select v-model="filters.email_verified" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white">
                                <option value="">All Verification Status</option>
                                <option value="1">Verified</option>
                                <option value="0">Unverified</option>
                            </select>
                            <select v-model="filters.country_id" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white">
                                <option value="">All Countries</option>
                                <!-- Add country options here -->
                            </select>
                        </div>
                    </div>

                    <!-- Action Row -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3">
                            <button @click="searchUsers" class="btn-primary">
                                <MagnifyingGlassIcon class="h-5 w-5 mr-2" />
                                Search
                            </button>
                            <button @click="clearFilters" v-if="hasFilters" class="btn-secondary">
                                Reset
                            </button>
                            <button
                                @click="showFilters = !showFilters"
                                class="btn-secondary"
                            >
                                <FunnelIcon class="h-5 w-5 mr-2" />
                                {{ showFilters ? 'Hide' : 'Show' }} Filters
                            </button>
                        </div>
                        <span v-if="hasFilters" class="text-sm text-gray-500">
                            {{ users.total }} results found
                        </span>
                    </div>

                    <!-- Bulk Actions -->
                    <div v-if="selectedUsers.length > 0" class="pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between bg-blue-50 rounded-2xl p-4">
                            <span class="text-sm font-medium text-blue-900">
                                {{ selectedUsers.length }} user(s) selected
                            </span>
                            <div class="flex gap-2">
                                <button @click="bulkUnsuspend" class="btn-secondary text-sm">
                                    Unsuspend
                                </button>
                                <button @click="bulkSuspend" class="btn-secondary text-sm">
                                    Suspend
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DATA TABLE CARD -->
            <div class="card-base overflow-hidden">
                <div v-if="users.data.length > 0" class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="w-12">
                                    <input
                                        type="checkbox"
                                        @change="toggleSelectAll"
                                        :checked="selectedUsers.length === users.data.length"
                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                    />
                                </th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Email Verified</th>
                                <th>Joined</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <input
                                        type="checkbox"
                                        :checked="selectedUsers.includes(user.id)"
                                        @change="toggleSelect(user.id)"
                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                    />
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <StatusBadge :status="user.role?.slug || 'user'" size="sm" />
                                </td>
                                <td>
                                    <StatusBadge 
                                        :status="user.suspended_at ? 'suspended' : 'active'" 
                                        size="sm" 
                                    />
                                </td>
                                <td>
                                    <StatusBadge 
                                        :status="user.email_verified_at ? 'verified' : 'unverified'" 
                                        size="sm" 
                                    />
                                </td>
                                <td>
                                    <span class="text-sm text-gray-600">
                                        {{ formatDate(user.created_at) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.users.show', user.id)"
                                            class="text-growth-600 hover:text-blue-700 text-sm font-medium"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="route('admin.users.edit', user.id)"
                                            class="text-gray-600 hover:text-gray-700 text-sm font-medium"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            v-if="canImpersonate(user)"
                                            @click="impersonateUser(user)"
                                            class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                                        >
                                            Impersonate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-else
                    icon="UserGroupIcon"
                    title="No users found"
                    description="Try adjusting your search criteria or create a new user to get started"
                    :action="{
                        label: 'Create User',
                        onClick: () => router.visit(route('admin.users.create')),
                        icon: UserPlusIcon
                    }"
                    :secondaryAction="{
                        label: 'Clear Filters',
                        onClick: clearFilters
                    }"
                />

                <!-- Pagination -->
                <div v-if="users.data.length > 0" class="border-t border-gray-200 px-6 py-4">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
