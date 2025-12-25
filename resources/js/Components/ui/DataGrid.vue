<template>
  <div :class="['data-grid rounded-xl border overflow-hidden', themeClasses]">
    <!-- Toolbar -->
    <div v-if="showToolbar" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div v-if="showSearch" class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg w-64 text-gray-900 dark:text-white placeholder-gray-400"
          />
        </div>
        <div v-if="selectedRows.length > 0" class="flex items-center gap-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ selectedRows.length }} selected</span>
          <button
            type="button"
            class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
            @click="$emit('bulkDelete', selectedRows)"
          >
            Delete
          </button>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="showColumnToggle"
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          @click="showColumnMenu = !showColumnMenu"
        >
          <ViewColumnsIcon class="w-5 h-5" />
        </button>
        <button
          v-if="showExport"
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          @click="exportData"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </button>
        <button
          v-if="showRefresh"
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          @click="$emit('refresh')"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Column Menu -->
    <div
      v-if="showColumnMenu"
      class="absolute right-4 top-16 z-50 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-2 min-w-48"
    >
      <label
        v-for="col in columns"
        :key="col.key"
        class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
      >
        <input
          type="checkbox"
          :checked="visibleColumns.includes(col.key)"
          class="w-4 h-4 rounded border-gray-300 text-blue-600"
          @change="toggleColumn(col.key)"
        />
        <span class="text-sm text-gray-700 dark:text-gray-300">{{ col.label }}</span>
      </label>
    </div>

    <!-- Table -->
    <div class="overflow-auto" :style="{ maxHeight: height }">
      <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
          <tr>
            <th v-if="selectable" class="w-12 px-4 py-3">
              <input
                type="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                class="w-4 h-4 rounded border-gray-300 text-blue-600"
                @change="toggleAllSelection"
              />
            </th>
            <th
              v-for="col in displayColumns"
              :key="col.key"
              :class="[
                'px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                col.sortable !== false ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700' : ''
              ]"
              :style="{ width: col.width }"
              @click="col.sortable !== false && sortBy(col.key)"
            >
              <div class="flex items-center gap-2">
                {{ col.label }}
                <template v-if="sortKey === col.key">
                  <ChevronUpIcon v-if="sortOrder === 'asc'" class="w-4 h-4" />
                  <ChevronDownIcon v-else class="w-4 h-4" />
                </template>
              </div>
            </th>
            <th v-if="showActions" class="w-24 px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(row, index) in paginatedData"
            :key="row[rowKey] || index"
            :class="[
              'transition-colors',
              selectedRows.includes(row[rowKey]) ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
            ]"
          >
            <td v-if="selectable" class="px-4 py-3">
              <input
                type="checkbox"
                :checked="selectedRows.includes(row[rowKey])"
                class="w-4 h-4 rounded border-gray-300 text-blue-600"
                @change="toggleRowSelection(row[rowKey])"
              />
            </td>
            <td
              v-for="col in displayColumns"
              :key="col.key"
              class="px-4 py-3 text-sm text-gray-900 dark:text-white"
            >
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                <template v-if="col.type === 'badge'">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getBadgeClass(row[col.key])]">
                    {{ row[col.key] }}
                  </span>
                </template>
                <template v-else-if="col.type === 'date'">
                  {{ formatDate(row[col.key]) }}
                </template>
                <template v-else-if="col.type === 'currency'">
                  ৳{{ Number(row[col.key]).toLocaleString() }}
                </template>
                <template v-else-if="col.type === 'boolean'">
                  <CheckCircleIcon v-if="row[col.key]" class="w-5 h-5 text-green-500" />
                  <XCircleIcon v-else class="w-5 h-5 text-red-500" />
                </template>
                <template v-else-if="col.type === 'image'">
                  <img :src="row[col.key]" :alt="col.label" class="w-10 h-10 rounded-lg object-cover" />
                </template>
                <template v-else>
                  {{ row[col.key] }}
                </template>
              </slot>
            </td>
            <td v-if="showActions" class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button
                  type="button"
                  class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg"
                  @click="$emit('view', row)"
                >
                  <EyeIcon class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  class="p-1.5 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded-lg"
                  @click="$emit('edit', row)"
                >
                  <PencilIcon class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
                  @click="$emit('delete', row)"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="paginatedData.length === 0">
            <td :colspan="displayColumns.length + (selectable ? 1 : 0) + (showActions ? 1 : 0)" class="px-4 py-12 text-center">
              <TableCellsIcon class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
              <p class="text-gray-500 dark:text-gray-400">No data available</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="showPagination" class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ startRow }} - {{ endRow }} of {{ filteredData.length }}
        </span>
        <select
          v-model="currentPageSize"
          class="text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg px-3 py-1.5"
        >
          <option v-for="size in pageSizes" :key="size" :value="size">{{ size }} per page</option>
        </select>
      </div>
      <div class="flex items-center gap-1">
        <button
          type="button"
          :disabled="currentPage === 1"
          class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50"
          @click="currentPage--"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          type="button"
          :class="[
            'w-10 h-10 rounded-lg text-sm font-medium',
            page === currentPage
              ? 'bg-blue-600 text-white'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
          @click="currentPage = page"
        >
          {{ page }}
        </button>
        <button
          type="button"
          :disabled="currentPage === totalPages"
          class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50"
          @click="currentPage++"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  MagnifyingGlassIcon,
  ViewColumnsIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  TableCellsIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, default: () => [] },
  rowKey: { type: String, default: 'id' },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  selectable: { type: Boolean, default: false },
  showToolbar: { type: Boolean, default: true },
  showSearch: { type: Boolean, default: true },
  showColumnToggle: { type: Boolean, default: true },
  showExport: { type: Boolean, default: true },
  showRefresh: { type: Boolean, default: true },
  showActions: { type: Boolean, default: true },
  showPagination: { type: Boolean, default: true },
  pageSize: { type: Number, default: 10 },
  pageSizes: { type: Array, default: () => [10, 25, 50, 100] }
})

const emit = defineEmits(['view', 'edit', 'delete', 'bulkDelete', 'refresh', 'export', 'selectionChange'])

const searchQuery = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')
const currentPage = ref(1)
const currentPageSize = ref(props.pageSize)
const selectedRows = ref([])
const visibleColumns = ref(props.columns.map(c => c.key))
const showColumnMenu = ref(false)

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const displayColumns = computed(() => 
  props.columns.filter(c => visibleColumns.value.includes(c.key))
)

const filteredData = computed(() => {
  let result = [...props.data]
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(row => 
      Object.values(row).some(val => 
        String(val).toLowerCase().includes(query)
      )
    )
  }
  
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = a[sortKey.value]
      const bVal = b[sortKey.value]
      const comp = aVal < bVal ? -1 : aVal > bVal ? 1 : 0
      return sortOrder.value === 'asc' ? comp : -comp
    })
  }
  
  return result
})

const totalPages = computed(() => 
  Math.ceil(filteredData.value.length / currentPageSize.value)
)

const startRow = computed(() => 
  (currentPage.value - 1) * currentPageSize.value + 1
)

const endRow = computed(() => 
  Math.min(currentPage.value * currentPageSize.value, filteredData.value.length)
)

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * currentPageSize.value
  return filteredData.value.slice(start, start + currentPageSize.value)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const allSelected = computed(() => 
  paginatedData.value.length > 0 && 
  paginatedData.value.every(row => selectedRows.value.includes(row[props.rowKey]))
)

const someSelected = computed(() => 
  selectedRows.value.length > 0 && !allSelected.value
)

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const toggleColumn = (key) => {
  const index = visibleColumns.value.indexOf(key)
  if (index > -1) {
    visibleColumns.value.splice(index, 1)
  } else {
    visibleColumns.value.push(key)
  }
}

const toggleRowSelection = (id) => {
  const index = selectedRows.value.indexOf(id)
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(id)
  }
  emit('selectionChange', selectedRows.value)
}

const toggleAllSelection = () => {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = paginatedData.value.map(row => row[props.rowKey])
  }
  emit('selectionChange', selectedRows.value)
}

const formatDate = (date) => new Date(date).toLocaleDateString()

const getBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    pending: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
    inactive: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
    approved: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    rejected: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
  }
  return classes[status?.toLowerCase()] || classes.inactive
}

const exportData = () => {
  emit('export', filteredData.value)
}

watch(searchQuery, () => { currentPage.value = 1 })
watch(currentPageSize, () => { currentPage.value = 1 })
</script>
