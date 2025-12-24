<template>
    <AuthenticatedLayout>
        <Head :title="`Ticket #${ticket.ticket_number}`" />

        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30">
            <!-- Hero Header -->
            <div class="relative bg-gradient-to-r from-growth-600 to-growth-700 overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute right-0 top-0 h-full w-1/2" viewBox="0 0 400 400" fill="none">
                        <circle cx="200" cy="200" r="150" stroke="currentColor" stroke-width="0.5" class="text-white" />
                        <circle cx="200" cy="200" r="100" stroke="currentColor" stroke-width="0.5" class="text-white" />
                        <circle cx="200" cy="200" r="50" stroke="currentColor" stroke-width="0.5" class="text-white" />
                    </svg>
                </div>
                <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                    <Link 
                        :href="route('support.index')" 
                        class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors mb-4"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Tickets
                    </Link>
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="font-mono text-sm text-white/80 bg-white/20 px-3 py-1 rounded-full">{{ ticket.ticket_number }}</span>
                                <span :class="statusBadgeClass(ticket.status)" class="px-3 py-1 text-xs font-bold rounded-full">
                                    {{ statusLabel(ticket.status) }}
                                </span>
                                <span :class="priorityBadgeClass(ticket.priority)" class="px-3 py-1 text-xs font-bold rounded-full">
                                    {{ priorityLabel(ticket.priority) }}
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ ticket.subject }}</h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-white/80">
                                <span class="flex items-center gap-1">
                                    <ClockIcon class="w-4 h-4" />
                                    {{ formatDate(ticket.created_at) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <TagIcon class="w-4 h-4" />
                                    {{ categoryLabel(ticket.category) }}
                                </span>
                                <span v-if="ticket.assigned_to" class="flex items-center gap-1">
                                    <UserCircleIcon class="w-4 h-4" />
                                    {{ ticket.assigned_to.name }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                v-if="ticket.status === 'open'"
                                :href="route('support.edit', ticket.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-xl transition-all"
                            >
                                <PencilSquareIcon class="w-4 h-4" />
                                Edit
                            </Link>
                            <button
                                v-if="ticket.status === 'open' && !hasReplies"
                                @click="deleteTicket"
                                :disabled="deletingTicket"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/80 hover:bg-red-600 text-white font-semibold rounded-xl transition-all"
                                :class="{ 'opacity-50 cursor-not-allowed': deletingTicket }"
                            >
                                <TrashIcon class="w-4 h-4" />
                                Delete
                            </button>
                            <button
                                v-if="ticket.status !== 'closed' && ticket.status !== 'resolved'"
                                @click="closeTicket"
                                :disabled="closingTicket"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 font-semibold rounded-xl transition-all"
                                :class="{ 'opacity-50 cursor-not-allowed': closingTicket }"
                            >
                                <XCircleIcon class="w-4 h-4" />
                                Close Ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Main Content - Conversation Thread -->
                    <div class="lg:col-span-2 space-y-4">
                        
                        <!-- Original Message -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-3 border-b border-gray-100">
                                <span class="text-sm font-semibold text-gray-600">Original Request</span>
                            </div>
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-growth-500 to-growth-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-growth-500/30">
                                            {{ (ticket.user.name || '').charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-3">
                                            <span class="font-bold text-gray-900">{{ ticket.user.name }}</span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ formatDate(ticket.created_at) }} at {{ formatTime(ticket.created_at) }}</span>
                                        </div>
                                        <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap bg-gray-50 rounded-xl p-4">{{ ticket.message }}</div>
                                        
                                        <!-- Attachments -->
                                        <div v-if="ticket.attachments && ticket.attachments.length > 0" class="mt-4">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachments</p>
                                            <div class="flex flex-wrap gap-2">
                                                <a
                                                    v-for="(attachment, index) in ticket.attachments"
                                                    :key="index"
                                                    :href="`/storage/${attachment.path}`"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-growth-50 text-growth-700 rounded-xl hover:bg-growth-100 transition-colors border border-growth-200"
                                                >
                                                    <PaperClipIcon class="w-4 h-4" />
                                                    {{ attachment.name }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Replies Thread -->
                        <div v-if="ticket.replies && ticket.replies.length > 0" class="space-y-4">
                            <div class="flex items-center gap-3 my-6">
                                <div class="flex-1 h-px bg-gray-200"></div>
                                <span class="text-sm font-semibold text-gray-500 flex items-center gap-2">
                                    <ChatBubbleLeftRightIcon class="w-4 h-4" />
                                    {{ ticket.replies.length }} {{ ticket.replies.length === 1 ? 'Reply' : 'Replies' }}
                                </span>
                                <div class="flex-1 h-px bg-gray-200"></div>
                            </div>
                            
                            <div v-for="reply in ticket.replies" :key="reply.id" 
                                :class="reply.is_staff_reply ? 'ml-0' : 'ml-0'"
                                class="bg-white rounded-2xl shadow-lg border overflow-hidden"
                            >
                                <div 
                                    :class="reply.is_staff_reply ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-100' : 'bg-gradient-to-r from-gray-50 to-gray-100 border-gray-100'"
                                    class="px-6 py-3 border-b flex items-center justify-between"
                                >
                                    <span v-if="reply.is_staff_reply" class="inline-flex items-center gap-2 text-sm font-semibold text-green-700">
                                        <ShieldCheckIcon class="w-4 h-4" />
                                        Staff Response
                                    </span>
                                    <span v-else class="text-sm font-semibold text-gray-600">Your Reply</span>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            <div 
                                                :class="reply.is_staff_reply ? 'bg-gradient-to-br from-green-500 to-emerald-600 shadow-green-500/30' : 'bg-gradient-to-br from-growth-500 to-growth-600 shadow-growth-500/30'"
                                                class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg"
                                            >
                                                {{ (reply.user.name || '').charAt(0).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                                <span class="font-bold text-gray-900">{{ reply.user.name }}</span>
                                                <span v-if="reply.is_staff_reply" class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-800 rounded-full">
                                                    Support Team
                                                </span>
                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ formatDate(reply.created_at) }} at {{ formatTime(reply.created_at) }}</span>
                                            </div>
                                            <div 
                                                :class="reply.is_staff_reply ? 'bg-green-50' : 'bg-gray-50'"
                                                class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap rounded-xl p-4"
                                            >{{ reply.message }}</div>
                                            
                                            <!-- Reply Attachments -->
                                            <div v-if="reply.attachments && reply.attachments.length > 0" class="mt-4">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachments</p>
                                                <div class="flex flex-wrap gap-2">
                                                    <a
                                                        v-for="(attachment, index) in reply.attachments"
                                                        :key="index"
                                                        :href="`/storage/${attachment.path}`"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-growth-50 text-growth-700 rounded-xl hover:bg-growth-100 transition-colors border border-growth-200"
                                                    >
                                                        <PaperClipIcon class="w-4 h-4" />
                                                        {{ attachment.name }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State for No Replies -->
                        <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <ChatBubbleLeftRightIcon class="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-1">No replies yet</h3>
                            <p class="text-sm text-gray-500">Our team will respond to your ticket shortly.</p>
                        </div>

                        <!-- Reply Form -->
                        <div v-if="ticket.status !== 'closed'" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-growth-600 to-growth-700 px-6 py-4">
                                <h3 class="font-bold text-white flex items-center gap-2">
                                    <PaperAirplaneIcon class="w-5 h-5" />
                                    Send a Reply
                                </h3>
                            </div>
                            <div class="p-6">
                                <form @submit.prevent="submitReply">
                                    <div class="mb-4">
                                        <textarea
                                            v-model="replyForm.message"
                                            rows="4"
                                            class="w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-growth-500 focus:ring-growth-500 transition-all resize-none"
                                            :class="{ 'border-red-500': replyForm.errors.message }"
                                            placeholder="Type your message here..."
                                            required
                                        ></textarea>
                                        <p v-if="replyForm.errors.message" class="mt-2 text-sm text-red-600">{{ replyForm.errors.message }}</p>
                                    </div>

                                    <div class="mb-4 p-4 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                        <label class="flex flex-col items-center cursor-pointer">
                                            <PaperClipIcon class="w-8 h-8 text-gray-400 mb-2" />
                                            <span class="text-sm font-semibold text-gray-600">Attach files</span>
                                            <span class="text-xs text-gray-500 mt-1">Max 5 files, 10MB each</span>
                                            <input
                                                type="file"
                                                @change="handleReplyFileUpload"
                                                multiple
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                class="hidden"
                                            />
                                        </label>
                                        <div v-if="replyForm.attachments.length > 0" class="mt-4 flex flex-wrap gap-2">
                                            <span 
                                                v-for="(file, index) in replyForm.attachments" 
                                                :key="index"
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-growth-100 text-growth-700 text-sm rounded-full"
                                            >
                                                <DocumentIcon class="w-4 h-4" />
                                                {{ file.name }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button
                                            type="submit"
                                            :disabled="replyForm.processing"
                                            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-bold rounded-xl shadow-lg shadow-growth-500/30 hover:shadow-xl hover:shadow-growth-500/40 transition-all"
                                            :class="{ 'opacity-50 cursor-not-allowed': replyForm.processing }"
                                        >
                                            <span v-if="replyForm.processing">Sending...</span>
                                            <span v-else class="flex items-center gap-2">
                                                <PaperAirplaneIcon class="w-5 h-5" />
                                                Send Reply
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Closed Ticket Banner -->
                        <div v-else class="bg-gray-100 rounded-2xl p-6 text-center">
                            <XCircleIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                            <h3 class="font-bold text-gray-900 mb-1">This ticket is closed</h3>
                            <p class="text-sm text-gray-500">You cannot reply to a closed ticket. Create a new ticket if you need further assistance.</p>
                        </div>

                        <!-- Rating Form -->
                        <div v-if="ticket.status === 'resolved' && !ticket.satisfaction_rating" class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-6 shadow-lg">
                            <div class="text-center mb-6">
                                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <StarIcon class="w-8 h-8 text-yellow-500" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">Rate Your Experience</h3>
                                <p class="text-sm text-gray-600">Your feedback helps us improve our support</p>
                            </div>
                            <form @submit.prevent="submitRating">
                                <div class="mb-6">
                                    <div class="flex justify-center gap-2">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            @click="ratingForm.rating = star"
                                            class="text-4xl transition-transform hover:scale-110"
                                            :class="star <= ratingForm.rating ? 'text-yellow-400 drop-shadow-lg' : 'text-gray-300'"
                                        >
                                            ★
                                        </button>
                                    </div>
                                    <p class="text-center text-sm text-gray-500 mt-2">
                                        {{ ratingForm.rating === 0 ? 'Click to rate' : ratingLabels[ratingForm.rating - 1] }}
                                    </p>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Additional Feedback (Optional)</label>
                                    <textarea
                                        v-model="ratingForm.feedback"
                                        rows="3"
                                        class="w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                        placeholder="Tell us more about your experience..."
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="!ratingForm.rating || ratingForm.processing"
                                    class="w-full px-6 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all"
                                    :class="{ 'opacity-50 cursor-not-allowed': !ratingForm.rating || ratingForm.processing }"
                                >
                                    Submit Rating
                                </button>
                            </form>
                        </div>

                        <!-- Already Rated Display -->
                        <div v-else-if="ticket.satisfaction_rating" class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-200 p-6 text-center">
                            <div class="flex justify-center gap-1 mb-3">
                                <span v-for="star in 5" :key="star" class="text-2xl" :class="star <= ticket.satisfaction_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                            </div>
                            <p class="text-sm font-semibold text-green-700">Thank you for your feedback!</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Ticket Details Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                                <h3 class="font-bold text-white flex items-center gap-2">
                                    <InformationCircleIcon class="w-5 h-5" />
                                    Ticket Details
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Ticket ID</span>
                                    <span class="font-mono text-sm font-semibold text-gray-900">{{ ticket.ticket_number }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span :class="statusClass(ticket.status)" class="px-3 py-1 text-xs font-bold rounded-full">
                                        {{ statusLabel(ticket.status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Priority</span>
                                    <span :class="priorityClass(ticket.priority)" class="px-3 py-1 text-xs font-bold rounded-full">
                                        {{ priorityLabel(ticket.priority) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Category</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ categoryLabel(ticket.category) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Created</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ formatDate(ticket.created_at) }}</span>
                                </div>
                                <div v-if="ticket.updated_at !== ticket.created_at" class="flex justify-between items-center py-3">
                                    <span class="text-sm text-gray-500">Last Updated</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ formatDate(ticket.updated_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Need Help Card -->
                        <div class="bg-gradient-to-br from-growth-600 to-growth-700 rounded-2xl p-6 text-white shadow-lg">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <PhoneIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold">Need Urgent Help?</h3>
                                    <p class="text-sm text-white/80">Call our support line</p>
                                </div>
                            </div>
                            <a href="tel:+8801910638075" class="block w-full py-3 bg-white text-growth-700 font-bold rounded-xl text-center hover:bg-growth-50 transition-colors">
                                +880 1910-638075
                            </a>
                        </div>

                        <!-- Quick Tips -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <LightBulbIcon class="w-5 h-5 text-yellow-500" />
                                Quick Tips
                            </h3>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>Include all relevant details in your replies</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>Attach screenshots if applicable</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>Check your email for notifications</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon,
    ClockIcon,
    TagIcon,
    UserCircleIcon,
    PencilSquareIcon,
    TrashIcon,
    XCircleIcon,
    ChatBubbleLeftRightIcon,
    PaperClipIcon,
    PaperAirplaneIcon,
    DocumentIcon,
    InformationCircleIcon,
    PhoneIcon,
    LightBulbIcon,
    CheckCircleIcon,
    ShieldCheckIcon,
    StarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    ticket: Object,
});

const closingTicket = ref(false);
const deletingTicket = ref(false);
const hasReplies = ref(props.ticket.replies && props.ticket.replies.length > 0);

const ratingLabels = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

const replyForm = useForm({
    message: '',
    attachments: [],
});

const ratingForm = useForm({
    rating: 0,
    feedback: '',
});

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
    replyForm.post(route('support.reply', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
        },
    });
};

const closeTicket = () => {
    if (!confirm('Are you sure you want to close this ticket?')) {
        return;
    }

    closingTicket.value = true;
    router.post(route('support.close', props.ticket.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            closingTicket.value = false;
        },
    });
};

const deleteTicket = () => {
    if (!confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
        return;
    }

    deletingTicket.value = true;
    router.delete(route('support.destroy', props.ticket.id), {
        onFinish: () => {
            deletingTicket.value = false;
        },
    });
};

const submitRating = () => {
    ratingForm.post(route('support.rate', props.ticket.id), {
        preserveScroll: true,
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

const statusBadgeClass = (status) => {
    const classes = {
        open: 'bg-green-500 text-white',
        in_progress: 'bg-blue-500 text-white',
        awaiting_reply: 'bg-yellow-500 text-white',
        resolved: 'bg-purple-500 text-white',
        closed: 'bg-gray-500 text-white',
    };
    return classes[status] || 'bg-gray-500 text-white';
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

const priorityBadgeClass = (priority) => {
    const classes = {
        urgent: 'bg-red-500 text-white',
        high: 'bg-orange-500 text-white',
        normal: 'bg-white/30 text-white',
        low: 'bg-white/20 text-white/80',
    };
    return classes[priority] || 'bg-white/20 text-white/80';
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

import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
const { formatDate, formatTime } = useBangladeshFormat();
</script>
