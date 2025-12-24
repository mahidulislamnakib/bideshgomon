<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    TicketIcon, ClockIcon, CheckCircleIcon, ExclamationTriangleIcon,
    MagnifyingGlassIcon, FunnelIcon, XMarkIcon, EyeIcon,
    UserCircleIcon, ChatBubbleLeftRightIcon, ArrowPathIcon,
    BoltIcon, InboxIcon, UserPlusIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

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

const showAssignModal = ref(false);
const selectedTicket = ref(null);

const assignForm = useForm({
    assigned_to: '',
});

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

const openAssignModal = (ticket) => {
    selectedTicket.value = ticket;
    assignForm.assigned_to = ticket.assigned_to || '';
    showAssignModal.value = true;
};

const submitAssign = () => {
    assignForm.post(route('admin.support-tickets.assign', selectedTicket.value.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            selectedTicket.value = null;
        },
    });
};

const statusConfig = {
    'open': { 
        bg: 'bg-gradient-to-r from-red-500 to-rose-600', 
        text: 'text-white',
        light: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        label: 'Open' 
    },
    'in_progress': { 
        bg: 'bg-gradient-to-r from-amber-400 to-orange-500', 
        text: 'text-white',
        light: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        label: 'In Progress' 
    },
    'awaiting_reply': { 
        bg: 'bg-gradient-to-r from-blue-400 to-indigo-500', 
        text: 'text-white',
        light: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        label: 'Awaiting Reply' 
    },
    'resolved': { 
        bg: 'bg-gradient-to-r from-emerald-500 to-teal-600', 
        text: 'text-white',
        light: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        label: 'Resolved' 
    },
    'closed': { 
        bg: 'bg-gradient-to-r from-gray-400 to-gray-500', 
        text: 'text-white',
        light: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        label: 'Closed' 
    },
};

const priorityConfig = {
    'low': { color: 'text-gray-500', bg: 'bg-gray-100 dark:bg-gray-700', label: 'Low' },
    'normal': { color: 'text-blue-600', bg: 'bg-blue-100 dark:bg-blue-900/30', label: 'Normal' },
    'medium': { color: 'text-blue-600', bg: 'bg-blue-100 dark:bg-blue-900/30', label: 'Medium' },
    'high': { color: 'text-orange-600', bg: 'bg-orange-100 dark:bg-orange-900/30', label: 'High' },
    'urgent': { color: 'text-red-600', bg: 'bg-red-100 dark:bg-red-900/30', label: 'Urgent' },
};

const getStatus = (status) => statusConfig[status] || statusConfig.open;
const getPriority = (priority) => priorityConfig[priority] || priorityConfig.normal;

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-GB', { 
        day: '2-digit',
        month: 'short', 
        year: 'numeric' 
    });
};

const formatTimeAgo = (date) => {
    if (!date) return '';
    const now = new Date();
    const then = new Date(date);
    const diffMs = now - then;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return formatDate(date);
};

const hasActiveFilters = computed(() => {
    return searchQuery.value || filterStatus.value || filterPriority.value || filterCategory.value || filterAssigned.value;
});
</script>

<template>
    <Head title="Support Tickets" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-96 h-96 bg-red-500 rounded-full filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-orange-500 rounded-full filter blur-3xl translate-x-1/2 translate-y-1/2"></div>
                </div>
                
                <div class="relative z-10 px-4 py-8 sm:px-6 lg:px-8">
                    <div class="max-w-7xl mx-auto">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2">Support Tickets</h1>
                                <p class="text-gray-400">Manage and respond to customer support requests</p>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/20">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-red-500 rounded-xl shadow-lg shadow-red-500/30">
                                        <InboxIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white">{{ stats?.open || 0 }}</p>
                                        <p class="text-gray-400 text-sm">Open</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/20">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-amber-500 rounded-xl shadow-lg shadow-amber-500/30">
                                        <ArrowPathIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white">{{ stats?.in_progress || 0 }}</p>
                                        <p class="text-gray-400 text-sm">In Progress</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/20">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-emerald-500 rounded-xl shadow-lg shadow-emerald-500/30">
                                        <CheckCircleSolidIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white">{{ stats?.resolved || 0 }}</p>
                                        <p class="text-gray-400 text-sm">Resolved</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/20">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-rose-600 rounded-xl shadow-lg shadow-rose-500/30">
                                        <BoltIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white">{{ stats?.urgent || 0 }}</p>
                                        <p class="text-gray-400 text-sm">Urgent</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-6 relative z-10">
                <!-- Filters Card -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <FunnelIcon class="h-5 w-5 text-gray-500" />
                        <h2 class="font-semibold text-gray-900 dark:text-white">Filters</h2>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="ml-auto text-sm text-red-600 hover:text-red-700 flex items-center gap-1"
                        >
                            <XMarkIcon class="h-4 w-4" />
                            Clear all
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div class="lg:col-span-2">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search tickets..."
                                @keyup.enter="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <select
                                v-model="filterStatus"
                                @change="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600"
                            >
                                <option value="">All Status</option>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="awaiting_reply">Awaiting Reply</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="filterPriority"
                                @change="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600"
                            >
                                <option value="">All Priority</option>
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="filterCategory"
                                @change="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600"
                            >
                                <option value="">All Categories</option>
                                <option value="visa">Visa</option>
                                <option value="jobs">Jobs</option>
                                <option value="account">Account</option>
                                <option value="payment">Payment</option>
                                <option value="services">Services</option>
                                <option value="technical">Technical</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="filterAssigned"
                                @change="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600"
                            >
                                <option value="">All Tickets</option>
                                <option value="me">Assigned to Me</option>
                                <option value="unassigned">Unassigned</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tickets List -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <TicketIcon class="h-5 w-5 text-growth-600" />
                            All Tickets
                            <span class="text-sm font-normal text-gray-500">({{ tickets?.total || 0 }})</span>
                        </h2>
                    </div>

                    <!-- Tickets Grid -->
                    <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
                        <div
                            v-for="ticket in tickets?.data"
                            :key="ticket.id"
                            class="p-5 hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-growth-400 to-teal-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-growth-500/30">
                                        {{ ticket.user?.name?.charAt(0).toUpperCase() || '?' }}
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ ticket.ticket_number }}</span>
                                                <span :class="[getStatus(ticket.status).light]" class="px-2 py-0.5 text-xs font-semibold rounded-full">
                                                    {{ getStatus(ticket.status).label }}
                                                </span>
                                                <span :class="[getPriority(ticket.priority).bg, getPriority(ticket.priority).color]" class="px-2 py-0.5 text-xs font-semibold rounded-full">
                                                    {{ getPriority(ticket.priority).label }}
                                                </span>
                                            </div>
                                            <Link :href="route('admin.support-tickets.show', ticket.id)" class="text-lg font-semibold text-gray-900 dark:text-white hover:text-growth-600 dark:hover:text-growth-400 transition-colors">
                                                {{ ticket.subject }}
                                            </Link>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 line-clamp-2">{{ ticket.message }}</p>
                                        </div>
                                        
                                        <!-- Time -->
                                        <div class="flex-shrink-0 text-right">
                                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ formatTimeAgo(ticket.created_at) }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ ticket.replies_count || 0 }} replies</p>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="flex items-center gap-4 mt-3">
                                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                            <UserCircleIcon class="h-4 w-4" />
                                            <span>{{ ticket.user?.name || 'Unknown' }}</span>
                                        </div>
                                        <div v-if="ticket.assigned_to" class="flex items-center gap-2 text-sm text-purple-600 dark:text-purple-400">
                                            <UserPlusIcon class="h-4 w-4" />
                                            <span>{{ ticket.assigned_to?.name }}</span>
                                        </div>
                                        <div v-else class="flex items-center gap-2 text-sm text-gray-400">
                                            <UserPlusIcon class="h-4 w-4" />
                                            <span>Unassigned</span>
                                        </div>
                                        <div class="ml-auto flex items-center gap-2">
                                            <button
                                                @click="openAssignModal(ticket)"
                                                class="px-3 py-1.5 text-xs font-medium text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-2xl transition-colors"
                                            >
                                                Assign
                                            </button>
                                            <Link
                                                :href="route('admin.support-tickets.show', ticket.id)"
                                                class="inline-flex items-center gap-1 px-4 py-1.5 bg-growth-600 text-white text-xs font-semibold rounded-2xl hover:bg-growth-700 transition-colors"
                                            >
                                                <EyeIcon class="h-4 w-4" />
                                                View
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!tickets?.data?.length" class="text-center py-16">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <TicketIcon class="h-10 w-10 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No tickets found</h3>
                        <p class="text-gray-500 dark:text-gray-400">Try adjusting your filters or search terms</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tickets?.data?.length && tickets.last_page > 1" class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Showing {{ tickets.from }} to {{ tickets.to }} of {{ tickets.total }} tickets
                            </p>
                            <div class="flex gap-2">
                                <Link
                                    v-if="tickets.prev_page_url"
                                    :href="tickets.prev_page_url"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 rounded-2xl hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="tickets.next_page_url"
                                    :href="tickets.next_page_url"
                                    class="px-4 py-2 text-sm font-medium text-white bg-growth-600 rounded-2xl hover:bg-growth-700 transition-colors"
                                >
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAssignModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-purple-500/30">
                        <UserPlusIcon class="h-8 w-8 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Assign Ticket</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center mb-6">Select a staff member to handle this ticket</p>
                    
                    <form @submit.prevent="submitAssign">
                        <select
                            v-model="assignForm.assigned_to"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-purple-500"
                        >
                            <option value="">Select a staff member</option>
                            <option v-for="member in staffMembers" :key="member.id" :value="member.id">
                                {{ member.name }} ({{ member.email }})
                            </option>
                        </select>
                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="showAssignModal = false" class="flex-1 px-4 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 rounded-xl font-medium transition-colors">Cancel</button>
                            <button type="submit" :disabled="assignForm.processing" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-purple-600 hover:to-indigo-700 disabled:opacity-50 transition-all shadow-lg shadow-purple-500/25">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
