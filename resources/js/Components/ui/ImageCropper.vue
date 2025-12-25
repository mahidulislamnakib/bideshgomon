<template>
  <div class="image-cropper">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-700">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ title }}</span>
      <div class="flex items-center gap-2">
        <button
          v-if="showAspectRatios"
          @click="showAspectMenu = !showAspectMenu"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors relative"
          title="Aspect Ratio"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
          </svg>
          
          <!-- Aspect Ratio Menu -->
          <div
            v-if="showAspectMenu"
            class="absolute right-0 top-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10 min-w-[120px]"
          >
            <button
              v-for="ratio in aspectRatios"
              :key="ratio.label"
              @click.stop="setAspectRatio(ratio.value)"
              :class="[
                'w-full px-3 py-1.5 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors',
                currentAspectRatio === ratio.value ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300'
              ]"
            >
              {{ ratio.label }}
            </button>
          </div>
        </button>
      </div>
    </div>

    <!-- Canvas Area -->
    <div class="relative bg-gray-900 overflow-hidden" :style="{ height: canvasHeight }">
      <!-- Image -->
      <img
        v-if="imageUrl"
        ref="imageRef"
        :src="imageUrl"
        class="max-w-full max-h-full absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
        :style="imageStyles"
        @load="onImageLoad"
        draggable="false"
      />
      
      <!-- Crop Overlay -->
      <div
        v-if="imageLoaded"
        class="absolute inset-0 pointer-events-none"
      >
        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-black/50" :style="overlayClipPath" />
        
        <!-- Crop Box -->
        <div
          ref="cropBoxRef"
          class="absolute border-2 border-white pointer-events-auto cursor-move"
          :style="cropBoxStyles"
          @mousedown="startDrag"
          @touchstart="startDrag"
        >
          <!-- Grid Lines -->
          <div v-if="showGrid" class="absolute inset-0">
            <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/30" />
            <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/30" />
            <div class="absolute top-1/3 left-0 right-0 h-px bg-white/30" />
            <div class="absolute top-2/3 left-0 right-0 h-px bg-white/30" />
          </div>
          
          <!-- Resize Handles -->
          <template v-if="resizable">
            <div
              v-for="handle in handles"
              :key="handle"
              :class="[
                'absolute w-4 h-4 bg-white rounded-full border-2 border-blue-500 pointer-events-auto',
                handlePositions[handle]
              ]"
              :style="{ cursor: handleCursors[handle] }"
              @mousedown.stop="startResize($event, handle)"
              @touchstart.stop="startResize($event, handle)"
            />
          </template>
        </div>
      </div>

      <!-- Placeholder -->
      <div
        v-if="!imageUrl"
        class="absolute inset-0 flex flex-col items-center justify-center text-gray-400"
      >
        <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-sm">No image selected</span>
      </div>
    </div>

    <!-- Controls -->
    <div v-if="showControls && imageLoaded" class="p-3 border-t border-gray-200 dark:border-gray-700 space-y-3">
      <!-- Zoom -->
      <div class="flex items-center gap-3">
        <button
          @click="zoom(-0.1)"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
          </svg>
        </button>
        <input
          type="range"
          :min="minZoom"
          :max="maxZoom"
          :step="0.01"
          v-model.number="scale"
          class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
        />
        <button
          @click="zoom(0.1)"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
          </svg>
        </button>
        <span class="text-xs text-gray-500 dark:text-gray-400 w-12 text-right">{{ Math.round(scale * 100) }}%</span>
      </div>
      
      <!-- Rotation -->
      <div v-if="showRotation" class="flex items-center gap-3">
        <button
          @click="rotate(-90)"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Rotate Left"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
          </svg>
        </button>
        <input
          type="range"
          :min="-180"
          :max="180"
          :step="1"
          v-model.number="rotation"
          class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
        />
        <button
          @click="rotate(90)"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Rotate Right"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6" />
          </svg>
        </button>
        <span class="text-xs text-gray-500 dark:text-gray-400 w-12 text-right">{{ rotation }}°</span>
      </div>
    </div>

    <!-- Actions -->
    <div v-if="showActions" class="flex items-center justify-between p-3 border-t border-gray-200 dark:border-gray-700">
      <button
        @click="reset"
        class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
      >
        Reset
      </button>
      <div class="flex items-center gap-2">
        <button
          @click="$emit('cancel')"
          class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
        >
          Cancel
        </button>
        <button
          @click="crop"
          class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
        >
          {{ cropButtonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  src: { type: String, default: '' },
  aspectRatio: { type: [Number, String], default: null },
  minWidth: { type: Number, default: 50 },
  minHeight: { type: Number, default: 50 },
  maxWidth: { type: Number, default: null },
  maxHeight: { type: Number, default: null },
  canvasHeight: { type: String, default: '400px' },
  outputType: { type: String, default: 'base64' }, // 'base64', 'blob', 'canvas'
  outputFormat: { type: String, default: 'image/jpeg' },
  outputQuality: { type: Number, default: 0.92 },
  title: { type: String, default: 'Crop Image' },
  cropButtonText: { type: String, default: 'Apply Crop' },
  showHeader: { type: Boolean, default: true },
  showControls: { type: Boolean, default: true },
  showActions: { type: Boolean, default: true },
  showGrid: { type: Boolean, default: true },
  showRotation: { type: Boolean, default: true },
  showAspectRatios: { type: Boolean, default: true },
  resizable: { type: Boolean, default: true },
  circular: { type: Boolean, default: false },
  minZoom: { type: Number, default: 0.5 },
  maxZoom: { type: Number, default: 3 }
})

const emit = defineEmits(['crop', 'cancel', 'change'])

const imageRef = ref(null)
const cropBoxRef = ref(null)

const imageUrl = ref(props.src)
const imageLoaded = ref(false)
const imageNaturalWidth = ref(0)
const imageNaturalHeight = ref(0)

const scale = ref(1)
const rotation = ref(0)
const currentAspectRatio = ref(props.aspectRatio)
const showAspectMenu = ref(false)

// Crop box position and size
const cropBox = ref({ x: 50, y: 50, width: 200, height: 200 })

const aspectRatios = [
  { label: 'Free', value: null },
  { label: '1:1', value: 1 },
  { label: '4:3', value: 4/3 },
  { label: '16:9', value: 16/9 },
  { label: '3:2', value: 3/2 },
  { label: '2:3', value: 2/3 }
]

const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w']

const handlePositions = {
  nw: 'top-0 left-0 -translate-x-1/2 -translate-y-1/2 cursor-nw-resize',
  n: 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 cursor-n-resize',
  ne: 'top-0 right-0 translate-x-1/2 -translate-y-1/2 cursor-ne-resize',
  e: 'top-1/2 right-0 translate-x-1/2 -translate-y-1/2 cursor-e-resize',
  se: 'bottom-0 right-0 translate-x-1/2 translate-y-1/2 cursor-se-resize',
  s: 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 cursor-s-resize',
  sw: 'bottom-0 left-0 -translate-x-1/2 translate-y-1/2 cursor-sw-resize',
  w: 'top-1/2 left-0 -translate-x-1/2 -translate-y-1/2 cursor-w-resize'
}

const handleCursors = {
  nw: 'nw-resize', n: 'n-resize', ne: 'ne-resize',
  e: 'e-resize', se: 'se-resize', s: 's-resize',
  sw: 'sw-resize', w: 'w-resize'
}

const imageStyles = computed(() => ({
  transform: `scale(${scale.value}) rotate(${rotation.value}deg)`,
  transition: 'transform 0.2s ease'
}))

const cropBoxStyles = computed(() => ({
  left: `${cropBox.value.x}px`,
  top: `${cropBox.value.y}px`,
  width: `${cropBox.value.width}px`,
  height: `${cropBox.value.height}px`,
  borderRadius: props.circular ? '50%' : '0'
}))

const overlayClipPath = computed(() => {
  const { x, y, width, height } = cropBox.value
  if (props.circular) {
    const cx = x + width / 2
    const cy = y + height / 2
    const rx = width / 2
    const ry = height / 2
    return {
      clipPath: `polygon(0% 0%, 0% 100%, 100% 100%, 100% 0%, 0% 0%, ${cx - rx}px ${cy}px, ${cx}px ${cy - ry}px, ${cx + rx}px ${cy}px, ${cx}px ${cy + ry}px, ${cx - rx}px ${cy}px)`
    }
  }
  return {
    clipPath: `polygon(0% 0%, 0% 100%, ${x}px 100%, ${x}px ${y}px, ${x + width}px ${y}px, ${x + width}px ${y + height}px, ${x}px ${y + height}px, ${x}px 100%, 100% 100%, 100% 0%)`
  }
})

// Drag state
let isDragging = false
let isResizing = false
let resizeHandle = ''
let startX = 0
let startY = 0
let startBox = { x: 0, y: 0, width: 0, height: 0 }

const onImageLoad = () => {
  if (imageRef.value) {
    imageNaturalWidth.value = imageRef.value.naturalWidth
    imageNaturalHeight.value = imageRef.value.naturalHeight
    imageLoaded.value = true
    initCropBox()
  }
}

const initCropBox = () => {
  if (!imageRef.value) return
  
  const container = imageRef.value.parentElement
  const containerWidth = container.clientWidth
  const containerHeight = container.clientHeight
  
  let width = Math.min(200, containerWidth * 0.6)
  let height = Math.min(200, containerHeight * 0.6)
  
  if (currentAspectRatio.value) {
    if (width / currentAspectRatio.value > height) {
      width = height * currentAspectRatio.value
    } else {
      height = width / currentAspectRatio.value
    }
  }
  
  cropBox.value = {
    x: (containerWidth - width) / 2,
    y: (containerHeight - height) / 2,
    width,
    height
  }
}

const setAspectRatio = (ratio) => {
  currentAspectRatio.value = ratio
  showAspectMenu.value = false
  
  if (ratio && imageLoaded.value) {
    const { width, height, x, y } = cropBox.value
    const centerX = x + width / 2
    const centerY = y + height / 2
    
    let newWidth = width
    let newHeight = width / ratio
    
    if (newHeight > height) {
      newHeight = height
      newWidth = height * ratio
    }
    
    cropBox.value = {
      x: centerX - newWidth / 2,
      y: centerY - newHeight / 2,
      width: newWidth,
      height: newHeight
    }
  }
}

const startDrag = (e) => {
  isDragging = true
  const event = e.touches ? e.touches[0] : e
  startX = event.clientX
  startY = event.clientY
  startBox = { ...cropBox.value }
  
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
  document.addEventListener('touchmove', onDrag)
  document.addEventListener('touchend', stopDrag)
}

const onDrag = (e) => {
  if (!isDragging) return
  
  const event = e.touches ? e.touches[0] : e
  const dx = event.clientX - startX
  const dy = event.clientY - startY
  
  const container = imageRef.value?.parentElement
  if (!container) return
  
  const maxX = container.clientWidth - startBox.width
  const maxY = container.clientHeight - startBox.height
  
  cropBox.value.x = Math.max(0, Math.min(maxX, startBox.x + dx))
  cropBox.value.y = Math.max(0, Math.min(maxY, startBox.y + dy))
  
  emitChange()
}

const stopDrag = () => {
  isDragging = false
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
  document.removeEventListener('touchmove', onDrag)
  document.removeEventListener('touchend', stopDrag)
}

const startResize = (e, handle) => {
  isResizing = true
  resizeHandle = handle
  const event = e.touches ? e.touches[0] : e
  startX = event.clientX
  startY = event.clientY
  startBox = { ...cropBox.value }
  
  document.addEventListener('mousemove', onResize)
  document.addEventListener('mouseup', stopResize)
  document.addEventListener('touchmove', onResize)
  document.addEventListener('touchend', stopResize)
}

const onResize = (e) => {
  if (!isResizing) return
  
  const event = e.touches ? e.touches[0] : e
  let dx = event.clientX - startX
  let dy = event.clientY - startY
  
  const container = imageRef.value?.parentElement
  if (!container) return
  
  let { x, y, width, height } = startBox
  
  // Apply resize based on handle
  if (resizeHandle.includes('e')) {
    width = Math.max(props.minWidth, startBox.width + dx)
  }
  if (resizeHandle.includes('w')) {
    const newWidth = Math.max(props.minWidth, startBox.width - dx)
    x = startBox.x + (startBox.width - newWidth)
    width = newWidth
  }
  if (resizeHandle.includes('s')) {
    height = Math.max(props.minHeight, startBox.height + dy)
  }
  if (resizeHandle.includes('n')) {
    const newHeight = Math.max(props.minHeight, startBox.height - dy)
    y = startBox.y + (startBox.height - newHeight)
    height = newHeight
  }
  
  // Apply aspect ratio constraint
  if (currentAspectRatio.value) {
    if (resizeHandle === 'e' || resizeHandle === 'w') {
      height = width / currentAspectRatio.value
    } else if (resizeHandle === 'n' || resizeHandle === 's') {
      width = height * currentAspectRatio.value
    } else {
      // Corner handles - maintain ratio based on larger change
      const newHeight = width / currentAspectRatio.value
      height = newHeight
    }
  }
  
  // Clamp to container bounds
  x = Math.max(0, Math.min(container.clientWidth - width, x))
  y = Math.max(0, Math.min(container.clientHeight - height, y))
  
  cropBox.value = { x, y, width, height }
  emitChange()
}

const stopResize = () => {
  isResizing = false
  resizeHandle = ''
  document.removeEventListener('mousemove', onResize)
  document.removeEventListener('mouseup', stopResize)
  document.removeEventListener('touchmove', onResize)
  document.removeEventListener('touchend', stopResize)
}

const zoom = (delta) => {
  scale.value = Math.max(props.minZoom, Math.min(props.maxZoom, scale.value + delta))
  emitChange()
}

const rotate = (deg) => {
  rotation.value = ((rotation.value + deg) % 360 + 360) % 360
  if (rotation.value > 180) rotation.value -= 360
  emitChange()
}

const reset = () => {
  scale.value = 1
  rotation.value = 0
  initCropBox()
  emitChange()
}

const emitChange = () => {
  emit('change', {
    cropBox: { ...cropBox.value },
    scale: scale.value,
    rotation: rotation.value
  })
}

const crop = async () => {
  if (!imageRef.value) return
  
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')
  
  const { x, y, width, height } = cropBox.value
  const img = imageRef.value
  const displayedWidth = img.clientWidth * scale.value
  const displayedHeight = img.clientHeight * scale.value
  
  const scaleX = img.naturalWidth / displayedWidth
  const scaleY = img.naturalHeight / displayedHeight
  
  canvas.width = width * scaleX
  canvas.height = height * scaleY
  
  ctx.save()
  
  if (rotation.value !== 0) {
    ctx.translate(canvas.width / 2, canvas.height / 2)
    ctx.rotate((rotation.value * Math.PI) / 180)
    ctx.translate(-canvas.width / 2, -canvas.height / 2)
  }
  
  const container = img.parentElement
  const imgX = (container.clientWidth - displayedWidth) / 2
  const imgY = (container.clientHeight - displayedHeight) / 2
  
  const sourceX = (x - imgX) * scaleX
  const sourceY = (y - imgY) * scaleY
  
  ctx.drawImage(
    img,
    sourceX, sourceY,
    width * scaleX, height * scaleY,
    0, 0,
    canvas.width, canvas.height
  )
  
  ctx.restore()
  
  let result
  if (props.outputType === 'canvas') {
    result = canvas
  } else if (props.outputType === 'blob') {
    result = await new Promise(resolve => canvas.toBlob(resolve, props.outputFormat, props.outputQuality))
  } else {
    result = canvas.toDataURL(props.outputFormat, props.outputQuality)
  }
  
  emit('crop', result)
}

watch(() => props.src, (newSrc) => {
  imageUrl.value = newSrc
  imageLoaded.value = false
})

watch(() => props.aspectRatio, (newRatio) => {
  setAspectRatio(newRatio)
})

// Close aspect menu on outside click
const handleClickOutside = (e) => {
  if (showAspectMenu.value && !e.target.closest('.image-cropper')) {
    showAspectMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  stopDrag()
  stopResize()
})

defineExpose({
  crop,
  reset,
  setAspectRatio
})
</script>

<style scoped>
.image-cropper {
  @apply bg-white dark:bg-gray-800 rounded-lg overflow-hidden;
}

input[type="range"] {
  @apply accent-blue-600;
}

input[type="range"]::-webkit-slider-thumb {
  @apply appearance-none w-4 h-4 bg-blue-600 rounded-full cursor-pointer;
}

input[type="range"]::-moz-range-thumb {
  @apply w-4 h-4 bg-blue-600 rounded-full cursor-pointer border-0;
}
</style>
