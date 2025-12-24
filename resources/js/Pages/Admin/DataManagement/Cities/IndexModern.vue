<template>
    <DataManagementTemplate
        title="Cities Management"
        description="Manage all cities in the system"
        singularName="City"
        pluralName="Cities"
        routePrefix="admin.data.cities"
        :items="cities"
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

        <!-- Custom Cell: City Name -->
        <template #cell-name="{ row }">
            <div>
                <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                <p v-if="row.name_bn" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.name_bn }}</p>
            </div>
        </template>

        <!-- Custom Cell: Country -->
        <template #cell-country="{ row }">
            <div v-if="row.country" class="flex items-center gap-2">
                <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ row.country.name }}</span>
            </div>
            <span v-else class="text-neutral-400">—</span>
        </template>

        <!-- Custom Cell: Population -->
        <template #cell-population="{ row }">
            <span class="text-sm font-mono text-neutral-700 dark:text-neutral-300">
                {{ row.population ? new Intl.NumberFormat().format(row.population) : '—' }}
            </span>
        </template>

        <!-- Custom Cell: Timezone -->
        <template #cell-timezone="{ row }">
            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ row.timezone || '—' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { BuildingOffice2Icon, CheckCircleIcon, XCircleIcon, MapPinIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    cities: Object,
    countries: Array,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    country_id: props.filters?.country_id || ''
});

const columns = [
    { key: 'name', label: 'City', sortable: true },
    { key: 'country', label: 'Country', sortable: false },
    { key: 'state_province', label: 'State/Province', sortable: true },
    { key: 'population', label: 'Population', sortable: true },
    { key: 'timezone', label: 'Timezone', sortable: false },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Cities', value: props.cities?.total || 0, icon: BuildingOffice2Icon, variant: 'primary' },
        { label: 'Active', value: props.cities?.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.cities?.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Countries', value: props.countries?.length || 0, icon: MapPinIcon, variant: 'neutral' }
    ]
};
</script>
