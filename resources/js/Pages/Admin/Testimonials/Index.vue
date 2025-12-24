<script setup>
import { ref, reactive, computed } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageSkeleton from '@/Components/ui/PageSkeleton.vue'
import EmptyState from '@/Components/Base/EmptyState.vue'
import {
    ChatBubbleBottomCenterTextIcon,
    StarIcon as StarIconOutline,
    CheckCircleIcon,
    SparklesIcon,
    PlusIcon,
    FunnelIcon,
    XMarkIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CheckIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
    testimonials: Object,
    filters: Object,
    stats: Object,
})

const loading = ref(false)
const showFilters = ref(false)

const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    rating: props.filters?.rating || '',
})

let searchTimeout = null

const hasActiveFilters = () => {
    return filters.search || filters.status || filters.rating
}

const search = () => {
    router.get(route('admin.testimonials.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(search, 300)
}

const resetFilters = () => {
    filters.search = ''
    filters.status = ''
    filters.rating = ''
    search()
}

const toggleApproval = (testimonial) => {
    const action = testimonial.is_approved ? 'unapprove' : 'approve'
    if (confirm(`Are you sure you want to ${action} this testimonial?`)) {
        router.post(route('admin.testimonials.toggle-approved', testimonial.id), {}, {
            preserveScroll: true,
        })
    }
}

const toggleFeatured = (testimonial) => {
    const action = testimonial.is_featured ? 'unfeature' : 'feature'
    if (confirm(`Are you sure you want to ${action} this testimonial?`)) {
        router.post(route('admin.testimonials.toggle-featured', testimonial.id), {}, {
            preserveScroll: true,
        })
    }
}

const deleteTestimonial = (testimonial) => {
    if (confirm(`Are you sure you want to delete the testimonial by ${testimonial.author_name}?`)) {
        router.delete(route('admin.testimonials.destroy', testimonial.id))
    }
}

// Computed stats from data if not provided
const computedStats = computed(() => {
    if (props.stats) return props.stats
    const data = props.testimonials?.data || []
    return {
        total: props.testimonials?.total || data.length,
        approved: data.filter(t => t.is_approved).length,
        pending: data.filter(t => !t.is_approved).length,
        featured: data.filter(t => t.is_featured).length,
    }
})
</script>

<template>
    <Head title="Testimonials" />

    <AdminLayout>
        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <!-- Main Content -->
        <div v-else class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Hero Header with Stats -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="testimonialGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#testimonialGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <ChatBubbleBottomCenterTextIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">Testimonials</h1>
                            </div>
                            <p class="text-neutral-300">Manage customer testimonials and reviews</p>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                                <span v-if="hasActiveFilters()" class="ml-1 px-2 py-0.5 bg-purple-500 text-white text-xs rounded-full">Active</span>
                            </button>
                            <Link
                                :href="route('admin.testimonials.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all shadow-lg shadow-purple-500/25"
                            >
                                <PlusIcon class="h-5 w-5" />
                                Add Testimonial
                            </Link>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-2xl">
                                    <ChatBubbleBottomCenterTextIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.total }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-2xl">
                                    <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Approved</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.approved }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-2xl">
                                    <StarIconOutline class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Pending</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.pending }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-2xl">
                                    <SparklesIcon class="h-6 w-6 text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Featured</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.featured }}</p>
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
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filter Testimonials</h3>
                            <button @click="showFilters = false" class="p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors">
                                <XMarkIcon class="h-5 w-5 text-neutral-500" />
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" />
                                    <input
                                        v-model="filters.search"
                                        type="text"
                                        placeholder="Search by name, content..."
                                        @input="debouncedSearch"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-purple-500 focus:ring-purple-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status</label>
                                <select
                                    v-model="filters.status"
                                    @change="search"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 py-2.5"
                                >
                                    <option value="">All Status</option>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                    <option value="featured">Featured</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Rating</label>
                                <select
                                    v-model="filters.rating"
                                    @change="search"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 py-2.5"
                                >
                                    <option value="">All Ratings</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-4">
                            <button
                                @click="search"
                                class="px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all"
                            >
                                Apply Filters
                            </button>
                            <button
                                @click="resetFilters"
                                class="px-4 py-2.5 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-xl hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </transition>

                <!-- Testimonials Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="!testimonials?.data || testimonials.data.length === 0"
                        icon="ChatBubbleLeftRightIcon"
                        title="No testimonials found"
                        description="Collect testimonials from happy customers to build trust and social proof."
                        :action="{
                            label: 'Add Testimonial',
                            onClick: () => router.visit(route('admin.testimonials.create')),
                        }"
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Author</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Content</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Rating</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="testimonial in testimonials.data" :key="testimonial.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div v-if="testimonial.photo" class="flex-shrink-0">
                                                <img :src="`/storage/${testimonial.photo}`" :alt="testimonial.author_name" class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-neutral-700"/>
                                            </div>
                                            <div v-else class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">{{ testimonial.author_name?.charAt(0) }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ testimonial.author_name }}</div>
                                                <div v-if="testimonial.position" class="text-sm text-neutral-500 dark:text-neutral-400">{{ testimonial.position }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-neutral-900 dark:text-white">{{ testimonial.company || '-' }}</div>
                                        <div v-if="testimonial.location" class="text-sm text-neutral-500 dark:text-neutral-400">{{ testimonial.location }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300 line-clamp-2 max-w-xs">{{ testimonial.content }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-0.5">
                                            <StarIconSolid
                                                v-for="i in 5"
                                                :key="i"
                                                class="h-4 w-4"
                                                :class="i <= testimonial.rating ? 'text-yellow-400' : 'text-neutral-300 dark:text-neutral-600'"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1.5">
                                            <span
                                                :class="testimonial.is_approved
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full w-fit"
                                            >
                                                <CheckCircleIcon v-if="testimonial.is_approved" class="h-3 w-3" />
                                                {{ testimonial.is_approved ? 'Approved' : 'Pending' }}
                                            </span>
                                            <span
                                                v-if="testimonial.is_featured"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 w-fit"
                                            >
                                                <SparklesIcon class="h-3 w-3" />
                                                Featured
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button
                                                @click="toggleApproval(testimonial)"
                                                :class="testimonial.is_approved
                                                    ? 'text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20'
                                                    : 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                                class="p-2 rounded-2xl transition-colors"
                                                :title="testimonial.is_approved ? 'Unapprove' : 'Approve'"
                                            >
                                                <CheckIcon v-if="!testimonial.is_approved" class="h-4 w-4" />
                                                <XCircleIcon v-else class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="toggleFeatured(testimonial)"
                                                :class="testimonial.is_featured
                                                    ? 'text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-700'
                                                    : 'text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20'"
                                                class="p-2 rounded-2xl transition-colors"
                                                :title="testimonial.is_featured ? 'Unfeature' : 'Feature'"
                                            >
                                                <SparklesIcon class="h-4 w-4" />
                                            </button>
                                            <Link
                                                :href="route('admin.testimonials.edit', testimonial.id)"
                                                class="p-2 text-neutral-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-2xl transition-colors"
                                                title="Edit"
                                            >
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </Link>
                                            <button
                                                @click="deleteTestimonial(testimonial)"
                                                class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-colors"
                                                title="Delete"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="testimonials?.data && testimonials.data.length > 0 && testimonials.last_page > 1" class="bg-neutral-50 dark:bg-neutral-900/50 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Showing <span class="font-semibold text-neutral-900 dark:text-white">{{ testimonials.from || 0 }}</span>
                                to <span class="font-semibold text-neutral-900 dark:text-white">{{ testimonials.to || 0 }}</span>
                                of <span class="font-semibold text-neutral-900 dark:text-white">{{ testimonials.total || 0 }}</span> testimonials
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="testimonials.prev_page_url"
                                    :href="testimonials.prev_page_url"
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
                                    v-if="testimonials.next_page_url"
                                    :href="testimonials.next_page_url"
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
