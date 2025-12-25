<template>
  <div :class="containerClasses">
    <!-- Input wrapper -->
    <div :class="inputWrapperClasses">
      <!-- Leading icon -->
      <div v-if="$slots.leading || leadingIcon" :class="leadingClasses">
        <slot name="leading">
          <component :is="leadingIcon" class="w-5 h-5" />
        </slot>
      </div>
      
      <!-- Input -->
      <input
        v-if="type !== 'textarea'"
        ref="inputRef"
        :id="inputId"
        :type="type"
        :value="modelValue"
        :class="inputClasses"
        :placeholder="floatLabel ? ' ' : placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        v-bind="$attrs"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
      />
      
      <!-- Textarea -->
      <textarea
        v-else
        ref="inputRef"
        :id="inputId"
        :value="modelValue"
        :class="textareaClasses"
        :placeholder="floatLabel ? ' ' : placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        v-bind="$attrs"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
      />
      
      <!-- Floating label -->
      <label
        :for="inputId"
        :class="labelClasses"
      >
        {{ label }}
        <span v-if="required" class="text-red-500 ml-0.5">*</span>
      </label>
      
      <!-- Trailing icon -->
      <div v-if="$slots.trailing || trailingIcon || clearable" :class="trailingClasses">
        <button
          v-if="clearable && modelValue"
          type="button"
          class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600"
          @click="clear"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
        <slot v-else name="trailing">
          <component :is="trailingIcon" class="w-5 h-5" />
        </slot>
      </div>
    </div>
    
    <!-- Helper text -->
    <p v-if="helperText && !error" :class="helperClasses">
      {{ helperText }}
    </p>
    
    <!-- Error message -->
    <p v-if="error" :class="errorClasses">
      {{ error }}
    </p>
    
    <!-- Character count -->
    <p v-if="showCount && maxLength" :class="countClasses">
      {{ modelValue?.length || 0 }} / {{ maxLength }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, useId } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

defineOptions({
  inheritAttrs: false
})

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  
  // Label
  label: {
    type: String,
    required: true
  },
  
  // Float behavior
  floatLabel: {
    type: Boolean,
    default: true
  },
  
  // Input type
  type: {
    type: String,
    default: 'text'
  },
  
  // Placeholder (shown when not floating)
  placeholder: {
    type: String,
    default: ''
  },
  
  // Textarea rows
  rows: {
    type: Number,
    default: 3
  },
  
  // Icons
  leadingIcon: {
    type: [Object, Function],
    default: null
  },
  trailingIcon: {
    type: [Object, Function],
    default: null
  },
  
  // Validation
  error: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  
  // Features
  clearable: {
    type: Boolean,
    default: false
  },
  showCount: {
    type: Boolean,
    default: false
  },
  maxLength: {
    type: Number,
    default: null
  },
  
  // States
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
  
  // Variant
  variant: {
    type: String,
    default: 'outlined',
    validator: (v) => ['outlined', 'filled', 'underlined'].includes(v)
  },
  
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  }
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'clear'])

const inputRef = ref(null)
const isFocused = ref(false)
const inputId = useId()

// Check if label should float
const isFloating = computed(() => {
  return isFocused.value || props.modelValue || props.modelValue === 0
})

// Size config
const sizeConfig = computed(() => {
  const sizes = {
    sm: { input: 'text-sm py-2', label: 'text-sm', padding: 'px-3' },
    md: { input: 'text-base py-3', label: 'text-base', padding: 'px-4' },
    lg: { input: 'text-lg py-4', label: 'text-lg', padding: 'px-5' }
  }
  return sizes[props.size]
})

// Container classes
const containerClasses = computed(() => [
  'w-full'
])

// Input wrapper classes
const inputWrapperClasses = computed(() => [
  'relative flex items-center',
  props.variant === 'outlined' && [
    'border rounded-lg',
    props.error 
      ? 'border-red-500 dark:border-red-400' 
      : isFocused.value 
        ? 'border-primary-500 ring-2 ring-primary-500/20' 
        : 'border-gray-300 dark:border-gray-600',
    'bg-white dark:bg-gray-800'
  ],
  props.variant === 'filled' && [
    'rounded-t-lg border-b-2',
    'bg-gray-100 dark:bg-gray-700/50',
    props.error 
      ? 'border-red-500' 
      : isFocused.value 
        ? 'border-primary-500' 
        : 'border-gray-300 dark:border-gray-600'
  ],
  props.variant === 'underlined' && [
    'border-b-2',
    props.error 
      ? 'border-red-500' 
      : isFocused.value 
        ? 'border-primary-500' 
        : 'border-gray-300 dark:border-gray-600'
  ],
  'transition-all duration-200',
  props.disabled && 'opacity-50 cursor-not-allowed'
])

// Input classes
const inputClasses = computed(() => [
  'w-full bg-transparent outline-none',
  sizeConfig.value.input,
  sizeConfig.value.padding,
  'text-gray-900 dark:text-white',
  'placeholder:text-transparent',
  props.floatLabel && 'pt-6 pb-2',
  (props.$slots?.leading || props.leadingIcon) && 'pl-10',
  (props.$slots?.trailing || props.trailingIcon || props.clearable) && 'pr-10',
  props.disabled && 'cursor-not-allowed'
])

// Textarea classes
const textareaClasses = computed(() => [
  'w-full bg-transparent outline-none resize-none',
  sizeConfig.value.input,
  sizeConfig.value.padding,
  'text-gray-900 dark:text-white',
  'placeholder:text-transparent',
  props.floatLabel && 'pt-6 pb-2',
  props.disabled && 'cursor-not-allowed'
])

// Label classes
const labelClasses = computed(() => [
  'absolute left-0 pointer-events-none',
  'transition-all duration-200 ease-out',
  sizeConfig.value.padding,
  (props.$slots?.leading || props.leadingIcon) && 'left-8',
  // Floating state
  isFloating.value ? [
    'top-1 text-xs',
    props.error 
      ? 'text-red-500' 
      : isFocused.value 
        ? 'text-primary-600 dark:text-primary-400' 
        : 'text-gray-500 dark:text-gray-400'
  ] : [
    'top-1/2 -translate-y-1/2',
    sizeConfig.value.label,
    'text-gray-500 dark:text-gray-400'
  ]
])

// Leading icon classes
const leadingClasses = computed(() => [
  'absolute left-3',
  'text-gray-400 dark:text-gray-500',
  isFocused.value && 'text-primary-500'
])

// Trailing icon classes
const trailingClasses = computed(() => [
  'absolute right-3',
  'text-gray-400 dark:text-gray-500'
])

// Helper text classes
const helperClasses = computed(() => [
  'mt-1 text-xs text-gray-500 dark:text-gray-400'
])

// Error classes
const errorClasses = computed(() => [
  'mt-1 text-xs text-red-500 dark:text-red-400'
])

// Count classes
const countClasses = computed(() => [
  'mt-1 text-xs text-gray-400 dark:text-gray-500 text-right'
])

// Handlers
function handleInput(e) {
  let value = e.target.value
  
  if (props.maxLength && value.length > props.maxLength) {
    value = value.slice(0, props.maxLength)
    e.target.value = value
  }
  
  emit('update:modelValue', value)
}

function handleFocus(e) {
  isFocused.value = true
  emit('focus', e)
}

function handleBlur(e) {
  isFocused.value = false
  emit('blur', e)
}

function clear() {
  emit('update:modelValue', '')
  emit('clear')
  inputRef.value?.focus()
}

// Focus method
function focus() {
  inputRef.value?.focus()
}

function blur() {
  inputRef.value?.blur()
}

// Expose
defineExpose({
  focus,
  blur,
  inputRef
})
</script>
