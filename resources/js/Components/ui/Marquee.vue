<template>
  <div
    ref="containerRef"
    class="overflow-hidden"
    :class="containerClasses"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <div
      ref="contentRef"
      class="flex whitespace-nowrap"
      :class="[
        direction === 'vertical' ? 'flex-col' : '',
        animationClasses
      ]"
      :style="animationStyle"
    >
      <!-- Original content -->
      <div
        ref="originalRef"
        class="flex shrink-0"
        :class="[
          direction === 'vertical' ? 'flex-col' : '',
          gapClasses
        ]"
      >
        <slot>
          <div
            v-for="(item, index) in items"
            :key="index"
            class="shrink-0"
          >
            <slot name="item" :item="item" :index="index">
              <div class="px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
                {{ item }}
              </div>
            </slot>
          </div>
        </slot>
      </div>

      <!-- Duplicate for seamless loop -->
      <div
        v-if="loop"
        class="flex shrink-0"
        :class="[
          direction === 'vertical' ? 'flex-col' : '',
          gapClasses
        ]"
        aria-hidden="true"
      >
        <slot>
          <div
            v-for="(item, index) in items"
            :key="`dup-${index}`"
            class="shrink-0"
          >
            <slot name="item" :item="item" :index="index">
              <div class="px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
                {{ item }}
              </div>
            </slot>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  direction: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right', 'up', 'down', 'vertical'].includes(value)
  },
  speed: {
    type: Number,
    default: 50 // pixels per second
  },
  pauseOnHover: {
    type: Boolean,
    default: true
  },
  loop: {
    type: Boolean,
    default: true
  },
  gap: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  gradient: {
    type: Boolean,
    default: true
  },
  gradientColor: {
    type: String,
    default: 'white'
  },
  gradientWidth: {
    type: String,
    default: '100px'
  }
})

const emit = defineEmits(['iteration', 'pause', 'resume'])

// Refs
const containerRef = ref(null)
const contentRef = ref(null)
const originalRef = ref(null)
const isPaused = ref(false)
const contentWidth = ref(0)
const contentHeight = ref(0)

// Compute actual direction
const actualDirection = computed(() => {
  if (props.direction === 'vertical') return 'up'
  return props.direction
})

// Is vertical scrolling
const isVertical = computed(() => {
  return ['up', 'down', 'vertical'].includes(props.direction)
})

// Gap classes
const gapClasses = computed(() => {
  const gaps = {
    none: 'gap-0',
    sm: 'gap-2',
    md: 'gap-4',
    lg: 'gap-6',
    xl: 'gap-8'
  }
  return gaps[props.gap]
})

// Container classes
const containerClasses = computed(() => {
  const classes = ['relative']
  
  if (props.gradient) {
    classes.push('marquee-gradient')
  }
  
  return classes
})

// Animation classes
const animationClasses = computed(() => {
  if (isPaused.value) {
    return 'animation-paused'
  }
  return ''
})

// Calculate animation duration
const animationDuration = computed(() => {
  const distance = isVertical.value ? contentHeight.value : contentWidth.value
  if (distance === 0 || props.speed === 0) return 10
  return distance / props.speed
})

// Animation style
const animationStyle = computed(() => {
  const duration = `${animationDuration.value}s`
  const isReverse = actualDirection.value === 'right' || actualDirection.value === 'down'
  
  if (isVertical.value) {
    return {
      animation: `marquee-vertical ${duration} linear infinite`,
      animationDirection: isReverse ? 'reverse' : 'normal',
      animationPlayState: isPaused.value ? 'paused' : 'running'
    }
  }
  
  return {
    animation: `marquee-horizontal ${duration} linear infinite`,
    animationDirection: isReverse ? 'reverse' : 'normal',
    animationPlayState: isPaused.value ? 'paused' : 'running'
  }
})

// Measure content
function measureContent() {
  if (originalRef.value) {
    contentWidth.value = originalRef.value.offsetWidth
    contentHeight.value = originalRef.value.offsetHeight
  }
}

// Handle mouse enter
function handleMouseEnter() {
  if (props.pauseOnHover) {
    isPaused.value = true
    emit('pause')
  }
}

// Handle mouse leave
function handleMouseLeave() {
  if (props.pauseOnHover) {
    isPaused.value = false
    emit('resume')
  }
}

// Pause/Resume methods
function pause() {
  isPaused.value = true
  emit('pause')
}

function resume() {
  isPaused.value = false
  emit('resume')
}

// Resize observer
let resizeObserver = null

onMounted(() => {
  measureContent()
  
  resizeObserver = new ResizeObserver(measureContent)
  if (originalRef.value) {
    resizeObserver.observe(originalRef.value)
  }
  
  // Add CSS keyframes
  if (!document.getElementById('marquee-styles')) {
    const style = document.createElement('style')
    style.id = 'marquee-styles'
    style.textContent = `
      @keyframes marquee-horizontal {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
      }
      @keyframes marquee-vertical {
        from { transform: translateY(0); }
        to { transform: translateY(-50%); }
      }
      .animation-paused {
        animation-play-state: paused !important;
      }
      .marquee-gradient::before,
      .marquee-gradient::after {
        content: '';
        position: absolute;
        z-index: 10;
        pointer-events: none;
      }
    `
    document.head.appendChild(style)
  }
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})

// Watch items for remeasurement
watch(() => props.items, measureContent, { deep: true })

// Expose methods
defineExpose({
  pause,
  resume,
  isPaused: () => isPaused.value
})
</script>

<style scoped>
.marquee-gradient::before {
  top: 0;
  bottom: 0;
  left: 0;
  width: v-bind(gradientWidth);
  background: linear-gradient(to right, v-bind(gradientColor), transparent);
}

.marquee-gradient::after {
  top: 0;
  bottom: 0;
  right: 0;
  width: v-bind(gradientWidth);
  background: linear-gradient(to left, v-bind(gradientColor), transparent);
}
</style>
