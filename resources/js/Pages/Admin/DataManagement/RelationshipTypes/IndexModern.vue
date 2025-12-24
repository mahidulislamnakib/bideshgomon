<template>
    <DataManagementTemplate
        title="Relationship Types Management"
        description="Manage family relationship types in the system"
        singularName="Relationship Type"
        pluralName="Relationship Types"
        routePrefix="admin.data.relationship-types"
        :items="types"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Relationship Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900 flex items-center justify-center">
                    <UserGroupIcon class="w-4 h-4 text-pink-600 dark:text-pink-400" />
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

        <!-- Custom Cell: Gender -->
        <template #cell-gender="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                row.gender === 'male' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.gender === 'female' ? 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.gender || 'Any' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { UserGroupIcon, CheckCircleIcon, XCircleIcon, HeartIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    types: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Relationship', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'gender', label: 'Gender', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Types', value: props.types?.total || 0, icon: UserGroupIcon, variant: 'primary' },
        { label: 'Active', value: props.types?.data?.filter(t => t.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.types?.data?.filter(t => !t.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Family', value: props.types?.data?.length || 0, icon: HeartIcon, variant: 'neutral' }
    ]
};
</script>
