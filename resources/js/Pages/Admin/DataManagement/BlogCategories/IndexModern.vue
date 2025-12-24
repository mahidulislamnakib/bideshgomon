<template>
    <DataManagementTemplate
        title="Blog Categories Management"
        description="Manage all blog categories in the system"
        singularName="Blog Category"
        pluralName="Blog Categories"
        routePrefix="admin.data.blog-categories"
        :items="categories"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Category Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span 
                    class="w-10 h-10 rounded-xl flex items-center justify-center"
                    :style="{ backgroundColor: row.color + '20' || '#6366f120' }"
                >
                    <TagIcon 
                        class="w-5 h-5"
                        :style="{ color: row.color || '#6366f1' }"
                    />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.description" class="text-sm text-neutral-500 dark:text-neutral-400 line-clamp-1">{{ row.description }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Slug -->
        <template #cell-slug="{ row }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                {{ row.slug }}
            </span>
        </template>

        <!-- Custom Cell: Posts Count -->
        <template #cell-posts_count="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                {{ row.posts_count || 0 }} posts
            </span>
        </template>

        <!-- Custom Cell: Color Preview -->
        <template #cell-color="{ row }">
            <div class="flex items-center gap-2">
                <span 
                    class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-700 shadow-sm"
                    :style="{ backgroundColor: row.color || '#6366f1' }"
                ></span>
                <span class="text-xs font-mono text-neutral-500">{{ row.color || '#6366f1' }}</span>
            </div>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { TagIcon, CheckCircleIcon, XCircleIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Category', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'posts_count', label: 'Posts', sortable: true },
    { key: 'color', label: 'Color', sortable: false },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Categories', value: props.categories?.total || 0, icon: TagIcon, variant: 'primary' },
        { label: 'Active', value: props.categories?.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.categories?.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'With Posts', value: props.categories?.data?.filter(c => c.posts_count > 0).length || 0, icon: DocumentTextIcon, variant: 'neutral' }
    ]
};
</script>
