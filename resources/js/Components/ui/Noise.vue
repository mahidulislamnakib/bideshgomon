<template>
  <div 
    :class="containerClasses"
    :style="containerStyle"
  >
    <!-- Noise overlay -->
    <canvas
      ref="canvasRef"
      :class="canvasClasses"
      :style="canvasStyle"
    />
    
    <!-- Content slot -->
    <div class="relative z-10">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  // Noise settings
  variant: {
    type: String,
    default: 'noise',
    validator: (v) => ['noise', 'grain', 'scanlines', 'static', 'snow'].includes(v)
  },
  
  // Appearance
  opacity: {
    type: Number,
    default: 0.05,
    validator: (v) => v >= 0 && v <= 1
  },
  intensity: {
    type: Number,
    default: 1,
    validator: (v) => v >= 0 && v <= 5
  },
  color: {
    type: String,
    default: ''
  },
  
  // Animation
  animated: {
    type: Boolean,
    default: true
  },
  fps: {
    type: Number,
    default: 24
  },
  
  // Scanline specific
  scanlineSpacing: {
    type: Number,
    default: 4
  },
  scanlineWidth: {
    type: Number,
    default: 1
  },
  
  // Blend mode
  blendMode: {
    type: String,
    default: 'overlay',
    validator: (v) => ['normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn', 'hard-light', 'soft-light', 'difference', 'exclusion'].includes(v)
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['ready'])

const canvasRef = ref(null)
let animationId = null
let ctx = null
let lastFrame = 0

// Container classes
const containerClasses = computed(() => [
  'relative overflow-hidden'
])

const containerStyle = computed(() => ({}))

// Canvas classes
const canvasClasses = computed(() => [
  'absolute inset-0 w-full h-full pointer-events-none',
  props.disabled && 'hidden'
])

const canvasStyle = computed(() => ({
  opacity: props.opacity,
  mixBlendMode: props.blendMode
}))

// Initialize canvas
function initCanvas() {
  if (!canvasRef.value) return
  
  ctx = canvasRef.value.getContext('2d')
  resizeCanvas()
  
  if (props.animated) {
    startAnimation()
  } else {
    renderFrame()
  }
  
  emit('ready')
}

// Resize canvas to match container
function resizeCanvas() {
  if (!canvasRef.value) return
  
  const parent = canvasRef.value.parentElement
  const dpr = window.devicePixelRatio || 1
  
  canvasRef.value.width = parent.offsetWidth * dpr
  canvasRef.value.height = parent.offsetHeight * dpr
  
  ctx?.scale(dpr, dpr)
}

// Render a single frame
function renderFrame() {
  if (!ctx || !canvasRef.value) return
  
  const width = canvasRef.value.width
  const height = canvasRef.value.height
  
  // Clear canvas
  ctx.clearRect(0, 0, width, height)
  
  switch (props.variant) {
    case 'noise':
    case 'grain':
      renderNoise(width, height)
      break
    case 'scanlines':
      renderScanlines(width, height)
      break
    case 'static':
      renderStatic(width, height)
      break
    case 'snow':
      renderSnow(width, height)
      break
  }
}

// Render noise/grain pattern
function renderNoise(width, height) {
  const imageData = ctx.createImageData(width, height)
  const data = imageData.data
  const intensity = props.intensity * 50
  
  // Parse color if provided
  let r = 128, g = 128, b = 128
  if (props.color) {
    const temp = document.createElement('div')
    temp.style.color = props.color
    document.body.appendChild(temp)
    const rgb = getComputedStyle(temp).color.match(/\d+/g)
    document.body.removeChild(temp)
    if (rgb) {
      r = parseInt(rgb[0])
      g = parseInt(rgb[1])
      b = parseInt(rgb[2])
    }
  }
  
  for (let i = 0; i < data.length; i += 4) {
    const noise = Math.random() * intensity
    
    if (props.color) {
      data[i] = r
      data[i + 1] = g
      data[i + 2] = b
    } else {
      data[i] = noise
      data[i + 1] = noise
      data[i + 2] = noise
    }
    data[i + 3] = props.variant === 'grain' 
      ? Math.random() * 255 * props.intensity
      : 255
  }
  
  ctx.putImageData(imageData, 0, 0)
}

// Render scanlines
function renderScanlines(width, height) {
  const spacing = props.scanlineSpacing
  const lineWidth = props.scanlineWidth
  
  // Parse color
  let color = 'rgba(0, 0, 0, 0.3)'
  if (props.color) {
    color = props.color
  }
  
  ctx.strokeStyle = color
  ctx.lineWidth = lineWidth
  
  for (let y = 0; y < height; y += spacing) {
    ctx.beginPath()
    ctx.moveTo(0, y)
    ctx.lineTo(width, y)
    ctx.stroke()
  }
}

// Render TV static
function renderStatic(width, height) {
  const imageData = ctx.createImageData(width, height)
  const data = imageData.data
  const blockSize = Math.max(1, Math.floor(4 / props.intensity))
  
  for (let y = 0; y < height; y += blockSize) {
    for (let x = 0; x < width; x += blockSize) {
      const value = Math.random() > 0.5 ? 255 : 0
      
      for (let by = 0; by < blockSize && y + by < height; by++) {
        for (let bx = 0; bx < blockSize && x + bx < width; bx++) {
          const i = ((y + by) * width + (x + bx)) * 4
          data[i] = value
          data[i + 1] = value
          data[i + 2] = value
          data[i + 3] = 255
        }
      }
    }
  }
  
  ctx.putImageData(imageData, 0, 0)
}

// Render snow effect
function renderSnow(width, height) {
  const particles = Math.floor((width * height) / 1000 * props.intensity)
  
  ctx.fillStyle = props.color || 'white'
  
  for (let i = 0; i < particles; i++) {
    const x = Math.random() * width
    const y = Math.random() * height
    const size = Math.random() * 2 + 1
    
    ctx.beginPath()
    ctx.arc(x, y, size, 0, Math.PI * 2)
    ctx.fill()
  }
}

// Animation loop
function animate(timestamp) {
  if (props.disabled) return
  
  const interval = 1000 / props.fps
  
  if (timestamp - lastFrame >= interval) {
    renderFrame()
    lastFrame = timestamp
  }
  
  animationId = requestAnimationFrame(animate)
}

function startAnimation() {
  if (animationId) cancelAnimationFrame(animationId)
  animationId = requestAnimationFrame(animate)
}

function stopAnimation() {
  if (animationId) {
    cancelAnimationFrame(animationId)
    animationId = null
  }
}

// Resize observer
let resizeObserver = null

function setupResizeObserver() {
  if (!canvasRef.value) return
  
  resizeObserver = new ResizeObserver(() => {
    resizeCanvas()
    if (!props.animated) renderFrame()
  })
  
  resizeObserver.observe(canvasRef.value.parentElement)
}

// Watch for changes
watch(() => props.animated, (animated) => {
  if (animated) {
    startAnimation()
  } else {
    stopAnimation()
    renderFrame()
  }
})

watch(() => props.disabled, (disabled) => {
  if (disabled) {
    stopAnimation()
  } else if (props.animated) {
    startAnimation()
  } else {
    renderFrame()
  }
})

watch(() => [props.variant, props.intensity, props.color, props.scanlineSpacing, props.scanlineWidth], () => {
  if (!props.animated) renderFrame()
})

// Lifecycle
onMounted(() => {
  initCanvas()
  setupResizeObserver()
})

onUnmounted(() => {
  stopAnimation()
  resizeObserver?.disconnect()
})

// Expose for parent control
defineExpose({
  renderFrame,
  startAnimation,
  stopAnimation,
  resize: resizeCanvas
})
</script>
