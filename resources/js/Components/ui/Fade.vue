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
  // Fade direction
  direction: {
    type: String,
    default: 'none',
    validator: (v) => ['none', 'up', 'down', 'left', 'right'].includes(v)
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
  // Distance for directional fade (px)
  distance: {
    type: Number,
    default: 20
  },
  // Easing function
  easing: {
    type: String,
    default: 'ease-out'
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

const transitionName = computed(() => {
  if (props.direction === 'none') return 'fade'
  return `fade-${props.direction}`
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
/* Base fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity v-bind('duration + "ms"') v-bind('easing');
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
}

/* Fade up */
.fade-up-enter-active,
.fade-up-leave-active {
  transition: opacity v-bind('duration + "ms"') v-bind('easing'),
              transform v-bind('duration + "ms"') v-bind('easing');
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(v-bind('distance + "px"'));
}

.fade-up-leave-to {
  opacity: 0;
  transform: translateY(v-bind('-distance + "px"'));
}

.fade-up-enter-to,
.fade-up-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* Fade down */
.fade-down-enter-active,
.fade-down-leave-active {
  transition: opacity v-bind('duration + "ms"') v-bind('easing'),
              transform v-bind('duration + "ms"') v-bind('easing');
}

.fade-down-enter-from {
  opacity: 0;
  transform: translateY(v-bind('-distance + "px"'));
}

.fade-down-leave-to {
  opacity: 0;
  transform: translateY(v-bind('distance + "px"'));
}

.fade-down-enter-to,
.fade-down-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* Fade left */
.fade-left-enter-active,
.fade-left-leave-active {
  transition: opacity v-bind('duration + "ms"') v-bind('easing'),
              transform v-bind('duration + "ms"') v-bind('easing');
}

.fade-left-enter-from {
  opacity: 0;
  transform: translateX(v-bind('distance + "px"'));
}

.fade-left-leave-to {
  opacity: 0;
  transform: translateX(v-bind('-distance + "px"'));
}

.fade-left-enter-to,
.fade-left-leave-from {
  opacity: 1;
  transform: translateX(0);
}

/* Fade right */
.fade-right-enter-active,
.fade-right-leave-active {
  transition: opacity v-bind('duration + "ms"') v-bind('easing'),
              transform v-bind('duration + "ms"') v-bind('easing');
}

.fade-right-enter-from {
  opacity: 0;
  transform: translateX(v-bind('-distance + "px"'));
}

.fade-right-leave-to {
  opacity: 0;
  transform: translateX(v-bind('distance + "px"'));
}

.fade-right-enter-to,
.fade-right-leave-from {
  opacity: 1;
  transform: translateX(0);
}
</style>
