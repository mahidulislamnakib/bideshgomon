<template>
  <div :class="containerClasses">
    <!-- Header -->
    <div v-if="showHeader" :class="headerClasses">
      <!-- Window controls -->
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-red-500" />
        <div class="w-3 h-3 rounded-full bg-yellow-500" />
        <div class="w-3 h-3 rounded-full bg-green-500" />
      </div>
      
      <!-- Title -->
      <div class="flex-1 text-center text-sm font-medium text-gray-400">
        {{ title }}
      </div>
      
      <!-- Spacer -->
      <div class="w-14" />
    </div>
    
    <!-- Terminal content -->
    <div 
      ref="contentRef"
      :class="contentClasses"
      :style="{ maxHeight }"
      @click="focusInput"
    >
      <!-- Output lines -->
      <div
        v-for="(line, index) in outputLines"
        :key="index"
        :class="getLineClasses(line)"
      >
        <span v-if="line.type === 'command'" class="text-green-400">{{ prompt }}</span>
        <span :class="getTextClasses(line)">{{ line.text }}</span>
      </div>
      
      <!-- Current input line -->
      <div v-if="interactive" class="flex items-center">
        <span class="text-green-400">{{ prompt }}</span>
        <input
          ref="inputRef"
          v-model="currentInput"
          type="text"
          :class="inputClasses"
          @keydown="handleKeydown"
          @keydown.up.prevent="historyUp"
          @keydown.down.prevent="historyDown"
          @keydown.tab.prevent="handleTab"
        />
        <span 
          v-if="showCursor"
          :class="cursorClasses"
        >▋</span>
      </div>
      
      <!-- Loading indicator -->
      <div v-if="loading" class="flex items-center gap-2 text-gray-400">
        <span class="text-green-400">{{ prompt }}</span>
        <span class="animate-pulse">{{ loadingText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'

const props = defineProps({
  // Display
  title: {
    type: String,
    default: 'Terminal'
  },
  prompt: {
    type: String,
    default: '$ '
  },
  
  // Content
  initialLines: {
    type: Array,
    default: () => []
  },
  
  // Behavior
  interactive: {
    type: Boolean,
    default: true
  },
  autoScroll: {
    type: Boolean,
    default: true
  },
  
  // Features
  showHeader: {
    type: Boolean,
    default: true
  },
  showCursor: {
    type: Boolean,
    default: true
  },
  
  // Sizing
  maxHeight: {
    type: String,
    default: '400px'
  },
  
  // Loading
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Processing...'
  },
  
  // Theme
  theme: {
    type: String,
    default: 'dark',
    validator: (v) => ['dark', 'light', 'matrix', 'retro'].includes(v)
  }
})

const emit = defineEmits(['command', 'clear', 'output'])

const contentRef = ref(null)
const inputRef = ref(null)
const currentInput = ref('')
const outputLines = ref([])
const commandHistory = ref([])
const historyIndex = ref(-1)

// Initialize with initial lines
onMounted(() => {
  if (props.initialLines.length > 0) {
    outputLines.value = props.initialLines.map(line => {
      if (typeof line === 'string') {
        return { type: 'output', text: line }
      }
      return line
    })
  }
})

// Theme classes
const themeClasses = computed(() => {
  const themes = {
    dark: {
      container: 'bg-gray-900 border-gray-700',
      header: 'bg-gray-800',
      content: 'bg-gray-900 text-gray-100',
      input: 'text-gray-100'
    },
    light: {
      container: 'bg-white border-gray-300',
      header: 'bg-gray-100',
      content: 'bg-white text-gray-900',
      input: 'text-gray-900'
    },
    matrix: {
      container: 'bg-black border-green-900',
      header: 'bg-green-900/30',
      content: 'bg-black text-green-400',
      input: 'text-green-400'
    },
    retro: {
      container: 'bg-amber-950 border-amber-800',
      header: 'bg-amber-900/50',
      content: 'bg-amber-950 text-amber-400',
      input: 'text-amber-400'
    }
  }
  return themes[props.theme]
})

// Container classes
const containerClasses = computed(() => [
  'rounded-lg border overflow-hidden font-mono text-sm',
  themeClasses.value.container
])

// Header classes
const headerClasses = computed(() => [
  'flex items-center gap-3 px-4 py-2',
  themeClasses.value.header
])

// Content classes
const contentClasses = computed(() => [
  'p-4 overflow-auto space-y-1',
  themeClasses.value.content
])

// Input classes
const inputClasses = computed(() => [
  'flex-1 bg-transparent border-none outline-none caret-transparent',
  themeClasses.value.input
])

// Cursor classes
const cursorClasses = computed(() => [
  'animate-pulse ml-0.5',
  props.theme === 'matrix' ? 'text-green-400' : 
  props.theme === 'retro' ? 'text-amber-400' : 
  'text-gray-100'
])

// Get line classes based on type
function getLineClasses(line) {
  return ['flex items-start gap-0']
}

// Get text classes based on line type
function getTextClasses(line) {
  const typeClasses = {
    command: '',
    output: 'text-gray-300',
    error: 'text-red-400',
    success: 'text-green-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return typeClasses[line.type] || 'text-gray-300'
}

// Handle keydown
function handleKeydown(e) {
  if (e.key === 'Enter') {
    e.preventDefault()
    executeCommand()
  } else if (e.key === 'c' && e.ctrlKey) {
    e.preventDefault()
    cancelCommand()
  } else if (e.key === 'l' && e.ctrlKey) {
    e.preventDefault()
    clear()
  }
}

// Execute command
function executeCommand() {
  const command = currentInput.value.trim()
  
  if (!command) return
  
  // Add to output
  outputLines.value.push({ type: 'command', text: command })
  
  // Add to history
  commandHistory.value.push(command)
  historyIndex.value = commandHistory.value.length
  
  // Handle built-in commands
  if (command === 'clear' || command === 'cls') {
    clear()
  } else {
    // Emit command for external handling
    emit('command', command)
  }
  
  // Clear input
  currentInput.value = ''
  
  // Scroll to bottom
  scrollToBottom()
}

// Cancel current command
function cancelCommand() {
  outputLines.value.push({ type: 'command', text: currentInput.value + '^C' })
  currentInput.value = ''
}

// Clear terminal
function clear() {
  outputLines.value = []
  emit('clear')
}

// Navigate command history
function historyUp() {
  if (historyIndex.value > 0) {
    historyIndex.value--
    currentInput.value = commandHistory.value[historyIndex.value]
  }
}

function historyDown() {
  if (historyIndex.value < commandHistory.value.length - 1) {
    historyIndex.value++
    currentInput.value = commandHistory.value[historyIndex.value]
  } else {
    historyIndex.value = commandHistory.value.length
    currentInput.value = ''
  }
}

// Tab completion placeholder
function handleTab() {
  // Could implement tab completion here
}

// Focus input
function focusInput() {
  inputRef.value?.focus()
}

// Scroll to bottom
function scrollToBottom() {
  if (props.autoScroll) {
    nextTick(() => {
      if (contentRef.value) {
        contentRef.value.scrollTop = contentRef.value.scrollHeight
      }
    })
  }
}

// Add output line programmatically
function addOutput(text, type = 'output') {
  outputLines.value.push({ type, text })
  scrollToBottom()
  emit('output', { type, text })
}

// Watch for loading changes
watch(() => props.loading, () => {
  scrollToBottom()
})

// Expose for parent control
defineExpose({
  addOutput,
  clear,
  focusInput,
  outputLines,
  commandHistory
})
</script>
