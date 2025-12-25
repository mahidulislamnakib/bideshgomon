<template>
  <component
    :is="as"
    class="relative inline-block"
    :class="[containerClass, { 'glitch-active': isActive }]"
    :style="textStyle"
    @mouseenter="trigger === 'hover' && startGlitch()"
    @mouseleave="trigger === 'hover' && stopGlitch()"
  >
    <!-- Main text -->
    <span class="relative z-10">
      <slot>{{ text }}</slot>
    </span>

    <!-- Glitch layers -->
    <span
      v-if="isActive"
      class="glitch-layer glitch-before absolute inset-0 z-0"
      :style="{ ...textStyle, color: beforeColor }"
      aria-hidden="true"
    >
      <slot>{{ text }}</slot>
    </span>
    <span
      v-if="isActive"
      class="glitch-layer glitch-after absolute inset-0 z-0"
      :style="{ ...textStyle, color: afterColor }"
      aria-hidden="true"
    >
      <slot>{{ text }}</slot>
    </span>
  </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  as: {
    type: String,
    default: 'span'
  },
  beforeColor: {
    type: String,
    default: '#ff0000'
  },
  afterColor: {
    type: String,
    default: '#00ffff'
  },
  intensity: {
    type: Number,
    default: 5,
    validator: (v) => v >= 1 && v <= 20
  },
  speed: {
    type: Number,
    default: 200
  },
  trigger: {
    type: String,
    default: 'always',
    validator: (v) => ['always', 'hover', 'manual'].includes(v)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['start', 'stop'])

const isActive = ref(false)
let intervalId = null

const textStyle = computed(() => ({
  '--glitch-intensity': `${props.intensity}px`
}))

function startGlitch() {
  if (props.disabled || isActive.value) return

  isActive.value = true
  emit('start')
}

function stopGlitch() {
  if (!isActive.value) return

  isActive.value = false
  emit('stop')
}

function toggleGlitch() {
  if (isActive.value) {
    stopGlitch()
  } else {
    startGlitch()
  }
}

// Watch trigger mode
watch(() => props.trigger, (newTrigger) => {
  if (newTrigger === 'always' && !props.disabled) {
    startGlitch()
  } else if (newTrigger !== 'always') {
    stopGlitch()
  }
}, { immediate: true })

// Watch disabled state
watch(() => props.disabled, (disabled) => {
  if (disabled) {
    stopGlitch()
  } else if (props.trigger === 'always') {
    startGlitch()
  }
})

onMounted(() => {
  if (props.trigger === 'always' && !props.disabled) {
    startGlitch()
  }
})

onUnmounted(() => {
  stopGlitch()
})

defineExpose({
  startGlitch,
  stopGlitch,
  toggleGlitch,
  isActive
})
</script>

<style scoped>
.glitch-layer {
  pointer-events: none;
  opacity: 0.8;
}

.glitch-active .glitch-before {
  animation: glitch-before 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
  clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%);
}

.glitch-active .glitch-after {
  animation: glitch-after 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite reverse;
  clip-path: polygon(0 55%, 100% 55%, 100% 100%, 0 100%);
}

@keyframes glitch-before {
  0% {
    transform: translate(0);
  }
  20% {
    transform: translate(calc(var(--glitch-intensity) * -1), calc(var(--glitch-intensity) * 0.5));
  }
  40% {
    transform: translate(var(--glitch-intensity), calc(var(--glitch-intensity) * -0.5));
  }
  60% {
    transform: translate(calc(var(--glitch-intensity) * -0.5), var(--glitch-intensity));
  }
  80% {
    transform: translate(calc(var(--glitch-intensity) * 0.5), calc(var(--glitch-intensity) * -1));
  }
  100% {
    transform: translate(0);
  }
}

@keyframes glitch-after {
  0% {
    transform: translate(0);
  }
  20% {
    transform: translate(var(--glitch-intensity), calc(var(--glitch-intensity) * -0.5));
  }
  40% {
    transform: translate(calc(var(--glitch-intensity) * -1), calc(var(--glitch-intensity) * 0.5));
  }
  60% {
    transform: translate(calc(var(--glitch-intensity) * 0.5), calc(var(--glitch-intensity) * -1));
  }
  80% {
    transform: translate(calc(var(--glitch-intensity) * -0.5), var(--glitch-intensity));
  }
  100% {
    transform: translate(0);
  }
}
</style>
