<template>
    <DataManagementTemplate
        title="Email Templates Management"
        description="Manage all email templates in the system"
        singularName="Email Template"
        pluralName="Email Templates"
        routePrefix="admin.data.email-templates"
        :items="templates"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Filters -->
        <template #custom-filters="{ filters, applyFilters }">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Type</label>
                <select
                    v-model="localFilters.type"
                    @change="() => { filters.type = localFilters.type; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Types</option>
                    <option value="transactional">Transactional</option>
                    <option value="marketing">Marketing</option>
                    <option value="notification">Notification</option>
                    <option value="system">System</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Template Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span :class="[
                    'w-10 h-10 rounded-xl flex items-center justify-center',
                    row.type === 'transactional' ? 'bg-blue-100 dark:bg-blue-900' :
                    row.type === 'marketing' ? 'bg-green-100 dark:bg-green-900' :
                    row.type === 'notification' ? 'bg-amber-100 dark:bg-amber-900' :
                    'bg-gray-100 dark:bg-gray-700'
                ]">
                    <EnvelopeIcon :class="[
                        'w-5 h-5',
                        row.type === 'transactional' ? 'text-blue-600 dark:text-blue-400' :
                        row.type === 'marketing' ? 'text-green-600 dark:text-green-400' :
                        row.type === 'notification' ? 'text-amber-600 dark:text-amber-400' :
                        'text-gray-600 dark:text-gray-400'
                    ]" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.subject" class="text-sm text-neutral-500 dark:text-neutral-400 line-clamp-1">{{ row.subject }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Slug -->
        <template #cell-slug="{ row }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                {{ row.slug }}
            </span>
        </template>

        <!-- Custom Cell: Type -->
        <template #cell-type="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.type === 'transactional' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.type === 'marketing' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                row.type === 'notification' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.type || 'General' }}
            </span>
        </template>

        <!-- Custom Cell: Last Updated -->
        <template #cell-updated_at="{ row }">
            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                {{ formatDate(row.updated_at) }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { EnvelopeIcon, CheckCircleIcon, XCircleIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    templates: Object,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    type: props.filters?.type || ''
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-BD', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const columns = [
    { key: 'name', label: 'Template', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'updated_at', label: 'Updated', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Templates', value: props.templates?.total || 0, icon: EnvelopeIcon, variant: 'primary' },
        { label: 'Active', value: props.templates?.data?.filter(t => t.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.templates?.data?.filter(t => !t.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Transactional', value: props.templates?.data?.filter(t => t.type === 'transactional').length || 0, icon: PaperAirplaneIcon, variant: 'neutral' }
    ]
};
</script>
