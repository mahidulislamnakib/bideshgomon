<template>
    <DataManagementTemplate
        title="CV Templates Management"
        description="Manage all CV/Resume templates in the system"
        singularName="CV Template"
        pluralName="CV Templates"
        routePrefix="admin.data.cv-templates"
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
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select
                    v-model="localFilters.category"
                    @change="() => { filters.category = localFilters.category; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Categories</option>
                    <option value="professional">Professional</option>
                    <option value="creative">Creative</option>
                    <option value="academic">Academic</option>
                    <option value="simple">Simple</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Template Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <div class="w-12 h-16 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center border border-gray-200 dark:border-gray-600">
                    <img 
                        v-if="row.thumbnail" 
                        :src="row.thumbnail" 
                        :alt="row.name"
                        class="w-full h-full object-cover"
                    />
                    <DocumentTextIcon v-else class="w-6 h-6 text-gray-400" />
                </div>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.description" class="text-sm text-neutral-500 dark:text-neutral-400 line-clamp-1">{{ row.description }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Category -->
        <template #cell-category="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full capitalize',
                row.category === 'professional' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                row.category === 'creative' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.category === 'academic' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.category || 'General' }}
            </span>
        </template>

        <!-- Custom Cell: Premium -->
        <template #cell-is_premium="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                row.is_premium 
                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.is_premium ? 'Premium' : 'Free' }}
            </span>
        </template>

        <!-- Custom Cell: Downloads -->
        <template #cell-download_count="{ row }">
            <span class="inline-flex items-center gap-1 text-sm text-neutral-700 dark:text-neutral-300">
                <ArrowDownTrayIcon class="w-4 h-4" />
                {{ row.download_count || 0 }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { DocumentTextIcon, CheckCircleIcon, XCircleIcon, ArrowDownTrayIcon, StarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    templates: Object,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    category: props.filters?.category || ''
});

const columns = [
    { key: 'name', label: 'Template', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'is_premium', label: 'Type', sortable: false },
    { key: 'download_count', label: 'Downloads', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Templates', value: props.templates?.total || 0, icon: DocumentTextIcon, variant: 'primary' },
        { label: 'Active', value: props.templates?.data?.filter(t => t.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Premium', value: props.templates?.data?.filter(t => t.is_premium).length || 0, icon: StarIcon, variant: 'warning' },
        { label: 'Downloads', value: props.templates?.data?.reduce((sum, t) => sum + (t.download_count || 0), 0) || 0, icon: ArrowDownTrayIcon, variant: 'neutral' }
    ]
};
</script>
