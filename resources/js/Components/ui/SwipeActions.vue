<template>
  <div
    ref="containerRef"
    :class="containerClasses"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
  >
    <!-- Left actions -->
    <div
      v-if="leftActions.length > 0"
      ref="leftActionsRef"
      :class="leftActionsClasses"
      :style="leftActionsStyle"
    >
      <button
        v-for="action in leftActions"
        :key="action.id || action.label"
        type="button"
        :class="actionButtonClasses(action, 'left')"
        :style="actionButtonStyle(action)"
        @click="handleAction(action)"
      >
        <component v-if="action.icon" :is="action.icon" class="w-5 h-5" />
        <span v-if="action.label" class="text-xs font-medium">{{ action.label }}</span>
      </button>
    </div>
    
    <!-- Content -->
    <div
      ref="contentRef"
      :class="contentClasses"
      :style="contentStyle"
    >
      <slot />
    </div>
    
    <!-- Right actions -->
    <div
      v-if="rightActions.length > 0"
      ref="rightActionsRef"
      :class="rightActionsClasses"
      :style="rightActionsStyle"
    >
      <button
        v-for="action in rightActions"
        :key="action.id || action.label"
        type="button"
        :class="actionButtonClasses(action, 'right')"
        :style="actionButtonStyle(action)"
        @click="handleAction(action)"
      >
        <component v-if="action.icon" :is="action.icon" class="w-5 h-5" />
        <span v-if="action.label" class="text-xs font-medium">{{ action.label }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  // Left swipe actions (revealed when swiping right)
  leftActions: {
    type: Array,
    default: () => []
    // { id?, label, icon?, color?, bgColor?, action? }
  },
  
  // Right swipe actions (revealed when swiping left)
  rightActions: {
    type: Array,
    default: () => []
    // { id?, label, icon?, color?, bgColor?, action? }
  },
  
  // Action width
  actionWidth: {
    type: Number,
    default: 80
  },
  
  // Threshold for action trigger (percentage of action width)
  threshold: {
    type: Number,
    default: 0.3
  },
  
  // Auto close after action
  autoClose: {
    type: Boolean,
    default: true
  },
  
  // Disable swipe
  disabled: {
    type: Boolean,
    default: false
  },
  
  // Allow overswipe (full width)
  allowOverswipe: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['action', 'open', 'close'])

const containerRef = ref(null)
const contentRef = ref(null)
const leftActionsRef = ref(null)
const rightActionsRef = ref(null)

const translateX = ref(0)
const startX = ref(0)
const currentX = ref(0)
const isDragging = ref(false)
const isOpen = ref(null) // 'left', 'right', or null

// Max translate values
const maxLeftTranslate = computed(() => 
  props.leftActions.length * props.actionWidth
)
const maxRightTranslate = computed(() => 
  props.rightActions.length * props.actionWidth
)

// Container classes
const containerClasses = computed(() => [
  'relative overflow-hidden',
  'touch-pan-y'
])

// Content classes
const contentClasses = computed(() => [
  'relative z-10',
  'bg-white dark:bg-gray-800',
  isDragging.value ? 'transition-none' : 'transition-transform duration-300'
])

// Content style
const contentStyle = computed(() => ({
  transform: `translateX(${translateX.value}px)`
}))

// Left actions classes
const leftActionsClasses = computed(() => [
  'absolute inset-y-0 left-0',
  'flex items-stretch'
])

// Left actions style
const leftActionsStyle = computed(() => ({
  width: `${maxLeftTranslate.value}px`
}))

// Right actions classes
const rightActionsClasses = computed(() => [
  'absolute inset-y-0 right-0',
  'flex items-stretch flex-row-reverse'
])

// Right actions style
const rightActionsStyle = computed(() => ({
  width: `${maxRightTranslate.value}px`
}))

// Action button classes
function actionButtonClasses(action, side) {
  return [
    'flex flex-col items-center justify-center gap-1',
    'px-4 min-w-[80px]',
    'text-white',
    'transition-all duration-200',
    action.bgColor ? '' : (side === 'left' ? 'bg-green-500' : 'bg-red-500')
  ]
}

// Action button style
function actionButtonStyle(action) {
  return {
    width: `${props.actionWidth}px`,
    backgroundColor: action.bgColor,
    color: action.color
  }
}

// Touch handlers
function handleTouchStart(e) {
  if (props.disabled) return
  
  isDragging.value = true
  startX.value = e.touches[0].clientX
  currentX.value = startX.value
}

function handleTouchMove(e) {
  if (!isDragging.value || props.disabled) return
  
  currentX.value = e.touches[0].clientX
  const diff = currentX.value - startX.value
  
  // Calculate new translate value
  let newTranslate = translateX.value + diff
  
  // Already open, adjust from current position
  if (isOpen.value === 'left') {
    newTranslate = maxLeftTranslate.value + diff
  } else if (isOpen.value === 'right') {
    newTranslate = -maxRightTranslate.value + diff
  } else {
    newTranslate = diff
  }
  
  // Apply limits
  if (props.leftActions.length === 0 && newTranslate > 0) {
    newTranslate = 0
  }
  if (props.rightActions.length === 0 && newTranslate < 0) {
    newTranslate = 0
  }
  
  // Apply overswipe limits if not allowed
  if (!props.allowOverswipe) {
    if (newTranslate > maxLeftTranslate.value) {
      newTranslate = maxLeftTranslate.value + (newTranslate - maxLeftTranslate.value) * 0.2
    }
    if (newTranslate < -maxRightTranslate.value) {
      newTranslate = -maxRightTranslate.value + (newTranslate + maxRightTranslate.value) * 0.2
    }
  }
  
  translateX.value = newTranslate
  startX.value = currentX.value
}

function handleTouchEnd() {
  if (!isDragging.value) return
  
  isDragging.value = false
  
  // Determine final position
  if (translateX.value > maxLeftTranslate.value * props.threshold) {
    // Open left actions
    openLeft()
  } else if (translateX.value < -maxRightTranslate.value * props.threshold) {
    // Open right actions
    openRight()
  } else {
    // Close
    close()
  }
}

// Open/close methods
function openLeft() {
  if (props.leftActions.length === 0) return
  translateX.value = maxLeftTranslate.value
  isOpen.value = 'left'
  emit('open', 'left')
}

function openRight() {
  if (props.rightActions.length === 0) return
  translateX.value = -maxRightTranslate.value
  isOpen.value = 'right'
  emit('open', 'right')
}

function close() {
  translateX.value = 0
  isOpen.value = null
  emit('close')
}

// Handle action click
function handleAction(action) {
  emit('action', action)
  
  if (action.action && typeof action.action === 'function') {
    action.action()
  }
  
  if (props.autoClose) {
    close()
  }
}

// Expose
defineExpose({
  openLeft,
  openRight,
  close,
  isOpen
})
</script>
