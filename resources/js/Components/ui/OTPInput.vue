<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-2 text-center" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- OTP Input boxes -->
    <div class="flex justify-center" :class="gapClasses">
      <input
        v-for="(_, index) in length"
        :key="index"
        ref="inputRefs"
        type="text"
        inputmode="numeric"
        :maxlength="1"
        :value="digits[index]"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClasses(index)"
        @input="handleInput(index, $event)"
        @keydown="handleKeydown(index, $event)"
        @paste="handlePaste"
        @focus="handleFocus(index)"
      />
    </div>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-2 text-sm text-center"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>

    <!-- Resend timer -->
    <div v-if="showResend" class="mt-4 text-center">
      <button
        v-if="resendCountdown === 0"
        type="button"
        class="text-sm font-medium text-primary-600 hover:text-primary-700"
        :disabled="disabled"
        @click="handleResend"
      >
        Resend code
      </button>
      <p v-else class="text-sm text-gray-500">
        Resend code in <span class="font-medium text-gray-900">{{ resendCountdown }}s</span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  length: {
    type: Number,
    default: 6
  },
  label: {
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
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'outlined',
    validator: (value) => ['outlined', 'filled', 'underlined'].includes(value)
  },
  autoFocus: {
    type: Boolean,
    default: true
  },
  autoSubmit: {
    type: Boolean,
    default: true
  },
  showResend: {
    type: Boolean,
    default: false
  },
  resendTimeout: {
    type: Number,
    default: 60 // seconds
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
  mask: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'complete', 'resend'])

// Refs
const inputRefs = ref([])
const digits = ref(Array(props.length).fill(''))
const focusedIndex = ref(-1)
const resendCountdown = ref(0)
let resendTimer = null

// Initialize from modelValue
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    digits.value = newVal.split('').slice(0, props.length)
    while (digits.value.length < props.length) {
      digits.value.push('')
    }
  } else {
    digits.value = Array(props.length).fill('')
  }
}, { immediate: true })

// Auto-focus first input
onMounted(() => {
  if (props.autoFocus && inputRefs.value[0]) {
    nextTick(() => {
      inputRefs.value[0].focus()
    })
  }
  
  if (props.showResend) {
    startResendTimer()
  }
})

onUnmounted(() => {
  if (resendTimer) {
    clearInterval(resendTimer)
  }
})

// Handle input
function handleInput(index, event) {
  const value = event.target.value.replace(/\D/g, '') // Only digits
  
  if (value) {
    digits.value[index] = value.charAt(0)
    
    // Move to next input
    if (index < props.length - 1) {
      nextTick(() => {
        inputRefs.value[index + 1]?.focus()
      })
    }
    
    emitValue()
  }
}

// Handle keydown
function handleKeydown(index, event) {
  switch (event.key) {
    case 'Backspace':
      event.preventDefault()
      if (digits.value[index]) {
        digits.value[index] = ''
      } else if (index > 0) {
        digits.value[index - 1] = ''
        inputRefs.value[index - 1]?.focus()
      }
      emitValue()
      break
      
    case 'ArrowLeft':
      if (index > 0) {
        inputRefs.value[index - 1]?.focus()
      }
      break
      
    case 'ArrowRight':
      if (index < props.length - 1) {
        inputRefs.value[index + 1]?.focus()
      }
      break
      
    case 'Delete':
      digits.value[index] = ''
      emitValue()
      break
  }
}

// Handle paste
function handlePaste(event) {
  event.preventDefault()
  const pastedData = event.clipboardData.getData('text').replace(/\D/g, '')
  
  if (pastedData) {
    const pastedDigits = pastedData.split('').slice(0, props.length)
    pastedDigits.forEach((digit, i) => {
      digits.value[i] = digit
    })
    
    // Focus last filled or next empty
    const focusIndex = Math.min(pastedDigits.length, props.length - 1)
    nextTick(() => {
      inputRefs.value[focusIndex]?.focus()
    })
    
    emitValue()
  }
}

// Handle focus
function handleFocus(index) {
  focusedIndex.value = index
  // Select content on focus
  inputRefs.value[index]?.select()
}

// Emit value
function emitValue() {
  const value = digits.value.join('')
  emit('update:modelValue', value)
  
  // Auto-submit when complete
  if (props.autoSubmit && value.length === props.length) {
    emit('complete', value)
  }
}

// Resend timer
function startResendTimer() {
  resendCountdown.value = props.resendTimeout
  resendTimer = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) {
      clearInterval(resendTimer)
    }
  }, 1000)
}

// Handle resend
function handleResend() {
  emit('resend')
  startResendTimer()
}

// Clear OTP
function clear() {
  digits.value = Array(props.length).fill('')
  emit('update:modelValue', '')
  inputRefs.value[0]?.focus()
}

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

const gapClasses = computed(() => {
  const gaps = {
    sm: 'gap-2',
    md: 'gap-3',
    lg: 'gap-4'
  }
  return gaps[props.size]
})

function inputClasses(index) {
  const sizes = {
    sm: 'h-10 w-10 text-lg',
    md: 'h-12 w-12 text-xl',
    lg: 'h-14 w-14 text-2xl'
  }
  
  const variants = {
    outlined: 'rounded-lg border-2 bg-white',
    filled: 'rounded-lg border-0 bg-gray-100',
    underlined: 'rounded-none border-b-2 border-t-0 border-l-0 border-r-0 bg-transparent'
  }
  
  let stateClass = ''
  if (props.error) {
    stateClass = 'border-red-300 focus:border-red-500 focus:ring-red-500'
  } else if (focusedIndex.value === index) {
    stateClass = 'border-primary-500 ring-2 ring-primary-500/20'
  } else if (digits.value[index]) {
    stateClass = 'border-primary-300'
  } else {
    stateClass = 'border-gray-300'
  }
  
  const baseClasses = 'text-center font-semibold focus:outline-none transition-all disabled:bg-gray-50 disabled:cursor-not-allowed'
  
  return `${baseClasses} ${sizes[props.size]} ${variants[props.variant]} ${stateClass}`
}

// Expose methods
defineExpose({
  clear,
  focus: () => inputRefs.value[0]?.focus()
})
</script>
