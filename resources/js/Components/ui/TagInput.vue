<template>
  <div class="w-full">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Input Container -->
    <div
      class="flex flex-wrap items-center gap-2 p-2 border rounded-lg transition-all min-h-[42px]"
      :class="containerClasses"
      @click="focusInput"
    >
      <!-- Tags -->
      <TransitionGroup name="tag">
        <span
          v-for="(tag, index) in modelValue"
          :key="tag"
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-medium transition-all"
          :class="getTagClasses(index)"
        >
          <!-- Tag Icon (optional) -->
          <component v-if="tagIcon" :is="tagIcon" class="w-3.5 h-3.5" />
          
          <!-- Tag Text -->
          <span class="max-w-[150px] truncate">{{ tag }}</span>
          
          <!-- Remove Button -->
          <button
            v-if="!disabled && !readonly"
            type="button"
            class="ml-0.5 hover:bg-black/10 dark:hover:bg-white/10 rounded-full p-0.5 transition-colors"
            @click.stop="removeTag(index)"
            :aria-label="`Remove ${tag}`"
          >
            <XMarkIcon class="w-3.5 h-3.5" />
          </button>
        </span>
      </TransitionGroup>

      <!-- Input Field -->
      <input
        v-if="!disabled && !readonly && (maxTags === null || modelValue.length < maxTags)"
        ref="inputRef"
        type="text"
        v-model="inputValue"
        :placeholder="modelValue.length === 0 ? placeholder : ''"
        class="flex-1 min-w-[120px] border-0 p-1 text-sm bg-transparent focus:ring-0 focus:outline-none text-gray-900 dark:text-white placeholder-gray-400"
        @keydown="handleKeydown"
        @blur="handleBlur"
        @paste="handlePaste"
      />

      <!-- Max Tags Indicator -->
      <span 
        v-if="maxTags && modelValue.length >= maxTags" 
        class="text-xs text-gray-400"
      >
        Max {{ maxTags }} tags
      </span>
    </div>

    <!-- Helper Text / Suggestions -->
    <div class="mt-1 flex items-center justify-between">
      <p v-if="helperText" class="text-xs text-gray-500 dark:text-gray-400">
        {{ helperText }}
      </p>
      <p v-if="showCount" class="text-xs text-gray-400">
        {{ modelValue.length }}{{ maxTags ? `/${maxTags}` : '' }} tags
      </p>
    </div>

    <!-- Suggestions Dropdown -->
    <div
      v-if="filteredSuggestions.length > 0 && inputValue && showSuggestions"
      class="mt-1 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg bg-white dark:bg-gray-800 max-h-40 overflow-y-auto"
    >
      <button
        v-for="(suggestion, index) in filteredSuggestions"
        :key="suggestion"
        type="button"
        class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-blue-50 dark:bg-blue-900/20': index === suggestionIndex }"
        @click="addSuggestion(suggestion)"
      >
        {{ suggestion }}
      </button>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  // Array of tags
  modelValue: {
    type: Array,
    default: () => []
  },
  // Label text
  label: {
    type: String,
    default: ''
  },
  // Placeholder text
  placeholder: {
    type: String,
    default: 'Add tags...'
  },
  // Helper text
  helperText: {
    type: String,
    default: ''
  },
  // Maximum number of tags
  maxTags: {
    type: Number,
    default: null
  },
  // Maximum tag length
  maxLength: {
    type: Number,
    default: 50
  },
  // Suggestions array
  suggestions: {
    type: Array,
    default: () => []
  },
  // Tag color variant
  color: {
    type: String,
    default: 'blue',
    validator: (v) => ['blue', 'green', 'red', 'yellow', 'purple', 'pink', 'gray', 'random'].includes(v)
  },
  // Allow duplicates
  allowDuplicates: {
    type: Boolean,
    default: false
  },
  // Case sensitive
  caseSensitive: {
    type: Boolean,
    default: false
  },
  // Custom validation function
  validate: {
    type: Function,
    default: null
  },
  // Required field
  required: {
    type: Boolean,
    default: false
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Read-only mode
  readonly: {
    type: Boolean,
    default: false
  },
  // Show tag count
  showCount: {
    type: Boolean,
    default: false
  },
  // Error message
  error: {
    type: String,
    default: ''
  },
  // Delimiter characters (for splitting pasted content)
  delimiters: {
    type: Array,
    default: () => [',', ';', '\n']
  },
  // Tag icon
  tagIcon: {
    type: [Object, Function],
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'tag-added', 'tag-removed', 'invalid-tag'])

// Refs
const inputRef = ref(null)
const inputValue = ref('')
const suggestionIndex = ref(-1)
const showSuggestions = ref(true)

// Color classes for tags
const colorClasses = {
  blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
  green: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
  red: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
  purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
  pink: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300',
  gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const allColors = Object.keys(colorClasses).filter(c => c !== 'random')

// Container classes
const containerClasses = computed(() => {
  if (props.disabled) {
    return 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 cursor-not-allowed'
  }
  if (props.error) {
    return 'border-red-500 focus-within:ring-2 focus-within:ring-red-200 dark:focus-within:ring-red-800'
  }
  return 'border-gray-300 dark:border-gray-600 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 cursor-text'
})

// Filtered suggestions
const filteredSuggestions = computed(() => {
  if (!inputValue.value || props.suggestions.length === 0) return []
  
  const search = props.caseSensitive ? inputValue.value : inputValue.value.toLowerCase()
  
  return props.suggestions.filter(s => {
    const suggestion = props.caseSensitive ? s : s.toLowerCase()
    // Not already added and matches search
    const notAdded = !props.modelValue.some(tag => 
      (props.caseSensitive ? tag : tag.toLowerCase()) === suggestion
    )
    return notAdded && suggestion.includes(search)
  }).slice(0, 5)
})

// Get tag classes based on color
function getTagClasses(index) {
  if (props.color === 'random') {
    const colorIndex = index % allColors.length
    return colorClasses[allColors[colorIndex]]
  }
  return colorClasses[props.color]
}

// Focus input
function focusInput() {
  inputRef.value?.focus()
}

// Add tag
function addTag(value) {
  const tag = value.trim()
  
  if (!tag) return false
  
  // Check max length
  if (tag.length > props.maxLength) {
    emit('invalid-tag', { tag, reason: 'Too long' })
    return false
  }
  
  // Check max tags
  if (props.maxTags && props.modelValue.length >= props.maxTags) {
    emit('invalid-tag', { tag, reason: 'Maximum tags reached' })
    return false
  }
  
  // Check duplicates
  const exists = props.modelValue.some(t => 
    props.caseSensitive ? t === tag : t.toLowerCase() === tag.toLowerCase()
  )
  if (exists && !props.allowDuplicates) {
    emit('invalid-tag', { tag, reason: 'Duplicate' })
    return false
  }
  
  // Custom validation
  if (props.validate && !props.validate(tag)) {
    emit('invalid-tag', { tag, reason: 'Validation failed' })
    return false
  }
  
  // Add tag
  const newTags = [...props.modelValue, tag]
  emit('update:modelValue', newTags)
  emit('tag-added', tag)
  
  return true
}

// Remove tag
function removeTag(index) {
  const removedTag = props.modelValue[index]
  const newTags = props.modelValue.filter((_, i) => i !== index)
  emit('update:modelValue', newTags)
  emit('tag-removed', removedTag)
  nextTick(() => focusInput())
}

// Handle keydown
function handleKeydown(e) {
  // Enter or comma to add tag
  if (e.key === 'Enter' || (props.delimiters.includes(e.key) && e.key !== '\n')) {
    e.preventDefault()
    
    if (suggestionIndex.value >= 0 && filteredSuggestions.value.length > 0) {
      addSuggestion(filteredSuggestions.value[suggestionIndex.value])
    } else if (addTag(inputValue.value)) {
      inputValue.value = ''
    }
    suggestionIndex.value = -1
    return
  }
  
  // Backspace to remove last tag
  if (e.key === 'Backspace' && !inputValue.value && props.modelValue.length > 0) {
    removeTag(props.modelValue.length - 1)
    return
  }
  
  // Navigate suggestions
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    suggestionIndex.value = Math.min(
      suggestionIndex.value + 1,
      filteredSuggestions.value.length - 1
    )
    return
  }
  
  if (e.key === 'ArrowUp') {
    e.preventDefault()
    suggestionIndex.value = Math.max(suggestionIndex.value - 1, -1)
    return
  }
  
  // Escape to close suggestions
  if (e.key === 'Escape') {
    showSuggestions.value = false
    suggestionIndex.value = -1
  }
}

// Handle blur
function handleBlur() {
  // Add tag on blur if there's content
  setTimeout(() => {
    if (inputValue.value.trim()) {
      if (addTag(inputValue.value)) {
        inputValue.value = ''
      }
    }
    showSuggestions.value = true
    suggestionIndex.value = -1
  }, 150)
}

// Handle paste
function handlePaste(e) {
  e.preventDefault()
  const text = e.clipboardData.getData('text')
  
  // Split by delimiters
  const regex = new RegExp(`[${props.delimiters.map(d => d.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('')}]`)
  const tags = text.split(regex).map(t => t.trim()).filter(t => t)
  
  tags.forEach(tag => addTag(tag))
}

// Add suggestion
function addSuggestion(suggestion) {
  if (addTag(suggestion)) {
    inputValue.value = ''
    suggestionIndex.value = -1
    nextTick(() => focusInput())
  }
}
</script>

<style scoped>
.tag-enter-active,
.tag-leave-active {
  transition: all 0.2s ease;
}

.tag-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.tag-leave-to {
  opacity: 0;
  transform: scale(0.8);
}

.tag-move {
  transition: transform 0.2s ease;
}
</style>
