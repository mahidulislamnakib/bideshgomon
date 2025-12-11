<template>
    <div :class="['table-wrapper', utilityClasses.card, 'overflow-hidden']">
        <!-- Header with Search and Filters -->
        <div v-if="showHeader" class="p-6 border-b border-neutral-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900">{{ title }}</h3>
                    <p v-if="description" class="text-sm text-neutral-500 mt-1">{{ description }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div v-if="searchable" class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="pl-10 pr-4 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                            @input="handleSearch"
                        />
                    </div>

                    <!-- Filter Button -->
                    <button
                        v-if="filterable"
                        @click="showFilters = !showFilters"
                        class="flex items-center gap-2 px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors text-sm font-medium text-neutral-700"
                    >
                        <FunnelIcon class="h-5 w-5" />
                        <span class="hidden sm:inline">Filters</span>
                        <span v-if="activeFiltersCount > 0" class="px-2 py-0.5 bg-primary-600 text-white text-xs font-bold rounded-full">
                            {{ activeFiltersCount }}
                        </span>
                    </button>

                    <!-- Action Slot (e.g., Add New button) -->
                    <slot name="actions"></slot>
                </div>
            </div>

            <!-- Filters Panel -->
            <div v-if="showFilters && filterable" class="mt-4 p-4 bg-neutral-50 rounded-lg">
                <slot name="filters">
                    <p class="text-sm text-neutral-500">No filters available</p>
                </slot>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <!-- Checkbox Column (if selectable) -->
                        <th v-if="selectable" class="px-6 py-3 text-left">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                @change="toggleAll"
                                class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                            />
                        </th>

                        <!-- Column Headers -->
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider',
                                column.sortable && 'cursor-pointer hover:bg-neutral-100 transition-colors'
                            ]"
                            @click="column.sortable && handleSort(column.key)"
                        >
                            <div class="flex items-center gap-2">
                                {{ column.label }}
                                <div v-if="column.sortable" class="flex flex-col">
                                    <ChevronUpIcon
                                        :class="[
                                            'h-3 w-3 -mb-1',
                                            sortKey === column.key && sortOrder === 'asc' ? 'text-primary-600' : 'text-neutral-400'
                                        ]"
                                    />
                                    <ChevronDownIcon
                                        :class="[
                                            'h-3 w-3 -mt-1',
                                            sortKey === column.key && sortOrder === 'desc' ? 'text-primary-600' : 'text-neutral-400'
                                        ]"
                                    />
                                </div>
                            </div>
                        </th>

                        <!-- Actions Column -->
                        <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-semibold text-neutral-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-neutral-200">
                    <!-- Loading State -->
                    <tr v-if="loading">
                        <td :colspan="columnCount" class="px-6 py-12 text-center">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-else-if="!data || data.length === 0">
                        <td :colspan="columnCount" class="px-6 py-12 text-center">
                            <slot name="empty">
                                <div class="flex flex-col items-center">
                                    <FolderOpenIcon class="h-12 w-12 text-neutral-400 mb-3" />
                                    <h3 class="text-sm font-medium text-neutral-900">No data found</h3>
                                    <p class="text-sm text-neutral-500 mt-1">{{ emptyMessage }}</p>
                                </div>
                            </slot>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <template v-else>
                        <tr
                            v-for="(row, index) in data"
                            :key="row.id || index"
                            class="hover:bg-neutral-50 transition-colors"
                        >
                            <!-- Checkbox -->
                            <td v-if="selectable" class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    :checked="selected.includes(row.id)"
                                    @change="toggleRow(row.id)"
                                    class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                                />
                            </td>

                            <!-- Data Cells -->
                            <td
                                v-for="column in columns"
                                :key="column.key"
                                class="px-6 py-4 text-sm text-neutral-900"
                            >
                                <slot :name="`cell(${column.key})`" :row="row" :value="row[column.key]">
                                    {{ row[column.key] }}
                                </slot>
                            </td>

                            <!-- Actions -->
                            <td v-if="hasActions" class="px-6 py-4 text-right text-sm font-medium">
                                <slot name="actions" :row="row"></slot>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Footer with Pagination -->
        <div v-if="pagination && !loading" class="px-6 py-4 border-t border-neutral-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-neutral-700">
                    Showing <span class="font-medium">{{ pagination.from }}</span> to <span class="font-medium">{{ pagination.to }}</span> of <span class="font-medium">{{ pagination.total }}</span> results
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        v-for="link in pagination.links"
                        :key="link.label"
                        :href="link.url"
                        :class="[
                            'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                            link.active
                                ? 'bg-primary-600 text-white'
                                : link.url
                                    ? 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50'
                                    : 'bg-neutral-100 text-neutral-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    FolderOpenIcon
} from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';

const props = defineProps({
    title: String,
    description: String,
    columns: {
        type: Array,
        required: true // [{ key: 'name', label: 'Name', sortable: true }]
    },
    data: {
        type: Array,
        required: true
    },
    pagination: Object, // Laravel pagination object
    loading: Boolean,
    showHeader: {
        type: Boolean,
        default: true
    },
    searchable: {
        type: Boolean,
        default: true
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...'
    },
    filterable: Boolean,
    selectable: Boolean,
    hasActions: Boolean,
    emptyMessage: {
        type: String,
        default: 'Try adjusting your search or filter to find what you\'re looking for.'
    }
});

const emit = defineEmits(['search', 'sort', 'select']);

const searchQuery = ref('');
const showFilters = ref(false);
const selected = ref([]);
const sortKey = ref(null);
const sortOrder = ref('asc');

const columnCount = computed(() => {
    let count = props.columns.length;
    if (props.selectable) count++;
    if (props.hasActions) count++;
    return count;
});

const activeFiltersCount = computed(() => {
    // Implement based on your filter logic
    return 0;
});

const allSelected = computed(() => {
    return props.data.length > 0 && selected.value.length === props.data.length;
});

const handleSearch = () => {
    emit('search', searchQuery.value);
};

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    emit('sort', { key: sortKey.value, order: sortOrder.value });
};

const toggleAll = () => {
    if (allSelected.value) {
        selected.value = [];
    } else {
        selected.value = props.data.map(row => row.id);
    }
    emit('select', selected.value);
};

const toggleRow = (id) => {
    const index = selected.value.indexOf(id);
    if (index > -1) {
        selected.value.splice(index, 1);
    } else {
        selected.value.push(id);
    }
    emit('select', selected.value);
};
</script>
