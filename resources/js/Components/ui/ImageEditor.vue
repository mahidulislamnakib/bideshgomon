<template>
  <div :class="['image-editor rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg">
          <PhotoIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ imageWidth }} × {{ imageHeight }}px
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Undo"
          :disabled="historyIndex <= 0"
          @click="undo"
        >
          <ArrowUturnLeftIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Redo"
          :disabled="historyIndex >= history.length - 1"
          @click="redo"
        >
          <ArrowUturnRightIcon class="w-5 h-5" />
        </button>
        <div class="w-px h-6 bg-gray-300 dark:bg-gray-600" />
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="resetImage"
        >
          Reset
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors"
          @click="saveImage"
        >
          Save
        </button>
      </div>
    </div>

    <div class="flex">
      <!-- Tools Sidebar -->
      <div class="w-14 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-2 space-y-1">
        <button
          v-for="tool in tools"
          :key="tool.id"
          type="button"
          :class="[
            'w-full p-2 rounded-lg transition-colors',
            activeTool === tool.id
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          :title="tool.label"
          @click="activeTool = tool.id"
        >
          <component :is="tool.icon" class="w-5 h-5 mx-auto" />
        </button>
      </div>

      <!-- Canvas Area -->
      <div class="flex-1 relative overflow-hidden bg-gray-100 dark:bg-gray-900" :style="{ height }">
        <!-- Crop Overlay -->
        <div
          v-if="activeTool === 'crop' && isCropping"
          class="absolute inset-0 bg-black/50 z-10"
        >
          <div
            class="absolute border-2 border-white shadow-xl"
            :style="cropBoxStyle"
            @mousedown="startCropDrag"
          >
            <!-- Crop Handles -->
            <div class="absolute -top-1.5 -left-1.5 w-3 h-3 bg-white border border-gray-300 cursor-nw-resize" @mousedown.stop="startCropResize('nw', $event)" />
            <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-white border border-gray-300 cursor-ne-resize" @mousedown.stop="startCropResize('ne', $event)" />
            <div class="absolute -bottom-1.5 -left-1.5 w-3 h-3 bg-white border border-gray-300 cursor-sw-resize" @mousedown.stop="startCropResize('sw', $event)" />
            <div class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-white border border-gray-300 cursor-se-resize" @mousedown.stop="startCropResize('se', $event)" />
            <!-- Rule of Thirds Grid -->
            <div class="absolute inset-0 pointer-events-none">
              <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/30" />
              <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/30" />
              <div class="absolute top-1/3 left-0 right-0 h-px bg-white/30" />
              <div class="absolute top-2/3 left-0 right-0 h-px bg-white/30" />
            </div>
          </div>
        </div>

        <!-- Image Canvas -->
        <div class="absolute inset-0 flex items-center justify-center p-8">
          <canvas
            ref="canvasRef"
            class="max-w-full max-h-full shadow-2xl"
            :style="{ 
              transform: `rotate(${rotation}deg) scaleX(${flipH ? -1 : 1}) scaleY(${flipV ? -1 : 1})`,
              filter: filterStyle
            }"
          />
        </div>

        <!-- No Image State -->
        <div v-if="!imageSrc" class="absolute inset-0 flex items-center justify-center">
          <div class="text-center">
            <PhotoIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
            <p class="text-gray-500 dark:text-gray-400 mb-4">No image loaded</p>
            <label class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg cursor-pointer transition-colors">
              <input type="file" accept="image/*" class="hidden" @change="loadImage" />
              Upload Image
            </label>
          </div>
        </div>
      </div>

      <!-- Properties Panel -->
      <div class="w-64 flex-shrink-0 border-l border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 overflow-y-auto">
        <!-- Crop Options -->
        <div v-if="activeTool === 'crop'" class="p-4 space-y-4">
          <h4 class="font-medium text-gray-900 dark:text-white">Crop</h4>
          <div class="space-y-2">
            <label class="block text-xs text-gray-600 dark:text-gray-400">Aspect Ratio</label>
            <select
              v-model="cropAspect"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg"
            >
              <option value="free">Free</option>
              <option value="1:1">1:1 (Square)</option>
              <option value="4:3">4:3</option>
              <option value="16:9">16:9</option>
              <option value="3:2">3:2</option>
            </select>
          </div>
          <button
            type="button"
            class="w-full px-3 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors"
            @click="isCropping ? applyCrop() : startCrop()"
          >
            {{ isCropping ? 'Apply Crop' : 'Start Crop' }}
          </button>
        </div>

        <!-- Transform Options -->
        <div v-if="activeTool === 'transform'" class="p-4 space-y-4">
          <h4 class="font-medium text-gray-900 dark:text-white">Transform</h4>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              class="px-3 py-2 text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors"
              @click="rotation = (rotation - 90) % 360"
            >
              ↶ Rotate Left
            </button>
            <button
              type="button"
              class="px-3 py-2 text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors"
              @click="rotation = (rotation + 90) % 360"
            >
              ↷ Rotate Right
            </button>
            <button
              type="button"
              :class="[
                'px-3 py-2 text-sm rounded-lg transition-colors',
                flipH ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600'
              ]"
              @click="flipH = !flipH"
            >
              ↔ Flip H
            </button>
            <button
              type="button"
              :class="[
                'px-3 py-2 text-sm rounded-lg transition-colors',
                flipV ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600'
              ]"
              @click="flipV = !flipV"
            >
              ↕ Flip V
            </button>
          </div>
        </div>

        <!-- Adjust Options -->
        <div v-if="activeTool === 'adjust'" class="p-4 space-y-4">
          <h4 class="font-medium text-gray-900 dark:text-white">Adjustments</h4>
          <div v-for="adjust in adjustments" :key="adjust.id" class="space-y-1">
            <div class="flex justify-between text-xs">
              <span class="text-gray-600 dark:text-gray-400">{{ adjust.label }}</span>
              <span class="text-gray-500">{{ adjust.value }}%</span>
            </div>
            <input
              type="range"
              :min="adjust.min"
              :max="adjust.max"
              v-model.number="adjust.value"
              class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
            />
          </div>
          <button
            type="button"
            class="w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
            @click="resetAdjustments"
          >
            Reset Adjustments
          </button>
        </div>

        <!-- Filters -->
        <div v-if="activeTool === 'filter'" class="p-4 space-y-4">
          <h4 class="font-medium text-gray-900 dark:text-white">Filters</h4>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="filter in filters"
              :key="filter.id"
              type="button"
              :class="[
                'p-2 rounded-lg border-2 transition-all',
                activeFilter === filter.id
                  ? 'border-blue-500 ring-2 ring-blue-500/20'
                  : 'border-gray-200 dark:border-gray-700 hover:border-blue-300'
              ]"
              @click="activeFilter = filter.id"
            >
              <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded mb-1" :style="{ filter: filter.css }" />
              <span class="text-xs text-gray-600 dark:text-gray-400">{{ filter.label }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  PhotoIcon,
  ArrowUturnLeftIcon,
  ArrowUturnRightIcon,
  ScissorsIcon,
  ArrowsRightLeftIcon,
  AdjustmentsHorizontalIcon,
  SparklesIcon,
  PencilIcon,
  EyeDropperIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  src: { type: String, default: '' },
  title: { type: String, default: 'Image Editor' },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true }
})

const emit = defineEmits(['save', 'change'])

const canvasRef = ref(null)
const imageSrc = ref(props.src)
const imageWidth = ref(0)
const imageHeight = ref(0)
const activeTool = ref('adjust')
const history = ref([])
const historyIndex = ref(-1)

// Transform
const rotation = ref(0)
const flipH = ref(false)
const flipV = ref(false)

// Crop
const isCropping = ref(false)
const cropAspect = ref('free')
const cropBox = ref({ x: 50, y: 50, width: 200, height: 150 })

// Adjustments
const adjustments = ref([
  { id: 'brightness', label: 'Brightness', value: 100, min: 0, max: 200 },
  { id: 'contrast', label: 'Contrast', value: 100, min: 0, max: 200 },
  { id: 'saturation', label: 'Saturation', value: 100, min: 0, max: 200 },
  { id: 'blur', label: 'Blur', value: 0, min: 0, max: 20 },
  { id: 'sepia', label: 'Sepia', value: 0, min: 0, max: 100 }
])

// Filters
const activeFilter = ref('none')
const filters = [
  { id: 'none', label: 'None', css: 'none' },
  { id: 'grayscale', label: 'B&W', css: 'grayscale(100%)' },
  { id: 'sepia', label: 'Sepia', css: 'sepia(100%)' },
  { id: 'vintage', label: 'Vintage', css: 'sepia(50%) contrast(90%)' },
  { id: 'cool', label: 'Cool', css: 'saturate(120%) hue-rotate(180deg)' },
  { id: 'warm', label: 'Warm', css: 'saturate(150%) hue-rotate(-30deg)' }
]

const tools = [
  { id: 'crop', label: 'Crop', icon: ScissorsIcon },
  { id: 'transform', label: 'Transform', icon: ArrowsRightLeftIcon },
  { id: 'adjust', label: 'Adjust', icon: AdjustmentsHorizontalIcon },
  { id: 'filter', label: 'Filters', icon: SparklesIcon },
  { id: 'draw', label: 'Draw', icon: PencilIcon },
  { id: 'picker', label: 'Color Picker', icon: EyeDropperIcon }
]

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const filterStyle = computed(() => {
  const adj = adjustments.value
  const brightness = adj.find(a => a.id === 'brightness').value
  const contrast = adj.find(a => a.id === 'contrast').value
  const saturation = adj.find(a => a.id === 'saturation').value
  const blur = adj.find(a => a.id === 'blur').value
  const sepia = adj.find(a => a.id === 'sepia').value

  let filter = `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturation}%) blur(${blur}px) sepia(${sepia}%)`
  
  const activeFilterObj = filters.find(f => f.id === activeFilter.value)
  if (activeFilterObj && activeFilterObj.css !== 'none') {
    filter += ` ${activeFilterObj.css}`
  }
  
  return filter
})

const cropBoxStyle = computed(() => ({
  left: `${cropBox.value.x}px`,
  top: `${cropBox.value.y}px`,
  width: `${cropBox.value.width}px`,
  height: `${cropBox.value.height}px`
}))

const loadImage = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const reader = new FileReader()
  reader.onload = (e) => {
    imageSrc.value = e.target.result
    drawImage(e.target.result)
  }
  reader.readAsDataURL(file)
}

const drawImage = (src) => {
  const canvas = canvasRef.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  const img = new Image()
  img.onload = () => {
    canvas.width = img.width
    canvas.height = img.height
    imageWidth.value = img.width
    imageHeight.value = img.height
    ctx.drawImage(img, 0, 0)
    saveToHistory()
  }
  img.src = src
}

const saveToHistory = () => {
  const canvas = canvasRef.value
  if (!canvas) return
  
  history.value = history.value.slice(0, historyIndex.value + 1)
  history.value.push(canvas.toDataURL())
  historyIndex.value = history.value.length - 1
}

const undo = () => {
  if (historyIndex.value > 0) {
    historyIndex.value--
    drawImage(history.value[historyIndex.value])
  }
}

const redo = () => {
  if (historyIndex.value < history.value.length - 1) {
    historyIndex.value++
    drawImage(history.value[historyIndex.value])
  }
}

const resetImage = () => {
  if (history.value.length > 0) {
    drawImage(history.value[0])
    historyIndex.value = 0
  }
  rotation.value = 0
  flipH.value = false
  flipV.value = false
  resetAdjustments()
  activeFilter.value = 'none'
}

const resetAdjustments = () => {
  adjustments.value.forEach(adj => {
    adj.value = adj.id === 'brightness' || adj.id === 'contrast' || adj.id === 'saturation' ? 100 : 0
  })
}

const startCrop = () => {
  isCropping.value = true
  cropBox.value = { x: 50, y: 50, width: 200, height: 150 }
}

const applyCrop = () => {
  isCropping.value = false
  saveToHistory()
}

const startCropDrag = (e) => {
  // Implement drag logic
}

const startCropResize = (handle, e) => {
  // Implement resize logic
}

const saveImage = () => {
  const canvas = canvasRef.value
  if (!canvas) return
  
  const dataUrl = canvas.toDataURL('image/png')
  emit('save', dataUrl)
  
  const a = document.createElement('a')
  a.href = dataUrl
  a.download = 'edited-image.png'
  a.click()
}

onMounted(() => {
  if (props.src) {
    drawImage(props.src)
  }
})

watch(() => props.src, (newSrc) => {
  if (newSrc) drawImage(newSrc)
})
</script>
