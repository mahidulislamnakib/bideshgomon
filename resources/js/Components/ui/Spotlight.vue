<template>
  <div
    ref="containerRef"
    class="relative overflow-hidden"
    :class="containerClass"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
  >
    <!-- Spotlight effect -->
    <div
      v-if="active"
      class="pointer-events-none absolute inset-0 z-10 transition-opacity duration-300"
      :class="{ 'opacity-0': !isHovering && !alwaysOn }"
      :style="spotlightStyle"
    />

    <!-- Content -->
    <div class="relative z-0">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  active: {
    type: Boolean,
    default: true
  },
  size: {
    type: Number,
    default: 200
  },
  color: {
    type: String,
    default: 'rgba(255, 255, 255, 0.15)'
  },
  borderColor: {
    type: String,
    default: 'rgba(255, 255, 255, 0.2)'
  },
  blur: {
    type: Number,
    default: 40
  },
  alwaysOn: {
    type: Boolean,
    default: false
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['move', 'enter', 'leave'])

const containerRef = ref(null)
const mouseX = ref(0)
const mouseY = ref(0)
const isHovering = ref(false)

const spotlightStyle = computed(() => {
  return {
    background: `
      radial-gradient(
        ${props.size}px circle at ${mouseX.value}px ${mouseY.value}px,
        ${props.color},
        transparent 80%
      )
    `,
    boxShadow: isHovering.value || props.alwaysOn
      ? `inset 0 0 0 1px ${props.borderColor}`
      : 'none',
    filter: `blur(${props.blur}px)`,
    mixBlendMode: 'soft-light'
  }
})

function handleMouseMove(event) {
  if (!containerRef.value || !props.active) return

  const rect = containerRef.value.getBoundingClientRect()
  mouseX.value = event.clientX - rect.left
  mouseY.value = event.clientY - rect.top

  if (!isHovering.value) {
    isHovering.value = true
    emit('enter')
  }

  emit('move', { x: mouseX.value, y: mouseY.value })
}

function handleMouseLeave() {
  isHovering.value = false
  emit('leave')
}

defineExpose({
  isHovering,
  mouseX,
  mouseY
})
</script>
