<template>
    <DataManagementTemplate
        title="Languages Management"
        description="Manage all languages in the system"
        singularName="Language"
        pluralName="Languages"
        routePrefix="admin.data.languages"
        :items="languages"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Language Name -->
        <template #cell-name="{ row }">
            <div>
                <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                <p v-if="row.native_name" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.native_name }}</p>
            </div>
        </template>

        <!-- Custom Cell: Language Code -->
        <template #cell-code="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-mono">
                {{ row.code }}
            </span>
        </template>

        <!-- Custom Cell: Direction -->
        <template #cell-direction="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                row.direction === 'rtl' 
                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'
                    : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
            ]">
                {{ row.direction?.toUpperCase() || 'LTR' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { LanguageIcon, CheckCircleIcon, XCircleIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    languages: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Language', sortable: true },
    { key: 'code', label: 'Code', sortable: true },
    { key: 'native_name', label: 'Native Name', sortable: true },
    { key: 'direction', label: 'Direction', sortable: false },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Languages', value: props.languages?.total || 0, icon: LanguageIcon, variant: 'primary' },
        { label: 'Active', value: props.languages?.data?.filter(l => l.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.languages?.data?.filter(l => !l.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'RTL Languages', value: props.languages?.data?.filter(l => l.direction === 'rtl').length || 0, icon: GlobeAltIcon, variant: 'neutral' }
    ]
};
</script>
