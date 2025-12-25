<template>
  <div :class="['flowchart rounded-lg overflow-hidden border', containerClasses]">
    <!-- Toolbar -->
    <div :class="['flex items-center justify-between px-3 py-2 border-b', themeClasses.toolbar]">
      <!-- Node Tools -->
      <div class="flex items-center gap-1">
        <button
          v-for="nodeType in nodeTypes"
          :key="nodeType.id"
          @click="addNode(nodeType.id)"
          :class="['px-3 py-1.5 text-sm rounded-lg transition-colors flex items-center gap-1.5', themeClasses.addBtn]"
          :title="nodeType.label"
        >
          <component :is="nodeType.icon" class="w-4 h-4" />
          {{ nodeType.label }}
        </button>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1">
        <button
          @click="zoomIn"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Zoom In"
        >
          <MagnifyingGlassPlusIcon class="w-5 h-5" />
        </button>
        <button
          @click="zoomOut"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Zoom Out"
        >
          <MagnifyingGlassMinusIcon class="w-5 h-5" />
        </button>
        <button
          @click="fitToView"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Fit to View"
        >
          <ArrowsPointingOutIcon class="w-5 h-5" />
        </button>

        <div class="w-px h-6 bg-gray-300 mx-2" />

        <button
          @click="deleteSelected"
          :disabled="!selectedNode && !selectedEdge"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Delete Selected"
        >
          <TrashIcon class="w-5 h-5" />
        </button>
        <button
          @click="exportFlowchart"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Export"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Canvas -->
    <div
      ref="canvasRef"
      :class="['relative overflow-hidden', themeClasses.canvas]"
      :style="{ height: `${height}px` }"
      @mousedown="handleCanvasMouseDown"
      @mousemove="handleMouseMove"
      @mouseup="handleMouseUp"
      @wheel="handleWheel"
    >
      <!-- Grid -->
      <div
        v-if="showGrid"
        class="absolute inset-0 pointer-events-none"
        :style="gridStyle"
      />

      <!-- SVG Layer for Edges -->
      <svg
        class="absolute inset-0 w-full h-full pointer-events-none"
        :style="{ transform: `scale(${zoom}) translate(${pan.x}px, ${pan.y}px)` }"
      >
        <defs>
          <marker
            id="arrowhead"
            markerWidth="10"
            markerHeight="7"
            refX="9"
            refY="3.5"
            orient="auto"
          >
            <polygon
              points="0 0, 10 3.5, 0 7"
              :fill="theme === 'dark' ? '#9CA3AF' : '#6B7280'"
            />
          </marker>
        </defs>

        <!-- Edges -->
        <g v-for="edge in edges" :key="edge.id">
          <path
            :d="getEdgePath(edge)"
            :stroke="selectedEdge === edge.id ? '#3B82F6' : (theme === 'dark' ? '#9CA3AF' : '#6B7280')"
            stroke-width="2"
            fill="none"
            marker-end="url(#arrowhead)"
            class="pointer-events-auto cursor-pointer"
            @click.stop="selectEdge(edge.id)"
          />
          <!-- Edge Label -->
          <text
            v-if="edge.label"
            :x="getEdgeMidpoint(edge).x"
            :y="getEdgeMidpoint(edge).y - 8"
            text-anchor="middle"
            :fill="theme === 'dark' ? '#9CA3AF' : '#6B7280'"
            font-size="12"
          >
            {{ edge.label }}
          </text>
        </g>

        <!-- Connection Preview -->
        <path
          v-if="connectingFrom && mousePos"
          :d="getConnectionPreviewPath()"
          stroke="#3B82F6"
          stroke-width="2"
          stroke-dasharray="5,5"
          fill="none"
        />
      </svg>

      <!-- Nodes -->
      <div
        :style="{ transform: `scale(${zoom}) translate(${pan.x}px, ${pan.y}px)`, transformOrigin: '0 0' }"
        class="absolute inset-0"
      >
        <div
          v-for="node in nodes"
          :key="node.id"
          :class="[
            'absolute cursor-move select-none',
            getNodeClasses(node)
          ]"
          :style="{ left: `${node.x}px`, top: `${node.y}px`, width: `${node.width || 160}px` }"
          @mousedown.stop="startDragNode(node, $event)"
          @click.stop="selectNode(node.id)"
          @dblclick.stop="editNode(node)"
        >
          <!-- Node Content -->
          <div :class="['p-3 rounded-lg border-2 shadow-sm', getNodeStyle(node)]">
            <!-- Node Icon -->
            <div class="flex items-center gap-2 mb-1">
              <component
                :is="getNodeIcon(node.type)"
                :class="['w-4 h-4 flex-shrink-0', getNodeIconColor(node.type)]"
              />
              <span class="font-medium text-sm truncate">{{ node.label }}</span>
            </div>
            
            <!-- Node Description -->
            <p v-if="node.description" :class="['text-xs truncate', themeClasses.nodeDescription]">
              {{ node.description }}
            </p>

            <!-- Connection Points -->
            <div
              class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-blue-500 opacity-0 hover:opacity-100 cursor-crosshair transition-opacity"
              @mousedown.stop="startConnection(node, 'top')"
            />
            <div
              class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-blue-500 opacity-0 hover:opacity-100 cursor-crosshair transition-opacity"
              @mousedown.stop="startConnection(node, 'bottom')"
            />
            <div
              class="absolute top-1/2 -left-1.5 -translate-y-1/2 w-3 h-3 rounded-full bg-blue-500 opacity-0 hover:opacity-100 cursor-crosshair transition-opacity"
              @mousedown.stop="startConnection(node, 'left')"
            />
            <div
              class="absolute top-1/2 -right-1.5 -translate-y-1/2 w-3 h-3 rounded-full bg-blue-500 opacity-0 hover:opacity-100 cursor-crosshair transition-opacity"
              @mousedown.stop="startConnection(node, 'right')"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Node Edit Modal -->
    <Teleport to="body">
      <div
        v-if="editingNode"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="cancelEdit"
      >
        <div :class="['w-80 p-4 rounded-xl shadow-xl', themeClasses.modal]">
          <h3 :class="['text-lg font-semibold mb-4', themeClasses.modalTitle]">Edit Node</h3>
          
          <div class="space-y-3">
            <div>
              <label :class="['block text-sm font-medium mb-1', themeClasses.label]">Label</label>
              <input
                v-model="editForm.label"
                type="text"
                :class="['w-full px-3 py-2 rounded-lg border', themeClasses.input]"
              />
            </div>
            <div>
              <label :class="['block text-sm font-medium mb-1', themeClasses.label]">Description</label>
              <textarea
                v-model="editForm.description"
                rows="2"
                :class="['w-full px-3 py-2 rounded-lg border', themeClasses.input]"
              />
            </div>
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <button
              @click="cancelEdit"
              :class="['px-4 py-2 text-sm rounded-lg', themeClasses.cancelBtn]"
            >
              Cancel
            </button>
            <button
              @click="saveEdit"
              class="px-4 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Status Bar -->
    <div v-if="showStatusBar" :class="['flex items-center justify-between px-3 py-1 text-xs border-t', themeClasses.statusBar]">
      <div class="flex items-center gap-4">
        <span>Nodes: {{ nodes.length }}</span>
        <span>Connections: {{ edges.length }}</span>
      </div>
      <div class="flex items-center gap-4">
        <span>Zoom: {{ Math.round(zoom * 100) }}%</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  PlayIcon,
  StopIcon,
  Cog6ToothIcon,
  DocumentTextIcon,
  QuestionMarkCircleIcon,
  ArrowPathIcon,
  MagnifyingGlassPlusIcon,
  MagnifyingGlassMinusIcon,
  ArrowsPointingOutIcon,
  TrashIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  height: {
    type: Number,
    default: 500
  },
  showGrid: {
    type: Boolean,
    default: true
  },
  showStatusBar: {
    type: Boolean,
    default: true
  },
  initialNodes: {
    type: Array,
    default: () => []
  },
  initialEdges: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['change', 'node-click', 'edge-click'])

const canvasRef = ref(null)
const nodes = ref(props.initialNodes.length ? [...props.initialNodes] : [])
const edges = ref(props.initialEdges.length ? [...props.initialEdges] : [])
const zoom = ref(1)
const pan = reactive({ x: 0, y: 0 })
const selectedNode = ref(null)
const selectedEdge = ref(null)
const draggingNode = ref(null)
const dragOffset = reactive({ x: 0, y: 0 })
const connectingFrom = ref(null)
const connectingPort = ref(null)
const mousePos = ref(null)
const isPanning = ref(false)
const panStart = reactive({ x: 0, y: 0 })
const editingNode = ref(null)
const editForm = reactive({ label: '', description: '' })

let nodeIdCounter = 1

const nodeTypes = [
  { id: 'start', label: 'Start', icon: PlayIcon },
  { id: 'end', label: 'End', icon: StopIcon },
  { id: 'process', label: 'Process', icon: Cog6ToothIcon },
  { id: 'decision', label: 'Decision', icon: QuestionMarkCircleIcon },
  { id: 'data', label: 'Data', icon: DocumentTextIcon },
  { id: 'loop', label: 'Loop', icon: ArrowPathIcon }
]

const getNodeIcon = (type) => {
  const nodeType = nodeTypes.find(t => t.id === type)
  return nodeType?.icon || DocumentTextIcon
}

const getNodeIconColor = (type) => {
  const colors = {
    start: 'text-green-500',
    end: 'text-red-500',
    process: 'text-blue-500',
    decision: 'text-yellow-500',
    data: 'text-purple-500',
    loop: 'text-orange-500'
  }
  return colors[type] || 'text-gray-500'
}

const getNodeStyle = (node) => {
  const isSelected = selectedNode.value === node.id
  const baseStyles = {
    start: 'border-green-500 bg-green-50 dark:bg-green-900/20',
    end: 'border-red-500 bg-red-50 dark:bg-red-900/20',
    process: 'border-blue-500 bg-blue-50 dark:bg-blue-900/20',
    decision: 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20',
    data: 'border-purple-500 bg-purple-50 dark:bg-purple-900/20',
    loop: 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
  }

  let style = baseStyles[node.type] || 'border-gray-300 bg-gray-50'
  if (props.theme === 'dark') {
    style = style.replace('bg-', 'bg-opacity-20 bg-')
  }
  if (isSelected) {
    style += ' ring-2 ring-blue-500 ring-offset-2'
  }
  return style
}

const getNodeClasses = (node) => {
  return [
    selectedNode.value === node.id ? 'z-10' : 'z-0'
  ]
}

const gridStyle = computed(() => {
  const size = 20
  const color = props.theme === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'
  return {
    backgroundImage: `linear-gradient(${color} 1px, transparent 1px), linear-gradient(90deg, ${color} 1px, transparent 1px)`,
    backgroundSize: `${size * zoom.value}px ${size * zoom.value}px`,
    backgroundPosition: `${pan.x * zoom.value}px ${pan.y * zoom.value}px`
  }
})

const addNode = (type) => {
  const id = `node-${nodeIdCounter++}`
  const canvasRect = canvasRef.value?.getBoundingClientRect()
  const x = (canvasRect?.width || 400) / 2 / zoom.value - pan.x - 80
  const y = (canvasRect?.height || 300) / 2 / zoom.value - pan.y - 30

  nodes.value.push({
    id,
    type,
    label: `${type.charAt(0).toUpperCase() + type.slice(1)} ${nodes.value.length + 1}`,
    description: '',
    x,
    y,
    width: 160
  })

  emitChange()
}

const selectNode = (id) => {
  selectedNode.value = id
  selectedEdge.value = null
  const node = nodes.value.find(n => n.id === id)
  if (node) emit('node-click', node)
}

const selectEdge = (id) => {
  selectedEdge.value = id
  selectedNode.value = null
  const edge = edges.value.find(e => e.id === id)
  if (edge) emit('edge-click', edge)
}

const startDragNode = (node, e) => {
  draggingNode.value = node.id
  dragOffset.x = e.clientX / zoom.value - node.x
  dragOffset.y = e.clientY / zoom.value - node.y
  selectNode(node.id)
}

const handleCanvasMouseDown = (e) => {
  if (e.button === 0 && !draggingNode.value && !connectingFrom.value) {
    isPanning.value = true
    panStart.x = e.clientX - pan.x * zoom.value
    panStart.y = e.clientY - pan.y * zoom.value
    selectedNode.value = null
    selectedEdge.value = null
  }
}

const handleMouseMove = (e) => {
  mousePos.value = { x: e.clientX, y: e.clientY }

  if (draggingNode.value) {
    const node = nodes.value.find(n => n.id === draggingNode.value)
    if (node) {
      node.x = e.clientX / zoom.value - dragOffset.x - pan.x
      node.y = e.clientY / zoom.value - dragOffset.y - pan.y
    }
  } else if (isPanning.value) {
    pan.x = (e.clientX - panStart.x) / zoom.value
    pan.y = (e.clientY - panStart.y) / zoom.value
  }
}

const handleMouseUp = (e) => {
  if (draggingNode.value) {
    emitChange()
  }
  
  if (connectingFrom.value) {
    // Check if we're over a node
    const rect = canvasRef.value.getBoundingClientRect()
    const x = (e.clientX - rect.left) / zoom.value - pan.x
    const y = (e.clientY - rect.top) / zoom.value - pan.y
    
    const targetNode = nodes.value.find(n => {
      return n.id !== connectingFrom.value &&
             x >= n.x && x <= n.x + (n.width || 160) &&
             y >= n.y && y <= n.y + 60
    })

    if (targetNode) {
      createEdge(connectingFrom.value, targetNode.id)
    }
  }

  draggingNode.value = null
  isPanning.value = false
  connectingFrom.value = null
  connectingPort.value = null
}

const startConnection = (node, port) => {
  connectingFrom.value = node.id
  connectingPort.value = port
}

const createEdge = (sourceId, targetId) => {
  // Check if edge already exists
  const exists = edges.value.some(
    e => e.source === sourceId && e.target === targetId
  )
  if (exists) return

  edges.value.push({
    id: `edge-${Date.now()}`,
    source: sourceId,
    target: targetId,
    label: ''
  })

  emitChange()
}

const getNodeCenter = (nodeId) => {
  const node = nodes.value.find(n => n.id === nodeId)
  if (!node) return { x: 0, y: 0 }
  return {
    x: node.x + (node.width || 160) / 2,
    y: node.y + 30
  }
}

const getEdgePath = (edge) => {
  const source = getNodeCenter(edge.source)
  const target = getNodeCenter(edge.target)
  
  const dx = target.x - source.x
  const dy = target.y - source.y
  
  // Bezier curve
  const cx = source.x + dx / 2
  const cy1 = source.y + dy / 4
  const cy2 = target.y - dy / 4
  
  return `M ${source.x} ${source.y} C ${cx} ${cy1}, ${cx} ${cy2}, ${target.x} ${target.y}`
}

const getEdgeMidpoint = (edge) => {
  const source = getNodeCenter(edge.source)
  const target = getNodeCenter(edge.target)
  return {
    x: (source.x + target.x) / 2,
    y: (source.y + target.y) / 2
  }
}

const getConnectionPreviewPath = () => {
  if (!connectingFrom.value || !mousePos.value) return ''
  
  const source = getNodeCenter(connectingFrom.value)
  const rect = canvasRef.value.getBoundingClientRect()
  const target = {
    x: (mousePos.value.x - rect.left) / zoom.value - pan.x,
    y: (mousePos.value.y - rect.top) / zoom.value - pan.y
  }
  
  return `M ${source.x} ${source.y} L ${target.x} ${target.y}`
}

const deleteSelected = () => {
  if (selectedNode.value) {
    nodes.value = nodes.value.filter(n => n.id !== selectedNode.value)
    edges.value = edges.value.filter(
      e => e.source !== selectedNode.value && e.target !== selectedNode.value
    )
    selectedNode.value = null
    emitChange()
  } else if (selectedEdge.value) {
    edges.value = edges.value.filter(e => e.id !== selectedEdge.value)
    selectedEdge.value = null
    emitChange()
  }
}

const editNode = (node) => {
  editingNode.value = node.id
  editForm.label = node.label
  editForm.description = node.description || ''
}

const saveEdit = () => {
  const node = nodes.value.find(n => n.id === editingNode.value)
  if (node) {
    node.label = editForm.label
    node.description = editForm.description
    emitChange()
  }
  editingNode.value = null
}

const cancelEdit = () => {
  editingNode.value = null
}

const zoomIn = () => {
  zoom.value = Math.min(zoom.value * 1.2, 3)
}

const zoomOut = () => {
  zoom.value = Math.max(zoom.value / 1.2, 0.3)
}

const fitToView = () => {
  zoom.value = 1
  pan.x = 0
  pan.y = 0
}

const handleWheel = (e) => {
  e.preventDefault()
  const delta = e.deltaY > 0 ? 0.9 : 1.1
  zoom.value = Math.max(0.3, Math.min(3, zoom.value * delta))
}

const exportFlowchart = () => {
  const data = {
    nodes: nodes.value,
    edges: edges.value
  }
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'flowchart.json'
  a.click()
  URL.revokeObjectURL(url)
}

const emitChange = () => {
  emit('change', { nodes: nodes.value, edges: edges.value })
}

const containerClasses = computed(() => {
  return props.theme === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      toolbar: 'bg-gray-800 border-gray-700',
      addBtn: 'bg-gray-700 text-gray-200 hover:bg-gray-600',
      action: 'text-gray-400 hover:bg-gray-700 disabled:opacity-50',
      canvas: 'bg-gray-900',
      nodeDescription: 'text-gray-400',
      statusBar: 'bg-gray-800 border-gray-700 text-gray-400',
      modal: 'bg-gray-800',
      modalTitle: 'text-white',
      label: 'text-gray-300',
      input: 'bg-gray-700 border-gray-600 text-white',
      cancelBtn: 'bg-gray-700 text-gray-300 hover:bg-gray-600'
    }
  }
  return {
    toolbar: 'bg-gray-50 border-gray-200',
    addBtn: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    action: 'text-gray-600 hover:bg-gray-200 disabled:opacity-50',
    canvas: 'bg-gray-50',
    nodeDescription: 'text-gray-500',
    statusBar: 'bg-gray-50 border-gray-200 text-gray-600',
    modal: 'bg-white',
    modalTitle: 'text-gray-900',
    label: 'text-gray-700',
    input: 'bg-white border-gray-300 text-gray-900',
    cancelBtn: 'bg-gray-100 text-gray-700 hover:bg-gray-200'
  }
})

defineExpose({
  addNode,
  deleteSelected,
  zoomIn,
  zoomOut,
  fitToView,
  exportFlowchart,
  getNodes: () => [...nodes.value],
  getEdges: () => [...edges.value],
  setData: (data) => {
    nodes.value = data.nodes || []
    edges.value = data.edges || []
  }
})
</script>
