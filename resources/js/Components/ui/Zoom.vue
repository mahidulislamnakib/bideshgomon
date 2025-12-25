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
  // Zoom type
  type: {
    type: String,
    default: 'in',
    validator: (v) => ['in', 'out', 'in-up', 'in-down', 'in-left', 'in-right'].includes(v)
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
  // Scale factor for zoom (0-1 for zoom-in, >1 for zoom-out)
  scale: {
    type: Number,
    default: 0.9
  },
  // Distance for directional zoom (px)
  distance: {
    type: Number,
    default: 30
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

const transitionName = computed(() => `zoom-${props.type}`)

// Lifecycle hooks
const onBeforeEnter = (el) => emit('before-enter', el)
const onEnter = (el, done) => emit('enter', el, done)
const onAfterEnter = (el) => emit('after-enter', el)
const onBeforeLeave = (el) => emit('before-leave', el)
const onLeave = (el, done) => emit('leave', el, done)
const onAfterLeave = (el) => emit('after-leave', el)

const enterScale = computed(() => props.type === 'out' ? 1 / props.scale : props.scale)
const leaveScale = computed(() => props.type === 'out' ? props.scale : 1 / props.scale)
</script>

<style scoped>
/* Zoom In */
.zoom-in-enter-active,
.zoom-in-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.zoom-in-enter-from {
  transform: scale(v-bind('enterScale'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-leave-to {
  transform: scale(v-bind('leaveScale'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-enter-to,
.zoom-in-leave-from {
  transform: scale(1);
  opacity: 1;
}

/* Zoom Out */
.zoom-out-enter-active,
.zoom-out-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.zoom-out-enter-from {
  transform: scale(v-bind('1 / scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-out-leave-to {
  transform: scale(v-bind('scale'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-out-enter-to,
.zoom-out-leave-from {
  transform: scale(1);
  opacity: 1;
}

/* Zoom In Up */
.zoom-in-up-enter-active,
.zoom-in-up-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.zoom-in-up-enter-from {
  transform: scale(v-bind('scale')) translateY(v-bind('distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-up-leave-to {
  transform: scale(v-bind('scale')) translateY(v-bind('-distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-up-enter-to,
.zoom-in-up-leave-from {
  transform: scale(1) translateY(0);
  opacity: 1;
}

/* Zoom In Down */
.zoom-in-down-enter-active,
.zoom-in-down-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.zoom-in-down-enter-from {
  transform: scale(v-bind('scale')) translateY(v-bind('-distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-down-leave-to {
  transform: scale(v-bind('scale')) translateY(v-bind('distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-down-enter-to,
.zoom-in-down-leave-from {
  transform: scale(1) translateY(0);
  opacity: 1;
}

/* Zoom In Left */
.zoom-in-left-enter-active,
.zoom-in-left-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.zoom-in-left-enter-from {
  transform: scale(v-bind('scale')) translateX(v-bind('distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-left-leave-to {
  transform: scale(v-bind('scale')) translateX(v-bind('-distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-left-enter-to,
.zoom-in-left-leave-from {
  transform: scale(1) translateX(0);
  opacity: 1;
}

/* Zoom In Right */
.zoom-in-right-enter-active,
.zoom-in-right-leave-active {
  transition: transform v-bind('duration + "ms"') v-bind('easing'),
              opacity v-bind('duration + "ms"') v-bind('easing');
  transform-origin: v-bind('origin');
}

.zoom-in-right-enter-from {
  transform: scale(v-bind('scale')) translateX(v-bind('-distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-right-leave-to {
  transform: scale(v-bind('scale')) translateX(v-bind('distance + "px"'));
  opacity: v-bind('fade ? 0 : 1');
}

.zoom-in-right-enter-to,
.zoom-in-right-leave-from {
  transform: scale(1) translateX(0);
  opacity: 1;
}
</style>
