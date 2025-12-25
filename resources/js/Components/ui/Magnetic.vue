<template>
  <div
    ref="containerRef"
    class="magnetic-container inline-block"
    :class="{ 'cursor-pointer': !disabled }"
    @mouseenter="handleMouseEnter"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
  >
    <div
      ref="elementRef"
      class="magnetic-element transition-transform"
      :style="elementStyle"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  strength: {
    type: Number,
    default: 0.3
  },
  radius: {
    type: Number,
    default: 200
  },
  ease: {
    type: Number,
    default: 0.15
  },
  scale: {
    type: Number,
    default: 1
  },
  scaleOnHover: {
    type: Number,
    default: 1.1
  },
  disabled: {
    type: Boolean,
    default: false
  },
  resetOnLeave: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['enter', 'move', 'leave'])

const containerRef = ref(null)
const elementRef = ref(null)
const isHovering = ref(false)
const translateX = ref(0)
const translateY = ref(0)
const currentScale = ref(props.scale)
const animationFrame = ref(null)

const targetX = ref(0)
const targetY = ref(0)
const targetScale = ref(props.scale)

const elementStyle = computed(() => ({
  transform: `translate(${translateX.value}px, ${translateY.value}px) scale(${currentScale.value})`,
  transitionDuration: isHovering.value ? '0ms' : '300ms',
  transitionTimingFunction: 'cubic-bezier(0.33, 1, 0.68, 1)'
}))

function handleMouseEnter(e) {
  if (props.disabled) return
  
  isHovering.value = true
  targetScale.value = props.scaleOnHover
  emit('enter', e)
  
  startAnimation()
}

function handleMouseMove(e) {
  if (props.disabled || !isHovering.value) return

  const container = containerRef.value
  if (!container) return

  const rect = container.getBoundingClientRect()
  const centerX = rect.left + rect.width / 2
  const centerY = rect.top + rect.height / 2

  const distanceX = e.clientX - centerX
  const distanceY = e.clientY - centerY
  const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY)

  if (distance < props.radius) {
    const factor = 1 - distance / props.radius
    targetX.value = distanceX * props.strength * factor
    targetY.value = distanceY * props.strength * factor
  } else {
    targetX.value = 0
    targetY.value = 0
  }

  emit('move', {
    x: targetX.value,
    y: targetY.value,
    distance,
    event: e
  })
}

function handleMouseLeave(e) {
  if (props.disabled) return
  
  isHovering.value = false
  
  if (props.resetOnLeave) {
    targetX.value = 0
    targetY.value = 0
    targetScale.value = props.scale
  }

  emit('leave', e)
}

function startAnimation() {
  if (animationFrame.value) return

  function animate() {
    // Ease towards target
    translateX.value += (targetX.value - translateX.value) * props.ease
    translateY.value += (targetY.value - translateY.value) * props.ease
    currentScale.value += (targetScale.value - currentScale.value) * props.ease

    // Check if animation should continue
    const dx = Math.abs(targetX.value - translateX.value)
    const dy = Math.abs(targetY.value - translateY.value)
    const ds = Math.abs(targetScale.value - currentScale.value)

    if (dx > 0.01 || dy > 0.01 || ds > 0.001 || isHovering.value) {
      animationFrame.value = requestAnimationFrame(animate)
    } else {
      translateX.value = targetX.value
      translateY.value = targetY.value
      currentScale.value = targetScale.value
      animationFrame.value = null
    }
  }

  animate()
}

function reset() {
  translateX.value = 0
  translateY.value = 0
  currentScale.value = props.scale
  targetX.value = 0
  targetY.value = 0
  targetScale.value = props.scale
}

defineExpose({ reset })
</script>

<style scoped>
.magnetic-container {
  perspective: 1000px;
}

.magnetic-element {
  will-change: transform;
}
</style>
