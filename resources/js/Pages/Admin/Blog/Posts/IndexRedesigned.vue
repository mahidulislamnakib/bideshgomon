<template>
    <Head title="Blog Posts" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Blog Posts</h1>
                <p class="text-neutral-600 mt-2">Manage blog articles and content</p>
            </div>
            <div class="flex items-center gap-3">
                <ActionButton
                    label="Categories"
                    :href="route('admin.blog.categories.index')"
                    variant="secondary"
                    size="md"
                    :icon="FolderIcon"
                />
                <ActionButton
                    label="New Post"
                    :href="route('admin.blog.posts.create')"
                    variant="primary"
                    size="md"
                    :icon="PlusIcon"
                />
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                :icon="DocumentTextIcon"
                label="Total Posts"
                :value="stats.total"
                :change="stats.growth"
                :description="`${stats.thisMonth} this month`"
                variant="primary"
            />
            <StatsCard
                :icon="CheckCircleIcon"
                label="Published"
                :value="stats.published"
                :change="stats.publishedGrowth"
                variant="success"
            />
            <StatsCard
                :icon="DocumentDuplicateIcon"
                label="Drafts"
                :value="stats.drafts"
                variant="warning"
            />
            <StatsCard
                :icon="EyeIcon"
                label="Total Views"
                :value="formatNumber(stats.totalViews)"
                :description="`Avg ${stats.avgViews} per post`"
                variant="neutral"
            />
        </div>

        <!-- Blog Posts Table -->
        <TableWrapper
            title="All Posts"
            description="Manage your blog content"
            :columns="columns"
            :data="posts.data"
            :pagination="posts"
            searchable
            filterable
            selectable
            has-actions
            @search="handleSearch"
            @sort="handleSort"
            @select="handleSelect"
        >
            <!-- Filters Slot -->
            <template #filters>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Category</label>
                        <select
                            v-model="filters.category_id"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Author</label>
                        <select
                            v-model="filters.author_id"
                            @change="applyFilters"
                            class="w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                        >
                            <option value="">All Authors</option>
                            <option v-for="author in authors" :key="author.id" :value="author.id">
                                {{ author.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <ActionButton
                            label="Clear Filters"
                            variant="ghost"
                            size="md"
                            @click="clearFilters"
                            full-width
                        />
                    </div>
                </div>
            </template>

            <!-- Custom Cells -->
            <template #cell(title)="{ row }">
                <div class="flex items-center gap-3">
                    <img
                        v-if="row.featured_image"
                        :src="row.featured_image"
                        :alt="row.title"
                        class="h-12 w-12 rounded object-cover"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-neutral-900 truncate">{{ row.title }}</p>
                        <p class="text-sm text-neutral-500 truncate">{{ row.excerpt }}</p>
                    </div>
                </div>
            </template>

            <template #cell(author)="{ value }">
                <div class="flex items-center gap-2">
                    <img
                        :src="value?.avatar || '/images/default-avatar.png'"
                        :alt="value?.name"
                        class="h-6 w-6 rounded-full"
                    />
                    <span class="text-sm text-neutral-700">{{ value?.name }}</span>
                </div>
            </template>

            <template #cell(category)="{ value }">
                <span v-if="value" class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800">
                    {{ value.name }}
                </span>
                <span v-else class="text-sm text-neutral-400">Uncategorized</span>
            </template>

            <template #cell(status)="{ value }">
                <span :class="{
                    'bg-success-100 text-success-700': value === 'published',
                    'bg-neutral-100 text-neutral-700': value === 'draft',
                    'bg-blue-100 text-blue-700': value === 'scheduled'
                }" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                    {{ value }}
                </span>
            </template>

            <template #cell(views)="{ value }">
                <div class="flex items-center gap-1 text-sm text-neutral-600">
                    <EyeIcon class="h-4 w-4" />
                    <span>{{ formatNumber(value) }}</span>
                </div>
            </template>

            <template #cell(published_at)="{ value }">
                <span class="text-sm text-neutral-600">
                    {{ value ? formatDate(value) : 'Not published' }}
                </span>
            </template>

            <!-- Row Actions -->
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <a
                        v-if="row.status === 'published'"
                        :href="route('blog.show', row.slug)"
                        target="_blank"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                    >
                        Preview
                    </a>
                    <Link
                        :href="route('admin.blog.posts.edit', row.id)"
                        class="text-growth-600 hover:text-blue-700 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="row.status === 'draft'"
                        @click="publishPost(row.id)"
                        class="text-success-600 hover:text-success-700 text-sm font-medium"
                    >
                        Publish
                    </button>
                    <button
                        v-if="row.status === 'published'"
                        @click="unpublishPost(row.id)"
                        class="text-yellow-600 hover:text-yellow-700 text-sm font-medium"
                    >
                        Unpublish
                    </button>
                    <button
                        @click="deletePost(row.id)"
                        class="text-red-600 hover:text-growth-700 text-sm font-medium"
                    >
                        Delete
                    </button>
                </div>
            </template>
        </TableWrapper>
    </DashboardShell>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardShell from '@/Layouts/DashboardShell.vue';
import StatsCard from '@/Components/UI/StatsCard.vue';
import TableWrapper from '@/Components/UI/TableWrapper.vue';
import ActionButton from '@/Components/UI/ActionButton.vue';
import {
    DocumentTextIcon,
    CheckCircleIcon,
    DocumentDuplicateIcon,
    EyeIcon,
    PlusIcon,
    FolderIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    posts: Object,
    categories: Array,
    authors: Array,
    stats: Object,
    filters: Object
});

const columns = [
    { key: 'title', label: 'Title', sortable: true },
    { key: 'author', label: 'Author', sortable: false },
    { key: 'category', label: 'Category', sortable: false },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'views', label: 'Views', sortable: true },
    { key: 'published_at', label: 'Published', sortable: true }
];

const filters = ref({
    status: props.filters?.status || '',
    category_id: props.filters?.category_id || '',
    author_id: props.filters?.author_id || ''
});

const handleSearch = (query) => {
    router.get(route('admin.blog.posts.index'), {
        search: query,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSort = ({ key, order }) => {
    router.get(route('admin.blog.posts.index'), {
        sort: key,
        order: order,
        ...filters.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSelect = (selectedIds) => {
    console.log('Selected posts:', selectedIds);
};

const applyFilters = () => {
    router.get(route('admin.blog.posts.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        status: '',
        category_id: '',
        author_id: ''
    };
    router.get(route('admin.blog.posts.index'));
};

const publishPost = (id) => {
    if (confirm('Publish this post now?')) {
        router.post(route('admin.blog.posts.publish', id));
    }
};

const unpublishPost = (id) => {
    if (confirm('Move this post back to draft?')) {
        router.post(route('admin.blog.posts.unpublish', id));
    }
};

const deletePost = (id) => {
    if (confirm('Are you sure? This action cannot be undone.')) {
        router.delete(route('admin.blog.posts.destroy', id));
    }
};

const formatNumber = (num) => {
    return parseFloat(num || 0).toLocaleString('en-BD');
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-BD', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>
