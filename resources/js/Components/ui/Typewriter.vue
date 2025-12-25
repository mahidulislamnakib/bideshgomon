<template>
  <component 
    :is="tag"
    ref="containerRef"
    :class="containerClasses"
  >
    <span ref="textRef">{{ displayText }}</span>
    <span 
      v-if="showCursor"
      :class="cursorClasses"
      :style="cursorStyle"
    >{{ cursorChar }}</span>
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // Text content
  text: {
    type: [String, Array],
    required: true
  },
  
  // Typing settings
  speed: {
    type: Number,
    default: 50
  },
  deleteSpeed: {
    type: Number,
    default: 30
  },
  delay: {
    type: Number,
    default: 1500
  },
  startDelay: {
    type: Number,
    default: 0
  },
  
  // Behavior
  loop: {
    type: Boolean,
    default: false
  },
  loopDelay: {
    type: Number,
    default: 1000
  },
  deleteOnComplete: {
    type: Boolean,
    default: true
  },
  pauseOnHover: {
    type: Boolean,
    default: false
  },
  
  // Cursor
  showCursor: {
    type: Boolean,
    default: true
  },
  cursorChar: {
    type: String,
    default: '|'
  },
  cursorBlink: {
    type: Boolean,
    default: true
  },
  cursorBlinkSpeed: {
    type: Number,
    default: 500
  },
  
  // Styling
  tag: {
    type: String,
    default: 'span'
  },
  cursorColor: {
    type: String,
    default: ''
  },
  
  // State
  autoStart: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['start', 'complete', 'loop', 'type', 'delete', 'pause', 'resume'])

const containerRef = ref(null)
const textRef = ref(null)
const displayText = ref('')
const isTyping = ref(false)
const isPaused = ref(false)
const isDeleting = ref(false)
const currentIndex = ref(0)
const cursorVisible = ref(true)

let typeTimeout = null
let cursorInterval = null

// Get texts array
const texts = computed(() => {
  return Array.isArray(props.text) ? props.text : [props.text]
})

// Container classes
const containerClasses = computed(() => [
  'inline'
])

// Cursor classes
const cursorClasses = computed(() => [
  'inline-block',
  props.cursorBlink && cursorVisible.value ? 'opacity-100' : props.cursorBlink ? 'opacity-0' : 'opacity-100',
  'transition-opacity duration-100'
])

// Cursor style
const cursorStyle = computed(() => ({
  color: props.cursorColor || undefined
}))

// Type a single character
function typeChar() {
  if (props.disabled || isPaused.value) return
  
  const currentText = texts.value[currentIndex.value]
  
  if (isDeleting.value) {
    // Deleting
    if (displayText.value.length > 0) {
      displayText.value = displayText.value.slice(0, -1)
      emit('delete', displayText.value)
      typeTimeout = setTimeout(typeChar, props.deleteSpeed)
    } else {
      // Finished deleting
      isDeleting.value = false
      currentIndex.value = (currentIndex.value + 1) % texts.value.length
      
      if (currentIndex.value === 0 && !props.loop) {
        // Completed all texts, not looping
        isTyping.value = false
        emit('complete')
      } else {
        // Continue to next text
        if (currentIndex.value === 0) {
          emit('loop')
        }
        typeTimeout = setTimeout(typeChar, props.loopDelay)
      }
    }
  } else {
    // Typing
    if (displayText.value.length < currentText.length) {
      displayText.value = currentText.slice(0, displayText.value.length + 1)
      emit('type', displayText.value)
      typeTimeout = setTimeout(typeChar, props.speed)
    } else {
      // Finished typing current text
      if (texts.value.length > 1 || props.loop) {
        // Multiple texts or looping - delete after delay
        if (props.deleteOnComplete) {
          typeTimeout = setTimeout(() => {
            isDeleting.value = true
            typeChar()
          }, props.delay)
        }
      } else {
        // Single text, no loop
        isTyping.value = false
        emit('complete')
      }
    }
  }
}

// Start typing
function start() {
  if (props.disabled || isTyping.value) return
  
  isTyping.value = true
  displayText.value = ''
  currentIndex.value = 0
  isDeleting.value = false
  
  emit('start')
  
  typeTimeout = setTimeout(typeChar, props.startDelay)
}

// Stop typing
function stop() {
  if (typeTimeout) {
    clearTimeout(typeTimeout)
    typeTimeout = null
  }
  isTyping.value = false
}

// Pause typing
function pause() {
  isPaused.value = true
  emit('pause')
}

// Resume typing
function resume() {
  if (isPaused.value) {
    isPaused.value = false
    emit('resume')
    typeChar()
  }
}

// Reset to initial state
function reset() {
  stop()
  displayText.value = ''
  currentIndex.value = 0
  isDeleting.value = false
}

// Complete immediately
function complete() {
  stop()
  displayText.value = texts.value[texts.value.length - 1]
  currentIndex.value = texts.value.length - 1
  emit('complete')
}

// Cursor blink
function startCursorBlink() {
  if (cursorInterval) clearInterval(cursorInterval)
  
  if (props.cursorBlink) {
    cursorInterval = setInterval(() => {
      cursorVisible.value = !cursorVisible.value
    }, props.cursorBlinkSpeed)
  }
}

// Hover handlers
function handleMouseEnter() {
  if (props.pauseOnHover && isTyping.value) {
    pause()
  }
}

function handleMouseLeave() {
  if (props.pauseOnHover && isPaused.value) {
    resume()
  }
}

// Watch for text changes
watch(() => props.text, () => {
  if (props.autoStart) {
    reset()
    start()
  }
})

// Watch disabled
watch(() => props.disabled, (disabled) => {
  if (disabled) {
    stop()
  } else if (props.autoStart) {
    start()
  }
})

// Lifecycle
onMounted(() => {
  startCursorBlink()
  
  if (props.autoStart && !props.disabled) {
    start()
  }
  
  // Add hover listeners
  containerRef.value?.addEventListener('mouseenter', handleMouseEnter)
  containerRef.value?.addEventListener('mouseleave', handleMouseLeave)
})

onUnmounted(() => {
  stop()
  if (cursorInterval) clearInterval(cursorInterval)
  
  containerRef.value?.removeEventListener('mouseenter', handleMouseEnter)
  containerRef.value?.removeEventListener('mouseleave', handleMouseLeave)
})

// Expose for parent control
defineExpose({
  start,
  stop,
  pause,
  resume,
  reset,
  complete,
  isTyping,
  isPaused,
  displayText,
  currentIndex
})
</script>
