<template>
  <component
    :is="clickable ? 'button' : 'div'"
    class="relative overflow-hidden transition-all duration-200"
    :class="[containerClasses, { 'cursor-pointer hover:shadow-lg': clickable }]"
    :type="clickable ? 'button' : undefined"
    @click="clickable && $emit('click')"
  >
    <!-- Background pattern/decoration -->
    <div
      v-if="showDecoration"
      class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10"
      :class="decorationClasses"
    />

    <!-- Main content -->
    <div class="relative">
      <!-- Header: Icon + Title -->
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <!-- Icon -->
          <div
            v-if="icon"
            class="flex-shrink-0 rounded-lg p-2"
            :class="iconContainerClasses"
          >
            <component :is="icon" class="h-5 w-5" :class="iconClasses" />
          </div>

          <!-- Title & Description -->
          <div>
            <h3 class="text-sm font-medium" :class="titleClasses">
              {{ title }}
            </h3>
            <p v-if="description" class="text-xs mt-0.5" :class="descriptionClasses">
              {{ description }}
            </p>
          </div>
        </div>

        <!-- Actions/Menu slot -->
        <slot name="actions" />
      </div>

      <!-- Value -->
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span
            class="font-bold tracking-tight"
            :class="[valueClasses, valueSizeClasses]"
          >
            <span v-if="prefix" class="text-lg font-normal" :class="prefixClasses">{{ prefix }}</span>
            <slot name="value">{{ formattedValue }}</slot>
            <span v-if="suffix" class="text-lg font-normal" :class="suffixClasses">{{ suffix }}</span>
          </span>
        </div>

        <!-- Trend indicator -->
        <div v-if="trend !== null || trendLabel" class="flex items-center gap-2 mt-2">
          <span
            v-if="trend !== null"
            class="inline-flex items-center gap-0.5 text-sm font-medium"
            :class="trendClasses"
          >
            <component :is="trendIcon" class="h-4 w-4" />
            {{ Math.abs(trend) }}%
          </span>
          <span v-if="trendLabel" class="text-xs" :class="trendLabelClasses">
            {{ trendLabel }}
          </span>
        </div>
      </div>

      <!-- Progress bar -->
      <div v-if="progress !== null" class="mt-4">
        <div class="flex items-center justify-between text-xs mb-1">
          <span :class="progressLabelClasses">{{ progressLabel }}</span>
          <span :class="progressValueClasses">{{ progress }}%</span>
        </div>
        <div class="h-1.5 w-full rounded-full overflow-hidden" :class="progressBgClasses">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="progressBarClasses"
            :style="{ width: `${Math.min(100, Math.max(0, progress))}%` }"
          />
        </div>
      </div>

      <!-- Footer slot -->
      <div v-if="$slots.footer" class="mt-4 pt-4 border-t" :class="footerBorderClasses">
        <slot name="footer" />
      </div>
    </div>

    <!-- Loading overlay -->
    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center bg-white/80"
    >
      <svg class="h-6 w-6 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { ArrowUpIcon, ArrowDownIcon, MinusIcon } from '@heroicons/vue/20/solid'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  value: {
    type: [Number, String],
    required: true
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
    type: String,
    default: ''
  },
  format: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'currency', 'percentage', 'none'].includes(value)
  },
  trend: {
    type: Number,
    default: null
  },
  trendLabel: {
    type: String,
    default: ''
  },
  progress: {
    type: Number,
    default: null
  },
  progressLabel: {
    type: String,
    default: 'Progress'
  },
  icon: {
    type: [Object, Function],
    default: null
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'warning', 'error', 'gradient'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  showDecoration: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

const { formatCurrency, formatNumber } = useBangladeshFormat()

// Format value
const formattedValue = computed(() => {
  if (props.format === 'none') return props.value
  if (props.format === 'currency') return formatCurrency(props.value)
  if (props.format === 'percentage') return `${props.value}%`
  if (typeof props.value === 'number') return formatNumber(props.value)
  return props.value
})

// Trend icon
const trendIcon = computed(() => {
  if (props.trend === null || props.trend === 0) return MinusIcon
  return props.trend > 0 ? ArrowUpIcon : ArrowDownIcon
})

// Container styling
const containerClasses = computed(() => {
  const base = 'rounded-xl p-5'
  const variants = {
    default: 'bg-white border border-gray-200',
    primary: 'bg-primary-50 border border-primary-100',
    success: 'bg-green-50 border border-green-100',
    warning: 'bg-yellow-50 border border-yellow-100',
    error: 'bg-red-50 border border-red-100',
    gradient: 'bg-gradient-to-br from-primary-500 to-primary-700 text-white'
  }
  return `${base} ${variants[props.variant]}`
})

// Icon container
const iconContainerClasses = computed(() => {
  const variants = {
    default: 'bg-gray-100',
    primary: 'bg-primary-100',
    success: 'bg-green-100',
    warning: 'bg-yellow-100',
    error: 'bg-red-100',
    gradient: 'bg-white/20'
  }
  return variants[props.variant]
})

// Icon color
const iconClasses = computed(() => {
  const variants = {
    default: 'text-gray-600',
    primary: 'text-primary-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600',
    gradient: 'text-white'
  }
  return variants[props.variant]
})

// Title styling
const titleClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white/90' : 'text-gray-600'
})

// Description styling
const descriptionClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white/70' : 'text-gray-500'
})

// Value styling
const valueClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white' : 'text-gray-900'
})

// Value size
const valueSizeClasses = computed(() => {
  const sizes = {
    sm: 'text-2xl',
    md: 'text-3xl',
    lg: 'text-4xl'
  }
  return sizes[props.size]
})

// Prefix/Suffix styling
const prefixClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white/80' : 'text-gray-500'
})

const suffixClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white/80' : 'text-gray-500'
})

// Trend styling
const trendClasses = computed(() => {
  if (props.trend === null || props.trend === 0) {
    return props.variant === 'gradient' ? 'text-white/70' : 'text-gray-500'
  }
  if (props.trend > 0) {
    return props.variant === 'gradient' ? 'text-green-200' : 'text-green-600'
  }
  return props.variant === 'gradient' ? 'text-red-200' : 'text-red-600'
})

const trendLabelClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white/70' : 'text-gray-500'
})

// Progress bar styling
const progressLabelClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white/70' : 'text-gray-500'
})

const progressValueClasses = computed(() => {
  return props.variant === 'gradient' ? 'text-white' : 'text-gray-700'
})

const progressBgClasses = computed(() => {
  return props.variant === 'gradient' ? 'bg-white/20' : 'bg-gray-200'
})

const progressBarClasses = computed(() => {
  const variants = {
    default: 'bg-primary-600',
    primary: 'bg-primary-600',
    success: 'bg-green-600',
    warning: 'bg-yellow-600',
    error: 'bg-red-600',
    gradient: 'bg-white'
  }
  return variants[props.variant]
})

// Footer border
const footerBorderClasses = computed(() => {
  return props.variant === 'gradient' ? 'border-white/20' : 'border-gray-200'
})

// Decoration
const decorationClasses = computed(() => {
  const variants = {
    default: 'bg-gray-400',
    primary: 'bg-primary-500',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    error: 'bg-red-500',
    gradient: 'bg-white'
  }
  return variants[props.variant]
})
</script>
