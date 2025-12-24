<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue';
import BulkActionsBar from '@/Components/ui/BulkActionsBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';
import { useBulkSelection } from '@/Composables/useBulkSelection';
import { useConfirm } from '@/Composables/useConfirm';
import { ref, computed, watch } from 'vue';
import { 
    MagnifyingGlassIcon, PlusIcon, FunnelIcon, CalendarIcon,
    MapPinIcon, UsersIcon, EyeIcon, PencilIcon, TrashIcon,
    SparklesIcon, GlobeAltIcon, CheckCircleIcon, XCircleIcon,
    ChevronLeftIcon, ChevronRightIcon, XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import { debounce } from '@/Composables/useDebouncedSearch';

const props = defineProps({
    events: Object,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();
const { confirmBulk, confirmDelete } = useConfirm();

const { selectedItems: selectedEvents, allSelected, toggleItem, toggleAll, isSelected, hasSelection, selectionCount, clearSelection } = useBulkSelection({
    getItems: () => props.events?.data || [],
});

const bulkPublish = async () => {
    const confirmed = await confirmBulk('publish', selectionCount.value, 'event')
    if (confirmed) {
        router.post(route('admin.events.bulk-publish'), {
            ids: selectedEvents.value,
            published: true,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkUnpublish = async () => {
    const confirmed = await confirmBulk('unpublish', selectionCount.value, 'event')
    if (confirmed) {
        router.post(route('admin.events.bulk-publish'), {
            ids: selectedEvents.value,
            published: false,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkFeature = async () => {
    const confirmed = await confirmBulk('feature', selectionCount.value, 'event')
    if (confirmed) {
        router.post(route('admin.events.bulk-feature'), {
            ids: selectedEvents.value,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkDelete = async () => {
    const confirmed = await confirmBulk('delete', selectionCount.value, 'event')
    if (confirmed) {
        router.post(route('admin.events.bulk-delete'), {
            ids: selectedEvents.value,
        }, {
            onSuccess: () => clearSelection(),
        })
    }
}

const bulkActions = computed(() => [
    { key: 'publish', label: 'Publish', icon: 'CheckCircleIcon', variant: 'success', handler: bulkPublish },
    { key: 'unpublish', label: 'Unpublish', icon: 'XCircleIcon', variant: 'warning', handler: bulkUnpublish },
    { key: 'feature', label: 'Feature', icon: 'SparklesIcon', variant: 'primary', handler: bulkFeature },
    { key: 'delete', label: 'Delete', icon: 'TrashIcon', variant: 'danger', handler: bulkDelete },
])

const { showHelp, globalShortcuts, registerShortcuts } = useKeyboardShortcuts()

// Page-specific shortcuts
const pageShortcuts = [
  { key: 'c', description: 'Create new event', action: () => router.visit(route('admin.events.create')) },
  { key: '/', description: 'Focus search', action: () => document.querySelector('input[type="search"], input[placeholder*="Search"]')?.focus() },
  { key: 'r', description: 'Refresh page', action: () => router.reload() },
]

registerShortcuts(pageShortcuts)

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');
const showFilters = ref(false);
const loading = ref(false);

const performSearch = () => {
    router.get(route('admin.events.index'), {
        search: search.value,
        type: type.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const debouncedPerformSearch = debounce(() => performSearch(), 400);

watch(search, () => {
    debouncedPerformSearch();
});

const clearFilters = () => {
    search.value = '';
    type.value = '';
    status.value = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return search.value || type.value || status.value;
});

const deleteEvent = async (eventId) => {
    const confirmed = await confirmDelete('this event')
    if (confirmed) {
        router.delete(route('admin.events.destroy', eventId));
    }
};

const toggleFeatured = (eventId) => {
    router.post(route('admin.events.toggle-featured', eventId));
};

const togglePublished = (eventId) => {
    router.post(route('admin.events.toggle-published', eventId));
};

const getEventTypeColor = (eventType) => {
    const colors = {
        'seminar': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'workshop': 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        'webinar': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'fair': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'consultation': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'other': 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300',
    };
    return colors[eventType?.toLowerCase()] || 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300';
};

const isUpcoming = (eventDate) => {
    return new Date(eventDate) >= new Date();
};

const getStatusBadge = (event) => {
    if (!event.is_published) {
        return { text: 'Draft', class: 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300' };
    }
    if (isUpcoming(event.event_date)) {
        return { text: 'Upcoming', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' };
    }
    return { text: 'Past', class: 'bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-gray-400' };
};
</script>

<template>
    <Head title="Manage Events" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Events', href: null }
            ]" />
        </template>

        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <!-- Main Content -->
        <div v-else class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- SVG Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="eventsGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#eventsGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-orange-500/20 to-amber-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-yellow-500/20 to-orange-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl shadow-lg">
                                <CalendarIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Events Management</h1>
                                <p class="mt-1 text-gray-300">Manage all events and webinars on the platform</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                {{ showFilters ? 'Hide' : 'Show' }} Filters
                            </button>
                            <Link
                                :href="route('admin.events.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-600 text-white rounded-xl font-semibold hover:from-orange-600 hover:to-amber-700 transition-all shadow-lg"
                            >
                                <PlusIcon class="h-5 w-5" />
                                Create Event
                            </Link>
                        </div>
                    </div>

                    <!-- Stats Cards in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-orange-500/20 rounded-xl">
                                    <CalendarIcon class="h-5 w-5 text-orange-300" />
                                </div>
                                <div>
                                    <p class="text-orange-300 text-xs font-medium">Total Events</p>
                                    <p class="text-2xl font-bold text-white">{{ events?.total || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-xl">
                                    <CheckCircleIcon class="h-5 w-5 text-green-300" />
                                </div>
                                <div>
                                    <p class="text-green-300 text-xs font-medium">Published</p>
                                    <p class="text-2xl font-bold text-white">{{ events?.data?.filter(e => e.is_published).length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-xl">
                                    <SparklesIcon class="h-5 w-5 text-yellow-300" />
                                </div>
                                <div>
                                    <p class="text-yellow-300 text-xs font-medium">Featured</p>
                                    <p class="text-2xl font-bold text-white">{{ events?.data?.filter(e => e.is_featured).length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-xl">
                                    <GlobeAltIcon class="h-5 w-5 text-blue-300" />
                                </div>
                                <div>
                                    <p class="text-blue-300 text-xs font-medium">Online</p>
                                    <p class="text-2xl font-bold text-white">{{ events?.data?.filter(e => e.is_online).length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <!-- Filters Panel -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <!-- Search -->
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <input
                                        v-model="search"
                                        @keyup.enter="performSearch"
                                        type="text"
                                        placeholder="Search events by title or location..."
                                        class="w-full pl-10 pr-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-orange-500 dark:bg-neutral-700 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Event Type -->
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Type</label>
                                <select
                                    v-model="type"
                                    @change="performSearch"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-orange-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Types</option>
                                    <option value="seminar">Seminar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="fair">Fair</option>
                                    <option value="consultation">Consultation</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="lg:w-48">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="status"
                                    @change="performSearch"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-orange-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Status</option>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="past">Past</option>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-end gap-3">
                                <button
                                    @click="performSearch"
                                    class="px-5 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-medium transition-colors"
                                >
                                    Apply
                                </button>
                                <button
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    class="px-5 py-3 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors flex items-center gap-2"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <BulkActionsBar
                    :count="selectionCount"
                    item-label="event"
                    :actions="bulkActions"
                    @clear="clearSelection"
                />

                <!-- Events Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="!events.data || events.data.length === 0"
                        icon="CalendarIcon"
                        title="No events found"
                        description="Create your first event to engage with your community and share important dates."
                        :action="{
                            label: 'Create Event',
                            onClick: () => router.visit(route('admin.events.create')),
                        }"
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            :indeterminate="hasSelection && !allSelected"
                                            @change="toggleAll"
                                            class="h-5 w-5 text-orange-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-orange-500 dark:bg-neutral-700"
                                        />
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Event
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="event in events.data" :key="event.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            :checked="isSelected(event.id)"
                                            @change="toggleItem(event.id)"
                                            class="h-5 w-5 text-orange-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-orange-500 dark:bg-neutral-700"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            <img 
                                                v-if="event.image" 
                                                :src="event.image" 
                                                :alt="event.title"
                                                class="h-12 w-12 rounded-xl object-cover mr-3"
                                            />
                                            <div v-else class="h-12 w-12 bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/30 dark:to-amber-900/30 rounded-xl flex items-center justify-center mr-3">
                                                <CalendarIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ event.title }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ event.description }}</div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span
                                                        v-if="event.is_featured"
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300"
                                                    >
                                                        <SparklesIcon class="h-3 w-3 mr-1" />
                                                        Featured
                                                    </span>
                                                    <span
                                                        v-if="event.max_participants"
                                                        class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400"
                                                    >
                                                        <UsersIcon class="h-3 w-3 mr-1" />
                                                        Max: {{ event.max_participants }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="getEventTypeColor(event.event_type)"
                                            class="px-3 py-1 text-xs font-semibold rounded-full capitalize"
                                        >
                                            {{ event.event_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(event.event_date) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ event.event_time }}</div>
                                        <div v-if="event.end_date" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            to {{ formatDate(event.end_date) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-start text-sm text-gray-900 dark:text-white">
                                            <GlobeAltIcon v-if="event.is_online" class="h-4 w-4 mr-1.5 text-green-500 mt-0.5 flex-shrink-0" />
                                            <MapPinIcon v-else class="h-4 w-4 mr-1.5 text-gray-400 mt-0.5 flex-shrink-0" />
                                            <div>
                                                <div>{{ event.is_online ? 'Online' : event.location }}</div>
                                                <div v-if="event.is_online && event.meeting_link" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px]">
                                                    {{ event.meeting_link }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="getStatusBadge(event).class"
                                            class="px-3 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ getStatusBadge(event).text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <Link
                                                :href="route('admin.events.show', event.id)"
                                                class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-xl transition-colors"
                                                title="View"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <Link
                                                :href="route('admin.events.edit', event.id)"
                                                class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-colors"
                                                title="Edit"
                                            >
                                                <PencilIcon class="h-5 w-5" />
                                            </Link>
                                            <button
                                                @click="toggleFeatured(event.id)"
                                                :class="event.is_featured ? 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700'"
                                                class="p-2 rounded-xl transition-colors"
                                                title="Toggle Featured"
                                            >
                                                <SparklesIcon class="h-5 w-5" />
                                            </button>
                                            <button
                                                @click="togglePublished(event.id)"
                                                :class="event.is_published ? 'text-green-600 bg-green-50 dark:bg-green-900/20' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700'"
                                                class="p-2 rounded-xl transition-colors"
                                                title="Toggle Published"
                                            >
                                                <CheckCircleIcon class="h-5 w-5" />
                                            </button>
                                            <button
                                                @click="deleteEvent(event.id)"
                                                class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                                                title="Delete"
                                            >
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="events.data && events.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-medium text-gray-900 dark:text-white">{{ events.from }}</span>
                                to <span class="font-medium text-gray-900 dark:text-white">{{ events.to }}</span>
                                of <span class="font-medium text-gray-900 dark:text-white">{{ events.total }}</span> events
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="events.prev_page_url"
                                    :href="events.prev_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <Link
                                    v-if="events.next_page_url"
                                    :href="events.next_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <KeyboardShortcutsModal
            v-model:show="showHelp"
            :shortcuts="pageShortcuts"
            :global-shortcuts="globalShortcuts"
        />
    </AdminLayout>
</template>
