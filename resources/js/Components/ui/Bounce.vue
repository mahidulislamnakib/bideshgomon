<template>
  <span
    ref="elementRef"
    class="bounce-element inline-block"
    :class="[animationClass, { 'bounce-active': isBouncing }]"
    :style="bounceStyle"
  >
    <slot />
  </span>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'bounce',
    validator: (v) => ['bounce', 'bounce-in', 'bounce-out', 'rubber', 'jello', 'swing', 'tada', 'wobble', 'heartbeat'].includes(v)
  },
  trigger: {
    type: String,
    default: 'hover',
    validator: (v) => ['hover', 'click', 'always', 'manual'].includes(v)
  },
  duration: {
    type: Number,
    default: 1000
  },
  delay: {
    type: Number,
    default: 0
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
const isBouncing = ref(false)
let timeoutId = null
let delayTimeoutId = null

const animationClass = computed(() => {
  if (!isBouncing.value) return ''
  
  const animations = {
    'bounce': 'animate-bounce',
    'bounce-in': 'animate-bounce-in',
    'bounce-out': 'animate-bounce-out',
    'rubber': 'animate-rubber',
    'jello': 'animate-jello',
    'swing': 'animate-swing',
    'tada': 'animate-tada',
    'wobble': 'animate-wobble',
    'heartbeat': 'animate-heartbeat'
  }
  
  return animations[props.type]
})

const bounceStyle = computed(() => ({
  animationDuration: `${props.duration}ms`,
  animationDelay: `${props.delay}ms`,
  animationIterationCount: props.loop && isBouncing.value ? 'infinite' : '1'
}))

function start() {
  if (props.disabled || isBouncing.value) return
  
  if (props.delay > 0) {
    delayTimeoutId = setTimeout(() => {
      doStart()
    }, props.delay)
  } else {
    doStart()
  }
}

function doStart() {
  isBouncing.value = true
  emit('start')
  
  if (!props.loop) {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      stop()
    }, props.duration)
  }
}

function stop() {
  isBouncing.value = false
  emit('end')
  
  if (timeoutId) {
    clearTimeout(timeoutId)
    timeoutId = null
  }
  if (delayTimeoutId) {
    clearTimeout(delayTimeoutId)
    delayTimeoutId = null
  }
}

function toggle() {
  if (isBouncing.value) {
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
  if (delayTimeoutId) clearTimeout(delayTimeoutId)
  
  if (elementRef.value) {
    elementRef.value.removeEventListener('mouseenter', handleMouseEnter)
    elementRef.value.removeEventListener('mouseleave', handleMouseLeave)
    elementRef.value.removeEventListener('click', handleClick)
  }
})

defineExpose({ start, stop, toggle, isBouncing })
</script>

<style scoped>
.bounce-element {
  transform-origin: center bottom;
}

.animate-bounce {
  animation: bounce ease;
}

.animate-bounce-in {
  animation: bounce-in ease-out;
}

.animate-bounce-out {
  animation: bounce-out ease-in;
}

.animate-rubber {
  animation: rubber ease;
}

.animate-jello {
  animation: jello ease;
}

.animate-swing {
  animation: swing ease-in-out;
  transform-origin: top center;
}

.animate-tada {
  animation: tada ease;
}

.animate-wobble {
  animation: wobble ease-in-out;
}

.animate-heartbeat {
  animation: heartbeat ease-in-out;
}

@keyframes bounce {
  0%, 20%, 53%, 100% {
    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
    transform: translateY(0);
  }
  40%, 43% {
    animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    transform: translateY(-30px);
  }
  70% {
    animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    transform: translateY(-15px);
  }
  80% {
    transform: translateY(0);
  }
  90% {
    transform: translateY(-4px);
  }
}

@keyframes bounce-in {
  0% {
    opacity: 0;
    transform: scale(0.3);
  }
  50% {
    transform: scale(1.05);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes bounce-out {
  0% {
    transform: scale(1);
  }
  25% {
    transform: scale(0.95);
  }
  50% {
    opacity: 1;
    transform: scale(1.1);
  }
  100% {
    opacity: 0;
    transform: scale(0.3);
  }
}

@keyframes rubber {
  0% { transform: scale(1, 1); }
  30% { transform: scale(1.25, 0.75); }
  40% { transform: scale(0.75, 1.25); }
  50% { transform: scale(1.15, 0.85); }
  65% { transform: scale(0.95, 1.05); }
  75% { transform: scale(1.05, 0.95); }
  100% { transform: scale(1, 1); }
}

@keyframes jello {
  0%, 11.1%, 100% { transform: none; }
  22.2% { transform: skewX(-12.5deg) skewY(-12.5deg); }
  33.3% { transform: skewX(6.25deg) skewY(6.25deg); }
  44.4% { transform: skewX(-3.125deg) skewY(-3.125deg); }
  55.5% { transform: skewX(1.5625deg) skewY(1.5625deg); }
  66.6% { transform: skewX(-0.78125deg) skewY(-0.78125deg); }
  77.7% { transform: skewX(0.390625deg) skewY(0.390625deg); }
  88.8% { transform: skewX(-0.1953125deg) skewY(-0.1953125deg); }
}

@keyframes swing {
  20% { transform: rotate(15deg); }
  40% { transform: rotate(-10deg); }
  60% { transform: rotate(5deg); }
  80% { transform: rotate(-5deg); }
  100% { transform: rotate(0deg); }
}

@keyframes tada {
  0% { transform: scale(1) rotate(0deg); }
  10%, 20% { transform: scale(0.9) rotate(-3deg); }
  30%, 50%, 70%, 90% { transform: scale(1.1) rotate(3deg); }
  40%, 60%, 80% { transform: scale(1.1) rotate(-3deg); }
  100% { transform: scale(1) rotate(0deg); }
}

@keyframes wobble {
  0% { transform: translateX(0%); }
  15% { transform: translateX(-25%) rotate(-5deg); }
  30% { transform: translateX(20%) rotate(3deg); }
  45% { transform: translateX(-15%) rotate(-3deg); }
  60% { transform: translateX(10%) rotate(2deg); }
  75% { transform: translateX(-5%) rotate(-1deg); }
  100% { transform: translateX(0%); }
}

@keyframes heartbeat {
  0% { transform: scale(1); }
  14% { transform: scale(1.3); }
  28% { transform: scale(1); }
  42% { transform: scale(1.3); }
  70% { transform: scale(1); }
}
</style>
