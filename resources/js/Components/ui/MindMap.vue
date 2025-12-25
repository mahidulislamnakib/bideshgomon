<template>
  <div :class="['mind-map-container rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg">
          <CircleStackIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ nodes.length }} nodes</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Zoom In"
          @click="zoomIn"
        >
          <MagnifyingGlassPlusIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Zoom Out"
          @click="zoomOut"
        >
          <MagnifyingGlassMinusIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Reset View"
          @click="resetView"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Export"
          @click="exportMap"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Canvas -->
    <div
      ref="canvasRef"
      class="relative overflow-hidden cursor-grab active:cursor-grabbing"
      :style="{ height }"
      @mousedown="startPan"
      @mousemove="onPan"
      @mouseup="endPan"
      @mouseleave="endPan"
      @wheel="onWheel"
      @dblclick="addNodeAtPosition"
    >
      <svg
        class="absolute inset-0 w-full h-full pointer-events-none"
        :style="{ transform: `translate(${panOffset.x}px, ${panOffset.y}px) scale(${zoom})` }"
      >
        <!-- Connections -->
        <g v-for="connection in connections" :key="`${connection.from}-${connection.to}`">
          <path
            :d="getConnectionPath(connection)"
            fill="none"
            :stroke="theme === 'dark' ? '#6b7280' : '#d1d5db'"
            stroke-width="2"
            :class="connection.style === 'dashed' ? 'stroke-dasharray-4' : ''"
          />
        </g>
      </svg>

      <!-- Nodes -->
      <div
        :style="{ transform: `translate(${panOffset.x}px, ${panOffset.y}px) scale(${zoom})`, transformOrigin: '0 0' }"
        class="absolute inset-0"
      >
        <div
          v-for="node in nodes"
          :key="node.id"
          :class="[
            'absolute cursor-move select-none transition-shadow',
            'rounded-xl shadow-lg border-2',
            node.id === selectedNode?.id ? 'ring-2 ring-blue-500 ring-offset-2' : '',
            getNodeColorClasses(node.color)
          ]"
          :style="{
            left: `${node.x}px`,
            top: `${node.y}px`,
            minWidth: '120px',
            maxWidth: '200px'
          }"
          @mousedown.stop="startDragNode($event, node)"
          @click.stop="selectNode(node)"
          @dblclick.stop="editNode(node)"
        >
          <div class="p-3">
            <div v-if="editingNode?.id === node.id" class="flex items-center gap-2">
              <input
                ref="editInputRef"
                v-model="editingText"
                type="text"
                class="w-full px-2 py-1 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                @keydown.enter="saveNodeEdit"
                @keydown.escape="cancelNodeEdit"
                @blur="saveNodeEdit"
              />
            </div>
            <div v-else>
              <div class="flex items-center gap-2 mb-1">
                <component
                  v-if="node.icon"
                  :is="getNodeIcon(node.icon)"
                  class="w-4 h-4 flex-shrink-0"
                />
                <span class="font-medium text-sm truncate">{{ node.label }}</span>
              </div>
              <p v-if="node.description" class="text-xs opacity-75 line-clamp-2">
                {{ node.description }}
              </p>
            </div>
          </div>

          <!-- Connection Handle -->
          <div
            class="absolute -right-2 top-1/2 -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full cursor-crosshair opacity-0 hover:opacity-100 transition-opacity"
            @mousedown.stop="startConnection($event, node)"
          />
        </div>
      </div>

      <!-- Connection Preview -->
      <svg
        v-if="isConnecting"
        class="absolute inset-0 w-full h-full pointer-events-none"
        :style="{ transform: `translate(${panOffset.x}px, ${panOffset.y}px) scale(${zoom})` }"
      >
        <line
          :x1="connectionStart.x"
          :y1="connectionStart.y"
          :x2="connectionEnd.x"
          :y2="connectionEnd.y"
          stroke="#3b82f6"
          stroke-width="2"
          stroke-dasharray="4"
        />
      </svg>

      <!-- Instructions -->
      <div class="absolute bottom-4 left-4 text-xs text-gray-400 dark:text-gray-500 space-y-1">
        <p>Double-click: Add node</p>
        <p>Drag node handle: Connect</p>
        <p>Scroll: Zoom • Drag: Pan</p>
      </div>
    </div>

    <!-- Node Editor Panel -->
    <div
      v-if="selectedNode && showNodePanel"
      class="absolute right-4 top-20 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
    >
      <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <span class="font-medium text-sm text-gray-900 dark:text-white">Node Properties</span>
        <button
          type="button"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
          @click="showNodePanel = false"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
      <div class="p-3 space-y-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
          <input
            v-model="selectedNode.label"
            type="text"
            class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
          <textarea
            v-model="selectedNode.description"
            rows="2"
            class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
          <div class="flex gap-1">
            <button
              v-for="color in nodeColors"
              :key="color"
              type="button"
              :class="[
                'w-6 h-6 rounded-full border-2 transition-transform hover:scale-110',
                selectedNode.color === color ? 'ring-2 ring-offset-2 ring-blue-500' : 'border-transparent'
              ]"
              :style="{ backgroundColor: getColorHex(color) }"
              @click="selectedNode.color = color"
            />
          </div>
        </div>
        <div class="flex gap-2 pt-2">
          <button
            type="button"
            class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors"
            @click="deleteSelectedNode"
          >
            Delete Node
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import {
  CircleStackIcon,
  MagnifyingGlassPlusIcon,
  MagnifyingGlassMinusIcon,
  ArrowPathIcon,
  ArrowDownTrayIcon,
  XMarkIcon,
  LightBulbIcon,
  StarIcon,
  FlagIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  QuestionMarkCircleIcon,
  BookmarkIcon,
  HeartIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'Mind Map' },
  initialNodes: {
    type: Array,
    default: () => [
      { id: 'root', label: 'Main Idea', x: 400, y: 250, color: 'blue', icon: 'star' }
    ]
  },
  initialConnections: { type: Array, default: () => [] },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true }
})

const emit = defineEmits(['update:nodes', 'update:connections', 'nodeSelect', 'nodeAdd', 'nodeDelete'])

const canvasRef = ref(null)
const editInputRef = ref(null)
const nodes = ref([...props.initialNodes])
const connections = ref([...props.initialConnections])
const selectedNode = ref(null)
const editingNode = ref(null)
const editingText = ref('')
const showNodePanel = ref(false)
const zoom = ref(1)
const panOffset = ref({ x: 0, y: 0 })
const isPanning = ref(false)
const panStart = ref({ x: 0, y: 0 })
const isDragging = ref(false)
const dragNode = ref(null)
const dragOffset = ref({ x: 0, y: 0 })
const isConnecting = ref(false)
const connectionStartNode = ref(null)
const connectionStart = ref({ x: 0, y: 0 })
const connectionEnd = ref({ x: 0, y: 0 })

const nodeColors = ['blue', 'green', 'yellow', 'red', 'purple', 'pink', 'gray']

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-gray-50 border-gray-200'
)

const getNodeColorClasses = (color) => {
  const colors = {
    blue: 'bg-blue-50 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700 text-blue-900 dark:text-blue-100',
    green: 'bg-green-50 dark:bg-green-900/30 border-green-300 dark:border-green-700 text-green-900 dark:text-green-100',
    yellow: 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700 text-yellow-900 dark:text-yellow-100',
    red: 'bg-red-50 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-900 dark:text-red-100',
    purple: 'bg-purple-50 dark:bg-purple-900/30 border-purple-300 dark:border-purple-700 text-purple-900 dark:text-purple-100',
    pink: 'bg-pink-50 dark:bg-pink-900/30 border-pink-300 dark:border-pink-700 text-pink-900 dark:text-pink-100',
    gray: 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100'
  }
  return colors[color] || colors.blue
}

const getColorHex = (color) => {
  const colors = {
    blue: '#3b82f6', green: '#22c55e', yellow: '#eab308',
    red: '#ef4444', purple: '#a855f7', pink: '#ec4899', gray: '#6b7280'
  }
  return colors[color] || '#3b82f6'
}

const getNodeIcon = (icon) => {
  const icons = {
    star: StarIcon, bulb: LightBulbIcon, flag: FlagIcon,
    check: CheckCircleIcon, warning: ExclamationCircleIcon,
    question: QuestionMarkCircleIcon, bookmark: BookmarkIcon, heart: HeartIcon
  }
  return icons[icon] || StarIcon
}

const getConnectionPath = (connection) => {
  const fromNode = nodes.value.find(n => n.id === connection.from)
  const toNode = nodes.value.find(n => n.id === connection.to)
  if (!fromNode || !toNode) return ''
  
  const x1 = fromNode.x + 60
  const y1 = fromNode.y + 30
  const x2 = toNode.x + 60
  const y2 = toNode.y + 30
  
  const midX = (x1 + x2) / 2
  return `M ${x1} ${y1} C ${midX} ${y1}, ${midX} ${y2}, ${x2} ${y2}`
}

const zoomIn = () => { zoom.value = Math.min(2, zoom.value + 0.1) }
const zoomOut = () => { zoom.value = Math.max(0.3, zoom.value - 0.1) }
const resetView = () => { zoom.value = 1; panOffset.value = { x: 0, y: 0 } }

const startPan = (e) => {
  if (e.target === canvasRef.value || e.target.tagName === 'svg') {
    isPanning.value = true
    panStart.value = { x: e.clientX - panOffset.value.x, y: e.clientY - panOffset.value.y }
  }
}

const onPan = (e) => {
  if (isPanning.value) {
    panOffset.value = { x: e.clientX - panStart.value.x, y: e.clientY - panStart.value.y }
  }
  if (isDragging.value && dragNode.value) {
    const rect = canvasRef.value.getBoundingClientRect()
    dragNode.value.x = (e.clientX - rect.left - panOffset.value.x) / zoom.value - dragOffset.value.x
    dragNode.value.y = (e.clientY - rect.top - panOffset.value.y) / zoom.value - dragOffset.value.y
  }
  if (isConnecting.value) {
    const rect = canvasRef.value.getBoundingClientRect()
    connectionEnd.value = {
      x: (e.clientX - rect.left - panOffset.value.x) / zoom.value,
      y: (e.clientY - rect.top - panOffset.value.y) / zoom.value
    }
  }
}

const endPan = (e) => {
  isPanning.value = false
  if (isDragging.value) {
    isDragging.value = false
    dragNode.value = null
    emit('update:nodes', nodes.value)
  }
  if (isConnecting.value) {
    const rect = canvasRef.value.getBoundingClientRect()
    const x = (e.clientX - rect.left - panOffset.value.x) / zoom.value
    const y = (e.clientY - rect.top - panOffset.value.y) / zoom.value
    const targetNode = nodes.value.find(n => 
      x >= n.x && x <= n.x + 120 && y >= n.y && y <= n.y + 60 && n.id !== connectionStartNode.value?.id
    )
    if (targetNode) {
      const exists = connections.value.some(c => 
        (c.from === connectionStartNode.value.id && c.to === targetNode.id) ||
        (c.from === targetNode.id && c.to === connectionStartNode.value.id)
      )
      if (!exists) {
        connections.value.push({ from: connectionStartNode.value.id, to: targetNode.id })
        emit('update:connections', connections.value)
      }
    }
    isConnecting.value = false
    connectionStartNode.value = null
  }
}

const onWheel = (e) => {
  e.preventDefault()
  const delta = e.deltaY > 0 ? -0.1 : 0.1
  zoom.value = Math.max(0.3, Math.min(2, zoom.value + delta))
}

const startDragNode = (e, node) => {
  isDragging.value = true
  dragNode.value = node
  const rect = canvasRef.value.getBoundingClientRect()
  dragOffset.value = {
    x: (e.clientX - rect.left - panOffset.value.x) / zoom.value - node.x,
    y: (e.clientY - rect.top - panOffset.value.y) / zoom.value - node.y
  }
}

const startConnection = (e, node) => {
  isConnecting.value = true
  connectionStartNode.value = node
  connectionStart.value = { x: node.x + 120, y: node.y + 30 }
  connectionEnd.value = { ...connectionStart.value }
}

const selectNode = (node) => {
  selectedNode.value = node
  showNodePanel.value = true
  emit('nodeSelect', node)
}

const editNode = (node) => {
  editingNode.value = node
  editingText.value = node.label
  nextTick(() => editInputRef.value?.focus())
}

const saveNodeEdit = () => {
  if (editingNode.value && editingText.value.trim()) {
    editingNode.value.label = editingText.value.trim()
    emit('update:nodes', nodes.value)
  }
  editingNode.value = null
  editingText.value = ''
}

const cancelNodeEdit = () => {
  editingNode.value = null
  editingText.value = ''
}

const addNodeAtPosition = (e) => {
  const rect = canvasRef.value.getBoundingClientRect()
  const x = (e.clientX - rect.left - panOffset.value.x) / zoom.value - 60
  const y = (e.clientY - rect.top - panOffset.value.y) / zoom.value - 30
  
  const newNode = {
    id: `node-${Date.now()}`,
    label: 'New Idea',
    x,
    y,
    color: 'blue'
  }
  nodes.value.push(newNode)
  emit('nodeAdd', newNode)
  emit('update:nodes', nodes.value)
  
  editNode(newNode)
}

const deleteSelectedNode = () => {
  if (!selectedNode.value) return
  const nodeId = selectedNode.value.id
  nodes.value = nodes.value.filter(n => n.id !== nodeId)
  connections.value = connections.value.filter(c => c.from !== nodeId && c.to !== nodeId)
  emit('nodeDelete', selectedNode.value)
  emit('update:nodes', nodes.value)
  emit('update:connections', connections.value)
  selectedNode.value = null
  showNodePanel.value = false
}

const exportMap = () => {
  const data = { nodes: nodes.value, connections: connections.value }
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'mindmap.json'
  a.click()
  URL.revokeObjectURL(url)
}
</script>

<style scoped>
.stroke-dasharray-4 { stroke-dasharray: 4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
