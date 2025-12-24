<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import EmptyState from '@/Components/Base/EmptyState.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
    TicketIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    FunnelIcon,
    XMarkIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    tickets: Object,
    stats: Object,
    filters: Object,
})

const { formatDate, formatTime } = useBangladeshFormat()
const showFilters = ref(false)

const searchForm = useForm({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    priority: props.filters?.priority || '',
    category: props.filters?.category || '',
})

const hasActiveFilters = () => {
    return searchForm.search || searchForm.status || searchForm.priority || searchForm.category
}

const search = () => {
    searchForm.get(route('admin.support-tickets.index'), {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    searchForm.reset()
    search()
}

const deleteTicket = (ticketId) => {
    if (confirm('Are you sure you want to delete this support ticket?')) {
        router.delete(route('admin.support-tickets.destroy', ticketId), {
            preserveScroll: true,
        })
    }
}

const getStatusBadgeClass = (status) => {
    const classes = {
        open: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        resolved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        closed: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
    }
    return classes[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        low: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    }
    return classes[priority] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'
}
</script>

<template>
    <Head title="Support Tickets" />

    <AdminLayout>
        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Hero Header with Stats -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="ticketGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#ticketGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <TicketIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">Support Tickets</h1>
                            </div>
                            <p class="text-neutral-300">Manage customer support requests and issues</p>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                                <span v-if="hasActiveFilters()" class="ml-1 px-2 py-0.5 bg-orange-500 text-white text-xs rounded-full">Active</span>
                            </button>
                            <Link
                                :href="route('admin.support-tickets.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:from-orange-600 hover:to-red-600 transition-all shadow-lg shadow-orange-500/25"
                            >
                                <PlusIcon class="h-5 w-5" />
                                New Ticket
                            </Link>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-xl">
                                    <TicketIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total Tickets</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-xl">
                                    <ClockIcon class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Open</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.open || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-xl">
                                    <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Resolved</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.resolved || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-500/20 rounded-xl">
                                    <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Urgent</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.urgent || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Collapsible Filters -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filter Tickets</h3>
                            <button @click="showFilters = false" class="p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-xl transition-colors">
                                <XMarkIcon class="h-5 w-5 text-neutral-500" />
                            </button>
                        </div>

                        <form @submit.prevent="search" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" />
                                    <input
                                        v-model="searchForm.search"
                                        type="text"
                                        placeholder="Search tickets..."
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-orange-500 focus:ring-orange-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status</label>
                                <select
                                    v-model="searchForm.status"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 py-2.5"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Priority</label>
                                <select
                                    v-model="searchForm.priority"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 py-2.5"
                                >
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Category</label>
                                <select
                                    v-model="searchForm.category"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 py-2.5"
                                >
                                    <option value="">All Categories</option>
                                    <option value="technical">Technical</option>
                                    <option value="billing">Billing</option>
                                    <option value="general">General</option>
                                    <option value="visa">Visa</option>
                                    <option value="booking">Booking</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-3">
                                <button
                                    type="submit"
                                    class="flex-1 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white font-medium rounded-xl hover:from-orange-600 hover:to-red-600 transition-all"
                                >
                                    Apply
                                </button>
                                <button
                                    type="button"
                                    @click="clearFilters"
                                    class="px-4 py-2.5 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-xl hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                                >
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </transition>

                <!-- Tickets Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="ticket in tickets?.data" :key="ticket.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">#{{ ticket.id }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ ticket.subject }}</div>
                                        <div class="text-sm text-neutral-500 dark:text-neutral-400 truncate max-w-xs">
                                            {{ ticket.message }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-neutral-900 dark:text-white">{{ ticket.user?.name || 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getPriorityBadgeClass(ticket.priority)" class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize">
                                            {{ ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusBadgeClass(ticket.status)" class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize">
                                            {{ (ticket?.status || '').replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ formatDate(ticket.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('admin.support-tickets.show', ticket.id)"
                                                class="p-2 text-neutral-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-colors"
                                                title="View"
                                            >
                                                <EyeIcon class="h-4 w-4" />
                                            </Link>
                                            <Link
                                                :href="route('admin.support-tickets.edit', ticket.id)"
                                                class="p-2 text-neutral-500 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-xl transition-colors"
                                                title="Edit"
                                            >
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </Link>
                                            <button
                                                @click="deleteTicket(ticket.id)"
                                                class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                                                title="Delete"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="!tickets?.data || tickets.data.length === 0"
                        icon="TicketIcon"
                        title="No support tickets"
                        description="All caught up! There are no support tickets to review at the moment."
                    />

                    <!-- Pagination -->
                    <div v-if="tickets?.data && tickets.data.length > 0" class="bg-neutral-50 dark:bg-neutral-900/50 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Showing <span class="font-semibold text-neutral-900 dark:text-white">{{ tickets.from || 0 }}</span>
                                to <span class="font-semibold text-neutral-900 dark:text-white">{{ tickets.to || 0 }}</span>
                                of <span class="font-semibold text-neutral-900 dark:text-white">{{ tickets.total || 0 }}</span> tickets
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="tickets.prev_page_url"
                                    :href="tickets.prev_page_url"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <span v-else class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl cursor-not-allowed">
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </span>
                                <Link
                                    v-if="tickets.next_page_url"
                                    :href="tickets.next_page_url"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                                <span v-else class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl cursor-not-allowed">
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
