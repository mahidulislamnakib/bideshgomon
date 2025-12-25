<template>
  <span ref="containerRef" class="wave-text inline-flex" :class="containerClasses">
    <span
      v-for="(char, index) in characters"
      :key="index"
      class="wave-char inline-block"
      :class="{ 'whitespace-pre': char === ' ' }"
      :style="getCharStyle(index)"
    >
      {{ char === ' ' ? '\u00A0' : char }}
    </span>
  </span>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  animation: {
    type: String,
    default: 'wave',
    validator: (v) => ['wave', 'bounce', 'pulse', 'swing', 'rubber', 'jelly', 'float'].includes(v)
  },
  color: {
    type: String,
    default: null
  },
  gradientColors: {
    type: Array,
    default: null // ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7']
  },
  duration: {
    type: Number,
    default: 1000 // ms
  },
  delay: {
    type: Number,
    default: 50 // ms between each character
  },
  amplitude: {
    type: Number,
    default: 10 // pixels for wave height
  },
  loop: {
    type: Boolean,
    default: true
  },
  autoStart: {
    type: Boolean,
    default: true
  },
  trigger: {
    type: String,
    default: 'auto',
    validator: (v) => ['auto', 'hover', 'click'].includes(v)
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['start', 'complete', 'loop'])

const containerRef = ref(null)
const isAnimating = ref(false)
const currentTime = ref(0)
let animationFrame = null
let startTime = null

const characters = computed(() => props.text.split(''))

const containerClasses = computed(() => ({
  'cursor-pointer': props.trigger === 'hover' || props.trigger === 'click'
}))

const animationConfigs = {
  wave: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const wave = Math.sin((progress - offset) * Math.PI * 2)
      return `translateY(${wave * props.amplitude}px)`
    }
  },
  bounce: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const adjusted = (progress - offset + 1) % 1
      const bounce = Math.abs(Math.sin(adjusted * Math.PI)) * props.amplitude
      return `translateY(${-bounce}px)`
    }
  },
  pulse: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const adjusted = (progress - offset + 1) % 1
      const scale = 1 + Math.sin(adjusted * Math.PI) * 0.3
      return `scale(${scale})`
    }
  },
  swing: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const adjusted = (progress - offset + 1) % 1
      const angle = Math.sin(adjusted * Math.PI * 2) * 15
      return `rotate(${angle}deg)`
    }
  },
  rubber: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const adjusted = (progress - offset + 1) % 1
      const scaleX = 1 + Math.sin(adjusted * Math.PI * 2) * 0.25
      const scaleY = 1 - Math.sin(adjusted * Math.PI * 2) * 0.25
      return `scale(${scaleX}, ${scaleY})`
    }
  },
  jelly: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const adjusted = (progress - offset + 1) % 1
      const skew = Math.sin(adjusted * Math.PI * 2) * 10
      return `skewX(${skew}deg)`
    }
  },
  float: {
    transform: (progress, index) => {
      const offset = (index * props.delay) / props.duration
      const adjusted = (progress - offset + 1) % 1
      const y = Math.sin(adjusted * Math.PI * 2) * props.amplitude
      const rotate = Math.sin(adjusted * Math.PI * 2) * 5
      return `translateY(${y}px) rotate(${rotate}deg)`
    }
  }
}

function getCharStyle(index) {
  const style = {}

  // Color
  if (props.gradientColors && props.gradientColors.length > 0) {
    const colorIndex = index % props.gradientColors.length
    style.color = props.gradientColors[colorIndex]
  } else if (props.color) {
    style.color = props.color
  }

  // Animation transform
  if (isAnimating.value && !props.disabled) {
    const config = animationConfigs[props.animation]
    if (config) {
      const progress = (currentTime.value % props.duration) / props.duration
      style.transform = config.transform(progress, index)
    }
  }

  // Transition
  style.transition = 'transform 0.1s ease-out'
  style.transformOrigin = 'center bottom'

  return style
}

function animate(timestamp) {
  if (!startTime) startTime = timestamp
  currentTime.value = timestamp - startTime

  // Check if one loop completed
  if (currentTime.value >= props.duration) {
    emit('loop')
    if (!props.loop) {
      stop()
      emit('complete')
      return
    }
  }

  animationFrame = requestAnimationFrame(animate)
}

function start() {
  if (props.disabled || isAnimating.value) return
  
  isAnimating.value = true
  startTime = null
  emit('start')
  animationFrame = requestAnimationFrame(animate)
}

function stop() {
  isAnimating.value = false
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
    animationFrame = null
  }
}

function toggle() {
  if (isAnimating.value) {
    stop()
  } else {
    start()
  }
}

function handleMouseEnter() {
  if (props.trigger === 'hover' && !props.disabled) {
    start()
  }
}

function handleMouseLeave() {
  if (props.trigger === 'hover') {
    stop()
  }
}

function handleClick() {
  if (props.trigger === 'click' && !props.disabled) {
    toggle()
  }
}

watch(() => props.disabled, (disabled) => {
  if (disabled) stop()
})

onMounted(() => {
  if (containerRef.value) {
    containerRef.value.addEventListener('mouseenter', handleMouseEnter)
    containerRef.value.addEventListener('mouseleave', handleMouseLeave)
    containerRef.value.addEventListener('click', handleClick)
  }

  if (props.autoStart && props.trigger === 'auto' && !props.disabled) {
    start()
  }
})

onUnmounted(() => {
  stop()
  
  if (containerRef.value) {
    containerRef.value.removeEventListener('mouseenter', handleMouseEnter)
    containerRef.value.removeEventListener('mouseleave', handleMouseLeave)
    containerRef.value.removeEventListener('click', handleClick)
  }
})

defineExpose({ start, stop, toggle, isAnimating })
</script>

<style scoped>
.wave-text {
  display: inline-flex;
  flex-wrap: wrap;
}

.wave-char {
  display: inline-block;
  will-change: transform;
}
</style>
