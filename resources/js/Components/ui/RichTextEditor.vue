<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-1.5" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <div class="relative">
      <!-- Display area -->
      <div
        :class="displayClasses"
        @click="openEditor"
      >
        <!-- Rendered content -->
        <div
          v-if="modelValue && !isEditing"
          class="prose prose-sm max-w-none"
          v-html="renderedContent"
        />
        
        <!-- Placeholder -->
        <p v-else-if="!isEditing" class="text-gray-400">
          {{ placeholder }}
        </p>
      </div>

      <!-- Editor -->
      <div v-if="isEditing" class="space-y-2">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-1 rounded-t-lg border border-b-0 border-gray-300 bg-gray-50 px-2 py-1.5">
          <!-- Bold -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            :class="{ 'bg-gray-200': isActive('bold') }"
            title="Bold (Ctrl+B)"
            @click="toggleFormat('bold')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 0 1 0 8H6z M6 12h9a4 4 0 0 1 0 8H6z"/>
            </svg>
          </button>

          <!-- Italic -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            :class="{ 'bg-gray-200': isActive('italic') }"
            title="Italic (Ctrl+I)"
            @click="toggleFormat('italic')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-2 0v16m-4 0h8"/>
            </svg>
          </button>

          <!-- Underline -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            :class="{ 'bg-gray-200': isActive('underline') }"
            title="Underline (Ctrl+U)"
            @click="toggleFormat('underline')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3v7a6 6 0 0 0 12 0V3M4 21h16"/>
            </svg>
          </button>

          <div class="w-px h-5 bg-gray-300 mx-1" />

          <!-- Heading -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors text-sm font-bold"
            title="Heading"
            @click="toggleFormat('heading')"
          >
            H
          </button>

          <!-- Quote -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            title="Quote"
            @click="toggleFormat('quote')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
          </button>

          <div class="w-px h-5 bg-gray-300 mx-1" />

          <!-- Bullet list -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            title="Bullet list"
            @click="toggleFormat('bullet')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>

          <!-- Numbered list -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            title="Numbered list"
            @click="toggleFormat('numbered')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M4 6v.01M4 12v.01M4 18v.01"/>
            </svg>
          </button>

          <div class="w-px h-5 bg-gray-300 mx-1" />

          <!-- Link -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            title="Insert link"
            @click="insertLink"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
          </button>

          <!-- Code -->
          <button
            type="button"
            class="rounded p-1.5 hover:bg-gray-200 transition-colors"
            title="Inline code"
            @click="toggleFormat('code')"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
          </button>

          <div class="flex-1" />

          <!-- Character count -->
          <span v-if="maxLength" class="text-xs text-gray-500">
            {{ charCount }}/{{ maxLength }}
          </span>
        </div>

        <!-- Textarea -->
        <textarea
          ref="textareaRef"
          :value="modelValue"
          :placeholder="placeholder"
          :disabled="disabled"
          :maxlength="maxLength"
          :rows="rows"
          class="block w-full rounded-b-lg border-gray-300 text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 font-mono text-sm"
          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': error }"
          @input="handleInput"
          @keydown="handleKeydown"
          @blur="handleBlur"
        />

        <!-- Actions -->
        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
            @click="cancelEdit"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700"
            @click="saveEdit"
          >
            Save
          </button>
        </div>
      </div>
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
import { ref, computed, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Write something...'
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  rows: {
    type: Number,
    default: 6
  },
  maxLength: {
    type: Number,
    default: null
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

const emit = defineEmits(['update:modelValue', 'change', 'save', 'cancel'])

// State
const textareaRef = ref(null)
const isEditing = ref(false)
const originalValue = ref('')
const activeFormats = ref([])

// Computed
const charCount = computed(() => props.modelValue?.length || 0)

// Simple markdown to HTML rendering
const renderedContent = computed(() => {
  if (!props.modelValue) return ''
  
  let html = props.modelValue
    // Escape HTML
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    // Bold
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    // Italic
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    // Underline
    .replace(/__(.*?)__/g, '<u>$1</u>')
    // Code
    .replace(/`(.*?)`/g, '<code class="bg-gray-100 px-1 rounded text-sm">$1</code>')
    // Links
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary-600 hover:underline" target="_blank">$1</a>')
    // Headings
    .replace(/^### (.*$)/gim, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>')
    .replace(/^## (.*$)/gim, '<h2 class="text-xl font-semibold mt-4 mb-2">$1</h2>')
    .replace(/^# (.*$)/gim, '<h1 class="text-2xl font-bold mt-4 mb-2">$1</h1>')
    // Blockquotes
    .replace(/^> (.*$)/gim, '<blockquote class="border-l-4 border-gray-300 pl-4 italic text-gray-600">$1</blockquote>')
    // Lists
    .replace(/^\* (.*$)/gim, '<li class="ml-4 list-disc">$1</li>')
    .replace(/^\d+\. (.*$)/gim, '<li class="ml-4 list-decimal">$1</li>')
    // Line breaks
    .replace(/\n/g, '<br>')
  
  return html
})

// Open editor
function openEditor() {
  if (props.disabled) return
  isEditing.value = true
  originalValue.value = props.modelValue
  nextTick(() => {
    textareaRef.value?.focus()
  })
}

// Handle input
function handleInput(event) {
  emit('update:modelValue', event.target.value)
}

// Handle keyboard shortcuts
function handleKeydown(event) {
  if (event.ctrlKey || event.metaKey) {
    switch (event.key.toLowerCase()) {
      case 'b':
        event.preventDefault()
        toggleFormat('bold')
        break
      case 'i':
        event.preventDefault()
        toggleFormat('italic')
        break
      case 'u':
        event.preventDefault()
        toggleFormat('underline')
        break
      case 's':
        event.preventDefault()
        saveEdit()
        break
    }
  }
  
  if (event.key === 'Escape') {
    cancelEdit()
  }
}

// Handle blur
function handleBlur() {
  // Optional: auto-save on blur
}

// Toggle format
function toggleFormat(format) {
  const textarea = textareaRef.value
  if (!textarea) return
  
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end)
  
  let prefix = ''
  let suffix = ''
  let replacement = selected
  
  switch (format) {
    case 'bold':
      prefix = '**'
      suffix = '**'
      break
    case 'italic':
      prefix = '*'
      suffix = '*'
      break
    case 'underline':
      prefix = '__'
      suffix = '__'
      break
    case 'code':
      prefix = '`'
      suffix = '`'
      break
    case 'heading':
      prefix = '## '
      suffix = ''
      break
    case 'quote':
      prefix = '> '
      suffix = ''
      break
    case 'bullet':
      prefix = '* '
      suffix = ''
      break
    case 'numbered':
      prefix = '1. '
      suffix = ''
      break
  }
  
  replacement = prefix + (selected || 'text') + suffix
  
  const newValue = text.substring(0, start) + replacement + text.substring(end)
  emit('update:modelValue', newValue)
  
  nextTick(() => {
    textarea.focus()
    const newCursorPos = start + prefix.length + (selected || 'text').length
    textarea.setSelectionRange(newCursorPos, newCursorPos)
  })
}

// Insert link
function insertLink() {
  const url = prompt('Enter URL:')
  if (!url) return
  
  const textarea = textareaRef.value
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end) || 'link text'
  
  const linkMarkdown = `[${selected}](${url})`
  const newValue = text.substring(0, start) + linkMarkdown + text.substring(end)
  emit('update:modelValue', newValue)
}

// Check if format is active (simplified)
function isActive(format) {
  return activeFormats.value.includes(format)
}

// Save edit
function saveEdit() {
  isEditing.value = false
  emit('change', props.modelValue)
  emit('save', props.modelValue)
}

// Cancel edit
function cancelEdit() {
  emit('update:modelValue', originalValue.value)
  isEditing.value = false
  emit('cancel')
}

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

const displayClasses = computed(() => {
  const base = 'min-h-[100px] rounded-lg border border-gray-300 bg-white p-3 cursor-text'
  if (props.disabled) {
    return `${base} bg-gray-50 cursor-not-allowed`
  }
  return `${base} hover:border-gray-400`
})
</script>
