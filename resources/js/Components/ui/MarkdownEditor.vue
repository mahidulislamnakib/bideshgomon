<template>
  <div :class="['markdown-editor', containerClasses]">
    <!-- Toolbar -->
    <div
      v-if="showToolbar"
      :class="['flex flex-wrap items-center gap-1 px-3 py-2 border-b', themeClasses.toolbar]"
    >
      <!-- Text Formatting -->
      <div class="flex items-center gap-0.5">
        <button
          v-for="action in textActions"
          :key="action.name"
          type="button"
          :title="action.title"
          :class="['p-2 rounded transition-colors', themeClasses.button]"
          @click="executeAction(action)"
        >
          <component :is="action.icon" class="w-4 h-4" />
        </button>
      </div>

      <div :class="['w-px h-6 mx-1', themeClasses.divider]" />

      <!-- Headings -->
      <div class="flex items-center gap-0.5">
        <button
          v-for="n in 3"
          :key="`h${n}`"
          type="button"
          :title="`Heading ${n}`"
          :class="['px-2 py-1 rounded text-xs font-bold transition-colors', themeClasses.button]"
          @click="insertHeading(n)"
        >
          H{{ n }}
        </button>
      </div>

      <div :class="['w-px h-6 mx-1', themeClasses.divider]" />

      <!-- Lists & Structure -->
      <div class="flex items-center gap-0.5">
        <button
          v-for="action in listActions"
          :key="action.name"
          type="button"
          :title="action.title"
          :class="['p-2 rounded transition-colors', themeClasses.button]"
          @click="executeAction(action)"
        >
          <component :is="action.icon" class="w-4 h-4" />
        </button>
      </div>

      <div :class="['w-px h-6 mx-1', themeClasses.divider]" />

      <!-- Insert -->
      <div class="flex items-center gap-0.5">
        <button
          v-for="action in insertActions"
          :key="action.name"
          type="button"
          :title="action.title"
          :class="['p-2 rounded transition-colors', themeClasses.button]"
          @click="executeAction(action)"
        >
          <component :is="action.icon" class="w-4 h-4" />
        </button>
      </div>

      <div class="flex-1" />

      <!-- View Toggle -->
      <div :class="['flex rounded-lg overflow-hidden', themeClasses.viewToggle]">
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium transition-colors',
            mode === 'edit' ? themeClasses.viewActive : themeClasses.viewInactive
          ]"
          @click="mode = 'edit'"
        >
          Edit
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium transition-colors',
            mode === 'split' ? themeClasses.viewActive : themeClasses.viewInactive
          ]"
          @click="mode = 'split'"
        >
          Split
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium transition-colors',
            mode === 'preview' ? themeClasses.viewActive : themeClasses.viewInactive
          ]"
          @click="mode = 'preview'"
        >
          Preview
        </button>
      </div>

      <!-- Fullscreen -->
      <button
        v-if="allowFullscreen"
        type="button"
        title="Toggle fullscreen"
        :class="['p-2 rounded ml-2 transition-colors', themeClasses.button]"
        @click="toggleFullscreen"
      >
        <ArrowsPointingOutIcon v-if="!isFullscreen" class="w-4 h-4" />
        <ArrowsPointingInIcon v-else class="w-4 h-4" />
      </button>
    </div>

    <!-- Editor Area -->
    <div :class="['flex flex-1 min-h-0', mode === 'split' && 'divide-x', themeClasses.divide]">
      <!-- Textarea -->
      <div
        v-show="mode !== 'preview'"
        :class="['relative', mode === 'split' ? 'w-1/2' : 'w-full']"
      >
        <!-- Line Numbers -->
        <div
          v-if="showLineNumbers"
          ref="lineNumbersRef"
          :class="['absolute left-0 top-0 bottom-0 w-12 overflow-hidden select-none text-right pr-3 pt-3', themeClasses.lineNumbers]"
        >
          <div
            v-for="n in lineCount"
            :key="n"
            class="text-xs leading-6"
          >
            {{ n }}
          </div>
        </div>

        <textarea
          ref="textareaRef"
          :value="modelValue"
          :placeholder="placeholder"
          :disabled="disabled"
          :class="[
            'w-full h-full resize-none outline-none font-mono text-sm leading-6 p-3',
            showLineNumbers && 'pl-14',
            themeClasses.textarea
          ]"
          @input="handleInput"
          @keydown="handleKeydown"
          @scroll="syncScroll"
        />
      </div>

      <!-- Preview -->
      <div
        v-show="mode !== 'edit'"
        ref="previewRef"
        :class="[
          'overflow-auto p-4 prose prose-sm max-w-none',
          mode === 'split' ? 'w-1/2' : 'w-full',
          themeClasses.preview
        ]"
        v-html="renderedMarkdown"
      />
    </div>

    <!-- Status Bar -->
    <div
      v-if="showStatusBar"
      :class="['flex items-center justify-between px-3 py-1.5 border-t text-xs', themeClasses.statusBar]"
    >
      <div class="flex items-center gap-4">
        <span>{{ wordCount }} words</span>
        <span>{{ charCount }} characters</span>
        <span>{{ lineCount }} lines</span>
      </div>
      <div class="flex items-center gap-4">
        <span v-if="cursorPosition">
          Ln {{ cursorPosition.line }}, Col {{ cursorPosition.col }}
        </span>
        <span>Markdown</span>
      </div>
    </div>

    <!-- Link/Image Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
          <div class="absolute inset-0 bg-black/50" @click="closeModal" />
          <div :class="['relative w-full max-w-md rounded-xl shadow-2xl p-6', themeClasses.modal]">
            <h3 class="text-lg font-semibold mb-4">{{ modalTitle }}</h3>
            
            <div class="space-y-4">
              <div v-if="modalType === 'image'">
                <label class="block text-sm font-medium mb-1">Image URL</label>
                <input
                  v-model="modalData.url"
                  type="url"
                  placeholder="https://example.com/image.png"
                  :class="['w-full px-3 py-2 rounded-lg border', themeClasses.input]"
                />
              </div>
              
              <div v-if="modalType === 'link'">
                <label class="block text-sm font-medium mb-1">Link URL</label>
                <input
                  v-model="modalData.url"
                  type="url"
                  placeholder="https://example.com"
                  :class="['w-full px-3 py-2 rounded-lg border', themeClasses.input]"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">
                  {{ modalType === 'image' ? 'Alt Text' : 'Link Text' }}
                </label>
                <input
                  v-model="modalData.text"
                  type="text"
                  :placeholder="modalType === 'image' ? 'Image description' : 'Click here'"
                  :class="['w-full px-3 py-2 rounded-lg border', themeClasses.input]"
                />
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
              <button
                type="button"
                :class="['px-4 py-2 rounded-lg font-medium', themeClasses.buttonSecondary]"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="button"
                :class="['px-4 py-2 rounded-lg font-medium', themeClasses.buttonPrimary]"
                @click="confirmModal"
              >
                Insert
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { Transition, Teleport } from 'vue'
import {
  BoldIcon,
  ItalicIcon,
  CodeBracketIcon,
  ListBulletIcon,
  QueueListIcon,
  LinkIcon,
  PhotoIcon,
  MinusIcon,
  ChatBubbleBottomCenterTextIcon,
  ArrowsPointingOutIcon,
  ArrowsPointingInIcon,
  TableCellsIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Write your markdown here...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  showToolbar: {
    type: Boolean,
    default: true
  },
  showStatusBar: {
    type: Boolean,
    default: true
  },
  showLineNumbers: {
    type: Boolean,
    default: true
  },
  allowFullscreen: {
    type: Boolean,
    default: true
  },
  initialMode: {
    type: String,
    default: 'edit',
    validator: v => ['edit', 'split', 'preview'].includes(v)
  },
  minHeight: {
    type: String,
    default: '300px'
  },
  maxHeight: {
    type: String,
    default: 'none'
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'save'])

const textareaRef = ref(null)
const previewRef = ref(null)
const lineNumbersRef = ref(null)
const mode = ref(props.initialMode)
const isFullscreen = ref(false)
const showModal = ref(false)
const modalType = ref('link')
const modalData = ref({ url: '', text: '' })
const cursorPosition = ref({ line: 1, col: 1 })

// Strikethrough icon as inline SVG component
const StrikethroughIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="12" x2="20" y2="12"/><path d="M17.5 7.5c-.5-2-2.5-3-5-3-3 0-5.5 1.5-5.5 4 0 1.5 1 2.5 2.5 3"/><path d="M12 12c2.5.5 5.5 1.5 5.5 4 0 2.5-2.5 4-5.5 4-2.5 0-4.5-1-5-3"/></svg>`
}

const textActions = [
  { name: 'bold', icon: BoldIcon, title: 'Bold (Ctrl+B)', prefix: '**', suffix: '**' },
  { name: 'italic', icon: ItalicIcon, title: 'Italic (Ctrl+I)', prefix: '_', suffix: '_' },
  { name: 'strikethrough', icon: StrikethroughIcon, title: 'Strikethrough', prefix: '~~', suffix: '~~' },
  { name: 'code', icon: CodeBracketIcon, title: 'Inline Code', prefix: '`', suffix: '`' }
]

const listActions = [
  { name: 'ul', icon: ListBulletIcon, title: 'Bullet List', prefix: '- ', line: true },
  { name: 'ol', icon: QueueListIcon, title: 'Numbered List', prefix: '1. ', line: true },
  { name: 'quote', icon: ChatBubbleBottomCenterTextIcon, title: 'Quote', prefix: '> ', line: true }
]

const insertActions = [
  { name: 'link', icon: LinkIcon, title: 'Insert Link', modal: 'link' },
  { name: 'image', icon: PhotoIcon, title: 'Insert Image', modal: 'image' },
  { name: 'hr', icon: MinusIcon, title: 'Horizontal Rule', insert: '\n\n---\n\n' },
  { name: 'table', icon: TableCellsIcon, title: 'Insert Table', insert: '\n| Header 1 | Header 2 | Header 3 |\n|----------|----------|----------|\n| Cell 1   | Cell 2   | Cell 3   |\n| Cell 4   | Cell 5   | Cell 6   |\n' }
]

const containerClasses = computed(() => [
  'flex flex-col rounded-xl border overflow-hidden',
  isFullscreen.value ? 'fixed inset-0 z-50' : '',
  props.theme === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
])

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      toolbar: 'bg-gray-800 border-gray-700',
      button: 'text-gray-300 hover:bg-gray-700 hover:text-white',
      divider: 'bg-gray-700',
      viewToggle: 'bg-gray-700',
      viewActive: 'bg-blue-600 text-white',
      viewInactive: 'text-gray-400 hover:text-white',
      textarea: 'bg-gray-900 text-gray-100 placeholder-gray-500',
      lineNumbers: 'bg-gray-800 text-gray-500 border-r border-gray-700',
      preview: 'bg-gray-800 text-gray-100 prose-invert',
      divide: 'divide-gray-700',
      statusBar: 'bg-gray-800 border-gray-700 text-gray-400',
      modal: 'bg-gray-800 text-white',
      input: 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500',
      buttonPrimary: 'bg-blue-600 text-white hover:bg-blue-700',
      buttonSecondary: 'bg-gray-700 text-gray-300 hover:bg-gray-600'
    }
  }
  return {
    toolbar: 'bg-gray-50 border-gray-200',
    button: 'text-gray-600 hover:bg-gray-200 hover:text-gray-900',
    divider: 'bg-gray-300',
    viewToggle: 'bg-gray-200',
    viewActive: 'bg-white text-gray-900 shadow-sm',
    viewInactive: 'text-gray-600 hover:text-gray-900',
    textarea: 'bg-white text-gray-900 placeholder-gray-400',
    lineNumbers: 'bg-gray-50 text-gray-400 border-r border-gray-200',
    preview: 'bg-gray-50 text-gray-900',
    divide: 'divide-gray-200',
    statusBar: 'bg-gray-50 border-gray-200 text-gray-500',
    modal: 'bg-white text-gray-900',
    input: 'bg-white border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500',
    buttonPrimary: 'bg-blue-600 text-white hover:bg-blue-700',
    buttonSecondary: 'bg-gray-100 text-gray-700 hover:bg-gray-200'
  }
})

const wordCount = computed(() => {
  const text = props.modelValue.trim()
  return text ? text.split(/\s+/).length : 0
})

const charCount = computed(() => props.modelValue.length)

const lineCount = computed(() => {
  return props.modelValue ? props.modelValue.split('\n').length : 1
})

const modalTitle = computed(() => {
  return modalType.value === 'image' ? 'Insert Image' : 'Insert Link'
})

// Simple markdown to HTML renderer
const renderedMarkdown = computed(() => {
  let html = props.modelValue
  
  // Escape HTML
  html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
  
  // Code blocks
  html = html.replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code class="language-$1">$2</code></pre>')
  
  // Inline code
  html = html.replace(/`([^`]+)`/g, '<code>$1</code>')
  
  // Headers
  html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>')
  html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>')
  html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>')
  
  // Bold & Italic
  html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>')
  html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
  html = html.replace(/\*(.+?)\*/g, '<em>$1</em>')
  html = html.replace(/_(.+?)_/g, '<em>$1</em>')
  
  // Strikethrough
  html = html.replace(/~~(.+?)~~/g, '<del>$1</del>')
  
  // Links & Images
  html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="max-w-full h-auto rounded">')
  html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:underline">$1</a>')
  
  // Blockquotes
  html = html.replace(/^> (.+)$/gm, '<blockquote class="border-l-4 border-gray-300 pl-4 italic">$1</blockquote>')
  
  // Horizontal rules
  html = html.replace(/^---$/gm, '<hr class="my-4 border-gray-300">')
  
  // Lists
  html = html.replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal">$1</li>')
  html = html.replace(/^- (.+)$/gm, '<li class="ml-4 list-disc">$1</li>')
  
  // Paragraphs
  html = html.replace(/\n\n/g, '</p><p>')
  html = '<p>' + html + '</p>'
  html = html.replace(/<p><\/p>/g, '')
  
  return html
})

const handleInput = (e) => {
  emit('update:modelValue', e.target.value)
  emit('change', e.target.value)
  updateCursorPosition()
}

const handleKeydown = (e) => {
  // Ctrl/Cmd + B = Bold
  if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
    e.preventDefault()
    executeAction(textActions[0])
  }
  // Ctrl/Cmd + I = Italic
  if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
    e.preventDefault()
    executeAction(textActions[1])
  }
  // Ctrl/Cmd + S = Save
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault()
    emit('save', props.modelValue)
  }
  // Tab for indentation
  if (e.key === 'Tab') {
    e.preventDefault()
    insertText('  ')
  }
}

const executeAction = (action) => {
  if (action.modal) {
    modalType.value = action.modal
    modalData.value = { url: '', text: getSelectedText() }
    showModal.value = true
    return
  }
  
  if (action.insert) {
    insertText(action.insert)
    return
  }
  
  if (action.line) {
    wrapLine(action.prefix)
    return
  }
  
  wrapSelection(action.prefix, action.suffix)
}

const insertHeading = (level) => {
  const prefix = '#'.repeat(level) + ' '
  wrapLine(prefix)
}

const getSelectedText = () => {
  const textarea = textareaRef.value
  if (!textarea) return ''
  return textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
}

const insertText = (text) => {
  const textarea = textareaRef.value
  if (!textarea) return
  
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const value = textarea.value
  
  const newValue = value.substring(0, start) + text + value.substring(end)
  emit('update:modelValue', newValue)
  
  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + text.length, start + text.length)
  })
}

const wrapSelection = (prefix, suffix) => {
  const textarea = textareaRef.value
  if (!textarea) return
  
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const value = textarea.value
  const selected = value.substring(start, end) || 'text'
  
  const newValue = value.substring(0, start) + prefix + selected + suffix + value.substring(end)
  emit('update:modelValue', newValue)
  
  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + prefix.length, start + prefix.length + selected.length)
  })
}

const wrapLine = (prefix) => {
  const textarea = textareaRef.value
  if (!textarea) return
  
  const start = textarea.selectionStart
  const value = textarea.value
  
  // Find line start
  let lineStart = start
  while (lineStart > 0 && value[lineStart - 1] !== '\n') {
    lineStart--
  }
  
  const newValue = value.substring(0, lineStart) + prefix + value.substring(lineStart)
  emit('update:modelValue', newValue)
  
  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + prefix.length, start + prefix.length)
  })
}

const closeModal = () => {
  showModal.value = false
  modalData.value = { url: '', text: '' }
}

const confirmModal = () => {
  let text = ''
  if (modalType.value === 'image') {
    text = `![${modalData.value.text || 'Image'}](${modalData.value.url})`
  } else {
    text = `[${modalData.value.text || 'Link'}](${modalData.value.url})`
  }
  insertText(text)
  closeModal()
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

const updateCursorPosition = () => {
  const textarea = textareaRef.value
  if (!textarea) return
  
  const value = textarea.value.substring(0, textarea.selectionStart)
  const lines = value.split('\n')
  cursorPosition.value = {
    line: lines.length,
    col: lines[lines.length - 1].length + 1
  }
}

const syncScroll = () => {
  if (lineNumbersRef.value && textareaRef.value) {
    lineNumbersRef.value.scrollTop = textareaRef.value.scrollTop
  }
}

onMounted(() => {
  if (textareaRef.value) {
    textareaRef.value.style.minHeight = props.minHeight
    if (props.maxHeight !== 'none') {
      textareaRef.value.style.maxHeight = props.maxHeight
    }
  }
})

defineExpose({
  focus: () => textareaRef.value?.focus(),
  insertText,
  getSelectedText,
  setMode: (m) => mode.value = m
})
</script>

<style scoped>
.markdown-editor textarea {
  tab-size: 2;
}

.markdown-editor :deep(.prose) {
  max-width: none;
}

.markdown-editor :deep(.prose h1) {
  @apply text-2xl font-bold mt-6 mb-4;
}

.markdown-editor :deep(.prose h2) {
  @apply text-xl font-bold mt-5 mb-3;
}

.markdown-editor :deep(.prose h3) {
  @apply text-lg font-semibold mt-4 mb-2;
}

.markdown-editor :deep(.prose pre) {
  @apply bg-gray-800 text-gray-100 rounded-lg p-4 overflow-x-auto my-4;
}

.markdown-editor :deep(.prose code) {
  @apply bg-gray-100 text-pink-600 px-1 py-0.5 rounded text-sm;
}

.markdown-editor :deep(.prose pre code) {
  @apply bg-transparent text-gray-100 p-0;
}

.markdown-editor :deep(.prose blockquote) {
  @apply border-l-4 border-gray-300 pl-4 my-4 italic text-gray-600;
}
</style>
