<template>
  <div
    ref="glowRef"
    class="glow-container"
    :class="[
      `glow-${variant}`,
      { 'is-active': isActive, 'is-animated': animated }
    ]"
    :style="containerStyle"
    @mouseenter="handleMouseEnter"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
  >
    <div class="glow-effect" :style="glowStyle" />
    <div class="glow-content">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'

const props = defineProps({
  // Glow variant
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'gradient', 'rainbow', 'neon', 'subtle'].includes(v)
  },
  // Glow color (for default/neon variants)
  color: {
    type: String,
    default: '#3b82f6'
  },
  // Secondary color (for gradient variant)
  secondaryColor: {
    type: String,
    default: '#8b5cf6'
  },
  // Glow size/spread
  size: {
    type: Number,
    default: 100
  },
  // Glow blur amount
  blur: {
    type: Number,
    default: 60
  },
  // Glow opacity
  opacity: {
    type: Number,
    default: 0.6
  },
  // Enable animation
  animated: {
    type: Boolean,
    default: false
  },
  // Animation duration (ms)
  animationDuration: {
    type: Number,
    default: 3000
  },
  // Follow mouse
  followMouse: {
    type: Boolean,
    default: true
  },
  // Always show (not just on hover)
  alwaysOn: {
    type: Boolean,
    default: false
  },
  // Border radius
  borderRadius: {
    type: String,
    default: '0.5rem'
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['enter', 'leave', 'move'])

const glowRef = ref(null)
const isActive = ref(props.alwaysOn)
const mouseX = ref(50)
const mouseY = ref(50)
let animationFrame = null

const containerStyle = computed(() => ({
  '--glow-color': props.color,
  '--glow-secondary': props.secondaryColor,
  '--glow-size': `${props.size}px`,
  '--glow-blur': `${props.blur}px`,
  '--glow-opacity': props.opacity,
  '--glow-duration': `${props.animationDuration}ms`,
  '--border-radius': props.borderRadius,
  position: 'relative',
  borderRadius: props.borderRadius,
  overflow: 'hidden'
}))

const glowStyle = computed(() => {
  const baseStyle = {
    opacity: isActive.value ? props.opacity : 0
  }
  
  if (props.followMouse && isActive.value) {
    baseStyle.left = `${mouseX.value}%`
    baseStyle.top = `${mouseY.value}%`
    baseStyle.transform = 'translate(-50%, -50%)'
  }
  
  return baseStyle
})

const handleMouseEnter = () => {
  if (props.disabled || props.alwaysOn) return
  isActive.value = true
  emit('enter')
}

const handleMouseMove = (e) => {
  if (props.disabled || !props.followMouse || !glowRef.value) return
  
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
  
  animationFrame = requestAnimationFrame(() => {
    const rect = glowRef.value.getBoundingClientRect()
    mouseX.value = ((e.clientX - rect.left) / rect.width) * 100
    mouseY.value = ((e.clientY - rect.top) / rect.height) * 100
    
    emit('move', {
      x: mouseX.value,
      y: mouseY.value
    })
  })
}

const handleMouseLeave = () => {
  if (props.disabled || props.alwaysOn) return
  isActive.value = false
  emit('leave')
}

const show = () => {
  isActive.value = true
}

const hide = () => {
  if (!props.alwaysOn) {
    isActive.value = false
  }
}

onUnmounted(() => {
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
})

defineExpose({
  show,
  hide,
  isActive
})
</script>

<style scoped>
.glow-container {
  position: relative;
  isolation: isolate;
}

.glow-effect {
  position: absolute;
  width: var(--glow-size);
  height: var(--glow-size);
  border-radius: 50%;
  filter: blur(var(--glow-blur));
  pointer-events: none;
  z-index: 0;
  transition: opacity 0.3s ease;
}

.glow-content {
  position: relative;
  z-index: 1;
}

/* Default glow */
.glow-default .glow-effect {
  background: var(--glow-color);
}

/* Gradient glow */
.glow-gradient .glow-effect {
  background: linear-gradient(135deg, var(--glow-color), var(--glow-secondary));
}

/* Rainbow glow */
.glow-rainbow .glow-effect {
  background: conic-gradient(
    from 0deg,
    #ff0000,
    #ff8000,
    #ffff00,
    #80ff00,
    #00ff00,
    #00ff80,
    #00ffff,
    #0080ff,
    #0000ff,
    #8000ff,
    #ff00ff,
    #ff0080,
    #ff0000
  );
}

.glow-rainbow.is-animated .glow-effect {
  animation: rainbow-spin var(--glow-duration) linear infinite;
}

/* Neon glow */
.glow-neon .glow-effect {
  background: var(--glow-color);
  box-shadow:
    0 0 20px var(--glow-color),
    0 0 40px var(--glow-color),
    0 0 60px var(--glow-color);
}

.glow-neon.is-animated .glow-effect {
  animation: neon-pulse var(--glow-duration) ease-in-out infinite;
}

/* Subtle glow */
.glow-subtle .glow-effect {
  background: var(--glow-color);
  opacity: 0.3;
}

/* Animated state */
.is-animated.glow-default .glow-effect,
.is-animated.glow-gradient .glow-effect {
  animation: glow-float var(--glow-duration) ease-in-out infinite;
}

@keyframes glow-float {
  0%, 100% {
    transform: translate(-50%, -50%) scale(1);
  }
  50% {
    transform: translate(-50%, -50%) scale(1.2);
  }
}

@keyframes rainbow-spin {
  from {
    transform: translate(-50%, -50%) rotate(0deg);
  }
  to {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}

@keyframes neon-pulse {
  0%, 100% {
    opacity: var(--glow-opacity);
    filter: blur(var(--glow-blur));
  }
  50% {
    opacity: calc(var(--glow-opacity) * 1.5);
    filter: blur(calc(var(--glow-blur) * 1.3));
  }
}
</style>
