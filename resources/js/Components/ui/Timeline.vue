<template>
  <div class="relative" :class="containerClass">
    <!-- Timeline Line -->
    <div
      v-if="!horizontal"
      class="absolute top-0 bottom-0 w-0.5"
      :class="[
        linePosition === 'left' ? 'left-4' : linePosition === 'right' ? 'right-4' : 'left-1/2 -translate-x-1/2',
        'bg-gray-200 dark:bg-gray-700'
      ]"
    />

    <!-- Timeline Items -->
    <div :class="horizontal ? 'flex gap-8 overflow-x-auto pb-4' : 'space-y-8'">
      <div
        v-for="(item, index) in items"
        :key="item.id || index"
        class="relative"
        :class="getItemClasses(index)"
      >
        <!-- Connector (horizontal mode) -->
        <div
          v-if="horizontal && index < items.length - 1"
          class="absolute top-4 left-8 right-0 h-0.5 bg-gray-200 dark:bg-gray-700"
          :class="{ 'bg-blue-500 dark:bg-blue-400': item.status === 'completed' }"
        />

        <!-- Timeline Dot/Icon -->
        <div
          class="relative z-10 flex items-center justify-center rounded-full border-2 transition-all"
          :class="getDotClasses(item)"
        >
          <!-- Custom Icon -->
          <component
            v-if="item.icon"
            :is="item.icon"
            class="w-4 h-4"
          />
          <!-- Status Icons -->
          <CheckIcon
            v-else-if="item.status === 'completed'"
            class="w-4 h-4"
          />
          <ClockIcon
            v-else-if="item.status === 'pending'"
            class="w-3 h-3"
          />
          <ExclamationTriangleIcon
            v-else-if="item.status === 'error'"
            class="w-4 h-4"
          />
          <!-- Default: number or dot -->
          <span
            v-else-if="showNumbers"
            class="text-xs font-semibold"
          >
            {{ index + 1 }}
          </span>
        </div>

        <!-- Content -->
        <div
          class="mt-2"
          :class="horizontal ? 'w-48' : getContentMarginClass"
        >
          <!-- Title -->
          <h4
            class="text-sm font-semibold"
            :class="getTitleClasses(item)"
          >
            {{ item.title }}
          </h4>

          <!-- Date/Time -->
          <p
            v-if="item.date"
            class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
          >
            {{ formatDate(item.date) }}
          </p>

          <!-- Description -->
          <p
            v-if="item.description"
            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
          >
            {{ item.description }}
          </p>

          <!-- Meta/Tags -->
          <div
            v-if="item.tags && item.tags.length > 0"
            class="mt-2 flex flex-wrap gap-1"
          >
            <span
              v-for="tag in item.tags"
              :key="tag"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
            >
              {{ tag }}
            </span>
          </div>

          <!-- Custom Content Slot -->
          <slot :name="`item-${index}`" :item="item" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CheckIcon,
  ClockIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/solid'

const props = defineProps({
  // Timeline items
  items: {
    type: Array,
    required: true,
    default: () => []
    // Each item: { id?, title, description?, date?, status?, icon?, color?, tags? }
  },
  // Horizontal layout
  horizontal: {
    type: Boolean,
    default: false
  },
  // Line position (vertical mode)
  linePosition: {
    type: String,
    default: 'left',
    validator: (v) => ['left', 'center', 'right'].includes(v)
  },
  // Show step numbers
  showNumbers: {
    type: Boolean,
    default: false
  },
  // Dot size
  dotSize: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  // Alternating sides (center mode)
  alternating: {
    type: Boolean,
    default: false
  },
  // Date format
  dateFormat: {
    type: String,
    default: 'short' // 'short', 'long', 'relative'
  }
})

// Container class
const containerClass = computed(() => {
  if (props.horizontal) return ''
  if (props.linePosition === 'center') return 'px-4'
  return ''
})

// Get item positioning classes
function getItemClasses(index) {
  if (props.horizontal) {
    return 'flex flex-col items-center text-center'
  }
  
  if (props.linePosition === 'center') {
    if (props.alternating) {
      return index % 2 === 0 
        ? 'flex flex-row-reverse text-right pr-1/2'
        : 'flex flex-row text-left pl-1/2'
    }
    return 'flex flex-col items-center text-center'
  }
  
  if (props.linePosition === 'right') {
    return 'flex flex-row-reverse'
  }
  
  return 'flex flex-row'
}

// Content margin class based on line position
const getContentMarginClass = computed(() => {
  if (props.linePosition === 'center') return 'mx-auto max-w-xs'
  if (props.linePosition === 'right') return 'mr-4 text-right'
  return 'ml-4'
})

// Get dot classes based on status and size
function getDotClasses(item) {
  const sizeClasses = {
    sm: 'w-6 h-6',
    md: 'w-8 h-8',
    lg: 'w-10 h-10'
  }
  
  const baseSize = sizeClasses[props.dotSize]
  
  // Status-based colors
  const statusColors = {
    completed: 'bg-green-500 border-green-500 text-white',
    current: 'bg-blue-500 border-blue-500 text-white animate-pulse',
    pending: 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400',
    error: 'bg-red-500 border-red-500 text-white',
    warning: 'bg-yellow-500 border-yellow-500 text-white'
  }
  
  // Custom color override
  if (item.color) {
    const customColors = {
      blue: 'bg-blue-500 border-blue-500 text-white',
      green: 'bg-green-500 border-green-500 text-white',
      red: 'bg-red-500 border-red-500 text-white',
      yellow: 'bg-yellow-500 border-yellow-500 text-white',
      purple: 'bg-purple-500 border-purple-500 text-white',
      pink: 'bg-pink-500 border-pink-500 text-white',
      gray: 'bg-gray-500 border-gray-500 text-white'
    }
    return [baseSize, customColors[item.color] || statusColors.pending]
  }
  
  return [baseSize, statusColors[item.status] || statusColors.pending]
}

// Get title classes
function getTitleClasses(item) {
  if (item.status === 'completed') {
    return 'text-gray-900 dark:text-white'
  }
  if (item.status === 'current') {
    return 'text-blue-600 dark:text-blue-400'
  }
  if (item.status === 'error') {
    return 'text-red-600 dark:text-red-400'
  }
  return 'text-gray-700 dark:text-gray-300'
}

// Format date
function formatDate(date) {
  if (!date) return ''
  
  const d = typeof date === 'string' ? new Date(date) : date
  
  if (props.dateFormat === 'relative') {
    const now = new Date()
    const diff = now - d
    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    
    if (days === 0) return 'Today'
    if (days === 1) return 'Yesterday'
    if (days < 7) return `${days} days ago`
    if (days < 30) return `${Math.floor(days / 7)} weeks ago`
    if (days < 365) return `${Math.floor(days / 30)} months ago`
    return `${Math.floor(days / 365)} years ago`
  }
  
  if (props.dateFormat === 'long') {
    return d.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }
  
  // DD/MM/YYYY format (Bangladesh standard)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return `${day}/${month}/${year}`
}
</script>
