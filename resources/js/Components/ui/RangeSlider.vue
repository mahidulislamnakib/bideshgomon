<template>
  <div class="w-full">
    <!-- Label -->
    <div v-if="label || showValue" class="flex items-center justify-between mb-2">
      <label v-if="label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ label }}
      </label>
      <span v-if="showValue" class="text-sm font-medium text-blue-600 dark:text-blue-400">
        {{ formattedValue }}
      </span>
    </div>

    <!-- Slider Container -->
    <div 
      ref="sliderRef"
      class="relative h-6 flex items-center"
      :class="{ 'opacity-50 cursor-not-allowed': disabled }"
    >
      <!-- Track Background -->
      <div
        class="absolute h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700"
        :class="trackClass"
      />

      <!-- Active Track -->
      <div
        class="absolute h-2 rounded-full"
        :class="activeTrackClass"
        :style="activeTrackStyle"
      />

      <!-- Tick Marks -->
      <template v-if="showTicks && step">
        <div
          v-for="tick in tickMarks"
          :key="tick.value"
          class="absolute w-0.5 h-3 bg-gray-300 dark:bg-gray-600 -translate-x-1/2"
          :style="{ left: `${tick.percent}%` }"
        />
      </template>

      <!-- Min Handle (range mode) -->
      <div
        v-if="range"
        class="absolute w-5 h-5 -translate-x-1/2 cursor-grab active:cursor-grabbing"
        :class="handleClass"
        :style="{ left: `${minPercent}%` }"
        @mousedown="startDrag('min', $event)"
        @touchstart.prevent="startDrag('min', $event)"
        role="slider"
        :aria-valuenow="internalValue[0]"
        :aria-valuemin="min"
        :aria-valuemax="internalValue[1]"
        :aria-label="label ? `${label} minimum` : 'Minimum value'"
        tabindex="0"
        @keydown="handleKeydown('min', $event)"
      >
        <!-- Tooltip -->
        <div
          v-if="showTooltip && (isDragging === 'min' || tooltipAlways)"
          class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded whitespace-nowrap"
        >
          {{ formatValue(internalValue[0]) }}
          <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700" />
        </div>
      </div>

      <!-- Max Handle (or single handle) -->
      <div
        class="absolute w-5 h-5 -translate-x-1/2 cursor-grab active:cursor-grabbing"
        :class="handleClass"
        :style="{ left: `${maxPercent}%` }"
        @mousedown="startDrag('max', $event)"
        @touchstart.prevent="startDrag('max', $event)"
        role="slider"
        :aria-valuenow="range ? internalValue[1] : internalValue"
        :aria-valuemin="range ? internalValue[0] : min"
        :aria-valuemax="max"
        :aria-label="label ? (range ? `${label} maximum` : label) : (range ? 'Maximum value' : 'Value')"
        tabindex="0"
        @keydown="handleKeydown('max', $event)"
      >
        <!-- Tooltip -->
        <div
          v-if="showTooltip && (isDragging === 'max' || tooltipAlways)"
          class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded whitespace-nowrap"
        >
          {{ formatValue(range ? internalValue[1] : internalValue) }}
          <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700" />
        </div>
      </div>
    </div>

    <!-- Min/Max Labels -->
    <div v-if="showMinMax" class="flex justify-between mt-1">
      <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatValue(min) }}</span>
      <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatValue(max) }}</span>
    </div>

    <!-- Tick Labels -->
    <div v-if="tickLabels.length > 0" class="relative mt-2">
      <span
        v-for="label in tickLabels"
        :key="label.value"
        class="absolute text-xs text-gray-500 dark:text-gray-400 -translate-x-1/2"
        :style="{ left: `${label.percent}%` }"
      >
        {{ label.label }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // Single value or [min, max] for range
  modelValue: {
    type: [Number, Array],
    default: 0
  },
  // Minimum value
  min: {
    type: Number,
    default: 0
  },
  // Maximum value
  max: {
    type: Number,
    default: 100
  },
  // Step increment
  step: {
    type: Number,
    default: 1
  },
  // Range mode (dual handles)
  range: {
    type: Boolean,
    default: false
  },
  // Label text
  label: {
    type: String,
    default: ''
  },
  // Show current value
  showValue: {
    type: Boolean,
    default: true
  },
  // Show tooltip on drag
  showTooltip: {
    type: Boolean,
    default: true
  },
  // Always show tooltip
  tooltipAlways: {
    type: Boolean,
    default: false
  },
  // Show min/max labels
  showMinMax: {
    type: Boolean,
    default: false
  },
  // Show tick marks
  showTicks: {
    type: Boolean,
    default: false
  },
  // Custom tick labels array: [{ value, label }]
  ticks: {
    type: Array,
    default: () => []
  },
  // Color variant
  color: {
    type: String,
    default: 'blue',
    validator: (v) => ['blue', 'green', 'red', 'purple', 'gray'].includes(v)
  },
  // Format function for values
  formatFn: {
    type: Function,
    default: null
  },
  // Prefix for displayed value
  prefix: {
    type: String,
    default: ''
  },
  // Suffix for displayed value
  suffix: {
    type: String,
    default: ''
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'drag-start', 'drag-end'])

// Refs
const sliderRef = ref(null)
const isDragging = ref(null)

// Internal value
const internalValue = ref(props.range ? [...props.modelValue] : props.modelValue)

// Watch for external changes
watch(() => props.modelValue, (val) => {
  internalValue.value = props.range ? [...val] : val
})

// Computed percentages
const minPercent = computed(() => {
  if (!props.range) return 0
  return ((internalValue.value[0] - props.min) / (props.max - props.min)) * 100
})

const maxPercent = computed(() => {
  const val = props.range ? internalValue.value[1] : internalValue.value
  return ((val - props.min) / (props.max - props.min)) * 100
})

// Active track style
const activeTrackStyle = computed(() => {
  if (props.range) {
    return {
      left: `${minPercent.value}%`,
      width: `${maxPercent.value - minPercent.value}%`
    }
  }
  return {
    left: '0%',
    width: `${maxPercent.value}%`
  }
})

// Formatted display value
const formattedValue = computed(() => {
  if (props.range) {
    return `${formatValue(internalValue.value[0])} - ${formatValue(internalValue.value[1])}`
  }
  return formatValue(internalValue.value)
})

// Format value helper
function formatValue(val) {
  if (props.formatFn) return props.formatFn(val)
  return `${props.prefix}${val}${props.suffix}`
}

// Tick marks
const tickMarks = computed(() => {
  if (!props.step) return []
  const ticks = []
  for (let val = props.min; val <= props.max; val += props.step) {
    ticks.push({
      value: val,
      percent: ((val - props.min) / (props.max - props.min)) * 100
    })
  }
  return ticks
})

// Tick labels
const tickLabels = computed(() => {
  return props.ticks.map(t => ({
    ...t,
    percent: ((t.value - props.min) / (props.max - props.min)) * 100
  }))
})

// Color classes
const colorClasses = {
  blue: 'bg-blue-600 dark:bg-blue-500',
  green: 'bg-green-600 dark:bg-green-500',
  red: 'bg-red-600 dark:bg-red-500',
  purple: 'bg-purple-600 dark:bg-purple-500',
  gray: 'bg-gray-600 dark:bg-gray-500'
}

const trackClass = computed(() => props.disabled ? '' : '')

const activeTrackClass = computed(() => colorClasses[props.color])

const handleClass = computed(() => {
  const base = 'rounded-full bg-white border-2 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 transition-shadow'
  const colorBorder = {
    blue: 'border-blue-600 dark:border-blue-500 focus-visible:ring-blue-500',
    green: 'border-green-600 dark:border-green-500 focus-visible:ring-green-500',
    red: 'border-red-600 dark:border-red-500 focus-visible:ring-red-500',
    purple: 'border-purple-600 dark:border-purple-500 focus-visible:ring-purple-500',
    gray: 'border-gray-600 dark:border-gray-500 focus-visible:ring-gray-500'
  }
  return `${base} ${colorBorder[props.color]}`
})

// Get value from position
function getValueFromPosition(clientX) {
  const rect = sliderRef.value.getBoundingClientRect()
  const percent = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width))
  let value = props.min + percent * (props.max - props.min)
  
  // Snap to step
  if (props.step) {
    value = Math.round(value / props.step) * props.step
  }
  
  return Math.max(props.min, Math.min(props.max, value))
}

// Start dragging
function startDrag(handle, event) {
  if (props.disabled) return
  
  isDragging.value = handle
  emit('drag-start', handle)
  
  const moveHandler = (e) => {
    const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX
    const newValue = getValueFromPosition(clientX)
    
    if (props.range) {
      const newRange = [...internalValue.value]
      if (handle === 'min') {
        newRange[0] = Math.min(newValue, newRange[1])
      } else {
        newRange[1] = Math.max(newValue, newRange[0])
      }
      internalValue.value = newRange
      emit('update:modelValue', newRange)
    } else {
      internalValue.value = newValue
      emit('update:modelValue', newValue)
    }
  }
  
  const upHandler = () => {
    isDragging.value = null
    emit('drag-end', internalValue.value)
    emit('change', internalValue.value)
    document.removeEventListener('mousemove', moveHandler)
    document.removeEventListener('mouseup', upHandler)
    document.removeEventListener('touchmove', moveHandler)
    document.removeEventListener('touchend', upHandler)
  }
  
  document.addEventListener('mousemove', moveHandler)
  document.addEventListener('mouseup', upHandler)
  document.addEventListener('touchmove', moveHandler)
  document.addEventListener('touchend', upHandler)
}

// Keyboard navigation
function handleKeydown(handle, event) {
  if (props.disabled) return
  
  const step = event.shiftKey ? props.step * 10 : props.step
  let delta = 0
  
  switch (event.key) {
    case 'ArrowLeft':
    case 'ArrowDown':
      delta = -step
      break
    case 'ArrowRight':
    case 'ArrowUp':
      delta = step
      break
    case 'Home':
      delta = props.min - (props.range ? internalValue.value[handle === 'min' ? 0 : 1] : internalValue.value)
      break
    case 'End':
      delta = props.max - (props.range ? internalValue.value[handle === 'min' ? 0 : 1] : internalValue.value)
      break
    default:
      return
  }
  
  event.preventDefault()
  
  if (props.range) {
    const newRange = [...internalValue.value]
    const idx = handle === 'min' ? 0 : 1
    newRange[idx] = Math.max(props.min, Math.min(props.max, newRange[idx] + delta))
    
    // Ensure min <= max
    if (idx === 0) newRange[0] = Math.min(newRange[0], newRange[1])
    else newRange[1] = Math.max(newRange[0], newRange[1])
    
    internalValue.value = newRange
    emit('update:modelValue', newRange)
  } else {
    const newVal = Math.max(props.min, Math.min(props.max, internalValue.value + delta))
    internalValue.value = newVal
    emit('update:modelValue', newVal)
  }
  
  emit('change', internalValue.value)
}
</script>
