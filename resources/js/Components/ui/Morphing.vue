<template>
  <component :is="as" :class="containerClass">
    {{ displayText }}
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  words: {
    type: Array,
    required: true
  },
  as: {
    type: String,
    default: 'span'
  },
  duration: {
    type: Number,
    default: 2000 // Time each word is displayed
  },
  morphDuration: {
    type: Number,
    default: 500 // Time for morph transition
  },
  loop: {
    type: Boolean,
    default: true
  },
  autoStart: {
    type: Boolean,
    default: true
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['change', 'complete', 'start', 'stop'])

const currentIndex = ref(0)
const morphProgress = ref(0)
const isAnimating = ref(false)

let intervalId = null
let morphAnimationId = null

const currentWord = computed(() => props.words[currentIndex.value] || '')
const nextWord = computed(() => {
  const nextIdx = (currentIndex.value + 1) % props.words.length
  return props.words[nextIdx] || ''
})

// Interpolate between two strings character by character
function morphText(from, to, progress) {
  const maxLength = Math.max(from.length, to.length)
  let result = ''

  for (let i = 0; i < maxLength; i++) {
    const fromChar = from[i] || ' '
    const toChar = to[i] || ' '

    if (progress === 0) {
      result += fromChar
    } else if (progress === 1) {
      result += toChar
    } else {
      // During transition, blend characters
      const charProgress = Math.min(1, progress * 2 - (i / maxLength))
      if (charProgress <= 0) {
        result += fromChar
      } else if (charProgress >= 1) {
        result += toChar
      } else {
        // Random character during transition
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*'
        result += chars[Math.floor(Math.random() * chars.length)]
      }
    }
  }

  return result.trim()
}

const displayText = computed(() => {
  if (morphProgress.value === 0) {
    return currentWord.value
  }
  return morphText(currentWord.value, nextWord.value, morphProgress.value)
})

function animateMorph() {
  const startTime = performance.now()

  function tick(now) {
    const elapsed = now - startTime
    const progress = Math.min(elapsed / props.morphDuration, 1)

    // Easing function
    morphProgress.value = 1 - Math.pow(1 - progress, 3)

    if (progress < 1) {
      morphAnimationId = requestAnimationFrame(tick)
    } else {
      // Morph complete, update index
      currentIndex.value = (currentIndex.value + 1) % props.words.length
      morphProgress.value = 0

      emit('change', currentIndex.value)

      // Check if we've completed a full loop
      if (currentIndex.value === 0 && !props.loop) {
        stop()
        emit('complete')
      }
    }
  }

  morphAnimationId = requestAnimationFrame(tick)
}

function start() {
  if (isAnimating.value) return

  isAnimating.value = true
  emit('start')

  intervalId = setInterval(() => {
    animateMorph()
  }, props.duration)
}

function stop() {
  isAnimating.value = false

  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }

  if (morphAnimationId) {
    cancelAnimationFrame(morphAnimationId)
    morphAnimationId = null
  }

  emit('stop')
}

function reset() {
  stop()
  currentIndex.value = 0
  morphProgress.value = 0
}

function restart() {
  reset()
  start()
}

function goTo(index) {
  if (index >= 0 && index < props.words.length) {
    currentIndex.value = index
    morphProgress.value = 0
  }
}

// Watch for words changes
watch(() => props.words, () => {
  restart()
}, { deep: true })

onMounted(() => {
  if (props.autoStart) {
    start()
  }
})

onUnmounted(() => {
  stop()
})

defineExpose({
  start,
  stop,
  reset,
  restart,
  goTo,
  currentIndex,
  isAnimating
})
</script>
