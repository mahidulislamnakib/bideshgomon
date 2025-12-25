<template>
  <div class="circular-progress" :class="[sizeClasses[size]]">
    <!-- SVG Circle -->
    <svg :viewBox="`0 0 ${viewBoxSize} ${viewBoxSize}`" class="transform -rotate-90">
      <!-- Background Circle -->
      <circle
        :cx="center"
        :cy="center"
        :r="radius"
        fill="none"
        :stroke="trackColor"
        :stroke-width="strokeWidth"
        class="transition-all duration-300"
      />
      
      <!-- Progress Circle(s) -->
      <circle
        v-for="(segment, index) in segments"
        :key="index"
        :cx="center"
        :cy="center"
        :r="radius"
        fill="none"
        :stroke="segment.color"
        :stroke-width="strokeWidth"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="segment.offset"
        :stroke-linecap="rounded ? 'round' : 'butt'"
        class="transition-all duration-500 ease-out"
        :style="{ transformOrigin: 'center', transform: `rotate(${segment.rotation}deg)` }"
      />
      
      <!-- Gradient Definition (if using gradient) -->
      <defs v-if="gradient">
        <linearGradient :id="gradientId" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" :stop-color="gradientColors[0]" />
          <stop offset="100%" :stop-color="gradientColors[1]" />
        </linearGradient>
      </defs>
    </svg>

    <!-- Center Content -->
    <div class="absolute inset-0 flex flex-col items-center justify-center">
      <slot name="center">
        <!-- Icon -->
        <component
          v-if="icon && !showValue"
          :is="icon"
          :class="[iconSizeClasses[size], 'text-gray-600 dark:text-gray-400']"
        />
        
        <!-- Value -->
        <div v-if="showValue" class="text-center">
          <span :class="[valueSizeClasses[size], 'font-bold', valueColorClass]">
            {{ displayValue }}
          </span>
          <span v-if="suffix" :class="[suffixSizeClasses[size], 'text-gray-500 dark:text-gray-400 ml-0.5']">
            {{ suffix }}
          </span>
        </div>
        
        <!-- Label -->
        <span
          v-if="label"
          :class="[labelSizeClasses[size], 'text-gray-500 dark:text-gray-400 mt-1']"
        >
          {{ label }}
        </span>
      </slot>
    </div>

    <!-- Status Indicator -->
    <div
      v-if="status"
      class="absolute -bottom-1 left-1/2 -translate-x-1/2 px-2 py-0.5 text-xs font-medium rounded-full"
      :class="statusClasses[status]"
    >
      {{ statusLabels[status] || status }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  value: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(v)
  },
  color: { type: String, default: '#3B82F6' },
  trackColor: { type: String, default: '#E5E7EB' },
  strokeWidth: { type: Number, default: 8 },
  rounded: { type: Boolean, default: true },
  showValue: { type: Boolean, default: true },
  suffix: { type: String, default: '%' },
  label: { type: String, default: '' },
  icon: { type: [Object, Function], default: null },
  gradient: { type: Boolean, default: false },
  gradientColors: {
    type: Array,
    default: () => ['#3B82F6', '#8B5CF6']
  },
  animated: { type: Boolean, default: true },
  indeterminate: { type: Boolean, default: false },
  status: {
    type: String,
    default: null,
    validator: (v) => !v || ['success', 'warning', 'error', 'info'].includes(v)
  },
  segments: {
    type: Array,
    default: null // Array of { value, color } for multi-segment progress
  },
  thickness: {
    type: String,
    default: 'normal',
    validator: (v) => ['thin', 'normal', 'thick'].includes(v)
  }
})

const gradientId = ref(`gradient-${Math.random().toString(36).substr(2, 9)}`)

const sizeClasses = {
  xs: 'w-12 h-12',
  sm: 'w-16 h-16',
  md: 'w-24 h-24',
  lg: 'w-32 h-32',
  xl: 'w-40 h-40',
  '2xl': 'w-48 h-48'
}

const valueSizeClasses = {
  xs: 'text-xs',
  sm: 'text-sm',
  md: 'text-xl',
  lg: 'text-2xl',
  xl: 'text-3xl',
  '2xl': 'text-4xl'
}

const suffixSizeClasses = {
  xs: 'text-[8px]',
  sm: 'text-xs',
  md: 'text-sm',
  lg: 'text-base',
  xl: 'text-lg',
  '2xl': 'text-xl'
}

const labelSizeClasses = {
  xs: 'text-[8px]',
  sm: 'text-xs',
  md: 'text-xs',
  lg: 'text-sm',
  xl: 'text-base',
  '2xl': 'text-lg'
}

const iconSizeClasses = {
  xs: 'w-4 h-4',
  sm: 'w-5 h-5',
  md: 'w-8 h-8',
  lg: 'w-10 h-10',
  xl: 'w-12 h-12',
  '2xl': 'w-16 h-16'
}

const statusClasses = {
  success: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
  warning: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
  error: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
  info: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
}

const statusLabels = {
  success: 'Complete',
  warning: 'Warning',
  error: 'Error',
  info: 'In Progress'
}

const thicknessMultiplier = {
  thin: 0.5,
  normal: 1,
  thick: 1.5
}

const viewBoxSize = 100
const center = viewBoxSize / 2
const effectiveStrokeWidth = computed(() => props.strokeWidth * thicknessMultiplier[props.thickness])
const radius = computed(() => (viewBoxSize - effectiveStrokeWidth.value) / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)

const percentage = computed(() => {
  if (props.indeterminate) return 25
  return Math.min(100, Math.max(0, (props.value / props.max) * 100))
})

const segments = computed(() => {
  if (props.segments?.length) {
    // Multi-segment mode
    let totalValue = props.segments.reduce((sum, s) => sum + s.value, 0)
    let currentRotation = 0
    
    return props.segments.map((segment) => {
      const segmentPercentage = (segment.value / totalValue) * 100
      const offset = circumference.value - (segmentPercentage / 100) * circumference.value
      const result = {
        color: segment.color,
        offset,
        rotation: currentRotation
      }
      currentRotation += (segmentPercentage / 100) * 360
      return result
    })
  }
  
  // Single segment mode
  const offset = circumference.value - (percentage.value / 100) * circumference.value
  return [{
    color: props.gradient ? `url(#${gradientId.value})` : props.color,
    offset,
    rotation: 0
  }]
})

const displayValue = computed(() => {
  if (props.indeterminate) return '...'
  return Math.round(props.value)
})

const valueColorClass = computed(() => {
  if (props.status) {
    return {
      success: 'text-green-600 dark:text-green-400',
      warning: 'text-yellow-600 dark:text-yellow-400',
      error: 'text-red-600 dark:text-red-400',
      info: 'text-blue-600 dark:text-blue-400'
    }[props.status]
  }
  return 'text-gray-900 dark:text-gray-100'
})
</script>

<style scoped>
.circular-progress {
  @apply relative inline-flex items-center justify-center;
}

.circular-progress svg {
  @apply w-full h-full;
}

/* Indeterminate animation */
.circular-progress:has([data-indeterminate="true"]) svg {
  animation: rotate 2s linear infinite;
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}
</style>
