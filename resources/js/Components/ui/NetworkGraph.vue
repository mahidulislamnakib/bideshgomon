<template>
  <div :class="['network-graph', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
          <ShareIcon class="w-5 h-5 text-purple-600 dark:text-purple-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">Network Graph</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ nodes.length }} nodes, {{ edges.length }} connections
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="resetView"
          title="Reset View"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="togglePhysics"
          :title="physicsEnabled ? 'Disable Physics' : 'Enable Physics'"
        >
          <CpuChipIcon :class="['w-5 h-5', physicsEnabled ? 'text-green-500' : '']" />
        </button>
        <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
          <button
            type="button"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            @click="zoomOut"
          >
            <MinusIcon class="w-4 h-4" />
          </button>
          <span class="px-2 text-sm text-gray-600 dark:text-gray-400 min-w-12 text-center">
            {{ Math.round(zoom * 100) }}%
          </span>
          <button
            type="button"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            @click="zoomIn"
          >
            <PlusIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Canvas -->
    <div
      ref="containerRef"
      class="relative overflow-hidden"
      :style="{ height: height }"
      @mousedown="startPan"
      @mousemove="onMouseMove"
      @mouseup="endPan"
      @mouseleave="endPan"
      @wheel="onWheel"
    >
      <svg
        ref="svgRef"
        class="w-full h-full"
        :viewBox="viewBox"
      >
        <!-- Grid -->
        <defs>
          <pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse">
            <path
              d="M 50 0 L 0 0 0 50"
              fill="none"
              :stroke="props.theme === 'dark' ? '#374151' : '#E5E7EB'"
              stroke-width="0.5"
            />
          </pattern>
        </defs>
        <rect v-if="showGrid" width="100%" height="100%" fill="url(#grid)" />

        <!-- Edges -->
        <g>
          <g
            v-for="edge in edges"
            :key="`edge-${edge.source}-${edge.target}`"
            class="cursor-pointer"
            @click="selectEdge(edge)"
          >
            <line
              :x1="getNodePosition(edge.source).x"
              :y1="getNodePosition(edge.source).y"
              :x2="getNodePosition(edge.target).x"
              :y2="getNodePosition(edge.target).y"
              :stroke="getEdgeColor(edge)"
              :stroke-width="edge.weight || 2"
              :stroke-dasharray="edge.dashed ? '5,5' : 'none'"
              class="transition-colors"
            />
            <!-- Arrow -->
            <polygon
              v-if="edge.directed"
              :points="getArrowPoints(edge)"
              :fill="getEdgeColor(edge)"
            />
            <!-- Edge Label -->
            <text
              v-if="edge.label"
              :x="(getNodePosition(edge.source).x + getNodePosition(edge.target).x) / 2"
              :y="(getNodePosition(edge.source).y + getNodePosition(edge.target).y) / 2 - 8"
              text-anchor="middle"
              :fill="props.theme === 'dark' ? '#9CA3AF' : '#6B7280'"
              font-size="12"
            >
              {{ edge.label }}
            </text>
          </g>
        </g>

        <!-- Nodes -->
        <g>
          <g
            v-for="node in nodes"
            :key="node.id"
            :transform="`translate(${nodePositions[node.id]?.x || 0}, ${nodePositions[node.id]?.y || 0})`"
            class="cursor-pointer"
            @mousedown.stop="startNodeDrag(node, $event)"
            @click="selectNode(node)"
          >
            <!-- Node Shape -->
            <circle
              v-if="node.shape === 'circle' || !node.shape"
              :r="node.size || 30"
              :fill="getNodeColor(node)"
              :stroke="selectedNode?.id === node.id ? '#3B82F6' : 'transparent'"
              stroke-width="3"
              class="transition-colors"
            />
            <rect
              v-else-if="node.shape === 'square'"
              :x="-(node.size || 30)"
              :y="-(node.size || 30)"
              :width="(node.size || 30) * 2"
              :height="(node.size || 30) * 2"
              :fill="getNodeColor(node)"
              :stroke="selectedNode?.id === node.id ? '#3B82F6' : 'transparent'"
              stroke-width="3"
              rx="4"
              class="transition-colors"
            />
            <polygon
              v-else-if="node.shape === 'diamond'"
              :points="getDiamondPoints(node.size || 30)"
              :fill="getNodeColor(node)"
              :stroke="selectedNode?.id === node.id ? '#3B82F6' : 'transparent'"
              stroke-width="3"
              class="transition-colors"
            />
            
            <!-- Node Icon -->
            <text
              v-if="node.icon"
              text-anchor="middle"
              dominant-baseline="central"
              :fill="getNodeTextColor(node)"
              font-size="20"
            >
              {{ node.icon }}
            </text>
            
            <!-- Node Label -->
            <text
              :y="(node.size || 30) + 16"
              text-anchor="middle"
              :fill="props.theme === 'dark' ? '#E5E7EB' : '#1F2937'"
              font-size="12"
              font-weight="500"
            >
              {{ node.label }}
            </text>
          </g>
        </g>
      </svg>

      <!-- Tooltip -->
      <Transition
        enter-active-class="transition-opacity duration-150"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="hoveredNode && showTooltip"
          class="absolute pointer-events-none px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-xl"
          :style="{
            left: `${tooltipPosition.x}px`,
            top: `${tooltipPosition.y}px`,
            transform: 'translate(-50%, -100%)'
          }"
        >
          <div class="font-semibold">{{ hoveredNode.label }}</div>
          <div v-if="hoveredNode.description" class="text-gray-300 text-xs mt-1">
            {{ hoveredNode.description }}
          </div>
          <div class="text-gray-400 text-xs mt-1">
            {{ getNodeConnections(hoveredNode.id) }} connections
          </div>
        </div>
      </Transition>
    </div>

    <!-- Legend -->
    <div v-if="showLegend && nodeTypes.length > 0" class="p-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex flex-wrap gap-4">
        <div
          v-for="type in nodeTypes"
          :key="type.name"
          class="flex items-center gap-2"
        >
          <div
            class="w-4 h-4 rounded-full"
            :style="{ backgroundColor: type.color }"
          />
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ type.name }}</span>
        </div>
      </div>
    </div>

    <!-- Status Bar -->
    <div v-if="showStatusBar" class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
      <div class="flex items-center gap-4">
        <span>Nodes: {{ nodes.length }}</span>
        <span>Edges: {{ edges.length }}</span>
        <span v-if="selectedNode">Selected: {{ selectedNode.label }}</span>
      </div>
      <div>
        <span v-if="physicsEnabled">Physics: Active</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import {
  ShareIcon,
  ArrowPathIcon,
  CpuChipIcon,
  PlusIcon,
  MinusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  nodes: {
    type: Array,
    default: () => [
      { id: 'a', label: 'Server A', color: '#3B82F6', icon: '🖥️' },
      { id: 'b', label: 'Server B', color: '#10B981', icon: '🖥️' },
      { id: 'c', label: 'Database', color: '#8B5CF6', icon: '💾', shape: 'square' },
      { id: 'd', label: 'Cache', color: '#F59E0B', icon: '⚡', shape: 'diamond' },
      { id: 'e', label: 'Client', color: '#EF4444', icon: '💻' }
    ]
  },
  edges: {
    type: Array,
    default: () => [
      { source: 'e', target: 'a', label: 'HTTP' },
      { source: 'a', target: 'c', directed: true },
      { source: 'a', target: 'd', directed: true },
      { source: 'b', target: 'c', directed: true },
      { source: 'b', target: 'd', directed: true },
      { source: 'a', target: 'b', dashed: true }
    ]
  },
  height: {
    type: String,
    default: '500px'
  },
  theme: {
    type: String,
    default: 'light',
    validator: (v) => ['light', 'dark'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showGrid: {
    type: Boolean,
    default: true
  },
  showLegend: {
    type: Boolean,
    default: true
  },
  showStatusBar: {
    type: Boolean,
    default: true
  },
  showTooltip: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['node-click', 'edge-click', 'node-move'])

const containerRef = ref(null)
const svgRef = ref(null)
const zoom = ref(1)
const panOffset = ref({ x: 0, y: 0 })
const isPanning = ref(false)
const panStart = ref({ x: 0, y: 0 })
const nodePositions = ref({})
const draggingNode = ref(null)
const dragOffset = ref({ x: 0, y: 0 })
const selectedNode = ref(null)
const selectedEdge = ref(null)
const hoveredNode = ref(null)
const tooltipPosition = ref({ x: 0, y: 0 })
const physicsEnabled = ref(false)
const physicsInterval = ref(null)

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark'
    ? 'bg-gray-900 border-gray-700'
    : 'bg-white border-gray-200'
])

const viewBox = computed(() => {
  const width = 800
  const height = 500
  const cx = width / 2 - panOffset.value.x / zoom.value
  const cy = height / 2 - panOffset.value.y / zoom.value
  const vw = width / zoom.value
  const vh = height / zoom.value
  return `${cx - vw/2} ${cy - vh/2} ${vw} ${vh}`
})

const nodeTypes = computed(() => {
  const types = new Map()
  props.nodes.forEach(node => {
    if (node.type && node.color) {
      types.set(node.type, { name: node.type, color: node.color })
    }
  })
  return Array.from(types.values())
})

const initializePositions = () => {
  const positions = {}
  const centerX = 400
  const centerY = 250
  const radius = 150
  
  props.nodes.forEach((node, index) => {
    if (node.x !== undefined && node.y !== undefined) {
      positions[node.id] = { x: node.x, y: node.y }
    } else {
      const angle = (2 * Math.PI * index) / props.nodes.length
      positions[node.id] = {
        x: centerX + radius * Math.cos(angle),
        y: centerY + radius * Math.sin(angle)
      }
    }
  })
  
  nodePositions.value = positions
}

const getNodePosition = (nodeId) => {
  return nodePositions.value[nodeId] || { x: 0, y: 0 }
}

const getNodeColor = (node) => {
  return node.color || '#6B7280'
}

const getNodeTextColor = (node) => {
  return '#FFFFFF'
}

const getEdgeColor = (edge) => {
  if (selectedEdge.value === edge) return '#3B82F6'
  return edge.color || (props.theme === 'dark' ? '#6B7280' : '#9CA3AF')
}

const getDiamondPoints = (size) => {
  return `0,${-size} ${size},0 0,${size} ${-size},0`
}

const getArrowPoints = (edge) => {
  const source = getNodePosition(edge.source)
  const target = getNodePosition(edge.target)
  
  const dx = target.x - source.x
  const dy = target.y - source.y
  const angle = Math.atan2(dy, dx)
  
  const targetNode = props.nodes.find(n => n.id === edge.target)
  const nodeSize = targetNode?.size || 30
  
  const tipX = target.x - (nodeSize + 5) * Math.cos(angle)
  const tipY = target.y - (nodeSize + 5) * Math.sin(angle)
  
  const arrowSize = 10
  const p1x = tipX - arrowSize * Math.cos(angle - Math.PI/6)
  const p1y = tipY - arrowSize * Math.sin(angle - Math.PI/6)
  const p2x = tipX - arrowSize * Math.cos(angle + Math.PI/6)
  const p2y = tipY - arrowSize * Math.sin(angle + Math.PI/6)
  
  return `${tipX},${tipY} ${p1x},${p1y} ${p2x},${p2y}`
}

const getNodeConnections = (nodeId) => {
  return props.edges.filter(e => e.source === nodeId || e.target === nodeId).length
}

const zoomIn = () => {
  zoom.value = Math.min(3, zoom.value + 0.2)
}

const zoomOut = () => {
  zoom.value = Math.max(0.3, zoom.value - 0.2)
}

const onWheel = (e) => {
  e.preventDefault()
  if (e.deltaY < 0) {
    zoomIn()
  } else {
    zoomOut()
  }
}

const startPan = (e) => {
  if (draggingNode.value) return
  isPanning.value = true
  panStart.value = { x: e.clientX - panOffset.value.x, y: e.clientY - panOffset.value.y }
}

const onMouseMove = (e) => {
  if (isPanning.value) {
    panOffset.value = {
      x: e.clientX - panStart.value.x,
      y: e.clientY - panStart.value.y
    }
  } else if (draggingNode.value) {
    const rect = svgRef.value.getBoundingClientRect()
    const svgX = (e.clientX - rect.left - panOffset.value.x) / zoom.value + 400 - 400/zoom.value
    const svgY = (e.clientY - rect.top - panOffset.value.y) / zoom.value + 250 - 250/zoom.value
    
    nodePositions.value[draggingNode.value.id] = {
      x: svgX,
      y: svgY
    }
  }
  
  // Tooltip tracking
  if (!draggingNode.value && !isPanning.value) {
    const rect = containerRef.value?.getBoundingClientRect()
    if (rect) {
      tooltipPosition.value = {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top - 10
      }
    }
  }
}

const endPan = () => {
  if (draggingNode.value) {
    emit('node-move', {
      node: draggingNode.value,
      position: nodePositions.value[draggingNode.value.id]
    })
  }
  isPanning.value = false
  draggingNode.value = null
}

const startNodeDrag = (node, e) => {
  draggingNode.value = node
  hoveredNode.value = node
}

const selectNode = (node) => {
  selectedNode.value = node
  selectedEdge.value = null
  emit('node-click', node)
}

const selectEdge = (edge) => {
  selectedEdge.value = edge
  selectedNode.value = null
  emit('edge-click', edge)
}

const resetView = () => {
  zoom.value = 1
  panOffset.value = { x: 0, y: 0 }
  initializePositions()
}

const togglePhysics = () => {
  physicsEnabled.value = !physicsEnabled.value
  
  if (physicsEnabled.value) {
    startPhysics()
  } else {
    stopPhysics()
  }
}

const startPhysics = () => {
  physicsInterval.value = setInterval(() => {
    applyForces()
  }, 50)
}

const stopPhysics = () => {
  if (physicsInterval.value) {
    clearInterval(physicsInterval.value)
    physicsInterval.value = null
  }
}

const applyForces = () => {
  const positions = { ...nodePositions.value }
  const repulsion = 5000
  const attraction = 0.01
  const damping = 0.9
  
  // Calculate forces
  props.nodes.forEach(node1 => {
    let fx = 0
    let fy = 0
    const pos1 = positions[node1.id]
    
    // Repulsion from other nodes
    props.nodes.forEach(node2 => {
      if (node1.id === node2.id) return
      const pos2 = positions[node2.id]
      const dx = pos1.x - pos2.x
      const dy = pos1.y - pos2.y
      const dist = Math.max(1, Math.sqrt(dx*dx + dy*dy))
      const force = repulsion / (dist * dist)
      fx += (dx / dist) * force
      fy += (dy / dist) * force
    })
    
    // Attraction along edges
    props.edges.forEach(edge => {
      let otherId = null
      if (edge.source === node1.id) otherId = edge.target
      if (edge.target === node1.id) otherId = edge.source
      if (!otherId) return
      
      const pos2 = positions[otherId]
      const dx = pos2.x - pos1.x
      const dy = pos2.y - pos1.y
      fx += dx * attraction
      fy += dy * attraction
    })
    
    // Center gravity
    fx += (400 - pos1.x) * 0.001
    fy += (250 - pos1.y) * 0.001
    
    // Apply forces
    if (!draggingNode.value || draggingNode.value.id !== node1.id) {
      positions[node1.id] = {
        x: pos1.x + fx * damping,
        y: pos1.y + fy * damping
      }
    }
  })
  
  nodePositions.value = positions
}

onMounted(() => {
  initializePositions()
})

onUnmounted(() => {
  stopPhysics()
})

watch(() => props.nodes, initializePositions, { deep: true })
</script>
