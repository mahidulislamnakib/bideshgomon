<template>
  <div
    ref="containerRef"
    class="scratch-card relative inline-block select-none"
    :style="{ width: width + 'px', height: height + 'px' }"
  >
    <!-- Revealed Content -->
    <div class="absolute inset-0 overflow-hidden rounded-lg">
      <slot>
        <div class="flex items-center justify-center h-full bg-gradient-to-br from-yellow-400 to-orange-500 text-white font-bold text-2xl">
          🎉 You Won!
        </div>
      </slot>
    </div>

    <!-- Scratch Layer -->
    <canvas
      ref="canvasRef"
      :width="width"
      :height="height"
      class="absolute inset-0 rounded-lg cursor-crosshair"
      :class="{ 'pointer-events-none': isRevealed }"
      @mousedown="startScratching"
      @mousemove="scratch"
      @mouseup="stopScratching"
      @mouseleave="stopScratching"
      @touchstart.prevent="startScratching"
      @touchmove.prevent="scratch"
      @touchend="stopScratching"
    />

    <!-- Percentage Display -->
    <div
      v-if="showProgress"
      class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded"
    >
      {{ Math.round(scratchedPercent) }}%
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
  width: {
    type: Number,
    default: 300
  },
  height: {
    type: Number,
    default: 200
  },
  coverColor: {
    type: String,
    default: '#C0C0C0'
  },
  coverImage: {
    type: String,
    default: null
  },
  brushSize: {
    type: Number,
    default: 40
  },
  revealThreshold: {
    type: Number,
    default: 50
  },
  showProgress: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['scratch', 'reveal', 'complete'])

const containerRef = ref(null)
const canvasRef = ref(null)
const ctx = ref(null)
const isScratching = ref(false)
const isRevealed = ref(false)
const scratchedPercent = ref(0)
const lastPoint = ref(null)

onMounted(() => {
  initCanvas()
})

watch(() => [props.coverColor, props.coverImage], () => {
  nextTick(() => {
    initCanvas()
  })
})

function initCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return

  ctx.value = canvas.getContext('2d')
  
  if (props.coverImage) {
    const img = new Image()
    img.crossOrigin = 'anonymous'
    img.onload = () => {
      ctx.value.drawImage(img, 0, 0, props.width, props.height)
    }
    img.src = props.coverImage
  } else {
    // Draw cover color
    ctx.value.fillStyle = props.coverColor
    ctx.value.fillRect(0, 0, props.width, props.height)
    
    // Add scratch pattern
    ctx.value.fillStyle = '#999'
    ctx.value.font = '14px Arial'
    ctx.value.textAlign = 'center'
    ctx.value.textBaseline = 'middle'
    ctx.value.fillText('Scratch Here!', props.width / 2, props.height / 2)
  }

  isRevealed.value = false
  scratchedPercent.value = 0
}

function getEventPosition(e) {
  const canvas = canvasRef.value
  const rect = canvas.getBoundingClientRect()
  
  let clientX, clientY
  if (e.touches && e.touches.length > 0) {
    clientX = e.touches[0].clientX
    clientY = e.touches[0].clientY
  } else {
    clientX = e.clientX
    clientY = e.clientY
  }

  return {
    x: (clientX - rect.left) * (canvas.width / rect.width),
    y: (clientY - rect.top) * (canvas.height / rect.height)
  }
}

function startScratching(e) {
  if (props.disabled || isRevealed.value) return
  
  isScratching.value = true
  lastPoint.value = getEventPosition(e)
  scratchAt(lastPoint.value)
}

function scratch(e) {
  if (!isScratching.value || props.disabled || isRevealed.value) return

  const currentPoint = getEventPosition(e)
  
  // Draw line from last point to current point
  scratchLine(lastPoint.value, currentPoint)
  lastPoint.value = currentPoint

  // Calculate scratched percentage
  calculateScratchedPercent()
}

function stopScratching() {
  isScratching.value = false
  lastPoint.value = null
}

function scratchAt(point) {
  if (!ctx.value) return

  ctx.value.globalCompositeOperation = 'destination-out'
  ctx.value.beginPath()
  ctx.value.arc(point.x, point.y, props.brushSize / 2, 0, Math.PI * 2)
  ctx.value.fill()
}

function scratchLine(from, to) {
  if (!ctx.value || !from || !to) return

  ctx.value.globalCompositeOperation = 'destination-out'
  ctx.value.lineWidth = props.brushSize
  ctx.value.lineCap = 'round'
  ctx.value.lineJoin = 'round'
  
  ctx.value.beginPath()
  ctx.value.moveTo(from.x, from.y)
  ctx.value.lineTo(to.x, to.y)
  ctx.value.stroke()
}

function calculateScratchedPercent() {
  if (!ctx.value) return

  const imageData = ctx.value.getImageData(0, 0, props.width, props.height)
  const pixels = imageData.data
  let transparent = 0
  const total = pixels.length / 4

  for (let i = 3; i < pixels.length; i += 4) {
    if (pixels[i] === 0) {
      transparent++
    }
  }

  scratchedPercent.value = (transparent / total) * 100
  emit('scratch', scratchedPercent.value)

  if (scratchedPercent.value >= props.revealThreshold && !isRevealed.value) {
    revealAll()
  }
}

function revealAll() {
  if (!ctx.value || isRevealed.value) return

  isRevealed.value = true
  
  // Animate reveal
  let opacity = 1
  const fadeInterval = setInterval(() => {
    opacity -= 0.1
    if (opacity <= 0) {
      clearInterval(fadeInterval)
      ctx.value.clearRect(0, 0, props.width, props.height)
      emit('complete')
    } else {
      ctx.value.globalCompositeOperation = 'source-over'
      ctx.value.clearRect(0, 0, props.width, props.height)
      ctx.value.globalAlpha = opacity
      ctx.value.fillStyle = props.coverColor
      ctx.value.fillRect(0, 0, props.width, props.height)
    }
  }, 30)

  emit('reveal')
}

function reset() {
  initCanvas()
}

defineExpose({ reset, revealAll, scratchedPercent })
</script>

<style scoped>
.scratch-card {
  touch-action: none;
}
</style>
