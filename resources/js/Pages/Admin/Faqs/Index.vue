<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    MagnifyingGlassIcon, PlusIcon, FunnelIcon, QuestionMarkCircleIcon,
    EyeIcon, PencilSquareIcon, TrashIcon, CheckCircleIcon, XCircleIcon,
    ArrowsUpDownIcon, ChartBarIcon, XMarkIcon, TagIcon, DocumentTextIcon,
    ChevronLeftIcon, ChevronRightIcon, Squares2X2Icon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    faqs: Object,
    categories: Array,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const category_id = ref(props.filters?.category_id || '');
const status = ref(props.filters?.status || '');
const showFilters = ref(false);
const loading = ref(false);

const performSearch = () => {
    router.get(route('admin.faqs.index'), {
        search: search.value,
        category_id: category_id.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    category_id.value = '';
    status.value = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return search.value || category_id.value || status.value;
});

const deleteFaq = (faqId) => {
    if (confirm('Delete this FAQ? This action cannot be undone.')) {
        router.delete(route('admin.faqs.destroy', faqId));
    }
};

const togglePublished = (faqId) => {
    router.patch(route('admin.faqs.update', faqId), {
        _method: 'PATCH',
    });
};

const getCategoryColor = (categoryName) => {
    const colors = [
        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
    ];
    const hash = categoryName?.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) || 0;
    return colors[hash % colors.length];
};

const getHelpfulColor = (faq) => {
    const total = (faq.helpful_count || 0) + (faq.not_helpful_count || 0);
    if (total === 0) return 'text-neutral-400';
    const percentage = (faq.helpful_count / total) * 100;
    if (percentage >= 70) return 'text-green-600 dark:text-green-400';
    if (percentage >= 40) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

const getHelpfulPercentage = (faq) => {
    const total = (faq.helpful_count || 0) + (faq.not_helpful_count || 0);
    if (total === 0) return 0;
    return Math.round((faq.helpful_count / total) * 100);
};

// Computed stats
const computedStats = computed(() => {
    if (props.stats) return props.stats;
    const data = props.faqs?.data || [];
    return {
        total: props.faqs?.total || data.length,
        published: data.filter(f => f.is_published).length,
        draft: data.filter(f => !f.is_published).length,
        categories: props.categories?.length || 0,
    };
});
</script>

<template>
    <Head title="Manage FAQs" />

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
                            <pattern id="faqGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#faqGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-teal-500/20 to-cyan-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <QuestionMarkCircleIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">FAQ Management</h1>
                            </div>
                            <p class="text-neutral-300">Manage frequently asked questions and answers</p>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <Link
                                :href="route('admin.faq-categories.index')"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <Squares2X2Icon class="h-5 w-5" />
                                Categories
                            </Link>
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-xl hover:bg-white/20 transition-all border border-white/20"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                Filters
                                <span v-if="hasActiveFilters" class="ml-1 px-2 py-0.5 bg-cyan-500 text-white text-xs rounded-full">Active</span>
                            </button>
                            <Link
                                :href="route('admin.faqs.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-blue-600 transition-all shadow-lg shadow-cyan-500/25"
                            >
                                <PlusIcon class="h-5 w-5" />
                                Create FAQ
                            </Link>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-xl">
                                    <QuestionMarkCircleIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total FAQs</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.total }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-xl">
                                    <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Published</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.published }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-xl">
                                    <DocumentTextIcon class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Draft</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.draft }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-xl">
                                    <TagIcon class="h-6 w-6 text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Categories</p>
                                    <p class="text-2xl font-bold text-white">{{ computedStats.categories }}</p>
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
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Filter FAQs</h3>
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
                                        v-model="search"
                                        @keyup.enter="performSearch"
                                        type="text"
                                        placeholder="Search FAQs by question or answer..."
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-cyan-500 focus:ring-cyan-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Category</label>
                                <select
                                    v-model="category_id"
                                    @change="performSearch"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-cyan-500 focus:ring-cyan-500 py-2.5"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status</label>
                                <select
                                    v-model="status"
                                    @change="performSearch"
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-cyan-500 focus:ring-cyan-500 py-2.5"
                                >
                                    <option value="">All Status</option>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-4">
                            <button
                                @click="performSearch"
                                class="px-4 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium rounded-xl hover:from-cyan-600 hover:to-blue-600 transition-all"
                            >
                                Apply Filters
                            </button>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="px-4 py-2.5 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-xl hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                            >
                                Clear All
                            </button>
                        </div>
                    </div>
                </transition>

                <!-- FAQs Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="!faqs?.data || faqs.data.length === 0"
                        icon="QuestionMarkCircleIcon"
                        title="No FAQs found"
                        description="Create helpful FAQs to answer common questions from your users."
                        :action="{
                            label: 'Create FAQ',
                            onClick: () => router.visit(route('admin.faqs.create')),
                        }"
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Question</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Stats</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                                <tr v-for="faq in faqs.data" :key="faq.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <ArrowsUpDownIcon class="h-4 w-4 text-neutral-400" />
                                            <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ faq.order }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-md">
                                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ faq.question }}</div>
                                            <div class="text-sm text-neutral-500 dark:text-neutral-400 line-clamp-2 mt-1">{{ faq.answer }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="faq.category"
                                            :class="getCategoryColor(faq.category.name)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ faq.category.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-xs text-neutral-500 dark:text-neutral-400">
                                                <EyeIcon class="h-3 w-3 mr-1" />
                                                {{ faq.view_count || 0 }} views
                                            </div>
                                            <div class="flex items-center text-xs" :class="getHelpfulColor(faq)">
                                                <ChartBarIcon class="h-3 w-3 mr-1" />
                                                {{ getHelpfulPercentage(faq) }}% helpful
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="faq.is_published 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                                : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ faq.is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Link
                                                :href="route('admin.faqs.edit', faq.id)"
                                                class="p-2 text-neutral-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-2xl transition-colors"
                                                title="Edit"
                                            >
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </Link>
                                            <button
                                                @click="togglePublished(faq.id)"
                                                :class="faq.is_published 
                                                    ? 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20' 
                                                    : 'text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'"
                                                class="p-2 rounded-2xl transition-colors"
                                                title="Toggle Published"
                                            >
                                                <CheckCircleIcon class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="deleteFaq(faq.id)"
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
                    <div v-if="faqs?.data && faqs.data.length > 0" class="bg-neutral-50 dark:bg-neutral-900/50 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Showing <span class="font-semibold text-neutral-900 dark:text-white">{{ faqs.from || 0 }}</span>
                                to <span class="font-semibold text-neutral-900 dark:text-white">{{ faqs.to || 0 }}</span>
                                of <span class="font-semibold text-neutral-900 dark:text-white">{{ faqs.total || 0 }}</span> FAQs
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="faqs.prev_page_url"
                                    :href="faqs.prev_page_url"
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
                                    v-if="faqs.next_page_url"
                                    :href="faqs.next_page_url"
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
