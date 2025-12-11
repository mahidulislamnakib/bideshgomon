<script setup>
import { computed } from 'vue'
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: Object,
    default: null
  },
  variant: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'emerald', 'purple', 'amber', 'rose', 'cyan', 'indigo'].includes(value)
  },
  trend: {
    type: Object,
    default: null
    // Format: { direction: 'up', value: '+12.5%', label: 'from last month' }
  },
  description: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const variantClasses = computed(() => {
  const variants = {
    blue: {
      bg: 'bg-blue-50 dark:bg-blue-900/20',
      icon: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
      text: 'text-blue-600 dark:text-blue-400'
    },
    emerald: {
      bg: 'bg-emerald-50 dark:bg-emerald-900/20',
      icon: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
      text: 'text-emerald-600 dark:text-emerald-400'
    },
    purple: {
      bg: 'bg-purple-50 dark:bg-purple-900/20',
      icon: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
      text: 'text-purple-600 dark:text-purple-400'
    },
    amber: {
      bg: 'bg-amber-50 dark:bg-amber-900/20',
      icon: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
      text: 'text-amber-600 dark:text-amber-400'
    },
    rose: {
      bg: 'bg-rose-50 dark:bg-rose-900/20',
      icon: 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400',
      text: 'text-rose-600 dark:text-rose-400'
    },
    cyan: {
      bg: 'bg-cyan-50 dark:bg-cyan-900/20',
      icon: 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400',
      text: 'text-cyan-600 dark:text-cyan-400'
    },
    indigo: {
      bg: 'bg-indigo-50 dark:bg-indigo-900/20',
      icon: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400',
      text: 'text-indigo-600 dark:text-indigo-400'
    }
  }
  
  return variants[props.variant]
})

const trendClasses = computed(() => {
  if (!props.trend) return ''
  
  return props.trend.direction === 'up'
    ? 'text-emerald-600 dark:text-emerald-400'
    : 'text-red-600 dark:text-red-400'
})

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString()
  }
  return props.value
})
</script>

<template>
  <div class="relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm hover:shadow-md transition-shadow">
    <!-- Loading overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center z-10">
      <div class="h-8 w-8 border-4 border-gray-300 border-t-blue-600 rounded-full animate-spin" />
    </div>

    <div class="flex items-start justify-between">
      <div class="flex-1">
        <!-- Label -->
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
          {{ label }}
        </p>

        <!-- Value -->
        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          {{ formattedValue }}
        </p>

        <!-- Trend -->
        <div v-if="trend" class="flex items-center space-x-1 text-sm font-medium" :class="trendClasses">
          <component :is="trend.direction === 'up' ? ArrowUpIcon : ArrowDownIcon" class="h-4 w-4" />
          <span>{{ trend.value }}</span>
          <span v-if="trend.label" class="text-gray-500 dark:text-gray-400 font-normal">
            {{ trend.label }}
          </span>
        </div>

        <!-- Description -->
        <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
          {{ description }}
        </p>
      </div>

      <!-- Icon -->
      <div v-if="icon" :class="[variantClasses.icon, 'rounded-lg p-3']">
        <component :is="icon" class="h-6 w-6" />
      </div>
    </div>

    <!-- Additional content slot -->
    <div v-if="$slots.default" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
      <slot />
    </div>
  </div>
</template>
