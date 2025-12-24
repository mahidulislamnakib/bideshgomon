<template>
  <div class="inline-flex items-center gap-1" :class="{ 'opacity-60 cursor-not-allowed': disabled }">
    <!-- Label (before) -->
    <span v-if="label && labelPosition === 'before'" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
    </span>

    <!-- Stars Container -->
    <div 
      class="inline-flex items-center gap-0.5"
      @mouseleave="handleMouseLeave"
    >
      <button
        v-for="star in totalStars"
        :key="star"
        type="button"
        :disabled="readonly || disabled"
        class="focus:outline-none transition-transform"
        :class="{
          'hover:scale-110 cursor-pointer': !readonly && !disabled,
          'cursor-default': readonly || disabled
        }"
        @click="handleClick(star)"
        @mouseenter="handleMouseEnter(star)"
        :aria-label="`Rate ${star} out of ${totalStars} stars`"
      >
        <!-- Star Icon -->
        <component
          :is="getStarIcon(star)"
          :class="[sizeClass, getStarColorClass(star)]"
        />
      </button>

      <!-- Clear Button -->
      <button
        v-if="clearable && modelValue > 0 && !readonly && !disabled"
        type="button"
        class="ml-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
        @click="clearRating"
        aria-label="Clear rating"
      >
        <XMarkIcon :class="sizeClass" />
      </button>
    </div>

    <!-- Value Display -->
    <span 
      v-if="showValue" 
      class="ml-2 text-sm font-medium"
      :class="valueColorClass"
    >
      {{ displayValue }}
    </span>

    <!-- Count Display -->
    <span 
      v-if="count !== null" 
      class="ml-1 text-sm text-gray-500 dark:text-gray-400"
    >
      ({{ formatCount(count) }})
    </span>

    <!-- Label (after) -->
    <span v-if="label && labelPosition === 'after'" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
    </span>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { StarIcon as StarSolid, XMarkIcon } from '@heroicons/vue/24/solid'
import { StarIcon as StarOutline } from '@heroicons/vue/24/outline'

const props = defineProps({
  // Current rating value
  modelValue: {
    type: Number,
    default: 0
  },
  // Total number of stars
  totalStars: {
    type: Number,
    default: 5
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
    default: 'yellow',
    validator: (v) => ['yellow', 'orange', 'red', 'blue', 'green', 'purple'].includes(v)
  },
  // Read-only mode
  readonly: {
    type: Boolean,
    default: false
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Allow clearing rating
  clearable: {
    type: Boolean,
    default: true
  },
  // Allow half stars
  allowHalf: {
    type: Boolean,
    default: false
  },
  // Show numeric value
  showValue: {
    type: Boolean,
    default: false
  },
  // Review count
  count: {
    type: Number,
    default: null
  },
  // Label text
  label: {
    type: String,
    default: ''
  },
  // Label position
  labelPosition: {
    type: String,
    default: 'before',
    validator: (v) => ['before', 'after'].includes(v)
  },
  // Empty star color
  emptyColor: {
    type: String,
    default: 'gray'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Hover state
const hoverValue = ref(0)

// Effective value (hover takes precedence)
const effectiveValue = computed(() => {
  if (hoverValue.value > 0 && !props.readonly && !props.disabled) {
    return hoverValue.value
  }
  return props.modelValue
})

// Display value (formatted)
const displayValue = computed(() => {
  if (props.allowHalf) {
    return props.modelValue.toFixed(1)
  }
  return props.modelValue.toString()
})

// Size classes
const sizeClass = computed(() => {
  const sizes = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
    xl: 'w-8 h-8'
  }
  return sizes[props.size]
})

// Star color class (filled)
const starColorClass = computed(() => {
  const colors = {
    yellow: 'text-yellow-400',
    orange: 'text-orange-400',
    red: 'text-red-500',
    blue: 'text-blue-500',
    green: 'text-green-500',
    purple: 'text-purple-500'
  }
  return colors[props.color]
})

// Empty star color class
const emptyStarColorClass = computed(() => {
  const colors = {
    gray: 'text-gray-300 dark:text-gray-600',
    light: 'text-gray-200 dark:text-gray-700'
  }
  return colors[props.emptyColor] || colors.gray
})

// Value text color
const valueColorClass = computed(() => {
  if (props.modelValue >= 4) return 'text-green-600 dark:text-green-400'
  if (props.modelValue >= 3) return 'text-yellow-600 dark:text-yellow-400'
  if (props.modelValue >= 2) return 'text-orange-600 dark:text-orange-400'
  return 'text-gray-600 dark:text-gray-400'
})

// Get star icon based on fill state
function getStarIcon(star) {
  const value = effectiveValue.value
  
  if (props.allowHalf) {
    if (star <= Math.floor(value)) return StarSolid
    if (star - 0.5 <= value) return StarSolid // Half star (show as full for simplicity)
    return StarOutline
  }
  
  return star <= value ? StarSolid : StarOutline
}

// Get star color class
function getStarColorClass(star) {
  const value = effectiveValue.value
  
  if (props.allowHalf) {
    if (star <= value) return starColorClass.value
    return emptyStarColorClass.value
  }
  
  return star <= value ? starColorClass.value : emptyStarColorClass.value
}

// Handle star click
function handleClick(star) {
  if (props.readonly || props.disabled) return
  
  // If clicking the same star, toggle between full and clear (or half if enabled)
  let newValue = star
  if (props.allowHalf && props.modelValue === star) {
    newValue = star - 0.5
  } else if (props.modelValue === star) {
    newValue = props.clearable ? 0 : star
  }
  
  emit('update:modelValue', newValue)
  emit('change', newValue)
}

// Handle mouse enter
function handleMouseEnter(star) {
  if (!props.readonly && !props.disabled) {
    hoverValue.value = star
  }
}

// Handle mouse leave
function handleMouseLeave() {
  hoverValue.value = 0
}

// Clear rating
function clearRating() {
  emit('update:modelValue', 0)
  emit('change', 0)
}

// Format count with K/M suffix
function formatCount(count) {
  if (count >= 1000000) {
    return (count / 1000000).toFixed(1) + 'M'
  }
  if (count >= 1000) {
    return (count / 1000).toFixed(1) + 'K'
  }
  return count.toString()
}
</script>
