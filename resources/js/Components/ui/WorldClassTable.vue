<template>
  <div class="overflow-hidden">
    <!-- Table Header (Title + Actions) -->
    <div v-if="title || $slots.header" class="px-6 py-4 border-b border-neutral-200 bg-neutral-50">
      <slot name="header">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-neutral-900">{{ title }}</h3>
            <p v-if="description" class="mt-1 text-sm text-neutral-600">{{ description }}</p>
          </div>
          <div v-if="$slots.actions">
            <slot name="actions" />
          </div>
        </div>
      </slot>
    </div>
    
    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200">
        <!-- Table Head -->
        <thead class="bg-neutral-50">
          <tr>
            <th
              v-for="(column, index) in columns"
              :key="index"
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
              :class="column.headerClass"
            >
              <div
                class="flex items-center gap-2"
                :class="column.sortable ? 'cursor-pointer select-none hover:text-neutral-700' : ''"
                @click="column.sortable ? handleSort(column.key) : null"
              >
                {{ column.label }}
                
                <!-- Sort Icon -->
                <template v-if="column.sortable">
                  <svg
                    v-if="sortBy === column.key && sortOrder === 'asc'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg
                    v-else-if="sortBy === column.key && sortOrder === 'desc'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                  <svg
                    v-else
                    class="w-4 h-4 text-neutral-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </template>
              </div>
            </th>
          </tr>
        </thead>
        
        <!-- Table Body -->
        <tbody class="bg-white divide-y divide-neutral-200">
          <!-- Empty State -->
          <tr v-if="!data || data.length === 0">
            <td :colspan="columns.length" class="px-6 py-12 text-center">
              <slot name="empty">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-12 h-12 text-neutral-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="text-neutral-500 font-medium">{{ emptyMessage }}</p>
                  <p v-if="emptyDescription" class="text-sm text-neutral-400 mt-1">{{ emptyDescription }}</p>
                </div>
              </slot>
            </td>
          </tr>
          
          <!-- Data Rows -->
          <tr
            v-for="(row, rowIndex) in sortedData"
            :key="rowIndex"
            class="hover:bg-neutral-50 transition-colors"
            :class="hoverable ? 'cursor-pointer' : ''"
            @click="handleRowClick(row, rowIndex)"
          >
            <td
              v-for="(column, colIndex) in columns"
              :key="colIndex"
              class="px-6 py-4 whitespace-nowrap text-sm"
              :class="column.cellClass"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]" :index="rowIndex">
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Footer (Pagination) -->
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-neutral-200 bg-neutral-50">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
/**
 * World-Class Table Component
 * Sortable, Responsive, Customizable
 */

import { computed, ref } from 'vue';

const props = defineProps({
  // Table metadata
  title: {
    type: String,
    default: '',
  },
  
  description: {
    type: String,
    default: '',
  },
  
  // Columns configuration
  columns: {
    type: Array,
    required: true,
    // Example: [{ key: 'name', label: 'Name', sortable: true, headerClass: '', cellClass: '' }]
  },
  
  // Data
  data: {
    type: Array,
    default: () => [],
  },
  
  // Empty state
  emptyMessage: {
    type: String,
    default: 'No data available',
  },
  
  emptyDescription: {
    type: String,
    default: '',
  },
  
  // Interaction
  hoverable: {
    type: Boolean,
    default: true,
  },
  
  // Sorting
  defaultSortBy: {
    type: String,
    default: '',
  },
  
  defaultSortOrder: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value),
  },
});

const emit = defineEmits(['row-click', 'sort']);

const sortBy = ref(props.defaultSortBy);
const sortOrder = ref(props.defaultSortOrder);

const sortedData = computed(() => {
  if (!sortBy.value) return props.data;
  
  return [...props.data].sort((a, b) => {
    const aVal = a[sortBy.value];
    const bVal = b[sortBy.value];
    
    if (aVal === bVal) return 0;
    
    const comparison = aVal > bVal ? 1 : -1;
    return sortOrder.value === 'asc' ? comparison : -comparison;
  });
});

const handleSort = (columnKey) => {
  if (sortBy.value === columnKey) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = columnKey;
    sortOrder.value = 'asc';
  }
  
  emit('sort', { sortBy: sortBy.value, sortOrder: sortOrder.value });
};

const handleRowClick = (row, index) => {
  if (props.hoverable) {
    emit('row-click', { row, index });
  }
};
</script>
