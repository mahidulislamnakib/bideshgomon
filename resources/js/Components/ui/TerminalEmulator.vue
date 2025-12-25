<template>
  <div :class="['terminal-emulator rounded-lg overflow-hidden border', containerClasses]">
    <!-- Title Bar -->
    <div :class="['flex items-center justify-between px-4 py-2', themeClasses.titleBar]">
      <div class="flex items-center gap-2">
        <!-- Window Controls -->
        <div class="flex items-center gap-1.5">
          <button
            class="w-3 h-3 rounded-full bg-red-500 hover:bg-red-600 transition-colors"
            @click="$emit('close')"
          />
          <button
            class="w-3 h-3 rounded-full bg-yellow-500 hover:bg-yellow-600 transition-colors"
            @click="$emit('minimize')"
          />
          <button
            class="w-3 h-3 rounded-full bg-green-500 hover:bg-green-600 transition-colors"
            @click="$emit('maximize')"
          />
        </div>
        <span :class="['text-sm ml-2', themeClasses.titleText]">{{ title }}</span>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="clearTerminal"
          :class="['p-1 rounded transition-colors', themeClasses.iconBtn]"
          title="Clear"
        >
          <TrashIcon class="w-4 h-4" />
        </button>
        <button
          @click="copyOutput"
          :class="['p-1 rounded transition-colors', themeClasses.iconBtn]"
          title="Copy"
        >
          <ClipboardDocumentIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Terminal Content -->
    <div
      ref="terminalRef"
      :class="['overflow-y-auto font-mono text-sm p-4', themeClasses.content]"
      :style="{ height: `${height}px` }"
      @click="focusInput"
    >
      <!-- Output Lines -->
      <div
        v-for="(line, i) in lines"
        :key="i"
        :class="['whitespace-pre-wrap', getLineClass(line.type)]"
      >
        <span v-if="line.type === 'command'" :class="themeClasses.prompt">
          {{ prompt }}
        </span>
        <span v-html="formatOutput(line.content)" />
      </div>

      <!-- Current Input Line -->
      <div class="flex items-center">
        <span :class="themeClasses.prompt">{{ prompt }}</span>
        <div class="relative flex-1">
          <input
            ref="inputRef"
            v-model="currentInput"
            type="text"
            :class="[
              'w-full bg-transparent border-none outline-none font-mono',
              themeClasses.input
            ]"
            autocomplete="off"
            spellcheck="false"
            @keydown="handleKeydown"
          />
          <span
            v-if="showCursor"
            :class="['absolute animate-pulse', themeClasses.cursor]"
            :style="{ left: `${cursorPosition * 0.6}em` }"
          >
            █
          </span>
        </div>
      </div>
    </div>

    <!-- Status Bar -->
    <div v-if="showStatusBar" :class="['flex items-center justify-between px-4 py-1 text-xs', themeClasses.statusBar]">
      <div class="flex items-center gap-4">
        <span>{{ workingDirectory }}</span>
      </div>
      <div class="flex items-center gap-4">
        <span v-if="isProcessing" class="flex items-center gap-1">
          <div class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse" />
          Processing...
        </span>
        <span v-else class="flex items-center gap-1">
          <div class="w-2 h-2 rounded-full bg-green-500" />
          Ready
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue'
import {
  TrashIcon,
  ClipboardDocumentIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    default: 'Terminal'
  },
  prompt: {
    type: String,
    default: '$ '
  },
  theme: {
    type: String,
    default: 'dark',
    validator: v => ['light', 'dark', 'matrix', 'ubuntu'].includes(v)
  },
  height: {
    type: Number,
    default: 400
  },
  showStatusBar: {
    type: Boolean,
    default: true
  },
  workingDirectory: {
    type: String,
    default: '~'
  },
  welcomeMessage: {
    type: String,
    default: 'Welcome to Terminal Emulator. Type "help" for available commands.'
  },
  commands: {
    type: Object,
    default: () => ({})
  },
  autoFocus: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['command', 'close', 'minimize', 'maximize'])

const terminalRef = ref(null)
const inputRef = ref(null)
const currentInput = ref('')
const lines = ref([])
const commandHistory = ref([])
const historyIndex = ref(-1)
const isProcessing = ref(false)
const showCursor = ref(true)

const cursorPosition = computed(() => currentInput.value.length)

// Built-in commands
const builtinCommands = {
  help: () => {
    return `Available commands:
  help     - Show this help message
  clear    - Clear the terminal
  echo     - Echo text back
  date     - Show current date/time
  whoami   - Display current user
  pwd      - Print working directory
  history  - Show command history
  
Custom commands can be added via the 'commands' prop.`
  },
  clear: () => {
    lines.value = []
    return null
  },
  echo: (args) => args.join(' '),
  date: () => new Date().toString(),
  whoami: () => 'user',
  pwd: () => props.workingDirectory,
  history: () => commandHistory.value.map((cmd, i) => `  ${i + 1}  ${cmd}`).join('\n')
}

const executeCommand = async (input) => {
  const trimmed = input.trim()
  if (!trimmed) return

  // Add to history
  commandHistory.value.push(trimmed)
  historyIndex.value = commandHistory.value.length

  // Add command line to output
  lines.value.push({ type: 'command', content: trimmed })

  // Parse command
  const parts = trimmed.split(' ')
  const cmd = parts[0].toLowerCase()
  const args = parts.slice(1)

  isProcessing.value = true

  try {
    let output = null

    // Check custom commands first
    if (props.commands[cmd]) {
      output = await props.commands[cmd](args, {
        print: (text, type = 'output') => lines.value.push({ type, content: text }),
        clear: () => { lines.value = [] },
        prompt: props.prompt
      })
    }
    // Then built-in commands
    else if (builtinCommands[cmd]) {
      output = builtinCommands[cmd](args)
    }
    // Unknown command
    else {
      output = `Command not found: ${cmd}. Type "help" for available commands.`
      lines.value.push({ type: 'error', content: output })
      emit('command', { command: trimmed, args, error: true })
      isProcessing.value = false
      scrollToBottom()
      return
    }

    if (output !== null) {
      lines.value.push({ type: 'output', content: output })
    }

    emit('command', { command: cmd, args, output })
  } catch (error) {
    lines.value.push({ type: 'error', content: `Error: ${error.message}` })
  }

  isProcessing.value = false
  scrollToBottom()
}

const handleKeydown = (e) => {
  switch (e.key) {
    case 'Enter':
      executeCommand(currentInput.value)
      currentInput.value = ''
      break
    case 'ArrowUp':
      e.preventDefault()
      if (historyIndex.value > 0) {
        historyIndex.value--
        currentInput.value = commandHistory.value[historyIndex.value]
      }
      break
    case 'ArrowDown':
      e.preventDefault()
      if (historyIndex.value < commandHistory.value.length - 1) {
        historyIndex.value++
        currentInput.value = commandHistory.value[historyIndex.value]
      } else {
        historyIndex.value = commandHistory.value.length
        currentInput.value = ''
      }
      break
    case 'Tab':
      e.preventDefault()
      // Auto-complete (simplified)
      const partial = currentInput.value.split(' ').pop()
      const allCommands = [...Object.keys(builtinCommands), ...Object.keys(props.commands)]
      const matches = allCommands.filter(c => c.startsWith(partial))
      if (matches.length === 1) {
        const parts = currentInput.value.split(' ')
        parts[parts.length - 1] = matches[0]
        currentInput.value = parts.join(' ')
      } else if (matches.length > 1) {
        lines.value.push({ type: 'output', content: matches.join('  ') })
        scrollToBottom()
      }
      break
    case 'c':
      if (e.ctrlKey) {
        e.preventDefault()
        lines.value.push({ type: 'command', content: currentInput.value + '^C' })
        currentInput.value = ''
        isProcessing.value = false
      }
      break
    case 'l':
      if (e.ctrlKey) {
        e.preventDefault()
        clearTerminal()
      }
      break
  }
}

const clearTerminal = () => {
  lines.value = []
}

const copyOutput = async () => {
  const text = lines.value.map(l => {
    if (l.type === 'command') return `${props.prompt}${l.content}`
    return l.content
  }).join('\n')
  await navigator.clipboard.writeText(text)
}

const focusInput = () => {
  inputRef.value?.focus()
}

const scrollToBottom = () => {
  nextTick(() => {
    if (terminalRef.value) {
      terminalRef.value.scrollTop = terminalRef.value.scrollHeight
    }
  })
}

const formatOutput = (text) => {
  if (!text) return ''
  
  // Convert ANSI-like codes to HTML
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\[red\](.*?)\[\/red\]/g, '<span class="text-red-500">$1</span>')
    .replace(/\[green\](.*?)\[\/green\]/g, '<span class="text-green-500">$1</span>')
    .replace(/\[yellow\](.*?)\[\/yellow\]/g, '<span class="text-yellow-500">$1</span>')
    .replace(/\[blue\](.*?)\[\/blue\]/g, '<span class="text-blue-500">$1</span>')
    .replace(/\[bold\](.*?)\[\/bold\]/g, '<span class="font-bold">$1</span>')
}

const getLineClass = (type) => {
  switch (type) {
    case 'error':
      return 'text-red-500'
    case 'success':
      return 'text-green-500'
    case 'warning':
      return 'text-yellow-500'
    case 'info':
      return 'text-blue-500'
    default:
      return ''
  }
}

const containerClasses = computed(() => {
  const themes = {
    dark: 'bg-gray-900 border-gray-700',
    light: 'bg-white border-gray-300',
    matrix: 'bg-black border-green-800',
    ubuntu: 'bg-[#300a24] border-[#5c3566]'
  }
  return themes[props.theme]
})

const themeClasses = computed(() => {
  const themes = {
    dark: {
      titleBar: 'bg-gray-800',
      titleText: 'text-gray-400',
      iconBtn: 'text-gray-400 hover:bg-gray-700',
      content: 'bg-gray-900 text-gray-100',
      prompt: 'text-green-400',
      input: 'text-gray-100',
      cursor: 'text-gray-100',
      statusBar: 'bg-gray-800 text-gray-400'
    },
    light: {
      titleBar: 'bg-gray-100',
      titleText: 'text-gray-600',
      iconBtn: 'text-gray-500 hover:bg-gray-200',
      content: 'bg-white text-gray-900',
      prompt: 'text-blue-600',
      input: 'text-gray-900',
      cursor: 'text-gray-900',
      statusBar: 'bg-gray-100 text-gray-600'
    },
    matrix: {
      titleBar: 'bg-gray-900',
      titleText: 'text-green-500',
      iconBtn: 'text-green-500 hover:bg-gray-800',
      content: 'bg-black text-green-400',
      prompt: 'text-green-500',
      input: 'text-green-400',
      cursor: 'text-green-400',
      statusBar: 'bg-gray-900 text-green-500'
    },
    ubuntu: {
      titleBar: 'bg-[#3c1f32]',
      titleText: 'text-white',
      iconBtn: 'text-white/70 hover:bg-[#5c3566]',
      content: 'bg-[#300a24] text-white',
      prompt: 'text-green-400',
      input: 'text-white',
      cursor: 'text-white',
      statusBar: 'bg-[#3c1f32] text-white/70'
    }
  }
  return themes[props.theme]
})

// Show welcome message
onMounted(() => {
  if (props.welcomeMessage) {
    lines.value.push({ type: 'info', content: props.welcomeMessage })
  }
  if (props.autoFocus) {
    focusInput()
  }
})

// Cursor blink
let cursorInterval
onMounted(() => {
  cursorInterval = setInterval(() => {
    showCursor.value = !showCursor.value
  }, 530)
})

defineExpose({
  execute: (cmd) => executeCommand(cmd),
  clear: clearTerminal,
  print: (text, type = 'output') => {
    lines.value.push({ type, content: text })
    scrollToBottom()
  },
  focus: focusInput,
  getHistory: () => [...commandHistory.value]
})
</script>

<style scoped>
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

.animate-pulse {
  animation: blink 1s step-end infinite;
}
</style>
