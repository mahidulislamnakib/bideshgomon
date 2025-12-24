<template>
    <DataManagementTemplate
        title="Countries Management"
        description="Manage all countries in the system"
        singularName="Country"
        pluralName="Countries"
        routePrefix="admin.data.countries"
        :items="countries"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Filters -->
        <template #custom-filters="{ filters, applyFilters }">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Region</label>
                <select
                    v-model="localFilters.region"
                    @change="() => { filters.region = localFilters.region; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Regions</option>
                    <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Country Name with Flag -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <CountryFlag 
                    :countryCode="row.iso_code_2"
                    size="md"
                    :useImage="true"
                />
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.name_bn" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.name_bn }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: ISO Codes -->
        <template #cell-iso_codes="{ row }">
            <span class="text-sm font-mono text-neutral-700 dark:text-neutral-300">
                {{ row.iso_code_2 }} / {{ row.iso_code_3 }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import CountryFlag from '@/Components/Rhythmic/CountryFlag.vue';
import { GlobeAltIcon, CheckCircleIcon, XCircleIcon, MapIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    countries: Object,
    regions: Array,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    region: props.filters?.region || ''
});

const columns = [
    { key: 'name', label: 'Country', sortable: true },
    { key: 'iso_codes', label: 'ISO Codes', sortable: false },
    { key: 'phone_code', label: 'Phone Code', sortable: true },
    { key: 'currency_code', label: 'Currency', sortable: true },
    { key: 'region', label: 'Region', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Countries', value: props.countries.total || 0, icon: GlobeAltIcon, variant: 'primary' },
        { label: 'Active', value: props.countries.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.countries.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Regions', value: props.regions?.length || 0, icon: MapIcon, variant: 'neutral' }
    ]
};
</script>
