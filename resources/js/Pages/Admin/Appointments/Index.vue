<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    MagnifyingGlassIcon, FunnelIcon, CalendarDaysIcon,
    UserCircleIcon, ClockIcon, VideoCameraIcon, BuildingOfficeIcon,
    CheckCircleIcon, XCircleIcon, EyeIcon, UserPlusIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    appointments: Object,
    stats: Object,
    staff: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const type = ref(props.filters.type || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const showFilters = ref(false);
const loading = ref(false);

// Action modals
const showConfirmModal = ref(false);
const showCancelModal = ref(false);
const showAssignModal = ref(false);
const selectedAppointment = ref(null);

const confirmForm = useForm({
    meeting_link: '',
    admin_notes: '',
});

const cancelForm = useForm({
    cancellation_reason: '',
});

const assignForm = useForm({
    assigned_to: '',
});

const performSearch = () => {
    router.get(route('admin.appointments.index'), {
        search: search.value,
        status: status.value,
        type: type.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    type.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return search.value || status.value || type.value || dateFrom.value || dateTo.value;
});

const openConfirmModal = (apt) => {
    selectedAppointment.value = apt;
    confirmForm.meeting_link = apt.meeting_link || '';
    confirmForm.admin_notes = apt.admin_notes || '';
    showConfirmModal.value = true;
};

const submitConfirm = () => {
    confirmForm.post(route('admin.appointments.confirm', selectedAppointment.value.id), {
        onSuccess: () => {
            showConfirmModal.value = false;
            confirmForm.reset();
        },
    });
};

const openCancelModal = (apt) => {
    selectedAppointment.value = apt;
    showCancelModal.value = true;
};

const submitCancel = () => {
    cancelForm.post(route('admin.appointments.cancel', selectedAppointment.value.id), {
        onSuccess: () => {
            showCancelModal.value = false;
            cancelForm.reset();
        },
    });
};

const openAssignModal = (apt) => {
    selectedAppointment.value = apt;
    assignForm.assigned_to = apt.assigned_to || '';
    showAssignModal.value = true;
};

const submitAssign = () => {
    assignForm.post(route('admin.appointments.assign', selectedAppointment.value.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            assignForm.reset();
        },
    });
};

const markComplete = (apt) => {
    if (confirm('Mark this appointment as completed?')) {
        router.post(route('admin.appointments.complete', apt.id));
    }
};

const getStatusBadge = (aptStatus) => {
    const badges = {
        'pending': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
        'confirmed': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
        'completed': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
        'cancelled': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
    };
    return badges[aptStatus] || 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300';
};

const getTypeIcon = (aptType) => {
    return aptType === 'online_meeting' ? VideoCameraIcon : BuildingOfficeIcon;
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-GB', { 
        day: '2-digit',
        month: 'short', 
        year: 'numeric' 
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

const formatPurpose = (purpose) => {
    if (!purpose) return '';
    return purpose.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="Appointments Management" />

    <AdminLayout>
        <PageSkeleton v-if="loading" />
        <div v-else class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Header -->
            <div class="relative overflow-hidden px-4 py-8 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, #10b981 0%, transparent 50%), radial-gradient(circle at 75% 75%, #0d9488 0%, transparent 50%);"></div>
                </div>
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white">Appointments</h1>
                            <p class="mt-2 text-gray-300">Manage all appointment bookings</p>
                        </div>
                        <Link
                            :href="route('admin.appointments.calendar')"
                            class="inline-flex items-center px-6 py-3 bg-growth-600 text-white rounded-2xl font-semibold hover:bg-growth-700 transition-all shadow-sm"
                        >
                            <CalendarDaysIcon class="h-5 w-5 mr-2" />
                            Calendar View
                        </Link>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-8">
                        <div class="bg-white/10 backdrop-blur border border-white/20 rounded-xl p-3">
                            <div class="text-gray-300 text-xs font-medium">Total</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ stats.total }}</div>
                        </div>
                        <div class="bg-yellow-500/20 backdrop-blur border border-yellow-400/30 rounded-xl p-3">
                            <div class="text-yellow-300 text-xs font-medium">Pending</div>
                            <div class="text-2xl font-bold text-yellow-100 mt-1">{{ stats.pending }}</div>
                        </div>
                        <div class="bg-blue-500/20 backdrop-blur border border-blue-400/30 rounded-xl p-3">
                            <div class="text-blue-300 text-xs font-medium">Confirmed</div>
                            <div class="text-2xl font-bold text-blue-100 mt-1">{{ stats.confirmed }}</div>
                        </div>
                        <div class="bg-green-500/20 backdrop-blur border border-green-400/30 rounded-xl p-3">
                            <div class="text-green-300 text-xs font-medium">Completed</div>
                            <div class="text-2xl font-bold text-green-100 mt-1">{{ stats.completed }}</div>
                        </div>
                        <div class="bg-red-500/20 backdrop-blur border border-red-400/30 rounded-xl p-3">
                            <div class="text-red-300 text-xs font-medium">Cancelled</div>
                            <div class="text-2xl font-bold text-red-100 mt-1">{{ stats.cancelled }}</div>
                        </div>
                        <div class="bg-purple-500/20 backdrop-blur border border-purple-400/30 rounded-xl p-3">
                            <div class="text-purple-300 text-xs font-medium">Upcoming</div>
                            <div class="text-2xl font-bold text-purple-100 mt-1">{{ stats.upcoming }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <!-- Search & Filters -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input
                                v-model="search"
                                @keyup.enter="performSearch"
                                type="text"
                                placeholder="Search by name, email, or appointment number..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-transparent"
                            />
                        </div>
                        <button
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <FunnelIcon class="h-5 w-5 mr-2" />
                            Filters
                            <span v-if="hasActiveFilters" class="ml-2 px-2 py-0.5 bg-growth-600 text-white text-xs rounded-full">Active</span>
                        </button>
                    </div>

                    <!-- Expandable Filters -->
                    <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="status"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                                <select
                                    v-model="type"
                                    @change="performSearch"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                >
                                    <option value="">All Types</option>
                                    <option value="office_visit">Office Visit</option>
                                    <option value="online_meeting">Online Meeting</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                                <input
                                    v-model="dateFrom"
                                    @change="performSearch"
                                    type="date"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                                <input
                                    v-model="dateTo"
                                    @change="performSearch"
                                    type="date"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                />
                            </div>
                        </div>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="mt-4 text-sm text-growth-600 hover:text-growth-700 font-medium"
                        >
                            Clear all filters
                        </button>
                    </div>
                </div>

                <!-- Appointments List -->
                <div class="space-y-4">
                    <div v-if="appointments.data.length === 0" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-12 text-center">
                        <CalendarDaysIcon class="h-16 w-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No appointments found</h3>
                        <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters</p>
                    </div>

                    <div
                        v-for="apt in appointments.data"
                        :key="apt.id"
                        class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-lg transition-shadow p-6"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="apt.appointment_type === 'online_meeting' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-purple-100 dark:bg-purple-900/30'">
                                    <component :is="getTypeIcon(apt.appointment_type)" class="h-6 w-6" :class="apt.appointment_type === 'online_meeting' ? 'text-blue-600 dark:text-blue-400' : 'text-purple-600 dark:text-purple-400'" />
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <div class="flex items-center gap-3 mb-1">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ apt.user?.name || 'Unknown User' }}</h3>
                                            <span :class="getStatusBadge(apt.status)" class="px-3 py-1 text-xs font-semibold rounded-full capitalize">
                                                {{ apt.status }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ apt.appointment_number }}</p>
                                    </div>
                                    <Link
                                        :href="route('admin.appointments.show', apt.id)"
                                        class="p-2 text-growth-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-2xl transition-colors"
                                        title="View Details"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Date & Time</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(apt.appointment_date) }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ formatTime(apt.appointment_time) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Type</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ apt.appointment_type?.replace('_', ' ') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Purpose</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatPurpose(apt.purpose) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Assigned To</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ apt.assigned_to_user?.name || 'Unassigned' }}</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-neutral-700">
                                    <button
                                        v-if="apt.status === 'pending'"
                                        @click="openConfirmModal(apt)"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-2xl hover:bg-blue-700 transition-colors"
                                    >
                                        <CheckCircleIcon class="h-4 w-4 mr-1" />
                                        Confirm
                                    </button>
                                    <button
                                        v-if="apt.status === 'confirmed'"
                                        @click="markComplete(apt)"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-2xl hover:bg-green-700 transition-colors"
                                    >
                                        <CheckCircleIcon class="h-4 w-4 mr-1" />
                                        Complete
                                    </button>
                                    <button
                                        v-if="['pending', 'confirmed'].includes(apt.status)"
                                        @click="openCancelModal(apt)"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm font-medium rounded-2xl hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
                                    >
                                        <XCircleIcon class="h-4 w-4 mr-1" />
                                        Cancel
                                    </button>
                                    <button
                                        v-if="['pending', 'confirmed'].includes(apt.status)"
                                        @click="openAssignModal(apt)"
                                        class="inline-flex items-center px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm font-medium rounded-2xl hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors"
                                    >
                                        <UserPlusIcon class="h-4 w-4 mr-1" />
                                        Assign
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="appointments.data.length > 0" class="mt-8 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ appointments.from }} to {{ appointments.to }} of {{ appointments.total }} appointments
                    </div>
                    <div class="flex items-center space-x-2">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="link in appointments.links"
                            :key="link.label"
                            :href="link.url || undefined"
                            :class="[
                                'px-4 py-2 text-sm rounded-2xl font-medium',
                                link.active
                                    ? 'bg-growth-600 text-white'
                                    : link.url
                                    ? 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-700 border border-gray-300 dark:border-neutral-600'
                                    : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showConfirmModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Confirm Appointment</h3>
                    <form @submit.prevent="submitConfirm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meeting Link (optional)</label>
                                <input
                                    v-model="confirmForm.meeting_link"
                                    type="url"
                                    placeholder="https://meet.google.com/..."
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes (optional)</label>
                                <textarea
                                    v-model="confirmForm.admin_notes"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                                ></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showConfirmModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl">Cancel</button>
                            <button type="submit" :disabled="confirmForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 disabled:opacity-50">Confirm Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showCancelModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Cancel Appointment</h3>
                    <form @submit.prevent="submitCancel">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for cancellation *</label>
                            <textarea
                                v-model="cancelForm.cancellation_reason"
                                rows="3"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showCancelModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl">Close</button>
                            <button type="submit" :disabled="cancelForm.processing" class="px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 disabled:opacity-50">Cancel Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showAssignModal = false"></div>
                <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Assign Staff Member</h3>
                    <form @submit.prevent="submitAssign">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Staff *</label>
                            <select
                                v-model="assignForm.assigned_to"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-600"
                            >
                                <option value="">Select a staff member</option>
                                <option v-for="member in staff" :key="member.id" :value="member.id">
                                    {{ member.name }} ({{ member.email }})
                                </option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl">Cancel</button>
                            <button type="submit" :disabled="assignForm.processing" class="px-4 py-2 bg-purple-600 text-white rounded-2xl hover:bg-purple-700 disabled:opacity-50">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
