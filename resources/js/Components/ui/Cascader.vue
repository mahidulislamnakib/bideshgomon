<template>
  <div class="cascader relative" ref="containerRef">
    <!-- Trigger -->
    <button
      type="button"
      :class="[
        'w-full flex items-center justify-between gap-2 px-4 py-3 rounded-xl border transition-all',
        isOpen
          ? 'border-blue-500 ring-2 ring-blue-500/20'
          : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
        disabled && 'opacity-50 cursor-not-allowed',
        themeClasses
      ]"
      :disabled="disabled"
      @click="toggle"
    >
      <div class="flex-1 text-left min-w-0">
        <template v-if="selectedPath.length > 0">
          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(item, index) in selectedPath" :key="item[valueKey]">
              <span class="text-sm text-gray-900 dark:text-white">{{ item[labelKey] }}</span>
              <ChevronRightIcon v-if="index < selectedPath.length - 1" class="w-3 h-3 text-gray-400" />
            </template>
          </div>
        </template>
        <span v-else class="text-gray-400 text-sm">{{ placeholder }}</span>
      </div>
      <div class="flex items-center gap-1">
        <button
          v-if="clearable && selectedPath.length > 0"
          type="button"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          @click.stop="clearSelection"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
        <ChevronDownIcon :class="['w-5 h-5 text-gray-400 transition-transform', isOpen && 'rotate-180']" />
      </div>
    </button>

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
          'absolute z-50 mt-2 rounded-xl border shadow-xl overflow-hidden',
          theme === 'dark' ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
        ]"
      >
        <!-- Search -->
        <div v-if="searchable" class="p-2 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              ref="searchRef"
              v-model="searchQuery"
              type="text"
              placeholder="Search..."
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg"
            />
          </div>
        </div>

        <!-- Search Results -->
        <div v-if="searchQuery && searchResults.length > 0" class="max-h-64 overflow-auto">
          <button
            v-for="result in searchResults"
            :key="result.path.map(i => i[valueKey]).join('-')"
            type="button"
            class="w-full flex items-center gap-1 px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
            @click="selectSearchResult(result)"
          >
            <template v-for="(item, index) in result.path" :key="item[valueKey]">
              <span>{{ item[labelKey] }}</span>
              <ChevronRightIcon v-if="index < result.path.length - 1" class="w-3 h-3 text-gray-400" />
            </template>
          </button>
        </div>

        <!-- No Results -->
        <div v-else-if="searchQuery && searchResults.length === 0" class="py-8 text-center">
          <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
          <p class="text-sm text-gray-500">No results found</p>
        </div>

        <!-- Cascading Panels -->
        <div v-else class="flex">
          <div
            v-for="(panel, level) in panels"
            :key="level"
            class="min-w-[180px] max-h-64 overflow-auto border-r border-gray-200 dark:border-gray-700 last:border-r-0"
          >
            <button
              v-for="item in panel"
              :key="item[valueKey]"
              type="button"
              :class="[
                'w-full flex items-center justify-between px-4 py-2 text-left text-sm transition-colors',
                isItemSelected(item, level)
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                  : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                item.disabled && 'opacity-50 cursor-not-allowed'
              ]"
              :disabled="item.disabled"
              @click="selectItem(item, level)"
              @mouseenter="hoverItem(item, level)"
            >
              <span>{{ item[labelKey] }}</span>
              <ChevronRightIcon v-if="item[childrenKey]?.length" class="w-4 h-4 text-gray-400" />
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import {
  ChevronDownIcon,
  ChevronRightIcon,
  XMarkIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'value' },
  childrenKey: { type: String, default: 'children' },
  placeholder: { type: String, default: 'Select...' },
  clearable: { type: Boolean, default: true },
  searchable: { type: Boolean, default: true },
  expandTrigger: { type: String, default: 'click' }, // click or hover
  changeOnSelect: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'change'])

const containerRef = ref(null)
const searchRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const activePath = ref([])

const themeClasses = computed(() =>
  props.theme === 'dark' ? 'bg-gray-800' : 'bg-white'
)

const selectedPath = computed(() => {
  if (!props.modelValue?.length) return []
  
  const path = []
  let current = props.options
  
  for (const value of props.modelValue) {
    const item = current.find(i => i[props.valueKey] === value)
    if (item) {
      path.push(item)
      current = item[props.childrenKey] || []
    } else {
      break
    }
  }
  
  return path
})

const panels = computed(() => {
  const result = [props.options]
  
  for (const item of activePath.value) {
    if (item[props.childrenKey]?.length) {
      result.push(item[props.childrenKey])
    }
  }
  
  return result
})

const searchResults = computed(() => {
  if (!searchQuery.value) return []
  
  const results = []
  const query = searchQuery.value.toLowerCase()
  
  const search = (items, path = []) => {
    for (const item of items) {
      const newPath = [...path, item]
      
      if (item[props.labelKey].toLowerCase().includes(query)) {
        results.push({ item, path: newPath })
      }
      
      if (item[props.childrenKey]) {
        search(item[props.childrenKey], newPath)
      }
    }
  }
  
  search(props.options)
  return results.slice(0, 20)
})

const toggle = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    activePath.value = [...selectedPath.value]
    nextTick(() => searchRef.value?.focus())
  }
}

const isItemSelected = (item, level) => {
  return activePath.value[level]?.[props.valueKey] === item[props.valueKey]
}

const selectItem = (item, level) => {
  if (item.disabled) return
  
  activePath.value = activePath.value.slice(0, level)
  activePath.value.push(item)
  
  const hasChildren = item[props.childrenKey]?.length > 0
  
  if (!hasChildren || props.changeOnSelect) {
    const values = activePath.value.map(i => i[props.valueKey])
    emit('update:modelValue', values)
    emit('change', values, activePath.value)
    
    if (!hasChildren) {
      isOpen.value = false
    }
  }
}

const hoverItem = (item, level) => {
  if (props.expandTrigger !== 'hover') return
  if (item.disabled) return
  
  activePath.value = activePath.value.slice(0, level)
  activePath.value.push(item)
}

const selectSearchResult = (result) => {
  const values = result.path.map(i => i[props.valueKey])
  emit('update:modelValue', values)
  emit('change', values, result.path)
  isOpen.value = false
  searchQuery.value = ''
}

const clearSelection = () => {
  emit('update:modelValue', [])
  emit('change', [], [])
  activePath.value = []
}

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false
    searchQuery.value = ''
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(() => props.modelValue, () => {
  if (isOpen.value) {
    activePath.value = [...selectedPath.value]
  }
})
</script>
