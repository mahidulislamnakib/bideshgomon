<!-- Modern Redesigned FAQs Management Page -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import { 
    MagnifyingGlassIcon, PlusIcon, FunnelIcon, QuestionMarkCircleIcon,
    EyeIcon, PencilIcon, TrashIcon, RectangleStackIcon,
    ChartBarIcon, HandThumbUpIcon
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

const getCategoryColor = (categoryName) => {
    const colors = [
        'bg-blue-100 text-blue-700',
        'bg-purple-100 text-purple-700',
        'bg-green-100 text-green-700',
        'bg-orange-100 text-orange-700',
        'bg-indigo-100 text-indigo-700',
        'bg-pink-100 text-pink-700',
    ];
    const hash = categoryName?.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) || 0;
    return colors[hash % colors.length];
};

const getHelpfulPercentage = (faq) => {
    const total = (faq.helpful_count || 0) + (faq.not_helpful_count || 0);
    if (total === 0) return 0;
    return Math.round((faq.helpful_count / total) * 100);
};

const getHelpfulColor = (percentage) => {
    if (percentage >= 70) return 'text-green-600';
    if (percentage >= 40) return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="FAQ Management - BideshGomon Admin" />

    <AdminLayout>
        <!-- PAGE HEADER -->
        <PageHeader
            title="FAQ Management"
            description="Manage frequently asked questions, answers, and categories"
            :primaryAction="{
                label: 'Create FAQ',
                onClick: () => router.visit(route('admin.faqs.create')),
                icon: PlusIcon
            }"
            :secondaryActions="[
                { label: 'Manage Categories', onClick: () => router.visit(route('admin.faq-categories.index')), icon: RectangleStackIcon }
            ]"
        />

        <!-- MAIN CONTENT -->
        <div class="page-container space-y-6">
            
            <!-- STATS GRID (if stats available) -->
            <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <QuestionMarkCircleIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total FAQs</p>
                        <p class="stat-card-value">{{ stats.total || faqs.total }}</p>
                        <p class="stat-card-change text-gray-500">
                            All questions
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-emerald-100">
                        <ChartBarIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Published</p>
                        <p class="stat-card-value">{{ stats?.published || '—' }}</p>
                        <p class="stat-card-change text-emerald-600">
                            Live on site
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-amber-100">
                        <PencilIcon class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Draft</p>
                        <p class="stat-card-value">{{ stats?.draft || '—' }}</p>
                        <p class="stat-card-change text-amber-600">
                            Pending review
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <RectangleStackIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Categories</p>
                        <p class="stat-card-value">{{ categories?.length || 0 }}</p>
                        <p class="stat-card-change text-gray-500">
                            Active categories
                        </p>
                    </div>
                </div>
            </div>

            <!-- SEARCH & FILTERS CARD -->
            <div class="card-base p-6">
                <div class="space-y-4">
                    <!-- Search Row -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <FormInput
                                v-model="search"
                                placeholder="Search FAQs by question or answer..."
                                :icon="MagnifyingGlassIcon"
                                @enter="performSearch"
                            />
                        </div>
                        <select v-model="category_id" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white" @change="performSearch">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <select v-model="status" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white" @change="performSearch">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <!-- Action Row -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3">
                            <button @click="performSearch" class="btn-primary">
                                <MagnifyingGlassIcon class="h-5 w-5 mr-2" />
                                Search
                            </button>
                            <button @click="clearFilters" v-if="hasActiveFilters" class="btn-secondary">
                                Reset
                            </button>
                        </div>
                        <span v-if="hasActiveFilters" class="text-sm text-gray-500">
                            {{ faqs.total }} results found
                        </span>
                    </div>
                </div>
            </div>

            <!-- FAQS LIST CARD -->
            <div class="card-base overflow-hidden">
                <div v-if="faqs.data.length > 0">
                    <!-- List View -->
                    <div class="divide-y divide-gray-200">
                        <div 
                            v-for="faq in faqs.data" 
                            :key="faq.id"
                            class="p-6 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Question -->
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ faq.question }}
                                    </h3>
                                    
                                    <!-- Answer Preview -->
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                        {{ faq.answer }}
                                    </p>

                                    <!-- Meta Information -->
                                    <div class="flex flex-wrap items-center gap-3">
                                        <!-- Category Badge -->
                                        <span 
                                            v-if="faq.category"
                                            :class="getCategoryColor(faq.category.name)"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ faq.category.name }}
                                        </span>

                                        <!-- Status Badge -->
                                        <StatusBadge :status="faq.is_published ? 'published' : 'draft'" size="sm" />

                                        <!-- Order -->
                                        <span class="text-xs text-gray-500">
                                            Order: {{ faq.display_order }}
                                        </span>

                                        <!-- Helpful Stats -->
                                        <div v-if="faq.helpful_count > 0 || faq.not_helpful_count > 0" class="flex items-center gap-1">
                                            <HandThumbUpIcon class="h-4 w-4 text-gray-400" />
                                            <span 
                                                :class="getHelpfulColor(getHelpfulPercentage(faq))"
                                                class="text-xs font-medium"
                                            >
                                                {{ getHelpfulPercentage(faq) }}% helpful
                                            </span>
                                            <span class="text-xs text-gray-400">
                                                ({{ faq.helpful_count + faq.not_helpful_count }} votes)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <Link
                                        :href="route('admin.faqs.show', faq.id)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="View"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <Link
                                        :href="route('admin.faqs.edit', faq.id)"
                                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="deleteFaq(faq.id)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-else
                    icon="QuestionMarkCircleIcon"
                    title="No FAQs found"
                    description="Get started by creating your first frequently asked question"
                    :action="{
                        label: 'Create FAQ',
                        onClick: () => router.visit(route('admin.faqs.create')),
                        icon: PlusIcon
                    }"
                    :secondaryAction="{
                        label: 'Clear Filters',
                        onClick: clearFilters
                    }"
                />

                <!-- Pagination -->
                <div v-if="faqs.data.length > 0 && faqs.links" class="border-t border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Showing {{ faqs.from }} to {{ faqs.to }} of {{ faqs.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in faqs.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm rounded-lg transition-colors',
                                    link.active 
                                        ? 'bg-red-600 text-white' 
                                        : link.url 
                                        ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
