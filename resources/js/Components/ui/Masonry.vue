<template>
  <div ref="containerRef" class="relative" :style="containerStyle">
    <div
      v-for="(item, index) in positionedItems"
      :key="item.id || index"
      class="absolute transition-all duration-300"
      :style="item.style"
    >
      <slot :item="item.data" :index="index">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <img
            v-if="item.data.image"
            :src="item.data.image"
            :alt="item.data.title || `Item ${index + 1}`"
            class="w-full object-cover"
            :style="{ height: `${item.data.height || 200}px` }"
          />
          <div v-if="item.data.title || item.data.description" class="p-4">
            <h3 v-if="item.data.title" class="font-medium text-gray-900">
              {{ item.data.title }}
            </h3>
            <p v-if="item.data.description" class="text-sm text-gray-600 mt-1">
              {{ item.data.description }}
            </p>
          </div>
        </div>
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
    // [{ id?, image?, title?, description?, height? }]
  },
  columns: {
    type: Number,
    default: 0 // 0 = auto based on container width
  },
  columnWidth: {
    type: Number,
    default: 300
  },
  gap: {
    type: Number,
    default: 16
  },
  responsive: {
    type: Object,
    default: () => ({
      // breakpoint: columns
      640: 1,
      768: 2,
      1024: 3,
      1280: 4
    })
  }
})

const emit = defineEmits(['layout-complete'])

// Refs
const containerRef = ref(null)
const containerWidth = ref(0)
const columnHeights = ref([])
const itemHeights = ref([])

// Calculate number of columns
const columnCount = computed(() => {
  if (props.columns > 0) return props.columns
  
  // Auto-calculate based on responsive breakpoints
  const width = containerWidth.value
  let cols = 1
  
  const breakpoints = Object.keys(props.responsive)
    .map(Number)
    .sort((a, b) => a - b)
  
  for (const breakpoint of breakpoints) {
    if (width >= breakpoint) {
      cols = props.responsive[breakpoint]
    }
  }
  
  return cols
})

// Calculate actual column width
const actualColumnWidth = computed(() => {
  const totalGap = props.gap * (columnCount.value - 1)
  return (containerWidth.value - totalGap) / columnCount.value
})

// Position items
const positionedItems = computed(() => {
  const cols = columnCount.value
  const colHeights = new Array(cols).fill(0)
  
  return props.items.map((item, index) => {
    // Find shortest column
    const minHeight = Math.min(...colHeights)
    const colIndex = colHeights.indexOf(minHeight)
    
    // Calculate position
    const x = colIndex * (actualColumnWidth.value + props.gap)
    const y = colHeights[colIndex]
    
    // Estimate item height (use provided height or default)
    const itemHeight = itemHeights.value[index] || item.height || 200
    
    // Update column height
    colHeights[colIndex] += itemHeight + props.gap
    
    return {
      data: item,
      style: {
        width: `${actualColumnWidth.value}px`,
        transform: `translate(${x}px, ${y}px)`
      }
    }
  })
})

// Container style
const containerStyle = computed(() => {
  const cols = columnCount.value
  const colHeights = new Array(cols).fill(0)
  
  props.items.forEach((item, index) => {
    const minHeight = Math.min(...colHeights)
    const colIndex = colHeights.indexOf(minHeight)
    const itemHeight = itemHeights.value[index] || item.height || 200
    colHeights[colIndex] += itemHeight + props.gap
  })
  
  const maxHeight = Math.max(...colHeights, 0)
  
  return {
    height: `${maxHeight}px`,
    width: '100%'
  }
})

// Measure container
function measureContainer() {
  if (containerRef.value) {
    containerWidth.value = containerRef.value.offsetWidth
  }
}

// Measure item heights (after render)
async function measureItems() {
  await nextTick()
  
  if (!containerRef.value) return
  
  const itemElements = containerRef.value.querySelectorAll(':scope > div')
  itemHeights.value = Array.from(itemElements).map(el => el.offsetHeight)
  
  emit('layout-complete')
}

// Resize observer
let resizeObserver = null

onMounted(() => {
  measureContainer()
  
  resizeObserver = new ResizeObserver(() => {
    measureContainer()
    measureItems()
  })
  
  if (containerRef.value) {
    resizeObserver.observe(containerRef.value)
  }
  
  // Initial measurement after images load
  setTimeout(measureItems, 100)
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})

// Re-measure when items change
watch(() => props.items, () => {
  nextTick(() => {
    measureItems()
  })
}, { deep: true })

// Expose methods
defineExpose({
  refresh: measureItems
})
</script>
