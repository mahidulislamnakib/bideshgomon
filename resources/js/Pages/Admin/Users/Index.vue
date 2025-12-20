<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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

const search = ref(props.filters.search || '');
const selectedUsers = ref([]);
const showFilters = ref(false);
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
            alert('Failed to impersonate user. ' + (errors.purpose || 'Please try again.'));
        }
    });
};
</script>

<template>
    <Head title="User Management" />

    <AdminLayout>
        <!-- Modern Hero Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                                <UsersIcon class="h-8 w-8 text-white" />
                            </div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                                User Management
                            </h1>
                        </div>
                        <p class="text-white/90 text-lg max-w-2xl">
                            Manage and moderate platform users with advanced filtering and bulk actions
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('admin.users.create')"
                            class="inline-flex items-center px-6 py-3.5 bg-white hover:bg-gray-50 text-indigo-600 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold gap-2"
                        >
                            <UserPlusIcon class="h-5 w-5" />
                            Create User
                        </Link>
                        <button
                            @click="exportUsers"
                            class="inline-flex items-center px-6 py-3.5 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-xl transition-all border border-white/30 font-semibold gap-2"
                        >
                            <ArrowDownTrayIcon class="h-5 w-5" />
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">

                <!-- Modern Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
                    <!-- Total Users -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <UsersIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Total</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Users</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-indigo-500 to-indigo-600"></div>
                    </div>

                    <!-- Active Users -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <CheckCircleIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Active</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 mb-1">Active</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.active }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>
                    </div>

                    <!-- Suspended -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <NoSymbolIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">Blocked</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 mb-1">Suspended</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.suspended }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-red-500 to-red-600"></div>
                    </div>

                    <!-- Verified -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <ShieldCheckIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Verified</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 mb-1">Verified</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.verified }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                    </div>

                    <!-- Unverified -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <XCircleIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1 rounded-full">Pending</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 mb-1">Unverified</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.unverified }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-600"></div>
                    </div>

                    <!-- Admins -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                                    <ShieldCheckIcon class="h-6 w-6 text-white" />
                                </div>
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Admin</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 mb-1">Admins</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.admins }}</p>
                        </div>
                        <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
                    </div>
                </div>

                <!-- Modern Search & Filters Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Search Input -->
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    v-model="search"
                                    @keyup.enter="searchUsers"
                                    type="text"
                                    placeholder="Search by name, email, or phone..."
                                    class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white text-gray-900 placeholder-gray-400"
                                />
                            </div>
                            <button
                                @click="showFilters = !showFilters"
                                class="px-6 py-3.5 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                            </button>
                        </div>

                        <!-- Filters Panel -->
                        <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <select
                                    v-model="filters.role"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600"
                                >
                                    <option value="">All Roles</option>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select
                                    v-model="filters.status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Verification</label>
                                <select
                                    v-model="filters.email_verified"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600"
                                >
                                    <option value="">All</option>
                                    <option value="verified">Verified</option>
                                    <option value="unverified">Unverified</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button
                                    @click="applyFilters"
                                    class="flex-1 px-4 py-2 bg-brand-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
                                >
                                    Apply
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Bulk Actions -->
                <div
                    v-if="selectedUsers.length > 0"
                    class="bg-red-50 border border-indigo-200 rounded-lg p-4 mb-6 flex items-center justify-between"
                >
                    <span class="text-indigo-900 font-medium">{{ selectedUsers.length }} user(s) selected</span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkSuspend"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm"
                        >
                            <NoSymbolIcon class="h-4 w-4 inline mr-1" />
                            Suspend
                        </button>
                        <button
                            @click="bulkUnsuspend"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm"
                        >
                            <CheckCircleIcon class="h-4 w-4 inline mr-1" />
                            Unsuspend
                        </button>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedUsers.length === users.data.length && users.data.length > 0"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 text-brand-red-600 rounded border-gray-300 focus:ring-brand-red-600"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registered
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :checked="selectedUsers.includes(user.id)"
                                            @change="toggleSelect(user.id)"
                                            class="h-4 w-4 text-brand-red-600 rounded border-gray-300 focus:ring-brand-red-600"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-brand-red-600 flex items-center justify-center text-white font-semibold">
                                                {{ (user.name || '').charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ user.name }}</div>
                                                <div class="text-sm text-gray-500">{{ user.country?.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <div class="text-gray-900">{{ user.email }}</div>
                                            <div v-if="user.phone" class="text-gray-500">{{ user.phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-semibold',
                                                user.role?.slug === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-brand-red-600',
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.users.show', user.id)"
                                            class="text-brand-red-600 hover:text-red-900 inline-flex items-center gap-1"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                            View
                                        </Link>
                                        <Link
                                            :href="route('admin.users.edit', user.id)"
                                            class="ml-3 text-brand-red-600 hover:text-red-900 inline-flex items-center gap-1"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                            Edit
                                        </Link>
                                        <button
                                            v-if="canImpersonate(user)"
                                            @click="impersonateUser(user)"
                                            class="ml-3 text-amber-600 hover:text-amber-800 inline-flex items-center gap-1"
                                            type="button"
                                        >
                                            <UserPlusIcon class="h-4 w-4" />
                                            Impersonate
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
