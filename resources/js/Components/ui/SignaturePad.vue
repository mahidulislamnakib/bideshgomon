<template>
  <div :class="['signature-pad rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ label }}</span>
      <div class="flex items-center gap-2">
        <!-- Pen Color -->
        <div class="flex items-center gap-1">
          <button
            v-for="color in penColors"
            :key="color"
            type="button"
            :class="[
              'w-5 h-5 rounded-full transition-transform hover:scale-110',
              penColor === color && 'ring-2 ring-blue-500 ring-offset-2'
            ]"
            :style="{ backgroundColor: color }"
            @click="penColor = color"
          />
        </div>
        
        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-2" />
        
        <!-- Pen Size -->
        <div class="flex items-center gap-1">
          <button
            v-for="size in penSizes"
            :key="size.value"
            type="button"
            :class="[
              'p-1.5 rounded transition-colors',
              penSize === size.value
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'
                : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700'
            ]"
            :title="size.label"
            @click="penSize = size.value"
          >
            <div
              class="rounded-full bg-current"
              :style="{ width: size.value + 'px', height: size.value + 'px' }"
            />
          </button>
        </div>
        
        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-2" />
        
        <!-- Actions -->
        <button
          type="button"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
          @click="undo"
          title="Undo"
        >
          <ArrowUturnLeftIcon class="w-4 h-4" />
        </button>
        <button
          type="button"
          class="p-1.5 text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
          @click="clear"
          title="Clear"
        >
          <TrashIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Canvas -->
    <div
      ref="containerRef"
      class="relative"
      :class="backgroundColor"
      :style="{ height }"
    >
      <canvas
        ref="canvasRef"
        class="w-full h-full cursor-crosshair touch-none"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="stopDrawing"
      />
      
      <!-- Placeholder -->
      <div
        v-if="isEmpty && !isDrawing"
        class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
      >
        <PencilIcon class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" />
        <p class="text-sm text-gray-400 dark:text-gray-500">{{ placeholder }}</p>
      </div>
      
      <!-- Guide Lines -->
      <div
        v-if="showGuideLine"
        class="absolute bottom-8 left-4 right-4 border-b border-dashed border-gray-300 dark:border-gray-600 pointer-events-none"
      />
    </div>

    <!-- Footer -->
    <div v-if="showFooter" class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
      <span :class="['text-xs', isEmpty ? 'text-gray-400' : 'text-green-500']">
        {{ isEmpty ? 'No signature' : 'Signature captured' }}
      </span>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="download"
          :disabled="isEmpty"
        >
          <ArrowDownTrayIcon class="w-4 h-4 inline mr-1" />
          Download
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
          @click="save"
          :disabled="isEmpty"
        >
          Save Signature
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import {
  ArrowUturnLeftIcon,
  TrashIcon,
  PencilIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Sign here' },
  placeholder: { type: String, default: 'Draw your signature here' },
  height: { type: String, default: '200px' },
  showHeader: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true },
  showGuideLine: { type: Boolean, default: true },
  format: { type: String, default: 'png' }, // png, jpeg, svg
  backgroundColor: { type: String, default: 'bg-white dark:bg-gray-900' },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'save', 'clear'])

const containerRef = ref(null)
const canvasRef = ref(null)
const ctx = ref(null)
const isDrawing = ref(false)
const isEmpty = ref(true)
const history = ref([])
const penColor = ref('#000000')
const penSize = ref(2)

const penColors = ['#000000', '#1F2937', '#3B82F6', '#10B981', '#EF4444', '#8B5CF6']
const penSizes = [
  { value: 1, label: 'Fine' },
  { value: 2, label: 'Medium' },
  { value: 4, label: 'Bold' }
]

const themeClasses = computed(() =>
  props.theme === 'dark'
    ? 'border-gray-700'
    : 'border-gray-200'
)

const initCanvas = () => {
  const canvas = canvasRef.value
  const container = containerRef.value
  if (!canvas || !container) return
  
  // Set canvas size to match container
  const rect = container.getBoundingClientRect()
  canvas.width = rect.width
  canvas.height = rect.height
  
  ctx.value = canvas.getContext('2d')
  ctx.value.lineCap = 'round'
  ctx.value.lineJoin = 'round'
  
  // Load existing signature if any
  if (props.modelValue) {
    const img = new Image()
    img.onload = () => {
      ctx.value.drawImage(img, 0, 0)
      isEmpty.value = false
    }
    img.src = props.modelValue
  }
}

const getCoordinates = (e) => {
  const rect = canvasRef.value.getBoundingClientRect()
  return {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top
  }
}

const startDrawing = (e) => {
  isDrawing.value = true
  const coords = getCoordinates(e)
  
  ctx.value.beginPath()
  ctx.value.moveTo(coords.x, coords.y)
  ctx.value.strokeStyle = penColor.value
  ctx.value.lineWidth = penSize.value
  
  // Save state for undo
  saveState()
}

const draw = (e) => {
  if (!isDrawing.value) return
  
  const coords = getCoordinates(e)
  ctx.value.lineTo(coords.x, coords.y)
  ctx.value.stroke()
  isEmpty.value = false
}

const stopDrawing = () => {
  if (isDrawing.value) {
    isDrawing.value = false
    ctx.value.closePath()
    emitValue()
  }
}

const handleTouchStart = (e) => {
  e.preventDefault()
  const touch = e.touches[0]
  startDrawing({ clientX: touch.clientX, clientY: touch.clientY })
}

const handleTouchMove = (e) => {
  e.preventDefault()
  const touch = e.touches[0]
  draw({ clientX: touch.clientX, clientY: touch.clientY })
}

const saveState = () => {
  const canvas = canvasRef.value
  history.value.push(canvas.toDataURL())
  if (history.value.length > 20) {
    history.value.shift()
  }
}

const undo = () => {
  if (history.value.length === 0) return
  
  const canvas = canvasRef.value
  const previousState = history.value.pop()
  
  if (history.value.length === 0) {
    clear()
    return
  }
  
  const img = new Image()
  img.onload = () => {
    ctx.value.clearRect(0, 0, canvas.width, canvas.height)
    ctx.value.drawImage(img, 0, 0)
    emitValue()
  }
  img.src = history.value[history.value.length - 1] || previousState
}

const clear = () => {
  const canvas = canvasRef.value
  ctx.value.clearRect(0, 0, canvas.width, canvas.height)
  isEmpty.value = true
  history.value = []
  emit('update:modelValue', '')
  emit('clear')
}

const save = () => {
  const dataUrl = getDataUrl()
  emit('save', dataUrl)
}

const download = () => {
  const dataUrl = getDataUrl()
  const link = document.createElement('a')
  link.download = `signature.${props.format}`
  link.href = dataUrl
  link.click()
}

const getDataUrl = () => {
  const canvas = canvasRef.value
  const format = props.format === 'jpeg' ? 'image/jpeg' : 'image/png'
  return canvas.toDataURL(format)
}

const emitValue = () => {
  const dataUrl = getDataUrl()
  emit('update:modelValue', dataUrl)
}

const handleResize = () => {
  const canvas = canvasRef.value
  const tempCanvas = document.createElement('canvas')
  tempCanvas.width = canvas.width
  tempCanvas.height = canvas.height
  tempCanvas.getContext('2d').drawImage(canvas, 0, 0)
  
  nextTick(() => {
    initCanvas()
    if (!isEmpty.value) {
      ctx.value.drawImage(tempCanvas, 0, 0)
    }
  })
}

onMounted(() => {
  initCanvas()
  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})

watch(() => props.modelValue, (newVal) => {
  if (!newVal && !isEmpty.value) {
    clear()
  }
})
</script>
