<template>
  <div class="treemap-container" ref="containerRef">
    <!-- Header -->
    <div v-if="title || showLegend" class="flex items-center justify-between mb-4">
      <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
      <div v-if="showLegend && legendItems.length > 0" class="flex flex-wrap gap-3">
        <div
          v-for="item in legendItems"
          :key="item.name"
          class="flex items-center gap-2 text-sm"
        >
          <span
            class="w-3 h-3 rounded"
            :style="{ backgroundColor: item.color }"
          />
          <span class="text-gray-600 dark:text-gray-400">{{ item.name }}</span>
        </div>
      </div>
    </div>

    <!-- TreeMap -->
    <div
      class="treemap relative"
      :style="{ height: height }"
      @mouseleave="hoveredNode = null"
    >
      <transition-group name="treemap" tag="div" class="absolute inset-0">
        <div
          v-for="node in computedNodes"
          :key="node.id"
          class="treemap-node absolute overflow-hidden transition-all duration-300 ease-out"
          :class="[
            hoveredNode === node.id ? 'ring-2 ring-white ring-offset-1 z-10' : '',
            clickable ? 'cursor-pointer' : ''
          ]"
          :style="getNodeStyle(node)"
          @mouseenter="hoveredNode = node.id"
          @mouseleave="hoveredNode = null"
          @click="handleNodeClick(node)"
        >
          <!-- Content -->
          <div
            class="absolute inset-1 flex flex-col justify-between p-2 overflow-hidden"
            :class="getTextColorClass(node.color)"
          >
            <div v-if="node.width > 60 && node.height > 40">
              <div class="font-medium text-sm truncate" :title="node.name">
                {{ node.name }}
              </div>
              <div v-if="showValues && node.height > 60" class="text-xs opacity-80 mt-1">
                {{ formatValue(node.value) }}
              </div>
            </div>
            
            <!-- Percentage Badge -->
            <div
              v-if="showPercentage && node.width > 50 && node.height > 50"
              class="text-xs font-semibold opacity-90"
            >
              {{ node.percentage.toFixed(1) }}%
            </div>
          </div>

          <!-- Gradient Overlay for depth effect -->
          <div
            class="absolute inset-0 bg-gradient-to-br from-white/10 to-black/10 pointer-events-none"
          />
        </div>
      </transition-group>

      <!-- Tooltip -->
      <Transition name="tooltip">
        <div
          v-if="hoveredNode && showTooltip"
          class="absolute z-20 px-3 py-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg pointer-events-none"
          :style="tooltipStyle"
        >
          <div class="font-medium">{{ getHoveredNodeData?.name }}</div>
          <div class="text-gray-300">
            <slot name="tooltip" :node="getHoveredNodeData">
              {{ formatValue(getHoveredNodeData?.value) }} ({{ getHoveredNodeData?.percentage.toFixed(1) }}%)
            </slot>
          </div>
        </div>
      </Transition>

      <!-- Empty State -->
      <div
        v-if="!data || data.length === 0"
        class="absolute inset-0 flex items-center justify-center text-gray-400 dark:text-gray-500"
      >
        <div class="text-center">
          <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM14 5a1 1 0 011-1h4a1 1 0 011 1v14a1 1 0 01-1 1h-4a1 1 0 01-1-1V5z" />
          </svg>
          <p class="text-sm">No data available</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  data: { type: Array, default: () => [] },
  valueKey: { type: String, default: 'value' },
  nameKey: { type: String, default: 'name' },
  colorKey: { type: String, default: 'color' },
  height: { type: String, default: '400px' },
  title: { type: String, default: '' },
  colors: {
    type: Array,
    default: () => [
      '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#6366F1',
      '#EC4899', '#8B5CF6', '#14B8A6', '#F97316', '#06B6D4'
    ]
  },
  showValues: { type: Boolean, default: true },
  showPercentage: { type: Boolean, default: true },
  showTooltip: { type: Boolean, default: true },
  showLegend: { type: Boolean, default: false },
  clickable: { type: Boolean, default: false },
  padding: { type: Number, default: 2 },
  borderRadius: { type: Number, default: 4 },
  valueFormatter: { type: Function, default: null }
})

const emit = defineEmits(['nodeClick'])

const containerRef = ref(null)
const containerWidth = ref(0)
const containerHeight = ref(0)
const hoveredNode = ref(null)
const mouseX = ref(0)
const mouseY = ref(0)

// Calculate total value
const totalValue = computed(() => {
  return props.data.reduce((sum, item) => sum + (item[props.valueKey] || 0), 0)
})

// Calculate treemap layout using squarified algorithm
const computedNodes = computed(() => {
  if (!props.data?.length || !containerWidth.value || !containerHeight.value) {
    return []
  }

  const width = containerWidth.value
  const height = containerHeight.value
  
  // Sort data by value descending
  const sortedData = [...props.data]
    .map((item, index) => ({
      ...item,
      id: item.id || index,
      name: item[props.nameKey] || `Item ${index + 1}`,
      value: item[props.valueKey] || 0,
      color: item[props.colorKey] || props.colors[index % props.colors.length]
    }))
    .filter(item => item.value > 0)
    .sort((a, b) => b.value - a.value)

  if (sortedData.length === 0) return []

  // Calculate percentages
  sortedData.forEach(item => {
    item.percentage = (item.value / totalValue.value) * 100
  })

  // Squarified treemap algorithm
  const nodes = squarify(sortedData, { x: 0, y: 0, width, height })
  
  return nodes
})

// Squarified treemap algorithm
function squarify(data, rect) {
  if (data.length === 0) return []
  if (data.length === 1) {
    return [{
      ...data[0],
      x: rect.x,
      y: rect.y,
      width: rect.width,
      height: rect.height
    }]
  }

  const totalValue = data.reduce((sum, d) => sum + d.value, 0)
  const results = []
  
  layoutRow(data, rect, totalValue, results)
  
  return results
}

function layoutRow(data, rect, totalValue, results) {
  if (data.length === 0) return
  
  const { x, y, width, height } = rect
  const horizontal = width >= height
  const mainSize = horizontal ? width : height
  const crossSize = horizontal ? height : width
  
  let row = []
  let rowSum = 0
  let remaining = [...data]
  
  while (remaining.length > 0) {
    const item = remaining[0]
    const testRow = [...row, item]
    const testSum = rowSum + item.value
    
    if (row.length === 0 || worst(testRow, testSum, mainSize * (testSum / totalValue), crossSize) <= worst(row, rowSum, mainSize * (rowSum / totalValue), crossSize)) {
      row.push(item)
      rowSum = testSum
      remaining.shift()
    } else {
      // Layout current row
      const rowWidth = mainSize * (rowSum / totalValue)
      let offset = 0
      
      for (const rowItem of row) {
        const itemSize = (rowItem.value / rowSum) * crossSize
        
        if (horizontal) {
          results.push({
            ...rowItem,
            x: x + offset,
            y,
            width: rowWidth,
            height: itemSize
          })
        } else {
          results.push({
            ...rowItem,
            x,
            y: y + offset,
            width: itemSize,
            height: rowWidth
          })
        }
        
        offset += itemSize
      }
      
      // Update rectangle for remaining items
      const newRect = horizontal
        ? { x: x + rowWidth, y, width: width - rowWidth, height }
        : { x, y: y + rowWidth, width, height: height - rowWidth }
      
      layoutRow(remaining, newRect, totalValue - rowSum, results)
      return
    }
  }
  
  // Layout final row
  if (row.length > 0) {
    const rowWidth = mainSize * (rowSum / totalValue)
    let offset = 0
    
    for (const rowItem of row) {
      const itemSize = (rowItem.value / rowSum) * crossSize
      
      if (horizontal) {
        results.push({
          ...rowItem,
          x: x + offset,
          y,
          width: rowWidth,
          height: itemSize
        })
      } else {
        results.push({
          ...rowItem,
          x,
          y: y + offset,
          width: itemSize,
          height: rowWidth
        })
      }
      
      offset += itemSize
    }
  }
}

function worst(row, rowSum, rowWidth, crossSize) {
  if (rowWidth === 0 || rowSum === 0) return Infinity
  
  let worstRatio = 0
  for (const item of row) {
    const itemHeight = (item.value / rowSum) * crossSize
    const ratio = Math.max(rowWidth / itemHeight, itemHeight / rowWidth)
    worstRatio = Math.max(worstRatio, ratio)
  }
  return worstRatio
}

const getNodeStyle = (node) => ({
  left: `${node.x + props.padding}px`,
  top: `${node.y + props.padding}px`,
  width: `${Math.max(0, node.width - props.padding * 2)}px`,
  height: `${Math.max(0, node.height - props.padding * 2)}px`,
  backgroundColor: node.color,
  borderRadius: `${props.borderRadius}px`
})

const getTextColorClass = (bgColor) => {
  // Simple brightness check
  const hex = bgColor.replace('#', '')
  const r = parseInt(hex.substr(0, 2), 16)
  const g = parseInt(hex.substr(2, 2), 16)
  const b = parseInt(hex.substr(4, 2), 16)
  const brightness = (r * 299 + g * 587 + b * 114) / 1000
  return brightness > 128 ? 'text-gray-900' : 'text-white'
}

const formatValue = (value) => {
  if (props.valueFormatter) return props.valueFormatter(value)
  if (value >= 1000000) return `${(value / 1000000).toFixed(1)}M`
  if (value >= 1000) return `${(value / 1000).toFixed(1)}K`
  return value?.toLocaleString()
}

const getHoveredNodeData = computed(() => {
  return computedNodes.value.find(n => n.id === hoveredNode.value)
})

const tooltipStyle = computed(() => {
  if (!containerRef.value || !hoveredNode.value) return {}
  
  const node = getHoveredNodeData.value
  if (!node) return {}
  
  return {
    left: `${node.x + node.width / 2}px`,
    top: `${Math.max(0, node.y - 10)}px`,
    transform: 'translate(-50%, -100%)'
  }
})

const legendItems = computed(() => {
  return computedNodes.value.map(node => ({
    name: node.name,
    color: node.color
  }))
})

const handleNodeClick = (node) => {
  if (props.clickable) {
    emit('nodeClick', node)
  }
}

const updateDimensions = () => {
  if (containerRef.value) {
    const rect = containerRef.value.querySelector('.treemap')?.getBoundingClientRect()
    if (rect) {
      containerWidth.value = rect.width
      containerHeight.value = rect.height
    }
  }
}

let resizeObserver = null

onMounted(() => {
  updateDimensions()
  
  resizeObserver = new ResizeObserver(updateDimensions)
  if (containerRef.value) {
    const treemapEl = containerRef.value.querySelector('.treemap')
    if (treemapEl) resizeObserver.observe(treemapEl)
  }
})

onBeforeUnmount(() => {
  resizeObserver?.disconnect()
})

watch(() => props.data, updateDimensions, { deep: true })
</script>

<style scoped>
.treemap-container {
  @apply w-full;
}

.treemap {
  @apply bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden;
}

.treemap-node {
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1);
}

.treemap-enter-active,
.treemap-leave-active {
  transition: all 0.3s ease;
}

.treemap-enter-from,
.treemap-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.tooltip-enter-active,
.tooltip-leave-active {
  transition: all 0.15s ease;
}

.tooltip-enter-from,
.tooltip-leave-to {
  opacity: 0;
  transform: translate(-50%, -100%) translateY(5px);
}
</style>
