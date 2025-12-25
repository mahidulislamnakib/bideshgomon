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

    <div class="relative">
      <!-- Currency symbol prefix -->
      <span
        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
        :class="sizeClasses.symbol"
      >
        {{ currencySymbol }}
      </span>

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
        class="block w-full rounded-lg border-0 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500"
        :class="[
          sizeClasses.input,
          { 'ring-red-300 focus:ring-red-500': error }
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Currency code suffix (optional) -->
      <span
        v-if="showCode"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm"
      >
        {{ currencyCode }}
      </span>
    </div>

    <!-- Formatted display -->
    <p v-if="showFormatted && formattedValue" class="mt-1 text-sm text-gray-500">
      {{ formattedValue }}
    </p>

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
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

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
    default: '0.00'
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  currency: {
    type: String,
    default: 'BDT',
    validator: (value) => ['BDT', 'USD', 'EUR', 'GBP', 'SAR', 'AED', 'MYR'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: Infinity
  },
  precision: {
    type: Number,
    default: 2
  },
  showCode: {
    type: Boolean,
    default: false
  },
  showFormatted: {
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
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Composable
const { formatCurrency } = useBangladeshFormat()

// Refs
const inputRef = ref(null)
const inputId = `currency-${Math.random().toString(36).substring(2, 9)}`
const rawValue = ref(props.modelValue)
const isFocused = ref(false)

// Currency configurations
const currencies = {
  BDT: { symbol: '৳', code: 'BDT', locale: 'bn-BD' },
  USD: { symbol: '$', code: 'USD', locale: 'en-US' },
  EUR: { symbol: '€', code: 'EUR', locale: 'de-DE' },
  GBP: { symbol: '£', code: 'GBP', locale: 'en-GB' },
  SAR: { symbol: '﷼', code: 'SAR', locale: 'ar-SA' },
  AED: { symbol: 'د.إ', code: 'AED', locale: 'ar-AE' },
  MYR: { symbol: 'RM', code: 'MYR', locale: 'ms-MY' }
}

// Get currency config
const currencyConfig = computed(() => currencies[props.currency] || currencies.BDT)
const currencySymbol = computed(() => currencyConfig.value.symbol)
const currencyCode = computed(() => currencyConfig.value.code)

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  rawValue.value = newVal
})

// Display value (formatted when not focused)
const displayValue = computed(() => {
  if (rawValue.value === null || rawValue.value === '') return ''
  
  const num = parseFloat(rawValue.value)
  if (isNaN(num)) return ''
  
  if (isFocused.value) {
    // Show raw number when focused
    return num.toString()
  }
  
  // Format with thousand separators when not focused
  return num.toLocaleString('en-US', {
    minimumFractionDigits: props.precision,
    maximumFractionDigits: props.precision
  })
})

// Full formatted value for display
const formattedValue = computed(() => {
  if (rawValue.value === null || rawValue.value === '') return ''
  
  const num = parseFloat(rawValue.value)
  if (isNaN(num)) return ''
  
  // Use Bangladesh format for BDT
  if (props.currency === 'BDT') {
    return formatCurrency(num)
  }
  
  // Use native Intl for other currencies
  return new Intl.NumberFormat(currencyConfig.value.locale, {
    style: 'currency',
    currency: props.currency,
    minimumFractionDigits: props.precision,
    maximumFractionDigits: props.precision
  }).format(num)
})

// Handle input
function handleInput(event) {
  let value = event.target.value
  
  // Remove non-numeric characters except decimal
  value = value.replace(/[^\d.]/g, '')
  
  // Ensure only one decimal point
  const parts = value.split('.')
  if (parts.length > 2) {
    value = parts[0] + '.' + parts.slice(1).join('')
  }
  
  // Limit decimal places
  if (parts.length === 2 && parts[1].length > props.precision) {
    value = parts[0] + '.' + parts[1].substring(0, props.precision)
  }
  
  // Parse to number
  const num = parseFloat(value)
  
  if (value === '' || value === '.') {
    rawValue.value = null
    emit('update:modelValue', null)
  } else if (!isNaN(num)) {
    rawValue.value = num
    emit('update:modelValue', num)
  }
}

// Handle blur
function handleBlur() {
  isFocused.value = false
  
  if (rawValue.value !== null) {
    let num = parseFloat(rawValue.value)
    
    // Apply constraints
    num = Math.max(props.min, Math.min(props.max, num))
    
    // Round to precision
    num = parseFloat(num.toFixed(props.precision))
    
    rawValue.value = num
    emit('update:modelValue', num)
    emit('change', num)
  }
}

// Handle focus
function handleFocus() {
  isFocused.value = true
  // Select all on focus
  inputRef.value?.select()
}

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: { input: 'py-1.5 pl-8 pr-3 text-sm', symbol: 'text-sm' },
    md: { input: 'py-2.5 pl-10 pr-4 text-base', symbol: 'text-base' },
    lg: { input: 'py-3 pl-12 pr-5 text-lg', symbol: 'text-lg' }
  }
  return sizes[props.size]
})

// Container classes
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

// Expose methods
defineExpose({
  focus: () => inputRef.value?.focus(),
  clear: () => {
    rawValue.value = null
    emit('update:modelValue', null)
  }
})
</script>
