<template>
  <component
    :is="as"
    class="inline-flex items-center gap-2 transition-all duration-200"
    :class="[containerClasses, { 'cursor-pointer': !disabled }]"
    @click="copyToClipboard"
  >
    <!-- Content to display -->
    <slot>
      <span :class="textClasses">{{ displayText }}</span>
    </slot>

    <!-- Copy button/icon -->
    <button
      v-if="showButton"
      type="button"
      :disabled="disabled"
      class="inline-flex items-center justify-center rounded transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
      :class="buttonClasses"
      @click.stop="copyToClipboard"
    >
      <Transition mode="out-in">
        <CheckIcon
          v-if="copied"
          class="text-green-500"
          :class="iconSizeClasses"
        />
        <component
          v-else
          :is="copyIcon"
          :class="[iconSizeClasses, iconClasses]"
        />
      </Transition>
    </button>

    <!-- Tooltip feedback -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="showTooltip && copied"
        class="absolute z-10 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded shadow-lg"
        :class="tooltipPositionClasses"
      >
        {{ successMessage }}
        <div class="absolute w-2 h-2 bg-gray-900 transform rotate-45" :class="tooltipArrowClasses" />
      </div>
    </Transition>
  </component>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ClipboardIcon, ClipboardDocumentIcon, ClipboardDocumentCheckIcon, CheckIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  displayText: {
    type: String,
    default: ''
  },
  as: {
    type: String,
    default: 'div'
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'ghost', 'minimal'].includes(value)
  },
  iconStyle: {
    type: String,
    default: 'clipboard',
    validator: (value) => ['clipboard', 'document', 'simple'].includes(value)
  },
  showButton: {
    type: Boolean,
    default: true
  },
  showTooltip: {
    type: Boolean,
    default: true
  },
  tooltipPosition: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value)
  },
  successMessage: {
    type: String,
    default: 'Copied!'
  },
  successDuration: {
    type: Number,
    default: 2000
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['copy', 'error'])

// State
const copied = ref(false)
let copiedTimer = null

// Copy icon based on style
const copyIcon = computed(() => {
  const icons = {
    clipboard: ClipboardIcon,
    document: ClipboardDocumentIcon,
    simple: ClipboardDocumentCheckIcon
  }
  return icons[props.iconStyle]
})

// Copy to clipboard
async function copyToClipboard() {
  if (props.disabled || copied.value) return

  try {
    await navigator.clipboard.writeText(props.text)
    copied.value = true
    emit('copy', props.text)

    // Reset after duration
    if (copiedTimer) clearTimeout(copiedTimer)
    copiedTimer = setTimeout(() => {
      copied.value = false
    }, props.successDuration)
  } catch (err) {
    // Fallback for older browsers
    try {
      const textArea = document.createElement('textarea')
      textArea.value = props.text
      textArea.style.position = 'fixed'
      textArea.style.left = '-9999px'
      document.body.appendChild(textArea)
      textArea.select()
      document.execCommand('copy')
      document.body.removeChild(textArea)
      
      copied.value = true
      emit('copy', props.text)
      
      if (copiedTimer) clearTimeout(copiedTimer)
      copiedTimer = setTimeout(() => {
        copied.value = false
      }, props.successDuration)
    } catch (fallbackErr) {
      emit('error', fallbackErr)
    }
  }
}

// Container styling
const containerClasses = computed(() => {
  const base = props.disabled ? 'opacity-50 cursor-not-allowed' : ''
  const variants = {
    default: 'relative',
    outline: 'relative px-3 py-2 border border-gray-200 rounded-lg hover:border-gray-300',
    ghost: 'relative px-3 py-2 rounded-lg hover:bg-gray-100',
    minimal: 'relative'
  }
  return `${base} ${variants[props.variant]}`
})

// Text styling
const textClasses = computed(() => {
  const sizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  }
  return `font-mono text-gray-700 ${sizes[props.size]}`
})

// Button styling
const buttonClasses = computed(() => {
  const sizes = {
    sm: 'p-1',
    md: 'p-1.5',
    lg: 'p-2'
  }
  
  const base = copied.value
    ? 'bg-green-50 focus:ring-green-500'
    : 'hover:bg-gray-100 focus:ring-primary-500'
  
  return `${sizes[props.size]} ${base}`
})

// Icon sizing
const iconSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-3.5 w-3.5',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  }
  return sizes[props.size]
})

// Icon color
const iconClasses = computed(() => {
  return props.disabled ? 'text-gray-300' : 'text-gray-500 hover:text-gray-700'
})

// Tooltip position classes
const tooltipPositionClasses = computed(() => {
  const positions = {
    top: '-top-8 left-1/2 -translate-x-1/2',
    bottom: '-bottom-8 left-1/2 -translate-x-1/2',
    left: 'top-1/2 -translate-y-1/2 -left-16',
    right: 'top-1/2 -translate-y-1/2 -right-16'
  }
  return positions[props.tooltipPosition]
})

// Tooltip arrow classes
const tooltipArrowClasses = computed(() => {
  const arrows = {
    top: '-bottom-1 left-1/2 -translate-x-1/2',
    bottom: '-top-1 left-1/2 -translate-x-1/2',
    left: 'top-1/2 -translate-y-1/2 -right-1',
    right: 'top-1/2 -translate-y-1/2 -left-1'
  }
  return arrows[props.tooltipPosition]
})

// Expose copy method
defineExpose({
  copy: copyToClipboard,
  copied
})
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: all 0.15s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
