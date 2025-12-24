<script setup>
import { computed } from 'vue'
import { MinusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: [Boolean, Array],
    default: false
  },
  value: {
    type: [String, Number, Boolean],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  error: {
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
  indeterminate: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  labelPosition: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'right'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const checkboxId = computed(() => `checkbox-${Math.random().toString(36).substr(2, 9)}`)

const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(props.value)
  }
  return props.modelValue
})

const checkboxClasses = computed(() => {
  const base = 'rounded border-2 transition-all duration-200 focus:ring-2 focus:ring-offset-2 cursor-pointer'
  
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6'
  }
  
  let stateClasses = ''
  if (props.error) {
    stateClasses = 'border-red-500 text-red-600 focus:ring-red-500'
  } else if (props.disabled) {
    stateClasses = 'border-gray-300 bg-gray-100 cursor-not-allowed opacity-50'
  } else if (isChecked.value || props.indeterminate) {
    stateClasses = 'border-blue-600 bg-growth-600 text-white focus:ring-growth-600'
  } else {
    stateClasses = 'border-gray-300 hover:border-gray-400 focus:ring-growth-600 dark:border-gray-600'
  }
  
  return [base, sizeClasses[props.size], stateClasses].join(' ')
})

const labelClasses = computed(() => {
  const sizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  }
  
  const baseClasses = 'font-medium text-gray-900 dark:text-white'
  const disabledClasses = props.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
  
  return [baseClasses, sizeClasses[props.size], disabledClasses].join(' ')
})

const descriptionClasses = computed(() => {
  const sizeClasses = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  }
  
  return ['text-gray-500 dark:text-gray-400 mt-0.5', sizeClasses[props.size]].join(' ')
})

const handleChange = (event) => {
  if (props.disabled) return
  
  if (Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue]
    const index = newValue.indexOf(props.value)
    
    if (event.target.checked) {
      if (index === -1) newValue.push(props.value)
    } else {
      if (index > -1) newValue.splice(index, 1)
    }
    
    emit('update:modelValue', newValue)
    emit('change', newValue)
  } else {
    emit('update:modelValue', event.target.checked)
    emit('change', event.target.checked)
  }
}
</script>

<template>
  <div class="flex items-start">
    <!-- Checkbox input -->
    <div class="flex items-center" :class="labelPosition === 'left' ? 'order-2' : 'order-1'">
      <div class="relative">
        <input
          :id="checkboxId"
          type="checkbox"
          :checked="isChecked"
          :value="value"
          :disabled="disabled"
          :required="required"
          :aria-invalid="!!error"
          :aria-describedby="error ? `${checkboxId}-error` : description ? `${checkboxId}-description` : undefined"
          :class="checkboxClasses"
          @change="handleChange"
        />
        
        <!-- Indeterminate icon overlay -->
        <div
          v-if="indeterminate"
          class="absolute inset-0 flex items-center justify-center pointer-events-none"
        >
          <MinusIcon class="h-3 w-3 text-white" />
        </div>
      </div>
    </div>

    <!-- Label and description -->
    <div
      v-if="label || description"
      :class="[
        'flex-1',
        labelPosition === 'left' ? 'mr-3 text-right order-1' : 'ml-3 order-2'
      ]"
    >
      <label
        :for="checkboxId"
        :class="labelClasses"
      >
        {{ label }}
        <span v-if="required" class="text-red-500 ml-0.5">*</span>
      </label>
      
      <p
        v-if="description"
        :id="`${checkboxId}-description`"
        :class="descriptionClasses"
      >
        {{ description }}
      </p>
      
      <!-- Error message -->
      <p
        v-if="error"
        :id="`${checkboxId}-error`"
        class="mt-1 text-sm text-red-600 dark:text-red-400"
        role="alert"
      >
        {{ error }}
      </p>
    </div>
  </div>
</template>
