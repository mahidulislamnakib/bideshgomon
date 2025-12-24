<template>
    <DataManagementTemplate
        title="Degrees Management"
        description="Manage all academic degrees in the system"
        singularName="Degree"
        pluralName="Degrees"
        routePrefix="admin.data.degrees"
        :items="degrees"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Filters -->
        <template #custom-filters="{ filters, applyFilters }">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Level</label>
                <select
                    v-model="localFilters.level"
                    @change="() => { filters.level = localFilters.level; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Levels</option>
                    <option value="doctorate">Doctorate</option>
                    <option value="masters">Masters</option>
                    <option value="bachelors">Bachelors</option>
                    <option value="diploma">Diploma</option>
                    <option value="certificate">Certificate</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Degree Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <AcademicCapIcon class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.abbreviation" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.abbreviation }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Level -->
        <template #cell-level="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.level === 'doctorate' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.level === 'masters' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.level === 'bachelors' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.level || 'Unknown' }}
            </span>
        </template>

        <!-- Custom Cell: Duration -->
        <template #cell-duration_years="{ row }">
            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                {{ row.duration_years ? `${row.duration_years} year${row.duration_years > 1 ? 's' : ''}` : '—' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { AcademicCapIcon, CheckCircleIcon, XCircleIcon, BookOpenIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    degrees: Object,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    level: props.filters?.level || ''
});

const columns = [
    { key: 'name', label: 'Degree', sortable: true },
    { key: 'abbreviation', label: 'Abbreviation', sortable: true },
    { key: 'level', label: 'Level', sortable: true },
    { key: 'duration_years', label: 'Duration', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Degrees', value: props.degrees?.total || 0, icon: AcademicCapIcon, variant: 'primary' },
        { label: 'Active', value: props.degrees?.data?.filter(d => d.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.degrees?.data?.filter(d => !d.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Doctorate', value: props.degrees?.data?.filter(d => d.level === 'doctorate').length || 0, icon: BookOpenIcon, variant: 'neutral' }
    ]
};
</script>
