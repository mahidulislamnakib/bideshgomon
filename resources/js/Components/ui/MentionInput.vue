<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-2" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Mention input -->
    <div class="relative">
      <div
        ref="editorRef"
        contenteditable="true"
        role="textbox"
        :aria-placeholder="placeholder"
        class="min-h-[100px] w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-2 transition-colors"
        :class="inputClasses"
        @input="handleInput"
        @keydown="handleKeydown"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Placeholder -->
      <div
        v-if="isEmpty && !isFocused"
        class="absolute top-3 left-4 text-gray-400 pointer-events-none"
      >
        {{ placeholder }}
      </div>

      <!-- Mention suggestions dropdown -->
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="showSuggestions && filteredSuggestions.length > 0"
          ref="dropdownRef"
          class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5"
          :style="dropdownStyle"
        >
          <button
            v-for="(suggestion, index) in filteredSuggestions"
            :key="suggestion.id || index"
            type="button"
            class="flex w-full items-center gap-3 px-4 py-2 text-sm transition-colors"
            :class="index === selectedIndex ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50'"
            @click="selectSuggestion(suggestion)"
            @mouseenter="selectedIndex = index"
          >
            <!-- Avatar -->
            <img
              v-if="suggestion.avatar"
              :src="suggestion.avatar"
              :alt="suggestion.name"
              class="h-8 w-8 rounded-full object-cover"
            />
            <div
              v-else
              class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-primary-600 font-medium text-sm"
            >
              {{ getInitials(suggestion.name) }}
            </div>

            <!-- Info -->
            <div class="flex-1 text-left">
              <p class="font-medium">{{ suggestion.name }}</p>
              <p v-if="suggestion.username" class="text-xs text-gray-500">
                @{{ suggestion.username }}
              </p>
            </div>
          </button>
        </div>
      </Transition>
    </div>

    <!-- Character count -->
    <div v-if="maxLength" class="mt-1.5 flex justify-end">
      <span
        class="text-xs"
        :class="charCount > maxLength ? 'text-red-600' : 'text-gray-400'"
      >
        {{ charCount }}/{{ maxLength }}
      </span>
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
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  suggestions: {
    type: Array,
    required: true
    // [{ id, name, username?, avatar? }]
  },
  trigger: {
    type: String,
    default: '@'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Type @ to mention someone...'
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
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

const emit = defineEmits(['update:modelValue', 'mention', 'submit'])

// Refs
const editorRef = ref(null)
const dropdownRef = ref(null)
const isFocused = ref(false)
const isEmpty = ref(true)
const showSuggestions = ref(false)
const searchQuery = ref('')
const selectedIndex = ref(0)
const mentionStartPosition = ref(null)
const charCount = ref(0)

// Filter suggestions based on search query
const filteredSuggestions = computed(() => {
  if (!searchQuery.value) return props.suggestions.slice(0, 10)
  
  const query = searchQuery.value.toLowerCase()
  return props.suggestions
    .filter(s => 
      s.name.toLowerCase().includes(query) ||
      (s.username && s.username.toLowerCase().includes(query))
    )
    .slice(0, 10)
})

// Dropdown position style
const dropdownStyle = computed(() => {
  return {}
})

// Get initials from name
function getInitials(name) {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Handle input
function handleInput(event) {
  const content = event.target.innerText
  isEmpty.value = content.trim().length === 0
  charCount.value = content.length
  
  // Check for mention trigger
  const selection = window.getSelection()
  if (selection.rangeCount > 0) {
    const range = selection.getRangeAt(0)
    const textBeforeCursor = getTextBeforeCursor(range)
    
    // Find trigger character
    const triggerIndex = textBeforeCursor.lastIndexOf(props.trigger)
    if (triggerIndex !== -1) {
      const textAfterTrigger = textBeforeCursor.slice(triggerIndex + 1)
      
      // Check if it's a valid mention context (no space before)
      if (triggerIndex === 0 || textBeforeCursor[triggerIndex - 1] === ' ') {
        if (!textAfterTrigger.includes(' ')) {
          searchQuery.value = textAfterTrigger
          mentionStartPosition.value = triggerIndex
          showSuggestions.value = true
          selectedIndex.value = 0
          return
        }
      }
    }
  }
  
  showSuggestions.value = false
  emit('update:modelValue', content)
}

// Get text before cursor
function getTextBeforeCursor(range) {
  const preRange = document.createRange()
  preRange.selectNodeContents(editorRef.value)
  preRange.setEnd(range.startContainer, range.startOffset)
  return preRange.toString()
}

// Handle keydown
function handleKeydown(event) {
  if (showSuggestions.value) {
    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault()
        selectedIndex.value = Math.min(selectedIndex.value + 1, filteredSuggestions.value.length - 1)
        break
        
      case 'ArrowUp':
        event.preventDefault()
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
        break
        
      case 'Enter':
      case 'Tab':
        event.preventDefault()
        if (filteredSuggestions.value[selectedIndex.value]) {
          selectSuggestion(filteredSuggestions.value[selectedIndex.value])
        }
        break
        
      case 'Escape':
        showSuggestions.value = false
        break
    }
  } else if (event.key === 'Enter' && event.ctrlKey) {
    event.preventDefault()
    emit('submit', editorRef.value?.innerText || '')
  }
}

// Select a suggestion
function selectSuggestion(suggestion) {
  if (!editorRef.value) return
  
  const content = editorRef.value.innerText
  const beforeMention = content.slice(0, mentionStartPosition.value)
  const afterSearch = content.slice(mentionStartPosition.value + 1 + searchQuery.value.length)
  
  // Create mention element
  const mentionText = `@${suggestion.username || suggestion.name}`
  
  // Update content
  editorRef.value.innerText = beforeMention + mentionText + ' ' + afterSearch
  
  // Place cursor after mention
  placeCaretAtEnd()
  
  showSuggestions.value = false
  emit('mention', suggestion)
  emit('update:modelValue', editorRef.value.innerText)
  
  isEmpty.value = editorRef.value.innerText.trim().length === 0
  charCount.value = editorRef.value.innerText.length
}

// Place caret at end
function placeCaretAtEnd() {
  if (!editorRef.value) return
  
  const range = document.createRange()
  const selection = window.getSelection()
  
  range.selectNodeContents(editorRef.value)
  range.collapse(false)
  
  selection.removeAllRanges()
  selection.addRange(range)
  
  editorRef.value.focus()
}

// Handle focus
function handleFocus() {
  isFocused.value = true
}

// Handle blur
function handleBlur() {
  isFocused.value = false
  // Delay to allow click on suggestion
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

// Initialize with modelValue
onMounted(() => {
  if (props.modelValue && editorRef.value) {
    editorRef.value.innerText = props.modelValue
    isEmpty.value = props.modelValue.trim().length === 0
    charCount.value = props.modelValue.length
  }
})

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (editorRef.value && editorRef.value.innerText !== newVal) {
    editorRef.value.innerText = newVal
    isEmpty.value = newVal.trim().length === 0
    charCount.value = newVal.length
  }
})

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60 pointer-events-none' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

const inputClasses = computed(() => {
  const base = props.error
    ? 'border-red-300 focus:ring-red-500'
    : 'border-gray-300 focus:ring-primary-500 focus:border-primary-500'
  
  return `${base} bg-white`
})

// Expose methods
defineExpose({
  focus: () => editorRef.value?.focus(),
  clear: () => {
    if (editorRef.value) {
      editorRef.value.innerText = ''
      isEmpty.value = true
      charCount.value = 0
      emit('update:modelValue', '')
    }
  },
  insertText: (text) => {
    if (editorRef.value) {
      editorRef.value.innerText += text
      charCount.value = editorRef.value.innerText.length
      emit('update:modelValue', editorRef.value.innerText)
    }
  }
})
</script>
