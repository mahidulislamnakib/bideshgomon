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
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-blue-500 to-blue-600',
      text: 'text-blue-600',
      badge: 'bg-blue-50 text-blue-600',
      border: 'from-blue-500 to-blue-600'
    },
    emerald: {
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-green-500 to-emerald-600',
      text: 'text-emerald-600',
      badge: 'bg-green-50 text-green-600',
      border: 'from-green-500 to-emerald-600'
    },
    purple: {
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-purple-500 to-purple-600',
      text: 'text-purple-600',
      badge: 'bg-purple-50 text-purple-600',
      border: 'from-purple-500 to-purple-600'
    },
    amber: {
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-amber-500 to-orange-600',
      text: 'text-amber-600',
      badge: 'bg-amber-50 text-amber-600',
      border: 'from-amber-500 to-orange-600'
    },
    rose: {
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-rose-500 to-rose-600',
      text: 'text-rose-600',
      badge: 'bg-rose-50 text-rose-600',
      border: 'from-rose-500 to-rose-600'
    },
    cyan: {
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-cyan-500 to-cyan-600',
      text: 'text-cyan-600',
      badge: 'bg-cyan-50 text-cyan-600',
      border: 'from-cyan-500 to-cyan-600'
    },
    indigo: {
      bg: 'bg-white',
      icon: 'bg-gradient-to-br from-indigo-500 to-indigo-600',
      text: 'text-indigo-600',
      badge: 'bg-indigo-50 text-indigo-600',
      border: 'from-indigo-500 to-indigo-600'
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
  <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
    <!-- Loading overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white/80 flex items-center justify-center z-10">
      <div class="h-8 w-8 border-4 border-gray-300 border-t-indigo-600 rounded-full animate-spin" />
    </div>

    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <!-- Icon with gradient -->
        <div v-if="icon" :class="[variantClasses.icon, 'p-4 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300']">
          <component :is="icon" class="h-8 w-8 text-white" />
        </div>
        
        <!-- Trend Badge -->
        <span v-if="trend" :class="[variantClasses.badge, 'text-xs font-semibold px-3 py-1 rounded-full']">
          {{ trend.direction === 'up' ? '↑' : '↓' }} {{ trend.label }}
        </span>
      </div>

      <!-- Label -->
      <h3 class="text-sm font-medium text-gray-600 mb-1">
        {{ label }}
      </h3>

      <!-- Value -->
      <p class="text-3xl font-bold text-gray-900 mb-2">
        {{ formattedValue }}
      </p>

      <!-- Trend Value -->
      <div v-if="trend" class="flex items-center gap-1 text-sm font-medium" :class="trendClasses">
        <component :is="trend.direction === 'up' ? ArrowUpIcon : ArrowDownIcon" class="h-4 w-4" />
        <span>{{ trend.value }}</span>
      </div>

      <!-- Description -->
      <p v-if="description" class="text-sm text-gray-500 mt-1">
        {{ description }}
      </p>

      <!-- Additional content slot -->
      <div v-if="$slots.default" class="mt-4 pt-4 border-t border-gray-200">
        <slot />
      </div>
    </div>

    <!-- Gradient bottom border -->
    <div :class="['h-1 bg-gradient-to-r', variantClasses.border]"></div>
  </div>
</template>
