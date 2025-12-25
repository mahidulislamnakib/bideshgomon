<template>
  <div
    class="morph-container"
    :class="{ 'is-morphing': isMorphing }"
    :style="morphStyle"
  >
    <Transition
      :name="transitionName"
      :mode="mode"
      @before-enter="onBeforeEnter"
      @enter="onEnter"
      @after-enter="onAfterEnter"
      @before-leave="onBeforeLeave"
      @leave="onLeave"
      @after-leave="onAfterLeave"
    >
      <component
        :is="currentShape"
        :key="shapeKey"
        class="morph-shape"
        :style="shapeStyle"
      />
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, h } from 'vue'

const props = defineProps({
  // Current shape type
  shape: {
    type: String,
    default: 'circle',
    validator: (v) => ['circle', 'square', 'triangle', 'hexagon', 'star', 'heart', 'diamond', 'blob'].includes(v)
  },
  // Shape size
  size: {
    type: Number,
    default: 100
  },
  // Shape color
  color: {
    type: String,
    default: '#3b82f6'
  },
  // Animation duration (ms)
  duration: {
    type: Number,
    default: 500
  },
  // Easing function
  easing: {
    type: String,
    default: 'cubic-bezier(0.4, 0, 0.2, 1)'
  },
  // Transition mode
  mode: {
    type: String,
    default: 'out-in',
    validator: (v) => ['out-in', 'in-out', 'default'].includes(v)
  }
})

const emit = defineEmits(['morph-start', 'morph-end'])

const isMorphing = ref(false)
const shapeKey = ref(0)

const transitionName = 'morph'

const morphStyle = computed(() => ({
  '--morph-duration': `${props.duration}ms`,
  '--morph-easing': props.easing,
  '--morph-size': `${props.size}px`,
  '--morph-color': props.color
}))

const shapeStyle = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
  backgroundColor: props.color
}))

// Shape clip paths
const clipPaths = {
  circle: 'circle(50% at 50% 50%)',
  square: 'polygon(0 0, 100% 0, 100% 100%, 0 100%)',
  triangle: 'polygon(50% 0%, 0% 100%, 100% 100%)',
  hexagon: 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)',
  star: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)',
  heart: 'path("M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z")',
  diamond: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
  blob: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'
}

const currentShape = computed(() => {
  const clipPath = clipPaths[props.shape] || clipPaths.circle
  
  return {
    render() {
      return h('div', {
        style: {
          clipPath: clipPath,
          WebkitClipPath: clipPath
        }
      })
    }
  }
})

const onBeforeEnter = () => {
  isMorphing.value = true
  emit('morph-start', props.shape)
}

const onEnter = () => {}

const onAfterEnter = () => {
  isMorphing.value = false
  emit('morph-end', props.shape)
}

const onBeforeLeave = () => {
  isMorphing.value = true
}

const onLeave = () => {}

const onAfterLeave = () => {}

watch(() => props.shape, () => {
  shapeKey.value++
})

// Public methods
const morphTo = (newShape) => {
  if (clipPaths[newShape]) {
    shapeKey.value++
  }
}

defineExpose({
  morphTo,
  isMorphing
})
</script>

<style scoped>
.morph-container {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.morph-shape {
  transition: clip-path var(--morph-duration) var(--morph-easing),
              transform var(--morph-duration) var(--morph-easing);
}

/* Morph Transition */
.morph-enter-active,
.morph-leave-active {
  transition: opacity var(--morph-duration) var(--morph-easing),
              transform var(--morph-duration) var(--morph-easing);
}

.morph-enter-from {
  opacity: 0;
  transform: scale(0.8) rotate(-10deg);
}

.morph-leave-to {
  opacity: 0;
  transform: scale(0.8) rotate(10deg);
}

.morph-enter-to,
.morph-leave-from {
  opacity: 1;
  transform: scale(1) rotate(0deg);
}

/* Hover effect */
.morph-container:hover .morph-shape {
  transform: scale(1.05);
}

/* Morphing state */
.is-morphing .morph-shape {
  pointer-events: none;
}
</style>
