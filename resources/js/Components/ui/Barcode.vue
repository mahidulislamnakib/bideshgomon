<template>
  <div :class="containerClasses">
    <!-- Barcode canvas -->
    <canvas ref="canvasRef" :style="canvasStyle" />
    
    <!-- Value text -->
    <p v-if="showValue && !loading && !error" :class="valueClasses">
      {{ formattedValue }}
    </p>
    
    <!-- Loading state -->
    <div v-if="loading" :class="loadingClasses">
      <div class="animate-pulse bg-gray-300 dark:bg-gray-600 rounded" :style="placeholderStyle" />
    </div>
    
    <!-- Error state -->
    <div v-if="error" :class="errorClasses">
      <ExclamationTriangleIcon class="w-5 h-5" />
      <span>{{ error }}</span>
    </div>
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
  
  // Format type
  format: {
    type: String,
    default: 'CODE128',
    validator: (v) => ['CODE128', 'CODE39', 'EAN13', 'EAN8', 'UPC', 'ITF14'].includes(v)
  },
  
  // Dimensions
  width: {
    type: Number,
    default: 2 // Module width
  },
  height: {
    type: Number,
    default: 100
  },
  
  // Colors
  lineColor: {
    type: String,
    default: '#000000'
  },
  background: {
    type: String,
    default: '#ffffff'
  },
  
  // Display
  showValue: {
    type: Boolean,
    default: true
  },
  fontSize: {
    type: Number,
    default: 14
  },
  textAlign: {
    type: String,
    default: 'center',
    validator: (v) => ['left', 'center', 'right'].includes(v)
  },
  textMargin: {
    type: Number,
    default: 2
  },
  
  // Margins
  margin: {
    type: Number,
    default: 10
  },
  marginTop: {
    type: Number,
    default: null
  },
  marginBottom: {
    type: Number,
    default: null
  },
  marginLeft: {
    type: Number,
    default: null
  },
  marginRight: {
    type: Number,
    default: null
  },
  
  // Flat style (no gradient)
  flat: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['ready', 'error'])

const canvasRef = ref(null)
const loading = ref(true)
const error = ref('')
const barcodeWidth = ref(200)

// Formatted value for display
const formattedValue = computed(() => {
  if (props.format === 'EAN13' || props.format === 'EAN8') {
    // Add spaces for readability
    return props.value.split('').reduce((acc, char, i) => {
      if (i === 1 || (i > 1 && i % 6 === 1)) return acc + ' ' + char
      return acc + char
    }, '')
  }
  return props.value
})

// Canvas style
const canvasStyle = computed(() => ({
  maxWidth: '100%',
  height: 'auto'
}))

// Container classes
const containerClasses = computed(() => [
  'inline-flex flex-col items-center'
])

// Value text classes
const valueClasses = computed(() => [
  'font-mono tracking-wider',
  'text-gray-900 dark:text-gray-100',
  {
    'text-left': props.textAlign === 'left',
    'text-center': props.textAlign === 'center',
    'text-right': props.textAlign === 'right'
  }
])

// Loading classes
const loadingClasses = computed(() => [
  'flex items-center justify-center'
])

// Placeholder style
const placeholderStyle = computed(() => ({
  width: '200px',
  height: `${props.height}px`
}))

// Error classes
const errorClasses = computed(() => [
  'flex items-center gap-2 p-4',
  'text-sm text-red-600 dark:text-red-400',
  'bg-red-50 dark:bg-red-900/20 rounded-lg'
])

// Barcode encoding patterns
const CODE128_PATTERNS = {
  ' ': '11011001100', '!': '11001101100', '"': '11001100110',
  '#': '10010011000', '$': '10010001100', '%': '10001001100',
  '&': '10011001000', "'": '10011000100', '(': '10001100100',
  ')': '11001001000', '*': '11001000100', '+': '11000100100',
  ',': '10110011100', '-': '10011011100', '.': '10011001110',
  '/': '10111001100', '0': '10011101100', '1': '10011100110',
  '2': '11001110010', '3': '11001011100', '4': '11001001110',
  '5': '11011100100', '6': '11001110100', '7': '11101101110',
  '8': '11101001100', '9': '11100101100', ':': '11100100110',
  ';': '11101100100', '<': '11100110100', '=': '11100110010',
  '>': '11011011000', '?': '11011000110', '@': '11000110110',
  'A': '10100011000', 'B': '10001011000', 'C': '10001000110',
  'D': '10110001000', 'E': '10001101000', 'F': '10001100010',
  'G': '11010001000', 'H': '11000101000', 'I': '11000100010',
  'J': '10110111000', 'K': '10110001110', 'L': '10001101110',
  'M': '10111011000', 'N': '10111000110', 'O': '10001110110',
  'P': '11101110110', 'Q': '11010001110', 'R': '11000101110',
  'S': '11011101000', 'T': '11011100010', 'U': '11011101110',
  'V': '11101011000', 'W': '11101000110', 'X': '11100010110',
  'Y': '11101101000', 'Z': '11101100010', '[': '11100011010',
  '\\': '11101111010', ']': '11001000010', '^': '11110001010',
  '_': '10100110000', '`': '10100001100', 'a': '10010110000',
  'b': '10010000110', 'c': '10000101100', 'd': '10000100110',
  'e': '10110010000', 'f': '10110000100', 'g': '10011010000',
  'h': '10011000010', 'i': '10000110100', 'j': '10000110010',
  'k': '11000010010', 'l': '11001010000', 'm': '11110111010',
  'n': '11000010100', 'o': '10001111010', 'p': '10100111100',
  'q': '10010111100', 'r': '10010011110', 's': '10111100100',
  't': '10011110100', 'u': '10011110010', 'v': '11110100100',
  'w': '11110010100', 'x': '11110010010', 'y': '11011011110',
  'z': '11011110110', '{': '11110110110', '|': '10101111000',
  '}': '10100011110', '~': '10001011110'
}

const START_CODE_B = '11010010000'
const STOP_CODE = '1100011101011'

// Generate barcode
function generateBarcode() {
  if (!canvasRef.value || !props.value) return
  
  loading.value = true
  error.value = ''
  
  try {
    const canvas = canvasRef.value
    const ctx = canvas.getContext('2d')
    
    // Encode the value
    let encoded = ''
    
    if (props.format === 'CODE128') {
      encoded = encodeCode128(props.value)
    } else if (props.format === 'CODE39') {
      encoded = encodeCode39(props.value)
    } else {
      // Fallback to CODE128
      encoded = encodeCode128(props.value)
    }
    
    // Calculate dimensions
    const moduleWidth = props.width
    const totalWidth = encoded.length * moduleWidth + getMarginLeft() + getMarginRight()
    const totalHeight = props.height + getMarginTop() + getMarginBottom()
    
    // Set canvas size
    const scale = window.devicePixelRatio || 1
    canvas.width = totalWidth * scale
    canvas.height = totalHeight * scale
    canvas.style.width = `${totalWidth}px`
    canvas.style.height = `${totalHeight}px`
    ctx.scale(scale, scale)
    
    barcodeWidth.value = totalWidth
    
    // Draw background
    ctx.fillStyle = props.background
    ctx.fillRect(0, 0, totalWidth, totalHeight)
    
    // Draw bars
    ctx.fillStyle = props.lineColor
    let x = getMarginLeft()
    
    for (let i = 0; i < encoded.length; i++) {
      if (encoded[i] === '1') {
        ctx.fillRect(x, getMarginTop(), moduleWidth, props.height)
      }
      x += moduleWidth
    }
    
    loading.value = false
    emit('ready', { canvas, dataUrl: canvas.toDataURL() })
  } catch (e) {
    error.value = 'Invalid barcode value'
    loading.value = false
    emit('error', e)
  }
}

// CODE128 encoding
function encodeCode128(value) {
  let encoded = START_CODE_B
  let checksum = 104 // Start Code B value
  
  for (let i = 0; i < value.length; i++) {
    const char = value[i]
    const pattern = CODE128_PATTERNS[char]
    
    if (!pattern) {
      throw new Error(`Invalid character: ${char}`)
    }
    
    encoded += pattern
    checksum += (char.charCodeAt(0) - 32) * (i + 1)
  }
  
  // Add checksum character
  const checksumChar = String.fromCharCode((checksum % 103) + 32)
  encoded += CODE128_PATTERNS[checksumChar] || CODE128_PATTERNS[' ']
  
  // Add stop code
  encoded += STOP_CODE
  
  return encoded
}

// CODE39 encoding
function encodeCode39(value) {
  const patterns = {
    '0': '101001101101', '1': '110100101011', '2': '101100101011',
    '3': '110110010101', '4': '101001101011', '5': '110100110101',
    '6': '101100110101', '7': '101001011011', '8': '110100101101',
    '9': '101100101101', 'A': '110101001011', 'B': '101101001011',
    'C': '110110100101', 'D': '101011001011', 'E': '110101100101',
    'F': '101101100101', 'G': '101010011011', 'H': '110101001101',
    'I': '101101001101', 'J': '101011001101', 'K': '110101010011',
    'L': '101101010011', 'M': '110110101001', 'N': '101011010011',
    'O': '110101101001', 'P': '101101101001', 'Q': '101010110011',
    'R': '110101011001', 'S': '101101011001', 'T': '101011011001',
    'U': '110010101011', 'V': '100110101011', 'W': '110011010101',
    'X': '100101101011', 'Y': '110010110101', 'Z': '100110110101',
    '-': '100101011011', '.': '110010101101', ' ': '100110101101',
    '$': '100100100101', '/': '100100101001', '+': '100101001001',
    '%': '101001001001', '*': '100101101101'
  }
  
  let encoded = patterns['*'] + '0' // Start character + gap
  
  for (const char of value.toUpperCase()) {
    if (!patterns[char]) {
      throw new Error(`Invalid character: ${char}`)
    }
    encoded += patterns[char] + '0' // Character + gap
  }
  
  encoded += patterns['*'] // Stop character
  
  return encoded
}

// Margin helpers
function getMarginTop() {
  return props.marginTop !== null ? props.marginTop : props.margin
}

function getMarginBottom() {
  return props.marginBottom !== null ? props.marginBottom : props.margin
}

function getMarginLeft() {
  return props.marginLeft !== null ? props.marginLeft : props.margin
}

function getMarginRight() {
  return props.marginRight !== null ? props.marginRight : props.margin
}

// Download barcode as image
function download(format = 'png') {
  if (!canvasRef.value) return
  
  const canvas = canvasRef.value
  const link = document.createElement('a')
  link.download = `barcode-${props.value}.${format}`
  link.href = canvas.toDataURL(`image/${format}`)
  link.click()
}

// Get data URL
function getDataUrl(format = 'png') {
  if (!canvasRef.value) return null
  return canvasRef.value.toDataURL(`image/${format}`)
}

// Watch for changes
watch(() => [props.value, props.format, props.width, props.height, props.lineColor, props.background], () => {
  generateBarcode()
}, { deep: true })

onMounted(() => {
  generateBarcode()
})

// Expose methods
defineExpose({
  download,
  getDataUrl,
  regenerate: generateBarcode
})
</script>
