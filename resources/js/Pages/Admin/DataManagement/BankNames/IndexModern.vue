<template>
    <DataManagementTemplate
        title="Bank Names Management"
        description="Manage all bank names in the system"
        singularName="Bank"
        pluralName="Banks"
        routePrefix="admin.data.bank-names"
        :items="banks"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Filters -->
        <template #custom-filters="{ filters, applyFilters }">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Country</label>
                <select
                    v-model="localFilters.country_id"
                    @change="() => { filters.country_id = localFilters.country_id; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Countries</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Bank Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <BuildingLibraryIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.short_name" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.short_name }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Swift Code -->
        <template #cell-swift_code="{ row }">
            <span v-if="row.swift_code" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ row.swift_code }}
            </span>
            <span v-else class="text-neutral-400">—</span>
        </template>

        <!-- Custom Cell: Country -->
        <template #cell-country="{ row }">
            <span v-if="row.country" class="text-sm text-neutral-700 dark:text-neutral-300">
                {{ row.country.name }}
            </span>
            <span v-else class="text-neutral-400">—</span>
        </template>

        <!-- Custom Cell: Bank Type -->
        <template #cell-type="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.type === 'central' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.type === 'commercial' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.type === 'islamic' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.type || 'General' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { BuildingLibraryIcon, CheckCircleIcon, XCircleIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    banks: Object,
    countries: Array,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    country_id: props.filters?.country_id || ''
});

const columns = [
    { key: 'name', label: 'Bank Name', sortable: true },
    { key: 'swift_code', label: 'SWIFT Code', sortable: true },
    { key: 'country', label: 'Country', sortable: false },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Banks', value: props.banks?.total || 0, icon: BuildingLibraryIcon, variant: 'primary' },
        { label: 'Active', value: props.banks?.data?.filter(b => b.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.banks?.data?.filter(b => !b.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Countries', value: props.countries?.length || 0, icon: GlobeAltIcon, variant: 'neutral' }
    ]
};
</script>
