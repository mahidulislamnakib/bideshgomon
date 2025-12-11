<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { ChevronDownIcon, XMarkIcon, MagnifyingGlassIcon, CheckIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: null
  },
  options: {
    type: Array,
    required: true,
    // Format: [{ value: 'val', label: 'Label', disabled: false }] or ['simple', 'strings']
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  error: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const searchQuery = ref('')
const selectRef = ref(null)
const searchInputRef = ref(null)
const selectId = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`)

// Normalize options to consistent format
const normalizedOptions = computed(() => {
  return props.options.map(option => {
    if (typeof option === 'object') {
      return {
        value: option.value,
        label: option.label || option.value,
        disabled: option.disabled || false,
        group: option.group || null
      }
    }
    return {
      value: option,
      label: option,
      disabled: false,
      group: null
    }
  })
})

// Filter options based on search
const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return normalizedOptions.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return normalizedOptions.value.filter(option =>
    option.label.toLowerCase().includes(query)
  )
})

// Group options if any have group property
const groupedOptions = computed(() => {
  const groups = {}
  const ungrouped = []
  
  filteredOptions.value.forEach(option => {
    if (option.group) {
      if (!groups[option.group]) {
        groups[option.group] = []
      }
      groups[option.group].push(option)
    } else {
      ungrouped.push(option)
    }
  })
  
  return { groups, ungrouped }
})

const hasGroups = computed(() => Object.keys(groupedOptions.value.groups).length > 0)

// Get selected option(s) label
const selectedLabel = computed(() => {
  if (props.multiple) {
    if (!props.modelValue || props.modelValue.length === 0) {
      return props.placeholder
    }
    const selected = normalizedOptions.value.filter(opt =>
      props.modelValue.includes(opt.value)
    )
    return selected.map(opt => opt.label).join(', ')
  } else {
    if (!props.modelValue) {
      return props.placeholder
    }
    const selected = normalizedOptions.value.find(opt => opt.value === props.modelValue)
    return selected ? selected.label : props.placeholder
  }
})

const isSelected = (value) => {
  if (props.multiple) {
    return props.modelValue && props.modelValue.includes(value)
  }
  return props.modelValue === value
}

const selectClasses = computed(() => {
  const base = 'w-full flex items-center justify-between rounded-lg border transition-colors cursor-pointer focus:outline-none focus:ring-2'
  
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2.5 text-base',
    lg: 'px-5 py-3 text-lg'
  }
  
  let stateClasses = ''
  if (props.error) {
    stateClasses = 'border-red-300 focus:border-red-500 focus:ring-red-500'
  } else if (props.disabled) {
    stateClasses = 'border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed'
  } else {
    stateClasses = 'border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700'
  }
  
  return [base, sizeClasses[props.size], stateClasses].join(' ')
})

const toggleDropdown = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value && props.searchable) {
    setTimeout(() => searchInputRef.value?.focus(), 50)
  }
}

const selectOption = (value) => {
  if (props.multiple) {
    const newValue = props.modelValue ? [...props.modelValue] : []
    const index = newValue.indexOf(value)
    
    if (index > -1) {
      newValue.splice(index, 1)
    } else {
      newValue.push(value)
    }
    
    emit('update:modelValue', newValue)
    emit('change', newValue)
  } else {
    emit('update:modelValue', value)
    emit('change', value)
    isOpen.value = false
  }
  
  searchQuery.value = ''
}

const clearSelection = () => {
  emit('update:modelValue', props.multiple ? [] : null)
  emit('change', props.multiple ? [] : null)
}

const handleClickOutside = (event) => {
  if (selectRef.value && !selectRef.value.contains(event.target)) {
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

watch(isOpen, (value) => {
  if (!value) {
    searchQuery.value = ''
  }
})
</script>

<template>
  <div ref="selectRef" class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Select button -->
    <div class="relative">
      <button
        :id="selectId"
        type="button"
        :class="selectClasses"
        :disabled="disabled"
        :aria-expanded="isOpen"
        :aria-haspopup="true"
        @click="toggleDropdown"
      >
        <span
          :class="[
            'truncate',
            !modelValue || (multiple && modelValue.length === 0)
              ? 'text-gray-400 dark:text-gray-500'
              : 'text-gray-900 dark:text-white'
          ]"
        >
          {{ selectedLabel }}
        </span>
        
        <div class="flex items-center space-x-2 ml-2">
          <button
            v-if="clearable && modelValue && (multiple ? modelValue.length > 0 : true)"
            type="button"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            @click.stop="clearSelection"
          >
            <XMarkIcon class="h-4 w-4" />
          </button>
          <ChevronDownIcon
            :class="['h-5 w-5 text-gray-400 transition-transform', isOpen && 'rotate-180']"
          />
        </div>
      </button>

      <!-- Dropdown -->
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-auto"
        >
          <!-- Search input -->
          <div v-if="searchable" class="p-2 border-b border-gray-200 dark:border-gray-700">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search..."
                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Options -->
          <div class="py-1">
            <!-- Grouped options -->
            <template v-if="hasGroups">
              <div
                v-for="(groupOptions, groupName) in groupedOptions.groups"
                :key="groupName"
              >
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  {{ groupName }}
                </div>
                <button
                  v-for="option in groupOptions"
                  :key="option.value"
                  type="button"
                  :disabled="option.disabled"
                  :class="[
                    'w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between',
                    isSelected(option.value) && 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                    option.disabled && 'opacity-50 cursor-not-allowed'
                  ]"
                  @click="!option.disabled && selectOption(option.value)"
                >
                  {{ option.label }}
                  <CheckIcon v-if="isSelected(option.value)" class="h-4 w-4" />
                </button>
              </div>
            </template>

            <!-- Ungrouped options -->
            <button
              v-for="option in hasGroups ? groupedOptions.ungrouped : filteredOptions"
              :key="option.value"
              type="button"
              :disabled="option.disabled"
              :class="[
                'w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between',
                isSelected(option.value) && 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                option.disabled && 'opacity-50 cursor-not-allowed'
              ]"
              @click="!option.disabled && selectOption(option.value)"
            >
              {{ option.label }}
              <CheckIcon v-if="isSelected(option.value)" class="h-4 w-4" />
            </button>

            <!-- No results -->
            <div
              v-if="filteredOptions.length === 0"
              class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center"
            >
              No options found
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Help text -->
    <p
      v-if="helpText && !error"
      class="mt-2 text-sm text-gray-500 dark:text-gray-400"
    >
      {{ helpText }}
    </p>

    <!-- Error message -->
    <p
      v-if="error"
      class="mt-2 text-sm text-red-600 dark:text-red-400"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
