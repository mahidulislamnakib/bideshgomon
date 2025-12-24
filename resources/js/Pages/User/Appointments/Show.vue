<template>
    <AuthenticatedLayout>
        <Head :title="`Appointment - ${formatDate(appointment?.appointment_date)}`" />

        <div v-if="appointment" class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30">
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
                    <Link :href="route('appointments.index')" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors mb-4">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Appointments
                    </Link>
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span :class="statusBadgeClass(appointment.status)" class="px-3 py-1 text-xs font-bold rounded-full">
                                    {{ statusLabel(appointment.status) }}
                                </span>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-white/20 text-white">
                                    {{ typeLabel(appointment.appointment_type) }}
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ purposeLabel(appointment.purpose) }}</h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-white/80">
                                <span class="flex items-center gap-1">
                                    <CalendarDaysIcon class="w-4 h-4" />
                                    {{ formatDate(appointment.appointment_date) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <ClockIcon class="w-4 h-4" />
                                    {{ formatTime(appointment.appointment_time) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                v-if="appointment.status === 'pending' || appointment.status === 'confirmed'"
                                @click="showRescheduleModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-xl transition-all"
                            >
                                <ArrowPathIcon class="w-4 h-4" />
                                Reschedule
                            </button>
                            <button
                                v-if="canCancel"
                                @click="showCancelModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/80 hover:bg-red-600 text-white font-semibold rounded-xl transition-all"
                            >
                                <XMarkIcon class="w-4 h-4" />
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Appointment Details Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-100">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <InformationCircleIcon class="w-5 h-5 text-growth-600" />
                                    Appointment Details
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex items-center gap-3 p-4 bg-growth-50 rounded-xl">
                                        <div class="w-12 h-12 bg-growth-100 rounded-xl flex items-center justify-center">
                                            <CalendarDaysIcon class="w-6 h-6 text-growth-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Date</p>
                                            <p class="font-bold text-gray-900">{{ formatDate(appointment.appointment_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl">
                                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <ClockIcon class="w-6 h-6 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Time</p>
                                            <p class="font-bold text-gray-900">{{ formatTime(appointment.appointment_time) }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border-t border-gray-100 pt-4 space-y-3">
                                    <div class="flex justify-between py-2">
                                        <span class="text-sm text-gray-500">Purpose</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ purposeLabel(appointment.purpose) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-sm text-gray-500">Type</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ typeLabel(appointment.appointment_type) }}</span>
                                    </div>
                                    <div v-if="appointment.notes" class="py-2">
                                        <span class="text-sm text-gray-500 block mb-1">Notes</span>
                                        <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ appointment.notes }}</p>
                                    </div>
                                    <div v-if="appointment.assigned_to" class="flex justify-between py-2">
                                        <span class="text-sm text-gray-500">Assigned to</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ appointment.assigned_to.name }}</span>
                                    </div>
                                    <div v-if="appointment.meeting_link && appointment.status === 'confirmed'" class="pt-4">
                                        <a :href="appointment.meeting_link" target="_blank" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                                            <VideoCameraIcon class="w-5 h-5" />
                                            Join Meeting
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div v-if="appointment.admin_notes" class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-6">
                            <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                                <ChatBubbleLeftRightIcon class="w-5 h-5" />
                                Admin Notes
                            </h4>
                            <p class="text-blue-800">{{ appointment.admin_notes }}</p>
                        </div>

                        <!-- Cancellation Reason -->
                        <div v-if="appointment.cancellation_reason" class="bg-red-50 rounded-2xl border-2 border-red-200 p-6">
                            <h4 class="font-bold text-red-900 mb-2 flex items-center gap-2">
                                <XCircleIcon class="w-5 h-5" />
                                Cancellation Reason
                            </h4>
                            <p class="text-red-800">{{ appointment.cancellation_reason }}</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Timeline Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                                <h3 class="font-bold text-white flex items-center gap-2">
                                    <ClockIcon class="w-5 h-5" />
                                    Timeline
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-growth-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <CalendarDaysIcon class="w-5 h-5 text-growth-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Requested</p>
                                            <p class="text-xs text-gray-500">{{ formatDateTime(appointment.created_at) }}</p>
                                        </div>
                                    </div>

                                    <div v-if="appointment.confirmed_at" class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <CheckCircleIcon class="w-5 h-5 text-green-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Confirmed</p>
                                            <p class="text-xs text-gray-500">{{ formatDateTime(appointment.confirmed_at) }}</p>
                                        </div>
                                    </div>

                                    <div v-if="appointment.completed_at" class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <CheckBadgeIcon class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Completed</p>
                                            <p class="text-xs text-gray-500">{{ formatDateTime(appointment.completed_at) }}</p>
                                        </div>
                                    </div>

                                    <div v-if="appointment.cancelled_at" class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <XCircleIcon class="w-5 h-5 text-red-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Cancelled</p>
                                            <p class="text-xs text-gray-500">{{ formatDateTime(appointment.cancelled_at) }}</p>
                                        </div>
                                    </div>
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
                                    <h3 class="font-bold">Need Help?</h3>
                                    <p class="text-sm text-white/80">Contact our support</p>
                                </div>
                            </div>
                            <a href="tel:+8801910638075" class="block w-full py-3 bg-white text-growth-700 font-bold rounded-xl text-center hover:bg-growth-50 transition-colors">
                                +880 1910-638075
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-else class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30 flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-growth-600 mx-auto"></div>
                <p class="mt-4 text-gray-500">Loading appointment...</p>
            </div>
        </div>

        <!-- Cancel Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <XCircleIcon class="w-6 h-6 text-red-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Cancel Appointment</h3>
                        <p class="text-sm text-gray-500">This action cannot be undone</p>
                    </div>
                </div>
                <form @submit.prevent="cancelAppointment">
                    <div class="mb-4">
                        <label for="cancellation_reason" class="block text-sm font-semibold text-gray-900 mb-2">
                            Reason for Cancellation <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="cancellation_reason"
                            v-model="cancelForm.reason"
                            rows="4"
                            class="w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500"
                            :class="{ 'border-red-500': cancelForm.errors.reason }"
                            required
                            placeholder="Please let us know why you're cancelling..."
                        ></textarea>
                        <p v-if="cancelForm.errors.reason" class="mt-1 text-sm text-red-600">{{ cancelForm.errors.reason }}</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showCancelModal = false"
                            class="px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                        >
                            Keep Appointment
                        </button>
                        <button
                            type="submit"
                            :disabled="cancelForm.processing"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-colors"
                            :class="{ 'opacity-50 cursor-not-allowed': cancelForm.processing }"
                        >
                            <XMarkIcon v-if="!cancelForm.processing" class="w-5 h-5" />
                            {{ cancelForm.processing ? 'Cancelling...' : 'Cancel Appointment' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Reschedule Modal -->
        <Modal :show="showRescheduleModal" @close="showRescheduleModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-growth-100 rounded-xl flex items-center justify-center">
                        <ArrowPathIcon class="w-6 h-6 text-growth-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Reschedule Appointment</h3>
                        <p class="text-sm text-gray-500">Choose a new date and time</p>
                    </div>
                </div>
                <div class="text-center py-8 text-gray-500">
                    <CalendarDaysIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p class="font-medium">Rescheduling coming soon</p>
                    <p class="text-sm">Please contact support to reschedule your appointment.</p>
                </div>
                <div class="flex justify-end">
                    <button
                        type="button"
                        @click="showRescheduleModal = false"
                        class="px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { 
    ArrowLeftIcon, 
    CalendarDaysIcon, 
    ClockIcon, 
    XMarkIcon, 
    ArrowPathIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    InformationCircleIcon,
    VideoCameraIcon,
    ChatBubbleLeftRightIcon,
    PhoneIcon,
    CheckBadgeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    appointment: Object,
});

const showCancelModal = ref(false);
const showRescheduleModal = ref(false);

const cancelForm = useForm({
    reason: '',
});

const canCancel = computed(() => {
    return ['pending', 'confirmed'].includes(props.appointment.status) && 
           new Date(props.appointment.appointment_date) >= new Date();
});

const cancelAppointment = () => {
    cancelForm.post(route('appointments.cancel', props.appointment.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
            cancelForm.reset();
        },
    });
};

const statusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-500 text-white',
        confirmed: 'bg-green-500 text-white',
        completed: 'bg-blue-500 text-white',
        cancelled: 'bg-red-500 text-white',
    };
    return classes[status] || 'bg-gray-500 text-white';
};

const statusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };
    return labels[status] || status;
};

const typeLabel = (type) => {
    const labels = {
        office_visit: 'Office Visit',
        online_meeting: 'Online Meeting',
    };
    return labels[type] || type;
};

const purposeLabel = (purpose) => {
    const labels = {
        consultation: 'Consultation',
        document_submission: 'Document Submission',
        general_inquiry: 'General Inquiry',
        visa_interview_prep: 'Visa Interview Preparation',
        application_review: 'Application Review',
    };
    return labels[purpose] || purpose;
};

import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
const { formatDate, formatTime, formatDateTime } = useBangladeshFormat();
</script>
