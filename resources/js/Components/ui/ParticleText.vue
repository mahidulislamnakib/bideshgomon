<template>
  <div
    ref="containerRef"
    class="particle-text relative inline-block"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <!-- Hidden text for sizing -->
    <span
      ref="textRef"
      class="invisible"
      :class="textClasses"
      :style="textStyle"
    >
      {{ text }}
    </span>

    <!-- Canvas for particles -->
    <canvas
      ref="canvasRef"
      class="absolute inset-0"
      :width="canvasWidth"
      :height="canvasHeight"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  fontSize: {
    type: Number,
    default: 48
  },
  fontFamily: {
    type: String,
    default: 'Arial, sans-serif'
  },
  fontWeight: {
    type: String,
    default: 'bold'
  },
  color: {
    type: String,
    default: '#3B82F6'
  },
  particleSize: {
    type: Number,
    default: 2
  },
  particleGap: {
    type: Number,
    default: 3
  },
  explodeOnHover: {
    type: Boolean,
    default: true
  },
  explodeRadius: {
    type: Number,
    default: 100
  },
  animateOnLoad: {
    type: Boolean,
    default: true
  },
  speed: {
    type: Number,
    default: 0.1
  }
})

const emit = defineEmits(['ready', 'explode', 'reform'])

const containerRef = ref(null)
const textRef = ref(null)
const canvasRef = ref(null)
const canvasWidth = ref(0)
const canvasHeight = ref(0)
const isHovering = ref(false)
const mouseX = ref(0)
const mouseY = ref(0)

let ctx = null
let particles = []
let animationFrame = null

const textClasses = computed(() => [
  'whitespace-nowrap'
])

const textStyle = computed(() => ({
  fontSize: `${props.fontSize}px`,
  fontFamily: props.fontFamily,
  fontWeight: props.fontWeight
}))

class Particle {
  constructor(x, y, color) {
    this.x = x
    this.y = y
    this.originX = x
    this.originY = y
    this.color = color
    this.size = props.particleSize
    this.vx = 0
    this.vy = 0
    this.friction = 0.95
    this.springFactor = props.speed
  }

  draw(ctx) {
    ctx.fillStyle = this.color
    ctx.beginPath()
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2)
    ctx.fill()
  }

  update(mouseX, mouseY, isHovering, explodeRadius) {
    if (isHovering) {
      const dx = this.x - mouseX
      const dy = this.y - mouseY
      const distance = Math.sqrt(dx * dx + dy * dy)

      if (distance < explodeRadius) {
        const force = (explodeRadius - distance) / explodeRadius
        const angle = Math.atan2(dy, dx)
        this.vx += Math.cos(angle) * force * 2
        this.vy += Math.sin(angle) * force * 2
      }
    }

    // Spring back to origin
    const dx = this.originX - this.x
    const dy = this.originY - this.y
    this.vx += dx * this.springFactor
    this.vy += dy * this.springFactor

    // Apply friction
    this.vx *= this.friction
    this.vy *= this.friction

    // Update position
    this.x += this.vx
    this.y += this.vy
  }
}

function initParticles() {
  if (!textRef.value || !canvasRef.value) return

  const text = textRef.value
  const canvas = canvasRef.value
  
  // Get text dimensions
  const rect = text.getBoundingClientRect()
  canvasWidth.value = rect.width + 20
  canvasHeight.value = rect.height + 20

  nextTick(() => {
    ctx = canvas.getContext('2d')
    if (!ctx) return

    // Draw text to get pixel data
    ctx.font = `${props.fontWeight} ${props.fontSize}px ${props.fontFamily}`
    ctx.fillStyle = props.color
    ctx.textBaseline = 'top'
    ctx.fillText(props.text, 10, 10)

    // Get image data
    const imageData = ctx.getImageData(0, 0, canvasWidth.value, canvasHeight.value)
    const data = imageData.data

    // Clear canvas
    ctx.clearRect(0, 0, canvasWidth.value, canvasHeight.value)

    // Create particles from non-transparent pixels
    particles = []
    for (let y = 0; y < canvasHeight.value; y += props.particleGap) {
      for (let x = 0; x < canvasWidth.value; x += props.particleGap) {
        const index = (y * canvasWidth.value + x) * 4
        const alpha = data[index + 3]

        if (alpha > 128) {
          const r = data[index]
          const g = data[index + 1]
          const b = data[index + 2]
          const color = `rgb(${r}, ${g}, ${b})`
          
          const particle = new Particle(x, y, color)
          
          // Animate from random position on load
          if (props.animateOnLoad) {
            particle.x = Math.random() * canvasWidth.value
            particle.y = Math.random() * canvasHeight.value
          }
          
          particles.push(particle)
        }
      }
    }

    emit('ready', particles.length)
    animate()
  })
}

function animate() {
  if (!ctx) return

  ctx.clearRect(0, 0, canvasWidth.value, canvasHeight.value)

  for (const particle of particles) {
    particle.update(mouseX.value, mouseY.value, isHovering.value, props.explodeRadius)
    particle.draw(ctx)
  }

  animationFrame = requestAnimationFrame(animate)
}

function handleMouseEnter() {
  if (!props.explodeOnHover) return
  isHovering.value = true
  emit('explode')
}

function handleMouseLeave() {
  isHovering.value = false
  emit('reform')
}

function handleMouseMove(e) {
  if (!containerRef.value) return
  
  const rect = containerRef.value.getBoundingClientRect()
  mouseX.value = e.clientX - rect.left
  mouseY.value = e.clientY - rect.top
}

function explode() {
  for (const particle of particles) {
    const angle = Math.random() * Math.PI * 2
    const force = Math.random() * 20 + 10
    particle.vx = Math.cos(angle) * force
    particle.vy = Math.sin(angle) * force
  }
}

function reform() {
  // Particles will naturally spring back
}

watch(() => props.text, () => {
  nextTick(initParticles)
})

watch(() => [props.fontSize, props.color, props.particleGap], () => {
  nextTick(initParticles)
})

onMounted(() => {
  initParticles()
  
  if (containerRef.value) {
    containerRef.value.addEventListener('mousemove', handleMouseMove)
  }
})

onUnmounted(() => {
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
  
  if (containerRef.value) {
    containerRef.value.removeEventListener('mousemove', handleMouseMove)
  }
})

defineExpose({ explode, reform, particles })
</script>

<style scoped>
.particle-text canvas {
  pointer-events: none;
}
</style>
