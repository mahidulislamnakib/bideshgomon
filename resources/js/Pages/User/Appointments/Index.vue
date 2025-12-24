<template>
    <AuthenticatedLayout>
        <Head title="My Appointments" />

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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                                My Appointments
                            </h1>
                            <p class="text-lg text-white/80">
                                Schedule and manage your appointments
                            </p>
                        </div>
                        <Link :href="route('appointments.create')" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-growth-700 font-bold rounded-xl shadow-lg hover:bg-growth-50 transition-all">
                            <PlusIcon class="w-5 h-5" />
                            Book Appointment
                        </Link>
                    </div>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-growth-100 flex items-center justify-center">
                                <CalendarDaysIcon class="w-6 h-6 text-growth-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ appointments.total || appointments.data.length }}</p>
                                <p class="text-sm text-gray-500">Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                                <ClockIcon class="w-6 h-6 text-yellow-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ pendingCount }}</p>
                                <p class="text-sm text-gray-500">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ confirmedCount }}</p>
                                <p class="text-sm text-gray-500">Confirmed</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <CheckBadgeIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ completedCount }}</p>
                                <p class="text-sm text-gray-500">Completed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 mb-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <FunnelIcon class="w-5 h-5 text-gray-400" />
                            <span class="text-sm font-semibold text-gray-700">Filters:</span>
                        </div>
                        <select v-model="filterStatus" @change="applyFilters" class="rounded-xl border-2 border-gray-200 px-4 py-2 text-sm focus:border-growth-500 focus:ring-growth-500">
                            <option value="">All Status</option>
                            <option value="pending">🟡 Pending</option>
                            <option value="confirmed">🟢 Confirmed</option>
                            <option value="completed">🔵 Completed</option>
                            <option value="cancelled">🔴 Cancelled</option>
                        </select>

                        <select v-model="filterType" @change="applyFilters" class="rounded-xl border-2 border-gray-200 px-4 py-2 text-sm focus:border-growth-500 focus:ring-growth-500">
                            <option value="">All Types</option>
                            <option value="office_visit">🏢 Office Visit</option>
                            <option value="online_meeting">💻 Online Meeting</option>
                        </select>
                    </div>
                </div>

                <!-- Appointments List -->
                <div v-if="appointments.data.length > 0" class="space-y-4">
                    <Link v-for="appointment in appointments.data" :key="appointment.id" :href="route('appointments.show', appointment.id)" class="block bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl hover:border-growth-200 transition-all group">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <span :class="statusBadgeClass(appointment.status)" class="px-3 py-1 text-xs font-bold rounded-full">
                                            {{ statusLabel(appointment.status) }}
                                        </span>
                                        <span :class="typeBadgeClass(appointment.appointment_type)" class="px-3 py-1 text-xs font-bold rounded-full">
                                            {{ typeLabel(appointment.appointment_type) }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-growth-600 transition-colors">
                                        {{ purposeLabel(appointment.purpose) }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                        <div class="flex items-center gap-1.5">
                                            <CalendarIcon class="w-4 h-4 text-growth-600" />
                                            <span class="font-medium">{{ formatDate(appointment.appointment_date) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <ClockIcon class="w-4 h-4 text-growth-600" />
                                            <span class="font-medium">{{ appointment.appointment_time }}</span>
                                        </div>
                                        <div v-if="appointment.assigned_to" class="flex items-center gap-1.5">
                                            <UserCircleIcon class="w-4 h-4 text-gray-400" />
                                            <span>{{ appointment.assigned_to.name }}</span>
                                        </div>
                                    </div>
                                    <p v-if="appointment.notes" class="mt-3 text-sm text-gray-500 line-clamp-2">{{ appointment.notes }}</p>
                                </div>
                                <div class="ml-4">
                                    <ChevronRightIcon class="w-6 h-6 text-gray-300 group-hover:text-growth-600 transition-colors" />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <CalendarDaysIcon class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">No appointments yet</h3>
                    <p class="text-gray-500 mb-6">Get started by booking your first appointment.</p>
                    <Link :href="route('appointments.create')" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <PlusIcon class="w-5 h-5" />
                        Book Appointment
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="appointments.data.length > 0 && appointments.links" class="mt-6">
                    <Pagination :links="appointments.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { 
    CalendarDaysIcon, 
    PlusIcon, 
    FunnelIcon, 
    CalendarIcon, 
    ClockIcon, 
    ChevronRightIcon,
    CheckCircleIcon,
    CheckBadgeIcon,
    UserCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    appointments: Object,
    filters: Object,
});

const filterStatus = ref(props.filters?.status || '');
const filterType = ref(props.filters?.type || '');

const pendingCount = computed(() => {
    return props.appointments.data.filter(a => a.status === 'pending').length;
});

const confirmedCount = computed(() => {
    return props.appointments.data.filter(a => a.status === 'confirmed').length;
});

const completedCount = computed(() => {
    return props.appointments.data.filter(a => a.status === 'completed').length;
});

const applyFilters = () => {
    router.get(route('appointments.index'), {
        status: filterStatus.value,
        type: filterType.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const statusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
        rescheduled: 'bg-purple-100 text-purple-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const statusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        completed: 'Completed',
        cancelled: 'Cancelled',
        rescheduled: 'Rescheduled',
    };
    return labels[status] || status;
};

const typeBadgeClass = (type) => {
    const classes = {
        office_visit: 'bg-indigo-100 text-indigo-800',
        online_meeting: 'bg-cyan-100 text-cyan-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
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
const { formatDate } = useBangladeshFormat();
</script>
