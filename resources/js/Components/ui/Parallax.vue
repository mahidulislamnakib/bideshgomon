<template>
  <div
    ref="containerRef"
    class="relative overflow-hidden"
    :class="containerClass"
    :style="containerStyle"
  >
    <!-- Parallax layers -->
    <div
      v-for="(layer, index) in layers"
      :key="index"
      class="absolute inset-0"
      :style="getLayerStyle(layer, index)"
    >
      <slot :name="`layer-${index}`" :layer="layer" :offset="currentOffset" />
    </div>

    <!-- Default content (no parallax) -->
    <div class="relative z-10">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  layers: {
    type: Array,
    default: () => [{ speed: 0.5 }, { speed: 0.3 }, { speed: 0.1 }]
    // Each layer: { speed, image?, backgroundColor?, zIndex? }
  },
  direction: {
    type: String,
    default: 'vertical',
    validator: (v) => ['vertical', 'horizontal', 'both'].includes(v)
  },
  speed: {
    type: Number,
    default: 0.5
  },
  disabled: {
    type: Boolean,
    default: false
  },
  height: {
    type: String,
    default: '400px'
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['scroll', 'update'])

const containerRef = ref(null)
const currentOffset = ref({ x: 0, y: 0 })
const isInView = ref(false)

const containerStyle = computed(() => ({
  height: props.height
}))

function getLayerStyle(layer, index) {
  if (props.disabled) {
    return {
      zIndex: layer.zIndex ?? index,
      backgroundImage: layer.image ? `url(${layer.image})` : undefined,
      backgroundColor: layer.backgroundColor,
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    }
  }

  const speed = layer.speed ?? props.speed
  const offsetX = props.direction !== 'vertical' ? currentOffset.value.x * speed : 0
  const offsetY = props.direction !== 'horizontal' ? currentOffset.value.y * speed : 0

  return {
    zIndex: layer.zIndex ?? index,
    backgroundImage: layer.image ? `url(${layer.image})` : undefined,
    backgroundColor: layer.backgroundColor,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    transform: `translate3d(${offsetX}px, ${offsetY}px, 0)`,
    willChange: 'transform'
  }
}

function handleScroll() {
  if (!containerRef.value || props.disabled) return

  const rect = containerRef.value.getBoundingClientRect()
  const windowHeight = window.innerHeight
  const windowWidth = window.innerWidth

  // Check if in viewport
  isInView.value = rect.top < windowHeight && rect.bottom > 0

  if (!isInView.value) return

  // Calculate offset based on scroll position
  const scrollY = window.scrollY
  const elementTop = rect.top + scrollY
  const relativeScroll = scrollY - elementTop + windowHeight

  // Normalize to percentage of viewport
  const normalizedY = (relativeScroll / (windowHeight + rect.height)) * 100 - 50

  // For horizontal, use scroll position relative to element width
  const normalizedX = (window.scrollX / windowWidth) * 100 - 50

  currentOffset.value = {
    x: normalizedX,
    y: normalizedY
  }

  emit('scroll', currentOffset.value)
  emit('update', { offset: currentOffset.value, isInView: isInView.value })
}

let rafId = null

function onScroll() {
  if (rafId) return
  rafId = requestAnimationFrame(() => {
    handleScroll()
    rafId = null
  })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', onScroll, { passive: true })
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', onScroll)
  if (rafId) cancelAnimationFrame(rafId)
})

defineExpose({
  currentOffset,
  isInView,
  refresh: handleScroll
})
</script>
