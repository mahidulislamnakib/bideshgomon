<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    ArrowLeftIcon, ChevronLeftIcon, ChevronRightIcon, 
    CalendarDaysIcon, ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    appointments: Array,
});

const currentDate = ref(new Date());

const currentMonthName = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];
    
    // Add empty slots for days before the first day of the month
    const startDay = firstDay.getDay();
    for (let i = 0; i < startDay; i++) {
        days.push({ date: null, appointments: [] });
    }
    
    // Add all days of the month
    for (let day = 1; day <= lastDay.getDate(); day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayAppointments = props.appointments.filter(apt => apt.start?.startsWith(dateStr));
        days.push({ 
            date: day, 
            dateStr,
            isToday: dateStr === new Date().toISOString().split('T')[0],
            appointments: dayAppointments 
        });
    }
    
    return days;
});

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-yellow-500',
        'confirmed': 'bg-blue-500',
        'completed': 'bg-green-500',
        'cancelled': 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
};

const formatTime = (datetime) => {
    if (!datetime) return '';
    const time = datetime.split('T')[1];
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
};
</script>

<template>
    <Head title="Appointments Calendar" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Header -->
            <div class="relative overflow-hidden px-4 py-8 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, #10b981 0%, transparent 50%), radial-gradient(circle at 75% 75%, #0d9488 0%, transparent 50%);"></div>
                </div>
                <div class="max-w-7xl mx-auto relative z-10">
                    <Link :href="route('admin.appointments.index')" class="inline-flex items-center text-gray-300 hover:text-white mb-4 transition-colors">
                        <ArrowLeftIcon class="h-5 w-5 mr-2" />
                        Back to List
                    </Link>
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white">Calendar View</h1>
                            <p class="mt-2 text-gray-300">View appointments in calendar format</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                    <!-- Calendar Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ currentMonthName }}</h2>
                        <div class="flex items-center gap-2">
                            <button
                                @click="goToToday"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-neutral-700 rounded-2xl hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
                            >
                                Today
                            </button>
                            <button
                                @click="previousMonth"
                                class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                            >
                                <ChevronLeftIcon class="h-5 w-5" />
                            </button>
                            <button
                                @click="nextMonth"
                                class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                            >
                                <ChevronRightIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-neutral-700 rounded-2xl overflow-hidden">
                        <!-- Day Headers -->
                        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="bg-gray-50 dark:bg-neutral-750 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ day }}
                        </div>
                        
                        <!-- Calendar Days -->
                        <div 
                            v-for="(day, index) in daysInMonth" 
                            :key="index" 
                            class="bg-white dark:bg-neutral-800 min-h-[120px] p-2"
                            :class="{ 'bg-blue-50 dark:bg-blue-900/20': day.isToday }"
                        >
                            <div v-if="day.date" class="h-full">
                                <div class="flex items-center justify-between mb-2">
                                    <span 
                                        class="text-sm font-medium"
                                        :class="day.isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'"
                                    >
                                        {{ day.date }}
                                    </span>
                                    <span v-if="day.appointments.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ day.appointments.length }} apt{{ day.appointments.length > 1 ? 's' : '' }}
                                    </span>
                                </div>
                                <div class="space-y-1 overflow-y-auto max-h-[80px]">
                                    <Link
                                        v-for="apt in day.appointments.slice(0, 3)"
                                        :key="apt.id"
                                        :href="apt.url"
                                        class="block p-1.5 rounded text-xs hover:opacity-80 transition-opacity"
                                        :class="[getStatusColor(apt.status), 'text-white']"
                                    >
                                        <div class="font-medium truncate">{{ apt.title }}</div>
                                        <div class="opacity-80">{{ formatTime(apt.start) }}</div>
                                    </Link>
                                    <div v-if="day.appointments.length > 3" class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                        +{{ day.appointments.length - 3 }} more
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center justify-center gap-6 mt-6 pt-6 border-t border-gray-200 dark:border-neutral-700">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Confirmed</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
