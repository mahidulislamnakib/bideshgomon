<template>
    <AuthenticatedLayout>
        <Head title="Support Tickets" />

        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30">
            <div class="py-8 sm:py-12">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <!-- Page Header -->
                    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">
                                My <span class="text-growth-600">Support</span> Tickets
                            </h1>
                            <p class="mt-2 text-lg text-gray-600">
                                Track your requests and get help from our team
                            </p>
                        </div>
                        <Link 
                            :href="route('support.create')"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-semibold rounded-xl shadow-lg shadow-growth-500/30 hover:shadow-xl hover:shadow-growth-500/40 transition-all"
                        >
                            <PlusIcon class="w-5 h-5" />
                            Create New Ticket
                        </Link>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-growth-100 flex items-center justify-center">
                                    <ChatBubbleLeftRightIcon class="w-6 h-6 text-growth-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ tickets.total || tickets.data.length }}</p>
                                    <p class="text-sm text-gray-500">Total Tickets</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                    <CheckCircleIcon class="w-6 h-6 text-green-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ openCount }}</p>
                                    <p class="text-sm text-gray-500">Open</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                    <ClockIcon class="w-6 h-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ inProgressCount }}</p>
                                    <p class="text-sm text-gray-500">In Progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                                    <CheckBadgeIcon class="w-6 h-6 text-purple-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ resolvedCount }}</p>
                                    <p class="text-sm text-gray-500">Resolved</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Sidebar -->
                        <div class="lg:col-span-1 space-y-6">
                            
                            <!-- Filters Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                                <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <FunnelIcon class="w-5 h-5 text-white" />
                                        </div>
                                        <h3 class="font-bold text-white">Filter Tickets</h3>
                                    </div>
                                </div>
                                <div class="p-6 space-y-5">
                                    <!-- Status Filter -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Status</label>
                                        <select 
                                            v-model="filterStatus" 
                                            @change="applyFilters"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-growth-500 transition-all text-gray-700 bg-gray-50"
                                        >
                                            <option value="">All Status</option>
                                            <option value="open">🟢 Open</option>
                                            <option value="in_progress">🔵 In Progress</option>
                                            <option value="awaiting_reply">🟡 Awaiting Reply</option>
                                            <option value="resolved">🟣 Resolved</option>
                                            <option value="closed">⚫ Closed</option>
                                        </select>
                                    </div>

                                    <!-- Category Filter -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Category</label>
                                        <select 
                                            v-model="filterCategory" 
                                            @change="applyFilters"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-growth-500 transition-all text-gray-700 bg-gray-50"
                                        >
                                            <option value="">All Categories</option>
                                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                                {{ cat.emoji }} {{ cat.label }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Clear Filters -->
                                    <button 
                                        v-if="filterStatus || filterCategory"
                                        @click="clearFilters"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-gray-600 hover:text-red-600 border-2 border-gray-200 hover:border-red-300 hover:bg-red-50 rounded-xl transition-all"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                        Clear All Filters
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Tips Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                        <LightBulbIcon class="w-5 h-5 text-yellow-600" />
                                    </div>
                                    <h3 class="font-bold text-gray-900">Quick Tips</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Click any ticket to view details</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Reply directly from ticket page</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Urgent tickets are prioritized</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Keep tickets updated for faster help</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Response Time Card -->
                            <div class="bg-gradient-to-br from-growth-600 to-growth-700 rounded-2xl shadow-lg p-6 text-white">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <ClockIcon class="w-5 h-5 text-white" />
                                    </div>
                                    <h3 class="font-bold text-lg">Response Time</h3>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-white/20">
                                        <span class="text-growth-100">Low Priority</span>
                                        <span class="font-semibold">24-48 hours</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-white/20">
                                        <span class="text-growth-100">Normal</span>
                                        <span class="font-semibold">12-24 hours</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-white/20">
                                        <span class="text-growth-100">High</span>
                                        <span class="font-semibold">4-12 hours</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-growth-100">Urgent</span>
                                        <span class="font-bold">&lt; 4 hours</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Need Help Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                <h3 class="font-bold text-lg text-gray-900 mb-2">Need Faster Help?</h3>
                                <p class="text-sm text-gray-600 mb-4">Create a new ticket with all details for faster resolution.</p>
                                <Link 
                                    :href="route('support.create')"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-semibold rounded-xl hover:from-growth-700 hover:to-growth-800 transition-all shadow-lg shadow-growth-500/25"
                                >
                                    <PlusIcon class="w-5 h-5" />
                                    Create New Ticket
                                </Link>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="lg:col-span-2">
                            
                            <!-- Results Count -->
                            <div class="flex items-center justify-between mb-4">
                                <p class="text-sm text-gray-600">
                                    Showing <span class="font-semibold text-gray-900">{{ tickets.data.length }}</span> 
                                    ticket{{ tickets.data.length !== 1 ? 's' : '' }}
                                    <span v-if="filterStatus || filterCategory" class="text-growth-600">
                                        (filtered)
                                    </span>
                                </p>
                            </div>

                            <!-- Tickets List -->
                            <div v-if="tickets.data.length > 0" class="space-y-4">
                                <Link 
                                    v-for="ticket in tickets.data" 
                                    :key="ticket.id" 
                                    :href="route('support.show', ticket.id)" 
                                    class="block group"
                                >
                                    <div class="bg-white rounded-2xl border-2 border-gray-100 hover:border-growth-300 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
                                        <!-- Priority Banner (for urgent/high) -->
                                        <div 
                                            v-if="ticket.priority === 'urgent' || ticket.priority === 'high'"
                                            :class="[
                                                'px-4 py-1.5 text-xs font-semibold',
                                                ticket.priority === 'urgent' ? 'bg-red-500 text-white' : 'bg-orange-400 text-orange-900'
                                            ]"
                                        >
                                            <span class="flex items-center gap-1">
                                                <ExclamationTriangleIcon class="w-3.5 h-3.5" />
                                                {{ ticket.priority === 'urgent' ? 'URGENT PRIORITY' : 'HIGH PRIORITY' }}
                                            </span>
                                        </div>

                                        <div class="p-5 sm:p-6">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex-1 min-w-0">
                                                    <!-- Header Row -->
                                                    <div class="flex items-center gap-3 flex-wrap mb-3">
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-mono font-semibold">
                                                            <TicketIcon class="w-3.5 h-3.5" />
                                                            {{ ticket.ticket_number }}
                                                        </span>
                                                        <span :class="[statusClasses[ticket.status], 'px-2.5 py-1 rounded-lg text-xs font-semibold']">
                                                            {{ statusLabels[ticket.status] }}
                                                        </span>
                                                        <span :class="[categoryClasses[ticket.category], 'px-2.5 py-1 rounded-lg text-xs font-medium']">
                                                            {{ getCategoryEmoji(ticket.category) }} {{ categoryLabels[ticket.category] }}
                                                        </span>
                                                    </div>

                                                    <!-- Title -->
                                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-growth-600 transition-colors mb-2">
                                                        {{ ticket.subject }}
                                                    </h3>

                                                    <!-- Message Preview -->
                                                    <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                                                        {{ ticket.message }}
                                                    </p>

                                                    <!-- Meta Row -->
                                                    <div class="flex items-center gap-4 flex-wrap text-xs text-gray-500">
                                                        <span class="flex items-center gap-1.5">
                                                            <CalendarIcon class="w-4 h-4" />
                                                            {{ formatDate(ticket.created_at) }}
                                                        </span>
                                                        <span v-if="ticket.replies_count > 0" class="flex items-center gap-1.5 font-medium text-growth-600">
                                                            <ChatBubbleLeftIcon class="w-4 h-4" />
                                                            {{ ticket.replies_count }} {{ ticket.replies_count === 1 ? 'reply' : 'replies' }}
                                                        </span>
                                                        <span v-if="ticket.assigned_to" class="flex items-center gap-1.5">
                                                            <UserCircleIcon class="w-4 h-4" />
                                                            {{ ticket.assigned_to.name }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Arrow -->
                                                <div class="flex-shrink-0 hidden sm:block">
                                                    <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-growth-100 flex items-center justify-center transition-colors">
                                                        <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-growth-600 group-hover:translate-x-0.5 transition-all" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-growth-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <ChatBubbleLeftRightIcon class="w-10 h-10 text-growth-600" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">No support tickets yet</h3>
                                <p class="text-gray-600 mb-6 max-w-sm mx-auto">
                                    Need help? Create your first support ticket and our team will assist you promptly.
                                </p>
                                <Link 
                                    :href="route('support.create')"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-semibold rounded-xl shadow-lg shadow-growth-500/30 hover:shadow-xl transition-all"
                                >
                                    <PlusIcon class="w-5 h-5" />
                                    Create Your First Ticket
                                </Link>
                            </div>

                            <!-- Pagination -->
                            <div v-if="tickets.data.length > 0 && tickets.links && tickets.links.length > 3" class="mt-8">
                                <div class="flex items-center justify-center gap-2">
                                    <template v-for="(link, index) in tickets.links" :key="index">
                                        <Link 
                                            v-if="link.url"
                                            :href="link.url"
                                            :class="[
                                                'px-4 py-2 text-sm font-medium rounded-xl transition-all',
                                                link.active 
                                                    ? 'bg-growth-600 text-white shadow-lg shadow-growth-500/30' 
                                                    : 'bg-white text-gray-600 border border-gray-200 hover:border-growth-300 hover:text-growth-600'
                                            ]"
                                            v-html="link.label"
                                        />
                                        <span 
                                            v-else
                                            class="px-4 py-2 text-sm text-gray-400"
                                            v-html="link.label"
                                        />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help CTA -->
                    <div class="mt-12 bg-gradient-to-r from-growth-600 to-emerald-600 rounded-2xl p-8 text-center text-white relative overflow-hidden">
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'40\' height=\'40\' viewBox=\'0 0 40 40\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\' fill-rule=\'evenodd\'%3E%3Cpath d=\'M0 40L40 0H20L0 20M40 40V20L20 40\'/%3E%3C/g%3E%3C/svg%3E');"></div>
                        </div>
                        <div class="relative">
                            <h2 class="text-2xl font-bold mb-2">Need Immediate Assistance?</h2>
                            <p class="text-white/80 mb-6 max-w-lg mx-auto">
                                For urgent matters, you can reach our support team directly via phone or WhatsApp
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a 
                                    href="tel:+8801910638075"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-growth-600 font-semibold rounded-xl hover:bg-gray-100 transition-colors shadow-lg"
                                >
                                    <PhoneIcon class="w-5 h-5" />
                                    Call Support
                                </a>
                                <a 
                                    href="https://wa.me/8801910638075"
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/10 text-white font-semibold rounded-xl hover:bg-white/20 transition-colors border-2 border-white/30"
                                >
                                    <ChatBubbleOvalLeftEllipsisIcon class="w-5 h-5" />
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import { 
    ChatBubbleLeftRightIcon, 
    PlusIcon, 
    FunnelIcon,
    XMarkIcon,
    LightBulbIcon,
    ChevronRightIcon,
    CalendarIcon,
    ChatBubbleLeftIcon,
    UserCircleIcon,
    TicketIcon,
    PhoneIcon,
    ChatBubbleOvalLeftEllipsisIcon,
    ExclamationTriangleIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon, CheckBadgeIcon, CheckIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    tickets: Object,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();

const filterStatus = ref(props.filters?.status || '');
const filterCategory = ref(props.filters?.category || '');

const categories = [
    { value: 'visa', label: 'Visa & Immigration', emoji: '🛂' },
    { value: 'jobs', label: 'Jobs & Applications', emoji: '💼' },
    { value: 'account', label: 'Account & Profile', emoji: '👤' },
    { value: 'payment', label: 'Payment & Wallet', emoji: '💳' },
    { value: 'services', label: 'Services & Booking', emoji: '🎯' },
    { value: 'technical', label: 'Technical Support', emoji: '🔧' },
];

const statusLabels = {
    open: 'Open',
    in_progress: 'In Progress',
    awaiting_reply: 'Awaiting Reply',
    resolved: 'Resolved',
    closed: 'Closed',
};

const statusClasses = {
    open: 'bg-green-100 text-green-700',
    in_progress: 'bg-blue-100 text-blue-700',
    awaiting_reply: 'bg-yellow-100 text-yellow-700',
    resolved: 'bg-purple-100 text-purple-700',
    closed: 'bg-gray-100 text-gray-700',
};

const categoryLabels = {
    visa: 'Visa',
    jobs: 'Jobs',
    account: 'Account',
    payment: 'Payment',
    services: 'Services',
    technical: 'Technical',
};

const categoryClasses = {
    visa: 'bg-indigo-50 text-indigo-700',
    jobs: 'bg-amber-50 text-amber-700',
    account: 'bg-cyan-50 text-cyan-700',
    payment: 'bg-emerald-50 text-emerald-700',
    services: 'bg-rose-50 text-rose-700',
    technical: 'bg-gray-100 text-gray-700',
};

const getCategoryEmoji = (category) => {
    const cat = categories.find(c => c.value === category);
    return cat?.emoji || '📋';
};

// Computed counts
const openCount = computed(() => 
    props.tickets.data.filter(t => t.status === 'open').length
);
const inProgressCount = computed(() => 
    props.tickets.data.filter(t => t.status === 'in_progress').length
);
const resolvedCount = computed(() => 
    props.tickets.data.filter(t => t.status === 'resolved').length
);

const applyFilters = () => {
    router.get(route('support.index'), {
        status: filterStatus.value,
        category: filterCategory.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterStatus.value = '';
    filterCategory.value = '';
    applyFilters();
};
</script>
