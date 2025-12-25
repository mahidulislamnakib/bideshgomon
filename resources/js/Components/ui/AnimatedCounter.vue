<template>
  <span :class="containerClass">
    <span v-if="prefix" class="prefix">{{ prefix }}</span>
    <span ref="counterRef" class="counter">{{ displayValue }}</span>
    <span v-if="suffix" class="suffix">{{ suffix }}</span>
  </span>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  value: {
    type: Number,
    required: true
  },
  duration: {
    type: Number,
    default: 2000
  },
  delay: {
    type: Number,
    default: 0
  },
  easing: {
    type: String,
    default: 'easeOutExpo',
    validator: (v) => ['linear', 'easeOut', 'easeIn', 'easeInOut', 'easeOutExpo', 'easeOutBounce'].includes(v)
  },
  decimals: {
    type: Number,
    default: 0
  },
  separator: {
    type: String,
    default: ','
  },
  decimalSeparator: {
    type: String,
    default: '.'
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
    type: String,
    default: ''
  },
  startOnVisible: {
    type: Boolean,
    default: true
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['start', 'end', 'update'])

const counterRef = ref(null)
const currentValue = ref(0)
const hasStarted = ref(false)
let animationFrame = null
let startTime = null

// Easing functions
const easings = {
  linear: (t) => t,
  easeOut: (t) => 1 - Math.pow(1 - t, 3),
  easeIn: (t) => t * t * t,
  easeInOut: (t) => t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2,
  easeOutExpo: (t) => t === 1 ? 1 : 1 - Math.pow(2, -10 * t),
  easeOutBounce: (t) => {
    const n1 = 7.5625
    const d1 = 2.75
    if (t < 1 / d1) return n1 * t * t
    if (t < 2 / d1) return n1 * (t -= 1.5 / d1) * t + 0.75
    if (t < 2.5 / d1) return n1 * (t -= 2.25 / d1) * t + 0.9375
    return n1 * (t -= 2.625 / d1) * t + 0.984375
  }
}

// Format number with separators
function formatNumber(value) {
  const fixed = value.toFixed(props.decimals)
  const [integer, decimal] = fixed.split('.')
  
  // Add thousand separators
  const formatted = integer.replace(/\B(?=(\d{3})+(?!\d))/g, props.separator)
  
  if (props.decimals > 0 && decimal) {
    return `${formatted}${props.decimalSeparator}${decimal}`
  }
  
  return formatted
}

const displayValue = computed(() => formatNumber(currentValue.value))

// Animation loop
function animate(timestamp) {
  if (!startTime) startTime = timestamp
  
  const elapsed = timestamp - startTime
  const progress = Math.min(elapsed / props.duration, 1)
  const easedProgress = easings[props.easing](progress)
  
  currentValue.value = easedProgress * props.value
  emit('update', currentValue.value)
  
  if (progress < 1) {
    animationFrame = requestAnimationFrame(animate)
  } else {
    currentValue.value = props.value
    emit('end', props.value)
  }
}

function start() {
  if (hasStarted.value) return
  
  hasStarted.value = true
  startTime = null
  
  setTimeout(() => {
    emit('start')
    animationFrame = requestAnimationFrame(animate)
  }, props.delay)
}

function reset() {
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
  currentValue.value = 0
  hasStarted.value = false
  startTime = null
}

// Watch for value changes
watch(() => props.value, () => {
  reset()
  if (!props.startOnVisible) {
    start()
  }
})

// Intersection Observer for visibility detection
let observer = null

onMounted(() => {
  if (props.startOnVisible && counterRef.value) {
    observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !hasStarted.value) {
          start()
        }
      },
      { threshold: 0.1 }
    )
    observer.observe(counterRef.value)
  } else {
    start()
  }
})

onUnmounted(() => {
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
  observer?.disconnect()
})

defineExpose({
  start,
  reset,
  currentValue
})
</script>
