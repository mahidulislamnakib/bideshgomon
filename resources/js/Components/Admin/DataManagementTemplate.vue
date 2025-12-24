<template>
    <DashboardShell>
        <Head :title="title" />

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">{{ title }}</h1>
                <p class="text-neutral-600 dark:text-neutral-400 mt-1">{{ description }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <ActionButton
                    v-if="showBulkUpload && bulkUploadRoute"
                    label="Bulk Upload"
                    :href="route(bulkUploadRoute)"
                    variant="secondary"
                    size="md"
                    :icon="CloudArrowUpIcon"
                />
                <ActionButton
                    v-if="createRoute"
                    :label="`Add ${singularName}`"
                    :href="route(createRoute)"
                    variant="primary"
                    size="md"
                    :icon="PlusIcon"
                />
            </div>
        </div>

        <!-- Stats Row (Optional) -->
        <div v-if="stats && showStats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatsCard
                v-for="(stat, index) in computedStats"
                :key="index"
                :icon="stat.icon"
                :label="stat.label"
                :value="stat.value"
                :change="stat.change"
                :description="stat.description"
                :variant="stat.variant || 'primary'"
            />
        </div>

        <!-- Table with Filters -->
        <TableWrapper
            :title="`All ${pluralName}`"
            :description="`View and manage ${pluralName.toLowerCase()}`"
            :columns="columns"
            :data="items.data"
            :pagination="items"
            searchable
            filterable
            :selectable="selectable"
            has-actions
            @search="handleSearch"
            @sort="handleSort"
            @select="handleSelect"
        >
            <!-- Filters Slot -->
            <template #filters>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div v-if="hasStatusFilter">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select
                            v-model="filters.is_active"
                            @change="applyFilters"
                            class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Custom Filter Slots -->
                    <slot name="custom-filters" :filters="filters" :applyFilters="applyFilters"></slot>

                    <!-- Clear Filters -->
                    <div class="flex items-end">
                        <ActionButton
                            label="Clear Filters"
                            variant="ghost"
                            size="md"
                            @click="clearFilters"
                            full-width
                        />
                    </div>
                </div>
            </template>

            <!-- Custom Cell Slots -->
            <template v-for="column in columns" :key="column.key" #[`cell(${column.key})`]="{ value, row }">
                <slot :name="`cell-${column.key}`" :value="value" :row="row">
                    <!-- Default rendering based on column type -->
                    <template v-if="column.type === 'badge'">
                        <span :class="getBadgeClass(value, column.badgeVariant)">
                            {{ value }}
                        </span>
                    </template>
                    <template v-else-if="column.type === 'status'">
                        <button
                            @click="toggleStatus(row)"
                            :class="[
                                'px-3 py-1 rounded-full text-xs font-semibold transition-colors',
                                row.is_active 
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                            ]"
                        >
                            {{ row.is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </template>
                    <template v-else-if="column.type === 'date'">
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ formatDate(value) }}</span>
                    </template>
                    <template v-else>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ value ?? '—' }}</span>
                    </template>
                </slot>
            </template>

            <!-- Row Actions -->
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        v-if="showRoute"
                        :href="route(showRoute, row.id)"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                    >
                        View
                    </Link>
                    <Link
                        v-if="editRoute"
                        :href="route(editRoute, row.id)"
                        class="text-growth-600 hover:text-growth-700 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="toggleStatusRoute"
                        @click="toggleStatus(row)"
                        class="text-amber-600 hover:text-amber-700 text-sm font-medium"
                    >
                        {{ row.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                        v-if="destroyRoute"
                        @click="deleteItem(row)"
                        class="text-red-600 hover:text-red-700 text-sm font-medium"
                    >
                        Delete
                    </button>
                    <slot name="extra-actions" :row="row"></slot>
                </div>
            </template>

            <!-- Empty State -->
            <template #empty>
                <EmptyState
                    :icon="FolderOpenIcon"
                    :title="`No ${pluralName.toLowerCase()} found`"
                    :description="`Get started by creating your first ${singularName.toLowerCase()}.`"
                    :action="createRoute ? { label: `Add ${singularName}`, onClick: () => $inertia.visit(route(createRoute)) } : null"
                />
            </template>
        </TableWrapper>

        <!-- Export Button -->
        <div v-if="exportRoute" class="mt-4 flex justify-end">
            <a
                :href="route(exportRoute, filters)"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors"
            >
                <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                Export CSV
            </a>
        </div>
    </DashboardShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardShell from '@/Layouts/DashboardShell.vue';
import StatsCard from '@/Components/UI/StatsCard.vue';
import TableWrapper from '@/Components/UI/TableWrapper.vue';
import ActionButton from '@/Components/UI/ActionButton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    PlusIcon,
    CloudArrowUpIcon,
    ArrowDownTrayIcon,
    FolderOpenIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    // Page Configuration
    title: { type: String, required: true },
    description: { type: String, default: '' },
    singularName: { type: String, required: true },
    pluralName: { type: String, required: true },
    
    // Data
    items: { type: Object, required: true },
    columns: { type: Array, required: true },
    stats: { type: Object, default: null },
    
    // Route Names (without admin.data. prefix - will be added automatically)
    routePrefix: { type: String, required: true }, // e.g., 'admin.data.countries'
    
    // Feature Flags
    showStats: { type: Boolean, default: false },
    showBulkUpload: { type: Boolean, default: true },
    hasStatusFilter: { type: Boolean, default: true },
    selectable: { type: Boolean, default: false },
    
    // Initial Filters
    initialFilters: { type: Object, default: () => ({}) }
});

// Computed Routes
const indexRoute = computed(() => `${props.routePrefix}.index`);
const createRoute = computed(() => `${props.routePrefix}.create`);
const showRoute = computed(() => `${props.routePrefix}.show`);
const editRoute = computed(() => `${props.routePrefix}.edit`);
const destroyRoute = computed(() => `${props.routePrefix}.destroy`);
const toggleStatusRoute = computed(() => `${props.routePrefix}.toggle-status`);
const bulkUploadRoute = computed(() => `${props.routePrefix}.bulk-upload`);
const exportRoute = computed(() => `${props.routePrefix}.export`);

// Filters
const filters = ref({
    search: props.initialFilters.search || '',
    is_active: props.initialFilters.is_active || '',
    ...props.initialFilters
});

// Stats with default icons
const computedStats = computed(() => {
    if (!props.stats) return [];
    
    const defaultStats = [
        { label: 'Total', value: props.stats.total || 0, icon: DocumentTextIcon, variant: 'primary' },
        { label: 'Active', value: props.stats.active || 0, icon: CheckCircleIcon, variant: 'success' },
        { label: 'Inactive', value: props.stats.inactive || 0, icon: XCircleIcon, variant: 'warning' },
        { label: 'Recent', value: props.stats.recent || 0, icon: ClockIcon, variant: 'neutral' }
    ];
    
    return props.stats.custom || defaultStats;
});

// Handlers
const handleSearch = (query) => {
    filters.value.search = query;
    applyFilters();
};

const handleSort = ({ key, order }) => {
    router.get(route(indexRoute.value), {
        ...filters.value,
        sort: key,
        order: order
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const handleSelect = (selected) => {
    console.log('Selected items:', selected);
};

const applyFilters = () => {
    router.get(route(indexRoute.value), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        is_active: ''
    };
    router.get(route(indexRoute.value));
};

const toggleStatus = (item) => {
    const action = item.is_active ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} "${item.name}"?`)) {
        router.post(route(toggleStatusRoute.value, item.id), {}, {
            preserveScroll: true
        });
    }
};

const deleteItem = (item) => {
    const name = item.name || item.title || `this ${props.singularName.toLowerCase()}`;
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        router.delete(route(destroyRoute.value, item.id), {
            preserveScroll: true
        });
    }
};

// Utilities
const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-BD', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const getBadgeClass = (value, variant = 'default') => {
    const variants = {
        default: 'bg-neutral-100 text-neutral-700',
        primary: 'bg-primary-100 text-primary-700',
        success: 'bg-green-100 text-green-700',
        warning: 'bg-yellow-100 text-yellow-700',
        danger: 'bg-red-100 text-red-700'
    };
    return `px-2 py-1 text-xs font-semibold rounded-full ${variants[variant] || variants.default}`;
};

// Expose for parent components
defineExpose({
    filters,
    applyFilters,
    clearFilters
});
</script>
