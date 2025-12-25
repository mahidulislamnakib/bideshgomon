<template>
  <div :class="['markdown-preview rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg">
          <DocumentTextIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ wordCount }} words • {{ charCount }} characters
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
            viewMode === 'edit'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'edit'"
        >
          <PencilIcon class="w-4 h-4 inline mr-1" />
          Edit
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
            viewMode === 'split'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'split'"
        >
          <Squares2X2Icon class="w-4 h-4 inline mr-1" />
          Split
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
            viewMode === 'preview'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'preview'"
        >
          <EyeIcon class="w-4 h-4 inline mr-1" />
          Preview
        </button>
        <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Copy Markdown"
          @click="copyMarkdown"
        >
          <ClipboardDocumentIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Download"
          @click="downloadMarkdown"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Toolbar -->
    <div v-if="viewMode !== 'preview'" class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
      <button
        v-for="tool in tools"
        :key="tool.action"
        type="button"
        class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
        :title="tool.label"
        @click="insertMarkdown(tool.action)"
      >
        <component :is="tool.icon" class="w-4 h-4" />
      </button>
      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />
      <select
        class="px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded"
        @change="insertHeading($event.target.value)"
      >
        <option value="">Heading</option>
        <option value="1">H1</option>
        <option value="2">H2</option>
        <option value="3">H3</option>
        <option value="4">H4</option>
      </select>
    </div>

    <!-- Content -->
    <div class="flex" :style="{ height }">
      <!-- Editor -->
      <div
        v-if="viewMode === 'edit' || viewMode === 'split'"
        :class="['flex-1 relative', viewMode === 'split' ? 'border-r border-gray-200 dark:border-gray-700' : '']"
      >
        <div class="absolute inset-0 flex">
          <!-- Line Numbers -->
          <div class="w-12 flex-shrink-0 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-mono text-right py-4 pr-2 select-none overflow-hidden border-r border-gray-200 dark:border-gray-700">
            <div v-for="n in lineCount" :key="n" class="leading-6">{{ n }}</div>
          </div>
          <!-- Text Area -->
          <textarea
            ref="editorRef"
            v-model="content"
            :class="[
              'flex-1 p-4 font-mono text-sm leading-6 outline-none resize-none',
              theme === 'dark' ? 'bg-gray-900 text-gray-100' : 'bg-white text-gray-900'
            ]"
            placeholder="Write your markdown here..."
            spellcheck="false"
            @input="onInput"
            @keydown="handleKeydown"
            @scroll="syncScroll"
          />
        </div>
      </div>

      <!-- Preview -->
      <div
        v-if="viewMode === 'preview' || viewMode === 'split'"
        ref="previewRef"
        :class="[
          'flex-1 p-6 overflow-auto prose prose-sm max-w-none',
          theme === 'dark' ? 'bg-gray-900 prose-invert' : 'bg-white'
        ]"
        @scroll="syncScroll"
        v-html="renderedHtml"
      />
    </div>

    <!-- Table of Contents -->
    <div
      v-if="showToc && headings.length > 0"
      class="absolute right-4 top-20 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3"
    >
      <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Contents</h4>
      <nav class="space-y-1">
        <a
          v-for="heading in headings"
          :key="heading.id"
          :href="`#${heading.id}`"
          :class="[
            'block text-sm text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors truncate',
            { 'pl-2': heading.level === 2, 'pl-4': heading.level === 3, 'pl-6': heading.level >= 4 }
          ]"
        >
          {{ heading.text }}
        </a>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, h } from 'vue'
import {
  DocumentTextIcon,
  PencilIcon,
  Squares2X2Icon,
  EyeIcon,
  ClipboardDocumentIcon,
  ArrowDownTrayIcon,
  ListBulletIcon,
  LinkIcon,
  PhotoIcon,
  CodeBracketIcon,
  TableCellsIcon,
  MinusIcon
} from '@heroicons/vue/24/outline'

// Custom icons
const BoldIcon = { render: () => h('svg', { class: 'w-4 h-4', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z' }), h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z' })]) }
const ItalicIcon = { render: () => h('svg', { class: 'w-4 h-4', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10 4h4m4 16h-4M15 4L9 20' })]) }
const QuoteIcon = { render: () => h('svg', { class: 'w-4 h-4', fill: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { d: 'M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z' })]) }

const props = defineProps({
  modelValue: { type: String, default: '' },
  title: { type: String, default: 'Markdown Editor' },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showToc: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'change'])

const editorRef = ref(null)
const previewRef = ref(null)
const content = ref(props.modelValue)
const viewMode = ref('split')

const tools = [
  { action: 'bold', label: 'Bold', icon: BoldIcon },
  { action: 'italic', label: 'Italic', icon: ItalicIcon },
  { action: 'quote', label: 'Quote', icon: QuoteIcon },
  { action: 'code', label: 'Code', icon: CodeBracketIcon },
  { action: 'link', label: 'Link', icon: LinkIcon },
  { action: 'image', label: 'Image', icon: PhotoIcon },
  { action: 'list', label: 'List', icon: ListBulletIcon },
  { action: 'table', label: 'Table', icon: TableCellsIcon },
  { action: 'hr', label: 'Horizontal Rule', icon: MinusIcon }
]

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const lineCount = computed(() => content.value.split('\n').length)
const wordCount = computed(() => content.value.trim() ? content.value.trim().split(/\s+/).length : 0)
const charCount = computed(() => content.value.length)

const headings = computed(() => {
  const matches = content.value.matchAll(/^(#{1,6})\s+(.+)$/gm)
  return Array.from(matches).map((match, i) => ({
    id: `heading-${i}`,
    level: match[1].length,
    text: match[2]
  }))
})

const renderedHtml = computed(() => {
  let html = content.value

  // Escape HTML
  html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

  // Code blocks
  html = html.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) => 
    `<pre class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg overflow-x-auto"><code class="language-${lang}">${code.trim()}</code></pre>`
  )

  // Inline code
  html = html.replace(/`([^`]+)`/g, '<code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded text-sm">$1</code>')

  // Headers
  html = html.replace(/^######\s+(.+)$/gm, '<h6 class="text-base font-semibold mt-4 mb-2">$1</h6>')
  html = html.replace(/^#####\s+(.+)$/gm, '<h5 class="text-lg font-semibold mt-4 mb-2">$1</h5>')
  html = html.replace(/^####\s+(.+)$/gm, '<h4 class="text-xl font-semibold mt-4 mb-2">$1</h4>')
  html = html.replace(/^###\s+(.+)$/gm, '<h3 class="text-2xl font-bold mt-6 mb-3">$1</h3>')
  html = html.replace(/^##\s+(.+)$/gm, '<h2 class="text-3xl font-bold mt-8 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">$1</h2>')
  html = html.replace(/^#\s+(.+)$/gm, '<h1 class="text-4xl font-bold mt-8 mb-4">$1</h1>')

  // Bold & Italic
  html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>')
  html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
  html = html.replace(/\*(.+?)\*/g, '<em>$1</em>')
  html = html.replace(/___(.+?)___/g, '<strong><em>$1</em></strong>')
  html = html.replace(/__(.+?)__/g, '<strong>$1</strong>')
  html = html.replace(/_(.+?)_/g, '<em>$1</em>')

  // Strikethrough
  html = html.replace(/~~(.+?)~~/g, '<del>$1</del>')

  // Links & Images
  html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="max-w-full rounded-lg my-4" />')
  html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-500 hover:underline">$1</a>')

  // Blockquotes
  html = html.replace(/^>\s+(.+)$/gm, '<blockquote class="border-l-4 border-gray-300 dark:border-gray-600 pl-4 italic text-gray-600 dark:text-gray-400 my-4">$1</blockquote>')

  // Unordered lists
  html = html.replace(/^[-*]\s+(.+)$/gm, '<li class="ml-4">$1</li>')
  html = html.replace(/(<li.*<\/li>\n?)+/g, '<ul class="list-disc list-inside my-4">$&</ul>')

  // Ordered lists
  html = html.replace(/^\d+\.\s+(.+)$/gm, '<li class="ml-4">$1</li>')

  // Horizontal rule
  html = html.replace(/^---$/gm, '<hr class="my-8 border-gray-200 dark:border-gray-700" />')

  // Tables
  html = html.replace(/\|(.+)\|\n\|[-|]+\|\n((?:\|.+\|\n?)+)/g, (_, header, body) => {
    const ths = header.split('|').filter(c => c.trim()).map(c => `<th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-50 dark:bg-gray-800">${c.trim()}</th>`).join('')
    const rows = body.trim().split('\n').map(row => {
      const tds = row.split('|').filter(c => c.trim()).map(c => `<td class="border border-gray-300 dark:border-gray-600 px-4 py-2">${c.trim()}</td>`).join('')
      return `<tr>${tds}</tr>`
    }).join('')
    return `<table class="w-full border-collapse my-4"><thead><tr>${ths}</tr></thead><tbody>${rows}</tbody></table>`
  })

  // Paragraphs
  html = html.replace(/^(?!<[a-z]|$)(.+)$/gm, '<p class="my-3">$1</p>')

  // Line breaks
  html = html.replace(/\n\n/g, '<br/>')

  return html
})

const onInput = () => {
  emit('update:modelValue', content.value)
  emit('change', content.value)
}

const insertMarkdown = (action) => {
  const editor = editorRef.value
  if (!editor) return

  const start = editor.selectionStart
  const end = editor.selectionEnd
  const selected = content.value.substring(start, end)

  let insertion = ''
  let cursorOffset = 0

  switch (action) {
    case 'bold':
      insertion = `**${selected || 'bold text'}**`
      cursorOffset = selected ? insertion.length : 2
      break
    case 'italic':
      insertion = `*${selected || 'italic text'}*`
      cursorOffset = selected ? insertion.length : 1
      break
    case 'code':
      insertion = selected.includes('\n') 
        ? `\`\`\`\n${selected || 'code'}\n\`\`\``
        : `\`${selected || 'code'}\``
      cursorOffset = selected ? insertion.length : 1
      break
    case 'link':
      insertion = `[${selected || 'link text'}](url)`
      cursorOffset = selected ? insertion.length - 1 : 1
      break
    case 'image':
      insertion = `![${selected || 'alt text'}](image-url)`
      cursorOffset = selected ? insertion.length - 1 : 2
      break
    case 'quote':
      insertion = `> ${selected || 'quote'}`
      cursorOffset = insertion.length
      break
    case 'list':
      insertion = `- ${selected || 'list item'}`
      cursorOffset = insertion.length
      break
    case 'table':
      insertion = '| Header 1 | Header 2 |\n|----------|----------|\n| Cell 1   | Cell 2   |'
      cursorOffset = insertion.length
      break
    case 'hr':
      insertion = '\n---\n'
      cursorOffset = insertion.length
      break
  }

  content.value = content.value.substring(0, start) + insertion + content.value.substring(end)
  
  setTimeout(() => {
    editor.focus()
    editor.setSelectionRange(start + cursorOffset, start + cursorOffset)
  }, 0)
  
  onInput()
}

const insertHeading = (level) => {
  if (!level) return
  const prefix = '#'.repeat(parseInt(level)) + ' '
  const editor = editorRef.value
  if (!editor) return

  const start = editor.selectionStart
  const lineStart = content.value.lastIndexOf('\n', start - 1) + 1
  
  content.value = content.value.substring(0, lineStart) + prefix + content.value.substring(lineStart)
  onInput()
}

const handleKeydown = (e) => {
  if (e.key === 'Tab') {
    e.preventDefault()
    const start = e.target.selectionStart
    const end = e.target.selectionEnd
    content.value = content.value.substring(0, start) + '  ' + content.value.substring(end)
    setTimeout(() => {
      e.target.selectionStart = e.target.selectionEnd = start + 2
    }, 0)
    onInput()
  }
}

const syncScroll = (e) => {
  // Sync scroll between editor and preview
}

const copyMarkdown = () => {
  navigator.clipboard.writeText(content.value)
}

const downloadMarkdown = () => {
  const blob = new Blob([content.value], { type: 'text/markdown' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'document.md'
  a.click()
  URL.revokeObjectURL(url)
}

watch(() => props.modelValue, (newVal) => {
  if (newVal !== content.value) {
    content.value = newVal
  }
})
</script>

<style>
.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
  color: inherit;
}
.prose a {
  color: #3b82f6;
}
.prose code {
  color: inherit;
}
</style>
