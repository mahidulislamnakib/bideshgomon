<template>
  <div class="w-full">
    <!-- Label -->
    <div v-if="label || showPercentage" class="flex justify-between items-center mb-1">
      <span v-if="label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ label }}
      </span>
      <span v-if="showPercentage" class="text-sm font-medium" :class="percentageColorClass">
        {{ displayValue }}%
      </span>
    </div>

    <!-- Progress Track -->
    <div 
      class="relative overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
      :class="heightClass"
      role="progressbar"
      :aria-valuenow="clampedValue"
      :aria-valuemin="0"
      :aria-valuemax="100"
      :aria-label="label || 'Progress'"
    >
      <!-- Progress Fill -->
      <div
        class="h-full rounded-full transition-all duration-500 ease-out"
        :class="[barColorClass, { 'animate-pulse': indeterminate }]"
        :style="barStyle"
      >
        <!-- Animated Stripes (optional) -->
        <div 
          v-if="striped" 
          class="absolute inset-0 bg-stripes opacity-20"
          :class="{ 'animate-stripes': animated }"
        />
      </div>

      <!-- Indeterminate Animation -->
      <div
        v-if="indeterminate"
        class="absolute inset-0 h-full rounded-full animate-indeterminate"
        :class="barColorClass"
        style="width: 30%"
      />

      <!-- Inner Label -->
      <span 
        v-if="showInnerLabel && !indeterminate && clampedValue > 15" 
        class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-white"
      >
        {{ displayValue }}%
      </span>
    </div>

    <!-- Helper Text -->
    <p v-if="helperText" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ helperText }}
    </p>

    <!-- Steps (if provided) -->
    <div v-if="steps.length > 0" class="flex justify-between mt-2">
      <div
        v-for="(step, index) in steps"
        :key="index"
        class="flex flex-col items-center"
      >
        <div
          class="w-3 h-3 rounded-full border-2 transition-colors"
          :class="getStepClass(index)"
        />
        <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ step }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Progress value (0-100)
  value: {
    type: Number,
    default: 0
  },
  // Maximum value (for custom ranges)
  max: {
    type: Number,
    default: 100
  },
  // Label text
  label: {
    type: String,
    default: ''
  },
  // Size variant
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(v)
  },
  // Color variant
  color: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'success', 'warning', 'danger', 'info', 'gray', 'gradient'].includes(v)
  },
  // Show percentage text
  showPercentage: {
    type: Boolean,
    default: true
  },
  // Show percentage inside bar
  showInnerLabel: {
    type: Boolean,
    default: false
  },
  // Striped pattern
  striped: {
    type: Boolean,
    default: false
  },
  // Animate stripes
  animated: {
    type: Boolean,
    default: false
  },
  // Indeterminate (loading) state
  indeterminate: {
    type: Boolean,
    default: false
  },
  // Helper text below progress
  helperText: {
    type: String,
    default: ''
  },
  // Step markers
  steps: {
    type: Array,
    default: () => []
  }
})

// Clamp value between 0 and 100
const clampedValue = computed(() => {
  const normalized = (props.value / props.max) * 100
  return Math.min(100, Math.max(0, normalized))
})

// Display value (rounded)
const displayValue = computed(() => Math.round(clampedValue.value))

// Height classes based on size
const heightClass = computed(() => {
  const sizes = {
    xs: 'h-1',
    sm: 'h-2',
    md: 'h-3',
    lg: 'h-4',
    xl: 'h-6'
  }
  return sizes[props.size]
})

// Bar color classes
const barColorClass = computed(() => {
  const colors = {
    primary: 'bg-blue-600 dark:bg-blue-500',
    success: 'bg-green-600 dark:bg-green-500',
    warning: 'bg-yellow-500 dark:bg-yellow-400',
    danger: 'bg-red-600 dark:bg-red-500',
    info: 'bg-cyan-500 dark:bg-cyan-400',
    gray: 'bg-gray-500 dark:bg-gray-400',
    gradient: 'bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500'
  }
  return colors[props.color]
})

// Percentage text color (changes based on progress)
const percentageColorClass = computed(() => {
  if (clampedValue.value >= 100) return 'text-green-600 dark:text-green-400'
  if (clampedValue.value >= 75) return 'text-blue-600 dark:text-blue-400'
  if (clampedValue.value >= 50) return 'text-yellow-600 dark:text-yellow-400'
  if (clampedValue.value >= 25) return 'text-orange-600 dark:text-orange-400'
  return 'text-gray-600 dark:text-gray-400'
})

// Bar style (width)
const barStyle = computed(() => {
  if (props.indeterminate) return { width: '0%' }
  return { width: `${clampedValue.value}%` }
})

// Get step marker class
function getStepClass(index) {
  const stepProgress = ((index + 1) / props.steps.length) * 100
  if (clampedValue.value >= stepProgress) {
    return 'bg-blue-600 border-blue-600 dark:bg-blue-500 dark:border-blue-500'
  }
  return 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600'
}
</script>

<style scoped>
/* Striped background pattern */
.bg-stripes {
  background-image: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.15) 25%,
    transparent 25%,
    transparent 50%,
    rgba(255, 255, 255, 0.15) 50%,
    rgba(255, 255, 255, 0.15) 75%,
    transparent 75%,
    transparent
  );
  background-size: 1rem 1rem;
}

/* Animated stripes */
@keyframes stripes {
  from {
    background-position: 1rem 0;
  }
  to {
    background-position: 0 0;
  }
}

.animate-stripes {
  animation: stripes 1s linear infinite;
}

/* Indeterminate animation */
@keyframes indeterminate {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(400%);
  }
}

.animate-indeterminate {
  animation: indeterminate 1.5s ease-in-out infinite;
}
</style>
