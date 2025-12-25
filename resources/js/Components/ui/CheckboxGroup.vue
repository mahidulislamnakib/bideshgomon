<template>
  <fieldset :disabled="disabled">
    <!-- Label -->
    <legend v-if="label" class="block text-sm font-medium mb-3" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </legend>

    <!-- Description -->
    <p v-if="description" class="text-sm text-gray-500 mb-4">
      {{ description }}
    </p>

    <!-- Options -->
    <div :class="layoutClasses">
      <div
        v-for="option in options"
        :key="option.value"
        :class="[
          variant === 'card' ? cardClasses(option) : 'relative flex items-start py-2',
          option.disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      >
        <!-- Card variant -->
        <template v-if="variant === 'card'">
          <label class="flex w-full cursor-pointer items-start gap-3">
            <input
              type="checkbox"
              :checked="isChecked(option.value)"
              :disabled="option.disabled || disabled"
              class="h-4 w-4 mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              @change="toggleOption(option.value)"
            />
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="font-medium" :class="isChecked(option.value) ? 'text-primary-900' : 'text-gray-900'">
                  {{ option.label }}
                </span>
                <span
                  v-if="option.badge"
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="option.badgeColor || 'bg-gray-100 text-gray-600'"
                >
                  {{ option.badge }}
                </span>
              </div>
              <p v-if="option.description" class="text-sm mt-1" :class="isChecked(option.value) ? 'text-primary-700' : 'text-gray-500'">
                {{ option.description }}
              </p>
            </div>
            <component
              v-if="option.icon"
              :is="option.icon"
              class="h-6 w-6 flex-shrink-0"
              :class="isChecked(option.value) ? 'text-primary-600' : 'text-gray-400'"
            />
          </label>
        </template>

        <!-- Simple variant -->
        <template v-else>
          <div class="flex h-5 items-center">
            <input
              :id="`checkbox-${uid}-${option.value}`"
              type="checkbox"
              :checked="isChecked(option.value)"
              :disabled="option.disabled || disabled"
              class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              @change="toggleOption(option.value)"
            />
          </div>
          <div class="ml-3 text-sm">
            <label
              :for="`checkbox-${uid}-${option.value}`"
              class="font-medium cursor-pointer"
              :class="isChecked(option.value) ? 'text-primary-700' : 'text-gray-900'"
            >
              {{ option.label }}
            </label>
            <p v-if="option.description" class="text-gray-500">
              {{ option.description }}
            </p>
          </div>
        </template>
      </div>
    </div>

    <!-- Select all / Clear all -->
    <div v-if="showSelectAll && options.length > 1" class="mt-3 flex gap-4">
      <button
        type="button"
        class="text-sm font-medium text-primary-600 hover:text-primary-700"
        @click="selectAll"
      >
        Select all
      </button>
      <button
        type="button"
        class="text-sm font-medium text-gray-600 hover:text-gray-700"
        @click="clearAll"
      >
        Clear all
      </button>
    </div>

    <!-- Error message -->
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>

    <!-- Helper text -->
    <p v-else-if="helperText" class="mt-2 text-sm text-gray-500">
      {{ helperText }}
    </p>
  </fieldset>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  options: {
    type: Array,
    required: true
    // [{ value, label, description?, icon?, badge?, badgeColor?, disabled? }]
  },
  label: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'simple',
    validator: (value) => ['simple', 'card'].includes(value)
  },
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal', 'grid'].includes(value)
  },
  columns: {
    type: Number,
    default: 2
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: Infinity
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  showSelectAll: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Unique ID for form elements
const uid = Math.random().toString(36).substring(2, 9)

// Check if option is selected
function isChecked(value) {
  return props.modelValue.includes(value)
}

// Toggle option
function toggleOption(value) {
  const currentIndex = props.modelValue.indexOf(value)
  const newValue = [...props.modelValue]

  if (currentIndex === -1) {
    // Check if we can add more
    if (newValue.length >= props.max) return
    newValue.push(value)
  } else {
    // Check if we can remove
    if (newValue.length <= props.min) return
    newValue.splice(currentIndex, 1)
  }

  emit('update:modelValue', newValue)
  emit('change', newValue)
}

// Select all
function selectAll() {
  const enabledOptions = props.options.filter(o => !o.disabled)
  const allValues = enabledOptions.map(o => o.value).slice(0, props.max)
  emit('update:modelValue', allValues)
  emit('change', allValues)
}

// Clear all
function clearAll() {
  const newValue = props.modelValue.slice(0, props.min)
  emit('update:modelValue', newValue)
  emit('change', newValue)
}

// Layout classes
const layoutClasses = computed(() => {
  if (props.variant === 'card') {
    const layouts = {
      vertical: 'space-y-3',
      horizontal: 'flex flex-wrap gap-3',
      grid: `grid gap-3 grid-cols-1 sm:grid-cols-${props.columns}`
    }
    return layouts[props.layout]
  }
  
  const layouts = {
    vertical: 'space-y-2',
    horizontal: 'flex flex-wrap gap-x-6 gap-y-2',
    grid: `grid gap-2 grid-cols-1 sm:grid-cols-${props.columns}`
  }
  return layouts[props.layout]
})

// Card classes
function cardClasses(option) {
  const base = 'relative flex cursor-pointer rounded-lg border p-4 transition-all focus-within:ring-2 focus-within:ring-primary-600 focus-within:ring-offset-2'
  const checked = isChecked(option.value)
  
  if (checked) {
    return `${base} border-primary-600 bg-primary-50`
  }
  return `${base} border-gray-200 bg-white hover:border-gray-300`
}

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})
</script>
