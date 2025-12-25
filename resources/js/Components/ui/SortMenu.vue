<template>
  <div class="sort-menu relative">
    <!-- Trigger Button -->
    <button
      type="button"
      :class="[
        'flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors',
        variant === 'outline'
          ? theme === 'dark'
            ? 'border border-gray-700 text-gray-300 hover:bg-gray-800'
            : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
          : theme === 'dark'
            ? 'bg-gray-800 text-white hover:bg-gray-700'
            : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
      ]"
      @click="isOpen = !isOpen"
    >
      <component :is="sortIcon" class="w-4 h-4" />
      <span class="text-sm">{{ currentLabel }}</span>
      <ChevronDownIcon :class="['w-4 h-4 transition-transform', isOpen ? 'rotate-180' : '']" />
    </button>

    <!-- Dropdown Menu -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-2 rounded-xl shadow-xl border overflow-hidden',
          positionClasses,
          theme === 'dark' ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
        ]"
        :style="{ minWidth: `${menuWidth}px` }"
      >
        <!-- Search (optional) -->
        <div v-if="searchable && options.length > 10" class="p-2 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search..."
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white placeholder-gray-400"
            />
          </div>
        </div>

        <!-- Options Groups -->
        <div class="py-1 max-h-80 overflow-y-auto">
          <template v-for="(group, groupIndex) in groupedOptions" :key="groupIndex">
            <div v-if="group.label" class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              {{ group.label }}
            </div>
            <button
              v-for="option in group.options"
              :key="option.value"
              type="button"
              :class="[
                'w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm transition-colors',
                isSelected(option)
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                  : theme === 'dark'
                    ? 'text-gray-300 hover:bg-gray-700'
                    : 'text-gray-700 hover:bg-gray-100'
              ]"
              @click="selectOption(option)"
            >
              <div class="flex items-center gap-3">
                <component
                  v-if="option.icon"
                  :is="option.icon"
                  :class="[
                    'w-4 h-4',
                    isSelected(option)
                      ? 'text-blue-600 dark:text-blue-400'
                      : 'text-gray-400 dark:text-gray-500'
                  ]"
                />
                <span>{{ option.label }}</span>
              </div>
              <div class="flex items-center gap-2">
                <!-- Direction Indicator -->
                <button
                  v-if="option.sortable !== false && isSelected(option)"
                  type="button"
                  class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                  @click.stop="toggleDirection"
                >
                  <component
                    :is="sortDirection === 'asc' ? BarsArrowUpIcon : BarsArrowDownIcon"
                    class="w-4 h-4"
                  />
                </button>
                <!-- Selected Check -->
                <CheckIcon
                  v-if="isSelected(option)"
                  class="w-4 h-4 text-blue-600 dark:text-blue-400"
                />
              </div>
            </button>
            <div
              v-if="groupIndex < groupedOptions.length - 1"
              class="my-1 border-t border-gray-200 dark:border-gray-700"
            />
          </template>
        </div>

        <!-- Footer Actions -->
        <div v-if="showReset" class="p-2 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            class="w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            @click="resetSort"
          >
            Reset to Default
          </button>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  ChevronDownIcon,
  MagnifyingGlassIcon,
  CheckIcon,
  BarsArrowUpIcon,
  BarsArrowDownIcon,
  ArrowsUpDownIcon,
  ClockIcon,
  CalendarIcon,
  StarIcon,
  HashtagIcon,
  CurrencyDollarIcon,
  UserIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: String, default: '' },
  direction: { type: String, default: 'desc' },
  options: { type: Array, default: () => [] },
  defaultOption: { type: String, default: '' },
  placeholder: { type: String, default: 'Sort by' },
  theme: { type: String, default: 'light' },
  variant: { type: String, default: 'filled' }, // filled, outline
  position: { type: String, default: 'left' }, // left, right
  menuWidth: { type: Number, default: 220 },
  searchable: { type: Boolean, default: false },
  showReset: { type: Boolean, default: true },
  showDirection: { type: Boolean, default: true }
})

const emit = defineEmits(['update:modelValue', 'update:direction', 'change'])

const isOpen = ref(false)
const searchQuery = ref('')
const sortDirection = ref(props.direction)

const defaultOptions = [
  { value: 'newest', label: 'Newest First', icon: ClockIcon, direction: 'desc' },
  { value: 'oldest', label: 'Oldest First', icon: ClockIcon, direction: 'asc' },
  { value: 'name_asc', label: 'Name (A-Z)', icon: DocumentTextIcon, direction: 'asc' },
  { value: 'name_desc', label: 'Name (Z-A)', icon: DocumentTextIcon, direction: 'desc' },
  { value: 'price_low', label: 'Price: Low to High', icon: CurrencyDollarIcon, direction: 'asc', group: 'Price' },
  { value: 'price_high', label: 'Price: High to Low', icon: CurrencyDollarIcon, direction: 'desc', group: 'Price' },
  { value: 'rating', label: 'Highest Rated', icon: StarIcon, direction: 'desc', group: 'Rating' },
  { value: 'popular', label: 'Most Popular', icon: HashtagIcon, direction: 'desc' }
]

const allOptions = computed(() => 
  props.options.length > 0 ? props.options : defaultOptions
)

const filteredOptions = computed(() => {
  if (!searchQuery.value) return allOptions.value
  return allOptions.value.filter(o => 
    o.label.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const groupedOptions = computed(() => {
  const groups = []
  const ungrouped = []

  filteredOptions.value.forEach(option => {
    if (option.group) {
      let group = groups.find(g => g.label === option.group)
      if (!group) {
        group = { label: option.group, options: [] }
        groups.push(group)
      }
      group.options.push(option)
    } else {
      ungrouped.push(option)
    }
  })

  if (ungrouped.length > 0) {
    groups.unshift({ label: null, options: ungrouped })
  }

  return groups
})

const currentOption = computed(() => 
  allOptions.value.find(o => o.value === props.modelValue)
)

const currentLabel = computed(() => {
  if (!currentOption.value) return props.placeholder
  if (props.showDirection && currentOption.value.sortable !== false) {
    const dirLabel = sortDirection.value === 'asc' ? '↑' : '↓'
    return `${currentOption.value.label} ${dirLabel}`
  }
  return currentOption.value.label
})

const sortIcon = computed(() => {
  if (currentOption.value?.icon) return currentOption.value.icon
  return ArrowsUpDownIcon
})

const positionClasses = computed(() => 
  props.position === 'right' ? 'right-0' : 'left-0'
)

const isSelected = (option) => props.modelValue === option.value

const selectOption = (option) => {
  if (option.direction) {
    sortDirection.value = option.direction
  }
  emit('update:modelValue', option.value)
  emit('update:direction', sortDirection.value)
  emit('change', { value: option.value, direction: sortDirection.value, option })
  isOpen.value = false
}

const toggleDirection = () => {
  sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  emit('update:direction', sortDirection.value)
  emit('change', { value: props.modelValue, direction: sortDirection.value, option: currentOption.value })
}

const resetSort = () => {
  const defaultOpt = props.defaultOption || allOptions.value[0]?.value
  sortDirection.value = 'desc'
  emit('update:modelValue', defaultOpt)
  emit('update:direction', 'desc')
  emit('change', { value: defaultOpt, direction: 'desc', option: null })
  isOpen.value = false
}

// Sync direction prop
watch(() => props.direction, (val) => {
  sortDirection.value = val
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
