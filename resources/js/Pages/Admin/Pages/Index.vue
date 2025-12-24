<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';

const props = defineProps({
    pages: Object,
    filters: Object,
    pageTypes: Object,
});

const loading = ref(false);

const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('admin.pages.index'), {
        search: searchQuery.value,
        type: selectedType.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedType.value = '';
    selectedStatus.value = '';
    applyFilters();
};

const togglePublished = (page) => {
    if (confirm(`Are you sure you want to ${page.is_published ? 'unpublish' : 'publish'} this page?`)) {
        router.post(route('admin.pages.toggle-published', page.id));
    }
};

const duplicatePage = (page) => {
    if (confirm('Duplicate this page?')) {
        router.post(route('admin.pages.duplicate', page.id));
    }
};

const deletePage = (page) => {
    if (confirm('Are you sure you want to delete this page? This action cannot be undone.')) {
        router.delete(route('admin.pages.destroy', page.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="CMS Pages" />

    <AdminLayout>
        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />

        <!-- Main Content -->
        <div v-else class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-display-md font-bold text-gray-900 dark:text-white">CMS Pages</h1>
                    <p class="mt-1 text-gray-600">Manage website pages, terms, privacy policy, and more</p>
                </div>
                <Link
                    :href="route('admin.pages.create')"
                    class="inline-flex items-center px-4 py-2 bg-growth-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-growth-700 transition-colors"
                >
                    ➕ Create Page
                </Link>
            </div>

            <!-- Filters Card -->
            <BaseCard padding="lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <BaseInput
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by title, content, or slug..."
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <BaseSelect
                        v-model="selectedType"
                        :options="[
                            { value: '', label: 'All Types' },
                            ...Object.entries(pageTypes || {}).map(([value, label]) => ({ value, label }))
                        ]"
                        @change="applyFilters"
                    />
                    <BaseSelect
                        v-model="selectedStatus"
                        :options="[
                            { value: '', label: 'All Pages' },
                            { value: 'published', label: 'Published' },
                            { value: 'draft', label: 'Draft' }
                        ]"
                        @change="applyFilters"
                    />
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="applyFilters" class="px-4 py-2 bg-growth-600 text-white rounded-xl hover:bg-growth-700">Search</button>
                    <button @click="clearFilters" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">Reset</button>
                </div>
            </BaseCard>

            <!-- Pages List -->
            <BaseCard padding="none">
                    <EmptyState
                        v-if="pages.data.length === 0"
                        icon="DocumentIcon"
                        title="No pages found"
                        description="Create custom pages to add content to your website."
                        :action="{
                            label: 'Create Page',
                            onClick: () => router.visit(route('admin.pages.create')),
                        }"
                    />

                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="page in pages.data" :key="page.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ page.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                /page/{{ page.slug }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-growth-600">
                                        {{ pageTypes[page.page_type] || page.page_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-semibold rounded-full',
                                        page.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ page.is_published ? '? Published' : '? Draft' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(page.updated_at) }}
                                    <span v-if="page.updated_by" class="block text-xs text-gray-400">
                                        by {{ page.updated_by.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('page.show', page.slug)"
                                            target="_blank"
                                            class="text-gray-600 hover:text-gray-900 dark:text-white"
                                            title="View"
                                        >
                                            👁️
                                        </Link>
                                        <Link
                                            :href="route('admin.pages.edit', page.id)"
                                            class="text-growth-600 hover:text-red-900"
                                            title="Edit"
                                        >
                                            ✏️
                                        </Link>
                                        <button
                                            @click="togglePublished(page)"
                                            class="text-yellow-600 hover:text-yellow-900"
                                            :title="page.is_published ? 'Unpublish' : 'Publish'"
                                        >
                                            {{ page.is_published ? '📤' : '📥' }}
                                        </button>
                                        <button
                                            @click="duplicatePage(page)"
                                            class="text-green-600 hover:text-green-900"
                                            title="Duplicate"
                                        >
                                            📋
                                        </button>
                                        <button
                                            @click="deletePage(page)"
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="pages.data.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ pages.from }} to {{ pages.to }} of {{ pages.total }} pages
                            </div>
                            <div v-if="pages.links && pages.links.length" class="flex gap-2">
                                <Link
                                    v-for="link in pages.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-xl transition-colors',
                                        link.active
                                            ? 'bg-growth-600 text-white'
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
