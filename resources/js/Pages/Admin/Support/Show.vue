<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    ArrowLeftIcon, ClockIcon, UserCircleIcon,
    CheckCircleIcon, XCircleIcon, PaperAirplaneIcon,
    UserPlusIcon, ExclamationTriangleIcon,
    ChatBubbleLeftRightIcon, PaperClipIcon, ArrowPathIcon,
    EnvelopeIcon, PhoneIcon, LockClosedIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    ticket: Object,
    staffMembers: Array,
});

const replyForm = useForm({
    message: '',
    is_internal: false,
});

const assignForm = useForm({
    assigned_to: props.ticket?.assignedTo?.id || '',
});

const priorityForm = useForm({
    priority: props.ticket?.priority || 'normal',
});

const statusForm = useForm({
    status: props.ticket?.status || 'open',
});

const closingTicket = ref(false);
const reopeningTicket = ref(false);
const showAssignModal = ref(false);

const submitReply = () => {
    replyForm.post(route('admin.support-tickets.reply', props.ticket.id), {
        onSuccess: () => replyForm.reset('message'),
        preserveScroll: true,
    });
};

const assignTicket = () => {
    assignForm.post(route('admin.support-tickets.assign', props.ticket.id), {
        onSuccess: () => showAssignModal.value = false,
    });
};

const updatePriority = () => {
    priorityForm.post(route('admin.support-tickets.update-priority', props.ticket.id));
};

const updateStatus = () => {
    statusForm.put(route('admin.support-tickets.update', props.ticket.id));
};

const closeTicket = () => {
    closingTicket.value = true;
    router.post(route('admin.support-tickets.close', props.ticket.id), {}, {
        onFinish: () => closingTicket.value = false,
    });
};

const reopenTicket = () => {
    reopeningTicket.value = true;
    router.post(route('admin.support-tickets.reopen', props.ticket.id), {}, {
        onFinish: () => reopeningTicket.value = false,
    });
};

const statusConfig = {
    'open': { bg: 'bg-gradient-to-r from-red-500 to-rose-600', icon: ExclamationTriangleIcon, label: 'Open' },
    'in_progress': { bg: 'bg-gradient-to-r from-amber-400 to-orange-500', icon: ArrowPathIcon, label: 'In Progress' },
    'awaiting_reply': { bg: 'bg-gradient-to-r from-blue-400 to-indigo-500', icon: ClockIcon, label: 'Awaiting Reply' },
    'resolved': { bg: 'bg-gradient-to-r from-emerald-500 to-teal-600', icon: CheckCircleSolidIcon, label: 'Resolved' },
    'closed': { bg: 'bg-gradient-to-r from-gray-400 to-gray-500', icon: XCircleIcon, label: 'Closed' },
};

const priorityConfig = {
    'low': { bg: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300', label: 'Low' },
    'normal': { bg: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300', label: 'Normal' },
    'medium': { bg: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300', label: 'Medium' },
    'high': { bg: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300', label: 'High' },
    'urgent': { bg: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300', label: 'Urgent' },
};

const categoryConfig = {
    'visa': { icon: '🛂', label: 'Visa' },
    'jobs': { icon: '💼', label: 'Jobs' },
    'account': { icon: '👤', label: 'Account' },
    'payment': { icon: '💳', label: 'Payment' },
    'services': { icon: '🛠️', label: 'Services' },
    'technical': { icon: '🔧', label: 'Technical' },
    'general': { icon: '📋', label: 'General' },
};

const currentStatus = computed(() => statusConfig[props.ticket?.status] || statusConfig.open);
const currentPriority = computed(() => priorityConfig[props.ticket?.priority] || priorityConfig.normal);
const currentCategory = computed(() => categoryConfig[props.ticket?.category] || categoryConfig.general);

const formatDateTime = (datetime) => {
    if (!datetime) return 'N/A';
    return new Date(datetime).toLocaleString('en-GB', { 
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const formatDate = (datetime) => {
    if (!datetime) return 'N/A';
    return new Date(datetime).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const conversationCount = computed(() => (props.ticket?.replies?.length || 0) + 1);
</script>

<template>
    <Head :title="`Ticket ${ticket?.ticket_number}`" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-96 h-96 bg-growth-500 rounded-full filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl translate-x-1/2 translate-y-1/2"></div>
                </div>
                
                <div class="relative z-10 px-4 py-8 sm:px-6 lg:px-8">
                    <div class="max-w-6xl mx-auto">
                        <Link :href="route('admin.support-tickets.index')" class="inline-flex items-center text-gray-400 hover:text-white mb-6 transition-colors group">
                            <ArrowLeftIcon class="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                            <span class="text-sm">Back to Tickets</span>
                        </Link>

                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span :class="currentStatus.bg" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-white shadow-lg">
                                        <component :is="currentStatus.icon" class="h-4 w-4" />
                                        {{ currentStatus.label }}
                                    </span>
                                    <span :class="currentPriority.bg" class="px-3 py-1.5 rounded-full text-sm font-medium">
                                        {{ currentPriority.label }} Priority
                                    </span>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur text-white rounded-full text-sm">
                                        {{ currentCategory.icon }} {{ currentCategory.label }}
                                    </span>
                                </div>
                                
                                <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">{{ ticket?.subject }}</h1>
                                <p class="text-gray-400">{{ ticket?.ticket_number }} • Created {{ formatDate(ticket?.created_at) }}</p>
                            </div>

                            <div class="flex-shrink-0">
                                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/20 min-w-[220px]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-growth-400 to-teal-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                            {{ ticket?.user?.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <div>
                                            <p class="text-white font-semibold">{{ ticket?.user?.name }}</p>
                                            <p class="text-gray-400 text-sm">{{ ticket?.user?.email }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 mt-6">
                            <button v-if="ticket?.status !== 'closed'" @click="closeTicket" :disabled="closingTicket"
                                class="inline-flex items-center px-5 py-2.5 bg-white/10 backdrop-blur text-white rounded-xl font-medium hover:bg-white/20 transition-all border border-white/20">
                                <XCircleIcon class="h-5 w-5 mr-2" />
                                Close Ticket
                            </button>
                            <button v-if="ticket?.status === 'closed'" @click="reopenTicket" :disabled="reopeningTicket"
                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/25">
                                <ArrowPathIcon class="h-5 w-5 mr-2" />
                                Reopen Ticket
                            </button>
                            <button @click="showAssignModal = true"
                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-purple-600 hover:to-indigo-700 transition-all shadow-lg shadow-purple-500/25">
                                <UserPlusIcon class="h-5 w-5 mr-2" />
                                {{ ticket?.assignedTo ? 'Reassign' : 'Assign Staff' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Conversation Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Original Message -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 bg-gradient-to-r from-growth-50 to-teal-50 dark:from-growth-900/20 dark:to-teal-900/20">
                                <div class="flex items-center gap-3">
                                    <ChatBubbleLeftRightIcon class="h-5 w-5 text-growth-600 dark:text-growth-400" />
                                    <span class="font-semibold text-growth-700 dark:text-growth-400">Original Request</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-growth-400 to-teal-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg flex-shrink-0">
                                        {{ ticket?.user?.name?.charAt(0).toUpperCase() || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ ticket?.user?.name }}</span>
                                            <span class="px-2 py-0.5 bg-growth-100 dark:bg-growth-900/30 text-growth-700 dark:text-growth-300 text-xs font-medium rounded-full">Customer</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(ticket?.created_at) }}</span>
                                        </div>
                                        <div class="prose max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ ticket?.message }}</div>
                                        
                                        <div v-if="ticket?.attachments?.length" class="mt-4 pt-4 border-t border-gray-100 dark:border-neutral-700">
                                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2 flex items-center gap-2">
                                                <PaperClipIcon class="h-4 w-4" />
                                                Attachments
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                <a v-for="(attachment, index) in ticket.attachments" :key="index" :href="`/storage/${attachment.path}`" target="_blank"
                                                    class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-neutral-700 rounded-2xl text-sm font-medium hover:bg-gray-100 dark:hover:bg-neutral-600 transition-colors">
                                                    <PaperClipIcon class="h-4 w-4 text-gray-500" />
                                                    {{ attachment.name }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Replies -->
                        <div v-for="reply in ticket?.replies" :key="reply.id" 
                            class="bg-white dark:bg-neutral-800 rounded-2xl shadow-lg border overflow-hidden"
                            :class="reply.is_internal ? 'border-l-4 border-l-purple-500 border-purple-200 dark:border-purple-800/50' : 'border-neutral-100 dark:border-neutral-700'">
                            <div v-if="reply.is_internal" class="px-4 py-2 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-100 dark:border-purple-800/30">
                                <span class="inline-flex items-center gap-2 text-xs font-medium text-purple-700 dark:text-purple-300">
                                    <LockClosedIcon class="h-3.5 w-3.5" />
                                    Internal Note - Not visible to customer
                                </span>
                            </div>
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow flex-shrink-0" 
                                        :class="reply.user?.id === ticket?.user?.id ? 'bg-gradient-to-br from-growth-400 to-teal-500' : 'bg-gradient-to-br from-blue-500 to-indigo-600'">
                                        {{ reply.user?.name?.charAt(0).toUpperCase() || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ reply.user?.name }}</span>
                                            <span v-if="reply.user?.id !== ticket?.user?.id" class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-full">Staff</span>
                                            <span v-else class="px-2 py-0.5 bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-300 text-xs font-medium rounded-full">Customer</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(reply.created_at) }}</span>
                                        </div>
                                        <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ reply.message }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div v-if="ticket?.status !== 'closed'" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                                <div class="flex items-center gap-3">
                                    <PaperAirplaneIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <span class="font-semibold text-gray-900 dark:text-white">Write Reply</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <form @submit.prevent="submitReply">
                                    <textarea v-model="replyForm.message" rows="4" required placeholder="Type your reply here..."
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition-all"></textarea>
                                    
                                    <div class="flex items-center justify-between mt-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input v-model="replyForm.is_internal" type="checkbox" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500" />
                                            <span class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                                <LockClosedIcon class="h-4 w-4 text-purple-500" />
                                                Internal note
                                            </span>
                                        </label>
                                        
                                        <button type="submit" :disabled="replyForm.processing || !replyForm.message.trim()"
                                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg shadow-blue-500/25 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <PaperAirplaneIcon class="h-4 w-4 mr-2" />
                                            Send Reply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Closed Notice -->
                        <div v-else class="bg-gray-100 dark:bg-neutral-800 rounded-2xl p-8 text-center border-2 border-dashed border-gray-300 dark:border-neutral-600">
                            <XCircleIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Ticket Closed</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">This conversation has been closed.</p>
                            <button @click="reopenTicket" :disabled="reopeningTicket"
                                class="inline-flex items-center px-5 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl font-semibold hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                                <ArrowPathIcon class="h-4 w-4 mr-2" />
                                Reopen Ticket
                            </button>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                    <select v-model="statusForm.status" @change="updateStatus" :disabled="statusForm.processing"
                                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="awaiting_reply">Awaiting Reply</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
                                    <select v-model="priorityForm.priority" @change="updatePriority" :disabled="priorityForm.processing"
                                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Staff -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Assigned Staff</h3>
                            </div>
                            <div class="p-6">
                                <div v-if="ticket?.assignedTo" class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">
                                        {{ ticket.assignedTo.name?.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ ticket.assignedTo.name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ ticket.assignedTo.email }}</p>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4">
                                    <UserCircleIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" />
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">No staff assigned</p>
                                    <button @click="showAssignModal = true"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-2xl font-medium hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                                        <UserPlusIcon class="h-4 w-4" />
                                        Assign Staff
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Info -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Ticket Details</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Ticket ID</span>
                                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">{{ ticket?.ticket_number }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Category</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ currentCategory.icon }} {{ currentCategory.label }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Created</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(ticket?.created_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Updated</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(ticket?.updated_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Messages</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ conversationCount }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Customer Info</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <div class="flex items-center gap-3">
                                    <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ ticket?.user?.email }}</span>
                                </div>
                                <div v-if="ticket?.user?.phone" class="flex items-center gap-3">
                                    <PhoneIcon class="h-5 w-5 text-gray-400" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ ticket?.user?.phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showAssignModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Assign Staff Member</h3>
                    <form @submit.prevent="assignTicket">
                        <select v-model="assignForm.assigned_to" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select a staff member...</option>
                            <option v-for="member in staffMembers" :key="member.id" :value="member.id">
                                {{ member.name }} ({{ member.email }})
                            </option>
                        </select>
                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="showAssignModal = false" 
                                class="flex-1 px-4 py-2.5 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 rounded-xl font-medium transition-colors">
                                Cancel
                            </button>
                            <button type="submit" :disabled="assignForm.processing" 
                                class="flex-1 px-4 py-2.5 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-50 transition-colors">
                                Assign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
