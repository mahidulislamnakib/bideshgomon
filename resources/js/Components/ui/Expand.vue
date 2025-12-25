<template>
  <div
    ref="containerRef"
    class="expand-container"
    :class="[`expand-${direction}`, { 'is-expanded': isExpanded }]"
    :style="containerStyle"
  >
    <div ref="contentRef" class="expand-content">
      <slot />
    </div>
    
    <!-- Gradient Overlay -->
    <div
      v-if="showGradient && !isExpanded"
      class="expand-gradient"
      :style="gradientStyle"
    />
    
    <!-- Toggle Button -->
    <div v-if="showToggle" class="expand-toggle-wrapper">
      <button
        type="button"
        class="expand-toggle"
        :disabled="disabled"
        @click="toggle"
      >
        <slot name="toggle" :expanded="isExpanded">
          <span>{{ isExpanded ? collapseText : expandText }}</span>
          <svg
            class="expand-icon"
            :class="{ 'rotate-180': isExpanded }"
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <polyline points="6 9 12 15 18 9" />
          </svg>
        </slot>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

const props = defineProps({
  // v-model binding
  modelValue: {
    type: Boolean,
    default: false
  },
  // Collapsed size (px)
  collapsedSize: {
    type: Number,
    default: 150
  },
  // Expand direction
  direction: {
    type: String,
    default: 'vertical',
    validator: (v) => ['vertical', 'horizontal'].includes(v)
  },
  // Animation duration (ms)
  duration: {
    type: Number,
    default: 300
  },
  // Easing
  easing: {
    type: String,
    default: 'ease-out'
  },
  // Show gradient overlay
  showGradient: {
    type: Boolean,
    default: true
  },
  // Gradient color (from transparent to this)
  gradientColor: {
    type: String,
    default: 'white'
  },
  // Show toggle button
  showToggle: {
    type: Boolean,
    default: true
  },
  // Expand button text
  expandText: {
    type: String,
    default: 'Read more'
  },
  // Collapse button text
  collapseText: {
    type: String,
    default: 'Read less'
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'expand', 'collapse'])

const containerRef = ref(null)
const contentRef = ref(null)
const isExpanded = ref(props.modelValue)
const fullSize = ref(0)
const needsExpand = ref(true)

const sizeProperty = computed(() => props.direction === 'vertical' ? 'height' : 'width')

const containerStyle = computed(() => {
  const size = isExpanded.value ? `${fullSize.value}px` : `${props.collapsedSize}px`
  return {
    [sizeProperty.value]: needsExpand.value ? size : 'auto',
    transition: `${sizeProperty.value} ${props.duration}ms ${props.easing}`,
    overflow: 'hidden',
    position: 'relative'
  }
})

const gradientStyle = computed(() => {
  const direction = props.direction === 'vertical' ? 'to bottom' : 'to right'
  return {
    background: `linear-gradient(${direction}, transparent, ${props.gradientColor})`
  }
})

const measureContent = async () => {
  await nextTick()
  if (contentRef.value) {
    fullSize.value = props.direction === 'vertical'
      ? contentRef.value.scrollHeight
      : contentRef.value.scrollWidth
    needsExpand.value = fullSize.value > props.collapsedSize
  }
}

const toggle = () => {
  if (props.disabled) return
  isExpanded.value = !isExpanded.value
  emit('update:modelValue', isExpanded.value)
  emit(isExpanded.value ? 'expand' : 'collapse')
}

const expand = () => {
  if (!isExpanded.value && !props.disabled) {
    isExpanded.value = true
    emit('update:modelValue', true)
    emit('expand')
  }
}

const collapse = () => {
  if (isExpanded.value && !props.disabled) {
    isExpanded.value = false
    emit('update:modelValue', false)
    emit('collapse')
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal !== isExpanded.value) {
    isExpanded.value = newVal
  }
})

onMounted(() => {
  measureContent()
  window.addEventListener('resize', measureContent)
})

defineExpose({
  toggle,
  expand,
  collapse,
  isExpanded,
  measureContent
})
</script>

<style scoped>
.expand-container {
  position: relative;
}

.expand-content {
  min-height: 100%;
}

.expand-gradient {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 60px;
  pointer-events: none;
}

.expand-horizontal .expand-gradient {
  top: 0;
  bottom: 0;
  left: auto;
  right: 0;
  width: 60px;
  height: auto;
}

.expand-toggle-wrapper {
  display: flex;
  justify-content: center;
  padding-top: 0.5rem;
}

.expand-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #3b82f6;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: color 0.2s;
}

.expand-toggle:hover:not(:disabled) {
  color: #2563eb;
}

.expand-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.expand-icon {
  transition: transform 0.3s ease;
}
</style>
