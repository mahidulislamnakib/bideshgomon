<template>
    <DataManagementTemplate
        title="Visa Types Management"
        description="Manage all visa types in the system"
        singularName="Visa Type"
        pluralName="Visa Types"
        routePrefix="admin.data.visa-types"
        :items="visaTypes"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Filters -->
        <template #custom-filters="{ filters, applyFilters }">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select
                    v-model="localFilters.category"
                    @change="() => { filters.category = localFilters.category; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Categories</option>
                    <option value="tourist">Tourist</option>
                    <option value="work">Work</option>
                    <option value="student">Student</option>
                    <option value="business">Business</option>
                    <option value="immigration">Immigration</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Visa Type Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span :class="[
                    'w-8 h-8 rounded-lg flex items-center justify-center',
                    row.category === 'work' ? 'bg-blue-100 dark:bg-blue-900' :
                    row.category === 'student' ? 'bg-purple-100 dark:bg-purple-900' :
                    row.category === 'tourist' ? 'bg-green-100 dark:bg-green-900' :
                    'bg-gray-100 dark:bg-gray-700'
                ]">
                    <DocumentIcon :class="[
                        'w-4 h-4',
                        row.category === 'work' ? 'text-blue-600 dark:text-blue-400' :
                        row.category === 'student' ? 'text-purple-600 dark:text-purple-400' :
                        row.category === 'tourist' ? 'text-green-600 dark:text-green-400' :
                        'text-gray-600 dark:text-gray-400'
                    ]" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.code" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.code }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Category -->
        <template #cell-category="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.category === 'work' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.category === 'student' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.category === 'tourist' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                row.category === 'business' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.category || 'General' }}
            </span>
        </template>

        <!-- Custom Cell: Duration -->
        <template #cell-max_duration="{ row }">
            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                {{ row.max_duration ? `${row.max_duration} days` : '—' }}
            </span>
        </template>

        <!-- Custom Cell: Processing Time -->
        <template #cell-processing_time="{ row }">
            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                {{ row.processing_time ? `${row.processing_time} days` : '—' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { DocumentIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    visaTypes: Object,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    category: props.filters?.category || ''
});

const columns = [
    { key: 'name', label: 'Visa Type', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'max_duration', label: 'Max Duration', sortable: true },
    { key: 'processing_time', label: 'Processing', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Visa Types', value: props.visaTypes?.total || 0, icon: DocumentIcon, variant: 'primary' },
        { label: 'Active', value: props.visaTypes?.data?.filter(v => v.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.visaTypes?.data?.filter(v => !v.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Work Visas', value: props.visaTypes?.data?.filter(v => v.category === 'work').length || 0, icon: ClockIcon, variant: 'neutral' }
    ]
};
</script>
