<template>
    <DataManagementTemplate
        title="Document Types Management"
        description="Manage all document types in the system"
        singularName="Document Type"
        pluralName="Document Types"
        routePrefix="admin.data.document-types"
        :items="documentTypes"
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
                    <option value="identity">Identity</option>
                    <option value="financial">Financial</option>
                    <option value="educational">Educational</option>
                    <option value="employment">Employment</option>
                    <option value="travel">Travel</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Document Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span :class="[
                    'w-8 h-8 rounded-lg flex items-center justify-center',
                    row.category === 'identity' ? 'bg-blue-100 dark:bg-blue-900' :
                    row.category === 'financial' ? 'bg-green-100 dark:bg-green-900' :
                    row.category === 'educational' ? 'bg-purple-100 dark:bg-purple-900' :
                    'bg-gray-100 dark:bg-gray-700'
                ]">
                    <DocumentTextIcon :class="[
                        'w-4 h-4',
                        row.category === 'identity' ? 'text-blue-600 dark:text-blue-400' :
                        row.category === 'financial' ? 'text-green-600 dark:text-green-400' :
                        row.category === 'educational' ? 'text-purple-600 dark:text-purple-400' :
                        'text-gray-600 dark:text-gray-400'
                    ]" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.name_bn" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.name_bn }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Category -->
        <template #cell-category="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.category === 'identity' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.category === 'financial' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                row.category === 'educational' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.category === 'employment' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.category || 'General' }}
            </span>
        </template>

        <!-- Custom Cell: Required -->
        <template #cell-is_required="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                row.is_required 
                    ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.is_required ? 'Required' : 'Optional' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { DocumentTextIcon, CheckCircleIcon, XCircleIcon, FolderIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    documentTypes: Object,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    category: props.filters?.category || ''
});

const columns = [
    { key: 'name', label: 'Document Type', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'is_required', label: 'Required', sortable: false },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Types', value: props.documentTypes?.total || 0, icon: DocumentTextIcon, variant: 'primary' },
        { label: 'Active', value: props.documentTypes?.data?.filter(d => d.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Required', value: props.documentTypes?.data?.filter(d => d.is_required).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Categories', value: [...new Set(props.documentTypes?.data?.map(d => d.category) || [])].length, icon: FolderIcon, variant: 'neutral' }
    ]
};
</script>
