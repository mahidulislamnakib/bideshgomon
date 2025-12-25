<template>
  <div :class="containerClasses">
    <!-- Header -->
    <div v-if="showHeader" :class="headerClasses">
      <!-- Window controls (macOS style) -->
      <div v-if="showControls" class="flex items-center gap-2">
        <button
          v-if="controls.includes('close')"
          class="w-3 h-3 rounded-full bg-red-500 hover:bg-red-600 transition-colors"
          @click="emit('close')"
        />
        <button
          v-if="controls.includes('minimize')"
          class="w-3 h-3 rounded-full bg-yellow-500 hover:bg-yellow-600 transition-colors"
          @click="emit('minimize')"
        />
        <button
          v-if="controls.includes('maximize')"
          class="w-3 h-3 rounded-full bg-green-500 hover:bg-green-600 transition-colors"
          @click="emit('maximize')"
        />
      </div>
      
      <!-- Filename or title -->
      <div 
        v-if="filename || title"
        :class="[
          'flex-1 text-center text-sm font-medium',
          theme === 'dark' ? 'text-gray-400' : 'text-gray-600'
        ]"
      >
        {{ filename || title }}
      </div>
      
      <!-- Language badge -->
      <div 
        v-if="language && showLanguage"
        :class="[
          'text-xs font-medium px-2 py-0.5 rounded',
          theme === 'dark' ? 'bg-gray-700 text-gray-300' : 'bg-gray-200 text-gray-600'
        ]"
      >
        {{ languageLabel }}
      </div>
      
      <!-- Copy button -->
      <button
        v-if="showCopy"
        :class="copyButtonClasses"
        @click="copyCode"
        :title="copied ? 'Copied!' : 'Copy code'"
      >
        <svg v-if="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <svg v-else class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </button>
    </div>
    
    <!-- Code content -->
    <div :class="codeContainerClasses">
      <!-- Line numbers -->
      <div 
        v-if="showLineNumbers"
        :class="lineNumbersClasses"
        aria-hidden="true"
      >
        <div
          v-for="line in lineCount"
          :key="line"
          :class="[
            'text-right pr-4 select-none',
            highlightedLines.includes(line) && 'bg-yellow-500/20'
          ]"
        >
          {{ line }}
        </div>
      </div>
      
      <!-- Code -->
      <pre :class="preClasses"><code 
        ref="codeRef"
        :class="codeClasses"
      ><template v-for="(line, index) in codeLines" :key="index"><span 
          :class="[
            'block',
            highlightedLines.includes(index + 1) && 'bg-yellow-500/20 -mx-4 px-4'
          ]"
        >{{ line }}</span></template></code></pre>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  // Code content
  code: {
    type: String,
    required: true
  },
  language: {
    type: String,
    default: 'text'
  },
  
  // Display
  filename: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  
  // Theme
  theme: {
    type: String,
    default: 'dark',
    validator: (v) => ['dark', 'light', 'github', 'dracula', 'nord', 'monokai'].includes(v)
  },
  
  // Features
  showLineNumbers: {
    type: Boolean,
    default: true
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showCopy: {
    type: Boolean,
    default: true
  },
  showLanguage: {
    type: Boolean,
    default: true
  },
  showControls: {
    type: Boolean,
    default: true
  },
  controls: {
    type: Array,
    default: () => ['close', 'minimize', 'maximize']
  },
  
  // Highlighting
  highlightedLines: {
    type: Array,
    default: () => []
  },
  
  // Sizing
  maxHeight: {
    type: String,
    default: ''
  },
  
  // Wrap
  wrap: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['copy', 'close', 'minimize', 'maximize'])

const codeRef = ref(null)
const copied = ref(false)

// Parse code lines
const codeLines = computed(() => {
  return props.code.split('\n')
})

const lineCount = computed(() => codeLines.value.length)

// Language labels
const languageLabel = computed(() => {
  const labels = {
    js: 'JavaScript',
    javascript: 'JavaScript',
    ts: 'TypeScript',
    typescript: 'TypeScript',
    py: 'Python',
    python: 'Python',
    php: 'PHP',
    html: 'HTML',
    css: 'CSS',
    scss: 'SCSS',
    sass: 'Sass',
    vue: 'Vue',
    jsx: 'JSX',
    tsx: 'TSX',
    json: 'JSON',
    yaml: 'YAML',
    yml: 'YAML',
    md: 'Markdown',
    markdown: 'Markdown',
    sql: 'SQL',
    bash: 'Bash',
    sh: 'Shell',
    shell: 'Shell',
    powershell: 'PowerShell',
    ps1: 'PowerShell',
    go: 'Go',
    rust: 'Rust',
    java: 'Java',
    kotlin: 'Kotlin',
    swift: 'Swift',
    ruby: 'Ruby',
    r: 'R',
    cpp: 'C++',
    c: 'C',
    csharp: 'C#',
    cs: 'C#',
    text: 'Text',
    plain: 'Plain Text'
  }
  return labels[props.language.toLowerCase()] || props.language.toUpperCase()
})

// Theme classes
const themeClasses = computed(() => {
  const themes = {
    dark: {
      container: 'bg-gray-900 border-gray-700',
      header: 'bg-gray-800 border-gray-700',
      code: 'text-gray-100',
      lineNumbers: 'text-gray-500 bg-gray-800/50'
    },
    light: {
      container: 'bg-gray-50 border-gray-200',
      header: 'bg-gray-100 border-gray-200',
      code: 'text-gray-900',
      lineNumbers: 'text-gray-400 bg-gray-100'
    },
    github: {
      container: 'bg-[#0d1117] border-[#30363d]',
      header: 'bg-[#161b22] border-[#30363d]',
      code: 'text-[#c9d1d9]',
      lineNumbers: 'text-[#6e7681] bg-[#0d1117]'
    },
    dracula: {
      container: 'bg-[#282a36] border-[#44475a]',
      header: 'bg-[#21222c] border-[#44475a]',
      code: 'text-[#f8f8f2]',
      lineNumbers: 'text-[#6272a4] bg-[#282a36]'
    },
    nord: {
      container: 'bg-[#2e3440] border-[#3b4252]',
      header: 'bg-[#3b4252] border-[#434c5e]',
      code: 'text-[#d8dee9]',
      lineNumbers: 'text-[#4c566a] bg-[#2e3440]'
    },
    monokai: {
      container: 'bg-[#272822] border-[#3e3d32]',
      header: 'bg-[#1e1f1c] border-[#3e3d32]',
      code: 'text-[#f8f8f2]',
      lineNumbers: 'text-[#75715e] bg-[#272822]'
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
  'flex items-center gap-3 px-4 py-2 border-b',
  themeClasses.value.header
])

// Code container classes
const codeContainerClasses = computed(() => [
  'flex overflow-auto',
  props.maxHeight && `max-h-[${props.maxHeight}]`
])

// Line numbers classes
const lineNumbersClasses = computed(() => [
  'flex-shrink-0 py-4 text-xs leading-6 border-r border-gray-700/50',
  themeClasses.value.lineNumbers
])

// Pre classes
const preClasses = computed(() => [
  'm-0 p-4 flex-1 overflow-x-auto',
  !props.wrap && 'whitespace-pre'
])

// Code classes
const codeClasses = computed(() => [
  'block text-sm leading-6',
  themeClasses.value.code,
  props.wrap && 'whitespace-pre-wrap break-words'
])

// Copy button classes
const copyButtonClasses = computed(() => [
  'p-1.5 rounded transition-colors',
  props.theme === 'dark' 
    ? 'text-gray-400 hover:text-gray-200 hover:bg-gray-700' 
    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200'
])

// Copy code
async function copyCode() {
  try {
    await navigator.clipboard.writeText(props.code)
    copied.value = true
    emit('copy', props.code)
    
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy code:', err)
  }
}

// Expose for parent control
defineExpose({
  copyCode,
  copied
})
</script>
