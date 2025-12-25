<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-1.5" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Canvas container -->
    <div
      class="relative rounded-lg border-2 overflow-hidden bg-white"
      :class="canvasContainerClasses"
      :style="{ height: height }"
    >
      <canvas
        ref="canvasRef"
        class="w-full h-full cursor-crosshair touch-none"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart.prevent="handleTouchStart"
        @touchmove.prevent="handleTouchMove"
        @touchend.prevent="stopDrawing"
      />

      <!-- Placeholder text -->
      <div
        v-if="isEmpty && !isDrawing"
        class="absolute inset-0 flex items-center justify-center pointer-events-none"
      >
        <p class="text-gray-400 text-sm">{{ placeholder }}</p>
      </div>

      <!-- Clear button -->
      <button
        v-if="!isEmpty && !disabled"
        type="button"
        class="absolute top-2 right-2 rounded-lg bg-white/90 p-1.5 shadow-sm hover:bg-white transition-colors"
        title="Clear signature"
        @click="clear"
      >
        <XMarkIcon class="h-4 w-4 text-gray-500" />
      </button>
    </div>

    <!-- Toolbar -->
    <div v-if="showToolbar" class="mt-2 flex items-center gap-3">
      <!-- Pen colors -->
      <div class="flex items-center gap-1.5">
        <button
          v-for="color in penColors"
          :key="color"
          type="button"
          class="h-5 w-5 rounded-full ring-2 ring-offset-1 transition-transform hover:scale-110"
          :class="penColor === color ? 'ring-primary-500' : 'ring-transparent'"
          :style="{ backgroundColor: color }"
          @click="penColor = color"
        />
      </div>

      <div class="h-4 w-px bg-gray-300" />

      <!-- Pen sizes -->
      <div class="flex items-center gap-1">
        <button
          v-for="size in penSizes"
          :key="size"
          type="button"
          class="flex h-6 w-6 items-center justify-center rounded hover:bg-gray-100 transition-colors"
          :class="penSize === size ? 'bg-gray-100' : ''"
          @click="penSize = size"
        >
          <span
            class="rounded-full bg-gray-700"
            :style="{ width: `${size * 2}px`, height: `${size * 2}px` }"
          />
        </button>
      </div>

      <div class="flex-1" />

      <!-- Actions -->
      <button
        type="button"
        class="text-sm text-gray-500 hover:text-gray-700"
        :disabled="isEmpty"
        @click="undo"
      >
        Undo
      </button>
    </div>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-1.5 text-sm"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: String, // Base64 image data
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Sign here'
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  height: {
    type: String,
    default: '200px'
  },
  showToolbar: {
    type: Boolean,
    default: true
  },
  backgroundColor: {
    type: String,
    default: '#ffffff'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Refs
const canvasRef = ref(null)
const isDrawing = ref(false)
const isEmpty = ref(true)
const history = ref([])

// Pen settings
const penColor = ref('#000000')
const penSize = ref(2)
const penColors = ['#000000', '#1e40af', '#dc2626', '#059669']
const penSizes = [1, 2, 3, 4]

// Canvas context
let ctx = null
let lastX = 0
let lastY = 0

// Initialize canvas
onMounted(() => {
  initCanvas()
  
  // Load existing signature
  if (props.modelValue) {
    loadSignature(props.modelValue)
  }
})

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal && canvasRef.value) {
    loadSignature(newVal)
  } else if (!newVal) {
    clear()
  }
})

// Initialize canvas
function initCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return
  
  // Set canvas size to match display size
  const rect = canvas.getBoundingClientRect()
  canvas.width = rect.width
  canvas.height = rect.height
  
  ctx = canvas.getContext('2d')
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  
  // Fill background
  ctx.fillStyle = props.backgroundColor
  ctx.fillRect(0, 0, canvas.width, canvas.height)
}

// Get coordinates from event
function getCoordinates(event) {
  const canvas = canvasRef.value
  const rect = canvas.getBoundingClientRect()
  const scaleX = canvas.width / rect.width
  const scaleY = canvas.height / rect.height
  
  if (event.touches && event.touches[0]) {
    return {
      x: (event.touches[0].clientX - rect.left) * scaleX,
      y: (event.touches[0].clientY - rect.top) * scaleY
    }
  }
  
  return {
    x: (event.clientX - rect.left) * scaleX,
    y: (event.clientY - rect.top) * scaleY
  }
}

// Start drawing
function startDrawing(event) {
  if (props.disabled) return
  
  isDrawing.value = true
  const coords = getCoordinates(event)
  lastX = coords.x
  lastY = coords.y
  
  // Save state for undo
  saveState()
}

// Draw
function draw(event) {
  if (!isDrawing.value || props.disabled) return
  
  const coords = getCoordinates(event)
  
  ctx.strokeStyle = penColor.value
  ctx.lineWidth = penSize.value
  
  ctx.beginPath()
  ctx.moveTo(lastX, lastY)
  ctx.lineTo(coords.x, coords.y)
  ctx.stroke()
  
  lastX = coords.x
  lastY = coords.y
  isEmpty.value = false
}

// Stop drawing
function stopDrawing() {
  if (isDrawing.value) {
    isDrawing.value = false
    emitValue()
  }
}

// Touch handlers
function handleTouchStart(event) {
  startDrawing(event)
}

function handleTouchMove(event) {
  draw(event)
}

// Save state for undo
function saveState() {
  const canvas = canvasRef.value
  if (canvas) {
    history.value.push(canvas.toDataURL())
    // Limit history size
    if (history.value.length > 20) {
      history.value.shift()
    }
  }
}

// Undo
function undo() {
  if (history.value.length > 0) {
    const previousState = history.value.pop()
    loadSignature(previousState)
    
    if (history.value.length === 0) {
      isEmpty.value = true
    }
    
    emitValue()
  }
}

// Clear canvas
function clear() {
  const canvas = canvasRef.value
  if (!canvas || !ctx) return
  
  ctx.fillStyle = props.backgroundColor
  ctx.fillRect(0, 0, canvas.width, canvas.height)
  
  isEmpty.value = true
  history.value = []
  
  emit('update:modelValue', '')
  emit('change', '')
}

// Load signature from base64
function loadSignature(dataUrl) {
  const canvas = canvasRef.value
  if (!canvas || !ctx) return
  
  const img = new Image()
  img.onload = () => {
    ctx.fillStyle = props.backgroundColor
    ctx.fillRect(0, 0, canvas.width, canvas.height)
    ctx.drawImage(img, 0, 0)
    isEmpty.value = false
  }
  img.src = dataUrl
}

// Emit value
function emitValue() {
  const canvas = canvasRef.value
  if (!canvas) return
  
  const dataUrl = canvas.toDataURL('image/png')
  emit('update:modelValue', dataUrl)
  emit('change', dataUrl)
}

// Get signature as blob
async function getBlob() {
  const canvas = canvasRef.value
  if (!canvas) return null
  
  return new Promise((resolve) => {
    canvas.toBlob(resolve, 'image/png')
  })
}

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60 pointer-events-none' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

const canvasContainerClasses = computed(() => {
  if (props.error) {
    return 'border-red-300'
  }
  return 'border-gray-300 hover:border-gray-400'
})

// Expose methods
defineExpose({
  clear,
  undo,
  getBlob,
  isEmpty: () => isEmpty.value
})
</script>
