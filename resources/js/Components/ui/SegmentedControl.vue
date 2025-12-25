<template>
  <div :class="containerClasses">
    <!-- Background indicator -->
    <div
      ref="indicatorRef"
      :class="indicatorClasses"
      :style="indicatorStyle"
    />
    
    <!-- Segments -->
    <button
      v-for="(option, index) in options"
      :key="option.value"
      ref="segmentRefs"
      type="button"
      :class="segmentClasses(option)"
      :disabled="option.disabled || disabled"
      @click="selectOption(option, index)"
    >
      <!-- Icon -->
      <component
        v-if="option.icon"
        :is="option.icon"
        :class="iconClasses(option)"
      />
      
      <!-- Label -->
      <span v-if="option.label && !iconOnly" :class="labelClasses(option)">
        {{ option.label }}
      </span>
      
      <!-- Badge -->
      <span
        v-if="option.badge !== undefined"
        :class="badgeClasses(option)"
      >
        {{ option.badge }}
      </span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  
  // Options array
  options: {
    type: Array,
    required: true
    // { value, label?, icon?, badge?, disabled? }
  },
  
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v)
  },
  
  // Variant
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'pills', 'bordered'].includes(v)
  },
  
  // Color
  color: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'success', 'danger', 'warning'].includes(v)
  },
  
  // Full width
  fullWidth: {
    type: Boolean,
    default: false
  },
  
  // Icon only mode
  iconOnly: {
    type: Boolean,
    default: false
  },
  
  // Animate indicator
  animated: {
    type: Boolean,
    default: true
  },
  
  // Disabled
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const segmentRefs = ref([])
const indicatorRef = ref(null)
const indicatorStyle = ref({})

// Find active index
const activeIndex = computed(() => {
  return props.options.findIndex(opt => opt.value === props.modelValue)
})

// Size config
const sizeConfig = computed(() => {
  const sizes = {
    xs: { padding: 'px-2 py-1', text: 'text-xs', icon: 'w-3 h-3', gap: 'gap-1' },
    sm: { padding: 'px-3 py-1.5', text: 'text-sm', icon: 'w-4 h-4', gap: 'gap-1.5' },
    md: { padding: 'px-4 py-2', text: 'text-sm', icon: 'w-5 h-5', gap: 'gap-2' },
    lg: { padding: 'px-5 py-2.5', text: 'text-base', icon: 'w-6 h-6', gap: 'gap-2' }
  }
  return sizes[props.size]
})

// Color config
const colorConfig = computed(() => {
  const colors = {
    primary: {
      bg: 'bg-primary-600',
      text: 'text-primary-600 dark:text-primary-400',
      activeText: 'text-white'
    },
    secondary: {
      bg: 'bg-gray-600',
      text: 'text-gray-600 dark:text-gray-400',
      activeText: 'text-white'
    },
    success: {
      bg: 'bg-green-600',
      text: 'text-green-600 dark:text-green-400',
      activeText: 'text-white'
    },
    danger: {
      bg: 'bg-red-600',
      text: 'text-red-600 dark:text-red-400',
      activeText: 'text-white'
    },
    warning: {
      bg: 'bg-amber-500',
      text: 'text-amber-600 dark:text-amber-400',
      activeText: 'text-white'
    }
  }
  return colors[props.color]
})

// Container classes
const containerClasses = computed(() => [
  'relative inline-flex items-center',
  props.fullWidth && 'w-full',
  props.variant === 'default' && [
    'bg-gray-100 dark:bg-gray-800',
    'p-1 rounded-lg'
  ],
  props.variant === 'pills' && [
    'bg-transparent',
    'gap-1'
  ],
  props.variant === 'bordered' && [
    'border border-gray-300 dark:border-gray-600',
    'rounded-lg overflow-hidden'
  ],
  props.disabled && 'opacity-50'
])

// Indicator classes
const indicatorClasses = computed(() => [
  'absolute z-0',
  props.animated && 'transition-all duration-200 ease-out',
  props.variant === 'default' && [
    colorConfig.value.bg,
    'rounded-md shadow-sm'
  ],
  props.variant === 'pills' && [
    colorConfig.value.bg,
    'rounded-full'
  ],
  props.variant === 'bordered' && [
    colorConfig.value.bg
  ]
])

// Segment classes
function segmentClasses(option) {
  const isActive = option.value === props.modelValue
  
  return [
    'relative z-10 flex items-center justify-center',
    sizeConfig.value.padding,
    sizeConfig.value.text,
    sizeConfig.value.gap,
    props.fullWidth && 'flex-1',
    'font-medium',
    'transition-colors duration-200',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500',
    // Variant specific
    props.variant === 'default' && 'rounded-md',
    props.variant === 'pills' && 'rounded-full px-4',
    props.variant === 'bordered' && [
      'border-r border-gray-300 dark:border-gray-600 last:border-r-0'
    ],
    // Active/inactive states
    isActive 
      ? colorConfig.value.activeText
      : [
          'text-gray-600 dark:text-gray-400',
          !option.disabled && 'hover:text-gray-900 dark:hover:text-gray-200'
        ],
    // Disabled
    option.disabled && 'cursor-not-allowed opacity-50',
    !option.disabled && !props.disabled && 'cursor-pointer'
  ]
}

// Icon classes
function iconClasses(option) {
  return [
    sizeConfig.value.icon,
    'flex-shrink-0'
  ]
}

// Label classes
function labelClasses(option) {
  return [
    'whitespace-nowrap'
  ]
}

// Badge classes
function badgeClasses(option) {
  const isActive = option.value === props.modelValue
  
  return [
    'ml-1 px-1.5 py-0.5 rounded-full text-xs font-medium',
    isActive
      ? 'bg-white/20 text-white'
      : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
  ]
}

// Update indicator position
function updateIndicator() {
  nextTick(() => {
    const segments = segmentRefs.value
    if (!segments || activeIndex.value < 0) {
      indicatorStyle.value = { opacity: 0 }
      return
    }
    
    const activeSegment = segments[activeIndex.value]
    if (!activeSegment) return
    
    indicatorStyle.value = {
      left: `${activeSegment.offsetLeft}px`,
      top: `${activeSegment.offsetTop}px`,
      width: `${activeSegment.offsetWidth}px`,
      height: `${activeSegment.offsetHeight}px`,
      opacity: 1
    }
  })
}

// Select option
function selectOption(option, index) {
  if (option.disabled || props.disabled) return
  
  emit('update:modelValue', option.value)
  emit('change', option.value)
}

// Watch for changes
watch(() => props.modelValue, updateIndicator)
watch(() => props.options, updateIndicator, { deep: true })

// Initial position
onMounted(() => {
  updateIndicator()
  
  // Update on resize
  window.addEventListener('resize', updateIndicator)
})

// Expose
defineExpose({
  updateIndicator
})
</script>
