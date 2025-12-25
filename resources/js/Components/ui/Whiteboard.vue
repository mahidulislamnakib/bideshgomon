<template>
  <div :class="['whiteboard rounded-lg overflow-hidden border', containerClasses]">
    <!-- Toolbar -->
    <div :class="['flex items-center justify-between px-3 py-2 border-b', themeClasses.toolbar]">
      <!-- Tools -->
      <div class="flex items-center gap-1">
        <button
          v-for="tool in tools"
          :key="tool.id"
          @click="currentTool = tool.id"
          :class="[
            'p-2 rounded-lg transition-colors',
            currentTool === tool.id ? themeClasses.toolActive : themeClasses.tool
          ]"
          :title="tool.label"
        >
          <component :is="tool.icon" class="w-5 h-5" />
        </button>

        <div class="w-px h-6 bg-gray-300 mx-2" />

        <!-- Stroke Width -->
        <select
          v-model="strokeWidth"
          :class="['text-sm px-2 py-1 rounded border', themeClasses.select]"
        >
          <option :value="2">Thin</option>
          <option :value="4">Medium</option>
          <option :value="8">Thick</option>
          <option :value="16">Bold</option>
        </select>

        <!-- Color Picker -->
        <div class="relative">
          <button
            @click="showColorPicker = !showColorPicker"
            class="w-8 h-8 rounded-lg border-2 border-white shadow-sm"
            :style="{ backgroundColor: currentColor }"
          />
          <div
            v-if="showColorPicker"
            :class="['absolute top-full left-0 mt-2 p-2 rounded-lg shadow-lg z-10 grid grid-cols-5 gap-1', themeClasses.colorPicker]"
          >
            <button
              v-for="color in colors"
              :key="color"
              @click="currentColor = color; showColorPicker = false"
              class="w-6 h-6 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform"
              :style="{ backgroundColor: color }"
            />
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1">
        <button
          @click="undo"
          :disabled="historyIndex <= 0"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Undo"
        >
          <ArrowUturnLeftIcon class="w-5 h-5" />
        </button>
        <button
          @click="redo"
          :disabled="historyIndex >= history.length - 1"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Redo"
        >
          <ArrowUturnRightIcon class="w-5 h-5" />
        </button>

        <div class="w-px h-6 bg-gray-300 mx-2" />

        <button
          @click="clearCanvas"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Clear"
        >
          <TrashIcon class="w-5 h-5" />
        </button>
        <button
          @click="downloadImage"
          :class="['p-2 rounded-lg transition-colors', themeClasses.action]"
          title="Download"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Canvas Area -->
    <div
      ref="canvasContainerRef"
      :class="['relative overflow-hidden', themeClasses.canvas]"
      :style="{ height: `${height}px` }"
    >
      <!-- Grid Background -->
      <div
        v-if="showGrid"
        class="absolute inset-0 pointer-events-none"
        :style="gridStyle"
      />

      <!-- SVG Canvas -->
      <svg
        ref="svgRef"
        class="absolute inset-0 w-full h-full"
        :viewBox="`0 0 ${canvasWidth} ${canvasHeight}`"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart.prevent="handleTouchStart"
        @touchmove.prevent="handleTouchMove"
        @touchend.prevent="stopDrawing"
      >
        <!-- Rendered Elements -->
        <g v-for="(el, i) in elements" :key="i">
          <!-- Freehand Path -->
          <path
            v-if="el.type === 'path'"
            :d="el.path"
            :stroke="el.color"
            :stroke-width="el.strokeWidth"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
          />

          <!-- Line -->
          <line
            v-else-if="el.type === 'line'"
            :x1="el.x1"
            :y1="el.y1"
            :x2="el.x2"
            :y2="el.y2"
            :stroke="el.color"
            :stroke-width="el.strokeWidth"
            stroke-linecap="round"
          />

          <!-- Rectangle -->
          <rect
            v-else-if="el.type === 'rect'"
            :x="Math.min(el.x1, el.x2)"
            :y="Math.min(el.y1, el.y2)"
            :width="Math.abs(el.x2 - el.x1)"
            :height="Math.abs(el.y2 - el.y1)"
            :stroke="el.color"
            :stroke-width="el.strokeWidth"
            :fill="el.filled ? el.color : 'none'"
            :fill-opacity="el.filled ? 0.2 : 0"
          />

          <!-- Circle/Ellipse -->
          <ellipse
            v-else-if="el.type === 'circle'"
            :cx="(el.x1 + el.x2) / 2"
            :cy="(el.y1 + el.y2) / 2"
            :rx="Math.abs(el.x2 - el.x1) / 2"
            :ry="Math.abs(el.y2 - el.y1) / 2"
            :stroke="el.color"
            :stroke-width="el.strokeWidth"
            :fill="el.filled ? el.color : 'none'"
            :fill-opacity="el.filled ? 0.2 : 0"
          />

          <!-- Arrow -->
          <g v-else-if="el.type === 'arrow'">
            <line
              :x1="el.x1"
              :y1="el.y1"
              :x2="el.x2"
              :y2="el.y2"
              :stroke="el.color"
              :stroke-width="el.strokeWidth"
              stroke-linecap="round"
            />
            <polygon
              :points="getArrowHead(el)"
              :fill="el.color"
            />
          </g>

          <!-- Text -->
          <text
            v-else-if="el.type === 'text'"
            :x="el.x"
            :y="el.y"
            :fill="el.color"
            :font-size="el.fontSize || 16"
            class="select-none"
          >
            {{ el.content }}
          </text>
        </g>

        <!-- Current Drawing Preview -->
        <g v-if="isDrawing && currentElement">
          <path
            v-if="currentElement.type === 'path'"
            :d="currentElement.path"
            :stroke="currentElement.color"
            :stroke-width="currentElement.strokeWidth"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
            opacity="0.8"
          />
          <line
            v-else-if="currentElement.type === 'line'"
            :x1="currentElement.x1"
            :y1="currentElement.y1"
            :x2="currentElement.x2"
            :y2="currentElement.y2"
            :stroke="currentElement.color"
            :stroke-width="currentElement.strokeWidth"
            stroke-linecap="round"
            opacity="0.8"
          />
          <rect
            v-else-if="currentElement.type === 'rect'"
            :x="Math.min(currentElement.x1, currentElement.x2)"
            :y="Math.min(currentElement.y1, currentElement.y2)"
            :width="Math.abs(currentElement.x2 - currentElement.x1)"
            :height="Math.abs(currentElement.y2 - currentElement.y1)"
            :stroke="currentElement.color"
            :stroke-width="currentElement.strokeWidth"
            fill="none"
            opacity="0.8"
          />
          <ellipse
            v-else-if="currentElement.type === 'circle'"
            :cx="(currentElement.x1 + currentElement.x2) / 2"
            :cy="(currentElement.y1 + currentElement.y2) / 2"
            :rx="Math.abs(currentElement.x2 - currentElement.x1) / 2"
            :ry="Math.abs(currentElement.y2 - currentElement.y1) / 2"
            :stroke="currentElement.color"
            :stroke-width="currentElement.strokeWidth"
            fill="none"
            opacity="0.8"
          />
        </g>
      </svg>

      <!-- Text Input Overlay -->
      <input
        v-if="showTextInput"
        ref="textInputRef"
        v-model="textInput"
        type="text"
        :class="['absolute px-2 py-1 text-sm border-2 border-blue-500 rounded outline-none', themeClasses.textInput]"
        :style="textInputStyle"
        @keydown.enter="finishTextInput"
        @keydown.escape="cancelTextInput"
        @blur="finishTextInput"
      />
    </div>

    <!-- Status Bar -->
    <div v-if="showStatusBar" :class="['flex items-center justify-between px-3 py-1 text-xs border-t', themeClasses.statusBar]">
      <div class="flex items-center gap-4">
        <span>Tool: {{ currentToolLabel }}</span>
        <span>Size: {{ strokeWidth }}px</span>
      </div>
      <div class="flex items-center gap-4">
        <span>{{ elements.length }} objects</span>
        <span>{{ canvasWidth }} × {{ canvasHeight }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import {
  PencilIcon,
  MinusIcon,
  Square2StackIcon,
  ArrowUpRightIcon,
  TrashIcon,
  ArrowUturnLeftIcon,
  ArrowUturnRightIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

// Custom icons
const CircleIcon = {
  render: () => {
    const h = require('vue').h
    return h('svg', { class: 'w-5 h-5', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('circle', { cx: 12, cy: 12, r: 9 })
    ])
  }
}

const TextIcon = {
  render: () => {
    const h = require('vue').h
    return h('svg', { class: 'w-5 h-5', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('text', { x: 6, y: 18, 'font-size': 16, fill: 'currentColor', stroke: 'none' }, 'T')
    ])
  }
}

const EraserIcon = {
  render: () => {
    const h = require('vue').h
    return h('svg', { class: 'w-5 h-5', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('path', { d: 'M20 20H9L3.5 14.5a2.12 2.12 0 0 1 0-3l9-9a2.12 2.12 0 0 1 3 0l5 5a2.12 2.12 0 0 1 0 3L13 18' })
    ])
  }
}

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
  gridSize: {
    type: Number,
    default: 20
  }
})

const emit = defineEmits(['change', 'save'])

const canvasContainerRef = ref(null)
const svgRef = ref(null)
const textInputRef = ref(null)

const canvasWidth = ref(800)
const canvasHeight = ref(500)
const currentTool = ref('pen')
const currentColor = ref('#000000')
const strokeWidth = ref(4)
const isDrawing = ref(false)
const currentElement = ref(null)
const elements = ref([])
const history = ref([[]])
const historyIndex = ref(0)
const showColorPicker = ref(false)
const showTextInput = ref(false)
const textInput = ref('')
const textInputPosition = ref({ x: 0, y: 0 })

const tools = [
  { id: 'pen', label: 'Pen', icon: PencilIcon },
  { id: 'line', label: 'Line', icon: MinusIcon },
  { id: 'rect', label: 'Rectangle', icon: Square2StackIcon },
  { id: 'circle', label: 'Circle', icon: CircleIcon },
  { id: 'arrow', label: 'Arrow', icon: ArrowUpRightIcon },
  { id: 'text', label: 'Text', icon: TextIcon },
  { id: 'eraser', label: 'Eraser', icon: EraserIcon }
]

const colors = [
  '#000000', '#FFFFFF', '#EF4444', '#F97316', '#EAB308',
  '#22C55E', '#14B8A6', '#3B82F6', '#8B5CF6', '#EC4899'
]

const currentToolLabel = computed(() => {
  return tools.find(t => t.id === currentTool.value)?.label || 'Unknown'
})

const gridStyle = computed(() => {
  const size = props.gridSize
  const color = props.theme === 'dark' ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
  return {
    backgroundImage: `linear-gradient(${color} 1px, transparent 1px), linear-gradient(90deg, ${color} 1px, transparent 1px)`,
    backgroundSize: `${size}px ${size}px`
  }
})

const textInputStyle = computed(() => ({
  left: `${textInputPosition.value.x}px`,
  top: `${textInputPosition.value.y}px`
}))

const getMousePos = (e) => {
  const rect = svgRef.value.getBoundingClientRect()
  const scaleX = canvasWidth.value / rect.width
  const scaleY = canvasHeight.value / rect.height
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top) * scaleY
  }
}

const startDrawing = (e) => {
  if (currentTool.value === 'text') {
    const pos = getMousePos(e)
    textInputPosition.value = { x: e.offsetX, y: e.offsetY }
    showTextInput.value = true
    nextTick(() => textInputRef.value?.focus())
    return
  }

  isDrawing.value = true
  const pos = getMousePos(e)

  if (currentTool.value === 'pen' || currentTool.value === 'eraser') {
    currentElement.value = {
      type: 'path',
      path: `M ${pos.x} ${pos.y}`,
      color: currentTool.value === 'eraser' ? (props.theme === 'dark' ? '#1F2937' : '#FFFFFF') : currentColor.value,
      strokeWidth: currentTool.value === 'eraser' ? strokeWidth.value * 3 : strokeWidth.value,
      points: [pos]
    }
  } else {
    currentElement.value = {
      type: currentTool.value,
      x1: pos.x,
      y1: pos.y,
      x2: pos.x,
      y2: pos.y,
      color: currentColor.value,
      strokeWidth: strokeWidth.value
    }
  }
}

const draw = (e) => {
  if (!isDrawing.value || !currentElement.value) return

  const pos = getMousePos(e)

  if (currentElement.value.type === 'path') {
    currentElement.value.points.push(pos)
    currentElement.value.path += ` L ${pos.x} ${pos.y}`
  } else {
    currentElement.value.x2 = pos.x
    currentElement.value.y2 = pos.y
  }
}

const stopDrawing = () => {
  if (isDrawing.value && currentElement.value) {
    elements.value.push({ ...currentElement.value })
    saveToHistory()
  }
  isDrawing.value = false
  currentElement.value = null
}

const handleTouchStart = (e) => {
  const touch = e.touches[0]
  const rect = svgRef.value.getBoundingClientRect()
  startDrawing({
    clientX: touch.clientX,
    clientY: touch.clientY,
    offsetX: touch.clientX - rect.left,
    offsetY: touch.clientY - rect.top
  })
}

const handleTouchMove = (e) => {
  const touch = e.touches[0]
  draw({ clientX: touch.clientX, clientY: touch.clientY })
}

const finishTextInput = () => {
  if (textInput.value.trim()) {
    const rect = svgRef.value.getBoundingClientRect()
    elements.value.push({
      type: 'text',
      x: (textInputPosition.value.x / rect.width) * canvasWidth.value,
      y: (textInputPosition.value.y / rect.height) * canvasHeight.value,
      content: textInput.value,
      color: currentColor.value,
      fontSize: 16 + strokeWidth.value * 2
    })
    saveToHistory()
  }
  showTextInput.value = false
  textInput.value = ''
}

const cancelTextInput = () => {
  showTextInput.value = false
  textInput.value = ''
}

const getArrowHead = (el) => {
  const angle = Math.atan2(el.y2 - el.y1, el.x2 - el.x1)
  const size = el.strokeWidth * 3
  const x = el.x2
  const y = el.y2
  
  const p1x = x - size * Math.cos(angle - Math.PI / 6)
  const p1y = y - size * Math.sin(angle - Math.PI / 6)
  const p2x = x - size * Math.cos(angle + Math.PI / 6)
  const p2y = y - size * Math.sin(angle + Math.PI / 6)
  
  return `${x},${y} ${p1x},${p1y} ${p2x},${p2y}`
}

const saveToHistory = () => {
  history.value = history.value.slice(0, historyIndex.value + 1)
  history.value.push(JSON.parse(JSON.stringify(elements.value)))
  historyIndex.value = history.value.length - 1
  emit('change', elements.value)
}

const undo = () => {
  if (historyIndex.value > 0) {
    historyIndex.value--
    elements.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]))
  }
}

const redo = () => {
  if (historyIndex.value < history.value.length - 1) {
    historyIndex.value++
    elements.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]))
  }
}

const clearCanvas = () => {
  elements.value = []
  saveToHistory()
}

const downloadImage = () => {
  const svg = svgRef.value
  const svgData = new XMLSerializer().serializeToString(svg)
  const canvas = document.createElement('canvas')
  canvas.width = canvasWidth.value
  canvas.height = canvasHeight.value
  const ctx = canvas.getContext('2d')
  
  // Fill background
  ctx.fillStyle = props.theme === 'dark' ? '#1F2937' : '#FFFFFF'
  ctx.fillRect(0, 0, canvas.width, canvas.height)
  
  const img = new Image()
  img.onload = () => {
    ctx.drawImage(img, 0, 0)
    const link = document.createElement('a')
    link.download = 'whiteboard.png'
    link.href = canvas.toDataURL('image/png')
    link.click()
  }
  img.src = 'data:image/svg+xml;base64,' + btoa(svgData)
  emit('save', elements.value)
}

const containerClasses = computed(() => {
  return props.theme === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      toolbar: 'bg-gray-800 border-gray-700',
      tool: 'text-gray-400 hover:bg-gray-700',
      toolActive: 'bg-blue-600 text-white',
      select: 'bg-gray-700 border-gray-600 text-white',
      colorPicker: 'bg-gray-800 border border-gray-700',
      action: 'text-gray-400 hover:bg-gray-700 disabled:opacity-50',
      canvas: 'bg-gray-800',
      textInput: 'bg-gray-700 text-white',
      statusBar: 'bg-gray-800 border-gray-700 text-gray-400'
    }
  }
  return {
    toolbar: 'bg-gray-50 border-gray-200',
    tool: 'text-gray-600 hover:bg-gray-200',
    toolActive: 'bg-blue-500 text-white',
    select: 'bg-white border-gray-300 text-gray-900',
    colorPicker: 'bg-white border border-gray-200',
    action: 'text-gray-600 hover:bg-gray-200 disabled:opacity-50',
    canvas: 'bg-white',
    textInput: 'bg-white text-gray-900',
    statusBar: 'bg-gray-50 border-gray-200 text-gray-600'
  }
})

onMounted(() => {
  if (canvasContainerRef.value) {
    canvasWidth.value = canvasContainerRef.value.offsetWidth
    canvasHeight.value = props.height
  }
})

defineExpose({
  clear: clearCanvas,
  undo,
  redo,
  download: downloadImage,
  getElements: () => [...elements.value],
  setElements: (els) => {
    elements.value = els
    saveToHistory()
  }
})
</script>
