<template>
  <component
    :is="as"
    :class="['inline-block bg-clip-text text-transparent', animationClass]"
    :style="gradientStyle"
  >
    <slot>{{ text }}</slot>
  </component>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  as: {
    type: String,
    default: 'span'
  },
  colors: {
    type: Array,
    default: () => ['#6366f1', '#8b5cf6', '#d946ef']
  },
  direction: {
    type: String,
    default: 'to right',
    validator: (v) => [
      'to right',
      'to left',
      'to bottom',
      'to top',
      'to bottom right',
      'to bottom left',
      'to top right',
      'to top left'
    ].includes(v)
  },
  preset: {
    type: String,
    default: null,
    validator: (v) => [
      null,
      'sunset',
      'ocean',
      'forest',
      'fire',
      'candy',
      'midnight',
      'rainbow',
      'aurora'
    ].includes(v)
  },
  animate: {
    type: Boolean,
    default: false
  },
  animationDuration: {
    type: Number,
    default: 3
  }
})

// Preset gradient colors
const presets = {
  sunset: ['#f97316', '#ec4899', '#8b5cf6'],
  ocean: ['#06b6d4', '#3b82f6', '#6366f1'],
  forest: ['#22c55e', '#10b981', '#14b8a6'],
  fire: ['#ef4444', '#f97316', '#fbbf24'],
  candy: ['#ec4899', '#f472b6', '#f9a8d4'],
  midnight: ['#1e3a8a', '#3730a3', '#4c1d95'],
  rainbow: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6'],
  aurora: ['#22d3ee', '#a855f7', '#ec4899', '#22d3ee']
}

const gradientColors = computed(() => {
  if (props.preset && presets[props.preset]) {
    return presets[props.preset]
  }
  return props.colors
})

const gradientStyle = computed(() => {
  const colors = gradientColors.value.join(', ')
  
  if (props.animate) {
    // For animation, we need a larger gradient that can be animated
    const animatedColors = [...gradientColors.value, ...gradientColors.value].join(', ')
    return {
      backgroundImage: `linear-gradient(${props.direction}, ${animatedColors})`,
      backgroundSize: '200% auto',
      animationDuration: `${props.animationDuration}s`
    }
  }

  return {
    backgroundImage: `linear-gradient(${props.direction}, ${colors})`
  }
})

const animationClass = computed(() => {
  return props.animate ? 'animate-gradient-x' : ''
})
</script>

<style scoped>
@keyframes gradient-x {
  0% {
    background-position: 0% center;
  }
  100% {
    background-position: -200% center;
  }
}

.animate-gradient-x {
  animation: gradient-x var(--tw-animate-duration, 3s) linear infinite;
}
</style>
