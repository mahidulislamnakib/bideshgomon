<template>
  <component
    :is="clickable ? 'button' : 'span'"
    class="inline-flex items-center gap-1.5 font-medium transition-all duration-200"
    :class="[sizeClasses, variantClasses, shapeClasses, { 'cursor-pointer': clickable }]"
    :type="clickable ? 'button' : undefined"
    @click="handleClick"
  >
    <!-- Leading icon -->
    <component
      v-if="icon"
      :is="icon"
      class="flex-shrink-0"
      :class="iconSizeClasses"
    />

    <!-- Avatar/Image -->
    <img
      v-if="avatar"
      :src="avatar"
      :alt="label"
      class="flex-shrink-0 rounded-full object-cover"
      :class="avatarSizeClasses"
    />

    <!-- Dot indicator -->
    <span
      v-if="dot"
      class="flex-shrink-0 rounded-full"
      :class="[dotSizeClasses, dotColorClasses]"
    />

    <!-- Label -->
    <span :class="{ 'sr-only': iconOnly }">
      <slot>{{ label }}</slot>
    </span>

    <!-- Count/Badge -->
    <span
      v-if="count !== null && count !== undefined"
      class="flex-shrink-0 rounded-full"
      :class="countClasses"
    >
      {{ formattedCount }}
    </span>

    <!-- Remove button -->
    <button
      v-if="removable"
      type="button"
      class="flex-shrink-0 rounded-full transition-colors focus:outline-none"
      :class="removeButtonClasses"
      @click.stop="$emit('remove')"
    >
      <span class="sr-only">Remove {{ label }}</span>
      <XMarkIcon :class="removeIconClasses" />
    </button>
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'gray',
    validator: (value) => [
      'gray', 'primary', 'success', 'warning', 'error', 'info',
      'gray-outline', 'primary-outline', 'success-outline', 'warning-outline', 'error-outline', 'info-outline',
      'gray-soft', 'primary-soft', 'success-soft', 'warning-soft', 'error-soft', 'info-soft'
    ].includes(value)
  },
  shape: {
    type: String,
    default: 'rounded',
    validator: (value) => ['rounded', 'pill', 'square'].includes(value)
  },
  icon: {
    type: [Object, Function],
    default: null
  },
  avatar: {
    type: String,
    default: ''
  },
  dot: {
    type: Boolean,
    default: false
  },
  dotColor: {
    type: String,
    default: 'current'
  },
  count: {
    type: [Number, String],
    default: null
  },
  removable: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  selected: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  iconOnly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click', 'remove'])

// Format count
const formattedCount = computed(() => {
  if (typeof props.count === 'number' && props.count > 999) {
    return '999+'
  }
  return props.count
})

// Handle click
function handleClick() {
  if (props.clickable && !props.disabled) {
    emit('click')
  }
}

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    xs: 'px-1.5 py-0.5 text-xs',
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
    lg: 'px-3 py-1.5 text-sm'
  }
  return sizes[props.size]
})

// Shape classes
const shapeClasses = computed(() => {
  const shapes = {
    rounded: 'rounded-md',
    pill: 'rounded-full',
    square: 'rounded-none'
  }
  return shapes[props.shape]
})

// Variant classes
const variantClasses = computed(() => {
  const base = props.disabled ? 'opacity-50 cursor-not-allowed' : ''
  
  const variants = {
    // Solid variants
    gray: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    primary: 'bg-primary-600 text-white hover:bg-primary-700',
    success: 'bg-green-600 text-white hover:bg-green-700',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600',
    error: 'bg-red-600 text-white hover:bg-red-700',
    info: 'bg-blue-600 text-white hover:bg-blue-700',
    
    // Outline variants
    'gray-outline': 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50',
    'primary-outline': 'bg-transparent border border-primary-300 text-primary-700 hover:bg-primary-50',
    'success-outline': 'bg-transparent border border-green-300 text-green-700 hover:bg-green-50',
    'warning-outline': 'bg-transparent border border-yellow-300 text-yellow-700 hover:bg-yellow-50',
    'error-outline': 'bg-transparent border border-red-300 text-red-700 hover:bg-red-50',
    'info-outline': 'bg-transparent border border-blue-300 text-blue-700 hover:bg-blue-50',
    
    // Soft variants
    'gray-soft': 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    'primary-soft': 'bg-primary-100 text-primary-700 hover:bg-primary-200',
    'success-soft': 'bg-green-100 text-green-700 hover:bg-green-200',
    'warning-soft': 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200',
    'error-soft': 'bg-red-100 text-red-700 hover:bg-red-200',
    'info-soft': 'bg-blue-100 text-blue-700 hover:bg-blue-200'
  }
  
  const selectedClasses = props.selected ? 'ring-2 ring-offset-1 ring-primary-500' : ''
  
  return `${base} ${variants[props.variant]} ${selectedClasses}`
})

// Icon size
const iconSizeClasses = computed(() => {
  const sizes = {
    xs: 'h-3 w-3',
    sm: 'h-3.5 w-3.5',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  }
  return sizes[props.size]
})

// Avatar size
const avatarSizeClasses = computed(() => {
  const sizes = {
    xs: 'h-3 w-3',
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6'
  }
  return sizes[props.size]
})

// Dot size
const dotSizeClasses = computed(() => {
  const sizes = {
    xs: 'h-1.5 w-1.5',
    sm: 'h-1.5 w-1.5',
    md: 'h-2 w-2',
    lg: 'h-2.5 w-2.5'
  }
  return sizes[props.size]
})

// Dot color
const dotColorClasses = computed(() => {
  if (props.dotColor === 'current') return 'bg-current'
  const colors = {
    gray: 'bg-gray-500',
    primary: 'bg-primary-500',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    error: 'bg-red-500',
    info: 'bg-blue-500'
  }
  return colors[props.dotColor] || 'bg-current'
})

// Count badge classes
const countClasses = computed(() => {
  const sizes = {
    xs: 'px-1 py-0.5 text-[10px] leading-none ml-0.5',
    sm: 'px-1.5 py-0.5 text-[10px] leading-none',
    md: 'px-1.5 py-0.5 text-xs leading-none',
    lg: 'px-2 py-0.5 text-xs leading-none'
  }
  return `bg-black/10 ${sizes[props.size]}`
})

// Remove button classes
const removeButtonClasses = computed(() => {
  return 'hover:bg-black/10 -mr-0.5 p-0.5'
})

// Remove icon size
const removeIconClasses = computed(() => {
  const sizes = {
    xs: 'h-3 w-3',
    sm: 'h-3 w-3',
    md: 'h-3.5 w-3.5',
    lg: 'h-4 w-4'
  }
  return sizes[props.size]
})
</script>
