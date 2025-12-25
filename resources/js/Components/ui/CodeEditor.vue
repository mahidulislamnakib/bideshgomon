<template>
  <div :class="['code-editor rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
      <div class="flex items-center gap-3">
        <select
          v-model="currentLanguage"
          class="px-3 py-1.5 text-sm bg-transparent border border-gray-200 dark:border-gray-600 rounded-lg"
        >
          <option v-for="lang in languages" :key="lang.id" :value="lang.id">{{ lang.label }}</option>
        </select>
        <span class="text-xs text-gray-500">{{ lineCount }} lines</span>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="showCopy"
          type="button"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg"
          @click="copyCode"
          :title="copied ? 'Copied!' : 'Copy'"
        >
          <ClipboardDocumentCheckIcon v-if="copied" class="w-4 h-4 text-green-500" />
          <ClipboardIcon v-else class="w-4 h-4" />
        </button>
        <button
          v-if="showFormat"
          type="button"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg"
          @click="formatCode"
          title="Format Code"
        >
          <CodeBracketIcon class="w-4 h-4" />
        </button>
        <button
          v-if="showFullscreen"
          type="button"
          class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg"
          @click="toggleFullscreen"
          title="Fullscreen"
        >
          <ArrowsPointingOutIcon v-if="!isFullscreen" class="w-4 h-4" />
          <ArrowsPointingInIcon v-else class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Editor Area -->
    <div class="relative flex" :style="{ height: isFullscreen ? 'calc(100vh - 100px)' : height }">
      <!-- Line Numbers -->
      <div
        v-if="showLineNumbers"
        class="flex-shrink-0 py-4 px-3 text-right select-none bg-gray-100 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700"
        :style="{ minWidth: '50px' }"
      >
        <div
          v-for="line in lineCount"
          :key="line"
          :class="[
            'text-xs font-mono leading-6',
            activeLine === line ? 'text-blue-500' : 'text-gray-400'
          ]"
        >
          {{ line }}
        </div>
      </div>

      <!-- Code Input -->
      <div class="flex-1 relative overflow-auto">
        <!-- Highlighted Code (Background) -->
        <pre
          ref="highlightRef"
          class="absolute inset-0 p-4 m-0 font-mono text-sm leading-6 pointer-events-none overflow-hidden whitespace-pre-wrap break-words"
          :class="languageClass"
          aria-hidden="true"
        ><code v-html="highlightedCode"></code></pre>

        <!-- Textarea (Foreground) -->
        <textarea
          ref="textareaRef"
          v-model="code"
          :placeholder="placeholder"
          :readonly="readonly"
          :disabled="disabled"
          class="absolute inset-0 w-full h-full p-4 m-0 font-mono text-sm leading-6 bg-transparent border-0 outline-none resize-none text-transparent caret-gray-900 dark:caret-white"
          spellcheck="false"
          autocomplete="off"
          autocorrect="off"
          autocapitalize="off"
          @input="handleInput"
          @scroll="syncScroll"
          @keydown="handleKeydown"
          @click="updateActiveLine"
        />
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showFooter" class="flex items-center justify-between px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
      <div class="flex items-center gap-4 text-xs text-gray-500">
        <span>Line {{ activeLine }}, Col {{ activeColumn }}</span>
        <span>{{ currentLanguageLabel }}</span>
      </div>
      <div class="flex items-center gap-2 text-xs text-gray-500">
        <span>{{ code.length }} chars</span>
        <span>{{ tabSize }} spaces</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import {
  ClipboardIcon,
  ClipboardDocumentCheckIcon,
  CodeBracketIcon,
  ArrowsPointingOutIcon,
  ArrowsPointingInIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: String, default: '' },
  language: { type: String, default: 'javascript' },
  placeholder: { type: String, default: 'Enter code...' },
  height: { type: String, default: '300px' },
  tabSize: { type: Number, default: 2 },
  readonly: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  showLineNumbers: { type: Boolean, default: true },
  showCopy: { type: Boolean, default: true },
  showFormat: { type: Boolean, default: true },
  showFullscreen: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true },
  theme: { type: String, default: 'dark' }
})

const emit = defineEmits(['update:modelValue', 'change'])

const textareaRef = ref(null)
const highlightRef = ref(null)
const code = ref(props.modelValue)
const currentLanguage = ref(props.language)
const activeLine = ref(1)
const activeColumn = ref(1)
const copied = ref(false)
const isFullscreen = ref(false)

const languages = [
  { id: 'javascript', label: 'JavaScript', ext: 'js' },
  { id: 'typescript', label: 'TypeScript', ext: 'ts' },
  { id: 'html', label: 'HTML', ext: 'html' },
  { id: 'css', label: 'CSS', ext: 'css' },
  { id: 'json', label: 'JSON', ext: 'json' },
  { id: 'php', label: 'PHP', ext: 'php' },
  { id: 'python', label: 'Python', ext: 'py' },
  { id: 'sql', label: 'SQL', ext: 'sql' },
  { id: 'bash', label: 'Bash', ext: 'sh' },
  { id: 'markdown', label: 'Markdown', ext: 'md' }
]

const themeClasses = computed(() =>
  props.theme === 'dark'
    ? 'bg-gray-900 border-gray-700 text-gray-100'
    : 'bg-white border-gray-200 text-gray-900'
)

const languageClass = computed(() => `language-${currentLanguage.value}`)

const currentLanguageLabel = computed(() =>
  languages.find(l => l.id === currentLanguage.value)?.label || currentLanguage.value
)

const lineCount = computed(() => {
  const lines = code.value.split('\n').length
  return Math.max(lines, 1)
})

// Simple syntax highlighting
const highlightedCode = computed(() => {
  let html = escapeHtml(code.value)
  
  const patterns = {
    javascript: [
      { regex: /(\/\/.*$)/gm, class: 'text-gray-500' },
      { regex: /(\/\*[\s\S]*?\*\/)/g, class: 'text-gray-500' },
      { regex: /\b(const|let|var|function|return|if|else|for|while|class|import|export|from|default|async|await|try|catch|throw|new)\b/g, class: 'text-purple-400' },
      { regex: /\b(true|false|null|undefined|NaN|Infinity)\b/g, class: 'text-orange-400' },
      { regex: /\b(\d+\.?\d*)\b/g, class: 'text-orange-400' },
      { regex: /(['"`])((?:\\.|(?!\1)[^\\])*?)\1/g, class: 'text-green-400' },
      { regex: /\b([A-Z][a-zA-Z0-9]*)\b/g, class: 'text-yellow-400' }
    ],
    html: [
      { regex: /(&lt;\/?[a-zA-Z][a-zA-Z0-9]*)/g, class: 'text-blue-400' },
      { regex: /(\s[a-zA-Z-]+)(=)/g, class: 'text-yellow-400' },
      { regex: /(".*?")/g, class: 'text-green-400' },
      { regex: /(&lt;!--[\s\S]*?--&gt;)/g, class: 'text-gray-500' }
    ],
    css: [
      { regex: /(\/\*[\s\S]*?\*\/)/g, class: 'text-gray-500' },
      { regex: /([.#][a-zA-Z_-][a-zA-Z0-9_-]*)/g, class: 'text-yellow-400' },
      { regex: /\b([a-z-]+)(?=\s*:)/g, class: 'text-blue-400' },
      { regex: /(:\s*)([^;{}]+)/g, class: 'text-green-400' }
    ],
    json: [
      { regex: /("(?:[^"\\]|\\.)*")(?=\s*:)/g, class: 'text-blue-400' },
      { regex: /:\s*("(?:[^"\\]|\\.)*")/g, class: 'text-green-400' },
      { regex: /:\s*(\d+\.?\d*)/g, class: 'text-orange-400' },
      { regex: /:\s*(true|false|null)/g, class: 'text-purple-400' }
    ]
  }
  
  const langPatterns = patterns[currentLanguage.value] || patterns.javascript
  
  langPatterns.forEach(({ regex, class: className }) => {
    html = html.replace(regex, (match, ...groups) => {
      const content = groups[0] || match
      return `<span class="${className}">${content}</span>${match.slice(content.length)}`
    })
  })
  
  return html
})

const escapeHtml = (text) => {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

const handleInput = () => {
  emit('update:modelValue', code.value)
  emit('change', code.value)
  updateActiveLine()
}

const handleKeydown = (e) => {
  // Tab handling
  if (e.key === 'Tab') {
    e.preventDefault()
    const start = e.target.selectionStart
    const end = e.target.selectionEnd
    const spaces = ' '.repeat(props.tabSize)
    
    code.value = code.value.substring(0, start) + spaces + code.value.substring(end)
    
    nextTick(() => {
      e.target.selectionStart = e.target.selectionEnd = start + props.tabSize
    })
  }
  
  // Auto-close brackets
  const pairs = { '(': ')', '[': ']', '{': '}', '"': '"', "'": "'", '`': '`' }
  if (pairs[e.key]) {
    e.preventDefault()
    const start = e.target.selectionStart
    const end = e.target.selectionEnd
    const selected = code.value.substring(start, end)
    
    code.value = code.value.substring(0, start) + e.key + selected + pairs[e.key] + code.value.substring(end)
    
    nextTick(() => {
      e.target.selectionStart = e.target.selectionEnd = start + 1 + selected.length
    })
  }
}

const updateActiveLine = () => {
  if (textareaRef.value) {
    const pos = textareaRef.value.selectionStart
    const lines = code.value.substring(0, pos).split('\n')
    activeLine.value = lines.length
    activeColumn.value = lines[lines.length - 1].length + 1
  }
}

const syncScroll = () => {
  if (highlightRef.value && textareaRef.value) {
    highlightRef.value.scrollTop = textareaRef.value.scrollTop
    highlightRef.value.scrollLeft = textareaRef.value.scrollLeft
  }
}

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(code.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch (e) {
    console.error('Copy failed:', e)
  }
}

const formatCode = () => {
  // Basic JSON formatting
  if (currentLanguage.value === 'json') {
    try {
      code.value = JSON.stringify(JSON.parse(code.value), null, props.tabSize)
    } catch (e) {
      console.error('Invalid JSON')
    }
  }
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

watch(() => props.modelValue, (newVal) => {
  if (newVal !== code.value) {
    code.value = newVal
  }
})

watch(() => props.language, (newVal) => {
  currentLanguage.value = newVal
})
</script>

<style scoped>
textarea::selection {
  background: rgba(59, 130, 246, 0.3);
}
</style>
