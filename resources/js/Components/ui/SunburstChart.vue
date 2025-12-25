<template>
  <div class="sunburst-chart" ref="containerRef">
    <!-- Header -->
    <div v-if="title" class="mb-4 text-center">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
      <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ subtitle }}</p>
    </div>

    <!-- SVG Chart -->
    <svg
      ref="svgRef"
      :width="size"
      :height="size"
      :viewBox="`0 0 ${size} ${size}`"
      class="sunburst-svg mx-auto"
      @mouseleave="hoveredSegment = null"
    >
      <g :transform="`translate(${size / 2}, ${size / 2})`">
        <!-- Background circle -->
        <circle
          :r="outerRadius"
          fill="none"
          stroke="currentColor"
          stroke-width="1"
          class="text-gray-200 dark:text-gray-700"
        />

        <!-- Segments -->
        <g
          v-for="segment in computedSegments"
          :key="segment.id"
          class="sunburst-segment transition-opacity duration-200"
          :class="{ 'opacity-50': hoveredSegment && hoveredSegment !== segment.id && !isAncestorOf(hoveredSegment, segment.id) }"
        >
          <path
            :d="segment.path"
            :fill="segment.color"
            stroke="white"
            :stroke-width="segment.depth === 0 ? 2 : 1"
            class="cursor-pointer transition-all duration-200 hover:brightness-110"
            @mouseenter="handleMouseEnter(segment)"
            @mouseleave="handleMouseLeave"
            @click="handleClick(segment)"
          />
          
          <!-- Label (for larger segments) -->
          <text
            v-if="showLabels && segment.labelVisible"
            :transform="segment.labelTransform"
            :text-anchor="segment.labelAnchor"
            dominant-baseline="middle"
            class="text-xs font-medium pointer-events-none select-none"
            :fill="getTextColor(segment.color)"
          >
            {{ truncateLabel(segment.name, segment.arcLength) }}
          </text>
        </g>

        <!-- Center Circle -->
        <circle
          v-if="showCenter"
          :r="innerRadius - 5"
          class="fill-white dark:fill-gray-800 cursor-pointer hover:fill-gray-50 dark:hover:fill-gray-700 transition-colors"
          @click="navigateUp"
        />
        
        <!-- Center Content -->
        <g v-if="showCenter">
          <text
            text-anchor="middle"
            dominant-baseline="middle"
            class="fill-gray-900 dark:fill-gray-100 font-semibold text-sm"
            :y="-8"
          >
            {{ centerTitle }}
          </text>
          <text
            text-anchor="middle"
            dominant-baseline="middle"
            class="fill-gray-500 dark:fill-gray-400 text-xs"
            :y="10"
          >
            {{ centerSubtitle }}
          </text>
        </g>
      </g>
    </svg>

    <!-- Tooltip -->
    <Transition name="tooltip">
      <div
        v-if="hoveredSegment && showTooltip"
        class="absolute z-10 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg pointer-events-none"
        :style="tooltipStyle"
      >
        <div class="font-medium">{{ tooltipData?.name }}</div>
        <div class="text-gray-300">
          <slot name="tooltip" :segment="tooltipData">
            {{ formatValue(tooltipData?.value) }}
            <span v-if="tooltipData?.percentage">({{ tooltipData.percentage.toFixed(1) }}%)</span>
          </slot>
        </div>
        <div v-if="tooltipData?.path?.length > 1" class="text-gray-400 text-xs mt-1">
          {{ tooltipData.path.join(' > ') }}
        </div>
      </div>
    </Transition>

    <!-- Legend -->
    <div v-if="showLegend && legendItems.length > 0" class="mt-4 flex flex-wrap justify-center gap-3">
      <div
        v-for="item in legendItems"
        :key="item.name"
        class="flex items-center gap-2 text-sm cursor-pointer hover:opacity-70 transition-opacity"
        @mouseenter="hoveredSegment = item.id"
        @mouseleave="hoveredSegment = null"
      >
        <span
          class="w-3 h-3 rounded-sm"
          :style="{ backgroundColor: item.color }"
        />
        <span class="text-gray-600 dark:text-gray-400">{{ item.name }}</span>
      </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div v-if="showBreadcrumb && breadcrumb.length > 0" class="mt-4 flex items-center justify-center gap-2 text-sm">
      <button
        @click="navigateToRoot"
        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
      >
        Root
      </button>
      <template v-for="(item, index) in breadcrumb" :key="index">
        <span class="text-gray-400">/</span>
        <button
          @click="navigateTo(item)"
          :class="[
            index === breadcrumb.length - 1
              ? 'text-gray-900 dark:text-gray-100 font-medium'
              : 'text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300'
          ]"
        >
          {{ item.name }}
        </button>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  data: { type: Object, default: () => ({}) },
  size: { type: Number, default: 400 },
  innerRadius: { type: Number, default: 60 },
  levels: { type: Number, default: 3 },
  colors: {
    type: Array,
    default: () => [
      '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#6366F1',
      '#EC4899', '#8B5CF6', '#14B8A6', '#F97316', '#06B6D4',
      '#84CC16', '#22D3EE', '#A855F7', '#FB923C', '#2DD4BF'
    ]
  },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  showLabels: { type: Boolean, default: true },
  showTooltip: { type: Boolean, default: true },
  showLegend: { type: Boolean, default: false },
  showCenter: { type: Boolean, default: true },
  showBreadcrumb: { type: Boolean, default: false },
  clickable: { type: Boolean, default: true },
  valueKey: { type: String, default: 'value' },
  nameKey: { type: String, default: 'name' },
  childrenKey: { type: String, default: 'children' },
  valueFormatter: { type: Function, default: null }
})

const emit = defineEmits(['segmentClick', 'navigate'])

const containerRef = ref(null)
const svgRef = ref(null)
const hoveredSegment = ref(null)
const tooltipPosition = ref({ x: 0, y: 0 })
const currentRoot = ref(null)
const breadcrumb = ref([])

const outerRadius = computed(() => props.size / 2 - 20)
const ringWidth = computed(() => (outerRadius.value - props.innerRadius) / props.levels)

// Flatten hierarchical data into segments
const computedSegments = computed(() => {
  const root = currentRoot.value || props.data
  if (!root || (!root[props.valueKey] && !root[props.childrenKey]?.length)) {
    return []
  }

  const segments = []
  let colorIndex = 0

  const processNode = (node, depth, startAngle, endAngle, parentColor, path = []) => {
    if (depth >= props.levels) return

    const children = node[props.childrenKey] || []
    const totalValue = children.reduce((sum, child) => sum + (child[props.valueKey] || 0), 0)
    
    if (totalValue === 0) return

    let currentAngle = startAngle
    
    children.forEach((child, index) => {
      const value = child[props.valueKey] || 0
      if (value === 0) return

      const angleSize = ((endAngle - startAngle) * value) / totalValue
      const childEndAngle = currentAngle + angleSize
      
      const color = depth === 0 
        ? props.colors[colorIndex++ % props.colors.length]
        : adjustColor(parentColor, depth * 15)

      const innerR = props.innerRadius + depth * ringWidth.value
      const outerR = props.innerRadius + (depth + 1) * ringWidth.value
      
      const segment = {
        id: `${depth}-${index}-${child[props.nameKey]}`,
        name: child[props.nameKey] || `Segment ${index + 1}`,
        value,
        percentage: (value / totalValue) * 100,
        depth,
        color,
        path: [...path, child[props.nameKey] || `Segment ${index + 1}`],
        node: child,
        startAngle: currentAngle,
        endAngle: childEndAngle,
        innerRadius: innerR,
        outerRadius: outerR,
        arcLength: (childEndAngle - currentAngle) * (innerR + outerR) / 2,
        ...calculatePath(currentAngle, childEndAngle, innerR, outerR)
      }
      
      segments.push(segment)
      
      // Process children
      if (child[props.childrenKey]?.length) {
        processNode(child, depth + 1, currentAngle, childEndAngle, color, segment.path)
      }
      
      currentAngle = childEndAngle
    })
  }

  processNode(root, 0, 0, Math.PI * 2, props.colors[0], [])
  
  return segments
})

const calculatePath = (startAngle, endAngle, innerR, outerR) => {
  const startOuter = polarToCartesian(outerR, startAngle)
  const endOuter = polarToCartesian(outerR, endAngle)
  const startInner = polarToCartesian(innerR, startAngle)
  const endInner = polarToCartesian(innerR, endAngle)
  
  const largeArc = endAngle - startAngle > Math.PI ? 1 : 0
  
  const path = [
    `M ${startOuter.x} ${startOuter.y}`,
    `A ${outerR} ${outerR} 0 ${largeArc} 1 ${endOuter.x} ${endOuter.y}`,
    `L ${endInner.x} ${endInner.y}`,
    `A ${innerR} ${innerR} 0 ${largeArc} 0 ${startInner.x} ${startInner.y}`,
    'Z'
  ].join(' ')
  
  // Calculate label position and visibility
  const midAngle = (startAngle + endAngle) / 2
  const midRadius = (innerR + outerR) / 2
  const labelPos = polarToCartesian(midRadius, midAngle)
  const arcLength = (endAngle - startAngle) * midRadius
  
  return {
    path,
    labelX: labelPos.x,
    labelY: labelPos.y,
    labelTransform: `translate(${labelPos.x}, ${labelPos.y}) rotate(${radiansToDegrees(midAngle) + (midAngle > Math.PI / 2 && midAngle < Math.PI * 1.5 ? 180 : 0)})`,
    labelAnchor: midAngle > Math.PI / 2 && midAngle < Math.PI * 1.5 ? 'end' : 'start',
    labelVisible: arcLength > 40 && (outerR - innerR) > 20
  }
}

const polarToCartesian = (radius, angle) => ({
  x: radius * Math.cos(angle - Math.PI / 2),
  y: radius * Math.sin(angle - Math.PI / 2)
})

const radiansToDegrees = (radians) => radians * (180 / Math.PI)

const adjustColor = (hex, percent) => {
  const num = parseInt(hex.replace('#', ''), 16)
  const amt = Math.round(2.55 * percent)
  const R = Math.min(255, (num >> 16) + amt)
  const G = Math.min(255, ((num >> 8) & 0x00FF) + amt)
  const B = Math.min(255, (num & 0x0000FF) + amt)
  return `#${(0x1000000 + R * 0x10000 + G * 0x100 + B).toString(16).slice(1)}`
}

const getTextColor = (bgColor) => {
  const hex = bgColor.replace('#', '')
  const r = parseInt(hex.substr(0, 2), 16)
  const g = parseInt(hex.substr(2, 2), 16)
  const b = parseInt(hex.substr(4, 2), 16)
  const brightness = (r * 299 + g * 587 + b * 114) / 1000
  return brightness > 128 ? '#1F2937' : '#FFFFFF'
}

const truncateLabel = (text, maxLength) => {
  const maxChars = Math.floor(maxLength / 7)
  if (text.length <= maxChars) return text
  return text.substring(0, maxChars - 1) + '…'
}

const formatValue = (value) => {
  if (props.valueFormatter) return props.valueFormatter(value)
  if (value >= 1000000) return `${(value / 1000000).toFixed(1)}M`
  if (value >= 1000) return `${(value / 1000).toFixed(1)}K`
  return value?.toLocaleString()
}

const isAncestorOf = (hoveredId, segmentId) => {
  const hovered = computedSegments.value.find(s => s.id === hoveredId)
  const segment = computedSegments.value.find(s => s.id === segmentId)
  if (!hovered || !segment) return false
  return segment.path.some((p, i) => hovered.path[i] === p)
}

const tooltipData = computed(() => {
  return computedSegments.value.find(s => s.id === hoveredSegment.value)
})

const tooltipStyle = computed(() => {
  if (!containerRef.value || !tooltipData.value) return {}
  
  const rect = containerRef.value.getBoundingClientRect()
  return {
    left: `${tooltipPosition.value.x - rect.left}px`,
    top: `${tooltipPosition.value.y - rect.top - 10}px`,
    transform: 'translate(-50%, -100%)'
  }
})

const centerTitle = computed(() => {
  if (hoveredSegment.value) {
    return tooltipData.value?.name || ''
  }
  return currentRoot.value?.[props.nameKey] || 'Total'
})

const centerSubtitle = computed(() => {
  if (hoveredSegment.value) {
    return formatValue(tooltipData.value?.value)
  }
  const total = computedSegments.value
    .filter(s => s.depth === 0)
    .reduce((sum, s) => sum + s.value, 0)
  return formatValue(total)
})

const legendItems = computed(() => {
  return computedSegments.value
    .filter(s => s.depth === 0)
    .map(s => ({ id: s.id, name: s.name, color: s.color }))
})

const handleMouseEnter = (segment) => {
  hoveredSegment.value = segment.id
}

const handleMouseLeave = () => {
  hoveredSegment.value = null
}

const handleClick = (segment) => {
  emit('segmentClick', segment)
  
  if (props.clickable && segment.node[props.childrenKey]?.length) {
    navigateTo(segment)
  }
}

const navigateTo = (segment) => {
  currentRoot.value = segment.node
  breadcrumb.value.push({ name: segment.name, node: segment.node })
  emit('navigate', { type: 'drillDown', segment })
}

const navigateUp = () => {
  if (breadcrumb.value.length > 0) {
    breadcrumb.value.pop()
    currentRoot.value = breadcrumb.value.length > 0 
      ? breadcrumb.value[breadcrumb.value.length - 1].node 
      : null
    emit('navigate', { type: 'drillUp' })
  }
}

const navigateToRoot = () => {
  currentRoot.value = null
  breadcrumb.value = []
  emit('navigate', { type: 'reset' })
}

// Track mouse position for tooltip
const handleMouseMove = (e) => {
  tooltipPosition.value = { x: e.clientX, y: e.clientY }
}

onMounted(() => {
  document.addEventListener('mousemove', handleMouseMove)
})

watch(() => props.data, () => {
  currentRoot.value = null
  breadcrumb.value = []
}, { deep: true })
</script>

<style scoped>
.sunburst-chart {
  @apply relative;
}

.sunburst-svg {
  @apply overflow-visible;
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
