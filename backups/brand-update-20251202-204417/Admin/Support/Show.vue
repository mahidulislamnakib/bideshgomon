<template>
    <AdminLayout>
        <Head :title="`Ticket #${ticket.ticket_number}`" />

        <div class="py-rhythm-lg">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <div class="mb-rhythm-md">
                    <Link :href="route('admin.support-tickets.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Tickets
                    </Link>
                </div>

                <!-- Ticket Header -->
                <div class="bg-white shadow rounded-lg mb-rhythm-md">
                    <div class="px-6 py-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <span class="font-mono text-sm text-gray-600 font-semibold">{{ ticket.ticket_number }}</span>
                                    <span :class="statusClass(ticket.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ statusLabel(ticket.status) }}
                                    </span>
                                    <span :class="priorityClass(ticket.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ priorityLabel(ticket.priority) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full font-medium">
                                        {{ categoryLabel(ticket.category) }}
                                    </span>
                                </div>
                                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ ticket.subject }}</h1>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span>From: <span class="font-medium text-gray-900">{{ ticket.user.name }}</span></span>
                                    <span>•</span>
                                    <span>Created: {{ formatDateTime(ticket.created_at) }}</span>
                                    <span v-if="ticket.assigned_to">•</span>
                                    <span v-if="ticket.assigned_to">Assigned to: <span class="font-medium text-gray-900">{{ ticket.assigned_to.name }}</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            <!-- Assign Ticket -->
                            <select
                                v-model="assignForm.assigned_to"
                                @change="assignTicket"
                                class="rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm"
                                :disabled="assignForm.processing"
                            >
                                <option value="">Assign to...</option>
                                <option v-for="staff in staffMembers" :key="staff.id" :value="staff.id">
                                    {{ staff.name }}
                                </option>
                            </select>

                            <!-- Change Priority -->
                            <select
                                v-model="priorityForm.priority"
                                @change="updatePriority"
                                class="rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm"
                                :disabled="priorityForm.processing"
                            >
                                <option value="low">Low Priority</option>
                                <option value="normal">Normal Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">Urgent Priority</option>
                            </select>

                            <!-- Change Status -->
                            <select
                                v-model="statusForm.status"
                                @change="updateStatus"
                                class="rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500 text-sm"
                                :disabled="statusForm.processing"
                            >
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="awaiting_reply">Awaiting Reply</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>

                            <!-- Quick Actions -->
                            <button
                                v-if="ticket.status !== 'closed'"
                                @click="closeTicket"
                                :disabled="closingTicket"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500"
                            >
                                Close Ticket
                            </button>

                            <button
                                v-if="ticket.status === 'closed'"
                                @click="reopenTicket"
                                :disabled="reopeningTicket"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500"
                            >
                                Reopen Ticket
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Original Message -->
                <div class="bg-white shadow rounded-lg mb-4">
                    <div class="px-6 py-5">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ ticket.user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-semibold text-gray-900">{{ ticket.user.name }}</span>
                                    <span class="text-sm text-gray-500">{{ formatDateTime(ticket.created_at) }}</span>
                                </div>
                                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ ticket.message }}</div>
                                <div v-if="ticket.attachments && ticket.attachments.length > 0" class="mt-4">
                                    <p class="text-xs font-medium text-gray-700 mb-2">Attachments:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            v-for="(attachment, index) in ticket.attachments"
                                            :key="index"
                                            :href="`/storage/${attachment.path}`"
                                            target="_blank"
                                            class="flex items-center gap-2 px-3 py-1 text-xs bg-gray-100 rounded-md hover:bg-gray-200"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            {{ attachment.name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                <div v-for="reply in ticket.replies" :key="reply.id" class="bg-white shadow rounded-lg mb-4">
                    <div class="px-6 py-5">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div 
                                    :class="reply.is_staff_reply ? 'bg-ocean-600' : 'bg-gray-600'"
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold"
                                >
                                    {{ reply.user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-semibold text-gray-900">{{ reply.user.name }}</span>
                                    <span v-if="reply.is_staff_reply" class="px-2 py-0.5 text-xs font-semibold bg-ocean-100 text-ocean-800 rounded-full">
                                        Staff
                                    </span>
                                    <span v-if="reply.is_internal_note" class="px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                        Internal Note
                                    </span>
                                    <span class="text-sm text-gray-500">{{ formatDateTime(reply.created_at) }}</span>
                                </div>
                                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ reply.message }}</div>
                                <div v-if="reply.attachments && reply.attachments.length > 0" class="mt-4">
                                    <p class="text-xs font-medium text-gray-700 mb-2">Attachments:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            v-for="(attachment, index) in reply.attachments"
                                            :key="index"
                                            :href="`/storage/${attachment.path}`"
                                            target="_blank"
                                            class="flex items-center gap-2 px-3 py-1 text-xs bg-gray-100 rounded-md hover:bg-gray-200"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            {{ attachment.name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reply Form -->
                <div v-if="ticket.status !== 'closed'" class="bg-white shadow rounded-lg">
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Reply</h3>
                        <form @submit.prevent="submitReply">
                            <div class="mb-4">
                                <textarea
                                    v-model="replyForm.message"
                                    rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-ocean-500 focus:ring-ocean-500"
                                    :class="{ 'border-red-500': replyForm.errors.message }"
                                    placeholder="Type your reply..."
                                    required
                                ></textarea>
                                <p v-if="replyForm.errors.message" class="mt-1 text-sm text-red-600">{{ replyForm.errors.message }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="replyForm.internal_note"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-ocean-600 shadow-sm focus:border-ocean-500 focus:ring-ocean-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Internal Note (not visible to user)</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <input
                                    type="file"
                                    @change="handleReplyFileUpload"
                                    multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-ocean-50 file:text-ocean-700 hover:file:bg-ocean-100"
                                />
                                <p class="mt-1 text-xs text-gray-500">Optional: Max 5 files, 10MB each</p>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="replyForm.processing"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-ocean-600 hover:bg-ocean-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500"
                                    :class="{ 'opacity-50 cursor-not-allowed': replyForm.processing }"
                                >
                                    <span v-if="replyForm.processing">Sending...</span>
                                    <span v-else>Send Reply</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    ticket: Object,
    staffMembers: Array,
});

const closingTicket = ref(false);
const reopeningTicket = ref(false);

const assignForm = useForm({
    assigned_to: props.ticket.assigned_to?.id || '',
});

const priorityForm = useForm({
    priority: props.ticket.priority,
});

const statusForm = useForm({
    status: props.ticket.status,
});

const replyForm = useForm({
    message: '',
    internal_note: false,
    attachments: [],
});

const assignTicket = () => {
    if (!assignForm.assigned_to) return;
    
    assignForm.post(route('admin.support-tickets.assign', props.ticket.id), {
        preserveScroll: true,
    });
};

const updatePriority = () => {
    priorityForm.post(route('admin.support-tickets.update-priority', props.ticket.id), {
        preserveScroll: true,
    });
};

const updateStatus = () => {
    statusForm.put(route('admin.support-tickets.update', props.ticket.id), {
        preserveScroll: true,
    });
};

const closeTicket = () => {
    if (!confirm('Are you sure you want to close this ticket?')) return;
    
    closingTicket.value = true;
    router.post(route('admin.support-tickets.close', props.ticket.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            closingTicket.value = false;
        },
    });
};

const reopenTicket = () => {
    reopeningTicket.value = true;
    router.post(route('admin.support-tickets.reopen', props.ticket.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            reopeningTicket.value = false;
        },
    });
};

const handleReplyFileUpload = (event) => {
    const files = Array.from(event.target.files);
    if (files.length > 5) {
        alert('Maximum 5 files allowed');
        event.target.value = '';
        return;
    }
    replyForm.attachments = files;
};

const submitReply = () => {
    replyForm.post(route('admin.support-tickets.reply', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
        },
    });
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
        visa: '🛂 Visa & Immigration',
        jobs: '💼 Jobs & Applications',
        account: '👤 Account & Profile',
        payment: '💳 Payment & Wallet',
        services: '🎯 Services & Booking',
        technical: '🔧 Technical Support',
    };
    return labels[category] || category;
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
