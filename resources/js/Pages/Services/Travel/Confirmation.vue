<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircleIcon, CalendarIcon, ClockIcon, UserIcon, VideoCameraIcon, MapPinIcon, EnvelopeIcon, PhoneIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    appointment: Object
});

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }).format(date);
};

const formatTime = (time) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
    return `${displayHour}:${minutes} ${ampm}`;
};
</script>

<template>
    <Head title="Booking Confirmed" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Header -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-12 text-center">
                        <div class="flex justify-center mb-4">
                            <div class="rounded-full bg-white p-3">
                                <CheckCircleIcon class="h-16 w-16 text-green-600" />
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold text-white mb-2">Booking Confirmed!</h1>
                        <p class="text-green-50 text-lg">Your travel consultation has been successfully booked</p>
                        <p class="text-green-100 text-sm mt-2">Booking #{{ appointment.appointment_number }}</p>
                    </div>

                    <!-- Appointment Details -->
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Date & Time -->
                            <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-ocean-100 dark:bg-ocean-900 rounded-lg">
                                        <CalendarIcon class="h-6 w-6 text-ocean-600 dark:text-ocean-400" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Date & Time</p>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ formatDate(appointment.appointment_date) }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                                        <ClockIcon class="h-4 w-4" />
                                        {{ formatTime(appointment.appointment_time) }} ({{ appointment.duration || '60' }} minutes)
                                    </p>
                                </div>
                            </div>

                            <!-- Consultant -->
                            <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                                        <UserIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Your Consultant</p>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ appointment.assigned_to?.name || 'To be assigned' }}</p>
                                    <p v-if="appointment.assigned_to?.email" class="text-sm text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                                        <EnvelopeIcon class="h-4 w-4" />
                                        {{ appointment.assigned_to.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Meeting Type -->
                            <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                        <VideoCameraIcon v-if="appointment.is_online" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                        <MapPinIcon v-else class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Meeting Type</p>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">
                                        {{ appointment.is_online ? 'Online Meeting' : 'Office Visit' }}
                                    </p>
                                    <p v-if="appointment.meeting_link" class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                                        Meeting link will be sent via email
                                    </p>
                                    <p v-else-if="appointment.location" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ appointment.location }}
                                    </p>
                                </div>
                            </div>

                            <!-- Purpose -->
                            <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-amber-100 dark:bg-amber-900 rounded-lg">
                                        <CheckCircleIcon class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Purpose</p>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-1 capitalize">
                                        {{ appointment.purpose?.replace(/_/g, ' ') || 'Travel Consultation' }}
                                    </p>
                                    <p v-if="appointment.notes" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ appointment.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-8">
                            <span :class="{
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': appointment.status === 'pending',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': appointment.status === 'confirmed',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': appointment.status === 'completed',
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': appointment.status === 'cancelled'
                            }" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold">
                                Status: {{ appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1) }}
                            </span>
                        </div>

                        <!-- What's Next -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">What's Next?</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">You'll receive a confirmation email with all the details</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Your consultant will reach out 24 hours before the meeting</span>
                                </li>
                                <li v-if="appointment.is_online" class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Meeting link will be sent to your email 1 hour before</span>
                                </li>
                                <li v-else class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Please arrive 10 minutes early for in-person visits</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">You can reschedule or cancel up to 24 hours before</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link :href="route('travel.bookings.index')" class="flex-1 text-center bg-ocean-600 text-white px-6 py-3 rounded-lg hover:bg-ocean-700 transition-colors font-medium">
                                View My Bookings
                            </Link>
                            <Link :href="route('dashboard')" class="flex-1 text-center border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                                Go to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Need Help -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Need help? <Link :href="route('support.tickets.create')" class="text-ocean-600 hover:text-ocean-700 font-medium">Contact Support</Link>
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
