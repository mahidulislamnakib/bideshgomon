<template>
  <Listbox
    :model-value="modelValue"
    :multiple="multiple"
    :disabled="disabled"
    @update:model-value="handleChange"
  >
    <div class="relative" :class="containerClasses">
      <!-- Label -->
      <ListboxLabel
        v-if="label"
        class="block text-sm font-medium mb-1.5"
        :class="labelClasses"
      >
        {{ label }}
        <span v-if="required" class="text-red-500 ml-0.5">*</span>
      </ListboxLabel>

      <!-- Select button -->
      <ListboxButton
        class="relative w-full cursor-pointer rounded-lg border-0 py-2.5 pl-3 pr-10 text-left ring-1 ring-inset transition-all focus:outline-none focus:ring-2"
        :class="buttonClasses"
      >
        <!-- Selected value display -->
        <span class="flex items-center gap-2 truncate">
          <!-- Avatar/Icon for selected -->
          <img
            v-if="selectedOption?.avatar"
            :src="selectedOption.avatar"
            :alt="selectedOption.label"
            class="h-5 w-5 flex-shrink-0 rounded-full"
          />
          <component
            v-else-if="selectedOption?.icon"
            :is="selectedOption.icon"
            class="h-5 w-5 flex-shrink-0 text-gray-400"
          />
          
          <!-- Label -->
          <span :class="selectedOption ? 'text-gray-900' : 'text-gray-400'">
            {{ displayValue }}
          </span>
        </span>

        <!-- Dropdown icon -->
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" />
        </span>
      </ListboxButton>

      <!-- Options dropdown -->
      <Transition
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <ListboxOptions
          class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        >
          <!-- Search input -->
          <div v-if="searchable" class="sticky top-0 z-10 bg-white px-3 py-2">
            <input
              v-model="searchQuery"
              type="text"
              class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
              placeholder="Search..."
              @click.stop
            />
          </div>

          <!-- Empty state -->
          <div v-if="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500">
            {{ emptyMessage }}
          </div>

          <!-- Options list -->
          <ListboxOption
            v-for="option in filteredOptions"
            :key="option.value"
            v-slot="{ active, selected }"
            :value="option.value"
            :disabled="option.disabled"
            as="template"
          >
            <li
              class="relative cursor-pointer select-none py-2.5 pl-3 pr-9 transition-colors"
              :class="[
                active ? 'bg-primary-50 text-primary-900' : 'text-gray-900',
                option.disabled ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              <div class="flex items-center gap-2">
                <!-- Avatar/Icon -->
                <img
                  v-if="option.avatar"
                  :src="option.avatar"
                  :alt="option.label"
                  class="h-5 w-5 flex-shrink-0 rounded-full"
                />
                <component
                  v-else-if="option.icon"
                  :is="option.icon"
                  class="h-5 w-5 flex-shrink-0"
                  :class="active ? 'text-primary-600' : 'text-gray-400'"
                />

                <!-- Label & Description -->
                <div class="flex-1 truncate">
                  <span :class="selected ? 'font-medium' : 'font-normal'">
                    {{ option.label }}
                  </span>
                  <p v-if="option.description" class="text-xs text-gray-500 truncate">
                    {{ option.description }}
                  </p>
                </div>
              </div>

              <!-- Checkmark -->
              <span
                v-if="selected"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-primary-600"
              >
                <CheckIcon class="h-5 w-5" />
              </span>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </Transition>

      <!-- Error message -->
      <p v-if="error" class="mt-1.5 text-sm text-red-600">
        {{ error }}
      </p>

      <!-- Helper text -->
      <p v-else-if="helperText" class="mt-1.5 text-sm text-gray-500">
        {{ helperText }}
      </p>
    </div>
  </Listbox>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Listbox,
  ListboxButton,
  ListboxLabel,
  ListboxOptions,
  ListboxOption
} from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: null
  },
  options: {
    type: Array,
    required: true
    // [{ value, label, icon?, avatar?, description?, disabled? }]
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'No options found'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Search state
const searchQuery = ref('')

// Filtered options based on search
const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => 
    option.label.toLowerCase().includes(query) ||
    option.description?.toLowerCase().includes(query)
  )
})

// Get selected option object
const selectedOption = computed(() => {
  if (props.multiple) {
    return props.options.filter(o => props.modelValue?.includes(o.value))
  }
  return props.options.find(o => o.value === props.modelValue)
})

// Display value
const displayValue = computed(() => {
  if (props.multiple) {
    if (!props.modelValue?.length) return props.placeholder
    if (props.modelValue.length === 1) {
      return selectedOption.value[0]?.label
    }
    return `${props.modelValue.length} selected`
  }
  return selectedOption.value?.label || props.placeholder
})

// Handle change
function handleChange(value) {
  emit('update:modelValue', value)
  emit('change', value)
  searchQuery.value = '' // Reset search on selection
}

// Container styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

// Label styling
const labelClasses = computed(() => {
  if (props.error) return 'text-red-700'
  return 'text-gray-700'
})

// Button styling
const buttonClasses = computed(() => {
  const base = 'bg-white text-sm'
  
  if (props.error) {
    return `${base} ring-red-300 focus:ring-red-500`
  }
  
  if (props.disabled) {
    return `${base} ring-gray-200 bg-gray-50 cursor-not-allowed`
  }
  
  return `${base} ring-gray-300 focus:ring-primary-600`
})
</script>
