<template>
  <span
    class="neon-text inline-block"
    :class="[animationClass, { 'neon-flicker': flicker }]"
    :style="neonStyle"
  >
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  color: {
    type: String,
    default: 'cyan',
    validator: (v) => ['cyan', 'pink', 'green', 'purple', 'orange', 'red', 'yellow', 'white', 'custom'].includes(v)
  },
  customColor: {
    type: String,
    default: '#00ffff'
  },
  intensity: {
    type: Number,
    default: 1,
    validator: (v) => v >= 0.1 && v <= 3
  },
  flicker: {
    type: Boolean,
    default: false
  },
  animation: {
    type: String,
    default: 'none',
    validator: (v) => ['none', 'pulse', 'glow', 'blink'].includes(v)
  },
  fontSize: {
    type: String,
    default: '2rem'
  },
  fontWeight: {
    type: String,
    default: 'bold'
  }
})

const colorMap = {
  cyan: '#00ffff',
  pink: '#ff00ff',
  green: '#00ff00',
  purple: '#bf00ff',
  orange: '#ff6600',
  red: '#ff0000',
  yellow: '#ffff00',
  white: '#ffffff'
}

const activeColor = computed(() => {
  return props.color === 'custom' ? props.customColor : colorMap[props.color]
})

const neonStyle = computed(() => {
  const color = activeColor.value
  const i = props.intensity
  
  return {
    color: color,
    fontSize: props.fontSize,
    fontWeight: props.fontWeight,
    textShadow: `
      0 0 ${5 * i}px ${color},
      0 0 ${10 * i}px ${color},
      0 0 ${20 * i}px ${color},
      0 0 ${40 * i}px ${color},
      0 0 ${80 * i}px ${color}
    `.trim()
  }
})

const animationClass = computed(() => {
  const animations = {
    none: '',
    pulse: 'neon-pulse',
    glow: 'neon-glow',
    blink: 'neon-blink'
  }
  return animations[props.animation]
})
</script>

<style scoped>
.neon-text {
  font-family: 'Arial Black', 'Arial Bold', sans-serif;
  letter-spacing: 0.1em;
}

.neon-pulse {
  animation: neon-pulse-anim 2s ease-in-out infinite;
}

.neon-glow {
  animation: neon-glow-anim 3s ease-in-out infinite;
}

.neon-blink {
  animation: neon-blink-anim 1s step-end infinite;
}

.neon-flicker {
  animation: neon-flicker-anim 0.15s ease-in-out infinite alternate;
}

@keyframes neon-pulse-anim {
  0%, 100% {
    opacity: 1;
    filter: brightness(1);
  }
  50% {
    opacity: 0.8;
    filter: brightness(1.5);
  }
}

@keyframes neon-glow-anim {
  0%, 100% {
    filter: brightness(1) blur(0px);
  }
  50% {
    filter: brightness(1.3) blur(1px);
  }
}

@keyframes neon-blink-anim {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0;
  }
}

@keyframes neon-flicker-anim {
  0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
    opacity: 1;
    text-shadow: inherit;
  }
  20%, 24%, 55% {
    opacity: 0.4;
    text-shadow: none;
  }
}
</style>
