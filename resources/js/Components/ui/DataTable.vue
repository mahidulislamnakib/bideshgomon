<template>
  <div class="w-full">
    <!-- Toolbar -->
    <div
      v-if="showToolbar"
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4"
    >
      <!-- Left: Search & Filters -->
      <div class="flex flex-1 items-center gap-3">
        <!-- Search -->
        <div v-if="searchable" class="relative flex-1 max-w-xs">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @input="handleSearch"
          />
        </div>

        <!-- Filter Slot -->
        <slot name="filters" />
      </div>

      <!-- Right: Actions -->
      <div class="flex items-center gap-3">
        <!-- Column Toggle -->
        <Menu v-if="columnToggle" as="div" class="relative">
          <MenuButton class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <AdjustmentsHorizontalIcon class="w-4 h-4" />
            Columns
          </MenuButton>
          <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <MenuItems class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black/5 z-50 py-1 max-h-64 overflow-auto">
              <MenuItem
                v-for="col in columns"
                :key="col.key"
                v-slot="{ active }"
              >
                <label
                  :class="[
                    'flex items-center gap-2 px-4 py-2 text-sm cursor-pointer',
                    active ? 'bg-gray-100 dark:bg-gray-700' : ''
                  ]"
                >
                  <input
                    type="checkbox"
                    :checked="visibleColumns.includes(col.key)"
                    @change="toggleColumn(col.key)"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  />
                  <span class="text-gray-700 dark:text-gray-300">{{ col.label }}</span>
                </label>
              </MenuItem>
            </MenuItems>
          </Transition>
        </Menu>

        <!-- Export -->
        <button
          v-if="exportable"
          type="button"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          @click="handleExport"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
          Export
        </button>

        <!-- Actions Slot -->
        <slot name="actions" />
      </div>
    </div>

    <!-- Bulk Actions Bar -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="selectedRows.length > 0"
        class="flex items-center justify-between px-4 py-3 mb-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg"
      >
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-primary-700 dark:text-primary-300">
            {{ selectedRows.length }} {{ selectedRows.length === 1 ? 'item' : 'items' }} selected
          </span>
          <button
            type="button"
            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
            @click="selectAll"
          >
            Select all {{ filteredData.length }}
          </button>
        </div>
        <div class="flex items-center gap-2">
          <slot name="bulk-actions" :selected="selectedRows" />
          <button
            type="button"
            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"
            @click="clearSelection"
          >
            Clear
          </button>
        </div>
      </div>
    </Transition>

    <!-- Table -->
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <!-- Select All -->
              <th v-if="selectable" class="w-12 px-4 py-3">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected && !allSelected"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
              </th>

              <!-- Columns -->
              <th
                v-for="col in displayColumns"
                :key="col.key"
                :class="[
                  'px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider',
                  col.sortable ? 'cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700' : '',
                  col.width || ''
                ]"
                @click="col.sortable && handleSort(col.key)"
              >
                <div class="flex items-center gap-2">
                  <span>{{ col.label }}</span>
                  <span v-if="col.sortable" class="flex flex-col">
                    <ChevronUpIcon
                      :class="[
                        'w-3 h-3 -mb-1',
                        sortKey === col.key && sortOrder === 'asc' ? 'text-primary-600' : 'text-gray-300'
                      ]"
                    />
                    <ChevronDownIcon
                      :class="[
                        'w-3 h-3',
                        sortKey === col.key && sortOrder === 'desc' ? 'text-primary-600' : 'text-gray-300'
                      ]"
                    />
                  </span>
                </div>
              </th>

              <!-- Actions -->
              <th v-if="$slots.rowActions" class="w-20 px-4 py-3" />
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Loading -->
            <tr v-if="loading">
              <td :colspan="totalColumns" class="px-4 py-12 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-8 h-8 border-2 border-gray-300 border-t-primary-500 rounded-full animate-spin" />
                  <span class="mt-3 text-sm text-gray-500">Loading...</span>
                </div>
              </td>
            </tr>

            <!-- Empty -->
            <tr v-else-if="filteredData.length === 0">
              <td :colspan="totalColumns" class="px-4 py-12 text-center">
                <slot name="empty">
                  <div class="flex flex-col items-center">
                    <InboxIcon class="w-12 h-12 text-gray-300" />
                    <span class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No data found</span>
                    <span class="mt-1 text-sm text-gray-500">{{ emptyMessage }}</span>
                  </div>
                </slot>
              </td>
            </tr>

            <!-- Rows -->
            <tr
              v-else
              v-for="(row, rowIndex) in paginatedData"
              :key="getRowKey(row, rowIndex)"
              :class="[
                'transition-colors',
                hoverable ? 'hover:bg-gray-50 dark:hover:bg-gray-800' : '',
                striped && rowIndex % 2 === 1 ? 'bg-gray-50/50 dark:bg-gray-800/50' : '',
                isSelected(row) ? 'bg-primary-50 dark:bg-primary-900/20' : '',
                clickable ? 'cursor-pointer' : ''
              ]"
              @click="handleRowClick(row, rowIndex)"
            >
              <!-- Select -->
              <td v-if="selectable" class="px-4 py-3" @click.stop>
                <input
                  type="checkbox"
                  :checked="isSelected(row)"
                  @change="toggleSelect(row)"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
              </td>

              <!-- Cells -->
              <td
                v-for="col in displayColumns"
                :key="col.key"
                class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"
              >
                <slot :name="`cell-${col.key}`" :row="row" :value="getCellValue(row, col.key)">
                  {{ formatCellValue(getCellValue(row, col.key), col) }}
                </slot>
              </td>

              <!-- Row Actions -->
              <td v-if="$slots.rowActions" class="px-4 py-3 text-right" @click.stop>
                <slot name="rowActions" :row="row" :index="rowIndex" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="paginated && filteredData.length > 0"
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-4"
    >
      <div class="text-sm text-gray-600 dark:text-gray-400">
        Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} results
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          :disabled="currentPage === 1"
          class="px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :class="currentPage === 1 ? 'border-gray-200 text-gray-400' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'"
          @click="currentPage--"
        >
          Previous
        </button>
        <div class="flex items-center gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            type="button"
            :class="[
              'w-8 h-8 text-sm font-medium rounded-lg transition-colors',
              page === currentPage
                ? 'bg-primary-600 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
        </div>
        <button
          type="button"
          :disabled="currentPage === totalPages"
          class="px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :class="currentPage === totalPages ? 'border-gray-200 text-gray-400' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'"
          @click="currentPage++"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import {
  MagnifyingGlassIcon,
  AdjustmentsHorizontalIcon,
  ArrowDownTrayIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  InboxIcon
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  // Data array
  data: {
    type: Array,
    default: () => []
  },
  // Column definitions
  columns: {
    type: Array,
    required: true
    // { key, label, sortable?, width?, format?: 'currency'|'date'|'number' }
  },
  // Row key field
  rowKey: {
    type: String,
    default: 'id'
  },
  // Loading state
  loading: {
    type: Boolean,
    default: false
  },
  // Empty message
  emptyMessage: {
    type: String,
    default: 'Try adjusting your search or filters'
  },
  // Features
  searchable: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  searchFields: {
    type: Array,
    default: () => []
  },
  sortable: {
    type: Boolean,
    default: true
  },
  selectable: {
    type: Boolean,
    default: false
  },
  paginated: {
    type: Boolean,
    default: true
  },
  pageSize: {
    type: Number,
    default: 10
  },
  columnToggle: {
    type: Boolean,
    default: false
  },
  exportable: {
    type: Boolean,
    default: false
  },
  // Styling
  striped: {
    type: Boolean,
    default: false
  },
  hoverable: {
    type: Boolean,
    default: true
  },
  clickable: {
    type: Boolean,
    default: false
  },
  showToolbar: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['row-click', 'selection-change', 'export', 'search', 'sort'])

const { formatCurrency, formatDate } = useBangladeshFormat()

// State
const searchQuery = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')
const currentPage = ref(1)
const selectedRows = ref([])
const visibleColumns = ref(props.columns.map(c => c.key))

// Display columns
const displayColumns = computed(() => {
  return props.columns.filter(c => visibleColumns.value.includes(c.key))
})

// Total columns count
const totalColumns = computed(() => {
  let count = displayColumns.value.length
  if (props.selectable) count++
  if (props.$slots?.rowActions) count++
  return count
})

// Filtered data
const filteredData = computed(() => {
  let result = [...props.data]

  // Search
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    const fields = props.searchFields.length > 0 ? props.searchFields : props.columns.map(c => c.key)
    
    result = result.filter(row => {
      return fields.some(field => {
        const value = getCellValue(row, field)
        return String(value).toLowerCase().includes(q)
      })
    })
  }

  // Sort
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = getCellValue(a, sortKey.value)
      const bVal = getCellValue(b, sortKey.value)
      
      if (aVal === bVal) return 0
      const comparison = aVal > bVal ? 1 : -1
      return sortOrder.value === 'asc' ? comparison : -comparison
    })
  }

  return result
})

// Pagination
const totalPages = computed(() => Math.ceil(filteredData.value.length / props.pageSize))
const startIndex = computed(() => (currentPage.value - 1) * props.pageSize)
const endIndex = computed(() => Math.min(startIndex.value + props.pageSize, filteredData.value.length))
const paginatedData = computed(() => {
  if (!props.paginated) return filteredData.value
  return filteredData.value.slice(startIndex.value, endIndex.value)
})

// Visible pagination pages
const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4, 5)
    } else if (current >= total - 2) {
      pages.push(total - 4, total - 3, total - 2, total - 1, total)
    } else {
      pages.push(current - 2, current - 1, current, current + 1, current + 2)
    }
  }
  
  return pages
})

// Selection
const allSelected = computed(() => {
  return filteredData.value.length > 0 && selectedRows.value.length === filteredData.value.length
})
const someSelected = computed(() => selectedRows.value.length > 0)

// Get row key
function getRowKey(row, index) {
  return row[props.rowKey] ?? index
}

// Get cell value (supports nested keys like 'user.name')
function getCellValue(row, key) {
  return key.split('.').reduce((obj, k) => obj?.[k], row)
}

// Format cell value
function formatCellValue(value, col) {
  if (value === null || value === undefined) return '—'
  
  switch (col.format) {
    case 'currency':
      return formatCurrency(value)
    case 'date':
      return formatDate(value)
    case 'number':
      return Number(value).toLocaleString('en-BD')
    default:
      return value
  }
}

// Toggle column visibility
function toggleColumn(key) {
  const index = visibleColumns.value.indexOf(key)
  if (index > -1) {
    visibleColumns.value.splice(index, 1)
  } else {
    visibleColumns.value.push(key)
  }
}

// Handle search
function handleSearch() {
  currentPage.value = 1
  emit('search', searchQuery.value)
}

// Handle sort
function handleSort(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
  emit('sort', { key: sortKey.value, order: sortOrder.value })
}

// Selection methods
function isSelected(row) {
  return selectedRows.value.some(r => getRowKey(r, -1) === getRowKey(row, -1))
}

function toggleSelect(row) {
  const key = getRowKey(row, -1)
  const index = selectedRows.value.findIndex(r => getRowKey(r, -1) === key)
  
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(row)
  }
  emit('selection-change', selectedRows.value)
}

function toggleSelectAll() {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = [...filteredData.value]
  }
  emit('selection-change', selectedRows.value)
}

function selectAll() {
  selectedRows.value = [...filteredData.value]
  emit('selection-change', selectedRows.value)
}

function clearSelection() {
  selectedRows.value = []
  emit('selection-change', selectedRows.value)
}

// Row click
function handleRowClick(row, index) {
  if (props.clickable) {
    emit('row-click', { row, index })
  }
}

// Export
function handleExport() {
  emit('export', filteredData.value)
}

// Reset page on filter change
watch(searchQuery, () => {
  currentPage.value = 1
})

// Expose methods
defineExpose({
  clearSelection,
  selectAll
})
</script>
