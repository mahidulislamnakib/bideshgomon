<template>
  <div
    ref="containerRef"
    class="overflow-auto"
    :style="containerStyle"
    @scroll="handleScroll"
  >
    <!-- Spacer for scroll height -->
    <div :style="{ height: `${totalHeight}px`, position: 'relative' }">
      <!-- Visible items -->
      <div
        v-for="item in visibleItems"
        :key="item.key"
        :style="item.style"
        class="absolute left-0 right-0"
      >
        <slot :item="item.data" :index="item.index">
          <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
            {{ item.data }}
          </div>
        </slot>
      </div>
    </div>

    <!-- Loading indicator -->
    <div
      v-if="loading"
      class="sticky bottom-0 left-0 right-0 flex justify-center py-4 bg-white/80 backdrop-blur-sm"
    >
      <slot name="loading">
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          Loading more...
        </div>
      </slot>
    </div>

    <!-- Empty state -->
    <div
      v-if="items.length === 0 && !loading"
      class="flex items-center justify-center py-12 text-gray-500"
    >
      <slot name="empty">
        <p class="text-sm">No items to display</p>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  itemHeight: {
    type: [Number, Function],
    default: 50
  },
  height: {
    type: [Number, String],
    default: 400
  },
  buffer: {
    type: Number,
    default: 5 // Extra items to render above/below visible area
  },
  keyField: {
    type: String,
    default: 'id'
  },
  loading: {
    type: Boolean,
    default: false
  },
  threshold: {
    type: Number,
    default: 100 // Pixels from bottom to trigger load more
  }
})

const emit = defineEmits(['scroll', 'load-more', 'visible-change'])

// Refs
const containerRef = ref(null)
const scrollTop = ref(0)
const containerHeight = ref(0)

// Get item height (supports fixed or dynamic)
function getItemHeight(index) {
  if (typeof props.itemHeight === 'function') {
    return props.itemHeight(props.items[index], index)
  }
  return props.itemHeight
}

// Calculate item positions
const itemPositions = computed(() => {
  const positions = []
  let top = 0
  
  for (let i = 0; i < props.items.length; i++) {
    const height = getItemHeight(i)
    positions.push({ top, height })
    top += height
  }
  
  return positions
})

// Total height of all items
const totalHeight = computed(() => {
  if (props.items.length === 0) return 0
  const lastItem = itemPositions.value[itemPositions.value.length - 1]
  return lastItem.top + lastItem.height
})

// Find visible range
const visibleRange = computed(() => {
  if (props.items.length === 0) {
    return { start: 0, end: 0 }
  }
  
  const positions = itemPositions.value
  let start = 0
  let end = props.items.length
  
  // Binary search for start
  let low = 0
  let high = positions.length - 1
  
  while (low <= high) {
    const mid = Math.floor((low + high) / 2)
    if (positions[mid].top + positions[mid].height < scrollTop.value) {
      low = mid + 1
    } else {
      high = mid - 1
    }
  }
  start = Math.max(0, low - props.buffer)
  
  // Find end
  const viewBottom = scrollTop.value + containerHeight.value
  low = start
  high = positions.length - 1
  
  while (low <= high) {
    const mid = Math.floor((low + high) / 2)
    if (positions[mid].top < viewBottom) {
      low = mid + 1
    } else {
      high = mid - 1
    }
  }
  end = Math.min(props.items.length, low + props.buffer)
  
  return { start, end }
})

// Visible items with positioning
const visibleItems = computed(() => {
  const { start, end } = visibleRange.value
  const items = []
  
  for (let i = start; i < end; i++) {
    const position = itemPositions.value[i]
    const item = props.items[i]
    
    items.push({
      index: i,
      key: item[props.keyField] ?? i,
      data: item,
      style: {
        top: `${position.top}px`,
        height: `${position.height}px`
      }
    })
  }
  
  return items
})

// Container style
const containerStyle = computed(() => {
  const height = typeof props.height === 'number' 
    ? `${props.height}px` 
    : props.height
  
  return {
    height,
    position: 'relative'
  }
})

// Handle scroll
function handleScroll(event) {
  scrollTop.value = event.target.scrollTop
  
  emit('scroll', {
    scrollTop: scrollTop.value,
    scrollHeight: event.target.scrollHeight,
    clientHeight: event.target.clientHeight
  })
  
  // Check for load more
  const { scrollTop: top, scrollHeight, clientHeight } = event.target
  const distanceFromBottom = scrollHeight - top - clientHeight
  
  if (distanceFromBottom < props.threshold && !props.loading) {
    emit('load-more')
  }
}

// Update container height
function updateContainerHeight() {
  if (containerRef.value) {
    containerHeight.value = containerRef.value.clientHeight
  }
}

// Scroll to index
function scrollToIndex(index, behavior = 'smooth') {
  if (!containerRef.value || index < 0 || index >= props.items.length) return
  
  const position = itemPositions.value[index]
  containerRef.value.scrollTo({
    top: position.top,
    behavior
  })
}

// Scroll to top
function scrollToTop(behavior = 'smooth') {
  containerRef.value?.scrollTo({ top: 0, behavior })
}

// Scroll to bottom
function scrollToBottom(behavior = 'smooth') {
  if (!containerRef.value) return
  containerRef.value.scrollTo({ 
    top: totalHeight.value, 
    behavior 
  })
}

// Watch visible range changes
watch(visibleRange, (newRange, oldRange) => {
  if (newRange.start !== oldRange?.start || newRange.end !== oldRange?.end) {
    emit('visible-change', {
      start: newRange.start,
      end: newRange.end,
      items: props.items.slice(newRange.start, newRange.end)
    })
  }
})

// Resize observer
let resizeObserver = null

onMounted(() => {
  updateContainerHeight()
  
  resizeObserver = new ResizeObserver(updateContainerHeight)
  if (containerRef.value) {
    resizeObserver.observe(containerRef.value)
  }
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})

// Expose methods
defineExpose({
  scrollToIndex,
  scrollToTop,
  scrollToBottom,
  getVisibleRange: () => visibleRange.value
})
</script>
