<script setup>
import { ref, computed } from 'vue'
import {
  ChevronUpIcon,
  ChevronDownIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import Checkbox from './Checkbox.vue'
import Badge from './Badge.vue'
import Spinner from './Spinner.vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    // Format: [{ key: 'name', label: 'Name', sortable: true, width: '200px' }]
  },
  data: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  selectable: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: null
    // Format: { current_page: 1, last_page: 10, per_page: 20, total: 200 }
  },
  emptyMessage: {
    type: String,
    default: 'No data available'
  }
})

const emit = defineEmits(['sort', 'row-click', 'selection-change', 'search', 'page-change'])

const sortKey = ref(null)
const sortDirection = ref('asc')
const selectedRows = ref([])
const searchQuery = ref('')

const allSelected = computed({
  get() {
    return props.data.length > 0 && selectedRows.value.length === props.data.length
  },
  set(value) {
    if (value) {
      selectedRows.value = props.data.map((_, index) => index)
    } else {
      selectedRows.value = []
    }
    emit('selection-change', selectedRows.value)
  }
})

const indeterminate = computed(() => {
  return selectedRows.value.length > 0 && selectedRows.value.length < props.data.length
})

const handleSort = (column) => {
  if (!column.sortable) return
  
  if (sortKey.value === column.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = column.key
    sortDirection.value = 'asc'
  }
  
  emit('sort', { key: sortKey.value, direction: sortDirection.value })
}

const handleRowClick = (row, index) => {
  if (props.clickable) {
    emit('row-click', { row, index })
  }
}

const handleRowSelection = (index) => {
  const position = selectedRows.value.indexOf(index)
  if (position > -1) {
    selectedRows.value.splice(position, 1)
  } else {
    selectedRows.value.push(index)
  }
  emit('selection-change', selectedRows.value)
}

const handleSearch = () => {
  emit('search', searchQuery.value)
}

const handlePageChange = (page) => {
  emit('page-change', page)
}

const getCellValue = (row, column) => {
  return row[column.key]
}
</script>

<template>
  <div class="w-full">
    <!-- Header -->
    <div v-if="title || description || $slots.actions || searchable" class="mb-4">
      <div class="flex items-center justify-between mb-2">
        <div>
          <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ title }}
          </h3>
          <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ description }}
          </p>
        </div>
        <div v-if="$slots.actions" class="flex items-center space-x-2">
          <slot name="actions" />
        </div>
      </div>

      <!-- Search -->
      <div v-if="searchable" class="relative">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Search..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
          @input="handleSearch"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
      <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th v-if="selectable" class="w-12 px-4 py-3">
              <Checkbox v-model="allSelected" :indeterminate="indeterminate" />
            </th>
            <th
              v-for="column in columns"
              :key="column.key"
              :style="{ width: column.width }"
              :class="[
                'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                column.sortable && 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 select-none'
              ]"
              @click="handleSort(column)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <div v-if="column.sortable" class="flex flex-col">
                  <ChevronUpIcon
                    :class="[
                      'h-3 w-3 -mb-1',
                      sortKey === column.key && sortDirection === 'asc'
                        ? 'text-blue-600'
                        : 'text-gray-400'
                    ]"
                  />
                  <ChevronDownIcon
                    :class="[
                      'h-3 w-3',
                      sortKey === column.key && sortDirection === 'desc'
                        ? 'text-blue-600'
                        : 'text-gray-400'
                    ]"
                  />
                </div>
              </div>
            </th>
            <th v-if="$slots.rowActions" class="w-24 px-4 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
          <!-- Loading state -->
          <tr v-if="loading">
            <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.rowActions ? 1 : 0)" class="px-4 py-12 text-center">
              <Spinner size="lg" label="Loading..." />
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.rowActions ? 1 : 0)" class="px-4 py-12 text-center">
              <p class="text-gray-500 dark:text-gray-400">{{ emptyMessage }}</p>
            </td>
          </tr>

          <!-- Data rows -->
          <tr
            v-for="(row, index) in data"
            v-else
            :key="index"
            :class="[
              'transition-colors',
              clickable && 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800',
              selectedRows.includes(index) && 'bg-blue-50 dark:bg-blue-900/20'
            ]"
            @click="handleRowClick(row, index)"
          >
            <td v-if="selectable" class="px-4 py-4" @click.stop>
              <Checkbox
                :model-value="selectedRows.includes(index)"
                @update:model-value="handleRowSelection(index)"
              />
            </td>
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-4 py-4 text-sm text-gray-900 dark:text-white whitespace-nowrap"
            >
              <slot :name="`cell-${column.key}`" :value="getCellValue(row, column)" :row="row" :index="index">
                {{ getCellValue(row, column) }}
              </slot>
            </td>
            <td v-if="$slots.rowActions" class="px-4 py-4 text-right text-sm font-medium" @click.stop>
              <div class="flex items-center justify-end space-x-2">
                <slot name="rowActions" :row="row" :index="index" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between mt-4">
      <div class="text-sm text-gray-500 dark:text-gray-400">
        Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to 
        {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
        {{ pagination.total }} results
      </div>
      <div class="flex items-center space-x-2">
        <button
          type="button"
          :disabled="pagination.current_page === 1"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
            pagination.current_page === 1
              ? 'text-gray-400 cursor-not-allowed'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
          @click="handlePageChange(pagination.current_page - 1)"
        >
          <ChevronLeftIcon class="h-5 w-5" />
        </button>
        
        <div class="flex items-center space-x-1">
          <button
            v-for="page in Math.min(pagination.last_page, 5)"
            :key="page"
            type="button"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              page === pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
            @click="handlePageChange(page)"
          >
            {{ page }}
          </button>
        </div>

        <button
          type="button"
          :disabled="pagination.current_page === pagination.last_page"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
            pagination.current_page === pagination.last_page
              ? 'text-gray-400 cursor-not-allowed'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
          @click="handlePageChange(pagination.current_page + 1)"
        >
          <ChevronRightIcon class="h-5 w-5" />
        </button>
      </div>
    </div>
  </div>
</template>
