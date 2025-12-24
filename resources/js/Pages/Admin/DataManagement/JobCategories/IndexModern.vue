<template>
    <DataManagementTemplate
        title="Job Categories Management"
        description="Manage all job categories in the system"
        singularName="Job Category"
        pluralName="Job Categories"
        routePrefix="admin.data.job-categories"
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
                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                    :style="{ backgroundColor: row.color + '20' || '#6366f120' }"
                >
                    <span 
                        class="w-4 h-4 rounded-full"
                        :style="{ backgroundColor: row.color || '#6366f1' }"
                    ></span>
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.name_bn" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.name_bn }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Slug -->
        <template #cell-slug="{ row }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                {{ row.slug }}
            </span>
        </template>

        <!-- Custom Cell: Job Count -->
        <template #cell-jobs_count="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">
                {{ row.jobs_count || 0 }} jobs
            </span>
        </template>

        <!-- Custom Cell: Sort Order -->
        <template #cell-sort_order="{ row }">
            <span class="text-sm font-mono text-neutral-600 dark:text-neutral-400">
                {{ row.sort_order || 0 }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { BriefcaseIcon, CheckCircleIcon, XCircleIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Category', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'jobs_count', label: 'Jobs', sortable: true },
    { key: 'sort_order', label: 'Order', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Categories', value: props.categories?.total || 0, icon: BriefcaseIcon, variant: 'primary' },
        { label: 'Active', value: props.categories?.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.categories?.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'With Jobs', value: props.categories?.data?.filter(c => c.jobs_count > 0).length || 0, icon: ChartBarIcon, variant: 'neutral' }
    ]
};
</script>
