<template>
  <Teleport to="body">
    <canvas
      ref="canvasRef"
      class="fireworks-canvas fixed inset-0 pointer-events-none z-[9999]"
      :style="{ opacity: isActive ? 1 : 0 }"
    />
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  particlesPerExplosion: {
    type: Number,
    default: 50
  },
  colors: {
    type: Array,
    default: () => ['#ff0000', '#ffa500', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#ff00ff', '#ffffff']
  },
  gravity: {
    type: Number,
    default: 0.05
  },
  friction: {
    type: Number,
    default: 0.99
  },
  sparkTrail: {
    type: Boolean,
    default: true
  },
  autoLaunch: {
    type: Boolean,
    default: false
  },
  launchInterval: {
    type: Number,
    default: 1000
  }
})

const emit = defineEmits(['launch', 'explode', 'complete'])

const canvasRef = ref(null)
const isActive = ref(false)
let ctx = null
let rockets = []
let particles = []
let animationId = null
let launchIntervalId = null

class Rocket {
  constructor(x, targetY, color) {
    this.x = x
    this.y = window.innerHeight
    this.targetY = targetY
    this.color = color
    this.velocity = 8 + Math.random() * 4
    this.trail = []
    this.exploded = false
  }

  update() {
    if (this.exploded) return false

    // Store trail
    if (props.sparkTrail) {
      this.trail.push({ x: this.x, y: this.y, alpha: 1 })
      if (this.trail.length > 10) this.trail.shift()
    }

    this.y -= this.velocity
    this.velocity *= 0.98

    // Explode when reaching target or velocity is too low
    if (this.y <= this.targetY || this.velocity < 2) {
      this.explode()
      return false
    }

    return true
  }

  draw(ctx) {
    // Draw trail
    this.trail.forEach((point, i) => {
      ctx.beginPath()
      ctx.arc(point.x, point.y, 2, 0, Math.PI * 2)
      ctx.fillStyle = `rgba(255, 200, 100, ${i / this.trail.length * 0.5})`
      ctx.fill()
    })

    // Draw rocket
    ctx.beginPath()
    ctx.arc(this.x, this.y, 3, 0, Math.PI * 2)
    ctx.fillStyle = '#ffffff'
    ctx.fill()
  }

  explode() {
    this.exploded = true
    emit('explode', { x: this.x, y: this.y })

    const color = this.color
    for (let i = 0; i < props.particlesPerExplosion; i++) {
      const angle = (Math.PI * 2 / props.particlesPerExplosion) * i + Math.random() * 0.5
      const velocity = 2 + Math.random() * 4
      particles.push(new Particle(this.x, this.y, color, angle, velocity))
    }
  }
}

class Particle {
  constructor(x, y, color, angle, velocity) {
    this.x = x
    this.y = y
    this.color = color
    this.angle = angle
    this.velocity = velocity
    this.alpha = 1
    this.decay = 0.015 + Math.random() * 0.01
    this.trail = []
  }

  update() {
    // Store trail
    if (props.sparkTrail) {
      this.trail.push({ x: this.x, y: this.y, alpha: this.alpha })
      if (this.trail.length > 5) this.trail.shift()
    }

    this.x += Math.cos(this.angle) * this.velocity
    this.y += Math.sin(this.angle) * this.velocity + props.gravity
    this.velocity *= props.friction
    this.alpha -= this.decay

    return this.alpha > 0
  }

  draw(ctx) {
    // Draw trail
    this.trail.forEach((point, i) => {
      ctx.beginPath()
      ctx.arc(point.x, point.y, 1.5, 0, Math.PI * 2)
      ctx.fillStyle = this.color.replace(')', `, ${point.alpha * (i / this.trail.length) * 0.3})`).replace('rgb', 'rgba')
      ctx.fill()
    })

    // Draw particle
    ctx.beginPath()
    ctx.arc(this.x, this.y, 2, 0, Math.PI * 2)
    ctx.fillStyle = this.color.replace(')', `, ${this.alpha})`).replace('rgb', 'rgba')
    // Handle hex colors
    if (this.color.startsWith('#')) {
      ctx.globalAlpha = this.alpha
      ctx.fillStyle = this.color
    }
    ctx.fill()
    ctx.globalAlpha = 1
  }
}

function initCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return

  canvas.width = window.innerWidth
  canvas.height = window.innerHeight
  ctx = canvas.getContext('2d')
}

function animate() {
  if (!ctx || !canvasRef.value) return

  // Semi-transparent clear for trail effect
  ctx.fillStyle = 'rgba(0, 0, 0, 0.1)'
  ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height)

  // Update and draw rockets
  rockets = rockets.filter(rocket => {
    rocket.draw(ctx)
    return rocket.update()
  })

  // Update and draw particles
  particles = particles.filter(particle => {
    particle.draw(ctx)
    return particle.update()
  })

  if (rockets.length > 0 || particles.length > 0 || props.autoLaunch) {
    animationId = requestAnimationFrame(animate)
  } else {
    isActive.value = false
    emit('complete')
  }
}

function launch(options = {}) {
  initCanvas()

  const x = options.x ?? Math.random() * canvasRef.value.width * 0.6 + canvasRef.value.width * 0.2
  const targetY = options.y ?? Math.random() * canvasRef.value.height * 0.3 + canvasRef.value.height * 0.1
  const color = options.color ?? props.colors[Math.floor(Math.random() * props.colors.length)]

  rockets.push(new Rocket(x, targetY, color))
  emit('launch', { x, targetY, color })

  if (!isActive.value) {
    isActive.value = true
    animate()
  }
}

function explodeAt(x, y, color) {
  initCanvas()
  
  const explosionColor = color ?? props.colors[Math.floor(Math.random() * props.colors.length)]
  
  for (let i = 0; i < props.particlesPerExplosion; i++) {
    const angle = (Math.PI * 2 / props.particlesPerExplosion) * i + Math.random() * 0.5
    const velocity = 2 + Math.random() * 4
    particles.push(new Particle(x, y, explosionColor, angle, velocity))
  }

  if (!isActive.value) {
    isActive.value = true
    animate()
  }
}

function startAutoLaunch() {
  if (launchIntervalId) return
  
  launchIntervalId = setInterval(() => {
    launch()
  }, props.launchInterval)
  
  launch() // Initial launch
}

function stopAutoLaunch() {
  if (launchIntervalId) {
    clearInterval(launchIntervalId)
    launchIntervalId = null
  }
}

function stop() {
  stopAutoLaunch()
  rockets = []
  particles = []
  if (animationId) {
    cancelAnimationFrame(animationId)
    animationId = null
  }
  if (ctx && canvasRef.value) {
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
  }
  isActive.value = false
}

function handleResize() {
  initCanvas()
}

onMounted(() => {
  initCanvas()
  window.addEventListener('resize', handleResize)
  
  if (props.autoLaunch) {
    startAutoLaunch()
  }
})

onUnmounted(() => {
  stop()
  window.removeEventListener('resize', handleResize)
})

defineExpose({ launch, explodeAt, startAutoLaunch, stopAutoLaunch, stop, isActive })
</script>

<style scoped>
.fireworks-canvas {
  background: transparent;
  transition: opacity 0.3s ease-out;
}
</style>
