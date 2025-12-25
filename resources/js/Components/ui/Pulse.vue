<template>
  <span
    ref="elementRef"
    class="pulse-element inline-block"
    :class="[animationClass, { 'pulse-active': isPulsing }]"
    :style="pulseStyle"
  >
    <slot />
  </span>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'pulse',
    validator: (v) => ['pulse', 'ping', 'glow', 'flash', 'fade', 'throb', 'ripple'].includes(v)
  },
  color: {
    type: String,
    default: 'currentColor'
  },
  trigger: {
    type: String,
    default: 'always',
    validator: (v) => ['hover', 'click', 'always', 'manual'].includes(v)
  },
  duration: {
    type: Number,
    default: 2000
  },
  scale: {
    type: Number,
    default: 1.05
  },
  loop: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['start', 'end'])

const elementRef = ref(null)
const isPulsing = ref(false)
let timeoutId = null

const animationClass = computed(() => {
  if (!isPulsing.value) return ''
  
  const animations = {
    'pulse': 'animate-pulse-scale',
    'ping': 'animate-ping',
    'glow': 'animate-glow',
    'flash': 'animate-flash',
    'fade': 'animate-fade',
    'throb': 'animate-throb',
    'ripple': 'animate-ripple'
  }
  
  return animations[props.type]
})

const pulseStyle = computed(() => ({
  '--pulse-scale': props.scale,
  '--pulse-color': props.color,
  animationDuration: `${props.duration}ms`,
  animationIterationCount: props.loop && isPulsing.value ? 'infinite' : '1'
}))

function start() {
  if (props.disabled || isPulsing.value) return
  
  isPulsing.value = true
  emit('start')
  
  if (!props.loop) {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      stop()
    }, props.duration)
  }
}

function stop() {
  isPulsing.value = false
  emit('end')
  
  if (timeoutId) {
    clearTimeout(timeoutId)
    timeoutId = null
  }
}

function toggle() {
  if (isPulsing.value) {
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

defineExpose({ start, stop, toggle, isPulsing })
</script>

<style scoped>
.pulse-element {
  --pulse-scale: 1.05;
  --pulse-color: currentColor;
}

.animate-pulse-scale {
  animation: pulse-scale ease-in-out;
}

.animate-ping {
  animation: ping cubic-bezier(0, 0, 0.2, 1);
}

.animate-glow {
  animation: glow ease-in-out;
}

.animate-flash {
  animation: flash ease-in-out;
}

.animate-fade {
  animation: fade ease-in-out;
}

.animate-throb {
  animation: throb ease-in-out;
}

.animate-ripple {
  position: relative;
  overflow: visible;
  animation: ripple-base ease-in-out;
}

.animate-ripple::after {
  content: '';
  position: absolute;
  inset: 0;
  border: 2px solid var(--pulse-color);
  border-radius: inherit;
  animation: ripple-ring ease-out infinite;
  animation-duration: inherit;
}

@keyframes pulse-scale {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(var(--pulse-scale));
    opacity: 0.8;
  }
}

@keyframes ping {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

@keyframes glow {
  0%, 100% {
    box-shadow: 0 0 5px var(--pulse-color), 0 0 10px var(--pulse-color);
  }
  50% {
    box-shadow: 0 0 20px var(--pulse-color), 0 0 40px var(--pulse-color), 0 0 60px var(--pulse-color);
  }
}

@keyframes flash {
  0%, 50%, 100% {
    opacity: 1;
  }
  25%, 75% {
    opacity: 0;
  }
}

@keyframes fade {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.3;
  }
}

@keyframes throb {
  0%, 100% {
    transform: scale(1);
  }
  25% {
    transform: scale(var(--pulse-scale));
  }
  50% {
    transform: scale(1);
  }
  75% {
    transform: scale(var(--pulse-scale));
  }
}

@keyframes ripple-base {
  0%, 100% {
    transform: scale(1);
  }
}

@keyframes ripple-ring {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}
</style>
