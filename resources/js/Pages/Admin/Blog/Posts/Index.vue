<template>
    <Head title="Blog Posts" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-display-md font-bold text-gray-900">Blog Posts</h1>
                    <p class="mt-1 text-gray-600">Manage your blog posts and content</p>
                </div>
                <Link
                    :href="route('admin.blog.posts.create')"
                    class="inline-flex items-center px-4 py-2 bg-brand-red-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-red-700 transition-colors"
                >
                    <PlusIcon class="h-5 w-5 mr-2" />
                    New Post
                </Link>
            </div>

            <!-- Filters Card -->
            <BaseCard padding="lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <BaseInput
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search posts..."
                            :icon="MagnifyingGlassIcon"
                            @input="handleSearch"
                        />
                    </div>
                    <BaseSelect
                        v-model="statusFilter"
                        :options="[
                            { value: '', label: 'All Status' },
                            { value: 'draft', label: 'Draft' },
                            { value: 'published', label: 'Published' },
                            { value: 'scheduled', label: 'Scheduled' }
                        ]"
                        @change="applyFilters"
                    />
                    <BaseSelect
                        v-model="categoryFilter"
                        :options="[
                            { value: '', label: 'All Categories' },
                            ...categories.map(cat => ({ value: cat.id, label: cat.name }))
                        ]"
                        @change="applyFilters"
                    />
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="applyFilters" class="px-4 py-2 bg-brand-red-600 text-white rounded-md hover:bg-red-700 transition-colors">Search</button>
                    <button @click="clearFilters" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Reset</button>
                </div>
            </BaseCard>

            <!-- Posts Table -->
            <BaseCard padding="none">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Post
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stats
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" v-if="posts.data.length > 0">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <img
                                            v-if="post.featured_image"
                                            :src="post.featured_image"
                                            :alt="post.title"
                                            class="h-12 w-12 rounded object-cover"
                                        />
                                        <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center" v-else>
                                            <span class="text-gray-400 text-xs">No img</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ post.title }}</div>
                                            <div class="text-sm text-gray-500">By {{ post.author?.name || 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <BaseBadge variant="default" v-if="post.category">
                                        {{ post.category.name }}
                                    </BaseBadge>
                                    <span v-else class="text-gray-400 text-sm">Uncategorized</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <BaseBadge
                                        :variant="
                                            post.status === 'published' ? 'success' :
                                            post.status === 'draft' ? 'warning' :
                                            'info'
                                        "
                                        rounded
                                    >
                                        {{ post.status }}
                                    </BaseBadge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <EyeIcon class="h-4 w-4" />
                                            {{ formatNumber(post.views_count || 0) }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <HeartIcon class="h-4 w-4" />
                                            {{ formatNumber(post.likes_count || 0) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(post.published_at || post.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.blog.posts.edit', post.id)"
                                            class="text-brand-red-600 hover:text-red-900"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click="deletePost(post)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-.868 0-1.563.695-1.563 1.563 0 .868.695 1.563 1.563 1.563.868 0 1.563-.695 1.563-1.563C13.563 8.695 12.868 8 12 8zM6 20l1.5-4.5L3 12l4.5-1.5L9 6l1.5 4.5L15 12l-4.5 1.5L9 18l-3 2z" />
                                    </svg>
                                    <div class="mt-4 text-gray-500">No blog posts found</div>
                                    <Link
                                        :href="route('admin.blog.posts.create')"
                                        class="mt-4 inline-block text-brand-red-600 hover:text-red-700"
                                    >
                                        Create your first post
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="posts.data.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ posts.from }} to {{ posts.to }} of {{ posts.total }} posts
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in posts.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                    link.active
                                        ? 'bg-brand-red-600 text-white'
                                        : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </BaseCard>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import { PlusIcon, MagnifyingGlassIcon, EyeIcon, HeartIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    posts: Object,
    categories: Array,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const categoryFilter = ref(props.filters.category || '');

let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('admin.blog.posts.index'), {
        search: searchQuery.value,
        status: statusFilter.value,
        category: categoryFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deletePost = (post) => {
    if (confirm(`Are you sure you want to delete "${post.title}"?`)) {
        router.delete(route('admin.blog.posts.destroy', post.id));
    }
};

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num;
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    categoryFilter.value = '';
    applyFilters();
};
</script>
