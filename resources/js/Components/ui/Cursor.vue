<template>
  <Teleport to="body">
    <div
      v-show="isVisible"
      ref="cursorRef"
      class="custom-cursor pointer-events-none fixed z-[9999]"
      :style="cursorStyle"
    >
      <!-- Main Cursor -->
      <div
        class="cursor-main absolute"
        :class="[
          sizeClasses,
          { 'scale-150': isClicking, 'scale-75': isHovering }
        ]"
        :style="mainCursorStyle"
      />

      <!-- Follower/Trail -->
      <div
        v-if="showFollower"
        class="cursor-follower absolute"
        :class="followerSizeClasses"
        :style="followerStyle"
      />

      <!-- Custom Content -->
      <div
        v-if="$slots.default"
        class="cursor-content absolute transform -translate-x-1/2 -translate-y-1/2"
      >
        <slot :is-clicking="isClicking" :is-hovering="isHovering" />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  color: {
    type: String,
    default: '#3B82F6'
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v)
  },
  variant: {
    type: String,
    default: 'dot',
    validator: (v) => ['dot', 'ring', 'filled', 'mixed'].includes(v)
  },
  showFollower: {
    type: Boolean,
    default: true
  },
  followerDelay: {
    type: Number,
    default: 0.15
  },
  blend: {
    type: String,
    default: 'normal',
    validator: (v) => ['normal', 'difference', 'exclusion', 'multiply'].includes(v)
  },
  hideDefault: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['move', 'click', 'hover'])

const cursorRef = ref(null)
const isVisible = ref(false)
const isClicking = ref(false)
const isHovering = ref(false)

const mouseX = ref(0)
const mouseY = ref(0)
const followerX = ref(0)
const followerY = ref(0)

let animationFrame = null

const sizeMap = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24
}

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'w-2 h-2',
    md: 'w-3 h-3',
    lg: 'w-4 h-4',
    xl: 'w-6 h-6'
  }
  return sizes[props.size]
})

const followerSizeClasses = computed(() => {
  const sizes = {
    sm: 'w-6 h-6',
    md: 'w-8 h-8',
    lg: 'w-10 h-10',
    xl: 'w-14 h-14'
  }
  return sizes[props.size]
})

const cursorStyle = computed(() => ({
  mixBlendMode: props.blend
}))

const mainCursorStyle = computed(() => {
  const size = sizeMap[props.size]
  const base = {
    left: `${mouseX.value}px`,
    top: `${mouseY.value}px`,
    transform: 'translate(-50%, -50%)',
    transition: 'transform 0.15s ease-out, width 0.15s ease-out, height 0.15s ease-out'
  }

  switch (props.variant) {
    case 'dot':
      return {
        ...base,
        backgroundColor: props.color,
        borderRadius: '50%'
      }
    case 'ring':
      return {
        ...base,
        border: `2px solid ${props.color}`,
        borderRadius: '50%',
        backgroundColor: 'transparent'
      }
    case 'filled':
      return {
        ...base,
        backgroundColor: props.color,
        borderRadius: '50%',
        opacity: 0.5
      }
    case 'mixed':
      return {
        ...base,
        backgroundColor: props.color,
        borderRadius: '50%',
        boxShadow: `0 0 0 2px transparent, 0 0 0 4px ${props.color}`
      }
    default:
      return base
  }
})

const followerStyle = computed(() => ({
  left: `${followerX.value}px`,
  top: `${followerY.value}px`,
  transform: 'translate(-50%, -50%)',
  border: `1px solid ${props.color}`,
  borderRadius: '50%',
  backgroundColor: 'transparent',
  opacity: 0.5,
  transition: 'width 0.3s ease-out, height 0.3s ease-out'
}))

function handleMouseMove(e) {
  if (props.disabled) return

  mouseX.value = e.clientX
  mouseY.value = e.clientY
  isVisible.value = true

  emit('move', { x: e.clientX, y: e.clientY })
}

function handleMouseDown() {
  if (props.disabled) return
  isClicking.value = true
  emit('click', true)
}

function handleMouseUp() {
  if (props.disabled) return
  isClicking.value = false
  emit('click', false)
}

function handleMouseOver(e) {
  if (props.disabled) return

  const target = e.target
  if (
    target.tagName === 'A' ||
    target.tagName === 'BUTTON' ||
    target.closest('a') ||
    target.closest('button') ||
    target.classList.contains('cursor-hover') ||
    window.getComputedStyle(target).cursor === 'pointer'
  ) {
    isHovering.value = true
    emit('hover', true)
  }
}

function handleMouseOut() {
  if (props.disabled) return
  isHovering.value = false
  emit('hover', false)
}

function handleMouseLeave() {
  isVisible.value = false
}

function animate() {
  // Smooth follower movement
  followerX.value += (mouseX.value - followerX.value) * props.followerDelay
  followerY.value += (mouseY.value - followerY.value) * props.followerDelay

  animationFrame = requestAnimationFrame(animate)
}

function updateBodyCursor() {
  if (props.hideDefault && !props.disabled) {
    document.body.style.cursor = 'none'
    document.querySelectorAll('a, button, [role="button"]').forEach((el) => {
      el.style.cursor = 'none'
    })
  } else {
    document.body.style.cursor = ''
    document.querySelectorAll('a, button, [role="button"]').forEach((el) => {
      el.style.cursor = ''
    })
  }
}

watch(() => props.disabled, updateBodyCursor)
watch(() => props.hideDefault, updateBodyCursor)

onMounted(() => {
  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mousedown', handleMouseDown)
  document.addEventListener('mouseup', handleMouseUp)
  document.addEventListener('mouseover', handleMouseOver)
  document.addEventListener('mouseout', handleMouseOut)
  document.addEventListener('mouseleave', handleMouseLeave)

  updateBodyCursor()
  animate()
})

onUnmounted(() => {
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mousedown', handleMouseDown)
  document.removeEventListener('mouseup', handleMouseUp)
  document.removeEventListener('mouseover', handleMouseOver)
  document.removeEventListener('mouseout', handleMouseOut)
  document.removeEventListener('mouseleave', handleMouseLeave)

  document.body.style.cursor = ''
  
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
})

defineExpose({ isClicking, isHovering, isVisible })
</script>

<style scoped>
.custom-cursor {
  pointer-events: none;
}

.cursor-main,
.cursor-follower {
  will-change: transform, left, top;
}

.cursor-main {
  transition: transform 0.15s cubic-bezier(0.33, 1, 0.68, 1);
}
</style>
