<template>
  <component
    :is="as"
    ref="containerRef"
    class="overflow-hidden"
  >
    <span
      v-for="(char, index) in characters"
      :key="index"
      class="inline-block transition-all"
      :class="getCharClass(index)"
      :style="getCharStyle(index)"
    >
      <template v-if="char === ' '">&nbsp;</template>
      <template v-else>{{ char }}</template>
    </span>
  </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  as: {
    type: String,
    default: 'span'
  },
  animation: {
    type: String,
    default: 'fade',
    validator: (v) => ['fade', 'slide-up', 'slide-down', 'slide-left', 'slide-right', 'blur', 'scale', 'rotate'].includes(v)
  },
  duration: {
    type: Number,
    default: 50 // ms per character
  },
  delay: {
    type: Number,
    default: 0
  },
  stagger: {
    type: Number,
    default: 30 // ms between each character
  },
  startOnVisible: {
    type: Boolean,
    default: true
  },
  loop: {
    type: Boolean,
    default: false
  },
  loopDelay: {
    type: Number,
    default: 2000
  },
  reverse: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['start', 'complete', 'loop'])

const containerRef = ref(null)
const revealedCount = ref(0)
const isRevealing = ref(false)
const hasStarted = ref(false)

const characters = computed(() => props.text.split(''))

const totalCharacters = computed(() => characters.value.length)

function getCharClass(index) {
  const isRevealed = props.reverse
    ? index >= totalCharacters.value - revealedCount.value
    : index < revealedCount.value

  if (isRevealed) return 'opacity-100'

  // Base hidden state classes
  const animations = {
    fade: 'opacity-0',
    'slide-up': 'opacity-0 translate-y-full',
    'slide-down': 'opacity-0 -translate-y-full',
    'slide-left': 'opacity-0 translate-x-full',
    'slide-right': 'opacity-0 -translate-x-full',
    blur: 'opacity-0 blur-sm',
    scale: 'opacity-0 scale-0',
    rotate: 'opacity-0 rotate-180'
  }

  return animations[props.animation]
}

function getCharStyle(index) {
  const isRevealed = props.reverse
    ? index >= totalCharacters.value - revealedCount.value
    : index < revealedCount.value

  return {
    transitionDuration: `${props.duration}ms`,
    transitionDelay: isRevealed ? '0ms' : `${index * props.stagger}ms`,
    transitionProperty: 'all',
    transitionTimingFunction: 'cubic-bezier(0.4, 0, 0.2, 1)'
  }
}

let animationInterval = null
let loopTimeout = null

function startReveal() {
  if (isRevealing.value) return

  isRevealing.value = true
  hasStarted.value = true
  emit('start')

  revealedCount.value = 0

  animationInterval = setInterval(() => {
    revealedCount.value++

    if (revealedCount.value >= totalCharacters.value) {
      clearInterval(animationInterval)
      isRevealing.value = false
      emit('complete')

      if (props.loop) {
        loopTimeout = setTimeout(() => {
          emit('loop')
          reset()
          startReveal()
        }, props.loopDelay)
      }
    }
  }, props.stagger)
}

function reset() {
  if (animationInterval) clearInterval(animationInterval)
  if (loopTimeout) clearTimeout(loopTimeout)
  revealedCount.value = 0
  isRevealing.value = false
}

function restart() {
  reset()
  setTimeout(startReveal, props.delay)
}

// Watch text changes
watch(() => props.text, () => {
  restart()
})

// Intersection Observer
let observer = null

onMounted(() => {
  if (props.startOnVisible && containerRef.value) {
    observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !hasStarted.value) {
          setTimeout(startReveal, props.delay)
        }
      },
      { threshold: 0.1 }
    )
    observer.observe(containerRef.value)
  } else {
    setTimeout(startReveal, props.delay)
  }
})

onUnmounted(() => {
  reset()
  observer?.disconnect()
})

defineExpose({
  startReveal,
  reset,
  restart,
  isRevealing,
  revealedCount
})
</script>
