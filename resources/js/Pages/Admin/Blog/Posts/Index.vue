<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue';
import SortableHeader from '@/Components/ui/SortableHeader.vue';
import { 
    PlusIcon, MagnifyingGlassIcon, EyeIcon, PencilIcon, TrashIcon,
    DocumentTextIcon, ArrowPathIcon
} from '@heroicons/vue/24/outline';
import { HeartIcon, StarIcon } from '@heroicons/vue/24/solid';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    posts: Object,
    categories: Array,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const categoryFilter = ref(props.filters?.category || '');

// Sorting state
const sortColumn = ref(props.filters?.sort || '');
const sortDirection = ref(props.filters?.direction || 'asc');

const sortBy = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    applyFilters();
};

let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
};

const applyFilters = () => {
    router.get(route('admin.blog.posts.index'), {
        search: searchQuery.value,
        status: statusFilter.value,
        category: categoryFilter.value,
        sort: sortColumn.value || undefined,
        direction: sortColumn.value ? sortDirection.value : undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    categoryFilter.value = '';
    sortColumn.value = '';
    sortDirection.value = 'asc';
    applyFilters();
};

const deletePost = (post) => {
    if (confirm(`Delete "${post.title}"?`)) {
        router.delete(route('admin.blog.posts.destroy', post.id));
    }
};

const formatNumber = (num) => num >= 1000 ? (num / 1000).toFixed(1) + 'k' : num;

const getStatusColor = (status) => ({
    published: 'bg-green-100 text-green-700',
    draft: 'bg-yellow-100 text-yellow-700',
    scheduled: 'bg-blue-100 text-blue-700'
}[status] || 'bg-gray-100 text-gray-700');

const stats = [
    { label: 'Total', value: props.posts.total, color: 'bg-blue-500' },
    { label: 'Published', value: props.posts.data.filter(p => p.status === 'published').length, color: 'bg-green-500' },
    { label: 'Draft', value: props.posts.data.filter(p => p.status === 'draft').length, color: 'bg-yellow-500' },
];
</script>

<template>
    <Head title="Blog Posts" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Blog Posts', href: null }
            ]" />
        </template>

        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Hero Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                            <DocumentTextIcon class="h-6 w-6" />
                            Blog Posts
                        </h1>
                        <p class="text-sm text-white/80 mt-0.5">Manage your blog content</p>
                    </div>
                    <Link :href="route('admin.blog.posts.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white text-purple-600 rounded-lg font-medium text-sm hover:bg-purple-50 transition-colors">
                        <PlusIcon class="h-4 w-4" />
                        New Post
                    </Link>
                </div>

                <!-- Stats -->
                <div class="max-w-7xl mx-auto mt-4 grid grid-cols-3 gap-3">
                    <div v-for="stat in stats" :key="stat.label" 
                        class="bg-white/10 backdrop-blur rounded-lg px-4 py-3 text-center">
                        <div class="text-xl font-bold text-white">{{ stat.value }}</div>
                        <div class="text-xs text-white/70">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Search posts..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-purple-500" />
                    </div>
                    <select v-model="statusFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-purple-500">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                    <select v-model="categoryFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-purple-500">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <button v-if="searchQuery || statusFilter || categoryFilter" @click="clearFilters"
                        class="p-2 text-gray-400 hover:text-gray-600">
                        <ArrowPathIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Posts List -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
                <div v-if="posts.data.length === 0" class="bg-white rounded-lg border p-12 text-center">
                    <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500">No blog posts found</p>
                    <Link :href="route('admin.blog.posts.create')" class="mt-3 inline-block text-sm text-purple-600 font-medium">
                        Create your first post →
                    </Link>
                </div>

                <div v-else class="bg-white rounded-lg border overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <SortableHeader 
                                    column="title" 
                                    label="Post" 
                                    :sort-column="sortColumn" 
                                    :sort-direction="sortDirection" 
                                    @sort="sortBy" 
                                />
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <SortableHeader 
                                    column="views_count" 
                                    label="Stats" 
                                    :sort-column="sortColumn" 
                                    :sort-direction="sortDirection" 
                                    @sort="sortBy" 
                                />
                                <SortableHeader 
                                    column="created_at" 
                                    label="Date" 
                                    :sort-column="sortColumn" 
                                    :sort-direction="sortDirection" 
                                    @sort="sortBy" 
                                />
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded bg-gray-100 overflow-hidden flex-shrink-0">
                                            <img v-if="post.featured_image" :src="post.featured_image" class="h-full w-full object-cover" />
                                            <div v-else class="h-full w-full flex items-center justify-center">
                                                <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-sm text-gray-900 truncate max-w-[200px]">{{ post.title }}</div>
                                            <div class="text-xs text-gray-500">{{ post.author?.name || 'Unknown' }}</div>
                                        </div>
                                        <StarIcon v-if="post.is_featured" class="h-4 w-4 text-yellow-500 flex-shrink-0" />
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="post.category" class="text-xs text-gray-600">{{ post.category.name }}</span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="getStatusColor(post.status)" class="px-2 py-0.5 rounded text-xs font-medium">
                                        {{ post.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <EyeIcon class="h-3.5 w-3.5" />
                                            {{ formatNumber(post.views_count || 0) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <HeartIcon class="h-3.5 w-3.5 text-red-400" />
                                            {{ formatNumber(post.likes_count || 0) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ formatDate(post.published_at || post.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('admin.blog.posts.edit', post.id)" 
                                            class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded">
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                        <button @click="deletePost(post)" 
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded">
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="posts.last_page > 1" class="px-4 py-3 border-t bg-gray-50 flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ posts.from }}-{{ posts.to }} of {{ posts.total }}</span>
                        <div class="flex gap-1">
                            <Link v-for="link in posts.links" :key="link.label" :href="link.url || '#'"
                                :class="['px-2 py-1 text-xs rounded', link.active ? 'bg-purple-600 text-white' : link.url ? 'hover:bg-gray-100' : 'text-gray-300']"
                                v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
