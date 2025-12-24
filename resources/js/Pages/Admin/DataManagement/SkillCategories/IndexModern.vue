<template>
    <DataManagementTemplate
        title="Skill Categories Management"
        description="Manage all skill categories in the system"
        singularName="Skill Category"
        pluralName="Skill Categories"
        routePrefix="admin.data.skill-categories"
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
                    class="w-10 h-10 rounded-xl flex items-center justify-center bg-growth-100 dark:bg-growth-900"
                >
                    <WrenchScrewdriverIcon class="w-5 h-5 text-growth-600 dark:text-growth-400" />
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

        <!-- Custom Cell: Skills Count -->
        <template #cell-skills_count="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-growth-100 dark:bg-growth-900 text-growth-700 dark:text-growth-300">
                {{ row.skills_count || 0 }} skills
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
import { WrenchScrewdriverIcon, CheckCircleIcon, XCircleIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Category', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'skills_count', label: 'Skills', sortable: true },
    { key: 'sort_order', label: 'Order', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Categories', value: props.categories?.total || 0, icon: WrenchScrewdriverIcon, variant: 'primary' },
        { label: 'Active', value: props.categories?.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.categories?.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'With Skills', value: props.categories?.data?.filter(c => c.skills_count > 0).length || 0, icon: ChartBarIcon, variant: 'neutral' }
    ]
};
</script>
