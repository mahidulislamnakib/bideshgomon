<template>
  <component 
    :is="tag"
    ref="textRef"
    :class="containerClasses"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    <span
      v-for="(char, index) in displayChars"
      :key="index"
      :class="charClasses(index)"
      :style="charStyle(index)"
    >{{ char }}</span>
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // Text content
  text: {
    type: String,
    required: true
  },
  
  // Animation settings
  trigger: {
    type: String,
    default: 'mount',
    validator: (v) => ['mount', 'hover', 'manual', 'visible'].includes(v)
  },
  duration: {
    type: Number,
    default: 1000
  },
  delay: {
    type: Number,
    default: 0
  },
  stagger: {
    type: Number,
    default: 30
  },
  
  // Scramble settings
  characters: {
    type: String,
    default: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?'
  },
  mode: {
    type: String,
    default: 'reveal',
    validator: (v) => ['reveal', 'scramble', 'typewriter', 'decode'].includes(v)
  },
  preserveSpaces: {
    type: Boolean,
    default: true
  },
  
  // Direction
  direction: {
    type: String,
    default: 'ltr',
    validator: (v) => ['ltr', 'rtl', 'random', 'center'].includes(v)
  },
  
  // Styling
  tag: {
    type: String,
    default: 'span'
  },
  scrambleColor: {
    type: String,
    default: ''
  },
  revealedColor: {
    type: String,
    default: ''
  },
  
  // Loop
  loop: {
    type: Boolean,
    default: false
  },
  loopDelay: {
    type: Number,
    default: 2000
  },
  
  // State
  playing: {
    type: Boolean,
    default: undefined
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['start', 'complete', 'update:playing'])

const textRef = ref(null)
const displayChars = ref([])
const revealedIndices = ref(new Set())
const isPlaying = ref(false)
const isComplete = ref(false)

let animationFrameId = null
let timeoutId = null
let startTime = null
let observer = null

// Initialize display with scrambled or empty chars
function initDisplay() {
  const chars = props.text.split('')
  
  switch (props.mode) {
    case 'reveal':
    case 'decode':
      displayChars.value = chars.map((char, i) => 
        props.preserveSpaces && char === ' ' ? ' ' : getRandomChar()
      )
      break
    case 'typewriter':
      displayChars.value = chars.map(() => '')
      break
    case 'scramble':
      displayChars.value = [...chars]
      break
    default:
      displayChars.value = [...chars]
  }
  
  revealedIndices.value.clear()
  isComplete.value = false
}

// Get random character from pool
function getRandomChar() {
  return props.characters[Math.floor(Math.random() * props.characters.length)]
}

// Get reveal order based on direction
function getRevealOrder() {
  const indices = Array.from({ length: props.text.length }, (_, i) => i)
  
  switch (props.direction) {
    case 'rtl':
      return indices.reverse()
    case 'random':
      return indices.sort(() => Math.random() - 0.5)
    case 'center':
      const center = Math.floor(indices.length / 2)
      const result = []
      for (let i = 0; i <= center; i++) {
        if (center + i < indices.length) result.push(center + i)
        if (center - i >= 0 && i !== 0) result.push(center - i)
      }
      return result
    default: // ltr
      return indices
  }
}

// Main animation loop
function animate(timestamp) {
  if (!startTime) startTime = timestamp
  const elapsed = timestamp - startTime
  
  if (elapsed < props.delay) {
    animationFrameId = requestAnimationFrame(animate)
    return
  }
  
  const adjustedElapsed = elapsed - props.delay
  const order = getRevealOrder()
  
  // Calculate which characters should be revealed
  const chars = props.text.split('')
  const newDisplay = [...displayChars.value]
  
  order.forEach((index, i) => {
    const charDelay = i * props.stagger
    const charElapsed = adjustedElapsed - charDelay
    
    if (charElapsed < 0) {
      // Not started yet
      if (props.mode === 'typewriter') {
        newDisplay[index] = ''
      }
    } else if (charElapsed < props.duration) {
      // Scrambling
      const progress = charElapsed / props.duration
      
      if (props.mode === 'typewriter') {
        if (progress > 0.3) {
          newDisplay[index] = chars[index]
          revealedIndices.value.add(index)
        } else {
          newDisplay[index] = getRandomChar()
        }
      } else if (props.mode === 'decode') {
        // More scrambling at start, then reveal
        if (progress > 0.7) {
          newDisplay[index] = chars[index]
          revealedIndices.value.add(index)
        } else {
          newDisplay[index] = props.preserveSpaces && chars[index] === ' ' 
            ? ' ' 
            : getRandomChar()
        }
      } else {
        // Regular scramble/reveal
        if (props.preserveSpaces && chars[index] === ' ') {
          newDisplay[index] = ' '
          revealedIndices.value.add(index)
        } else {
          newDisplay[index] = getRandomChar()
        }
      }
    } else {
      // Revealed
      newDisplay[index] = chars[index]
      revealedIndices.value.add(index)
    }
  })
  
  displayChars.value = newDisplay
  
  // Check if complete
  const totalDuration = props.delay + props.duration + (order.length * props.stagger)
  
  if (adjustedElapsed >= totalDuration - props.delay) {
    // Ensure final text is correct
    displayChars.value = chars
    isPlaying.value = false
    isComplete.value = true
    emit('complete')
    emit('update:playing', false)
    
    // Handle loop
    if (props.loop) {
      timeoutId = setTimeout(() => {
        initDisplay()
        play()
      }, props.loopDelay)
    }
    
    return
  }
  
  animationFrameId = requestAnimationFrame(animate)
}

// Control methods
function play() {
  if (props.disabled || isPlaying.value) return
  
  initDisplay()
  isPlaying.value = true
  startTime = null
  
  emit('start')
  emit('update:playing', true)
  
  animationFrameId = requestAnimationFrame(animate)
}

function stop() {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId)
    animationFrameId = null
  }
  if (timeoutId) {
    clearTimeout(timeoutId)
    timeoutId = null
  }
  
  isPlaying.value = false
  emit('update:playing', false)
}

function reset() {
  stop()
  initDisplay()
  revealedIndices.value.clear()
  isComplete.value = false
}

function complete() {
  stop()
  displayChars.value = props.text.split('')
  revealedIndices.value = new Set(displayChars.value.map((_, i) => i))
  isComplete.value = true
  emit('complete')
}

// Event handlers
function onMouseEnter() {
  if (props.trigger === 'hover' && !props.disabled) {
    play()
  }
}

function onMouseLeave() {
  if (props.trigger === 'hover') {
    // Optionally reset or complete on mouse leave
  }
}

// Setup intersection observer for visible trigger
function setupVisibilityObserver() {
  if (props.trigger !== 'visible' || !textRef.value) return
  
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        play()
        observer.disconnect()
      }
    },
    { threshold: 0.5 }
  )
  
  observer.observe(textRef.value)
}

// Container classes
const containerClasses = computed(() => [
  'inline-block'
])

// Character classes
function charClasses(index) {
  return [
    'transition-colors duration-150',
    revealedIndices.value.has(index) && 'revealed'
  ]
}

// Character styles
function charStyle(index) {
  const isRevealed = revealedIndices.value.has(index)
  
  return {
    color: isRevealed && props.revealedColor 
      ? props.revealedColor 
      : !isRevealed && props.scrambleColor 
        ? props.scrambleColor 
        : undefined
  }
}

// Watch for text changes
watch(() => props.text, () => {
  initDisplay()
  if (props.trigger === 'mount' && !props.disabled) {
    play()
  }
})

// Watch for external playing control
watch(() => props.playing, (newVal) => {
  if (newVal === undefined) return
  
  if (newVal && !isPlaying.value) {
    play()
  } else if (!newVal && isPlaying.value) {
    stop()
  }
})

// Watch for disabled changes
watch(() => props.disabled, (disabled) => {
  if (disabled) {
    stop()
    displayChars.value = props.text.split('')
  }
})

// Lifecycle
onMounted(() => {
  initDisplay()
  
  if (props.trigger === 'mount' && !props.disabled) {
    play()
  } else if (props.trigger === 'visible') {
    setupVisibilityObserver()
  }
})

onUnmounted(() => {
  stop()
  observer?.disconnect()
})

// Expose for parent control
defineExpose({
  play,
  stop,
  reset,
  complete,
  isPlaying,
  isComplete
})
</script>
