<template>
    <DataManagementTemplate
        title="Currencies Management"
        description="Manage all currencies and exchange rates"
        singularName="Currency"
        pluralName="Currencies"
        routePrefix="admin.data.currencies"
        :items="currencies"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Currency Code with Symbol -->
        <template #cell-code="{ row }">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300 font-bold text-xs">
                    {{ row.symbol || row.code?.charAt(0) }}
                </span>
                <span class="font-mono font-medium text-neutral-900 dark:text-white">{{ row.code }}</span>
            </div>
        </template>

        <!-- Custom Cell: Currency Name -->
        <template #cell-name="{ row }">
            <div>
                <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                <p v-if="row.symbol" class="text-sm text-neutral-500 dark:text-neutral-400">Symbol: {{ row.symbol }}</p>
            </div>
        </template>

        <!-- Custom Cell: Exchange Rate -->
        <template #cell-exchange_rate="{ row }">
            <span class="text-sm font-mono text-neutral-700 dark:text-neutral-300">
                {{ row.exchange_rate ? parseFloat(row.exchange_rate).toFixed(4) : '—' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { CurrencyDollarIcon, CheckCircleIcon, XCircleIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    currencies: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'code', label: 'Code', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'symbol', label: 'Symbol', sortable: false },
    { key: 'exchange_rate', label: 'Exchange Rate', sortable: true },
    { key: 'decimal_places', label: 'Decimals', sortable: false },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Currencies', value: props.currencies?.total || 0, icon: CurrencyDollarIcon, variant: 'primary' },
        { label: 'Active', value: props.currencies?.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.currencies?.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'With Rates', value: props.currencies?.data?.filter(c => c.exchange_rate).length || 0, icon: ArrowTrendingUpIcon, variant: 'neutral' }
    ]
};
</script>
