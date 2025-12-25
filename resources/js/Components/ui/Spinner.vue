<template>
  <div
    role="status"
    :aria-label="label"
    class="inline-flex items-center justify-center"
    :class="containerClasses"
  >
    <!-- Circle spinner -->
    <svg
      v-if="variant === 'circle'"
      class="animate-spin"
      :class="[sizeClasses, colorClasses]"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        :stroke-width="strokeWidth"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>

    <!-- Dots spinner -->
    <div
      v-else-if="variant === 'dots'"
      class="flex items-center gap-1"
      :class="sizeClasses"
    >
      <span
        v-for="i in 3"
        :key="i"
        class="rounded-full animate-bounce"
        :class="[dotSizeClasses, colorClasses]"
        :style="{ animationDelay: `${(i - 1) * 150}ms` }"
      />
    </div>

    <!-- Pulse spinner -->
    <div
      v-else-if="variant === 'pulse'"
      class="relative"
      :class="sizeClasses"
    >
      <span
        class="absolute inset-0 rounded-full animate-ping opacity-75"
        :class="bgColorClasses"
      />
      <span
        class="relative block rounded-full"
        :class="[sizeClasses, bgColorClasses]"
      />
    </div>

    <!-- Bars spinner -->
    <div
      v-else-if="variant === 'bars'"
      class="flex items-end gap-0.5"
      :class="sizeClasses"
    >
      <span
        v-for="i in 4"
        :key="i"
        class="animate-pulse rounded-sm"
        :class="[barSizeClasses, colorClasses]"
        :style="{
          animationDelay: `${(i - 1) * 100}ms`,
          height: `${25 + (i * 15)}%`
        }"
      />
    </div>

    <!-- Ring spinner -->
    <div
      v-else-if="variant === 'ring'"
      class="relative"
      :class="sizeClasses"
    >
      <div
        class="absolute inset-0 rounded-full border-2 opacity-25"
        :class="borderColorClasses"
      />
      <div
        class="absolute inset-0 rounded-full border-2 border-t-transparent animate-spin"
        :class="borderColorClasses"
      />
    </div>

    <!-- Label -->
    <span
      v-if="showLabel && label"
      class="ml-2"
      :class="labelClasses"
    >
      {{ label }}
    </span>

    <span class="sr-only">{{ label }}</span>
  </div>
</template>

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
    default: 'circle',
    validator: (value) => ['circle', 'dots', 'pulse', 'bars', 'ring'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'gray', 'white', 'success', 'warning', 'error'].includes(value)
  },
  label: {
    type: String,
    default: 'Loading...'
  },
  showLabel: {
    type: Boolean,
    default: false
  }
})

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    xs: 'h-3 w-3',
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-12 w-12'
  }
  return sizes[props.size]
})

// Stroke width for circle variant
const strokeWidth = computed(() => {
  const widths = {
    xs: 4,
    sm: 4,
    md: 4,
    lg: 3,
    xl: 3
  }
  return widths[props.size]
})

// Dot size for dots variant
const dotSizeClasses = computed(() => {
  const sizes = {
    xs: 'h-1 w-1',
    sm: 'h-1.5 w-1.5',
    md: 'h-2 w-2',
    lg: 'h-2.5 w-2.5',
    xl: 'h-3 w-3'
  }
  return sizes[props.size]
})

// Bar size for bars variant
const barSizeClasses = computed(() => {
  const sizes = {
    xs: 'w-0.5',
    sm: 'w-1',
    md: 'w-1.5',
    lg: 'w-2',
    xl: 'w-2.5'
  }
  return sizes[props.size]
})

// Color classes (text color for SVG)
const colorClasses = computed(() => {
  const colors = {
    primary: 'text-primary-600',
    gray: 'text-gray-600',
    white: 'text-white',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600'
  }
  return colors[props.color]
})

// Background color classes
const bgColorClasses = computed(() => {
  const colors = {
    primary: 'bg-primary-600',
    gray: 'bg-gray-600',
    white: 'bg-white',
    success: 'bg-green-600',
    warning: 'bg-yellow-600',
    error: 'bg-red-600'
  }
  return colors[props.color]
})

// Border color classes
const borderColorClasses = computed(() => {
  const colors = {
    primary: 'border-primary-600',
    gray: 'border-gray-600',
    white: 'border-white',
    success: 'border-green-600',
    warning: 'border-yellow-600',
    error: 'border-red-600'
  }
  return colors[props.color]
})

// Label classes
const labelClasses = computed(() => {
  const sizes = {
    xs: 'text-xs',
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
    xl: 'text-lg'
  }
  return `${sizes[props.size]} ${colorClasses.value}`
})

// Container classes
const containerClasses = ''
</script>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-50%);
  }
}
</style>
