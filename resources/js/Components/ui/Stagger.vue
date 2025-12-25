<template>
  <TransitionGroup
    :name="transitionName"
    :tag="tag"
    :class="containerClass"
    :style="containerStyle"
    @before-enter="onBeforeEnter"
    @enter="onEnter"
    @after-enter="onAfterEnter"
    @leave="onLeave"
  >
    <slot />
  </TransitionGroup>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Container tag
  tag: {
    type: String,
    default: 'div'
  },
  // Stagger effect type
  effect: {
    type: String,
    default: 'fade-up',
    validator: (v) => [
      'fade',
      'fade-up',
      'fade-down',
      'fade-left',
      'fade-right',
      'zoom',
      'slide-up',
      'slide-down',
      'slide-left',
      'slide-right',
      'flip',
      'rotate'
    ].includes(v)
  },
  // Base duration per item (ms)
  duration: {
    type: Number,
    default: 400
  },
  // Delay between each item (ms)
  staggerDelay: {
    type: Number,
    default: 100
  },
  // Initial delay before first item (ms)
  initialDelay: {
    type: Number,
    default: 0
  },
  // Easing function
  easing: {
    type: String,
    default: 'cubic-bezier(0.4, 0, 0.2, 1)'
  },
  // Distance for slide/fade effects (px)
  distance: {
    type: Number,
    default: 30
  },
  // Container class
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['enter', 'leave', 'complete'])

const transitionName = computed(() => `stagger-${props.effect}`)

const containerStyle = computed(() => ({
  '--stagger-duration': `${props.duration}ms`,
  '--stagger-easing': props.easing,
  '--stagger-distance': `${props.distance}px`
}))

let enterCount = 0
let totalItems = 0

const onBeforeEnter = (el) => {
  const index = Array.from(el.parentNode.children).indexOf(el)
  const delay = props.initialDelay + (index * props.staggerDelay)
  el.style.transitionDelay = `${delay}ms`
  totalItems = el.parentNode.children.length
}

const onEnter = (el, done) => {
  emit('enter', el)
  
  // Auto done after transition
  const handleEnd = () => {
    el.removeEventListener('transitionend', handleEnd)
    done()
  }
  el.addEventListener('transitionend', handleEnd)
}

const onAfterEnter = (el) => {
  el.style.transitionDelay = ''
  enterCount++
  
  if (enterCount >= totalItems) {
    emit('complete')
    enterCount = 0
  }
}

const onLeave = (el, done) => {
  const index = Array.from(el.parentNode.children).indexOf(el)
  const delay = index * (props.staggerDelay / 2)
  el.style.transitionDelay = `${delay}ms`
  
  emit('leave', el)
  
  const handleEnd = () => {
    el.removeEventListener('transitionend', handleEnd)
    el.style.transitionDelay = ''
    done()
  }
  el.addEventListener('transitionend', handleEnd)
}
</script>

<style scoped>
/* Fade */
.stagger-fade-enter-active,
.stagger-fade-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing);
}

.stagger-fade-enter-from,
.stagger-fade-leave-to {
  opacity: 0;
}

/* Fade Up */
.stagger-fade-up-enter-active,
.stagger-fade-up-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-fade-up-enter-from {
  opacity: 0;
  transform: translateY(var(--stagger-distance));
}

.stagger-fade-up-leave-to {
  opacity: 0;
  transform: translateY(calc(-1 * var(--stagger-distance)));
}

/* Fade Down */
.stagger-fade-down-enter-active,
.stagger-fade-down-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-fade-down-enter-from {
  opacity: 0;
  transform: translateY(calc(-1 * var(--stagger-distance)));
}

.stagger-fade-down-leave-to {
  opacity: 0;
  transform: translateY(var(--stagger-distance));
}

/* Fade Left */
.stagger-fade-left-enter-active,
.stagger-fade-left-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-fade-left-enter-from {
  opacity: 0;
  transform: translateX(var(--stagger-distance));
}

.stagger-fade-left-leave-to {
  opacity: 0;
  transform: translateX(calc(-1 * var(--stagger-distance)));
}

/* Fade Right */
.stagger-fade-right-enter-active,
.stagger-fade-right-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-fade-right-enter-from {
  opacity: 0;
  transform: translateX(calc(-1 * var(--stagger-distance)));
}

.stagger-fade-right-leave-to {
  opacity: 0;
  transform: translateX(var(--stagger-distance));
}

/* Zoom */
.stagger-zoom-enter-active,
.stagger-zoom-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-zoom-enter-from {
  opacity: 0;
  transform: scale(0.6);
}

.stagger-zoom-leave-to {
  opacity: 0;
  transform: scale(0.6);
}

/* Slide Up */
.stagger-slide-up-enter-active,
.stagger-slide-up-leave-active {
  transition: transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-slide-up-enter-from {
  transform: translateY(100%);
}

.stagger-slide-up-leave-to {
  transform: translateY(-100%);
}

/* Slide Down */
.stagger-slide-down-enter-active,
.stagger-slide-down-leave-active {
  transition: transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-slide-down-enter-from {
  transform: translateY(-100%);
}

.stagger-slide-down-leave-to {
  transform: translateY(100%);
}

/* Slide Left */
.stagger-slide-left-enter-active,
.stagger-slide-left-leave-active {
  transition: transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-slide-left-enter-from {
  transform: translateX(100%);
}

.stagger-slide-left-leave-to {
  transform: translateX(-100%);
}

/* Slide Right */
.stagger-slide-right-enter-active,
.stagger-slide-right-leave-active {
  transition: transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-slide-right-enter-from {
  transform: translateX(-100%);
}

.stagger-slide-right-leave-to {
  transform: translateX(100%);
}

/* Flip */
.stagger-flip-enter-active,
.stagger-flip-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
  transform-style: preserve-3d;
  backface-visibility: hidden;
}

.stagger-flip-enter-from {
  opacity: 0;
  transform: perspective(1000px) rotateX(-90deg);
}

.stagger-flip-leave-to {
  opacity: 0;
  transform: perspective(1000px) rotateX(90deg);
}

/* Rotate */
.stagger-rotate-enter-active,
.stagger-rotate-leave-active {
  transition: opacity var(--stagger-duration) var(--stagger-easing),
              transform var(--stagger-duration) var(--stagger-easing);
}

.stagger-rotate-enter-from {
  opacity: 0;
  transform: rotate(-180deg) scale(0.5);
}

.stagger-rotate-leave-to {
  opacity: 0;
  transform: rotate(180deg) scale(0.5);
}
</style>
