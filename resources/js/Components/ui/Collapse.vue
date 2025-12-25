<template>
  <div class="collapse-wrapper">
    <!-- Toggle Button/Header -->
    <div
      v-if="$slots.header || showToggle"
      class="collapse-header"
      :class="{ 'cursor-pointer': !disabled }"
      @click="!disabled && toggle()"
    >
      <slot name="header">
        <button
          type="button"
          class="collapse-toggle"
          :disabled="disabled"
          :aria-expanded="isOpen"
          :aria-controls="collapseId"
        >
          <span>{{ isOpen ? collapseText : expandText }}</span>
          <svg
            class="collapse-icon"
            :class="{ 'rotate-180': isOpen }"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="6 9 12 15 18 9" />
          </svg>
        </button>
      </slot>
    </div>

    <!-- Collapsible Content -->
    <div
      :id="collapseId"
      ref="contentRef"
      class="collapse-content"
      :style="contentStyle"
      @transitionend="onTransitionEnd"
    >
      <div ref="innerRef" class="collapse-inner">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

const props = defineProps({
  // v-model for open state
  modelValue: {
    type: Boolean,
    default: false
  },
  // Duration in ms
  duration: {
    type: Number,
    default: 300
  },
  // Easing function
  easing: {
    type: String,
    default: 'ease-out'
  },
  // Show default toggle button
  showToggle: {
    type: Boolean,
    default: true
  },
  // Expand text
  expandText: {
    type: String,
    default: 'Show more'
  },
  // Collapse text
  collapseText: {
    type: String,
    default: 'Show less'
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Unique ID
  id: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'open', 'close', 'toggle'])

const contentRef = ref(null)
const innerRef = ref(null)
const isOpen = ref(props.modelValue)
const contentHeight = ref(0)
const isAnimating = ref(false)

const collapseId = computed(() => props.id || `collapse-${Math.random().toString(36).slice(2, 9)}`)

const contentStyle = computed(() => ({
  height: isAnimating.value ? `${contentHeight.value}px` : (isOpen.value ? 'auto' : '0px'),
  overflow: 'hidden',
  transition: `height ${props.duration}ms ${props.easing}`
}))

const updateHeight = async () => {
  await nextTick()
  if (innerRef.value) {
    contentHeight.value = innerRef.value.offsetHeight
  }
}

const toggle = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  emit('update:modelValue', isOpen.value)
  emit('toggle', isOpen.value)
}

const open = async () => {
  if (isOpen.value || props.disabled) return
  await updateHeight()
  isAnimating.value = true
  isOpen.value = true
  emit('update:modelValue', true)
  emit('open')
}

const close = async () => {
  if (!isOpen.value || props.disabled) return
  await updateHeight()
  isAnimating.value = true
  // Force reflow
  contentRef.value?.offsetHeight
  contentHeight.value = 0
  isOpen.value = false
  emit('update:modelValue', false)
  emit('close')
}

const onTransitionEnd = () => {
  isAnimating.value = false
  if (isOpen.value) {
    contentHeight.value = innerRef.value?.offsetHeight || 0
  }
}

watch(() => props.modelValue, async (newVal) => {
  if (newVal !== isOpen.value) {
    if (newVal) {
      await open()
    } else {
      await close()
    }
  }
})

watch(isOpen, async (newVal, oldVal) => {
  if (newVal !== oldVal) {
    await updateHeight()
    isAnimating.value = true
    if (!newVal) {
      // Force reflow then collapse
      await nextTick()
      contentHeight.value = 0
    }
  }
})

onMounted(async () => {
  await updateHeight()
  if (!isOpen.value) {
    contentHeight.value = 0
  }
})

defineExpose({
  toggle,
  open,
  close,
  isOpen
})
</script>

<style scoped>
.collapse-wrapper {
  width: 100%;
}

.collapse-header {
  user-select: none;
}

.collapse-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: inherit;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.collapse-toggle:hover:not(:disabled) {
  background-color: rgba(0, 0, 0, 0.05);
}

.collapse-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.collapse-icon {
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.collapse-content {
  will-change: height;
}

.collapse-inner {
  padding: 0.5rem 0;
}
</style>
