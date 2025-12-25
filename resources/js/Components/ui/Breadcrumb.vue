<template>
  <nav :class="containerClasses" :aria-label="ariaLabel">
    <ol :class="listClasses">
      <li
        v-for="(item, index) in items"
        :key="item.id || index"
        :class="itemClasses"
      >
        <!-- Separator -->
        <span 
          v-if="index > 0" 
          :class="separatorClasses"
          aria-hidden="true"
        >
          <component 
            v-if="separatorIcon" 
            :is="separatorIcon" 
            class="w-4 h-4"
          />
          <template v-else>{{ separator }}</template>
        </span>
        
        <!-- Breadcrumb item -->
        <component
          :is="isLast(index) ? 'span' : (item.href ? 'a' : 'button')"
          :href="item.href"
          :class="getLinkClasses(index)"
          :aria-current="isLast(index) ? 'page' : undefined"
          @click="!item.href && handleClick(item, index)"
        >
          <!-- Icon -->
          <component
            v-if="item.icon"
            :is="item.icon"
            :class="iconClasses"
          />
          
          <!-- Home icon for first item -->
          <svg
            v-else-if="index === 0 && showHomeIcon"
            :class="iconClasses"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
          </svg>
          
          <!-- Label -->
          <span :class="labelClasses(index)">
            {{ truncate(item.label, maxLength) }}
          </span>
        </component>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Items
  items: {
    type: Array,
    required: true
    // Each item: { label, href?, icon?, id? }
  },
  
  // Separator
  separator: {
    type: String,
    default: '/'
  },
  separatorIcon: {
    type: [Object, Function],
    default: null
  },
  
  // Display
  showHomeIcon: {
    type: Boolean,
    default: false
  },
  maxLength: {
    type: Number,
    default: 0 // 0 = no truncation
  },
  
  // Styling
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'contained', 'pills'].includes(v)
  },
  
  // Accessibility
  ariaLabel: {
    type: String,
    default: 'Breadcrumb'
  }
})

const emit = defineEmits(['navigate'])

// Check if item is last
function isLast(index) {
  return index === props.items.length - 1
}

// Truncate text
function truncate(text, maxLength) {
  if (!maxLength || text.length <= maxLength) return text
  return text.slice(0, maxLength) + '...'
}

// Handle click
function handleClick(item, index) {
  emit('navigate', { item, index })
}

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: { text: 'text-xs', icon: 'w-3 h-3', gap: 'gap-1', padding: 'px-2 py-1' },
    md: { text: 'text-sm', icon: 'w-4 h-4', gap: 'gap-2', padding: 'px-3 py-1.5' },
    lg: { text: 'text-base', icon: 'w-5 h-5', gap: 'gap-3', padding: 'px-4 py-2' }
  }
  return sizes[props.size]
})

// Container classes
const containerClasses = computed(() => [
  'flex',
  props.variant === 'contained' && 'bg-gray-100 dark:bg-gray-800 rounded-lg p-2'
])

// List classes
const listClasses = computed(() => [
  'flex flex-wrap items-center',
  sizeClasses.value.gap
])

// Item classes
const itemClasses = computed(() => [
  'flex items-center',
  sizeClasses.value.gap
])

// Separator classes
const separatorClasses = computed(() => [
  'text-gray-400 dark:text-gray-500 flex-shrink-0'
])

// Get link classes based on position
function getLinkClasses(index) {
  const isLastItem = isLast(index)
  
  const base = [
    'flex items-center',
    sizeClasses.value.gap,
    sizeClasses.value.text,
    'transition-colors duration-150'
  ]
  
  if (props.variant === 'pills') {
    base.push(
      'rounded-full',
      sizeClasses.value.padding,
      isLastItem
        ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
        : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400'
    )
  } else {
    base.push(
      isLastItem
        ? 'text-gray-900 dark:text-gray-100 font-medium cursor-default'
        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
    )
  }
  
  return base
}

// Icon classes
const iconClasses = computed(() => [
  sizeClasses.value.icon,
  'flex-shrink-0'
])

// Label classes
function labelClasses(index) {
  return [
    isLast(index) ? '' : 'hover:underline'
  ]
}
</script>
