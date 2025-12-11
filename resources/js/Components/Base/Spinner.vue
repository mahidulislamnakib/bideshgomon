<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'white', 'gray', 'success', 'warning', 'error'].includes(value)
  },
  type: {
    type: String,
    default: 'spinner',
    validator: (value) => ['spinner', 'dots', 'pulse', 'bars'].includes(value)
  },
  label: {
    type: String,
    default: ''
  }
})

const spinnerClasses = computed(() => {
  const sizeClasses = {
    xs: 'h-3 w-3',
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-12 w-12'
  }
  
  const variantClasses = {
    primary: 'text-blue-600 dark:text-blue-500',
    white: 'text-white',
    gray: 'text-gray-600 dark:text-gray-400',
    success: 'text-emerald-600 dark:text-emerald-500',
    warning: 'text-amber-600 dark:text-amber-500',
    error: 'text-red-600 dark:text-red-500'
  }
  
  return [sizeClasses[props.size], variantClasses[props.variant]].join(' ')
})

const borderSizeClasses = computed(() => {
  const sizes = {
    xs: 'border',
    sm: 'border-2',
    md: 'border-2',
    lg: 'border-4',
    xl: 'border-4'
  }
  
  return sizes[props.size]
})

const dotSizeClasses = computed(() => {
  const sizes = {
    xs: 'h-1.5 w-1.5',
    sm: 'h-2 w-2',
    md: 'h-3 w-3',
    lg: 'h-4 w-4',
    xl: 'h-5 w-5'
  }
  
  return sizes[props.size]
})

const barHeightClasses = computed(() => {
  const heights = {
    xs: 'h-3',
    sm: 'h-4',
    md: 'h-6',
    lg: 'h-8',
    xl: 'h-12'
  }
  
  return heights[props.size]
})
</script>

<template>
  <div class="inline-flex items-center space-x-2">
    <!-- Spinner type -->
    <div
      v-if="type === 'spinner'"
      :class="[spinnerClasses, borderSizeClasses]"
      class="animate-spin rounded-full border-current border-t-transparent"
      role="status"
      :aria-label="label || 'Loading'"
    >
      <span class="sr-only">{{ label || 'Loading...' }}</span>
    </div>

    <!-- Dots type -->
    <div
      v-else-if="type === 'dots'"
      class="flex space-x-1"
      role="status"
      :aria-label="label || 'Loading'"
    >
      <div
        v-for="i in 3"
        :key="i"
        :class="[dotSizeClasses, spinnerClasses]"
        class="rounded-full bg-current animate-pulse"
        :style="{ animationDelay: `${(i - 1) * 0.15}s` }"
      />
      <span class="sr-only">{{ label || 'Loading...' }}</span>
    </div>

    <!-- Pulse type -->
    <div
      v-else-if="type === 'pulse'"
      class="relative"
      role="status"
      :aria-label="label || 'Loading'"
    >
      <div :class="[spinnerClasses]" class="rounded-full bg-current opacity-75" />
      <div
        :class="[spinnerClasses]"
        class="absolute inset-0 rounded-full bg-current animate-ping"
      />
      <span class="sr-only">{{ label || 'Loading...' }}</span>
    </div>

    <!-- Bars type -->
    <div
      v-else-if="type === 'bars'"
      class="flex items-end space-x-1"
      role="status"
      :aria-label="label || 'Loading'"
    >
      <div
        v-for="i in 4"
        :key="i"
        :class="[barHeightClasses, spinnerClasses]"
        class="w-1 bg-current rounded-full animate-pulse"
        :style="{
          animationDelay: `${(i - 1) * 0.1}s`,
          height: `${25 + (i % 2) * 50}%`
        }"
      />
      <span class="sr-only">{{ label || 'Loading...' }}</span>
    </div>

    <!-- Label -->
    <span v-if="label" :class="spinnerClasses" class="text-sm font-medium">
      {{ label }}
    </span>
  </div>
</template>

