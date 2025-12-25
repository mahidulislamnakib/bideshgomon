<template>
  <div :class="containerClasses">
    <canvas ref="canvasRef" :style="canvasStyle" />
    
    <!-- Loading state -->
    <div v-if="loading" :class="loadingClasses">
      <Spinner size="sm" />
    </div>
    
    <!-- Error state -->
    <div v-if="error" :class="errorClasses">
      <ExclamationTriangleIcon class="w-6 h-6" />
      <span class="text-xs">{{ error }}</span>
    </div>
    
    <!-- Logo overlay -->
    <img
      v-if="logo && !loading && !error"
      :src="logo"
      :alt="logoAlt"
      :class="logoClasses"
      :style="logoStyle"
    />
    
    <!-- Label -->
    <p v-if="label && !loading && !error" :class="labelClasses">
      {{ label }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  value: {
    type: String,
    required: true
  },
  
  // Size
  size: {
    type: Number,
    default: 200
  },
  
  // Colors
  fgColor: {
    type: String,
    default: '#000000'
  },
  bgColor: {
    type: String,
    default: '#ffffff'
  },
  
  // Error correction level
  errorCorrectionLevel: {
    type: String,
    default: 'M',
    validator: (v) => ['L', 'M', 'Q', 'H'].includes(v)
  },
  
  // Margin (quiet zone)
  margin: {
    type: Number,
    default: 4
  },
  
  // Logo
  logo: {
    type: String,
    default: ''
  },
  logoAlt: {
    type: String,
    default: 'Logo'
  },
  logoSize: {
    type: Number,
    default: 30
  },
  
  // Label
  label: {
    type: String,
    default: ''
  },
  
  // Download
  downloadable: {
    type: Boolean,
    default: false
  },
  downloadFilename: {
    type: String,
    default: 'qrcode'
  }
})

const emit = defineEmits(['ready', 'error'])

const canvasRef = ref(null)
const loading = ref(true)
const error = ref('')

// Canvas style
const canvasStyle = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`
}))

// Container classes
const containerClasses = computed(() => [
  'relative inline-flex flex-col items-center gap-2'
])

// Loading classes
const loadingClasses = computed(() => [
  'absolute inset-0 flex items-center justify-center',
  'bg-white/80 dark:bg-gray-800/80'
])

// Error classes
const errorClasses = computed(() => [
  'absolute inset-0 flex flex-col items-center justify-center gap-1',
  'bg-red-50 dark:bg-red-900/20',
  'text-red-600 dark:text-red-400'
])

// Logo classes
const logoClasses = computed(() => [
  'absolute rounded-md',
  'bg-white p-1 shadow-sm'
])

// Logo style
const logoStyle = computed(() => {
  const logoSizePercent = props.logoSize
  const position = (100 - logoSizePercent) / 2
  return {
    width: `${logoSizePercent}%`,
    height: `${logoSizePercent}%`,
    top: `${position}%`,
    left: `${position}%`
  }
})

// Label classes
const labelClasses = computed(() => [
  'text-sm font-medium text-gray-700 dark:text-gray-300',
  'max-w-full truncate'
])

// Simple QR code generation using canvas
function generateQR() {
  if (!canvasRef.value || !props.value) return
  
  loading.value = true
  error.value = ''
  
  try {
    const canvas = canvasRef.value
    const ctx = canvas.getContext('2d')
    const scale = window.devicePixelRatio || 1
    
    // Set canvas size with device pixel ratio for sharpness
    canvas.width = props.size * scale
    canvas.height = props.size * scale
    ctx.scale(scale, scale)
    
    // Generate QR matrix (simplified - using a basic pattern)
    const data = encodeData(props.value)
    const moduleCount = data.length
    const moduleSize = (props.size - props.margin * 2) / moduleCount
    
    // Draw background
    ctx.fillStyle = props.bgColor
    ctx.fillRect(0, 0, props.size, props.size)
    
    // Draw modules
    ctx.fillStyle = props.fgColor
    for (let row = 0; row < moduleCount; row++) {
      for (let col = 0; col < moduleCount; col++) {
        if (data[row][col]) {
          ctx.fillRect(
            props.margin + col * moduleSize,
            props.margin + row * moduleSize,
            moduleSize,
            moduleSize
          )
        }
      }
    }
    
    loading.value = false
    emit('ready', { canvas, dataUrl: canvas.toDataURL() })
  } catch (e) {
    error.value = 'Failed to generate QR code'
    loading.value = false
    emit('error', e)
  }
}

// Simple data encoder (creates a basic QR-like pattern)
function encodeData(text) {
  const size = calculateSize(text.length)
  const matrix = Array(size).fill(null).map(() => Array(size).fill(false))
  
  // Add finder patterns (corners)
  addFinderPattern(matrix, 0, 0)
  addFinderPattern(matrix, size - 7, 0)
  addFinderPattern(matrix, 0, size - 7)
  
  // Add timing patterns
  for (let i = 8; i < size - 8; i++) {
    matrix[6][i] = i % 2 === 0
    matrix[i][6] = i % 2 === 0
  }
  
  // Encode data in remaining area
  let pos = 0
  const bits = textToBits(text)
  for (let row = size - 1; row >= 0; row--) {
    for (let col = size - 1; col >= 0; col--) {
      if (!isReserved(row, col, size)) {
        matrix[row][col] = bits[pos % bits.length] === '1'
        pos++
      }
    }
  }
  
  return matrix
}

function calculateSize(dataLength) {
  // Calculate QR code size based on data length
  if (dataLength <= 17) return 21
  if (dataLength <= 32) return 25
  if (dataLength <= 53) return 29
  if (dataLength <= 78) return 33
  return 37
}

function addFinderPattern(matrix, startRow, startCol) {
  // 7x7 finder pattern
  for (let row = 0; row < 7; row++) {
    for (let col = 0; col < 7; col++) {
      const r = startRow + row
      const c = startCol + col
      if (r < matrix.length && c < matrix.length) {
        // Outer ring and center
        matrix[r][c] = 
          row === 0 || row === 6 || col === 0 || col === 6 ||
          (row >= 2 && row <= 4 && col >= 2 && col <= 4)
      }
    }
  }
}

function isReserved(row, col, size) {
  // Check if position is in finder pattern or timing pattern area
  const inTopLeft = row < 9 && col < 9
  const inTopRight = row < 9 && col >= size - 8
  const inBottomLeft = row >= size - 8 && col < 9
  const inTiming = row === 6 || col === 6
  return inTopLeft || inTopRight || inBottomLeft || inTiming
}

function textToBits(text) {
  let bits = ''
  for (let i = 0; i < text.length; i++) {
    const charCode = text.charCodeAt(i)
    bits += charCode.toString(2).padStart(8, '0')
  }
  return bits
}

// Download QR code as image
function download(format = 'png') {
  if (!canvasRef.value) return
  
  const canvas = canvasRef.value
  const link = document.createElement('a')
  link.download = `${props.downloadFilename}.${format}`
  link.href = canvas.toDataURL(`image/${format}`)
  link.click()
}

// Get data URL
function getDataUrl(format = 'png') {
  if (!canvasRef.value) return null
  return canvasRef.value.toDataURL(`image/${format}`)
}

// Watch for changes
watch(() => [props.value, props.size, props.fgColor, props.bgColor], () => {
  generateQR()
}, { deep: true })

onMounted(() => {
  generateQR()
})

// Expose methods
defineExpose({
  download,
  getDataUrl,
  regenerate: generateQR
})
</script>
