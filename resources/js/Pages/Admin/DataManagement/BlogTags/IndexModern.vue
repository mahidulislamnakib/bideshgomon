<template>
    <DataManagementTemplate
        title="Blog Tags Management"
        description="Manage all blog tags in the system"
        singularName="Blog Tag"
        pluralName="Blog Tags"
        routePrefix="admin.data.blog-tags"
        :items="tags"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Tag Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span 
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-primary-100 dark:bg-primary-900"
                >
                    <HashtagIcon class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Slug -->
        <template #cell-slug="{ row }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                #{{ row.slug }}
            </span>
        </template>

        <!-- Custom Cell: Posts Count -->
        <template #cell-posts_count="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                {{ row.posts_count || 0 }} posts
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { HashtagIcon, CheckCircleIcon, XCircleIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    tags: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Tag', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'posts_count', label: 'Posts', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Tags', value: props.tags?.total || 0, icon: HashtagIcon, variant: 'primary' },
        { label: 'Active', value: props.tags?.data?.filter(t => t.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.tags?.data?.filter(t => !t.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'With Posts', value: props.tags?.data?.filter(t => t.posts_count > 0).length || 0, icon: DocumentTextIcon, variant: 'neutral' }
    ]
};
</script>
