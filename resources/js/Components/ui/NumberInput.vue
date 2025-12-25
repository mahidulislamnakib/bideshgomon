<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium mb-1.5"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <div class="relative flex items-center">
      <!-- Prefix -->
      <span
        v-if="prefix"
        class="absolute left-3 text-gray-500 text-sm pointer-events-none"
      >
        {{ prefix }}
      </span>

      <!-- Decrement button -->
      <button
        v-if="showControls"
        type="button"
        :disabled="disabled || isAtMin"
        class="absolute left-0 h-full px-3 rounded-l-lg border-r border-gray-300 text-gray-500 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-600"
        @click="decrement"
      >
        <MinusIcon class="h-4 w-4" />
      </button>

      <!-- Input -->
      <input
        :id="inputId"
        ref="inputRef"
        type="text"
        inputmode="decimal"
        :value="displayValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        class="block w-full rounded-lg border-0 py-2.5 text-center text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500"
        :class="[
          inputClasses,
          { 'pl-10': prefix, 'pr-10': suffix },
          { 'px-12': showControls }
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @keydown.up.prevent="increment"
        @keydown.down.prevent="decrement"
      />

      <!-- Increment button -->
      <button
        v-if="showControls"
        type="button"
        :disabled="disabled || isAtMax"
        class="absolute right-0 h-full px-3 rounded-r-lg border-l border-gray-300 text-gray-500 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-600"
        @click="increment"
      >
        <PlusIcon class="h-4 w-4" />
      </button>

      <!-- Suffix -->
      <span
        v-if="suffix && !showControls"
        class="absolute right-3 text-gray-500 text-sm pointer-events-none"
      >
        {{ suffix }}
      </span>
    </div>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-1.5 text-sm"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { PlusIcon, MinusIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '0'
  },
  min: {
    type: Number,
    default: -Infinity
  },
  max: {
    type: Number,
    default: Infinity
  },
  step: {
    type: Number,
    default: 1
  },
  precision: {
    type: Number,
    default: null // null = auto
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
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
  showControls: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  formatThousands: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Refs
const inputRef = ref(null)
const inputId = `number-${Math.random().toString(36).substring(2, 9)}`

// Internal value
const internalValue = ref(props.modelValue)

// Watch external changes
watch(() => props.modelValue, (newVal) => {
  internalValue.value = newVal
})

// Display value with formatting
const displayValue = computed(() => {
  if (internalValue.value === null || internalValue.value === '') return ''
  
  let value = Number(internalValue.value)
  if (isNaN(value)) return ''
  
  // Apply precision
  if (props.precision !== null) {
    value = value.toFixed(props.precision)
  }
  
  // Format with thousand separators
  if (props.formatThousands) {
    const parts = value.toString().split('.')
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    return parts.join('.')
  }
  
  return value.toString()
})

// Check bounds
const isAtMin = computed(() => {
  const current = Number(internalValue.value) || 0
  return current <= props.min
})

const isAtMax = computed(() => {
  const current = Number(internalValue.value) || 0
  return current >= props.max
})

// Handle input
function handleInput(event) {
  let value = event.target.value
  
  // Remove formatting
  value = value.replace(/,/g, '')
  
  // Allow empty, minus sign, and decimal point during typing
  if (value === '' || value === '-' || value === '.') {
    internalValue.value = value
    return
  }
  
  // Parse number
  const parsed = parseFloat(value)
  if (!isNaN(parsed)) {
    internalValue.value = parsed
    emit('update:modelValue', parsed)
  }
}

// Handle blur - apply constraints
function handleBlur() {
  let value = Number(internalValue.value)
  
  if (isNaN(value) || internalValue.value === '') {
    value = null
  } else {
    // Clamp to min/max
    value = Math.max(props.min, Math.min(props.max, value))
    
    // Apply precision
    if (props.precision !== null) {
      value = parseFloat(value.toFixed(props.precision))
    }
  }
  
  internalValue.value = value
  emit('update:modelValue', value)
  emit('change', value)
}

// Increment/Decrement
function increment() {
  if (props.disabled || props.readonly) return
  
  let current = Number(internalValue.value) || 0
  let newValue = current + props.step
  
  // Apply max constraint
  newValue = Math.min(props.max, newValue)
  
  // Apply precision
  if (props.precision !== null) {
    newValue = parseFloat(newValue.toFixed(props.precision))
  }
  
  internalValue.value = newValue
  emit('update:modelValue', newValue)
  emit('change', newValue)
}

function decrement() {
  if (props.disabled || props.readonly) return
  
  let current = Number(internalValue.value) || 0
  let newValue = current - props.step
  
  // Apply min constraint
  newValue = Math.max(props.min, newValue)
  
  // Apply precision
  if (props.precision !== null) {
    newValue = parseFloat(newValue.toFixed(props.precision))
  }
  
  internalValue.value = newValue
  emit('update:modelValue', newValue)
  emit('change', newValue)
}

// Container classes
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

// Input classes
const inputClasses = computed(() => {
  if (props.error) {
    return 'ring-red-300 focus:ring-red-500'
  }
  return ''
})

// Expose methods
defineExpose({
  focus: () => inputRef.value?.focus(),
  increment,
  decrement
})
</script>
