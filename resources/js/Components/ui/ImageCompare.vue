<template>
  <div 
    ref="containerRef"
    :class="containerClasses"
    :style="containerStyle"
    @mousedown="startDragging"
    @mousemove="onDrag"
    @mouseup="stopDragging"
    @mouseleave="stopDragging"
    @touchstart.prevent="startDragging"
    @touchmove.prevent="onDrag"
    @touchend="stopDragging"
  >
    <!-- Before image (full width) -->
    <div class="absolute inset-0 overflow-hidden">
      <img
        v-if="beforeImage"
        :src="beforeImage"
        :alt="beforeAlt"
        :class="imageClasses"
        :style="{ objectPosition }"
      />
      <slot name="before" v-else />
      
      <!-- Before label -->
      <div
        v-if="showLabels && beforeLabel"
        :class="labelClasses.before"
      >
        {{ beforeLabel }}
      </div>
    </div>
    
    <!-- After image (clipped) -->
    <div
      :class="afterClasses"
      :style="clipStyle"
    >
      <img
        v-if="afterImage"
        :src="afterImage"
        :alt="afterAlt"
        :class="imageClasses"
        :style="{ objectPosition }"
      />
      <slot name="after" v-else />
      
      <!-- After label -->
      <div
        v-if="showLabels && afterLabel"
        :class="labelClasses.after"
      >
        {{ afterLabel }}
      </div>
    </div>
    
    <!-- Slider handle -->
    <div
      v-if="showHandle"
      :class="handleContainerClasses"
      :style="handleStyle"
    >
      <!-- Vertical line -->
      <div :class="lineClasses" />
      
      <!-- Handle button -->
      <div :class="handleClasses">
        <svg 
          v-if="handleIcon === 'arrows'"
          class="w-5 h-5" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
        </svg>
        <svg 
          v-else-if="handleIcon === 'grip'"
          class="w-5 h-5" 
          fill="currentColor" 
          viewBox="0 0 24 24"
        >
          <circle cx="8" cy="8" r="1.5" />
          <circle cx="16" cy="8" r="1.5" />
          <circle cx="8" cy="12" r="1.5" />
          <circle cx="16" cy="12" r="1.5" />
          <circle cx="8" cy="16" r="1.5" />
          <circle cx="16" cy="16" r="1.5" />
        </svg>
        <div v-else class="w-1 h-8 bg-current opacity-50 rounded" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  // Images
  beforeImage: {
    type: String,
    default: ''
  },
  afterImage: {
    type: String,
    default: ''
  },
  beforeAlt: {
    type: String,
    default: 'Before'
  },
  afterAlt: {
    type: String,
    default: 'After'
  },
  
  // Labels
  showLabels: {
    type: Boolean,
    default: true
  },
  beforeLabel: {
    type: String,
    default: 'Before'
  },
  afterLabel: {
    type: String,
    default: 'After'
  },
  
  // Handle
  showHandle: {
    type: Boolean,
    default: true
  },
  handleIcon: {
    type: String,
    default: 'arrows',
    validator: (v) => ['arrows', 'grip', 'line'].includes(v)
  },
  handleColor: {
    type: String,
    default: 'white'
  },
  
  // Behavior
  initialPosition: {
    type: Number,
    default: 50,
    validator: (v) => v >= 0 && v <= 100
  },
  orientation: {
    type: String,
    default: 'horizontal',
    validator: (v) => ['horizontal', 'vertical'].includes(v)
  },
  hoverToMove: {
    type: Boolean,
    default: false
  },
  
  // Styling
  aspectRatio: {
    type: String,
    default: '16/9'
  },
  rounded: {
    type: String,
    default: 'lg',
    validator: (v) => ['none', 'sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(v)
  },
  objectFit: {
    type: String,
    default: 'cover',
    validator: (v) => ['cover', 'contain', 'fill', 'none'].includes(v)
  },
  objectPosition: {
    type: String,
    default: 'center'
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['change', 'drag-start', 'drag-end'])

const containerRef = ref(null)
const position = ref(props.initialPosition)
const isDragging = ref(false)

const isHorizontal = computed(() => props.orientation === 'horizontal')

// Container classes
const containerClasses = computed(() => [
  'relative overflow-hidden select-none',
  roundedClasses.value,
  !props.disabled && 'cursor-col-resize'
])

const containerStyle = computed(() => ({
  aspectRatio: props.aspectRatio
}))

const roundedClasses = computed(() => {
  const classes = {
    none: '',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    xl: 'rounded-xl',
    '2xl': 'rounded-2xl',
    full: 'rounded-full'
  }
  return classes[props.rounded]
})

// Image classes
const imageClasses = computed(() => [
  'absolute inset-0 w-full h-full pointer-events-none',
  `object-${props.objectFit}`
])

// After container with clip
const afterClasses = computed(() => [
  'absolute inset-0 overflow-hidden'
])

const clipStyle = computed(() => {
  if (isHorizontal.value) {
    return {
      clipPath: `inset(0 0 0 ${position.value}%)`
    }
  }
  return {
    clipPath: `inset(${position.value}% 0 0 0)`
  }
})

// Label classes
const labelClasses = computed(() => ({
  before: [
    'absolute px-3 py-1.5 bg-black/60 text-white text-sm font-medium rounded',
    isHorizontal.value ? 'top-4 left-4' : 'top-4 left-4'
  ],
  after: [
    'absolute px-3 py-1.5 bg-black/60 text-white text-sm font-medium rounded',
    isHorizontal.value ? 'top-4 right-4' : 'bottom-4 left-4'
  ]
}))

// Handle container
const handleContainerClasses = computed(() => [
  'absolute flex items-center justify-center',
  isHorizontal.value 
    ? 'top-0 bottom-0 w-1 -translate-x-1/2' 
    : 'left-0 right-0 h-1 -translate-y-1/2'
])

const handleStyle = computed(() => {
  if (isHorizontal.value) {
    return { left: `${position.value}%` }
  }
  return { top: `${position.value}%` }
})

// Line classes
const lineClasses = computed(() => [
  'absolute bg-white shadow-lg',
  isHorizontal.value 
    ? 'w-0.5 h-full' 
    : 'h-0.5 w-full'
])

// Handle button classes
const handleClasses = computed(() => [
  'relative z-10 flex items-center justify-center',
  'w-10 h-10 rounded-full',
  'bg-white text-gray-700 shadow-lg',
  'border-2 border-white',
  'transition-transform hover:scale-110',
  isDragging.value && 'scale-110'
])

// Calculate position from event
function getPositionFromEvent(e) {
  if (!containerRef.value) return position.value
  
  const rect = containerRef.value.getBoundingClientRect()
  const clientPos = e.touches ? e.touches[0] : e
  
  let percentage
  if (isHorizontal.value) {
    percentage = ((clientPos.clientX - rect.left) / rect.width) * 100
  } else {
    percentage = ((clientPos.clientY - rect.top) / rect.height) * 100
  }
  
  return Math.max(0, Math.min(100, percentage))
}

// Drag handlers
function startDragging(e) {
  if (props.disabled) return
  
  isDragging.value = true
  position.value = getPositionFromEvent(e)
  emit('drag-start', position.value)
}

function onDrag(e) {
  if (props.disabled) return
  
  if (isDragging.value || props.hoverToMove) {
    const newPosition = getPositionFromEvent(e)
    position.value = newPosition
    emit('change', newPosition)
  }
}

function stopDragging() {
  if (isDragging.value) {
    isDragging.value = false
    emit('drag-end', position.value)
  }
}

// Keyboard support
function handleKeydown(e) {
  if (props.disabled) return
  
  const step = e.shiftKey ? 10 : 1
  
  if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
    e.preventDefault()
    position.value = Math.max(0, position.value - step)
    emit('change', position.value)
  } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
    e.preventDefault()
    position.value = Math.min(100, position.value + step)
    emit('change', position.value)
  }
}

// Watch initialPosition for external updates
watch(() => props.initialPosition, (val) => {
  position.value = val
})

// Lifecycle
onMounted(() => {
  containerRef.value?.addEventListener('keydown', handleKeydown)
  containerRef.value?.setAttribute('tabindex', '0')
})

onUnmounted(() => {
  containerRef.value?.removeEventListener('keydown', handleKeydown)
})

// Expose for parent control
defineExpose({
  position,
  isDragging,
  setPosition: (val) => { position.value = Math.max(0, Math.min(100, val)) },
  reset: () => { position.value = props.initialPosition }
})
</script>
