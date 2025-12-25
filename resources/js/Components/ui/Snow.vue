<template>
  <Teleport :to="container">
    <canvas
      ref="canvasRef"
      class="snow-canvas pointer-events-none"
      :class="positionClass"
      :style="canvasStyle"
    />
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  container: {
    type: String,
    default: 'body'
  },
  fullscreen: {
    type: Boolean,
    default: true
  },
  snowflakeCount: {
    type: Number,
    default: 100
  },
  color: {
    type: String,
    default: '#ffffff'
  },
  minSize: {
    type: Number,
    default: 2
  },
  maxSize: {
    type: Number,
    default: 5
  },
  speed: {
    type: Number,
    default: 1
  },
  wind: {
    type: Number,
    default: 0
  },
  opacity: {
    type: Number,
    default: 0.8
  },
  zIndex: {
    type: Number,
    default: 9999
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
let snowflakes = []
let animationId = null

const positionClass = computed(() => ({
  'fixed inset-0': props.fullscreen
}))

const canvasStyle = computed(() => ({
  zIndex: props.zIndex,
  background: 'transparent'
}))

class Snowflake {
  constructor(canvasWidth, canvasHeight) {
    this.reset(canvasWidth, canvasHeight, true)
  }

  reset(canvasWidth, canvasHeight, initial = false) {
    this.x = Math.random() * canvasWidth
    this.y = initial ? Math.random() * canvasHeight : -10
    this.size = props.minSize + Math.random() * (props.maxSize - props.minSize)
    this.speedY = (0.5 + Math.random()) * props.speed
    this.speedX = (Math.random() - 0.5) * 0.5
    this.opacity = 0.3 + Math.random() * (props.opacity - 0.3)
    this.wobble = Math.random() * Math.PI * 2
    this.wobbleSpeed = 0.02 + Math.random() * 0.03
  }

  update(canvasWidth, canvasHeight) {
    this.wobble += this.wobbleSpeed
    this.x += this.speedX + Math.sin(this.wobble) * 0.5 + props.wind
    this.y += this.speedY

    // Reset when off screen
    if (this.y > canvasHeight + 10 || this.x < -10 || this.x > canvasWidth + 10) {
      this.reset(canvasWidth, canvasHeight)
    }
  }

  draw(ctx) {
    ctx.beginPath()
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2)
    ctx.fillStyle = props.color
    ctx.globalAlpha = this.opacity
    ctx.fill()
    ctx.globalAlpha = 1
  }
}

function initCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return

  if (props.fullscreen) {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  } else {
    const parent = canvas.parentElement
    if (parent) {
      canvas.width = parent.offsetWidth
      canvas.height = parent.offsetHeight
    }
  }

  ctx = canvas.getContext('2d')
  
  // Initialize snowflakes
  snowflakes = []
  for (let i = 0; i < props.snowflakeCount; i++) {
    snowflakes.push(new Snowflake(canvas.width, canvas.height))
  }
}

function animate() {
  if (!ctx || !canvasRef.value) return

  const canvas = canvasRef.value
  ctx.clearRect(0, 0, canvas.width, canvas.height)

  snowflakes.forEach(flake => {
    flake.update(canvas.width, canvas.height)
    flake.draw(ctx)
  })

  if (isRunning.value) {
    animationId = requestAnimationFrame(animate)
  }
}

function start() {
  if (isRunning.value) return
  
  isRunning.value = true
  emit('start')
  animate()
}

function stop() {
  isRunning.value = false
  emit('stop')
  
  if (animationId) {
    cancelAnimationFrame(animationId)
    animationId = null
  }
}

function clear() {
  if (!ctx || !canvasRef.value) return
  ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
  snowflakes = []
}

function reset() {
  const wasRunning = isRunning.value
  stop()
  initCanvas()
  if (wasRunning || props.autoStart) {
    start()
  }
}

function setWind(value) {
  // Allow dynamic wind changes
  snowflakes.forEach(flake => {
    flake.speedX = (Math.random() - 0.5) * 0.5
  })
}

function handleResize() {
  const wasRunning = isRunning.value
  stop()
  initCanvas()
  if (wasRunning) start()
}

watch(() => props.snowflakeCount, () => {
  reset()
})

watch(() => props.speed, () => {
  snowflakes.forEach(flake => {
    flake.speedY = (0.5 + Math.random()) * props.speed
  })
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

defineExpose({ start, stop, clear, reset, setWind, isRunning })
</script>

<style scoped>
.snow-canvas {
  display: block;
}
</style>
