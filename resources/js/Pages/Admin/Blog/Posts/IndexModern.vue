<template>
    <AdminLayout>
        <Head title="Blog Posts" />

        <div class="page-container">
            <!-- Page Header -->
            <PageHeader
                title="Blog Management"
                description="Create, manage, and publish engaging blog content for your audience"
                :primaryAction="{
                    label: 'New Post',
                    onClick: () => router.visit(route('admin.blog.posts.create')),
                    icon: PlusIcon
                }"
                :secondaryActions="[
                    { label: 'Categories', onClick: () => router.visit(route('admin.blog.categories.index')), icon: FolderIcon },
                    { label: 'Tags', onClick: () => router.visit(route('admin.blog.tags.index')), icon: TagIcon }
                ]"
            />

            <!-- Statistics Grid -->
            <div class="grid-stats mb-8">
                <div class="stat-card">
                    <div class="stat-card-icon bg-blue-100">
                        <DocumentTextIcon class="h-6 w-6 text-growth-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Posts</p>
                        <p class="stat-card-value">{{ posts.total }}</p>
                        <p class="stat-card-change text-gray-600">All time</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-green-100">
                        <CheckCircleIcon class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Published</p>
                        <p class="stat-card-value">{{ publishedCount }}</p>
                        <p class="stat-card-change text-green-600">Live now</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-yellow-100">
                        <DocumentDuplicateIcon class="h-6 w-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Drafts</p>
                        <p class="stat-card-value">{{ draftCount }}</p>
                        <p class="stat-card-change text-yellow-600">Unpublished</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <EyeIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Views</p>
                        <p class="stat-card-value">{{ formatNumber(totalViews) }}</p>
                        <p class="stat-card-change text-purple-600">All posts</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="card-base mb-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <FormInput
                            v-model="searchQuery"
                            placeholder="Search posts by title or content..."
                            :icon="MagnifyingGlassIcon"
                            class="flex-1"
                            @input="handleSearch"
                            @enter="applyFilters"
                        />
                        <button
                            @click="showFilters = !showFilters"
                            class="btn-secondary flex items-center justify-center gap-2"
                        >
                            <FunnelIcon class="h-5 w-5" />
                            Filters
                            <span v-if="hasActiveFilters" class="ml-1 px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-semibold">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Filters Panel -->
                    <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="statusFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white" @change="applyFilters">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select v-model="categoryFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white" @change="applyFilters">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                            <select v-model="authorFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white" @change="applyFilters">
                                <option value="">All Authors</option>
                                <option v-for="author in authors" :key="author.id" :value="author.id">{{ author.name }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button @click="clearFilters" class="btn-secondary w-full">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Info -->
            <div v-if="hasActiveFilters" class="mb-4 text-sm text-gray-600">
                Showing {{ posts.from }} to {{ posts.to }} of {{ posts.total }} results
            </div>

            <!-- Posts Table -->
            <div class="card-base overflow-hidden">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="w-1/3">Post</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Stats</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="posts.data.length > 0">
                        <tr v-for="post in posts.data" :key="post.id">
                            <td>
                                <div class="flex items-start gap-3">
                                    <!-- Featured Image -->
                                    <img
                                        v-if="post.featured_image"
                                        :src="post.featured_image"
                                        :alt="post.title"
                                        class="h-16 w-16 rounded-lg object-cover flex-shrink-0 shadow-sm"
                                    />
                                    <div v-else class="h-16 w-16 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center flex-shrink-0">
                                        <PhotoIcon class="h-8 w-8 text-gray-400" />
                                    </div>
                                    
                                    <!-- Post Info -->
                                    <div class="flex-1 min-w-0">
                                        <Link
                                            :href="route('admin.blog.posts.edit', post.id)"
                                            class="font-semibold text-gray-900 hover:text-red-600 line-clamp-2"
                                        >
                                            {{ post.title }}
                                        </Link>
                                        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                            <UserCircleIcon class="h-4 w-4" />
                                            <span>{{ post.author?.name || 'Unknown' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <StatusBadge
                                    v-if="post.category"
                                    status="info"
                                    :customLabel="post.category.name"
                                    size="sm"
                                />
                                <span v-else class="text-gray-400 text-sm">Uncategorized</span>
                            </td>
                            <td>
                                <StatusBadge
                                    :status="post.status"
                                    size="sm"
                                />
                            </td>
                            <td>
                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-1 text-gray-600">
                                        <EyeIcon class="h-4 w-4" />
                                        <span class="font-medium">{{ formatNumber(post.views_count || 0) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-red-600">
                                        <HeartIcon class="h-4 w-4" />
                                        <span class="font-medium">{{ formatNumber(post.likes_count || 0) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-growth-600">
                                        <ChatBubbleLeftIcon class="h-4 w-4" />
                                        <span class="font-medium">{{ formatNumber(post.comments_count || 0) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900">{{ formatDate(post.published_at || post.created_at) }}</div>
                                <div v-if="post.published_at" class="text-xs text-gray-500 mt-1">
                                    Published
                                </div>
                                <div v-else class="text-xs text-yellow-600 mt-1">
                                    Draft
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('blog.show', post.slug)"
                                        target="_blank"
                                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                        title="View Post"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <Link
                                        :href="route('admin.blog.posts.edit', post.id)"
                                        class="p-2 text-growth-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="duplicatePost(post)"
                                        class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                        title="Duplicate"
                                    >
                                        <DocumentDuplicateIcon class="h-5 w-5" />
                                    </button>
                                    <button
                                        @click="deletePost(post)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <EmptyState
                    v-if="posts.data.length === 0"
                    icon="DocumentTextIcon"
                    :title="hasActiveFilters ? 'No posts match your filters' : 'No blog posts yet'"
                    :description="hasActiveFilters ? 'Try adjusting your search or filter criteria' : 'Start creating engaging content for your audience'"
                    :action="hasActiveFilters ? null : { label: 'Create First Post', onClick: () => router.visit(route('admin.blog.posts.create')) }"
                />

                <!-- Pagination -->
                <div v-if="posts.data.length > 0" class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span class="font-semibold">{{ posts.from }}</span> to <span class="font-semibold">{{ posts.to }}</span> of <span class="font-semibold">{{ posts.total }}</span> posts
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in posts.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-red-600 text-white shadow-sm'
                                        : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    DocumentDuplicateIcon,
    EyeIcon,
    HeartIcon,
    ChatBubbleLeftIcon,
    PencilIcon,
    TrashIcon,
    PhotoIcon,
    UserCircleIcon,
    FolderIcon,
    TagIcon
} from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    posts: Object,
    categories: Array,
    authors: {
        type: Array,
        default: () => []
    },
    filters: Object,
});

const { formatDate } = useBangladeshFormat();

// State
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const categoryFilter = ref(props.filters.category || '');
const authorFilter = ref(props.filters.author || '');
const showFilters = ref(false);

// Computed Stats
const publishedCount = computed(() => {
    return props.posts.data.filter(post => post.status === 'published').length;
});

const draftCount = computed(() => {
    return props.posts.data.filter(post => post.status === 'draft').length;
});

const totalViews = computed(() => {
    return props.posts.data.reduce((sum, post) => sum + (post.views_count || 0), 0);
});

const hasActiveFilters = computed(() => {
    return !!(searchQuery.value || statusFilter.value || categoryFilter.value || authorFilter.value);
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (searchQuery.value) count++;
    if (statusFilter.value) count++;
    if (categoryFilter.value) count++;
    if (authorFilter.value) count++;
    return count;
});

// Search with debounce
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

// Apply filters
const applyFilters = () => {
    router.get(route('admin.blog.posts.index'), {
        search: searchQuery.value,
        status: statusFilter.value,
        category: categoryFilter.value,
        author: authorFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Clear filters
const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    categoryFilter.value = '';
    authorFilter.value = '';
    applyFilters();
};

// Format numbers (1000 -> 1k)
const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num;
};

// Delete post
const deletePost = (post) => {
    if (confirm(`Are you sure you want to delete "${post.title}"? This action cannot be undone.`)) {
        router.delete(route('admin.blog.posts.destroy', post.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by flash message
            }
        });
    }
};

// Duplicate post
const duplicatePost = (post) => {
    if (confirm(`Create a copy of "${post.title}"?`)) {
        router.post(route('admin.blog.posts.duplicate', post.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by flash message
            }
        });
    }
};
</script>
