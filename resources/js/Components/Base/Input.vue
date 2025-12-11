<script setup>
import { computed, useAttrs } from 'vue'
import { ExclamationCircleIcon, CheckCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'email', 'password', 'number', 'tel', 'url', 'search', 'date', 'time'].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  iconLeft: {
    type: Object,
    default: null
  },
  iconRight: {
    type: Object,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'success', 'error'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])

const attrs = useAttrs()

const inputId = computed(() => {
  return attrs.id || `input-${Math.random().toString(36).substr(2, 9)}`
})

const inputClasses = computed(() => {
  const base = 'block w-full rounded-lg border transition-colors duration-200 focus:outline-none focus:ring-2'
  
  // Size classes
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2.5 text-base',
    lg: 'px-5 py-3 text-lg'
  }
  
  // Icon padding adjustments
  const iconPadding = {
    left: {
      sm: props.iconLeft ? 'pl-9' : '',
      md: props.iconLeft ? 'pl-10' : '',
      lg: props.iconLeft ? 'pl-12' : ''
    },
    right: {
      sm: props.iconRight ? 'pr-9' : '',
      md: props.iconRight ? 'pr-10' : '',
      lg: props.iconRight ? 'pr-12' : ''
    }
  }
  
  // State classes
  let stateClasses = ''
  if (props.error) {
    stateClasses = 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 dark:border-red-600 dark:text-red-400'
  } else if (props.variant === 'success') {
    stateClasses = 'border-emerald-300 text-emerald-900 focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-600 dark:text-emerald-400'
  } else if (props.disabled) {
    stateClasses = 'border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800'
  } else {
    stateClasses = 'border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400'
  }
  
  return [
    base,
    sizeClasses[props.size],
    iconPadding.left[props.size],
    iconPadding.right[props.size],
    stateClasses
  ].join(' ')
})

const iconClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6'
  }
  
  const positionClasses = {
    left: {
      sm: 'left-2.5',
      md: 'left-3',
      lg: 'left-4'
    },
    right: {
      sm: 'right-2.5',
      md: 'right-3',
      lg: 'right-4'
    }
  }
  
  return {
    size: sizeClasses[props.size],
    left: positionClasses.left[props.size],
    right: positionClasses.right[props.size]
  }
})

const statusIcon = computed(() => {
  if (props.error) return ExclamationCircleIcon
  if (props.variant === 'success') return CheckCircleIcon
  return null
})

const statusIconColor = computed(() => {
  if (props.error) return 'text-red-500 dark:text-red-400'
  if (props.variant === 'success') return 'text-emerald-500 dark:text-emerald-400'
  return ''
})

const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}
</script>

<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Input container -->
    <div class="relative">
      <!-- Left icon -->
      <div
        v-if="iconLeft"
        class="absolute inset-y-0 flex items-center pointer-events-none"
        :class="[iconClasses.left, 'text-gray-400 dark:text-gray-500']"
      >
        <component :is="iconLeft" :class="iconClasses.size" />
      </div>

      <!-- Input field -->
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="inputClasses"
        :aria-invalid="!!error"
        :aria-describedby="error ? `${inputId}-error` : helpText ? `${inputId}-help` : undefined"
        v-bind="$attrs"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
      />

      <!-- Right icon or status icon -->
      <div
        v-if="iconRight || statusIcon"
        class="absolute inset-y-0 flex items-center pointer-events-none"
        :class="[iconClasses.right, statusIcon ? statusIconColor : 'text-gray-400 dark:text-gray-500']"
      >
        <component :is="statusIcon || iconRight" :class="iconClasses.size" />
      </div>
    </div>

    <!-- Help text -->
    <p
      v-if="helpText && !error"
      :id="`${inputId}-help`"
      class="mt-2 text-sm text-gray-500 dark:text-gray-400"
    >
      <InformationCircleIcon class="inline h-4 w-4 mr-1" />
      {{ helpText }}
    </p>

    <!-- Error message -->
    <p
      v-if="error"
      :id="`${inputId}-error`"
      class="mt-2 text-sm text-red-600 dark:text-red-400"
      role="alert"
    >
      <ExclamationCircleIcon class="inline h-4 w-4 mr-1" />
      {{ error }}
    </p>
  </div>
</template>
