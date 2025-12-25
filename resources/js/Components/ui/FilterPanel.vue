<template>
  <div :class="['filter-panel rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg">
          <FunnelIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ activeFilterCount > 0 ? `${activeFilterCount} filters active` : 'No filters applied' }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="activeFilterCount > 0"
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
          @click="clearAll"
        >
          Clear All
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="$emit('close')"
        >
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Active Filters -->
    <div v-if="activeFilters.length > 0" class="flex flex-wrap gap-2 p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
      <span
        v-for="filter in activeFilters"
        :key="`${filter.group}-${filter.value}`"
        class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full"
      >
        <span class="font-medium">{{ filter.label }}:</span>
        {{ filter.displayValue }}
        <button
          type="button"
          class="p-0.5 hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full transition-colors"
          @click="removeFilter(filter)"
        >
          <XMarkIcon class="w-3 h-3" />
        </button>
      </span>
    </div>

    <!-- Filter Groups -->
    <div class="overflow-auto" :style="{ maxHeight: height }">
      <div
        v-for="group in filterGroups"
        :key="group.id"
        class="border-b border-gray-200 dark:border-gray-700 last:border-0"
      >
        <!-- Group Header -->
        <button
          type="button"
          class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          @click="toggleGroup(group.id)"
        >
          <div class="flex items-center gap-2">
            <component v-if="group.icon" :is="group.icon" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
            <span class="font-medium text-gray-900 dark:text-white">{{ group.label }}</span>
            <span
              v-if="getGroupActiveCount(group.id) > 0"
              class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full"
            >
              {{ getGroupActiveCount(group.id) }}
            </span>
          </div>
          <ChevronDownIcon
            :class="[
              'w-5 h-5 text-gray-400 transition-transform',
              expandedGroups.includes(group.id) ? 'rotate-180' : ''
            ]"
          />
        </button>

        <!-- Group Content -->
        <Transition name="expand">
          <div v-if="expandedGroups.includes(group.id)" class="px-4 pb-4">
            <!-- Checkbox Options -->
            <div v-if="group.type === 'checkbox'" class="space-y-2">
              <label
                v-for="option in group.options"
                :key="option.value"
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  :checked="isSelected(group.id, option.value)"
                  class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                  @change="toggleOption(group.id, option)"
                />
                <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ option.label }}</span>
                <span v-if="option.count !== undefined" class="text-xs text-gray-400 dark:text-gray-500">
                  ({{ option.count }})
                </span>
              </label>
            </div>

            <!-- Radio Options -->
            <div v-else-if="group.type === 'radio'" class="space-y-2">
              <label
                v-for="option in group.options"
                :key="option.value"
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
              >
                <input
                  type="radio"
                  :name="group.id"
                  :checked="isSelected(group.id, option.value)"
                  class="w-4 h-4 border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                  @change="selectOption(group.id, option)"
                />
                <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ option.label }}</span>
              </label>
            </div>

            <!-- Range Slider -->
            <div v-else-if="group.type === 'range'" class="space-y-4 pt-2">
              <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>{{ formatRangeValue(group, rangeValues[group.id]?.min || group.min) }}</span>
                <span>{{ formatRangeValue(group, rangeValues[group.id]?.max || group.max) }}</span>
              </div>
              <div class="relative">
                <input
                  type="range"
                  :min="group.min"
                  :max="group.max"
                  :step="group.step || 1"
                  :value="rangeValues[group.id]?.min || group.min"
                  class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                  @input="updateRange(group.id, 'min', $event.target.value)"
                />
              </div>
              <div class="flex gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500 dark:text-gray-400">Min</label>
                  <input
                    type="number"
                    :min="group.min"
                    :max="group.max"
                    :value="rangeValues[group.id]?.min || group.min"
                    class="w-full mt-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg"
                    @input="updateRange(group.id, 'min', $event.target.value)"
                  />
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500 dark:text-gray-400">Max</label>
                  <input
                    type="number"
                    :min="group.min"
                    :max="group.max"
                    :value="rangeValues[group.id]?.max || group.max"
                    class="w-full mt-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg"
                    @input="updateRange(group.id, 'max', $event.target.value)"
                  />
                </div>
              </div>
            </div>

            <!-- Date Range -->
            <div v-else-if="group.type === 'date'" class="space-y-3 pt-2">
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400">From</label>
                <input
                  type="date"
                  :value="dateValues[group.id]?.from"
                  class="w-full mt-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg text-gray-900 dark:text-white"
                  @input="updateDate(group.id, 'from', $event.target.value)"
                />
              </div>
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400">To</label>
                <input
                  type="date"
                  :value="dateValues[group.id]?.to"
                  class="w-full mt-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg text-gray-900 dark:text-white"
                  @input="updateDate(group.id, 'to', $event.target.value)"
                />
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="preset in datePresets"
                  :key="preset.id"
                  type="button"
                  class="px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                  @click="applyDatePreset(group.id, preset)"
                >
                  {{ preset.label }}
                </button>
              </div>
            </div>

            <!-- Search/Select -->
            <div v-else-if="group.type === 'search'" class="space-y-2 pt-2">
              <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  v-model="searchQueries[group.id]"
                  type="text"
                  :placeholder="`Search ${group.label.toLowerCase()}...`"
                  class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg text-gray-900 dark:text-white placeholder-gray-400"
                />
              </div>
              <div class="max-h-40 overflow-y-auto space-y-1">
                <label
                  v-for="option in getFilteredOptions(group)"
                  :key="option.value"
                  class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                >
                  <input
                    type="checkbox"
                    :checked="isSelected(group.id, option.value)"
                    class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    @change="toggleOption(group.id, option)"
                  />
                  <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ option.label }}</span>
                </label>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showFooter" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
        @click="$emit('close')"
      >
        Cancel
      </button>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
        @click="applyFilters"
      >
        Apply Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  FunnelIcon,
  XMarkIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  TagIcon,
  CalendarIcon,
  CurrencyDollarIcon,
  MapPinIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'Filters' },
  filters: { type: Array, default: () => [] },
  modelValue: { type: Object, default: () => ({}) },
  height: { type: String, default: '400px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true }
})

const emit = defineEmits(['update:modelValue', 'apply', 'close', 'clear'])

const expandedGroups = ref(['status', 'category'])
const selectedFilters = reactive({})
const rangeValues = reactive({})
const dateValues = reactive({})
const searchQueries = reactive({})

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const defaultFilters = [
  {
    id: 'status',
    label: 'Status',
    type: 'checkbox',
    icon: TagIcon,
    options: [
      { value: 'pending', label: 'Pending', count: 12 },
      { value: 'approved', label: 'Approved', count: 45 },
      { value: 'rejected', label: 'Rejected', count: 8 },
      { value: 'processing', label: 'Processing', count: 23 }
    ]
  },
  {
    id: 'category',
    label: 'Category',
    type: 'checkbox',
    options: [
      { value: 'tourist', label: 'Tourist Visa', count: 34 },
      { value: 'business', label: 'Business Visa', count: 18 },
      { value: 'student', label: 'Student Visa', count: 12 },
      { value: 'work', label: 'Work Visa', count: 8 }
    ]
  },
  {
    id: 'date',
    label: 'Date Range',
    type: 'date',
    icon: CalendarIcon
  },
  {
    id: 'price',
    label: 'Price Range',
    type: 'range',
    icon: CurrencyDollarIcon,
    min: 0,
    max: 50000,
    step: 1000,
    format: 'currency'
  },
  {
    id: 'country',
    label: 'Country',
    type: 'search',
    icon: MapPinIcon,
    options: [
      { value: 'sg', label: 'Singapore' },
      { value: 'my', label: 'Malaysia' },
      { value: 'th', label: 'Thailand' },
      { value: 'ae', label: 'UAE' },
      { value: 'us', label: 'United States' },
      { value: 'uk', label: 'United Kingdom' },
      { value: 'au', label: 'Australia' },
      { value: 'ca', label: 'Canada' }
    ]
  }
]

const filterGroups = computed(() => 
  props.filters.length > 0 ? props.filters : defaultFilters
)

const datePresets = [
  { id: 'today', label: 'Today', days: 0 },
  { id: 'week', label: 'This Week', days: 7 },
  { id: 'month', label: 'This Month', days: 30 },
  { id: 'year', label: 'This Year', days: 365 }
]

const activeFilters = computed(() => {
  const filters = []
  Object.entries(selectedFilters).forEach(([groupId, values]) => {
    const group = filterGroups.value.find(g => g.id === groupId)
    if (group && Array.isArray(values)) {
      values.forEach(value => {
        const option = group.options?.find(o => o.value === value)
        filters.push({
          group: groupId,
          label: group.label,
          value,
          displayValue: option?.label || value
        })
      })
    }
  })
  return filters
})

const activeFilterCount = computed(() => activeFilters.value.length)

const toggleGroup = (groupId) => {
  const index = expandedGroups.value.indexOf(groupId)
  if (index > -1) {
    expandedGroups.value.splice(index, 1)
  } else {
    expandedGroups.value.push(groupId)
  }
}

const getGroupActiveCount = (groupId) => {
  return activeFilters.value.filter(f => f.group === groupId).length
}

const isSelected = (groupId, value) => {
  return selectedFilters[groupId]?.includes(value)
}

const toggleOption = (groupId, option) => {
  if (!selectedFilters[groupId]) {
    selectedFilters[groupId] = []
  }
  const index = selectedFilters[groupId].indexOf(option.value)
  if (index > -1) {
    selectedFilters[groupId].splice(index, 1)
  } else {
    selectedFilters[groupId].push(option.value)
  }
}

const selectOption = (groupId, option) => {
  selectedFilters[groupId] = [option.value]
}

const updateRange = (groupId, type, value) => {
  if (!rangeValues[groupId]) {
    rangeValues[groupId] = {}
  }
  rangeValues[groupId][type] = Number(value)
}

const updateDate = (groupId, type, value) => {
  if (!dateValues[groupId]) {
    dateValues[groupId] = {}
  }
  dateValues[groupId][type] = value
}

const applyDatePreset = (groupId, preset) => {
  const today = new Date()
  const from = new Date()
  from.setDate(today.getDate() - preset.days)
  
  dateValues[groupId] = {
    from: from.toISOString().split('T')[0],
    to: today.toISOString().split('T')[0]
  }
}

const getFilteredOptions = (group) => {
  const query = searchQueries[group.id]?.toLowerCase() || ''
  if (!query) return group.options
  return group.options.filter(o => 
    o.label.toLowerCase().includes(query)
  )
}

const formatRangeValue = (group, value) => {
  if (group.format === 'currency') {
    return `৳${value.toLocaleString()}`
  }
  return value
}

const removeFilter = (filter) => {
  const index = selectedFilters[filter.group]?.indexOf(filter.value)
  if (index > -1) {
    selectedFilters[filter.group].splice(index, 1)
  }
}

const clearAll = () => {
  Object.keys(selectedFilters).forEach(key => {
    selectedFilters[key] = []
  })
  Object.keys(rangeValues).forEach(key => {
    delete rangeValues[key]
  })
  Object.keys(dateValues).forEach(key => {
    delete dateValues[key]
  })
  emit('clear')
}

const applyFilters = () => {
  const filters = {
    ...selectedFilters,
    ranges: { ...rangeValues },
    dates: { ...dateValues }
  }
  emit('update:modelValue', filters)
  emit('apply', filters)
}
</script>

<style scoped>
.expand-enter-active,
.expand-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 500px;
}

input[type="range"]::-webkit-slider-thumb {
  appearance: none;
  width: 16px;
  height: 16px;
  background: #3b82f6;
  border-radius: 50%;
  cursor: pointer;
}
</style>
