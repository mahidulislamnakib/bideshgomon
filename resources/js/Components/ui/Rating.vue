<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-1.5" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <div class="flex items-center gap-1">
      <!-- Stars -->
      <div class="flex" :class="{ 'gap-0.5': size === 'sm', 'gap-1': size !== 'sm' }">
        <button
          v-for="star in count"
          :key="star"
          type="button"
          :disabled="readonly || disabled"
          class="relative focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 rounded transition-transform"
          :class="[
            sizeClasses.button,
            !readonly && !disabled ? 'hover:scale-110 cursor-pointer' : 'cursor-default'
          ]"
          @click="handleClick(star)"
          @mouseenter="handleHover(star)"
          @mouseleave="handleHoverLeave"
        >
          <!-- Background star (empty) -->
          <component
            :is="icon"
            :class="[sizeClasses.icon, 'text-gray-300']"
          />
          
          <!-- Foreground star (filled) -->
          <component
            :is="iconFilled"
            class="absolute inset-0 transition-colors"
            :class="[sizeClasses.icon, getStarClasses(star)]"
            :style="getStarStyle(star)"
          />
        </button>
      </div>

      <!-- Value display -->
      <span v-if="showValue" class="ml-2 text-sm font-medium" :class="valueClasses">
        {{ displayValue }}
      </span>

      <!-- Count display -->
      <span v-if="reviewCount !== null" class="text-sm text-gray-500">
        ({{ reviewCount }})
      </span>
    </div>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-1.5 text-sm"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { StarIcon as StarIconOutline } from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
  modelValue: {
    type: Number,
    default: 0
  },
  count: {
    type: Number,
    default: 5
  },
  label: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'yellow',
    validator: (value) => ['yellow', 'primary', 'red', 'green'].includes(value)
  },
  allowHalf: {
    type: Boolean,
    default: false
  },
  allowClear: {
    type: Boolean,
    default: true
  },
  readonly: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  showValue: {
    type: Boolean,
    default: false
  },
  reviewCount: {
    type: Number,
    default: null
  },
  icon: {
    type: [Object, Function],
    default: () => StarIconOutline
  },
  iconFilled: {
    type: [Object, Function],
    default: () => StarIconSolid
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'hover'])

// Hover state
const hoverValue = ref(null)

// Computed values
const activeValue = computed(() => {
  return hoverValue.value !== null ? hoverValue.value : props.modelValue
})

const displayValue = computed(() => {
  const value = props.modelValue
  if (props.allowHalf) {
    return value.toFixed(1)
  }
  return value.toString()
})

// Handle click
function handleClick(star) {
  if (props.readonly || props.disabled) return

  let newValue = star
  
  // Allow half ratings
  if (props.allowHalf) {
    // If clicking same star, toggle between half and full
    const currentFloor = Math.floor(props.modelValue)
    const isHalf = props.modelValue % 1 !== 0
    
    if (star === currentFloor + 1 && !isHalf) {
      newValue = star - 0.5
    } else if (star === Math.ceil(props.modelValue) && isHalf) {
      newValue = star
    }
  }
  
  // Allow clear by clicking same rating
  if (props.allowClear && newValue === props.modelValue) {
    newValue = 0
  }

  emit('update:modelValue', newValue)
  emit('change', newValue)
}

// Handle hover
function handleHover(star) {
  if (props.readonly || props.disabled) return
  hoverValue.value = star
  emit('hover', star)
}

function handleHoverLeave() {
  hoverValue.value = null
}

// Get star fill classes
function getStarClasses(star) {
  const colors = {
    yellow: 'text-yellow-400',
    primary: 'text-primary-500',
    red: 'text-red-500',
    green: 'text-green-500'
  }
  
  if (star <= activeValue.value) {
    return colors[props.color]
  }
  return 'text-transparent'
}

// Get star style for partial fill
function getStarStyle(star) {
  const value = activeValue.value
  const diff = star - value
  
  if (diff <= 0) {
    return {} // Fully filled
  }
  if (diff >= 1) {
    return { clipPath: 'inset(0 100% 0 0)' } // Empty
  }
  
  // Partial fill (for half stars)
  const percentage = (1 - diff) * 100
  return { clipPath: `inset(0 ${100 - percentage}% 0 0)` }
}

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    xs: { button: 'p-0.5', icon: 'h-4 w-4' },
    sm: { button: 'p-0.5', icon: 'h-5 w-5' },
    md: { button: 'p-0.5', icon: 'h-6 w-6' },
    lg: { button: 'p-1', icon: 'h-7 w-7' },
    xl: { button: 'p-1', icon: 'h-8 w-8' }
  }
  return sizes[props.size]
})

// Value color classes
const valueClasses = computed(() => {
  const colors = {
    yellow: 'text-yellow-600',
    primary: 'text-primary-600',
    red: 'text-red-600',
    green: 'text-green-600'
  }
  return colors[props.color]
})

// Container classes
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})
</script>
