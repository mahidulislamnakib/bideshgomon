<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        :class="backdropClasses"
        @click="handleBackdropClick"
      />
    </Transition>
    
    <!-- Sheet -->
    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-y-full"
      enter-to-class="translate-y-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-y-0"
      leave-to-class="translate-y-full"
    >
      <div
        v-if="modelValue"
        ref="sheetRef"
        :class="sheetClasses"
        :style="sheetStyle"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
      >
        <!-- Drag handle -->
        <div v-if="showHandle" :class="handleContainerClasses">
          <div :class="handleClasses" />
        </div>
        
        <!-- Header -->
        <div v-if="$slots.header || title" :class="headerClasses">
          <slot name="header">
            <h3 :class="titleClasses">{{ title }}</h3>
            <button
              v-if="showClose"
              type="button"
              :class="closeButtonClasses"
              @click="close"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>
          </slot>
        </div>
        
        <!-- Content -->
        <div :class="contentClasses">
          <slot />
        </div>
        
        <!-- Footer -->
        <div v-if="$slots.footer" :class="footerClasses">
          <slot name="footer" />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  
  // Title
  title: {
    type: String,
    default: ''
  },
  
  // Height
  height: {
    type: String,
    default: 'auto',
    validator: (v) => ['auto', 'full', 'half', 'quarter'].includes(v) || v.endsWith('px') || v.endsWith('%')
  },
  
  // Max height (for auto)
  maxHeight: {
    type: String,
    default: '90vh'
  },
  
  // Features
  showHandle: {
    type: Boolean,
    default: true
  },
  showClose: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  
  // Swipe to dismiss
  swipeToDismiss: {
    type: Boolean,
    default: true
  },
  swipeThreshold: {
    type: Number,
    default: 100
  },
  
  // Prevent body scroll
  lockScroll: {
    type: Boolean,
    default: true
  },
  
  // Rounded corners
  rounded: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'open', 'close'])

const sheetRef = ref(null)
const translateY = ref(0)
const isDragging = ref(false)
const startY = ref(0)

// Height classes/style
const heightValue = computed(() => {
  switch (props.height) {
    case 'full': return '100vh'
    case 'half': return '50vh'
    case 'quarter': return '25vh'
    case 'auto': return 'auto'
    default: return props.height
  }
})

// Backdrop classes
const backdropClasses = computed(() => [
  'fixed inset-0 z-40',
  'bg-black/50 backdrop-blur-sm'
])

// Sheet classes
const sheetClasses = computed(() => [
  'fixed bottom-0 left-0 right-0 z-50',
  'bg-white dark:bg-gray-800',
  'shadow-2xl',
  'flex flex-col',
  'safe-area-inset-bottom',
  props.rounded && 'rounded-t-2xl',
  isDragging.value ? 'transition-none' : 'transition-transform duration-200'
])

// Sheet style
const sheetStyle = computed(() => ({
  height: heightValue.value,
  maxHeight: props.maxHeight,
  transform: `translateY(${translateY.value}px)`
}))

// Handle container classes
const handleContainerClasses = computed(() => [
  'flex justify-center py-3',
  'cursor-grab active:cursor-grabbing'
])

// Handle classes
const handleClasses = computed(() => [
  'w-12 h-1.5 rounded-full',
  'bg-gray-300 dark:bg-gray-600'
])

// Header classes
const headerClasses = computed(() => [
  'flex items-center justify-between',
  'px-4 py-3',
  'border-b border-gray-200 dark:border-gray-700'
])

// Title classes
const titleClasses = computed(() => [
  'text-lg font-semibold',
  'text-gray-900 dark:text-white'
])

// Close button classes
const closeButtonClasses = computed(() => [
  'p-1 rounded-full',
  'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
  'hover:bg-gray-100 dark:hover:bg-gray-700',
  'transition-colors'
])

// Content classes
const contentClasses = computed(() => [
  'flex-1 overflow-y-auto',
  'px-4 py-4'
])

// Footer classes
const footerClasses = computed(() => [
  'px-4 py-3',
  'border-t border-gray-200 dark:border-gray-700',
  'bg-gray-50 dark:bg-gray-800/50'
])

// Close sheet
function close() {
  emit('update:modelValue', false)
  emit('close')
}

// Handle backdrop click
function handleBackdropClick() {
  if (props.closeOnBackdrop) {
    close()
  }
}

// Handle escape key
function handleEscape(e) {
  if (e.key === 'Escape' && props.closeOnEscape && props.modelValue) {
    close()
  }
}

// Touch handlers for swipe to dismiss
function handleTouchStart(e) {
  if (!props.swipeToDismiss) return
  
  isDragging.value = true
  startY.value = e.touches[0].clientY
}

function handleTouchMove(e) {
  if (!isDragging.value || !props.swipeToDismiss) return
  
  const currentY = e.touches[0].clientY
  const diff = currentY - startY.value
  
  // Only allow dragging down
  if (diff > 0) {
    translateY.value = diff
  }
}

function handleTouchEnd() {
  if (!isDragging.value) return
  
  isDragging.value = false
  
  if (translateY.value > props.swipeThreshold) {
    close()
  }
  
  translateY.value = 0
}

// Lock body scroll
function lockBodyScroll() {
  if (props.lockScroll) {
    document.body.style.overflow = 'hidden'
  }
}

function unlockBodyScroll() {
  document.body.style.overflow = ''
}

// Watch for open/close
watch(() => props.modelValue, (val) => {
  if (val) {
    lockBodyScroll()
    emit('open')
  } else {
    unlockBodyScroll()
    translateY.value = 0
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  if (props.modelValue) {
    lockBodyScroll()
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  unlockBodyScroll()
})

// Expose
defineExpose({
  close,
  open: () => emit('update:modelValue', true)
})
</script>

<style scoped>
.safe-area-inset-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
