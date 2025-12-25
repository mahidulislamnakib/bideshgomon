<template>
  <canvas
    ref="canvasRef"
    class="matrix-rain"
    :class="{ 'pointer-events-none': !interactive }"
    :style="canvasStyle"
  />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  width: {
    type: [Number, String],
    default: '100%'
  },
  height: {
    type: [Number, String],
    default: '100%'
  },
  fontSize: {
    type: Number,
    default: 14
  },
  color: {
    type: String,
    default: '#00ff00'
  },
  backgroundColor: {
    type: String,
    default: 'rgba(0, 0, 0, 0.05)'
  },
  speed: {
    type: Number,
    default: 50 // ms between frames
  },
  density: {
    type: Number,
    default: 1, // 0.1 to 2
    validator: (v) => v >= 0.1 && v <= 2
  },
  characters: {
    type: String,
    default: 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン0123456789'
  },
  interactive: {
    type: Boolean,
    default: false
  },
  autoStart: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['start', 'stop'])

const canvasRef = ref(null)
const isRunning = ref(false)
let ctx = null
let columns = []
let animationId = null
let intervalId = null

const canvasStyle = computed(() => ({
  width: typeof props.width === 'number' ? `${props.width}px` : props.width,
  height: typeof props.height === 'number' ? `${props.height}px` : props.height,
  display: 'block'
}))

function initCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return

  // Set actual canvas dimensions
  const rect = canvas.getBoundingClientRect()
  canvas.width = rect.width
  canvas.height = rect.height

  ctx = canvas.getContext('2d')
  
  // Initialize columns
  const columnCount = Math.floor((canvas.width / props.fontSize) * props.density)
  columns = Array(columnCount).fill(0).map(() => ({
    y: Math.random() * canvas.height,
    speed: 0.5 + Math.random() * 1.5
  }))
}

function getRandomChar() {
  return props.characters[Math.floor(Math.random() * props.characters.length)]
}

function draw() {
  if (!ctx || !canvasRef.value) return

  const canvas = canvasRef.value
  
  // Semi-transparent background for trail effect
  ctx.fillStyle = props.backgroundColor
  ctx.fillRect(0, 0, canvas.width, canvas.height)

  // Draw characters
  ctx.fillStyle = props.color
  ctx.font = `${props.fontSize}px monospace`

  const columnWidth = canvas.width / columns.length

  columns.forEach((column, i) => {
    const char = getRandomChar()
    const x = i * columnWidth
    
    // Draw main character (brighter)
    ctx.fillStyle = props.color
    ctx.fillText(char, x, column.y)
    
    // Draw a brighter head
    ctx.fillStyle = '#ffffff'
    ctx.fillText(char, x, column.y)
    ctx.fillStyle = props.color

    // Move column down
    column.y += props.fontSize * column.speed

    // Reset column when it goes off screen
    if (column.y > canvas.height && Math.random() > 0.975) {
      column.y = 0
      column.speed = 0.5 + Math.random() * 1.5
    }
  })
}

function start() {
  if (isRunning.value) return
  
  isRunning.value = true
  emit('start')
  
  intervalId = setInterval(draw, props.speed)
}

function stop() {
  isRunning.value = false
  emit('stop')
  
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

function clear() {
  if (!ctx || !canvasRef.value) return
  ctx.fillStyle = '#000000'
  ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height)
}

function reset() {
  stop()
  initCanvas()
  clear()
  if (props.autoStart) {
    start()
  }
}

// Handle resize
function handleResize() {
  const wasRunning = isRunning.value
  stop()
  initCanvas()
  if (wasRunning) start()
}

watch(() => props.speed, () => {
  if (isRunning.value) {
    stop()
    start()
  }
})

onMounted(() => {
  initCanvas()
  
  if (props.autoStart) {
    start()
  }

  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  stop()
  window.removeEventListener('resize', handleResize)
})

defineExpose({ start, stop, reset, clear, isRunning })
</script>

<style scoped>
.matrix-rain {
  background-color: #000000;
}
</style>
