<template>
  <div
    ref="containerRef"
    :class="containerClasses"
    :style="containerStyle"
  >
    <!-- First pane -->
    <div
      ref="firstPaneRef"
      :class="paneClasses"
      :style="firstPaneStyle"
    >
      <slot name="first" />
    </div>
    
    <!-- Divider/Resizer -->
    <div
      ref="dividerRef"
      :class="dividerClasses"
      @mousedown="startResize"
      @touchstart.prevent="startResize"
      @dblclick="resetSize"
    >
      <div :class="dividerHandleClasses">
        <div :class="dividerDotsClasses">
          <span v-for="i in 3" :key="i" class="w-1 h-1 rounded-full bg-current" />
        </div>
      </div>
    </div>
    
    <!-- Second pane -->
    <div
      ref="secondPaneRef"
      :class="paneClasses"
      :style="secondPaneStyle"
    >
      <slot name="second" />
    </div>
    
    <!-- Resize overlay -->
    <div
      v-if="isResizing"
      class="fixed inset-0 z-50"
      :class="orientation === 'horizontal' ? 'cursor-col-resize' : 'cursor-row-resize'"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  // Orientation
  orientation: {
    type: String,
    default: 'horizontal',
    validator: (v) => ['horizontal', 'vertical'].includes(v)
  },
  
  // Initial split (percentage for first pane)
  initialSize: {
    type: Number,
    default: 50
  },
  
  // Min/max sizes (percentage)
  minSize: {
    type: Number,
    default: 10
  },
  maxSize: {
    type: Number,
    default: 90
  },
  
  // Divider width/height
  dividerSize: {
    type: Number,
    default: 8
  },
  
  // Show divider
  showDivider: {
    type: Boolean,
    default: true
  },
  
  // Allow collapse
  collapsible: {
    type: Boolean,
    default: false
  },
  collapseThreshold: {
    type: Number,
    default: 5
  },
  
  // Snap points (percentages)
  snapPoints: {
    type: Array,
    default: () => []
  },
  snapThreshold: {
    type: Number,
    default: 3
  },
  
  // Disabled
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:size', 'resize', 'resizeStart', 'resizeEnd', 'collapse'])

const containerRef = ref(null)
const firstPaneRef = ref(null)
const secondPaneRef = ref(null)
const dividerRef = ref(null)

const currentSize = ref(props.initialSize)
const isResizing = ref(false)
const isCollapsed = ref(null) // 'first', 'second', or null

// Container classes
const containerClasses = computed(() => [
  'flex w-full h-full',
  props.orientation === 'horizontal' ? 'flex-row' : 'flex-col',
  'overflow-hidden'
])

// Container style
const containerStyle = computed(() => ({
  '--divider-size': `${props.dividerSize}px`
}))

// Pane classes
const paneClasses = computed(() => [
  'overflow-auto',
  'transition-none'
])

// First pane style
const firstPaneStyle = computed(() => {
  if (isCollapsed.value === 'first') {
    return { flex: '0 0 0' }
  }
  if (isCollapsed.value === 'second') {
    return { flex: '1 1 auto' }
  }
  
  const size = `calc(${currentSize.value}% - var(--divider-size) / 2)`
  return {
    flex: `0 0 ${size}`,
    [props.orientation === 'horizontal' ? 'width' : 'height']: size
  }
})

// Second pane style
const secondPaneStyle = computed(() => {
  if (isCollapsed.value === 'second') {
    return { flex: '0 0 0' }
  }
  if (isCollapsed.value === 'first') {
    return { flex: '1 1 auto' }
  }
  
  const size = `calc(${100 - currentSize.value}% - var(--divider-size) / 2)`
  return {
    flex: `0 0 ${size}`,
    [props.orientation === 'horizontal' ? 'width' : 'height']: size
  }
})

// Divider classes
const dividerClasses = computed(() => [
  'flex-shrink-0 flex items-center justify-center',
  'bg-gray-200 dark:bg-gray-700',
  'transition-colors duration-200',
  !props.disabled && 'hover:bg-gray-300 dark:hover:bg-gray-600',
  !props.disabled && (props.orientation === 'horizontal' ? 'cursor-col-resize' : 'cursor-row-resize'),
  props.orientation === 'horizontal' ? 'w-[var(--divider-size)]' : 'h-[var(--divider-size)]',
  isResizing.value && 'bg-primary-400 dark:bg-primary-600',
  props.disabled && 'cursor-not-allowed opacity-50',
  !props.showDivider && 'bg-transparent hover:bg-gray-200 dark:hover:bg-gray-600'
])

// Divider handle classes
const dividerHandleClasses = computed(() => [
  'flex items-center justify-center',
  props.orientation === 'horizontal' ? 'h-12 w-full' : 'w-12 h-full'
])

// Divider dots classes
const dividerDotsClasses = computed(() => [
  'flex gap-0.5',
  props.orientation === 'horizontal' ? 'flex-col' : 'flex-row',
  'text-gray-400 dark:text-gray-500'
])

// Start resize
function startResize(e) {
  if (props.disabled) return
  
  isResizing.value = true
  isCollapsed.value = null
  
  emit('resizeStart', currentSize.value)
  
  document.addEventListener('mousemove', handleResize)
  document.addEventListener('mouseup', stopResize)
  document.addEventListener('touchmove', handleResize)
  document.addEventListener('touchend', stopResize)
}

// Handle resize
function handleResize(e) {
  if (!isResizing.value || !containerRef.value) return
  
  const rect = containerRef.value.getBoundingClientRect()
  const clientX = e.touches ? e.touches[0].clientX : e.clientX
  const clientY = e.touches ? e.touches[0].clientY : e.clientY
  
  let percentage
  if (props.orientation === 'horizontal') {
    percentage = ((clientX - rect.left) / rect.width) * 100
  } else {
    percentage = ((clientY - rect.top) / rect.height) * 100
  }
  
  // Apply snap points
  if (props.snapPoints.length > 0) {
    for (const snapPoint of props.snapPoints) {
      if (Math.abs(percentage - snapPoint) < props.snapThreshold) {
        percentage = snapPoint
        break
      }
    }
  }
  
  // Apply collapse
  if (props.collapsible) {
    if (percentage < props.collapseThreshold) {
      isCollapsed.value = 'first'
      emit('collapse', 'first')
      return
    }
    if (percentage > 100 - props.collapseThreshold) {
      isCollapsed.value = 'second'
      emit('collapse', 'second')
      return
    }
  }
  
  // Apply min/max
  percentage = Math.max(props.minSize, Math.min(props.maxSize, percentage))
  
  currentSize.value = percentage
  emit('resize', percentage)
}

// Stop resize
function stopResize() {
  isResizing.value = false
  
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
  document.removeEventListener('touchmove', handleResize)
  document.removeEventListener('touchend', stopResize)
  
  emit('update:size', currentSize.value)
  emit('resizeEnd', currentSize.value)
}

// Reset size on double click
function resetSize() {
  if (props.disabled) return
  
  isCollapsed.value = null
  currentSize.value = props.initialSize
  emit('update:size', currentSize.value)
  emit('resize', currentSize.value)
}

// Programmatic resize
function setSize(size) {
  currentSize.value = Math.max(props.minSize, Math.min(props.maxSize, size))
  emit('update:size', currentSize.value)
}

// Collapse panes
function collapse(pane) {
  if (!props.collapsible) return
  isCollapsed.value = pane
  emit('collapse', pane)
}

function expand() {
  isCollapsed.value = null
}

// Watch for external size changes
watch(() => props.initialSize, (val) => {
  if (!isResizing.value) {
    currentSize.value = val
  }
})

// Cleanup
onUnmounted(() => {
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
  document.removeEventListener('touchmove', handleResize)
  document.removeEventListener('touchend', stopResize)
})

// Expose
defineExpose({
  setSize,
  resetSize,
  collapse,
  expand,
  currentSize,
  isCollapsed
})
</script>
