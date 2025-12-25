<template>
  <Transition
    :name="transitionName"
    :mode="mode"
    :appear="appear"
    :duration="duration"
    @before-enter="onBeforeEnter"
    @enter="onEnter"
    @after-enter="onAfterEnter"
    @before-leave="onBeforeLeave"
    @leave="onLeave"
    @after-leave="onAfterLeave"
  >
    <slot />
  </Transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Scale type
  type: {
    type: String,
    default: 'up',
    validator: (v) => [
      'up',
      'down',
      'x',
      'y',
      'center',
      'top',
      'bottom',
      'left',
      'right'
    ].includes(v)
  },
  // Animation duration in ms
  duration: {
    type: [Number, Object],
    default: 300
  },
  // Transition mode
  mode: {
    type: String,
    default: 'out-in',
    validator: (v) => ['out-in', 'in-out', 'default'].includes(v)
  },
  // Appear on initial render
  appear: {
    type: Boolean,
    default: false
  },
  // Scale factor (0-1 for scale up, >1 for scale down start)
  scale: {
    type: Number,
    default: 0.8
  },
  // Include fade effect
  fade: {
    type: Boolean,
    default: true
  },
  // Easing function
  easing: {
    type: String,
    default: 'cubic-bezier(0.4, 0, 0.2, 1)'
  }
})

const emit = defineEmits([
  'before-enter',
  'enter',
  'after-enter',
  'before-leave',
  'leave',
  'after-leave'
])

const transitionName = computed(() => `scale-${props.type}`)

// Compute transform origin based on type
const transformOrigin = computed(() => {
  const origins = {
    up: 'center',
    down: 'center',
    x: 'center',
    y: 'center',
    center: 'center',
    top: 'top center',
    bottom: 'bottom center',
    left: 'center left',
    right: 'center right'
  }
  return origins[props.type] || 'center'
})

// Lifecycle hooks
const onBeforeEnter = (el) => emit('before-enter', el)
const onEnter = (el, done) => emit('enter', el, done)
const onAfterEnter = (el) => emit('after-enter', el)
const onBeforeLeave = (el) => emit('before-leave', el)
const onLeave = (el, done) => emit('leave', el, done)
const onAfterLeave = (el) => emit('after-leave', el)
</script>

<style scoped>
/* Scale Up (from smaller) */
.scale-up-enter-active,
.scale-up-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('transformOrigin');
}

.scale-up-enter-from {
  transform: scale(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-up-leave-to {
  transform: scale(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-up-enter-to,
.scale-up-leave-from {
  transform: scale(1);
  opacity: 1;
}

/* Scale Down (from larger) */
.scale-down-enter-active,
.scale-down-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('transformOrigin');
}

.scale-down-enter-from {
  transform: scale(v-bind('1 / scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-down-leave-to {
  transform: scale(v-bind('1 / scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-down-enter-to,
.scale-down-leave-from {
  transform: scale(1);
  opacity: 1;
}

/* Scale X (horizontal only) */
.scale-x-enter-active,
.scale-x-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('transformOrigin');
}

.scale-x-enter-from {
  transform: scaleX(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-x-leave-to {
  transform: scaleX(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-x-enter-to,
.scale-x-leave-from {
  transform: scaleX(1);
  opacity: 1;
}

/* Scale Y (vertical only) */
.scale-y-enter-active,
.scale-y-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('transformOrigin');
}

.scale-y-enter-from {
  transform: scaleY(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-y-leave-to {
  transform: scaleY(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.scale-y-enter-to,
.scale-y-leave-from {
  transform: scaleY(1);
  opacity: 1;
}

/* Scale from Center */
.scale-center-enter-active,
.scale-center-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: center;
}

.scale-center-enter-from {
  transform: scale(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-center-leave-to {
  transform: scale(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-center-enter-to,
.scale-center-leave-from {
  transform: scale(1);
  opacity: 1;
}

/* Scale from Top */
.scale-top-enter-active,
.scale-top-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: top center;
}

.scale-top-enter-from {
  transform: scaleY(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-top-leave-to {
  transform: scaleY(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-top-enter-to,
.scale-top-leave-from {
  transform: scaleY(1);
  opacity: 1;
}

/* Scale from Bottom */
.scale-bottom-enter-active,
.scale-bottom-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: bottom center;
}

.scale-bottom-enter-from {
  transform: scaleY(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-bottom-leave-to {
  transform: scaleY(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-bottom-enter-to,
.scale-bottom-leave-from {
  transform: scaleY(1);
  opacity: 1;
}

/* Scale from Left */
.scale-left-enter-active,
.scale-left-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: center left;
}

.scale-left-enter-from {
  transform: scaleX(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-left-leave-to {
  transform: scaleX(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-left-enter-to,
.scale-left-leave-from {
  transform: scaleX(1);
  opacity: 1;
}

/* Scale from Right */
.scale-right-enter-active,
.scale-right-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: center right;
}

.scale-right-enter-from {
  transform: scaleX(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-right-leave-to {
  transform: scaleX(0);
  opacity: v-bind('fade ? 0 : 1');
}

.scale-right-enter-to,
.scale-right-leave-from {
  transform: scaleX(1);
  opacity: 1;
}
</style>
