<template>
    <DataManagementTemplate
        title="Airports Management"
        description="Manage all airports in the system"
        singularName="Airport"
        pluralName="Airports"
        routePrefix="admin.data.airports"
        :items="airports"
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
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Type</label>
                <select
                    v-model="localFilters.type"
                    @change="() => { filters.type = localFilters.type; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Types</option>
                    <option value="international">International</option>
                    <option value="domestic">Domestic</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Airport Name -->
        <template #cell-name="{ row }">
            <div>
                <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                <p v-if="row.city" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.city }}</p>
            </div>
        </template>

        <!-- Custom Cell: IATA Code -->
        <template #cell-iata_code="{ row }">
            <span class="inline-flex items-center px-2.5 py-1 rounded bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-mono font-bold text-sm">
                {{ row.iata_code || '—' }}
            </span>
        </template>

        <!-- Custom Cell: ICAO Code -->
        <template #cell-icao_code="{ row }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ row.icao_code || '—' }}
            </span>
        </template>

        <!-- Custom Cell: Type -->
        <template #cell-type="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                row.type === 'international' 
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.type || 'Unknown' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { PaperAirplaneIcon, CheckCircleIcon, XCircleIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    airports: Object,
    countries: Array,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    country_id: props.filters?.country_id || '',
    type: props.filters?.type || ''
});

const columns = [
    { key: 'name', label: 'Airport', sortable: true },
    { key: 'iata_code', label: 'IATA', sortable: true },
    { key: 'icao_code', label: 'ICAO', sortable: true },
    { key: 'country', label: 'Country', sortable: false },
    { key: 'type', label: 'Type', sortable: false },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Airports', value: props.airports?.total || 0, icon: PaperAirplaneIcon, variant: 'primary' },
        { label: 'Active', value: props.airports?.data?.filter(a => a.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.airports?.data?.filter(a => !a.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'International', value: props.airports?.data?.filter(a => a.type === 'international').length || 0, icon: GlobeAltIcon, variant: 'neutral' }
    ]
};
</script>
