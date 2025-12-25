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
  // Rotation type
  type: {
    type: String,
    default: 'clockwise',
    validator: (v) => [
      'clockwise',
      'counter-clockwise',
      'flip-x',
      'flip-y',
      'swing',
      'roll-in',
      'roll-out'
    ].includes(v)
  },
  // Animation duration in ms
  duration: {
    type: [Number, Object],
    default: 400
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
  // Rotation degrees
  degrees: {
    type: Number,
    default: 90
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
  },
  // Transform origin
  origin: {
    type: String,
    default: 'center'
  },
  // Perspective for 3D rotations (px)
  perspective: {
    type: Number,
    default: 1000
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

const transitionName = computed(() => `rotate-${props.type}`)

// Lifecycle hooks
const onBeforeEnter = (el) => emit('before-enter', el)
const onEnter = (el, done) => emit('enter', el, done)
const onAfterEnter = (el) => emit('after-enter', el)
const onBeforeLeave = (el) => emit('before-leave', el)
const onLeave = (el, done) => emit('leave', el, done)
const onAfterLeave = (el) => emit('after-leave', el)
</script>

<style scoped>
/* Clockwise Rotation */
.rotate-clockwise-enter-active,
.rotate-clockwise-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.rotate-clockwise-enter-from {
  transform: rotate(v-bind('-degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-clockwise-leave-to {
  transform: rotate(v-bind('degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-clockwise-enter-to,
.rotate-clockwise-leave-from {
  transform: rotate(0deg);
  opacity: 1;
}

/* Counter-Clockwise Rotation */
.rotate-counter-clockwise-enter-active,
.rotate-counter-clockwise-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.rotate-counter-clockwise-enter-from {
  transform: rotate(v-bind('degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-counter-clockwise-leave-to {
  transform: rotate(v-bind('-degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-counter-clockwise-enter-to,
.rotate-counter-clockwise-leave-from {
  transform: rotate(0deg);
  opacity: 1;
}

/* Flip X (3D) */
.rotate-flip-x-enter-active,
.rotate-flip-x-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
  perspective: v-bind('perspective + "px"');
  backface-visibility: hidden;
}

.rotate-flip-x-enter-from {
  transform: perspective(v-bind('perspective + "px"')) rotateX(v-bind('-degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-flip-x-leave-to {
  transform: perspective(v-bind('perspective + "px"')) rotateX(v-bind('degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-flip-x-enter-to,
.rotate-flip-x-leave-from {
  transform: perspective(v-bind('perspective + "px"')) rotateX(0deg);
  opacity: 1;
}

/* Flip Y (3D) */
.rotate-flip-y-enter-active,
.rotate-flip-y-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
  perspective: v-bind('perspective + "px"');
  backface-visibility: hidden;
}

.rotate-flip-y-enter-from {
  transform: perspective(v-bind('perspective + "px"')) rotateY(v-bind('-degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-flip-y-leave-to {
  transform: perspective(v-bind('perspective + "px"')) rotateY(v-bind('degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-flip-y-enter-to,
.rotate-flip-y-leave-from {
  transform: perspective(v-bind('perspective + "px"')) rotateY(0deg);
  opacity: 1;
}

/* Swing */
.rotate-swing-enter-active,
.rotate-swing-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: top center;
}

.rotate-swing-enter-from {
  transform: rotate(v-bind('degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-swing-leave-to {
  transform: rotate(v-bind('-degrees + "deg"'));
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-swing-enter-to,
.rotate-swing-leave-from {
  transform: rotate(0deg);
  opacity: 1;
}

/* Roll In */
.rotate-roll-in-enter-active,
.rotate-roll-in-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
}

.rotate-roll-in-enter-from {
  transform: translateX(-100%) rotate(-120deg);
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-roll-in-leave-to {
  transform: translateX(100%) rotate(120deg);
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-roll-in-enter-to,
.rotate-roll-in-leave-from {
  transform: translateX(0) rotate(0deg);
  opacity: 1;
}

/* Roll Out */
.rotate-roll-out-enter-active,
.rotate-roll-out-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
}

.rotate-roll-out-enter-from {
  transform: translateX(100%) rotate(120deg);
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-roll-out-leave-to {
  transform: translateX(-100%) rotate(-120deg);
  opacity: v-bind('fade ? 0 : 1');
}

.rotate-roll-out-enter-to,
.rotate-roll-out-leave-from {
  transform: translateX(0) rotate(0deg);
  opacity: 1;
}
</style>
