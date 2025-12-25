<template>
  <div
    ref="revealRef"
    class="reveal-container"
    :class="[
      `reveal-${effect}`,
      `reveal-${direction}`,
      { 'is-revealed': isRevealed, 'is-animating': isAnimating }
    ]"
    :style="revealStyle"
  >
    <slot />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  // Reveal effect type
  effect: {
    type: String,
    default: 'fade',
    validator: (v) => ['fade', 'slide', 'zoom', 'flip', 'blur', 'clip', 'mask'].includes(v)
  },
  // Direction for directional effects
  direction: {
    type: String,
    default: 'up',
    validator: (v) => ['up', 'down', 'left', 'right'].includes(v)
  },
  // Animation duration (ms)
  duration: {
    type: Number,
    default: 600
  },
  // Delay before animation starts (ms)
  delay: {
    type: Number,
    default: 0
  },
  // Distance for slide effect (px)
  distance: {
    type: Number,
    default: 50
  },
  // Easing function
  easing: {
    type: String,
    default: 'cubic-bezier(0.4, 0, 0.2, 1)'
  },
  // Trigger on scroll into view
  triggerOnScroll: {
    type: Boolean,
    default: true
  },
  // Intersection threshold (0-1)
  threshold: {
    type: Number,
    default: 0.2
  },
  // Only animate once
  once: {
    type: Boolean,
    default: true
  },
  // Manual control (disable auto-trigger)
  manual: {
    type: Boolean,
    default: false
  },
  // v-model binding
  modelValue: {
    type: Boolean,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'reveal', 'hide'])

const revealRef = ref(null)
const isRevealed = ref(false)
const isAnimating = ref(false)
const hasAnimated = ref(false)
let observer = null

const revealStyle = computed(() => ({
  '--reveal-duration': `${props.duration}ms`,
  '--reveal-delay': `${props.delay}ms`,
  '--reveal-easing': props.easing,
  '--reveal-distance': `${props.distance}px`
}))

const reveal = () => {
  if (isRevealed.value) return
  isAnimating.value = true
  isRevealed.value = true
  hasAnimated.value = true
  emit('update:modelValue', true)
  emit('reveal')
  
  setTimeout(() => {
    isAnimating.value = false
  }, props.duration + props.delay)
}

const hide = () => {
  if (!isRevealed.value) return
  isAnimating.value = true
  isRevealed.value = false
  emit('update:modelValue', false)
  emit('hide')
  
  setTimeout(() => {
    isAnimating.value = false
  }, props.duration)
}

const reset = () => {
  isRevealed.value = false
  hasAnimated.value = false
  isAnimating.value = false
}

const handleIntersection = (entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      if (!hasAnimated.value || !props.once) {
        reveal()
      }
    } else if (!props.once) {
      hide()
    }
  })
}

const setupObserver = () => {
  if (!props.triggerOnScroll || props.manual) return
  
  observer = new IntersectionObserver(handleIntersection, {
    threshold: props.threshold,
    rootMargin: '0px'
  })
  
  if (revealRef.value) {
    observer.observe(revealRef.value)
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal !== null) {
    if (newVal && !isRevealed.value) {
      reveal()
    } else if (!newVal && isRevealed.value) {
      hide()
    }
  }
})

onMounted(() => {
  if (props.modelValue === true) {
    reveal()
  } else if (!props.manual) {
    setupObserver()
  }
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
  }
})

defineExpose({
  reveal,
  hide,
  reset,
  isRevealed
})
</script>

<style scoped>
.reveal-container {
  opacity: 0;
  transition-property: opacity, transform, filter, clip-path;
  transition-duration: var(--reveal-duration);
  transition-timing-function: var(--reveal-easing);
  transition-delay: var(--reveal-delay);
}

/* Fade Effect */
.reveal-fade.is-revealed {
  opacity: 1;
}

/* Slide Effects */
.reveal-slide.reveal-up {
  transform: translateY(var(--reveal-distance));
}

.reveal-slide.reveal-down {
  transform: translateY(calc(-1 * var(--reveal-distance)));
}

.reveal-slide.reveal-left {
  transform: translateX(var(--reveal-distance));
}

.reveal-slide.reveal-right {
  transform: translateX(calc(-1 * var(--reveal-distance)));
}

.reveal-slide.is-revealed {
  opacity: 1;
  transform: translate(0, 0);
}

/* Zoom Effects */
.reveal-zoom.reveal-up,
.reveal-zoom.reveal-down {
  transform: scale(0.8);
}

.reveal-zoom.reveal-left,
.reveal-zoom.reveal-right {
  transform: scale(1.2);
}

.reveal-zoom.is-revealed {
  opacity: 1;
  transform: scale(1);
}

/* Flip Effects */
.reveal-flip {
  perspective: 1000px;
}

.reveal-flip.reveal-up {
  transform: rotateX(-90deg);
  transform-origin: bottom;
}

.reveal-flip.reveal-down {
  transform: rotateX(90deg);
  transform-origin: top;
}

.reveal-flip.reveal-left {
  transform: rotateY(90deg);
  transform-origin: right;
}

.reveal-flip.reveal-right {
  transform: rotateY(-90deg);
  transform-origin: left;
}

.reveal-flip.is-revealed {
  opacity: 1;
  transform: rotate(0);
}

/* Blur Effect */
.reveal-blur {
  filter: blur(20px);
}

.reveal-blur.reveal-up {
  transform: translateY(var(--reveal-distance));
}

.reveal-blur.reveal-down {
  transform: translateY(calc(-1 * var(--reveal-distance)));
}

.reveal-blur.reveal-left {
  transform: translateX(var(--reveal-distance));
}

.reveal-blur.reveal-right {
  transform: translateX(calc(-1 * var(--reveal-distance)));
}

.reveal-blur.is-revealed {
  opacity: 1;
  filter: blur(0);
  transform: translate(0, 0);
}

/* Clip Effect */
.reveal-clip.reveal-up {
  clip-path: inset(100% 0 0 0);
}

.reveal-clip.reveal-down {
  clip-path: inset(0 0 100% 0);
}

.reveal-clip.reveal-left {
  clip-path: inset(0 0 0 100%);
}

.reveal-clip.reveal-right {
  clip-path: inset(0 100% 0 0);
}

.reveal-clip.is-revealed {
  opacity: 1;
  clip-path: inset(0 0 0 0);
}

/* Mask Effect */
.reveal-mask.reveal-up {
  clip-path: polygon(0 100%, 100% 100%, 100% 100%, 0 100%);
}

.reveal-mask.reveal-down {
  clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
}

.reveal-mask.reveal-left {
  clip-path: polygon(100% 0, 100% 0, 100% 100%, 100% 100%);
}

.reveal-mask.reveal-right {
  clip-path: polygon(0 0, 0 0, 0 100%, 0 100%);
}

.reveal-mask.is-revealed {
  opacity: 1;
  clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
}
</style>
