<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import EmptyState from '@/Components/Base/EmptyState.vue'
import { 
    MagnifyingGlassIcon, 
    FunnelIcon, 
    EyeIcon, 
    XMarkIcon,
    ClockIcon,
    UserCircleIcon,
    DocumentTextIcon,
    ArrowPathIcon,
    PlusCircleIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowRightOnRectangleIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
    activities: Object,
    filters: Object,
    subjectTypes: Array,
    events: Array
})

const { formatDate, formatTime } = useBangladeshFormat()

const searchQuery = ref(props.filters?.search || '')
const selectedSubjectType = ref(props.filters?.subject_type || '')
const selectedEvent = ref(props.filters?.event || '')
const fromDate = ref(props.filters?.from_date || '')
const toDate = ref(props.filters?.to_date || '')
const showFilters = ref(false)

const hasActiveFilters = computed(() => {
    return searchQuery.value || selectedSubjectType.value || selectedEvent.value || fromDate.value || toDate.value
})

const applyFilters = () => {
    router.get(route('admin.activity-log.index'), {
        search: searchQuery.value,
        subject_type: selectedSubjectType.value,
        event: selectedEvent.value,
        from_date: fromDate.value,
        to_date: toDate.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedSubjectType.value = ''
    selectedEvent.value = ''
    fromDate.value = ''
    toDate.value = ''
    applyFilters()
}

const getEventColor = (event) => {
    return {
        'created': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'updated': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'deleted': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'restored': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'login': 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400',
        'logout': 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'
    }[event] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'
}

const getEventIcon = (event) => {
    return {
        'created': PlusCircleIcon,
        'updated': PencilSquareIcon,
        'deleted': TrashIcon,
        'restored': ArrowPathIcon,
        'login': ArrowRightOnRectangleIcon,
        'logout': ArrowRightOnRectangleIcon
    }[event] || DocumentTextIcon
}

const getSubjectLabel = (subjectType) => {
    if (!subjectType) return 'N/A'
    return subjectType.split('\\').pop()
}
</script>

<template>
    <Head title="Activity Log" />

    <AdminLayout>
        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="activityGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#activityGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-teal-500/20 to-cyan-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-blue-500/20 to-teal-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <ClockIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">Activity Log</h1>
                            </div>
                            <p class="text-neutral-300">Track all system activities and user actions</p>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                                <span v-if="hasActiveFilters" class="ml-1 px-2 py-0.5 bg-teal-500 text-white text-xs rounded-full">Active</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-2xl">
                                    <DocumentTextIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total Activities</p>
                                    <p class="text-2xl font-bold text-white">{{ activities?.total || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-2xl">
                                    <PlusCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Creates</p>
                                    <p class="text-2xl font-bold text-white">{{ activities?.data?.filter(a => a.event === 'created').length || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-2xl">
                                    <PencilSquareIcon class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Updates</p>
                                    <p class="text-2xl font-bold text-white">{{ activities?.data?.filter(a => a.event === 'updated').length || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-500/20 rounded-2xl">
                                    <TrashIcon class="h-6 w-6 text-red-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Deletes</p>
                                    <p class="text-2xl font-bold text-white">{{ activities?.data?.filter(a => a.event === 'deleted').length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Collapsible Filters -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filter Activities</h3>
                            <button @click="showFilters = false" class="p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors">
                                <XMarkIcon class="h-5 w-5 text-neutral-500" />
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search..."
                                        @keyup.enter="applyFilters"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-teal-500 focus:ring-teal-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Subject Type</label>
                                <select
                                    v-model="selectedSubjectType"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 py-2.5"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in subjectTypes" :key="type" :value="type">{{ type }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Event</label>
                                <select
                                    v-model="selectedEvent"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 py-2.5"
                                >
                                    <option value="">All Events</option>
                                    <option v-for="event in events" :key="event" :value="event">{{ event }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">From Date</label>
                                <input
                                    v-model="fromDate"
                                    type="date"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 py-2.5"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">To Date</label>
                                <input
                                    v-model="toDate"
                                    type="date"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 py-2.5"
                                />
                            </div>
                        </div>

                        <div class="flex gap-3 mt-4">
                            <button
                                @click="applyFilters"
                                class="px-4 py-2.5 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-medium rounded-xl hover:from-teal-600 hover:to-cyan-600 transition-all"
                            >
                                Apply Filters
                            </button>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="px-4 py-2.5 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-xl hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </transition>

                <!-- Activity Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="!activities?.data || activities.data.length === 0"
                        icon="ClipboardDocumentListIcon"
                        title="No activity logs"
                        description="Activity logs will appear here once users start interacting with the platform."
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="activity in activities.data" :key="activity.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="getEventColor(activity.event)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1 capitalize"
                                        >
                                            <component :is="getEventIcon(activity.event)" class="h-3 w-3" />
                                            {{ activity.event || 'unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-neutral-900 dark:text-white max-w-xs truncate">{{ activity.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ getSubjectLabel(activity.subject_type) }}</div>
                                        <div v-if="activity.subject_id" class="text-xs text-neutral-500 dark:text-neutral-400">ID: {{ activity.subject_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="activity.causer" class="flex items-center gap-2">
                                            <div class="h-8 w-8 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-xs">{{ activity.causer.name?.charAt(0) || 'U' }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ activity.causer.name }}</div>
                                                <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ activity.causer.email }}</div>
                                            </div>
                                        </div>
                                        <span v-else class="text-sm text-neutral-500 dark:text-neutral-400 italic">System</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-neutral-900 dark:text-white">{{ formatDate(activity.created_at) }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatTime(activity.created_at) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <Link
                                            :href="route('admin.activity-log.show', activity.id)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/20 rounded-2xl transition-colors"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="activities?.data && activities.data.length > 0" class="bg-neutral-50 dark:bg-neutral-900/50 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Showing <span class="font-semibold text-neutral-900 dark:text-white">{{ activities.from || 0 }}</span>
                                to <span class="font-semibold text-neutral-900 dark:text-white">{{ activities.to || 0 }}</span>
                                of <span class="font-semibold text-neutral-900 dark:text-white">{{ activities.total || 0 }}</span> activities
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="activities.prev_page_url"
                                    :href="activities.prev_page_url"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-2xl hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <span v-else class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-2xl cursor-not-allowed">
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </span>
                                <Link
                                    v-if="activities.next_page_url"
                                    :href="activities.next_page_url"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-2xl hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                                <span v-else class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-2xl cursor-not-allowed">
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
