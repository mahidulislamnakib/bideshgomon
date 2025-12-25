<template>
  <div class="combobox relative" ref="containerRef">
    <!-- Input -->
    <div
      :class="[
        'flex items-center gap-2 px-4 py-3 rounded-xl border transition-all cursor-text',
        isOpen
          ? 'border-blue-500 ring-2 ring-blue-500/20'
          : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
        disabled && 'opacity-50 cursor-not-allowed',
        themeClasses
      ]"
      @click="focusInput"
    >
      <MagnifyingGlassIcon v-if="showSearchIcon" class="w-5 h-5 text-gray-400 flex-shrink-0" />
      
      <!-- Selected Tags (Multiple) -->
      <div v-if="multiple && selectedItems.length > 0" class="flex flex-wrap gap-1 flex-1">
        <span
          v-for="item in selectedItems"
          :key="item[valueKey]"
          class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md text-sm"
        >
          {{ item[labelKey] }}
          <XMarkIcon
            class="w-3 h-3 cursor-pointer hover:text-blue-800"
            @click.stop="removeItem(item)"
          />
        </span>
        <input
          ref="inputRef"
          v-model="query"
          type="text"
          :placeholder="selectedItems.length ? '' : placeholder"
          :disabled="disabled"
          class="flex-1 min-w-[60px] bg-transparent border-0 outline-none text-sm text-gray-900 dark:text-white placeholder-gray-400"
          @input="handleInput"
          @focus="openDropdown"
          @keydown="handleKeydown"
        />
      </div>
      
      <!-- Single Select Input -->
      <template v-else>
        <input
          ref="inputRef"
          v-model="displayQuery"
          type="text"
          :placeholder="placeholder"
          :disabled="disabled"
          class="flex-1 bg-transparent border-0 outline-none text-sm text-gray-900 dark:text-white placeholder-gray-400"
          @input="handleInput"
          @focus="openDropdown"
          @keydown="handleKeydown"
        />
      </template>
      
      <!-- Clear & Toggle -->
      <div class="flex items-center gap-1">
        <button
          v-if="clearable && hasValue"
          type="button"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          @click.stop="clearSelection"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
        <ChevronDownIcon
          :class="['w-5 h-5 text-gray-400 transition-transform', isOpen && 'rotate-180']"
        />
      </div>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-1"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 w-full mt-2 rounded-xl border shadow-xl max-h-64 overflow-auto',
          theme === 'dark' ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
        ]"
      >
        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-8">
          <div class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
        </div>

        <!-- Options -->
        <template v-else>
          <!-- Create Option -->
          <button
            v-if="allowCreate && query && !exactMatch"
            type="button"
            class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700 text-sm"
            @click="createOption"
          >
            <PlusIcon class="w-4 h-4 text-blue-500" />
            <span>Create "<span class="font-medium">{{ query }}</span>"</span>
          </button>

          <!-- Grouped Options -->
          <template v-if="grouped">
            <div v-for="group in groupedOptions" :key="group.label" class="py-1">
              <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                {{ group.label }}
              </div>
              <button
                v-for="(option, index) in group.options"
                :key="option[valueKey]"
                type="button"
                :class="[
                  'w-full flex items-center justify-between px-4 py-2 text-left text-sm transition-colors',
                  highlightedIndex === getGlobalIndex(group, index)
                    ? 'bg-blue-50 dark:bg-blue-900/20'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                  isSelected(option) && 'text-blue-600 dark:text-blue-400'
                ]"
                @click="selectOption(option)"
                @mouseenter="highlightedIndex = getGlobalIndex(group, index)"
              >
                <span>{{ option[labelKey] }}</span>
                <CheckIcon v-if="isSelected(option)" class="w-4 h-4" />
              </button>
            </div>
          </template>

          <!-- Flat Options -->
          <template v-else>
            <button
              v-for="(option, index) in filteredOptions"
              :key="option[valueKey]"
              type="button"
              :class="[
                'w-full flex items-center justify-between px-4 py-3 text-left text-sm transition-colors',
                highlightedIndex === index
                  ? 'bg-blue-50 dark:bg-blue-900/20'
                  : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                isSelected(option) && 'text-blue-600 dark:text-blue-400'
              ]"
              @click="selectOption(option)"
              @mouseenter="highlightedIndex = index"
            >
              <div class="flex items-center gap-3">
                <img
                  v-if="option.avatar"
                  :src="option.avatar"
                  :alt="option[labelKey]"
                  class="w-8 h-8 rounded-full object-cover"
                />
                <component
                  v-else-if="option.icon"
                  :is="option.icon"
                  class="w-5 h-5 text-gray-400"
                />
                <div>
                  <p class="font-medium">{{ option[labelKey] }}</p>
                  <p v-if="option.description" class="text-xs text-gray-500">{{ option.description }}</p>
                </div>
              </div>
              <CheckIcon v-if="isSelected(option)" class="w-4 h-4" />
            </button>
          </template>

          <!-- Empty State -->
          <div v-if="filteredOptions.length === 0 && !allowCreate" class="py-8 text-center">
            <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500">{{ emptyText }}</p>
          </div>
        </template>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import {
  MagnifyingGlassIcon,
  ChevronDownIcon,
  XMarkIcon,
  CheckIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: [String, Number, Array], default: null },
  options: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'value' },
  groupKey: { type: String, default: 'group' },
  placeholder: { type: String, default: 'Search or select...' },
  emptyText: { type: String, default: 'No results found' },
  multiple: { type: Boolean, default: false },
  grouped: { type: Boolean, default: false },
  clearable: { type: Boolean, default: true },
  allowCreate: { type: Boolean, default: false },
  showSearchIcon: { type: Boolean, default: true },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  filterFn: { type: Function, default: null },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'change', 'search', 'create'])

const containerRef = ref(null)
const inputRef = ref(null)
const isOpen = ref(false)
const query = ref('')
const highlightedIndex = ref(-1)

const themeClasses = computed(() =>
  props.theme === 'dark' ? 'bg-gray-800' : 'bg-white'
)

const displayQuery = computed({
  get() {
    if (isOpen.value) return query.value
    const selected = props.options.find(o => o[props.valueKey] === props.modelValue)
    return selected ? selected[props.labelKey] : query.value
  },
  set(val) {
    query.value = val
  }
})

const selectedItems = computed(() => {
  if (!props.multiple || !Array.isArray(props.modelValue)) return []
  return props.modelValue.map(val =>
    props.options.find(o => o[props.valueKey] === val)
  ).filter(Boolean)
})

const hasValue = computed(() =>
  props.multiple
    ? Array.isArray(props.modelValue) && props.modelValue.length > 0
    : props.modelValue !== null && props.modelValue !== ''
)

const filteredOptions = computed(() => {
  if (!query.value) return props.options
  
  const q = query.value.toLowerCase()
  
  if (props.filterFn) {
    return props.options.filter(o => props.filterFn(o, q))
  }
  
  return props.options.filter(o =>
    o[props.labelKey].toLowerCase().includes(q)
  )
})

const groupedOptions = computed(() => {
  if (!props.grouped) return []
  
  const groups = {}
  filteredOptions.value.forEach(option => {
    const group = option[props.groupKey] || 'Other'
    if (!groups[group]) groups[group] = { label: group, options: [] }
    groups[group].options.push(option)
  })
  
  return Object.values(groups)
})

const exactMatch = computed(() =>
  props.options.some(o => o[props.labelKey].toLowerCase() === query.value.toLowerCase())
)

const getGlobalIndex = (group, localIndex) => {
  let globalIndex = 0
  for (const g of groupedOptions.value) {
    if (g === group) return globalIndex + localIndex
    globalIndex += g.options.length
  }
  return -1
}

const focusInput = () => {
  if (!props.disabled) {
    inputRef.value?.focus()
  }
}

const openDropdown = () => {
  if (!props.disabled) {
    isOpen.value = true
    highlightedIndex.value = -1
  }
}

const closeDropdown = () => {
  isOpen.value = false
  if (!props.multiple && hasValue.value) {
    query.value = ''
  }
}

const handleInput = () => {
  emit('search', query.value)
  highlightedIndex.value = 0
}

const handleKeydown = (e) => {
  const options = filteredOptions.value
  
  switch (e.key) {
    case 'ArrowDown':
      e.preventDefault()
      highlightedIndex.value = Math.min(highlightedIndex.value + 1, options.length - 1)
      break
    case 'ArrowUp':
      e.preventDefault()
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
      break
    case 'Enter':
      e.preventDefault()
      if (highlightedIndex.value >= 0 && options[highlightedIndex.value]) {
        selectOption(options[highlightedIndex.value])
      } else if (props.allowCreate && query.value && !exactMatch.value) {
        createOption()
      }
      break
    case 'Escape':
      closeDropdown()
      break
    case 'Backspace':
      if (!query.value && props.multiple && selectedItems.value.length) {
        removeItem(selectedItems.value[selectedItems.value.length - 1])
      }
      break
  }
}

const selectOption = (option) => {
  if (props.multiple) {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const value = option[props.valueKey]
    const index = current.indexOf(value)
    
    if (index > -1) {
      current.splice(index, 1)
    } else {
      current.push(value)
    }
    
    emit('update:modelValue', current)
    emit('change', current)
    query.value = ''
  } else {
    emit('update:modelValue', option[props.valueKey])
    emit('change', option[props.valueKey])
    query.value = ''
    closeDropdown()
  }
}

const removeItem = (item) => {
  if (!props.multiple) return
  const current = [...props.modelValue].filter(v => v !== item[props.valueKey])
  emit('update:modelValue', current)
  emit('change', current)
}

const clearSelection = () => {
  emit('update:modelValue', props.multiple ? [] : null)
  emit('change', props.multiple ? [] : null)
  query.value = ''
}

const createOption = () => {
  emit('create', query.value)
  query.value = ''
}

const isSelected = (option) => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.includes(option[props.valueKey])
  }
  return props.modelValue === option[props.valueKey]
}

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
