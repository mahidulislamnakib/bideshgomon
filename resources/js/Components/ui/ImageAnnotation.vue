<template>
  <div :class="['image-annotation relative', containerClasses]">
    <!-- Toolbar -->
    <div
      v-if="showToolbar"
      :class="['flex items-center justify-between px-3 py-2 border-b', themeClasses.toolbar]"
    >
      <div class="flex items-center gap-1">
        <!-- Tool Selection -->
        <button
          v-for="tool in tools"
          :key="tool.id"
          type="button"
          :title="tool.title"
          :class="[
            'p-2 rounded transition-colors',
            activeTool === tool.id ? themeClasses.toolActive : themeClasses.tool
          ]"
          @click="activeTool = tool.id"
        >
          <component :is="tool.icon" class="w-4 h-4" />
        </button>

        <div :class="['w-px h-6 mx-2', themeClasses.divider]" />

        <!-- Color Picker -->
        <div class="relative">
          <button
            type="button"
            title="Annotation Color"
            :class="['p-2 rounded transition-colors flex items-center gap-1', themeClasses.tool]"
            @click="showColorPicker = !showColorPicker"
          >
            <div
              class="w-4 h-4 rounded border border-gray-300"
              :style="{ backgroundColor: currentColor }"
            />
            <ChevronDownIcon class="w-3 h-3" />
          </button>
          
          <Transition
            enter-active-class="transition-all duration-150"
            enter-from-class="opacity-0 scale-95"
            leave-active-class="transition-all duration-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="showColorPicker"
              :class="['absolute top-full left-0 mt-1 p-2 rounded-lg shadow-xl z-10 grid grid-cols-5 gap-1', themeClasses.dropdown]"
            >
              <button
                v-for="color in colors"
                :key="color"
                type="button"
                class="w-6 h-6 rounded border-2 transition-transform hover:scale-110"
                :class="currentColor === color ? 'border-blue-500' : 'border-transparent'"
                :style="{ backgroundColor: color }"
                @click="currentColor = color; showColorPicker = false"
              />
            </div>
          </Transition>
        </div>

        <!-- Stroke Width -->
        <div class="flex items-center gap-1 ml-2">
          <button
            v-for="width in [2, 4, 6]"
            :key="width"
            type="button"
            :title="`Stroke width: ${width}px`"
            :class="[
              'p-1.5 rounded transition-colors',
              strokeWidth === width ? themeClasses.toolActive : themeClasses.tool
            ]"
            @click="strokeWidth = width"
          >
            <div
              class="w-4 rounded-full bg-current"
              :style="{ height: `${width}px` }"
            />
          </button>
        </div>
      </div>

      <div class="flex items-center gap-1">
        <!-- Undo/Redo -->
        <button
          type="button"
          title="Undo (Ctrl+Z)"
          :disabled="historyIndex <= 0"
          :class="['p-2 rounded transition-colors', themeClasses.tool, historyIndex <= 0 && 'opacity-50 cursor-not-allowed']"
          @click="undo"
        >
          <ArrowUturnLeftIcon class="w-4 h-4" />
        </button>
        <button
          type="button"
          title="Redo (Ctrl+Y)"
          :disabled="historyIndex >= history.length - 1"
          :class="['p-2 rounded transition-colors', themeClasses.tool, historyIndex >= history.length - 1 && 'opacity-50 cursor-not-allowed']"
          @click="redo"
        >
          <ArrowUturnRightIcon class="w-4 h-4" />
        </button>

        <div :class="['w-px h-6 mx-2', themeClasses.divider]" />

        <!-- Clear All -->
        <button
          type="button"
          title="Clear All"
          :class="['p-2 rounded transition-colors text-red-500 hover:bg-red-50']"
          @click="clearAll"
        >
          <TrashIcon class="w-4 h-4" />
        </button>

        <!-- Export -->
        <button
          type="button"
          title="Export Image"
          :class="['p-2 rounded transition-colors', themeClasses.tool]"
          @click="exportImage"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Canvas Area -->
    <div
      ref="canvasContainer"
      :class="['relative overflow-hidden', !showToolbar && 'rounded-t-lg']"
      :style="{ cursor: getCursor }"
    >
      <!-- Source Image -->
      <img
        ref="imageRef"
        :src="src"
        :alt="alt"
        class="max-w-full h-auto"
        @load="onImageLoad"
      />

      <!-- SVG Overlay for Annotations -->
      <svg
        ref="svgRef"
        class="absolute inset-0 w-full h-full"
        :viewBox="`0 0 ${imageWidth} ${imageHeight}`"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart.prevent="handleTouchStart"
        @touchmove.prevent="handleTouchMove"
        @touchend.prevent="stopDrawing"
      >
        <!-- Existing Annotations -->
        <g v-for="(annotation, index) in annotations" :key="index">
          <!-- Rectangle -->
          <rect
            v-if="annotation.type === 'rectangle'"
            :x="Math.min(annotation.startX, annotation.endX)"
            :y="Math.min(annotation.startY, annotation.endY)"
            :width="Math.abs(annotation.endX - annotation.startX)"
            :height="Math.abs(annotation.endY - annotation.startY)"
            :stroke="annotation.color"
            :stroke-width="annotation.strokeWidth"
            fill="none"
            class="pointer-events-none"
          />

          <!-- Circle -->
          <ellipse
            v-if="annotation.type === 'circle'"
            :cx="(annotation.startX + annotation.endX) / 2"
            :cy="(annotation.startY + annotation.endY) / 2"
            :rx="Math.abs(annotation.endX - annotation.startX) / 2"
            :ry="Math.abs(annotation.endY - annotation.startY) / 2"
            :stroke="annotation.color"
            :stroke-width="annotation.strokeWidth"
            fill="none"
            class="pointer-events-none"
          />

          <!-- Arrow -->
          <g v-if="annotation.type === 'arrow'">
            <defs>
              <marker
                :id="`arrowhead-${index}`"
                markerWidth="10"
                markerHeight="7"
                refX="9"
                refY="3.5"
                orient="auto"
              >
                <polygon
                  points="0 0, 10 3.5, 0 7"
                  :fill="annotation.color"
                />
              </marker>
            </defs>
            <line
              :x1="annotation.startX"
              :y1="annotation.startY"
              :x2="annotation.endX"
              :y2="annotation.endY"
              :stroke="annotation.color"
              :stroke-width="annotation.strokeWidth"
              :marker-end="`url(#arrowhead-${index})`"
              class="pointer-events-none"
            />
          </g>

          <!-- Line -->
          <line
            v-if="annotation.type === 'line'"
            :x1="annotation.startX"
            :y1="annotation.startY"
            :x2="annotation.endX"
            :y2="annotation.endY"
            :stroke="annotation.color"
            :stroke-width="annotation.strokeWidth"
            class="pointer-events-none"
          />

          <!-- Freehand -->
          <path
            v-if="annotation.type === 'freehand'"
            :d="annotation.path"
            :stroke="annotation.color"
            :stroke-width="annotation.strokeWidth"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="pointer-events-none"
          />

          <!-- Text -->
          <g v-if="annotation.type === 'text'" class="pointer-events-none">
            <rect
              :x="annotation.x - 4"
              :y="annotation.y - 16"
              :width="annotation.text.length * 8 + 8"
              height="22"
              :fill="annotation.color"
              rx="4"
            />
            <text
              :x="annotation.x"
              :y="annotation.y"
              fill="white"
              font-size="14"
              font-family="sans-serif"
            >
              {{ annotation.text }}
            </text>
          </g>

          <!-- Highlight -->
          <rect
            v-if="annotation.type === 'highlight'"
            :x="Math.min(annotation.startX, annotation.endX)"
            :y="Math.min(annotation.startY, annotation.endY)"
            :width="Math.abs(annotation.endX - annotation.startX)"
            :height="Math.abs(annotation.endY - annotation.startY)"
            :fill="annotation.color"
            opacity="0.3"
            class="pointer-events-none"
          />
        </g>

        <!-- Current Drawing -->
        <g v-if="isDrawing && currentAnnotation">
          <rect
            v-if="currentAnnotation.type === 'rectangle'"
            :x="Math.min(currentAnnotation.startX, currentAnnotation.endX)"
            :y="Math.min(currentAnnotation.startY, currentAnnotation.endY)"
            :width="Math.abs(currentAnnotation.endX - currentAnnotation.startX)"
            :height="Math.abs(currentAnnotation.endY - currentAnnotation.startY)"
            :stroke="currentAnnotation.color"
            :stroke-width="currentAnnotation.strokeWidth"
            fill="none"
            stroke-dasharray="5,5"
          />

          <ellipse
            v-if="currentAnnotation.type === 'circle'"
            :cx="(currentAnnotation.startX + currentAnnotation.endX) / 2"
            :cy="(currentAnnotation.startY + currentAnnotation.endY) / 2"
            :rx="Math.abs(currentAnnotation.endX - currentAnnotation.startX) / 2"
            :ry="Math.abs(currentAnnotation.endY - currentAnnotation.startY) / 2"
            :stroke="currentAnnotation.color"
            :stroke-width="currentAnnotation.strokeWidth"
            fill="none"
            stroke-dasharray="5,5"
          />

          <line
            v-if="currentAnnotation.type === 'arrow' || currentAnnotation.type === 'line'"
            :x1="currentAnnotation.startX"
            :y1="currentAnnotation.startY"
            :x2="currentAnnotation.endX"
            :y2="currentAnnotation.endY"
            :stroke="currentAnnotation.color"
            :stroke-width="currentAnnotation.strokeWidth"
            stroke-dasharray="5,5"
          />

          <path
            v-if="currentAnnotation.type === 'freehand'"
            :d="currentAnnotation.path"
            :stroke="currentAnnotation.color"
            :stroke-width="currentAnnotation.strokeWidth"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
          />

          <rect
            v-if="currentAnnotation.type === 'highlight'"
            :x="Math.min(currentAnnotation.startX, currentAnnotation.endX)"
            :y="Math.min(currentAnnotation.startY, currentAnnotation.endY)"
            :width="Math.abs(currentAnnotation.endX - currentAnnotation.startX)"
            :height="Math.abs(currentAnnotation.endY - currentAnnotation.startY)"
            :fill="currentAnnotation.color"
            opacity="0.3"
          />
        </g>
      </svg>

      <!-- Text Input Modal -->
      <div
        v-if="showTextInput"
        class="absolute bg-white rounded-lg shadow-xl p-3 z-20"
        :style="{ left: `${textInputPosition.x}px`, top: `${textInputPosition.y}px` }"
      >
        <input
          ref="textInputRef"
          v-model="textInputValue"
          type="text"
          placeholder="Enter text..."
          class="w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
          @keydown.enter="addTextAnnotation"
          @keydown.escape="showTextInput = false"
        />
        <div class="flex justify-end gap-2 mt-2">
          <button
            type="button"
            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900"
            @click="showTextInput = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600"
            @click="addTextAnnotation"
          >
            Add
          </button>
        </div>
      </div>
    </div>

    <!-- Annotations List -->
    <div
      v-if="showAnnotationsList && annotations.length > 0"
      :class="['border-t p-3', themeClasses.list]"
    >
      <h4 :class="['text-sm font-medium mb-2', themeClasses.listTitle]">
        Annotations ({{ annotations.length }})
      </h4>
      <div class="space-y-1 max-h-32 overflow-y-auto">
        <div
          v-for="(annotation, index) in annotations"
          :key="index"
          :class="['flex items-center justify-between px-2 py-1 rounded text-sm', themeClasses.listItem]"
        >
          <span class="flex items-center gap-2">
            <span
              class="w-3 h-3 rounded-full"
              :style="{ backgroundColor: annotation.color }"
            />
            {{ annotation.type }} #{{ index + 1 }}
          </span>
          <button
            type="button"
            class="p-1 text-red-500 hover:text-red-700"
            @click="removeAnnotation(index)"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { Transition } from 'vue'
import {
  CursorArrowRaysIcon,
  Square2StackIcon,
  PencilIcon,
  ArrowLongRightIcon,
  MinusIcon,
  ChatBubbleLeftIcon,
  SunIcon,
  ChevronDownIcon,
  ArrowUturnLeftIcon,
  ArrowUturnRightIcon,
  TrashIcon,
  ArrowDownTrayIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

// Simple shape icons
const RectangleIcon = { template: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/></svg>' }
const CircleIcon = { template: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>' }

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  alt: {
    type: String,
    default: 'Annotatable image'
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  showToolbar: {
    type: Boolean,
    default: true
  },
  showAnnotationsList: {
    type: Boolean,
    default: false
  },
  initialAnnotations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:annotations', 'export', 'change'])

const tools = [
  { id: 'select', icon: CursorArrowRaysIcon, title: 'Select' },
  { id: 'rectangle', icon: RectangleIcon, title: 'Rectangle' },
  { id: 'circle', icon: CircleIcon, title: 'Circle' },
  { id: 'arrow', icon: ArrowLongRightIcon, title: 'Arrow' },
  { id: 'line', icon: MinusIcon, title: 'Line' },
  { id: 'freehand', icon: PencilIcon, title: 'Freehand' },
  { id: 'text', icon: ChatBubbleLeftIcon, title: 'Text' },
  { id: 'highlight', icon: SunIcon, title: 'Highlight' }
]

const colors = [
  '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6',
  '#8b5cf6', '#ec4899', '#000000', '#6b7280', '#ffffff'
]

const canvasContainer = ref(null)
const imageRef = ref(null)
const svgRef = ref(null)
const textInputRef = ref(null)

const activeTool = ref('rectangle')
const currentColor = ref('#ef4444')
const strokeWidth = ref(3)
const showColorPicker = ref(false)

const imageWidth = ref(0)
const imageHeight = ref(0)
const isDrawing = ref(false)
const currentAnnotation = ref(null)
const annotations = ref([...props.initialAnnotations])

const history = ref([[]])
const historyIndex = ref(0)

const showTextInput = ref(false)
const textInputPosition = ref({ x: 0, y: 0 })
const textInputValue = ref('')
const textAnnotationCoords = ref({ x: 0, y: 0 })

const containerClasses = computed(() => {
  return props.theme === 'dark'
    ? 'bg-gray-900 border border-gray-700 rounded-lg'
    : 'bg-white border border-gray-200 rounded-lg'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      toolbar: 'border-gray-700 bg-gray-800',
      tool: 'text-gray-400 hover:text-white hover:bg-gray-700',
      toolActive: 'text-white bg-blue-600',
      divider: 'bg-gray-700',
      dropdown: 'bg-gray-800 border border-gray-700',
      list: 'bg-gray-800 border-gray-700',
      listTitle: 'text-gray-300',
      listItem: 'text-gray-300 hover:bg-gray-700'
    }
  }
  return {
    toolbar: 'border-gray-200 bg-gray-50',
    tool: 'text-gray-600 hover:text-gray-900 hover:bg-gray-200',
    toolActive: 'text-white bg-blue-600',
    divider: 'bg-gray-300',
    dropdown: 'bg-white border border-gray-200',
    list: 'bg-gray-50 border-gray-200',
    listTitle: 'text-gray-700',
    listItem: 'text-gray-700 hover:bg-gray-100'
  }
})

const getCursor = computed(() => {
  if (activeTool.value === 'select') return 'default'
  if (activeTool.value === 'freehand') return 'crosshair'
  if (activeTool.value === 'text') return 'text'
  return 'crosshair'
})

const onImageLoad = () => {
  if (imageRef.value) {
    imageWidth.value = imageRef.value.naturalWidth
    imageHeight.value = imageRef.value.naturalHeight
  }
}

const getMousePosition = (e) => {
  if (!svgRef.value) return { x: 0, y: 0 }
  
  const rect = svgRef.value.getBoundingClientRect()
  const scaleX = imageWidth.value / rect.width
  const scaleY = imageHeight.value / rect.height
  
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top) * scaleY
  }
}

const startDrawing = (e) => {
  if (activeTool.value === 'select') return
  
  const pos = getMousePosition(e)
  
  if (activeTool.value === 'text') {
    textAnnotationCoords.value = pos
    textInputPosition.value = {
      x: e.clientX - canvasContainer.value.getBoundingClientRect().left,
      y: e.clientY - canvasContainer.value.getBoundingClientRect().top
    }
    showTextInput.value = true
    nextTick(() => textInputRef.value?.focus())
    return
  }
  
  isDrawing.value = true
  
  currentAnnotation.value = {
    type: activeTool.value,
    color: currentColor.value,
    strokeWidth: strokeWidth.value,
    startX: pos.x,
    startY: pos.y,
    endX: pos.x,
    endY: pos.y,
    path: activeTool.value === 'freehand' ? `M ${pos.x} ${pos.y}` : undefined
  }
}

const draw = (e) => {
  if (!isDrawing.value || !currentAnnotation.value) return
  
  const pos = getMousePosition(e)
  
  if (currentAnnotation.value.type === 'freehand') {
    currentAnnotation.value.path += ` L ${pos.x} ${pos.y}`
  } else {
    currentAnnotation.value.endX = pos.x
    currentAnnotation.value.endY = pos.y
  }
}

const stopDrawing = () => {
  if (isDrawing.value && currentAnnotation.value) {
    // Only add if there's meaningful drawing
    const dx = Math.abs(currentAnnotation.value.endX - currentAnnotation.value.startX)
    const dy = Math.abs(currentAnnotation.value.endY - currentAnnotation.value.startY)
    
    if (dx > 5 || dy > 5 || currentAnnotation.value.type === 'freehand') {
      annotations.value.push({ ...currentAnnotation.value })
      saveToHistory()
      emit('update:annotations', annotations.value)
      emit('change', annotations.value)
    }
  }
  
  isDrawing.value = false
  currentAnnotation.value = null
}

const handleTouchStart = (e) => {
  const touch = e.touches[0]
  startDrawing({ clientX: touch.clientX, clientY: touch.clientY })
}

const handleTouchMove = (e) => {
  const touch = e.touches[0]
  draw({ clientX: touch.clientX, clientY: touch.clientY })
}

const addTextAnnotation = () => {
  if (!textInputValue.value.trim()) {
    showTextInput.value = false
    return
  }
  
  annotations.value.push({
    type: 'text',
    color: currentColor.value,
    x: textAnnotationCoords.value.x,
    y: textAnnotationCoords.value.y,
    text: textInputValue.value.trim()
  })
  
  saveToHistory()
  emit('update:annotations', annotations.value)
  emit('change', annotations.value)
  
  textInputValue.value = ''
  showTextInput.value = false
}

const removeAnnotation = (index) => {
  annotations.value.splice(index, 1)
  saveToHistory()
  emit('update:annotations', annotations.value)
  emit('change', annotations.value)
}

const saveToHistory = () => {
  history.value = history.value.slice(0, historyIndex.value + 1)
  history.value.push([...annotations.value])
  historyIndex.value = history.value.length - 1
}

const undo = () => {
  if (historyIndex.value > 0) {
    historyIndex.value--
    annotations.value = [...history.value[historyIndex.value]]
    emit('update:annotations', annotations.value)
    emit('change', annotations.value)
  }
}

const redo = () => {
  if (historyIndex.value < history.value.length - 1) {
    historyIndex.value++
    annotations.value = [...history.value[historyIndex.value]]
    emit('update:annotations', annotations.value)
    emit('change', annotations.value)
  }
}

const clearAll = () => {
  annotations.value = []
  saveToHistory()
  emit('update:annotations', annotations.value)
  emit('change', annotations.value)
}

const exportImage = async () => {
  if (!imageRef.value || !svgRef.value) return
  
  const canvas = document.createElement('canvas')
  canvas.width = imageWidth.value
  canvas.height = imageHeight.value
  const ctx = canvas.getContext('2d')
  
  // Draw image
  ctx.drawImage(imageRef.value, 0, 0)
  
  // Draw SVG annotations
  const svgData = new XMLSerializer().serializeToString(svgRef.value)
  const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' })
  const svgUrl = URL.createObjectURL(svgBlob)
  
  const svgImg = new Image()
  svgImg.onload = () => {
    ctx.drawImage(svgImg, 0, 0)
    URL.revokeObjectURL(svgUrl)
    
    canvas.toBlob((blob) => {
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.download = 'annotated-image.png'
      link.href = url
      link.click()
      URL.revokeObjectURL(url)
      emit('export', blob)
    }, 'image/png')
  }
  svgImg.src = svgUrl
}

const handleKeydown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
    e.preventDefault()
    if (e.shiftKey) {
      redo()
    } else {
      undo()
    }
  }
  if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
    e.preventDefault()
    redo()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

watch(() => props.initialAnnotations, (val) => {
  annotations.value = [...val]
  history.value = [[...val]]
  historyIndex.value = 0
}, { deep: true })

defineExpose({
  undo,
  redo,
  clearAll,
  exportImage,
  getAnnotations: () => annotations.value,
  setTool: (tool) => activeTool.value = tool
})
</script>
