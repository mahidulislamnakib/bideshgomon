<template>
  <span
    ref="elementRef"
    class="shake-element inline-block"
    :class="[animationClass, { 'shake-active': isShaking }]"
    :style="shakeStyle"
  >
    <slot />
  </span>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'horizontal',
    validator: (v) => ['horizontal', 'vertical', 'rotate', 'crazy', 'slow', 'hard', 'little', 'chunk'].includes(v)
  },
  trigger: {
    type: String,
    default: 'hover',
    validator: (v) => ['hover', 'click', 'always', 'manual'].includes(v)
  },
  duration: {
    type: Number,
    default: 500
  },
  intensity: {
    type: Number,
    default: 1,
    validator: (v) => v >= 0.1 && v <= 3
  },
  loop: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['start', 'end'])

const elementRef = ref(null)
const isShaking = ref(false)
let timeoutId = null

const animationClass = computed(() => {
  if (!isShaking.value) return ''
  
  const animations = {
    horizontal: 'shake-horizontal',
    vertical: 'shake-vertical',
    rotate: 'shake-rotate',
    crazy: 'shake-crazy',
    slow: 'shake-slow',
    hard: 'shake-hard',
    little: 'shake-little',
    chunk: 'shake-chunk'
  }
  
  return animations[props.type]
})

const shakeStyle = computed(() => ({
  '--shake-intensity': props.intensity,
  animationDuration: props.type === 'slow' ? '5s' : `${props.duration}ms`,
  animationIterationCount: props.loop && isShaking.value ? 'infinite' : '1'
}))

function start() {
  if (props.disabled || isShaking.value) return
  
  isShaking.value = true
  emit('start')
  
  if (!props.loop) {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      stop()
    }, props.duration)
  }
}

function stop() {
  isShaking.value = false
  emit('end')
  
  if (timeoutId) {
    clearTimeout(timeoutId)
    timeoutId = null
  }
}

function toggle() {
  if (isShaking.value) {
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
    start()
  }
}

watch(() => props.disabled, (disabled) => {
  if (disabled) stop()
})

onMounted(() => {
  if (elementRef.value) {
    elementRef.value.addEventListener('mouseenter', handleMouseEnter)
    elementRef.value.addEventListener('mouseleave', handleMouseLeave)
    elementRef.value.addEventListener('click', handleClick)
  }
  
  if (props.trigger === 'always' && !props.disabled) {
    start()
  }
})

onUnmounted(() => {
  if (timeoutId) clearTimeout(timeoutId)
  
  if (elementRef.value) {
    elementRef.value.removeEventListener('mouseenter', handleMouseEnter)
    elementRef.value.removeEventListener('mouseleave', handleMouseLeave)
    elementRef.value.removeEventListener('click', handleClick)
  }
})

defineExpose({ start, stop, toggle, isShaking })
</script>

<style scoped>
.shake-element {
  --shake-intensity: 1;
}

.shake-horizontal {
  animation: shake-horizontal ease-in-out;
}

.shake-vertical {
  animation: shake-vertical ease-in-out;
}

.shake-rotate {
  animation: shake-rotate ease-in-out;
}

.shake-crazy {
  animation: shake-crazy linear;
}

.shake-slow {
  animation: shake-slow ease-in-out;
}

.shake-hard {
  animation: shake-hard ease-in-out;
}

.shake-little {
  animation: shake-little ease-in-out;
}

.shake-chunk {
  animation: shake-chunk ease-in-out;
}

@keyframes shake-horizontal {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(calc(-10px * var(--shake-intensity))); }
  20%, 40%, 60%, 80% { transform: translateX(calc(10px * var(--shake-intensity))); }
}

@keyframes shake-vertical {
  0%, 100% { transform: translateY(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateY(calc(-10px * var(--shake-intensity))); }
  20%, 40%, 60%, 80% { transform: translateY(calc(10px * var(--shake-intensity))); }
}

@keyframes shake-rotate {
  0%, 100% { transform: rotate(0deg); }
  10%, 30%, 50%, 70%, 90% { transform: rotate(calc(-5deg * var(--shake-intensity))); }
  20%, 40%, 60%, 80% { transform: rotate(calc(5deg * var(--shake-intensity))); }
}

@keyframes shake-crazy {
  0% { transform: translate(0, 0) rotate(0deg); }
  10% { transform: translate(calc(-5px * var(--shake-intensity)), calc(-5px * var(--shake-intensity))) rotate(calc(-5deg * var(--shake-intensity))); }
  20% { transform: translate(calc(5px * var(--shake-intensity)), calc(5px * var(--shake-intensity))) rotate(calc(5deg * var(--shake-intensity))); }
  30% { transform: translate(calc(-5px * var(--shake-intensity)), calc(5px * var(--shake-intensity))) rotate(calc(-5deg * var(--shake-intensity))); }
  40% { transform: translate(calc(5px * var(--shake-intensity)), calc(-5px * var(--shake-intensity))) rotate(calc(5deg * var(--shake-intensity))); }
  50% { transform: translate(calc(-5px * var(--shake-intensity)), calc(-5px * var(--shake-intensity))) rotate(calc(-5deg * var(--shake-intensity))); }
  60% { transform: translate(calc(5px * var(--shake-intensity)), calc(5px * var(--shake-intensity))) rotate(calc(5deg * var(--shake-intensity))); }
  70% { transform: translate(calc(-5px * var(--shake-intensity)), calc(5px * var(--shake-intensity))) rotate(calc(-5deg * var(--shake-intensity))); }
  80% { transform: translate(calc(5px * var(--shake-intensity)), calc(-5px * var(--shake-intensity))) rotate(calc(5deg * var(--shake-intensity))); }
  90% { transform: translate(calc(-5px * var(--shake-intensity)), calc(-5px * var(--shake-intensity))) rotate(calc(-5deg * var(--shake-intensity))); }
  100% { transform: translate(0, 0) rotate(0deg); }
}

@keyframes shake-slow {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  25% { transform: translate(calc(5px * var(--shake-intensity)), calc(5px * var(--shake-intensity))) rotate(calc(5deg * var(--shake-intensity))); }
  50% { transform: translate(calc(-5px * var(--shake-intensity)), calc(-5px * var(--shake-intensity))) rotate(calc(-5deg * var(--shake-intensity))); }
  75% { transform: translate(calc(-5px * var(--shake-intensity)), calc(5px * var(--shake-intensity))) rotate(calc(5deg * var(--shake-intensity))); }
}

@keyframes shake-hard {
  0%, 100% { transform: translate(0, 0); }
  10%, 30%, 50%, 70%, 90% { transform: translate(calc(-20px * var(--shake-intensity)), 0); }
  20%, 40%, 60%, 80% { transform: translate(calc(20px * var(--shake-intensity)), 0); }
}

@keyframes shake-little {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  10% { transform: translate(calc(-1px * var(--shake-intensity)), calc(-1px * var(--shake-intensity))) rotate(calc(-1deg * var(--shake-intensity))); }
  20% { transform: translate(calc(1px * var(--shake-intensity)), calc(1px * var(--shake-intensity))) rotate(calc(1deg * var(--shake-intensity))); }
  30% { transform: translate(calc(-1px * var(--shake-intensity)), calc(1px * var(--shake-intensity))) rotate(calc(1deg * var(--shake-intensity))); }
  40% { transform: translate(calc(1px * var(--shake-intensity)), calc(-1px * var(--shake-intensity))) rotate(calc(-1deg * var(--shake-intensity))); }
  50% { transform: translate(calc(-1px * var(--shake-intensity)), calc(-1px * var(--shake-intensity))) rotate(calc(-1deg * var(--shake-intensity))); }
  60% { transform: translate(calc(1px * var(--shake-intensity)), calc(1px * var(--shake-intensity))) rotate(calc(1deg * var(--shake-intensity))); }
  70% { transform: translate(calc(-1px * var(--shake-intensity)), calc(1px * var(--shake-intensity))) rotate(calc(-1deg * var(--shake-intensity))); }
  80% { transform: translate(calc(1px * var(--shake-intensity)), calc(-1px * var(--shake-intensity))) rotate(calc(1deg * var(--shake-intensity))); }
  90% { transform: translate(calc(-1px * var(--shake-intensity)), calc(-1px * var(--shake-intensity))) rotate(calc(1deg * var(--shake-intensity))); }
}

@keyframes shake-chunk {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  25% { transform: translate(calc(-30px * var(--shake-intensity)), 0) rotate(calc(-10deg * var(--shake-intensity))); }
  50% { transform: translate(calc(30px * var(--shake-intensity)), 0) rotate(calc(10deg * var(--shake-intensity))); }
  75% { transform: translate(calc(-30px * var(--shake-intensity)), 0) rotate(calc(-10deg * var(--shake-intensity))); }
}
</style>
