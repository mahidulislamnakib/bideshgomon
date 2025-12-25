<template>
  <component :is="as" :class="containerClass">
    <span>{{ displayText }}</span>
    <span
      v-if="showCursor"
      class="inline-block w-0.5 ml-0.5 animate-blink"
      :class="cursorClass"
      :style="{ height: '1em', backgroundColor: cursorColor }"
    />
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  strings: {
    type: Array,
    required: true
  },
  as: {
    type: String,
    default: 'span'
  },
  typeSpeed: {
    type: Number,
    default: 50
  },
  deleteSpeed: {
    type: Number,
    default: 30
  },
  pauseDelay: {
    type: Number,
    default: 1500
  },
  startDelay: {
    type: Number,
    default: 0
  },
  loop: {
    type: Boolean,
    default: true
  },
  showCursor: {
    type: Boolean,
    default: true
  },
  cursorColor: {
    type: String,
    default: 'currentColor'
  },
  cursorClass: {
    type: String,
    default: ''
  },
  containerClass: {
    type: String,
    default: ''
  },
  autoStart: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['complete', 'stringComplete', 'start', 'stop'])

const displayText = ref('')
const currentStringIndex = ref(0)
const isTyping = ref(false)
const isDeleting = ref(false)
const isPaused = ref(false)

let timeoutId = null

const currentString = computed(() => props.strings[currentStringIndex.value] || '')

function type() {
  if (!isTyping.value) return

  const target = currentString.value
  const current = displayText.value

  if (isDeleting.value) {
    // Deleting
    if (current.length > 0) {
      displayText.value = current.slice(0, -1)
      timeoutId = setTimeout(type, props.deleteSpeed)
    } else {
      // Finished deleting, move to next string
      isDeleting.value = false
      currentStringIndex.value = (currentStringIndex.value + 1) % props.strings.length

      if (currentStringIndex.value === 0 && !props.loop) {
        stop()
        emit('complete')
        return
      }

      timeoutId = setTimeout(type, props.typeSpeed)
    }
  } else {
    // Typing
    if (current.length < target.length) {
      displayText.value = target.slice(0, current.length + 1)
      timeoutId = setTimeout(type, props.typeSpeed)
    } else {
      // Finished typing current string
      emit('stringComplete', currentStringIndex.value)

      if (props.strings.length === 1 && !props.loop) {
        stop()
        emit('complete')
        return
      }

      // Pause before deleting
      isPaused.value = true
      timeoutId = setTimeout(() => {
        isPaused.value = false
        isDeleting.value = true
        type()
      }, props.pauseDelay)
    }
  }
}

function start() {
  if (isTyping.value) return

  isTyping.value = true
  emit('start')

  timeoutId = setTimeout(type, props.startDelay)
}

function stop() {
  isTyping.value = false
  isDeleting.value = false
  isPaused.value = false

  if (timeoutId) {
    clearTimeout(timeoutId)
    timeoutId = null
  }

  emit('stop')
}

function reset() {
  stop()
  displayText.value = ''
  currentStringIndex.value = 0
}

function restart() {
  reset()
  start()
}

// Watch for strings changes
watch(() => props.strings, () => {
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
  isTyping,
  displayText,
  currentStringIndex
})
</script>

<style scoped>
@keyframes blink {
  0%, 50% {
    opacity: 1;
  }
  51%, 100% {
    opacity: 0;
  }
}

.animate-blink {
  animation: blink 1s infinite;
}
</style>
