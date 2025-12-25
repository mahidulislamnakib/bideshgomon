<template>
  <div
    ref="containerRef"
    :class="containerClasses"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
    @mousedown="handleMouseDown"
  >
    <!-- Pull indicator -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 -translate-y-full"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-full"
    >
      <div
        v-if="pullDistance > 0 || isRefreshing"
        :class="indicatorClasses"
        :style="indicatorStyle"
      >
        <!-- Pull text -->
        <div v-if="!isRefreshing" :class="pullTextClasses">
          <ArrowDownIcon
            :class="arrowClasses"
            :style="arrowStyle"
          />
          <span>{{ pullText }}</span>
        </div>
        
        <!-- Loading spinner -->
        <div v-else :class="loadingClasses">
          <svg
            class="animate-spin w-5 h-5"
            viewBox="0 0 24 24"
            fill="none"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          <span>{{ loadingText }}</span>
        </div>
      </div>
    </Transition>
    
    <!-- Content -->
    <div
      ref="contentRef"
      :class="contentClasses"
      :style="contentStyle"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  // Threshold to trigger refresh (px)
  threshold: {
    type: Number,
    default: 80
  },
  
  // Maximum pull distance
  maxPull: {
    type: Number,
    default: 120
  },
  
  // Resistance factor (0-1, lower = more resistance)
  resistance: {
    type: Number,
    default: 0.5
  },
  
  // Text options
  pullText: {
    type: String,
    default: 'Pull to refresh'
  },
  releaseText: {
    type: String,
    default: 'Release to refresh'
  },
  loadingText: {
    type: String,
    default: 'Refreshing...'
  },
  successText: {
    type: String,
    default: 'Refresh complete'
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false
  },
  
  // Allow mouse events (for desktop testing)
  allowMouse: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['refresh'])

const containerRef = ref(null)
const contentRef = ref(null)
const pullDistance = ref(0)
const isRefreshing = ref(false)
const isPulling = ref(false)
const startY = ref(0)
const currentY = ref(0)

// Pull text based on state
const pullText = computed(() => {
  if (pullDistance.value >= props.threshold) {
    return props.releaseText
  }
  return props.pullText
})

// Container classes
const containerClasses = computed(() => [
  'relative overflow-hidden',
  'touch-pan-y'
])

// Indicator classes
const indicatorClasses = computed(() => [
  'absolute top-0 left-0 right-0',
  'flex items-center justify-center',
  'text-sm text-gray-600 dark:text-gray-400',
  'transition-all duration-200'
])

// Indicator style
const indicatorStyle = computed(() => {
  const height = Math.min(pullDistance.value, props.maxPull)
  return {
    height: isRefreshing.value ? '60px' : `${height}px`,
    paddingTop: isRefreshing.value ? '0' : `${Math.max(0, height - 40)}px`
  }
})

// Pull text classes
const pullTextClasses = computed(() => [
  'flex items-center gap-2'
])

// Arrow classes
const arrowClasses = computed(() => [
  'w-5 h-5 transition-transform duration-200'
])

// Arrow style (rotate when threshold reached)
const arrowStyle = computed(() => ({
  transform: pullDistance.value >= props.threshold ? 'rotate(180deg)' : 'rotate(0deg)'
}))

// Loading classes
const loadingClasses = computed(() => [
  'flex items-center gap-2',
  'text-primary-600 dark:text-primary-400'
])

// Content classes
const contentClasses = computed(() => [
  'transition-transform duration-200',
  isPulling.value && 'transition-none'
])

// Content style
const contentStyle = computed(() => ({
  transform: `translateY(${isRefreshing.value ? 60 : pullDistance.value}px)`
}))

// Check if at top of scroll
function isAtTop() {
  if (!contentRef.value) return true
  const scrollTop = contentRef.value.scrollTop || 0
  return scrollTop <= 0
}

// Touch handlers
function handleTouchStart(e) {
  if (props.disabled || isRefreshing.value) return
  if (!isAtTop()) return
  
  isPulling.value = true
  startY.value = e.touches[0].clientY
  currentY.value = startY.value
}

function handleTouchMove(e) {
  if (!isPulling.value || props.disabled || isRefreshing.value) return
  
  currentY.value = e.touches[0].clientY
  const diff = currentY.value - startY.value
  
  if (diff > 0) {
    // Apply resistance
    const resistedPull = diff * props.resistance
    pullDistance.value = Math.min(resistedPull, props.maxPull)
    
    // Prevent scroll while pulling
    if (pullDistance.value > 0) {
      e.preventDefault()
    }
  }
}

function handleTouchEnd() {
  if (!isPulling.value) return
  
  isPulling.value = false
  
  if (pullDistance.value >= props.threshold && !isRefreshing.value) {
    triggerRefresh()
  } else {
    pullDistance.value = 0
  }
}

// Mouse handlers (for desktop testing)
function handleMouseDown(e) {
  if (!props.allowMouse || props.disabled || isRefreshing.value) return
  if (!isAtTop()) return
  
  isPulling.value = true
  startY.value = e.clientY
  currentY.value = startY.value
  
  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
}

function handleMouseMove(e) {
  if (!isPulling.value || props.disabled || isRefreshing.value) return
  
  currentY.value = e.clientY
  const diff = currentY.value - startY.value
  
  if (diff > 0) {
    const resistedPull = diff * props.resistance
    pullDistance.value = Math.min(resistedPull, props.maxPull)
  }
}

function handleMouseUp() {
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
  
  if (!isPulling.value) return
  
  isPulling.value = false
  
  if (pullDistance.value >= props.threshold && !isRefreshing.value) {
    triggerRefresh()
  } else {
    pullDistance.value = 0
  }
}

// Trigger refresh
function triggerRefresh() {
  isRefreshing.value = true
  pullDistance.value = 0
  
  emit('refresh', {
    done: finishRefresh
  })
}

// Finish refresh (call from parent)
function finishRefresh() {
  isRefreshing.value = false
}

// Manual refresh trigger
function refresh() {
  if (!isRefreshing.value) {
    triggerRefresh()
  }
}

// Cleanup
onUnmounted(() => {
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
})

// Expose
defineExpose({
  refresh,
  finishRefresh,
  isRefreshing
})
</script>
