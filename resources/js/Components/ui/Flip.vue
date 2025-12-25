<template>
  <div
    class="flip-container"
    :class="containerClasses"
    :style="containerStyle"
    @click="handleClick"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <div
      class="flip-card"
      :class="{ 'flipped': isFlipped }"
      :style="cardStyle"
    >
      <!-- Front -->
      <div class="flip-face flip-front" :class="faceClasses">
        <slot name="front">
          <div class="p-4 text-center">Front</div>
        </slot>
      </div>
      
      <!-- Back -->
      <div class="flip-face flip-back" :class="[faceClasses, backClasses]">
        <slot name="back">
          <div class="p-4 text-center">Back</div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: undefined
  },
  direction: {
    type: String,
    default: 'horizontal',
    validator: (v) => ['horizontal', 'vertical'].includes(v)
  },
  trigger: {
    type: String,
    default: 'click',
    validator: (v) => ['click', 'hover', 'manual'].includes(v)
  },
  duration: {
    type: Number,
    default: 600
  },
  perspective: {
    type: Number,
    default: 1000
  },
  disabled: {
    type: Boolean,
    default: false
  },
  width: {
    type: String,
    default: '100%'
  },
  height: {
    type: String,
    default: 'auto'
  }
})

const emit = defineEmits(['update:modelValue', 'flip', 'flipStart', 'flipEnd'])

const internalFlipped = ref(false)

const isFlipped = computed(() => {
  return props.modelValue !== undefined ? props.modelValue : internalFlipped.value
})

const containerClasses = computed(() => ({
  'cursor-pointer': props.trigger !== 'manual' && !props.disabled,
  'pointer-events-none': props.disabled
}))

const containerStyle = computed(() => ({
  perspective: `${props.perspective}px`,
  width: props.width,
  height: props.height
}))

const cardStyle = computed(() => ({
  transitionDuration: `${props.duration}ms`
}))

const faceClasses = computed(() => ({
  'flip-horizontal': props.direction === 'horizontal',
  'flip-vertical': props.direction === 'vertical'
}))

const backClasses = computed(() => ({
  'rotate-y-180': props.direction === 'horizontal',
  'rotate-x-180': props.direction === 'vertical'
}))

function setFlipped(value) {
  if (props.disabled) return

  emit('flipStart', value)
  
  if (props.modelValue !== undefined) {
    emit('update:modelValue', value)
  } else {
    internalFlipped.value = value
  }
  
  emit('flip', value)
  
  setTimeout(() => {
    emit('flipEnd', value)
  }, props.duration)
}

function toggle() {
  setFlipped(!isFlipped.value)
}

function flipToFront() {
  setFlipped(false)
}

function flipToBack() {
  setFlipped(true)
}

function handleClick() {
  if (props.trigger === 'click') {
    toggle()
  }
}

function handleMouseEnter() {
  if (props.trigger === 'hover') {
    setFlipped(true)
  }
}

function handleMouseLeave() {
  if (props.trigger === 'hover') {
    setFlipped(false)
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal !== undefined) {
    internalFlipped.value = newVal
  }
})

defineExpose({ toggle, flipToFront, flipToBack, isFlipped })
</script>

<style scoped>
.flip-container {
  display: inline-block;
}

.flip-card {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transition: transform cubic-bezier(0.4, 0, 0.2, 1);
}

.flip-card.flipped .flip-horizontal {
  transform: rotateY(180deg);
}

.flip-card.flipped .flip-vertical {
  transform: rotateX(180deg);
}

.flip-face {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

.flip-front {
  z-index: 2;
}

.flip-back {
  z-index: 1;
}

.flip-back.rotate-y-180 {
  transform: rotateY(180deg);
}

.flip-back.rotate-x-180 {
  transform: rotateX(180deg);
}

/* Make first face visible when relative positioning needed */
.flip-container:not(.flip-card.flipped) .flip-front {
  position: relative;
}

.flip-card.flipped .flip-front {
  position: absolute;
}

.flip-card.flipped .flip-back {
  position: relative;
}
</style>
