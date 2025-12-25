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
  // Slide direction
  direction: {
    type: String,
    default: 'right',
    validator: (v) => ['up', 'down', 'left', 'right'].includes(v)
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
  // Distance to slide (px or %)
  distance: {
    type: String,
    default: '100%'
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

const transitionName = computed(() => {
  const base = props.fade ? 'slide-fade' : 'slide'
  return `${base}-${props.direction}`
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
/* Slide Up */
.slide-up-enter-active,
.slide-up-leave-active,
.slide-fade-up-enter-active,
.slide-fade-up-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
}

.slide-up-enter-from,
.slide-fade-up-enter-from {
  transform: translateY(v-bind('distance'));
}

.slide-up-leave-to,
.slide-fade-up-leave-to {
  transform: translateY(calc(-1 * v-bind('distance')));
}

.slide-fade-up-enter-from,
.slide-fade-up-leave-to {
  opacity: 0;
}

.slide-up-enter-to,
.slide-up-leave-from,
.slide-fade-up-enter-to,
.slide-fade-up-leave-from {
  transform: translateY(0);
  opacity: 1;
}

/* Slide Down */
.slide-down-enter-active,
.slide-down-leave-active,
.slide-fade-down-enter-active,
.slide-fade-down-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
}

.slide-down-enter-from,
.slide-fade-down-enter-from {
  transform: translateY(calc(-1 * v-bind('distance')));
}

.slide-down-leave-to,
.slide-fade-down-leave-to {
  transform: translateY(v-bind('distance'));
}

.slide-fade-down-enter-from,
.slide-fade-down-leave-to {
  opacity: 0;
}

.slide-down-enter-to,
.slide-down-leave-from,
.slide-fade-down-enter-to,
.slide-fade-down-leave-from {
  transform: translateY(0);
  opacity: 1;
}

/* Slide Left */
.slide-left-enter-active,
.slide-left-leave-active,
.slide-fade-left-enter-active,
.slide-fade-left-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
}

.slide-left-enter-from,
.slide-fade-left-enter-from {
  transform: translateX(v-bind('distance'));
}

.slide-left-leave-to,
.slide-fade-left-leave-to {
  transform: translateX(calc(-1 * v-bind('distance')));
}

.slide-fade-left-enter-from,
.slide-fade-left-leave-to {
  opacity: 0;
}

.slide-left-enter-to,
.slide-left-leave-from,
.slide-fade-left-enter-to,
.slide-fade-left-leave-from {
  transform: translateX(0);
  opacity: 1;
}

/* Slide Right */
.slide-right-enter-active,
.slide-right-leave-active,
.slide-fade-right-enter-active,
.slide-fade-right-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
}

.slide-right-enter-from,
.slide-fade-right-enter-from {
  transform: translateX(calc(-1 * v-bind('distance')));
}

.slide-right-leave-to,
.slide-fade-right-leave-to {
  transform: translateX(v-bind('distance'));
}

.slide-fade-right-enter-from,
.slide-fade-right-leave-to {
  opacity: 0;
}

.slide-right-enter-to,
.slide-right-leave-from,
.slide-fade-right-enter-to,
.slide-fade-right-leave-from {
  transform: translateX(0);
  opacity: 1;
}
</style>
