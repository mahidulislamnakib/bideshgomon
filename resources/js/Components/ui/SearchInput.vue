<template>
  <div class="relative" :class="containerClasses">
    <!-- Search icon -->
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <MagnifyingGlassIcon class="h-5 w-5" :class="iconClasses" />
    </div>

    <!-- Input -->
    <input
      ref="inputRef"
      type="text"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      class="block w-full rounded-lg border-0 py-2 pl-10 ring-1 ring-inset transition-all duration-200 focus:ring-2 focus:ring-inset"
      :class="[
        inputClasses,
        sizeClasses,
        { 'pr-10': clearable && modelValue, 'pr-4': !clearable || !modelValue }
      ]"
      @input="handleInput"
      @keydown.enter="handleSubmit"
      @keydown.escape="handleClear"
      @focus="isFocused = true"
      @blur="isFocused = false"
    />

    <!-- Loading spinner -->
    <div
      v-if="loading"
      class="absolute inset-y-0 right-0 flex items-center pr-3"
    >
      <svg
        class="h-5 w-5 animate-spin"
        :class="iconClasses"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>

    <!-- Clear button -->
    <button
      v-else-if="clearable && modelValue"
      type="button"
      class="absolute inset-y-0 right-0 flex items-center pr-3 transition-colors"
      :class="clearButtonClasses"
      @click="handleClear"
    >
      <XMarkIcon class="h-5 w-5" />
    </button>

    <!-- Keyboard shortcut hint -->
    <div
      v-if="shortcut && !modelValue && !isFocused"
      class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
    >
      <kbd
        class="inline-flex items-center rounded border px-1.5 py-0.5 font-mono text-xs"
        :class="shortcutClasses"
      >
        {{ shortcut }}
      </kbd>
    </div>

    <!-- Suggestions dropdown -->
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
        class="absolute z-10 mt-1 w-full rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5"
      >
        <ul class="max-h-60 overflow-auto py-1">
          <li
            v-for="(suggestion, index) in filteredSuggestions"
            :key="suggestion.id || index"
            class="cursor-pointer px-4 py-2 text-sm transition-colors"
            :class="[
              highlightedIndex === index ? 'bg-primary-50 text-primary-900' : 'text-gray-900 hover:bg-gray-50'
            ]"
            @click="selectSuggestion(suggestion)"
            @mouseenter="highlightedIndex = index"
          >
            <slot name="suggestion" :suggestion="suggestion">
              {{ typeof suggestion === 'string' ? suggestion : suggestion.label }}
            </slot>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Search...'
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'filled', 'minimal'].includes(value)
  },
  clearable: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  debounce: {
    type: Number,
    default: 300
  },
  shortcut: {
    type: String,
    default: ''
  },
  suggestions: {
    type: Array,
    default: () => []
  },
  autofocus: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'search', 'clear', 'select-suggestion'])

// Refs
const inputRef = ref(null)
const isFocused = ref(false)
const highlightedIndex = ref(-1)
const debouncedValue = ref(props.modelValue)
let debounceTimer = null

// Suggestions
const showSuggestions = computed(() => 
  isFocused.value && props.modelValue && props.suggestions.length > 0
)

const filteredSuggestions = computed(() => {
  if (!props.modelValue) return props.suggestions
  const query = props.modelValue.toLowerCase()
  return props.suggestions.filter(s => {
    const text = typeof s === 'string' ? s : s.label
    return text.toLowerCase().includes(query)
  }).slice(0, 10)
})

// Handle input with debounce
function handleInput(event) {
  const value = event.target.value
  emit('update:modelValue', value)
  
  // Debounce search emission
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    debouncedValue.value = value
    emit('search', value)
  }, props.debounce)
}

function handleSubmit() {
  if (highlightedIndex.value >= 0 && filteredSuggestions.value[highlightedIndex.value]) {
    selectSuggestion(filteredSuggestions.value[highlightedIndex.value])
  } else {
    emit('search', props.modelValue)
  }
}

function handleClear() {
  emit('update:modelValue', '')
  emit('clear')
  inputRef.value?.focus()
}

function selectSuggestion(suggestion) {
  const value = typeof suggestion === 'string' ? suggestion : suggestion.label
  emit('update:modelValue', value)
  emit('select-suggestion', suggestion)
  isFocused.value = false
}

// Keyboard shortcut handler
function handleKeyboardShortcut(event) {
  if (!props.shortcut) return
  
  const key = props.shortcut.toLowerCase()
  if (key === '/' && event.key === '/') {
    event.preventDefault()
    inputRef.value?.focus()
  } else if (key.includes('k') && (event.metaKey || event.ctrlKey) && event.key === 'k') {
    event.preventDefault()
    inputRef.value?.focus()
  }
}

// Lifecycle
onMounted(() => {
  if (props.autofocus) {
    inputRef.value?.focus()
  }
  if (props.shortcut) {
    document.addEventListener('keydown', handleKeyboardShortcut)
  }
})

onUnmounted(() => {
  if (debounceTimer) clearTimeout(debounceTimer)
  if (props.shortcut) {
    document.removeEventListener('keydown', handleKeyboardShortcut)
  }
})

// Watch for arrow key navigation in suggestions
watch(isFocused, (focused) => {
  if (!focused) {
    highlightedIndex.value = -1
  }
})

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-50 cursor-not-allowed' : ''
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'text-sm py-1.5',
    md: 'text-sm py-2',
    lg: 'text-base py-2.5'
  }
  return sizes[props.size]
})

const inputClasses = computed(() => {
  const variants = {
    default: 'bg-white text-gray-900 ring-gray-300 placeholder:text-gray-400 focus:ring-primary-600',
    filled: 'bg-gray-100 text-gray-900 ring-transparent placeholder:text-gray-500 focus:bg-white focus:ring-primary-600',
    minimal: 'bg-transparent text-gray-900 ring-transparent placeholder:text-gray-400 focus:ring-primary-600'
  }
  return variants[props.variant]
})

const iconClasses = computed(() => {
  return isFocused.value ? 'text-primary-500' : 'text-gray-400'
})

const clearButtonClasses = 'text-gray-400 hover:text-gray-600'

const shortcutClasses = 'bg-gray-100 border-gray-300 text-gray-500'

// Expose focus method
defineExpose({
  focus: () => inputRef.value?.focus(),
  blur: () => inputRef.value?.blur()
})
</script>
