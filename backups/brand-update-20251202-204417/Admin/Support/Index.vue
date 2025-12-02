<template>
    <AdminLayout>
        <Head title="Support Tickets" />

        <div class="py-rhythm-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header with Stats -->
                <div class="mb-rhythm-lg">
                    <div class="md:flex md:items-center md:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Support Tickets</h1>
                            <p class="mt-1 text-sm text-gray-600">Manage and respond to customer support requests</p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="mt-rhythm-md grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-md bg-green-500 p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Open</dt>
                                            <dd class="text-2xl font-semibold text-gray-900">{{ stats.open }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-md bg-blue-500 p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                            <dd class="text-2xl font-semibold text-gray-900">{{ stats.in_progress }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-md bg-purple-500 p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Resolved</dt>
                                            <dd class="text-2xl font-semibold text-gray-900">{{ stats.resolved }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-md bg-red-500 p-3">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Urgent</dt>
                                            <dd class="text-2xl font-semibold text-gray-900">{{ stats.urgent }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow rounded-lg mb-rhythm-md">
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                            <div>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search tickets..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                            <div>
                                <select v-model="filterStatus" @change="applyFilters" class="w-full rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm">
                                    <option value="">All Status</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="awaiting_reply">Awaiting Reply</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="filterPriority" @change="applyFilters" class="w-full rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm">
                                    <option value="">All Priority</option>
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="filterCategory" @change="applyFilters" class="w-full rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm">
                                    <option value="">All Categories</option>
                                    <option value="visa">🛂 Visa</option>
                                    <option value="jobs">💼 Jobs</option>
                                    <option value="account">👤 Account</option>
                                    <option value="payment">💳 Payment</option>
                                    <option value="services">🎯 Services</option>
                                    <option value="technical">🔧 Technical</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="filterAssigned" @change="applyFilters" class="w-full rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm">
                                    <option value="">All Tickets</option>
                                    <option value="me">Assigned to Me</option>
                                    <option value="unassigned">Unassigned</option>
                                </select>
                            </div>
                            <div>
                                <button @click="clearFilters" class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Replies</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ ticket.ticket_number }}</div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ ticket.subject }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ ticket.user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ ticket.user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm">{{ categoryLabel(ticket.category) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="priorityClass(ticket.priority)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ priorityLabel(ticket.priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="statusClass(ticket.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ statusLabel(ticket.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span v-if="ticket.assigned_to">{{ ticket.assigned_to.name }}</span>
                                        <span v-else class="text-gray-400 italic">Unassigned</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ticket.replies_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(ticket.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.support-tickets.show', ticket.id)" class="text-ocean-600 hover:text-ocean-900">
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tickets.data.length > 0" class="px-4 py-3 border-t border-gray-200">
                        <Pagination :links="tickets.links" />
                    </div>

                    <!-- Empty State -->
                    <div v-if="tickets.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
                        <p class="mt-1 text-sm text-gray-500">No support tickets match your current filters.</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    tickets: Object,
    staffMembers: Array,
    filters: Object,
    stats: Object,
});

const searchQuery = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');
const filterPriority = ref(props.filters?.priority || '');
const filterCategory = ref(props.filters?.category || '');
const filterAssigned = ref(props.filters?.assigned || '');

const applyFilters = () => {
    router.get(route('admin.support-tickets.index'), {
        search: searchQuery.value,
        status: filterStatus.value,
        priority: filterPriority.value,
        category: filterCategory.value,
        assigned: filterAssigned.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    filterStatus.value = '';
    filterPriority.value = '';
    filterCategory.value = '';
    filterAssigned.value = '';
    applyFilters();
};

const statusClass = (status) => {
    const classes = {
        open: 'bg-green-100 text-green-800',
        in_progress: 'bg-blue-100 text-blue-800',
        awaiting_reply: 'bg-yellow-100 text-yellow-800',
        resolved: 'bg-purple-100 text-purple-800',
        closed: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const statusLabel = (status) => {
    const labels = {
        open: 'Open',
        in_progress: 'In Progress',
        awaiting_reply: 'Awaiting Reply',
        resolved: 'Resolved',
        closed: 'Closed',
    };
    return labels[status] || status;
};

const priorityClass = (priority) => {
    const classes = {
        urgent: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        normal: 'bg-blue-100 text-blue-800',
        low: 'bg-gray-100 text-gray-800',
    };
    return classes[priority] || 'bg-gray-100 text-gray-800';
};

const priorityLabel = (priority) => {
    const labels = {
        urgent: 'Urgent',
        high: 'High',
        normal: 'Normal',
        low: 'Low',
    };
    return labels[priority] || priority;
};

const categoryLabel = (category) => {
    const labels = {
        visa: '🛂 Visa',
        jobs: '💼 Jobs',
        account: '👤 Account',
        payment: '💳 Payment',
        services: '🎯 Services',
        technical: '🔧 Technical',
    };
    return labels[category] || category;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>
