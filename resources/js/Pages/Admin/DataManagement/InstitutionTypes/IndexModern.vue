<template>
    <DataManagementTemplate
        title="Institution Types Management"
        description="Manage all educational institution types"
        singularName="Institution Type"
        pluralName="Institution Types"
        routePrefix="admin.data.institution-types"
        :items="types"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Type Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                    <BuildingOfficeIcon class="w-4 h-4 text-purple-600 dark:text-purple-400" />
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

        <!-- Custom Cell: Level -->
        <template #cell-level="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.level === 'higher' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.level === 'secondary' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.level === 'primary' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.level || 'General' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { BuildingOfficeIcon, CheckCircleIcon, XCircleIcon, AcademicCapIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    types: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Institution Type', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'level', label: 'Level', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Types', value: props.types?.total || 0, icon: BuildingOfficeIcon, variant: 'primary' },
        { label: 'Active', value: props.types?.data?.filter(t => t.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.types?.data?.filter(t => !t.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Higher Education', value: props.types?.data?.filter(t => t.level === 'higher').length || 0, icon: AcademicCapIcon, variant: 'neutral' }
    ]
};
</script>
