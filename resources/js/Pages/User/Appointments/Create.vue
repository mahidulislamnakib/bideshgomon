<template>
    <AuthenticatedLayout>
        <Head title="Book Appointment" />

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
                    <Link :href="route('appointments.index')" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors mb-4">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Appointments
                    </Link>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        Book an Appointment
                    </h1>
                    <p class="text-lg text-white/80">
                        Schedule a meeting with our team
                    </p>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Main Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-100">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <CalendarDaysIcon class="w-5 h-5 text-growth-600" />
                                    Appointment Details
                                </h3>
                            </div>
                            <div class="p-6">
                                <form @submit.prevent="submit">
                                    <!-- Appointment Type -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-semibold text-gray-900 mb-3">
                                            Appointment Type <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="relative flex cursor-pointer rounded-2xl border-2 p-4 focus:outline-none transition-all" :class="form.appointment_type === 'office_visit' ? 'border-growth-600 bg-growth-50 shadow-lg shadow-growth-500/20' : 'border-gray-200 hover:border-growth-300'">
                                                <input type="radio" v-model="form.appointment_type" value="office_visit" class="sr-only" />
                                                <div class="flex flex-1 items-center gap-3">
                                                    <div :class="form.appointment_type === 'office_visit' ? 'bg-growth-100' : 'bg-gray-100'" class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                                                        <BuildingOfficeIcon :class="form.appointment_type === 'office_visit' ? 'text-growth-600' : 'text-gray-400'" class="w-6 h-6" />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="block text-sm font-bold text-gray-900">Office Visit</span>
                                                        <span class="text-xs text-gray-500">In-person meeting</span>
                                                    </div>
                                                </div>
                                                <CheckCircleIcon v-if="form.appointment_type === 'office_visit'" class="h-6 w-6 text-growth-600" />
                                            </label>

                                            <label class="relative flex cursor-pointer rounded-2xl border-2 p-4 focus:outline-none transition-all" :class="form.appointment_type === 'online_meeting' ? 'border-growth-600 bg-growth-50 shadow-lg shadow-growth-500/20' : 'border-gray-200 hover:border-growth-300'">
                                                <input type="radio" v-model="form.appointment_type" value="online_meeting" class="sr-only" />
                                                <div class="flex flex-1 items-center gap-3">
                                                    <div :class="form.appointment_type === 'online_meeting' ? 'bg-growth-100' : 'bg-gray-100'" class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                                                        <VideoCameraIcon :class="form.appointment_type === 'online_meeting' ? 'text-growth-600' : 'text-gray-400'" class="w-6 h-6" />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="block text-sm font-bold text-gray-900">Online Meeting</span>
                                                        <span class="text-xs text-gray-500">Video call</span>
                                                    </div>
                                                </div>
                                                <CheckCircleIcon v-if="form.appointment_type === 'online_meeting'" class="h-6 w-6 text-growth-600" />
                                            </label>
                                        </div>
                                        <p v-if="form.errors.appointment_type" class="mt-2 text-sm text-red-600">{{ form.errors.appointment_type }}</p>
                                    </div>

                                    <!-- Date Selection -->
                                    <div class="mb-6">
                                        <label for="appointment_date" class="block text-sm font-semibold text-gray-900 mb-2">
                                            Select Date <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <CalendarDaysIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input
                                                id="appointment_date"
                                                v-model="form.appointment_date"
                                                type="date"
                                                :min="minDate"
                                                :max="maxDate"
                                                @change="loadTimeSlotsForDate"
                                                class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-growth-500 focus:ring-growth-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.appointment_date }"
                                                required
                                            />
                                        </div>
                                        <p v-if="form.errors.appointment_date" class="mt-2 text-sm text-red-600">{{ form.errors.appointment_date }}</p>
                                        <p class="mt-2 text-xs text-gray-500">Available: Monday - Friday, next 30 days</p>
                                    </div>

                                    <!-- Time Selection -->
                                    <div v-if="form.appointment_date" class="mb-6">
                                        <label class="block text-sm font-semibold text-gray-900 mb-3">
                                            Select Time <span class="text-red-500">*</span>
                                        </label>
                                        <div v-if="availableTimesForDate.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                            <label v-for="time in availableTimesForDate" :key="time" class="relative flex cursor-pointer rounded-xl border-2 p-3 text-center focus:outline-none transition-all" :class="form.appointment_time === time ? 'border-growth-600 bg-growth-50 shadow-lg shadow-growth-500/20' : 'border-gray-200 hover:border-growth-300'">
                                                <input type="radio" v-model="form.appointment_time" :value="time" class="sr-only" />
                                                <span class="flex-1 text-sm font-semibold" :class="form.appointment_time === time ? 'text-growth-700' : 'text-gray-700'">
                                                    {{ formatTime(time) }}
                                                </span>
                                            </label>
                                        </div>
                                        <div v-else class="text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                            <ClockIcon class="mx-auto h-12 w-12 text-gray-300" />
                                            <p class="mt-2 font-semibold text-gray-600">No available time slots</p>
                                            <p v-if="isWeekend" class="mt-1 text-sm text-amber-600">Office is closed on weekends. Please select a weekday.</p>
                                            <p v-else class="mt-1 text-sm text-gray-500">All slots are booked. Please try another date.</p>
                                        </div>
                                        <p v-if="form.errors.appointment_time" class="mt-2 text-sm text-red-600">{{ form.errors.appointment_time }}</p>
                                    </div>

                                    <!-- Purpose -->
                                    <div class="mb-6">
                                        <label for="purpose" class="block text-sm font-semibold text-gray-900 mb-2">
                                            Purpose <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            id="purpose"
                                            v-model="form.purpose"
                                            class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 shadow-sm focus:border-growth-500 focus:ring-growth-500 transition-all"
                                            :class="{ 'border-red-500': form.errors.purpose }"
                                            required
                                        >
                                            <option value="">Select Purpose</option>
                                            <option value="consultation">📋 Consultation</option>
                                            <option value="document_submission">📁 Document Submission</option>
                                            <option value="general_inquiry">❓ General Inquiry</option>
                                            <option value="visa_interview_prep">🎯 Visa Interview Preparation</option>
                                            <option value="application_review">📝 Application Review</option>
                                        </select>
                                        <p v-if="form.errors.purpose" class="mt-2 text-sm text-red-600">{{ form.errors.purpose }}</p>
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-6">
                                        <label for="notes" class="block text-sm font-semibold text-gray-900 mb-2">
                                            Additional Notes (Optional)
                                        </label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="4"
                                            class="w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-growth-500 focus:ring-growth-500 transition-all resize-none"
                                            :class="{ 'border-red-500': form.errors.notes }"
                                            placeholder="Any specific requirements or topics you'd like to discuss..."
                                        ></textarea>
                                        <p v-if="form.errors.notes" class="mt-2 text-sm text-red-600">{{ form.errors.notes }}</p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                        <Link :href="route('appointments.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </Link>
                                        <button
                                            type="submit"
                                            :disabled="form.processing || !canSubmit"
                                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-growth-600 to-growth-700 text-white font-bold rounded-xl shadow-lg shadow-growth-500/30 hover:shadow-xl transition-all"
                                            :class="{ 'opacity-50 cursor-not-allowed': form.processing || !canSubmit }"
                                        >
                                            <CalendarDaysIcon v-if="!form.processing" class="w-5 h-5" />
                                            <span v-if="form.processing">Booking...</span>
                                            <span v-else>Book Appointment</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Summary Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                                <h3 class="font-bold text-white flex items-center gap-2">
                                    <InformationCircleIcon class="w-5 h-5" />
                                    Appointment Summary
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Type</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ form.appointment_type === 'office_visit' ? '🏢 Office Visit' : '💻 Online Meeting' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Date</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ form.appointment_date || 'Not selected' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Time</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ form.appointment_time ? formatTime(form.appointment_time) : 'Not selected' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-500">Purpose</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ form.purpose || 'Not selected' }}
                                    </span>
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

                        <!-- Tips Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <LightBulbIcon class="w-5 h-5 text-yellow-500" />
                                Tips
                            </h3>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>Appointments are available Mon-Fri</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>Cancel or reschedule 24h in advance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>Bring relevant documents for office visits</span>
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
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CalendarDaysIcon, 
    ArrowLeftIcon, 
    ClockIcon, 
    CheckCircleIcon,
    BuildingOfficeIcon,
    VideoCameraIcon,
    InformationCircleIcon,
    PhoneIcon,
    LightBulbIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    availableSlots: Object,
});

const form = useForm({
    appointment_type: 'office_visit',
    appointment_date: '',
    appointment_time: '',
    purpose: '',
    notes: '',
});

const today = new Date();
const minDate = today.toISOString().split('T')[0];
const maxDate = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

const availableTimesForDate = ref([]);

const canSubmit = computed(() => {
    return form.appointment_type && form.appointment_date && form.appointment_time && form.purpose;
});

const isWeekend = computed(() => {
    if (!form.appointment_date) return false;
    const date = new Date(form.appointment_date + 'T00:00:00');
    const day = date.getDay();
    return day === 0 || day === 6; // 0 = Sunday, 6 = Saturday
});

const loadTimeSlotsForDate = () => {
    const dateStr = form.appointment_date;
    availableTimesForDate.value = props.availableSlots[dateStr] || [];
    form.appointment_time = ''; // Reset time when date changes
};

const formatTime = (time) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
    return `${displayHour}:${minutes} ${ampm}`;
};

const submit = () => {
    form.post(route('appointments.store'), {
        preserveScroll: true,
    });
};
</script>
