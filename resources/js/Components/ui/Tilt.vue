<template>
  <div
    ref="containerRef"
    class="transform-gpu"
    :class="containerClass"
    :style="tiltStyle"
    @mouseenter="handleMouseEnter"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
  >
    <slot :tilt="currentTilt" :isHovering="isHovering" />
    
    <!-- Glare effect -->
    <div
      v-if="glare"
      class="pointer-events-none absolute inset-0 overflow-hidden rounded-[inherit]"
    >
      <div
        class="absolute inset-0 transition-opacity duration-300"
        :class="{ 'opacity-0': !isHovering }"
        :style="glareStyle"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'

const props = defineProps({
  maxTilt: {
    type: Number,
    default: 20
  },
  perspective: {
    type: Number,
    default: 1000
  },
  scale: {
    type: Number,
    default: 1.05
  },
  speed: {
    type: Number,
    default: 400
  },
  glare: {
    type: Boolean,
    default: false
  },
  maxGlare: {
    type: Number,
    default: 0.35
  },
  glareColor: {
    type: String,
    default: 'rgba(255, 255, 255, 0.35)'
  },
  reverse: {
    type: Boolean,
    default: false
  },
  reset: {
    type: Boolean,
    default: true
  },
  axis: {
    type: String,
    default: 'both',
    validator: (v) => ['x', 'y', 'both'].includes(v)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['enter', 'move', 'leave', 'tilt'])

const containerRef = ref(null)
const isHovering = ref(false)

const currentTilt = reactive({
  x: 0,
  y: 0,
  percentX: 0,
  percentY: 0
})

const tiltStyle = computed(() => {
  if (props.disabled) {
    return {
      perspective: `${props.perspective}px`,
      transformStyle: 'preserve-3d'
    }
  }

  const tiltX = props.axis !== 'x' ? currentTilt.x : 0
  const tiltY = props.axis !== 'y' ? currentTilt.y : 0
  const scale = isHovering.value ? props.scale : 1

  return {
    perspective: `${props.perspective}px`,
    transformStyle: 'preserve-3d',
    transform: `rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(${scale}, ${scale}, ${scale})`,
    transition: isHovering.value 
      ? 'none'
      : `transform ${props.speed}ms cubic-bezier(0.03, 0.98, 0.52, 0.99)`
  }
})

const glareStyle = computed(() => {
  if (!props.glare) return {}

  const x = currentTilt.percentX
  const y = currentTilt.percentY
  const angle = Math.atan2(y - 50, x - 50) * (180 / Math.PI) + 90

  return {
    background: `linear-gradient(${angle}deg, ${props.glareColor} 0%, transparent 80%)`,
    opacity: Math.min((Math.abs(x - 50) + Math.abs(y - 50)) / 100 * props.maxGlare, props.maxGlare)
  }
})

function handleMouseEnter(e) {
  if (props.disabled) return
  isHovering.value = true
  emit('enter', e)
}

function handleMouseMove(e) {
  if (props.disabled || !containerRef.value) return

  const rect = containerRef.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  const width = rect.width
  const height = rect.height

  // Calculate percentage position (0-100)
  const percentX = (x / width) * 100
  const percentY = (y / height) * 100

  // Calculate tilt angles
  const tiltX = ((percentY - 50) / 50) * props.maxTilt * (props.reverse ? 1 : -1)
  const tiltY = ((percentX - 50) / 50) * props.maxTilt * (props.reverse ? -1 : 1)

  currentTilt.x = tiltX
  currentTilt.y = tiltY
  currentTilt.percentX = percentX
  currentTilt.percentY = percentY

  emit('move', { x: tiltX, y: tiltY, percentX, percentY })
  emit('tilt', currentTilt)
}

function handleMouseLeave(e) {
  if (props.disabled) return

  isHovering.value = false

  if (props.reset) {
    currentTilt.x = 0
    currentTilt.y = 0
    currentTilt.percentX = 50
    currentTilt.percentY = 50
  }

  emit('leave', e)
}

function resetTilt() {
  currentTilt.x = 0
  currentTilt.y = 0
  currentTilt.percentX = 50
  currentTilt.percentY = 50
  isHovering.value = false
}

defineExpose({
  currentTilt,
  isHovering,
  resetTilt
})
</script>
