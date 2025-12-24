<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    ArrowLeftIcon, CalendarDaysIcon, ClockIcon, 
    VideoCameraIcon, BuildingOfficeIcon, UserCircleIcon,
    CheckCircleIcon, XCircleIcon, PencilSquareIcon,
    LinkIcon, DocumentTextIcon, UserPlusIcon, TrashIcon,
    PhoneIcon, EnvelopeIcon, MapPinIcon, SparklesIcon,
    BellIcon, ChatBubbleLeftRightIcon, ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    appointment: Object,
    staff: Array,
});

const isEditing = ref(false);
const showConfirmModal = ref(false);
const showCancelModal = ref(false);
const showAssignModal = ref(false);
const showDeleteModal = ref(false);

const editForm = useForm({
    appointment_date: props.appointment.appointment_date?.split('T')[0] || '',
    appointment_time: props.appointment.appointment_time || '',
    meeting_link: props.appointment.meeting_link || '',
    admin_notes: props.appointment.admin_notes || '',
});

const confirmForm = useForm({
    meeting_link: props.appointment.meeting_link || '',
    admin_notes: props.appointment.admin_notes || '',
});

const cancelForm = useForm({
    cancellation_reason: '',
});

const assignForm = useForm({
    assigned_to: props.appointment.assigned_to || '',
});

const submitEdit = () => {
    editForm.put(route('admin.appointments.update', props.appointment.id), {
        onSuccess: () => isEditing.value = false,
    });
};

const submitConfirm = () => {
    confirmForm.post(route('admin.appointments.confirm', props.appointment.id), {
        onSuccess: () => showConfirmModal.value = false,
    });
};

const submitCancel = () => {
    cancelForm.post(route('admin.appointments.cancel', props.appointment.id), {
        onSuccess: () => showCancelModal.value = false,
    });
};

const submitAssign = () => {
    assignForm.post(route('admin.appointments.assign', props.appointment.id), {
        onSuccess: () => showAssignModal.value = false,
    });
};

const markComplete = () => {
    router.post(route('admin.appointments.complete', props.appointment.id));
};

const deleteAppointment = () => {
    router.delete(route('admin.appointments.destroy', props.appointment.id));
};

const statusConfig = {
    'pending': { 
        bg: 'bg-gradient-to-r from-amber-400 to-orange-500', 
        text: 'text-white',
        icon: ClockIcon,
        label: 'Pending Review'
    },
    'confirmed': { 
        bg: 'bg-gradient-to-r from-blue-500 to-indigo-600', 
        text: 'text-white',
        icon: CheckCircleIcon,
        label: 'Confirmed'
    },
    'completed': { 
        bg: 'bg-gradient-to-r from-emerald-500 to-teal-600', 
        text: 'text-white',
        icon: CheckCircleSolidIcon,
        label: 'Completed'
    },
    'cancelled': { 
        bg: 'bg-gradient-to-r from-red-500 to-rose-600', 
        text: 'text-white',
        icon: XCircleIcon,
        label: 'Cancelled'
    },
};

const currentStatus = computed(() => statusConfig[props.appointment.status] || statusConfig.pending);

const isUpcoming = computed(() => {
    if (!props.appointment.appointment_date) return false;
    const aptDate = new Date(props.appointment.appointment_date);
    return aptDate >= new Date() && ['pending', 'confirmed'].includes(props.appointment.status);
});

const daysUntil = computed(() => {
    if (!props.appointment.appointment_date) return null;
    const aptDate = new Date(props.appointment.appointment_date);
    const today = new Date();
    const diffTime = aptDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-GB', { 
        weekday: 'long',
        day: '2-digit',
        month: 'long', 
        year: 'numeric' 
    });
};

const formatShortDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-GB', { 
        day: '2-digit',
        month: 'short'
    });
};

const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
};

const formatDateTime = (datetime) => {
    if (!datetime) return 'N/A';
    return new Date(datetime).toLocaleString('en-GB', { 
        day: '2-digit',
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPurpose = (purpose) => {
    if (!purpose) return '';
    return purpose.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const purposeIcons = {
    'consultation': ChatBubbleLeftRightIcon,
    'document_submission': DocumentTextIcon,
    'general_inquiry': SparklesIcon,
    'visa_interview_prep': VideoCameraIcon,
    'application_review': DocumentTextIcon,
};

const getPurposeIcon = (purpose) => {
    return purposeIcons[purpose] || DocumentTextIcon;
};
</script>

<template>
    <Head :title="`Appointment #${appointment.appointment_number}`" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header with Status -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Decorative Elements -->
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-96 h-96 bg-growth-500 rounded-full filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl translate-x-1/2 translate-y-1/2"></div>
                </div>
                
                <div class="relative z-10 px-4 py-8 sm:px-6 lg:px-8">
                    <div class="max-w-6xl mx-auto">
                        <!-- Breadcrumb -->
                        <Link :href="route('admin.appointments.index')" class="inline-flex items-center text-gray-400 hover:text-white mb-6 transition-colors group">
                            <ArrowLeftIcon class="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                            <span class="text-sm">Back to Appointments</span>
                        </Link>

                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            <!-- Left: Appointment Info -->
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span :class="[currentStatus.bg, currentStatus.text]" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold shadow-lg">
                                        <component :is="currentStatus.icon" class="h-4 w-4" />
                                        {{ currentStatus.label }}
                                    </span>
                                    <span v-if="isUpcoming && daysUntil !== null" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur text-white rounded-full text-sm">
                                        <BellIcon class="h-4 w-4" />
                                        <span v-if="daysUntil === 0">Today!</span>
                                        <span v-else-if="daysUntil === 1">Tomorrow</span>
                                        <span v-else-if="daysUntil > 0">In {{ daysUntil }} days</span>
                                        <span v-else class="text-red-300">{{ Math.abs(daysUntil) }} days ago</span>
                                    </span>
                                </div>
                                
                                <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2">
                                    {{ formatPurpose(appointment.purpose) }}
                                </h1>
                                <p class="text-gray-400 text-lg">{{ appointment.appointment_number }}</p>
                            </div>

                            <!-- Right: Date/Time Card -->
                            <div class="flex-shrink-0">
                                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 min-w-[200px] text-center">
                                    <div class="text-5xl font-bold text-white mb-1">
                                        {{ formatShortDate(appointment.appointment_date).split(' ')[0] }}
                                    </div>
                                    <div class="text-xl text-gray-300 mb-3">
                                        {{ formatShortDate(appointment.appointment_date).split(' ')[1] }}
                                    </div>
                                    <div class="flex items-center justify-center gap-2 text-growth-400">
                                        <ClockIcon class="h-5 w-5" />
                                        <span class="text-lg font-semibold">{{ formatTime(appointment.appointment_time) }}</span>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-white/20">
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl" :class="appointment.appointment_type === 'online_meeting' ? 'bg-blue-500/20 text-blue-300' : 'bg-amber-500/20 text-amber-300'">
                                            <component :is="appointment.appointment_type === 'online_meeting' ? VideoCameraIcon : BuildingOfficeIcon" class="h-4 w-4" />
                                            <span class="text-sm font-medium capitalize">{{ appointment.appointment_type?.replace('_', ' ') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-3 mt-8">
                            <button
                                v-if="appointment.status === 'pending'"
                                @click="showConfirmModal = true"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg shadow-blue-500/25"
                            >
                                <CheckCircleIcon class="h-5 w-5 mr-2" />
                                Confirm Appointment
                            </button>
                            <button
                                v-if="appointment.status === 'confirmed'"
                                @click="markComplete"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/25"
                            >
                                <CheckCircleSolidIcon class="h-5 w-5 mr-2" />
                                Mark as Complete
                            </button>
                            <button
                                v-if="['pending', 'confirmed'].includes(appointment.status)"
                                @click="isEditing = !isEditing"
                                class="inline-flex items-center px-5 py-3 bg-white/10 backdrop-blur text-white rounded-xl font-medium hover:bg-white/20 transition-all border border-white/20"
                            >
                                <PencilSquareIcon class="h-5 w-5 mr-2" />
                                Edit Details
                            </button>
                            <button
                                v-if="['pending', 'confirmed'].includes(appointment.status)"
                                @click="showAssignModal = true"
                                class="inline-flex items-center px-5 py-3 bg-white/10 backdrop-blur text-white rounded-xl font-medium hover:bg-white/20 transition-all border border-white/20"
                            >
                                <UserPlusIcon class="h-5 w-5 mr-2" />
                                Assign Staff
                            </button>
                            <button
                                v-if="['pending', 'confirmed'].includes(appointment.status)"
                                @click="showCancelModal = true"
                                class="inline-flex items-center px-5 py-3 bg-red-500/20 text-red-300 rounded-xl font-medium hover:bg-red-500/30 transition-all border border-red-500/30"
                            >
                                <XCircleIcon class="h-5 w-5 mr-2" />
                                Cancel
                            </button>
                            <button
                                @click="showDeleteModal = true"
                                class="inline-flex items-center px-5 py-3 bg-white/5 text-gray-400 rounded-xl font-medium hover:bg-red-500/20 hover:text-red-300 transition-all border border-white/10"
                            >
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Edit Form Card -->
                        <div v-if="isEditing" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6 ring-2 ring-growth-500/50">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 bg-growth-100 dark:bg-growth-900/30 rounded-2xl">
                                    <PencilSquareIcon class="h-5 w-5 text-growth-600" />
                                </div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Appointment</h2>
                            </div>
                            
                            <form @submit.prevent="submitEdit" class="space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                                        <input
                                            v-model="editForm.appointment_date"
                                            type="date"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                                        <input
                                            v-model="editForm.appointment_time"
                                            type="time"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent transition-all"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meeting Link</label>
                                    <input
                                        v-model="editForm.meeting_link"
                                        type="url"
                                        placeholder="https://meet.google.com/..."
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent transition-all"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes</label>
                                    <textarea
                                        v-model="editForm.admin_notes"
                                        rows="4"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent transition-all resize-none"
                                    ></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="isEditing = false" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-xl font-medium transition-colors">Cancel</button>
                                    <button type="submit" :disabled="editForm.processing" class="px-6 py-2.5 bg-growth-600 text-white rounded-xl font-semibold hover:bg-growth-700 disabled:opacity-50 transition-all shadow-lg shadow-growth-500/25">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Appointment Details Card -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-700 bg-gradient-to-r from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-750">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-2xl">
                                        <CalendarDaysIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    Appointment Details
                                </h2>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <!-- Date -->
                                    <div class="group p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-100 dark:border-blue-800/30 hover:shadow-lg transition-all">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 bg-blue-500 rounded-2xl shadow-lg shadow-blue-500/30">
                                                <CalendarDaysIcon class="h-5 w-5 text-white" />
                                            </div>
                                            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">Date</span>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatDate(appointment.appointment_date) }}</p>
                                    </div>
                                    
                                    <!-- Time -->
                                    <div class="group p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-100 dark:border-purple-800/30 hover:shadow-lg transition-all">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 bg-purple-500 rounded-2xl shadow-lg shadow-purple-500/30">
                                                <ClockIcon class="h-5 w-5 text-white" />
                                            </div>
                                            <span class="text-sm font-medium text-purple-600 dark:text-purple-400">Time</span>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatTime(appointment.appointment_time) }}</p>
                                    </div>
                                    
                                    <!-- Type -->
                                    <div class="group p-4 bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 rounded-xl border border-cyan-100 dark:border-cyan-800/30 hover:shadow-lg transition-all">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 rounded-2xl shadow-lg" :class="appointment.appointment_type === 'online_meeting' ? 'bg-cyan-500 shadow-cyan-500/30' : 'bg-amber-500 shadow-amber-500/30'">
                                                <component :is="appointment.appointment_type === 'online_meeting' ? VideoCameraIcon : BuildingOfficeIcon" class="h-5 w-5 text-white" />
                                            </div>
                                            <span class="text-sm font-medium" :class="appointment.appointment_type === 'online_meeting' ? 'text-cyan-600 dark:text-cyan-400' : 'text-amber-600 dark:text-amber-400'">Type</span>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ appointment.appointment_type?.replace('_', ' ') }}</p>
                                    </div>
                                    
                                    <!-- Purpose -->
                                    <div class="group p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-100 dark:border-green-800/30 hover:shadow-lg transition-all">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 bg-green-500 rounded-2xl shadow-lg shadow-green-500/30">
                                                <component :is="getPurposeIcon(appointment.purpose)" class="h-5 w-5 text-white" />
                                            </div>
                                            <span class="text-sm font-medium text-green-600 dark:text-green-400">Purpose</span>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatPurpose(appointment.purpose) }}</p>
                                    </div>
                                </div>

                                <!-- Meeting Link -->
                                <div v-if="appointment.meeting_link" class="mt-6 p-5 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl border border-blue-200 dark:border-blue-700/50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-blue-500 rounded-2xl">
                                                <LinkIcon class="h-5 w-5 text-white" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Meeting Link</p>
                                                <p class="text-gray-600 dark:text-gray-300 text-sm truncate max-w-md">{{ appointment.meeting_link }}</p>
                                            </div>
                                        </div>
                                        <a :href="appointment.meeting_link" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-2xl font-medium hover:bg-blue-600 transition-colors">
                                            Join Meeting
                                            <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                        </a>
                                    </div>
                                </div>

                                <!-- Notes Sections -->
                                <div class="mt-6 space-y-4">
                                    <div v-if="appointment.notes" class="p-5 bg-gray-50 dark:bg-neutral-700/50 rounded-xl border border-gray-200 dark:border-neutral-600">
                                        <div class="flex items-center gap-2 mb-3">
                                            <DocumentTextIcon class="h-5 w-5 text-gray-500" />
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">User Notes</span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ appointment.notes }}</p>
                                    </div>

                                    <div v-if="appointment.admin_notes" class="p-5 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-xl border border-yellow-200 dark:border-yellow-700/50">
                                        <div class="flex items-center gap-2 mb-3">
                                            <SparklesIcon class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                            <span class="font-semibold text-yellow-800 dark:text-yellow-300">Admin Notes</span>
                                        </div>
                                        <p class="text-yellow-700 dark:text-yellow-200 leading-relaxed">{{ appointment.admin_notes }}</p>
                                    </div>

                                    <div v-if="appointment.status === 'cancelled' && appointment.cancellation_reason" class="p-5 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-xl border border-red-200 dark:border-red-700/50">
                                        <div class="flex items-center gap-2 mb-3">
                                            <XCircleIcon class="h-5 w-5 text-red-600 dark:text-red-400" />
                                            <span class="font-semibold text-red-800 dark:text-red-300">Cancellation Reason</span>
                                        </div>
                                        <p class="text-red-700 dark:text-red-200 leading-relaxed">{{ appointment.cancellation_reason }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Card -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-700 bg-gradient-to-r from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-750">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-2xl">
                                        <ClockIcon class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    Activity Timeline
                                </h2>
                            </div>
                            
                            <div class="p-6">
                                <div class="relative">
                                    <!-- Timeline Line -->
                                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-gray-200 via-gray-200 to-transparent dark:from-neutral-700 dark:via-neutral-700"></div>
                                    
                                    <div class="space-y-6">
                                        <!-- Created -->
                                        <div class="relative flex items-start gap-4 pl-10">
                                            <div class="absolute left-0 w-9 h-9 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-neutral-700 dark:to-neutral-600 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-neutral-800 shadow-md">
                                                <CalendarDaysIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                            </div>
                                            <div class="flex-1 pb-4">
                                                <p class="font-semibold text-gray-900 dark:text-white">Appointment Created</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(appointment.created_at) }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Confirmed -->
                                        <div v-if="appointment.confirmed_at" class="relative flex items-start gap-4 pl-10">
                                            <div class="absolute left-0 w-9 h-9 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-neutral-800 shadow-md shadow-blue-500/30">
                                                <CheckCircleIcon class="h-4 w-4 text-white" />
                                            </div>
                                            <div class="flex-1 pb-4">
                                                <p class="font-semibold text-gray-900 dark:text-white">Appointment Confirmed</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(appointment.confirmed_at) }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Completed -->
                                        <div v-if="appointment.completed_at" class="relative flex items-start gap-4 pl-10">
                                            <div class="absolute left-0 w-9 h-9 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-neutral-800 shadow-md shadow-emerald-500/30">
                                                <CheckCircleSolidIcon class="h-4 w-4 text-white" />
                                            </div>
                                            <div class="flex-1 pb-4">
                                                <p class="font-semibold text-gray-900 dark:text-white">Appointment Completed</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(appointment.completed_at) }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Cancelled -->
                                        <div v-if="appointment.cancelled_at" class="relative flex items-start gap-4 pl-10">
                                            <div class="absolute left-0 w-9 h-9 bg-gradient-to-br from-red-400 to-rose-500 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-neutral-800 shadow-md shadow-red-500/30">
                                                <XCircleIcon class="h-4 w-4 text-white" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 dark:text-white">Appointment Cancelled</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(appointment.cancelled_at) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="space-y-6">
                        <!-- Client Card -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                            <div class="h-20 bg-gradient-to-r from-growth-500 to-teal-500"></div>
                            <div class="px-6 pb-6 -mt-10">
                                <div class="w-20 h-20 bg-white dark:bg-neutral-700 rounded-2xl shadow-xl flex items-center justify-center mb-4 ring-4 ring-white dark:ring-neutral-800">
                                    <UserCircleIcon class="h-12 w-12 text-growth-500" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ appointment.user?.name }}</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Client</p>
                                
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3 text-sm">
                                        <EnvelopeIcon class="h-4 w-4 text-gray-400" />
                                        <span class="text-gray-600 dark:text-gray-300">{{ appointment.user?.email }}</span>
                                    </div>
                                    <div v-if="appointment.user?.phone" class="flex items-center gap-3 text-sm">
                                        <PhoneIcon class="h-4 w-4 text-gray-400" />
                                        <span class="text-gray-600 dark:text-gray-300">{{ appointment.user?.phone }}</span>
                                    </div>
                                </div>
                                
                                <Link :href="route('admin.users.show', appointment.user?.id)" class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors">
                                    View Full Profile
                                    <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>

                        <!-- Assigned Staff Card -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Assigned Staff</h3>
                            
                            <div v-if="appointment.assigned_to_user" class="flex items-center gap-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-100 dark:border-purple-800/30">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/30">
                                    <UserCircleIcon class="h-7 w-7 text-white" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ appointment.assigned_to_user?.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ appointment.assigned_to_user?.email }}</p>
                                </div>
                            </div>
                            
                            <div v-else class="p-6 bg-gray-50 dark:bg-neutral-700/30 rounded-xl border-2 border-dashed border-gray-200 dark:border-neutral-600 text-center">
                                <UserPlusIcon class="h-10 w-10 text-gray-400 mx-auto mb-3" />
                                <p class="text-gray-500 dark:text-gray-400 mb-3">No staff assigned</p>
                                <button
                                    v-if="['pending', 'confirmed'].includes(appointment.status)"
                                    @click="showAssignModal = true"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500 text-white rounded-2xl font-medium hover:bg-purple-600 transition-colors"
                                >
                                    <UserPlusIcon class="h-4 w-4" />
                                    Assign Now
                                </button>
                            </div>
                            
                            <button
                                v-if="appointment.assigned_to_user && ['pending', 'confirmed'].includes(appointment.status)"
                                @click="showAssignModal = true"
                                class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-xl font-medium transition-colors"
                            >
                                <UserPlusIcon class="h-4 w-4" />
                                Reassign Staff
                            </button>
                        </div>

                        <!-- Quick Stats Card -->
                        <div class="bg-gradient-to-br from-neutral-800 to-neutral-900 dark:from-neutral-900 dark:to-black rounded-2xl shadow-xl p-6 text-white">
                            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Appointment ID</h3>
                            <p class="text-2xl font-bold font-mono mb-4">{{ appointment.appointment_number }}</p>
                            <div class="pt-4 border-t border-white/10 text-sm text-gray-400">
                                <p>Created on {{ formatDateTime(appointment.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-500/30">
                        <CheckCircleIcon class="h-8 w-8 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Confirm Appointment</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center mb-6">Add meeting details and confirm this appointment</p>
                    
                    <form @submit.prevent="submitConfirm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meeting Link (optional)</label>
                            <input
                                v-model="confirmForm.meeting_link"
                                type="url"
                                placeholder="https://meet.google.com/..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes (optional)</label>
                            <textarea
                                v-model="confirmForm.admin_notes"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 resize-none"
                            ></textarea>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showConfirmModal = false" class="flex-1 px-4 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 rounded-xl font-medium transition-colors">Cancel</button>
                            <button type="submit" :disabled="confirmForm.processing" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 disabled:opacity-50 transition-all shadow-lg shadow-blue-500/25">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-red-500/30">
                        <XCircleIcon class="h-8 w-8 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Cancel Appointment</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center mb-6">Please provide a reason for cancellation</p>
                    
                    <form @submit.prevent="submitCancel">
                        <div>
                            <textarea
                                v-model="cancelForm.cancellation_reason"
                                rows="4"
                                required
                                placeholder="Enter the reason for cancellation..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-red-500 resize-none"
                            ></textarea>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="showCancelModal = false" class="flex-1 px-4 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 rounded-xl font-medium transition-colors">Go Back</button>
                            <button type="submit" :disabled="cancelForm.processing" class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-semibold hover:from-red-600 hover:to-rose-700 disabled:opacity-50 transition-all shadow-lg shadow-red-500/25">Cancel Appointment</button>
                        </div>
                    </form>
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
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Assign Staff Member</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center mb-6">Select a staff member to handle this appointment</p>
                    
                    <form @submit.prevent="submitAssign">
                        <select
                            v-model="assignForm.assigned_to"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-purple-500"
                        >
                            <option value="">Select a staff member</option>
                            <option v-for="member in staff" :key="member.id" :value="member.id">
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

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-red-500/30">
                        <TrashIcon class="h-8 w-8 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Delete Appointment</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center mb-6">Are you sure you want to delete this appointment? This action cannot be undone.</p>
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false" class="flex-1 px-4 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 rounded-xl font-medium transition-colors">Cancel</button>
                        <button @click="deleteAppointment" class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-semibold hover:from-red-600 hover:to-rose-700 transition-all shadow-lg shadow-red-500/25">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
