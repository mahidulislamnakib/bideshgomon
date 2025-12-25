<template>
  <Teleport to="body">
    <div
      v-if="isActive"
      ref="canvasContainer"
      class="fixed inset-0 pointer-events-none z-[9999]"
      :style="{ overflow: 'hidden' }"
    >
      <canvas ref="canvasRef" class="w-full h-full" />
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  active: {
    type: Boolean,
    default: false
  },
  particleCount: {
    type: Number,
    default: 150
  },
  spread: {
    type: Number,
    default: 70
  },
  startVelocity: {
    type: Number,
    default: 45
  },
  decay: {
    type: Number,
    default: 0.9
  },
  gravity: {
    type: Number,
    default: 1
  },
  drift: {
    type: Number,
    default: 0
  },
  ticks: {
    type: Number,
    default: 200
  },
  colors: {
    type: Array,
    default: () => ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffa500', '#ff69b4']
  },
  shapes: {
    type: Array,
    default: () => ['square', 'circle'],
    validator: (value) => value.every(s => ['square', 'circle', 'star'].includes(s))
  },
  origin: {
    type: Object,
    default: () => ({ x: 0.5, y: 0.5 })
  },
  zIndex: {
    type: Number,
    default: 9999
  },
  autoPlay: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['complete'])

// Refs
const canvasRef = ref(null)
const canvasContainer = ref(null)
const isActive = ref(false)
let animationId = null
let particles = []
let ctx = null

// Particle class
class Particle {
  constructor(options) {
    this.x = options.x
    this.y = options.y
    this.wobble = Math.random() * 10
    this.wobbleSpeed = 0.1 + Math.random() * 0.1
    this.velocity = {
      x: (Math.random() - 0.5) * options.spread,
      y: -options.startVelocity * (0.7 + Math.random() * 0.3)
    }
    this.angle = Math.random() * Math.PI * 2
    this.angularVelocity = (Math.random() - 0.5) * 0.2
    this.color = options.colors[Math.floor(Math.random() * options.colors.length)]
    this.shape = options.shapes[Math.floor(Math.random() * options.shapes.length)]
    this.size = 5 + Math.random() * 5
    this.decay = options.decay
    this.gravity = options.gravity
    this.drift = options.drift
    this.ticks = options.ticks
    this.life = 1
  }

  update() {
    this.wobble += this.wobbleSpeed
    this.velocity.x += this.drift
    this.velocity.y += this.gravity
    this.velocity.x *= this.decay
    this.velocity.y *= this.decay
    
    this.x += this.velocity.x + Math.sin(this.wobble) * 2
    this.y += this.velocity.y
    this.angle += this.angularVelocity
    
    this.life -= 1 / this.ticks
    
    return this.life > 0
  }

  draw(ctx) {
    ctx.save()
    ctx.translate(this.x, this.y)
    ctx.rotate(this.angle)
    ctx.globalAlpha = this.life
    ctx.fillStyle = this.color
    
    const size = this.size * this.life
    
    if (this.shape === 'circle') {
      ctx.beginPath()
      ctx.arc(0, 0, size / 2, 0, Math.PI * 2)
      ctx.fill()
    } else if (this.shape === 'star') {
      this.drawStar(ctx, size)
    } else {
      ctx.fillRect(-size / 2, -size / 2, size, size)
    }
    
    ctx.restore()
  }

  drawStar(ctx, size) {
    const spikes = 5
    const outerRadius = size / 2
    const innerRadius = size / 4
    
    ctx.beginPath()
    for (let i = 0; i < spikes * 2; i++) {
      const radius = i % 2 === 0 ? outerRadius : innerRadius
      const angle = (i * Math.PI) / spikes - Math.PI / 2
      const x = Math.cos(angle) * radius
      const y = Math.sin(angle) * radius
      
      if (i === 0) {
        ctx.moveTo(x, y)
      } else {
        ctx.lineTo(x, y)
      }
    }
    ctx.closePath()
    ctx.fill()
  }
}

// Start confetti
function start() {
  if (!canvasRef.value) return
  
  isActive.value = true
  
  const canvas = canvasRef.value
  canvas.width = window.innerWidth
  canvas.height = window.innerHeight
  ctx = canvas.getContext('2d')
  
  // Create particles
  const originX = props.origin.x * canvas.width
  const originY = props.origin.y * canvas.height
  
  for (let i = 0; i < props.particleCount; i++) {
    particles.push(new Particle({
      x: originX,
      y: originY,
      spread: props.spread,
      startVelocity: props.startVelocity,
      decay: props.decay,
      gravity: props.gravity,
      drift: props.drift,
      ticks: props.ticks,
      colors: props.colors,
      shapes: props.shapes
    }))
  }
  
  animate()
}

// Animation loop
function animate() {
  if (!ctx || !canvasRef.value) return
  
  ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
  
  particles = particles.filter(particle => {
    particle.draw(ctx)
    return particle.update()
  })
  
  if (particles.length > 0) {
    animationId = requestAnimationFrame(animate)
  } else {
    stop()
  }
}

// Stop confetti
function stop() {
  if (animationId) {
    cancelAnimationFrame(animationId)
    animationId = null
  }
  particles = []
  isActive.value = false
  emit('complete')
}

// Fire confetti burst
function fire(options = {}) {
  const mergedOptions = { ...props, ...options }
  
  if (!canvasRef.value) {
    isActive.value = true
    setTimeout(() => fire(options), 50)
    return
  }
  
  const canvas = canvasRef.value
  canvas.width = window.innerWidth
  canvas.height = window.innerHeight
  ctx = canvas.getContext('2d')
  
  const originX = (options.origin?.x ?? props.origin.x) * canvas.width
  const originY = (options.origin?.y ?? props.origin.y) * canvas.height
  
  for (let i = 0; i < (options.particleCount ?? props.particleCount); i++) {
    particles.push(new Particle({
      x: originX,
      y: originY,
      spread: mergedOptions.spread,
      startVelocity: mergedOptions.startVelocity,
      decay: mergedOptions.decay,
      gravity: mergedOptions.gravity,
      drift: mergedOptions.drift,
      ticks: mergedOptions.ticks,
      colors: mergedOptions.colors,
      shapes: mergedOptions.shapes
    }))
  }
  
  if (!animationId) {
    animate()
  }
}

// Cannon effect (fires from sides)
function cannon() {
  fire({ origin: { x: 0, y: 1 }, spread: 55 })
  fire({ origin: { x: 1, y: 1 }, spread: 55 })
}

// Fireworks effect
function fireworks() {
  const count = 3
  for (let i = 0; i < count; i++) {
    setTimeout(() => {
      fire({
        origin: { x: 0.2 + Math.random() * 0.6, y: 0.3 + Math.random() * 0.4 },
        particleCount: 50
      })
    }, i * 300)
  }
}

// Watch active prop
watch(() => props.active, (newVal) => {
  if (newVal && props.autoPlay) {
    start()
  } else if (!newVal) {
    stop()
  }
}, { immediate: true })

// Handle resize
function handleResize() {
  if (canvasRef.value) {
    canvasRef.value.width = window.innerWidth
    canvasRef.value.height = window.innerHeight
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  stop()
  window.removeEventListener('resize', handleResize)
})

// Expose methods
defineExpose({
  fire,
  cannon,
  fireworks,
  start,
  stop
})
</script>
