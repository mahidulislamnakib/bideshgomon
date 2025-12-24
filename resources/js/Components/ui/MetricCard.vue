<template>
  <div
    :class="[
      'relative overflow-hidden transition-all duration-300',
      variantClasses.container,
      clickable ? 'cursor-pointer hover:scale-[1.02]' : '',
      selected ? 'ring-2 ring-offset-2 ring-primary-500' : ''
    ]"
    @click="handleClick"
  >
    <!-- Background Pattern/Gradient -->
    <div
      v-if="showGradient"
      class="absolute inset-0 opacity-5"
      :class="variantClasses.gradient"
    />

    <!-- Sparkline Background -->
    <svg
      v-if="sparkline.length > 0"
      class="absolute bottom-0 left-0 right-0 h-16 opacity-20"
      preserveAspectRatio="none"
      viewBox="0 0 100 40"
    >
      <path
        :d="sparklinePath"
        fill="none"
        :class="variantClasses.sparkline"
        stroke-width="2"
      />
      <path
        :d="sparklineAreaPath"
        :class="variantClasses.sparklineArea"
      />
    </svg>

    <!-- Content -->
    <div class="relative z-10 p-5">
      <!-- Header -->
      <div class="flex items-start justify-between mb-3">
        <!-- Icon -->
        <div
          v-if="icon"
          :class="[
            'flex-shrink-0 rounded-xl p-2.5',
            variantClasses.iconBg
          ]"
        >
          <component :is="icon" :class="['w-5 h-5', variantClasses.icon]" />
        </div>

        <!-- Trend Badge -->
        <div
          v-if="trend !== undefined"
          :class="[
            'flex items-center gap-0.5 text-xs font-semibold px-2 py-1 rounded-full',
            trendClasses
          ]"
        >
          <component :is="trendIcon" class="w-3.5 h-3.5" />
          <span>{{ Math.abs(trend) }}%</span>
        </div>
      </div>

      <!-- Label -->
      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
        {{ label }}
      </p>

      <!-- Value with Animation -->
      <div class="flex items-baseline gap-2">
        <span
          ref="valueRef"
          :class="[
            'text-2xl sm:text-3xl font-bold tracking-tight',
            variantClasses.value
          ]"
        >
          {{ displayValue }}
        </span>
        <span v-if="suffix" class="text-sm font-medium text-gray-500 dark:text-gray-400">
          {{ suffix }}
        </span>
      </div>

      <!-- Comparison / Description -->
      <div v-if="comparison || description" class="mt-2">
        <p v-if="comparison" class="text-xs text-gray-500 dark:text-gray-400">
          <span :class="trendClasses.replace(/bg-\w+-\d+/, '').replace('rounded-full px-2 py-1', '')">
            {{ trend >= 0 ? '+' : '' }}{{ trend }}%
          </span>
          {{ comparison }}
        </p>
        <p v-else-if="description" class="text-xs text-gray-500 dark:text-gray-400">
          {{ description }}
        </p>
      </div>

      <!-- Progress Bar -->
      <div v-if="progress !== undefined" class="mt-3">
        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
          <span>{{ progressLabel || 'Progress' }}</span>
          <span>{{ progress }}%</span>
        </div>
        <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="variantClasses.progress"
            :style="{ width: `${progress}%` }"
          />
        </div>
      </div>

      <!-- Mini Chart / Data Points -->
      <div v-if="dataPoints.length > 0" class="mt-3 flex items-end justify-between gap-1 h-8">
        <div
          v-for="(point, index) in dataPoints"
          :key="index"
          class="flex-1 rounded-t transition-all duration-300"
          :class="variantClasses.bar"
          :style="{
            height: `${(point / maxDataPoint) * 100}%`,
            opacity: 0.4 + (index / dataPoints.length) * 0.6
          }"
          :title="`${point}`"
        />
      </div>
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 flex items-center justify-center backdrop-blur-sm"
    >
      <div class="w-6 h-6 border-2 border-gray-300 border-t-primary-500 rounded-full animate-spin" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ArrowUpIcon, ArrowDownIcon, MinusIcon } from '@heroicons/vue/20/solid'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  // Main value
  value: {
    type: [Number, String],
    required: true
  },
  // Previous value for comparison
  previousValue: {
    type: Number,
    default: undefined
  },
  // Label text
  label: {
    type: String,
    required: true
  },
  // Suffix text (e.g., "users", "৳")
  suffix: {
    type: String,
    default: ''
  },
  // Description text
  description: {
    type: String,
    default: ''
  },
  // Comparison text (e.g., "vs last month")
  comparison: {
    type: String,
    default: ''
  },
  // Icon component
  icon: {
    type: [Object, Function],
    default: null
  },
  // Color variant
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'primary', 'success', 'warning', 'danger', 'info'].includes(v)
  },
  // Trend percentage (override auto-calculation)
  trend: {
    type: Number,
    default: undefined
  },
  // Progress percentage (0-100)
  progress: {
    type: Number,
    default: undefined
  },
  // Progress label
  progressLabel: {
    type: String,
    default: ''
  },
  // Sparkline data
  sparkline: {
    type: Array,
    default: () => []
  },
  // Mini bar chart data
  dataPoints: {
    type: Array,
    default: () => []
  },
  // Format as currency
  isCurrency: {
    type: Boolean,
    default: false
  },
  // Show gradient background
  showGradient: {
    type: Boolean,
    default: true
  },
  // Animate value on mount
  animate: {
    type: Boolean,
    default: true
  },
  // Animation duration (ms)
  animationDuration: {
    type: Number,
    default: 1000
  },
  // Clickable card
  clickable: {
    type: Boolean,
    default: false
  },
  // Selected state
  selected: {
    type: Boolean,
    default: false
  },
  // Loading state
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const { formatCurrency } = useBangladeshFormat()

const valueRef = ref(null)
const animatedValue = ref(0)
const isAnimating = ref(false)

// Calculate trend from previous value
const calculatedTrend = computed(() => {
  if (props.trend !== undefined) return props.trend
  if (props.previousValue === undefined || props.previousValue === 0) return undefined
  const current = typeof props.value === 'number' ? props.value : parseFloat(props.value) || 0
  return Math.round(((current - props.previousValue) / props.previousValue) * 100)
})

// Display value (with animation)
const displayValue = computed(() => {
  const val = props.animate && isAnimating.value
    ? animatedValue.value
    : (typeof props.value === 'number' ? props.value : parseFloat(props.value) || 0)
  
  if (props.isCurrency) {
    return formatCurrency(val)
  }
  
  // Format large numbers
  if (typeof val === 'number') {
    if (val >= 1000000) {
      return `${(val / 1000000).toFixed(1)}M`
    } else if (val >= 1000) {
      return `${(val / 1000).toFixed(1)}K`
    }
    return val.toLocaleString('en-BD')
  }
  
  return val
})

// Max data point for bar chart scaling
const maxDataPoint = computed(() => {
  return Math.max(...props.dataPoints, 1)
})

// Sparkline SVG path
const sparklinePath = computed(() => {
  if (props.sparkline.length < 2) return ''
  
  const max = Math.max(...props.sparkline)
  const min = Math.min(...props.sparkline)
  const range = max - min || 1
  
  return props.sparkline
    .map((val, i) => {
      const x = (i / (props.sparkline.length - 1)) * 100
      const y = 40 - ((val - min) / range) * 35
      return `${i === 0 ? 'M' : 'L'} ${x} ${y}`
    })
    .join(' ')
})

// Sparkline area path
const sparklineAreaPath = computed(() => {
  if (!sparklinePath.value) return ''
  return `${sparklinePath.value} L 100 40 L 0 40 Z`
})

// Trend icon
const trendIcon = computed(() => {
  if (calculatedTrend.value === undefined) return MinusIcon
  if (calculatedTrend.value > 0) return ArrowUpIcon
  if (calculatedTrend.value < 0) return ArrowDownIcon
  return MinusIcon
})

// Trend classes
const trendClasses = computed(() => {
  if (calculatedTrend.value === undefined) return 'bg-gray-100 text-gray-600'
  if (calculatedTrend.value > 0) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
  if (calculatedTrend.value < 0) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
})

// Variant classes
const variantClasses = computed(() => {
  const variants = {
    default: {
      container: 'bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm',
      gradient: 'bg-gradient-to-br from-gray-500 to-gray-600',
      iconBg: 'bg-gray-100 dark:bg-gray-700',
      icon: 'text-gray-600 dark:text-gray-300',
      value: 'text-gray-900 dark:text-white',
      progress: 'bg-gray-500',
      bar: 'bg-gray-400 dark:bg-gray-500',
      sparkline: 'stroke-gray-400',
      sparklineArea: 'fill-gray-400/20'
    },
    primary: {
      container: 'bg-white dark:bg-gray-800 rounded-xl border border-primary-200 dark:border-primary-800 shadow-sm',
      gradient: 'bg-gradient-to-br from-primary-500 to-primary-600',
      iconBg: 'bg-primary-100 dark:bg-primary-900/30',
      icon: 'text-primary-600 dark:text-primary-400',
      value: 'text-gray-900 dark:text-white',
      progress: 'bg-primary-500',
      bar: 'bg-primary-400 dark:bg-primary-500',
      sparkline: 'stroke-primary-400',
      sparklineArea: 'fill-primary-400/20'
    },
    success: {
      container: 'bg-white dark:bg-gray-800 rounded-xl border border-green-200 dark:border-green-800 shadow-sm',
      gradient: 'bg-gradient-to-br from-green-500 to-green-600',
      iconBg: 'bg-green-100 dark:bg-green-900/30',
      icon: 'text-green-600 dark:text-green-400',
      value: 'text-gray-900 dark:text-white',
      progress: 'bg-green-500',
      bar: 'bg-green-400 dark:bg-green-500',
      sparkline: 'stroke-green-400',
      sparklineArea: 'fill-green-400/20'
    },
    warning: {
      container: 'bg-white dark:bg-gray-800 rounded-xl border border-yellow-200 dark:border-yellow-800 shadow-sm',
      gradient: 'bg-gradient-to-br from-yellow-500 to-yellow-600',
      iconBg: 'bg-yellow-100 dark:bg-yellow-900/30',
      icon: 'text-yellow-600 dark:text-yellow-400',
      value: 'text-gray-900 dark:text-white',
      progress: 'bg-yellow-500',
      bar: 'bg-yellow-400 dark:bg-yellow-500',
      sparkline: 'stroke-yellow-400',
      sparklineArea: 'fill-yellow-400/20'
    },
    danger: {
      container: 'bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-800 shadow-sm',
      gradient: 'bg-gradient-to-br from-red-500 to-red-600',
      iconBg: 'bg-red-100 dark:bg-red-900/30',
      icon: 'text-red-600 dark:text-red-400',
      value: 'text-gray-900 dark:text-white',
      progress: 'bg-red-500',
      bar: 'bg-red-400 dark:bg-red-500',
      sparkline: 'stroke-red-400',
      sparklineArea: 'fill-red-400/20'
    },
    info: {
      container: 'bg-white dark:bg-gray-800 rounded-xl border border-blue-200 dark:border-blue-800 shadow-sm',
      gradient: 'bg-gradient-to-br from-blue-500 to-blue-600',
      iconBg: 'bg-blue-100 dark:bg-blue-900/30',
      icon: 'text-blue-600 dark:text-blue-400',
      value: 'text-gray-900 dark:text-white',
      progress: 'bg-blue-500',
      bar: 'bg-blue-400 dark:bg-blue-500',
      sparkline: 'stroke-blue-400',
      sparklineArea: 'fill-blue-400/20'
    }
  }
  return variants[props.variant]
})

// Animate value on mount
function animateValue() {
  if (!props.animate) return
  
  const target = typeof props.value === 'number' ? props.value : parseFloat(props.value) || 0
  const duration = props.animationDuration
  const start = 0
  const startTime = performance.now()
  
  isAnimating.value = true
  
  function update(currentTime) {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    
    // Easing function (ease-out)
    const eased = 1 - Math.pow(1 - progress, 3)
    
    animatedValue.value = Math.round(start + (target - start) * eased)
    
    if (progress < 1) {
      requestAnimationFrame(update)
    } else {
      isAnimating.value = false
    }
  }
  
  requestAnimationFrame(update)
}

function handleClick() {
  if (props.clickable) {
    emit('click')
  }
}

onMounted(() => {
  animateValue()
})

watch(() => props.value, () => {
  animateValue()
})
</script>
