<script setup>
import { computed } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'warning', 'error', 'info', 'purple', 'pink'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  rounded: {
    type: Boolean,
    default: false
  },
  removable: {
    type: Boolean,
    default: false
  },
  icon: {
    type: Object,
    default: null
  },
  dot: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['remove'])

const badgeClasses = computed(() => {
  const base = 'inline-flex items-center font-medium transition-colors'
  
  const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
    lg: 'px-3 py-1.5 text-base'
  }
  
  const variantClasses = {
    default: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    primary: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    success: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    warning: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    error: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    info: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400',
    purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    pink: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400'
  }
  
  const roundedClass = props.rounded ? 'rounded-full' : 'rounded-md'
  
  return [
    base,
    sizeClasses[props.size],
    variantClasses[props.variant],
    roundedClass
  ].join(' ')
})

const iconClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  }
  
  return ['mr-1', sizeClasses[props.size]].join(' ')
})

const dotClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-1.5 w-1.5',
    md: 'h-2 w-2',
    lg: 'h-2.5 w-2.5'
  }
  
  const variantClasses = {
    default: 'bg-gray-500',
    primary: 'bg-blue-500',
    success: 'bg-emerald-500',
    warning: 'bg-amber-500',
    error: 'bg-red-500',
    info: 'bg-cyan-500',
    purple: 'bg-purple-500',
    pink: 'bg-pink-500'
  }
  
  return ['rounded-full mr-1.5', sizeClasses[props.size], variantClasses[props.variant]].join(' ')
})

const removeButtonClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-3 w-3',
    md: 'h-3.5 w-3.5',
    lg: 'h-4 w-4'
  }
  
  return ['ml-1.5 hover:opacity-70 cursor-pointer transition-opacity', sizeClasses[props.size]].join(' ')
})

const handleRemove = () => {
  emit('remove')
}
</script>

<template>
  <span :class="badgeClasses">
    <!-- Dot indicator -->
    <span v-if="dot" :class="dotClasses" aria-hidden="true" />
    
    <!-- Icon -->
    <component v-if="icon" :is="icon" :class="iconClasses" aria-hidden="true" />
    
    <!-- Content -->
    <slot />
    
    <!-- Remove button -->
    <button
      v-if="removable"
      type="button"
      :class="removeButtonClasses"
      aria-label="Remove"
      @click="handleRemove"
    >
      <XMarkIcon />
    </button>
  </span>
</template>
