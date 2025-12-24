<template>
    <DataManagementTemplate
        title="Language Tests Management"
        description="Manage language proficiency tests in the system"
        singularName="Language Test"
        pluralName="Language Tests"
        routePrefix="admin.data.language-tests"
        :items="tests"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Test Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <ClipboardDocumentCheckIcon class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.abbreviation" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.abbreviation }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Language -->
        <template #cell-language="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">
                {{ row.language || 'English' }}
            </span>
        </template>

        <!-- Custom Cell: Score Range -->
        <template #cell-score_range="{ row }">
            <span class="text-sm font-mono text-neutral-700 dark:text-neutral-300">
                {{ row.min_score || 0 }} - {{ row.max_score || 'N/A' }}
            </span>
        </template>

        <!-- Custom Cell: Validity -->
        <template #cell-validity_years="{ row }">
            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                {{ row.validity_years ? `${row.validity_years} year${row.validity_years > 1 ? 's' : ''}` : '—' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { ClipboardDocumentCheckIcon, CheckCircleIcon, XCircleIcon, LanguageIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    tests: Object,
    filters: Object,
    sort: Object,
});

const columns = [
    { key: 'name', label: 'Test Name', sortable: true },
    { key: 'abbreviation', label: 'Abbreviation', sortable: true },
    { key: 'language', label: 'Language', sortable: true },
    { key: 'score_range', label: 'Score Range', sortable: false },
    { key: 'validity_years', label: 'Validity', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Tests', value: props.tests?.total || 0, icon: ClipboardDocumentCheckIcon, variant: 'primary' },
        { label: 'Active', value: props.tests?.data?.filter(t => t.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.tests?.data?.filter(t => !t.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'English Tests', value: props.tests?.data?.filter(t => t.language === 'English').length || 0, icon: LanguageIcon, variant: 'neutral' }
    ]
};
</script>
