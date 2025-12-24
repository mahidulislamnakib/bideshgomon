<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import BulkActionsBar from '@/Components/ui/BulkActionsBar.vue';
import SortableHeader from '@/Components/ui/SortableHeader.vue';
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue';
import RowActionsDropdown from '@/Components/ui/RowActionsDropdown.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from '@/Composables/useConfirm';
import { useDebouncedSearch } from '@/Composables/useDebouncedSearch';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import Pagination from '@/Components/Pagination.vue';
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
    TrashIcon,
    UserPlusIcon,
    PencilIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object,
});

const toast = useToast();
const { confirmBulk } = useConfirm();

const { search, isSearching, debouncedSearch } = useDebouncedSearch({
    routeName: 'admin.users.index',
    initialFilters: props.filters,
    delay: 400,
});

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

const selectedUsers = ref([]);
const showFilters = ref(false);
const loading = ref(false);
const filters = ref({
    role: props.filters.role || '',
    status: props.filters.status || '',
    email_verified: props.filters.email_verified || '',
    country_id: props.filters.country_id || '',
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

const applyFilters = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        ...filters.value,
        sort: sortColumn.value || undefined,
        direction: sortColumn.value ? sortDirection.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    sortColumn.value = '';
    sortDirection.value = 'asc';
    filters.value = {
        role: '',
        status: '',
        email_verified: '',
        country_id: '',
    };
    router.get(route('admin.users.index'));
};

const bulkSuspend = async () => {
    if (selectedUsers.value.length === 0) {
        toast.warning('Please select users to suspend');
        return;
    }

    const reason = prompt('Enter suspension reason:');
    if (!reason) return;

    const confirmed = await confirmBulk('suspend', selectedUsers.value.length, 'user');
    if (confirmed) {
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

const bulkUnsuspend = async () => {
    if (selectedUsers.value.length === 0) {
        toast.warning('Please select users to unsuspend');
        return;
    }

    const confirmed = await confirmBulk('unsuspend', selectedUsers.value.length, 'user');
    if (confirmed) {
        router.post(route('admin.users.bulk-unsuspend'), {
            user_ids: selectedUsers.value,
        }, {
            onSuccess: () => {
                selectedUsers.value = [];
            },
        });
    }
};

const bulkActions = computed(() => [
    { key: 'suspend', label: 'Suspend', icon: 'NoSymbolIcon', variant: 'danger', handler: bulkSuspend },
    { key: 'unsuspend', label: 'Unsuspend', icon: 'CheckCircleIcon', variant: 'success', handler: bulkUnsuspend },
])

const exportUsers = () => {
    window.location.href = route('admin.users.export', {
        search: search.value,
        ...filters.value,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Impersonation helpers
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role?.slug === 'admin');
const impersonating = computed(() => page.props.auth?.user?.impersonating);

const canImpersonate = (user) => {
    if (!isAdmin.value) return false;
    if (impersonating.value) return false; // Already impersonating someone
    // Do not impersonate admins (policy)
    return user.role?.slug !== 'admin';
};

const impersonateUser = (user) => {
    if (!canImpersonate(user)) return;
    
    const purpose = prompt(`Impersonate ${user.name}?\n\nPlease provide a reason (required for audit trail):`);
    if (!purpose || purpose.trim() === '') {
        return; // User cancelled or provided empty reason
    }
    
    router.post(route('admin.users.impersonate', user.id), {
        purpose: purpose.trim()
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect to dashboard after successful impersonation
            router.visit(route('dashboard'));
        },
        onError: (errors) => {
            console.error('Impersonation failed:', errors);
            toast.error('Failed to impersonate user', errors.purpose || 'Please try again.');
        }
    });
};

// Get actions for a user row
const getUserActions = (user) => {
    const actions = [
        [
            { label: 'View Details', icon: EyeIcon, route: 'admin.users.show', routeParams: user.id },
            { label: 'Edit User', icon: PencilIcon, route: 'admin.users.edit', routeParams: user.id },
        ],
    ];
    
    // Add impersonate action if allowed
    if (canImpersonate(user)) {
        actions.push([
            { label: 'Impersonate', icon: UserPlusIcon, onClick: () => impersonateUser(user) },
        ]);
    }
    
    // Add suspend/unsuspend action
    actions.push([
        user.suspended_at 
            ? { label: 'Unsuspend', icon: CheckCircleIcon, onClick: () => handleBulkAction('unsuspend', [user.id]) }
            : { label: 'Suspend', icon: NoSymbolIcon, danger: true, onClick: () => handleBulkAction('suspend', [user.id]) },
    ]);
    
    return actions;
};

// Keyboard shortcuts
const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts()

// Page-specific shortcuts
const pageShortcuts = [
    { key: 'c', description: 'Create new user', action: () => router.visit(route('admin.users.create')) },
    { key: '/', description: 'Focus search', action: () => document.querySelector('input[type="search"], input[placeholder*="Search"]')?.focus() },
    { key: 'r', description: 'Refresh page', action: () => router.reload() },
]

registerShortcuts(pageShortcuts)
</script>

<template>
    <Head title="User Management" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Users', href: null }
            ]" />
        </template>

        <PageSkeleton v-if="loading" type="index" :table-rows="10" :table-columns="6" />
        <div v-else>
        <!-- Modern Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
            <!-- Animated background pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Gradient orbs -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-accent-500/30 to-accent-600/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-primary-500/30 to-primary-400/20 rounded-full blur-3xl"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                <UsersIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-300 uppercase tracking-wider">Admin</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2">
                            User Management
                        </h1>
                        <p class="text-sm sm:text-base text-gray-300">
                            Manage and moderate platform users with advanced filtering and bulk actions
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('admin.users.create')"
                            class="inline-flex items-center px-5 py-2.5 rounded-xl font-semibold gap-2 shadow-lg transition-all" style="background: linear-gradient(135deg, #14a800 0%, #108a00 100%); color: white;"
                        >
                            <UserPlusIcon class="h-5 w-5" />
                            Create User
                        </Link>
                        <button
                            @click="exportUsers"
                            class="inline-flex items-center px-5 py-2.5 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white rounded-xl transition-all border border-white/20 font-semibold gap-2"
                        >
                            <ArrowDownTrayIcon class="h-5 w-5" />
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Modern Statistics Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 md:gap-6 mb-8">
                    <!-- Total Users -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <UsersIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-growth-600 bg-indigo-50 px-3 py-1 rounded-full">Total</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Users</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-indigo-500 to-indigo-600"></div>
                    </div>

                    <!-- Active Users -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <CheckCircleIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Active</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>
                    </div>

                    <!-- Suspended -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <NoSymbolIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">Blocked</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Suspended</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.suspended }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-red-500 to-red-600"></div>
                    </div>

                    <!-- Verified -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <ShieldCheckIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-growth-600 bg-blue-50 px-3 py-1 rounded-full">Verified</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Verified</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.verified }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                    </div>

                    <!-- Unverified -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <XCircleIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1 rounded-full">Pending</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Unverified</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.unverified }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-600"></div>
                    </div>

                    <!-- Admins -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <ShieldCheckIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Admin</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Admins</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.admins }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
                    </div>
                </div>

                <!-- Modern Search & Filters Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Search Input -->
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    v-model="search"
                                    @input="debouncedSearch"
                                    @keyup.enter="searchUsers"
                                    type="text"
                                    placeholder="Search by name, email, or phone..."
                                    class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400"
                                />
                            </div>
                            <button
                                @click="showFilters = !showFilters"
                                class="px-6 py-3.5 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 hover:from-gray-200 hover:to-gray-300 dark:hover:from-gray-600 dark:hover:to-gray-500 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                            </button>
                        </div>

                        <!-- Filters Panel -->
                        <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                                <select
                                    v-model="filters.role"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Roles</option>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="filters.status"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Verification</label>
                                <select
                                    v-model="filters.email_verified"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All</option>
                                    <option value="verified">Verified</option>
                                    <option value="unverified">Unverified</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button
                                    @click="applyFilters"
                                    class="flex-1 px-4 py-2 bg-growth-600 text-white rounded-2xl hover:bg-growth-700 transition-colors font-medium"
                                >
                                    Apply
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Bulk Actions -->
                <BulkActionsBar
                    :count="selectedUsers.length"
                    item-label="user"
                    :actions="bulkActions"
                    :show-export="true"
                    @clear="selectedUsers = []"
                    @export="exportUsers"
                />

                <!-- Users Table -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedUsers.length === users.data.length && users.data.length > 0"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 text-growth-600 rounded border-gray-300 focus:ring-growth-600"
                                        />
                                    </th>
                                    <SortableHeader 
                                        column="name" 
                                        label="User" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <SortableHeader 
                                        column="email" 
                                        label="Contact" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <SortableHeader 
                                        column="created_at" 
                                        label="Registered" 
                                        :sort-column="sortColumn" 
                                        :sort-direction="sortDirection" 
                                        @sort="sortBy" 
                                    />
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :checked="selectedUsers.includes(user.id)"
                                            @change="toggleSelect(user.id)"
                                            class="h-4 w-4 text-growth-600 rounded border-gray-300 focus:ring-growth-600"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-growth-600 flex items-center justify-center text-white font-semibold">
                                                {{ (user.name || '').charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.country?.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <div class="text-gray-900 dark:text-white">{{ user.email }}</div>
                                            <div v-if="user.phone" class="text-gray-500 dark:text-gray-400">{{ user.phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-semibold',
                                                user.role?.slug === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-growth-600',
                                            ]"
                                        >
                                            {{ user.role?.name || 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span
                                                :class="[
                                                    'px-3 py-1 rounded-full text-xs font-semibold w-fit',
                                                    user.suspended_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800',
                                                ]"
                                            >
                                                {{ user.suspended_at ? 'Suspended' : 'Active' }}
                                            </span>
                                            <span
                                                :class="[
                                                    'px-3 py-1 rounded-full text-xs font-semibold w-fit',
                                                    user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                                ]"
                                            >
                                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <RowActionsDropdown :actions="getUserActions(user)" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <Pagination :links="users.links" />
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
