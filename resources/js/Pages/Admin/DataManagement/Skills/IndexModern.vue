<template>
    <DataManagementTemplate
        title="Skills Management"
        description="Manage all skills in the system"
        singularName="Skill"
        pluralName="Skills"
        routePrefix="admin.data.skills"
        :items="skills"
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
                    v-model="localFilters.category_id"
                    @change="() => { filters.category_id = localFilters.category_id; applyFilters(); }"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
            </div>
        </template>

        <!-- Custom Cell: Skill Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-growth-100 dark:bg-growth-900 flex items-center justify-center">
                    <WrenchScrewdriverIcon class="w-4 h-4 text-growth-600 dark:text-growth-400" />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.name_bn" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.name_bn }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Category -->
        <template #cell-category="{ row }">
            <span v-if="row.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                {{ row.category.name }}
            </span>
            <span v-else class="text-neutral-400">—</span>
        </template>

        <!-- Custom Cell: Level -->
        <template #cell-level="{ row }">
            <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                row.level === 'advanced' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' :
                row.level === 'intermediate' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]">
                {{ row.level || 'Basic' }}
            </span>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { ref } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { WrenchScrewdriverIcon, CheckCircleIcon, XCircleIcon, TagIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    skills: Object,
    categories: Array,
    filters: Object,
    sort: Object,
});

const localFilters = ref({
    category_id: props.filters?.category_id || ''
});

const columns = [
    { key: 'name', label: 'Skill', sortable: true },
    { key: 'category', label: 'Category', sortable: false },
    { key: 'level', label: 'Level', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Skills', value: props.skills?.total || 0, icon: WrenchScrewdriverIcon, variant: 'primary' },
        { label: 'Active', value: props.skills?.data?.filter(s => s.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.skills?.data?.filter(s => !s.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Categories', value: props.categories?.length || 0, icon: TagIcon, variant: 'neutral' }
    ]
};
</script>
