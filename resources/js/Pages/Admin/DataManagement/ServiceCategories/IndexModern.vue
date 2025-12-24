<template>
    <DataManagementTemplate
        title="Service Categories Management"
        description="Manage all service categories in the system"
        singularName="Service Category"
        pluralName="Service Categories"
        routePrefix="admin.data.service-categories"
        :items="categories"
        :columns="columns"
        :stats="stats"
        :initialFilters="filters"
        :showStats="true"
        :hasStatusFilter="true"
    >
        <!-- Custom Cell: Category Name -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <span 
                    class="w-10 h-10 rounded-xl flex items-center justify-center"
                    :style="{ backgroundColor: row.color + '20' || '#6366f120' }"
                >
                    <component 
                        :is="getIconComponent(row.icon)" 
                        class="w-5 h-5"
                        :style="{ color: row.color || '#6366f1' }"
                    />
                </span>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ row.name }}</p>
                    <p v-if="row.name_bn" class="text-sm text-neutral-500 dark:text-neutral-400">{{ row.name_bn }}</p>
                </div>
            </div>
        </template>

        <!-- Custom Cell: Slug -->
        <template #cell-slug="{ row }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                {{ row.slug }}
            </span>
        </template>

        <!-- Custom Cell: Services Count -->
        <template #cell-services_count="{ row }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                {{ row.services_count || 0 }} services
            </span>
        </template>

        <!-- Custom Cell: Color Preview -->
        <template #cell-color="{ row }">
            <div class="flex items-center gap-2">
                <span 
                    class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-700 shadow-sm"
                    :style="{ backgroundColor: row.color || '#6366f1' }"
                ></span>
                <span class="text-xs font-mono text-neutral-500">{{ row.color || '#6366f1' }}</span>
            </div>
        </template>
    </DataManagementTemplate>
</template>

<script setup>
import { markRaw } from 'vue';
import DataManagementTemplate from '@/Components/Admin/DataManagementTemplate.vue';
import { 
    Squares2X2Icon, 
    CheckCircleIcon, 
    XCircleIcon, 
    ChartBarIcon,
    GlobeAltIcon,
    BriefcaseIcon,
    AcademicCapIcon,
    HomeIcon,
    HeartIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Object,
    filters: Object,
    sort: Object,
});

// Map icon names to components
const iconMap = {
    'globe': GlobeAltIcon,
    'briefcase': BriefcaseIcon,
    'academic-cap': AcademicCapIcon,
    'home': HomeIcon,
    'heart': HeartIcon,
    'default': Squares2X2Icon
};

const getIconComponent = (iconName) => {
    return markRaw(iconMap[iconName] || iconMap.default);
};

const columns = [
    { key: 'name', label: 'Category', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false },
    { key: 'services_count', label: 'Services', sortable: true },
    { key: 'color', label: 'Color', sortable: false },
    { key: 'sort_order', label: 'Order', sortable: true },
    { key: 'is_active', label: 'Status', type: 'status', sortable: false }
];

const stats = {
    custom: [
        { label: 'Total Categories', value: props.categories?.total || 0, icon: Squares2X2Icon, variant: 'primary' },
        { label: 'Active', value: props.categories?.data?.filter(c => c.is_active).length || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.categories?.data?.filter(c => !c.is_active).length || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'With Services', value: props.categories?.data?.filter(c => c.services_count > 0).length || 0, icon: ChartBarIcon, variant: 'neutral' }
    ]
};
</script>
