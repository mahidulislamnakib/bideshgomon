<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { CalendarIcon, ClockIcon, UserIcon, VideoCameraIcon, MapPinIcon, CheckCircleIcon, ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    consultants: Array,
    availableSlots: Object // { '2025-12-05': ['09:00', '10:00', ...], ... }
});

// Current month view
const currentDate = ref(new Date());
const selectedDate = ref(null);
const selectedTime = ref(null);

// Form
const form = useForm({
    consultant_id: '',
    appointment_date: '',
    appointment_time: '',
    appointment_type: 'online_meeting',
    purpose: 'travel_consultation',
    notes: ''
});

// Calendar navigation
const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());

const monthName = computed(() => {
    return new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(currentDate.value);
});

const previousMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentDate.value = newDate;
};

const nextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentDate.value = newDate;
};

// Generate calendar days
const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const prevLastDay = new Date(year, month, 0);
    
    const firstDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    const daysInPrevMonth = prevLastDay.getDate();
    
    const days = [];
    
    // Previous month days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        days.push({
            day: daysInPrevMonth - i,
            isCurrentMonth: false,
            isPast: true,
            date: new Date(year, month - 1, daysInPrevMonth - i)
        });
    }
    
    // Current month days
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateStr = date.toISOString().split('T')[0];
        const hasSlots = props.availableSlots[dateStr]?.length > 0;
        
        days.push({
            day,
            isCurrentMonth: true,
            isPast: date < today,
            isToday: date.toDateString() === today.toDateString(),
            hasSlots,
            date,
            dateStr
        });
    }
    
    // Next month days
    const remainingDays = 42 - days.length;
    for (let day = 1; day <= remainingDays; day++) {
        days.push({
            day,
            isCurrentMonth: false,
            isPast: false,
            date: new Date(year, month + 1, day)
        });
    }
    
    return days;
});

// Available times for selected date
const availableTimes = computed(() => {
    if (!selectedDate.value) return [];
    return props.availableSlots[selectedDate.value] || [];
});

// Select date
const selectDate = (day) => {
    if (!day.isCurrentMonth || day.isPast || !day.hasSlots) return;
    
    selectedDate.value = day.dateStr;
    form.appointment_date = day.dateStr;
    selectedTime.value = null;
    form.appointment_time = '';
};

// Select time
const selectTime = (time) => {
    selectedTime.value = time;
    form.appointment_time = time;
};

// Format time
const formatTime = (time) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
    return `${displayHour}:${minutes} ${ampm}`;
};

// Format date
const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }).format(date);
};

// Submit booking
const submit = () => {
    form.post(route('travel.bookings.store'), {
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
};

// Watch consultant change
watch(() => form.consultant_id, (newVal) => {
    if (newVal) {
        // Reload available slots for selected consultant
        router.reload({ only: ['availableSlots'], data: { consultant_id: newVal } });
    }
});
</script>

<template>
    <Head title="Book Travel Consultation" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Book Travel Consultation</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Schedule a consultation with our travel experts</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Calendar View (Left Side) -->
                    <div class="lg:col-span-2">
                        <!-- Consultant Selection -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">1. Select Consultant</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label v-for="consultant in consultants" :key="consultant.id" class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none" :class="form.consultant_id === consultant.id ? 'border-ocean-600 bg-ocean-50 dark:bg-ocean-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-ocean-300'">
                                    <input type="radio" v-model="form.consultant_id" :value="consultant.id" class="sr-only" />
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex-shrink-0">
                                            <UserIcon class="h-10 w-10 text-gray-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ consultant.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ consultant.specialization || 'Travel Consultant' }}</p>
                                            <div class="flex items-center mt-1">
                                                <span class="text-yellow-500 text-sm">★</span>
                                                <span class="text-xs text-gray-600 dark:text-gray-400 ml-1">{{ consultant.rating || '4.8' }} ({{ consultant.reviews || '120' }} reviews)</span>
                                            </div>
                                        </div>
                                        <CheckCircleIcon v-if="form.consultant_id === consultant.id" class="h-6 w-6 text-ocean-600 flex-shrink-0" />
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.consultant_id" class="mt-2 text-sm text-red-600">{{ form.errors.consultant_id }}</p>
                        </div>

                        <!-- Calendar -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">2. Select Date & Time</h3>
                                <div class="flex items-center gap-2">
                                    <button @click="previousMonth" type="button" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                        <ArrowLeftIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                    </button>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white min-w-[150px] text-center">{{ monthName }}</span>
                                    <button @click="nextMonth" type="button" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                        <ArrowRightIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                    </button>
                                </div>
                            </div>

                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-2 mb-4">
                                <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center text-xs font-semibold text-gray-600 dark:text-gray-400 py-2">
                                    {{ day }}
                                </div>
                                <button v-for="(day, index) in calendarDays" :key="index" @click="selectDate(day)" type="button" :disabled="!day.isCurrentMonth || day.isPast || !day.hasSlots" class="aspect-square p-2 rounded-lg text-sm font-medium transition-colors relative" :class="{
                                    'text-gray-400 dark:text-gray-600 cursor-not-allowed': !day.isCurrentMonth,
                                    'text-gray-400 dark:text-gray-600 cursor-not-allowed opacity-50': day.isPast,
                                    'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white cursor-pointer': day.isCurrentMonth && !day.isPast && day.hasSlots,
                                    'bg-ocean-600 text-white': selectedDate === day.dateStr,
                                    'ring-2 ring-ocean-600': day.isToday && selectedDate !== day.dateStr,
                                    'cursor-not-allowed opacity-50': day.isCurrentMonth && !day.isPast && !day.hasSlots
                                }">
                                    {{ day.day }}
                                    <span v-if="day.hasSlots && selectedDate !== day.dateStr" class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-ocean-600 rounded-full"></span>
                                </button>
                            </div>

                            <!-- Time Slots -->
                            <div v-if="selectedDate" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Available Times for {{ formatDate(selectedDate) }}</h4>
                                <div v-if="availableTimes.length > 0" class="grid grid-cols-4 gap-2">
                                    <button v-for="time in availableTimes" :key="time" @click="selectTime(time)" type="button" class="px-3 py-2 rounded-lg border-2 text-sm font-medium transition-colors" :class="selectedTime === time ? 'border-ocean-600 bg-ocean-50 dark:bg-ocean-900/20 text-ocean-700 dark:text-ocean-400' : 'border-gray-300 dark:border-gray-600 hover:border-ocean-300 text-gray-700 dark:text-gray-300'">
                                        {{ formatTime(time) }}
                                    </button>
                                </div>
                                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <ClockIcon class="mx-auto h-12 w-12 text-gray-400" />
                                    <p class="mt-2 text-sm">No available time slots for this date</p>
                                </div>
                            </div>
                            <p v-if="form.errors.appointment_date" class="mt-2 text-sm text-red-600">{{ form.errors.appointment_date }}</p>
                            <p v-if="form.errors.appointment_time" class="mt-2 text-sm text-red-600">{{ form.errors.appointment_time }}</p>
                        </div>
                    </div>

                    <!-- Booking Details (Right Sidebar) -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 sticky top-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">3. Booking Details</h3>

                            <!-- Meeting Type -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Meeting Type</label>
                                <div class="space-y-2">
                                    <label class="relative flex cursor-pointer rounded-lg border-2 p-3 focus:outline-none" :class="form.appointment_type === 'online_meeting' ? 'border-ocean-600 bg-ocean-50 dark:bg-ocean-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-ocean-300'">
                                        <input type="radio" v-model="form.appointment_type" value="online_meeting" class="sr-only" />
                                        <div class="flex items-center gap-3 flex-1">
                                            <VideoCameraIcon class="h-5 w-5 text-gray-400" />
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">Online Meeting</span>
                                            <CheckCircleIcon v-if="form.appointment_type === 'online_meeting'" class="h-5 w-5 text-ocean-600 ml-auto" />
                                        </div>
                                    </label>
                                    <label class="relative flex cursor-pointer rounded-lg border-2 p-3 focus:outline-none" :class="form.appointment_type === 'office_visit' ? 'border-ocean-600 bg-ocean-50 dark:bg-ocean-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-ocean-300'">
                                        <input type="radio" v-model="form.appointment_type" value="office_visit" class="sr-only" />
                                        <div class="flex items-center gap-3 flex-1">
                                            <MapPinIcon class="h-5 w-5 text-gray-400" />
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">Office Visit</span>
                                            <CheckCircleIcon v-if="form.appointment_type === 'office_visit'" class="h-5 w-5 text-ocean-600 ml-auto" />
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Purpose -->
                            <div class="mb-6">
                                <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Purpose</label>
                                <select v-model="form.purpose" id="purpose" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-ocean-500 focus:border-ocean-500">
                                    <option value="travel_consultation">Travel Consultation</option>
                                    <option value="package_discussion">Package Discussion</option>
                                    <option value="visa_guidance">Visa Guidance</option>
                                    <option value="itinerary_planning">Itinerary Planning</option>
                                    <option value="general_inquiry">General Inquiry</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class="mb-6">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional Notes (Optional)</label>
                                <textarea v-model="form.notes" id="notes" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-ocean-500 focus:border-ocean-500" placeholder="Tell us about your travel plans..."></textarea>
                            </div>

                            <!-- Summary -->
                            <div v-if="form.consultant_id && selectedDate && selectedTime" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Booking Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-start gap-2">
                                        <UserIcon class="h-4 w-4 text-gray-400 mt-0.5" />
                                        <span class="text-gray-600 dark:text-gray-400">{{ consultants.find(c => c.id === form.consultant_id)?.name }}</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <CalendarIcon class="h-4 w-4 text-gray-400 mt-0.5" />
                                        <span class="text-gray-600 dark:text-gray-400">{{ formatDate(selectedDate) }}</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <ClockIcon class="h-4 w-4 text-gray-400 mt-0.5" />
                                        <span class="text-gray-600 dark:text-gray-400">{{ formatTime(selectedTime) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button @click="submit" type="button" :disabled="!form.consultant_id || !selectedDate || !selectedTime || form.processing" class="w-full bg-ocean-600 text-white px-4 py-3 rounded-lg hover:bg-ocean-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="form.processing">Booking...</span>
                                <span v-else>Confirm Booking</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
