<template>
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
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh]"
        @click.self="close"
        @keydown.escape="close"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" />
        
        <!-- Spotlight container -->
        <div
          ref="spotlightRef"
          :class="containerClasses"
          :style="{ width: width + 'px', maxWidth: '90vw' }"
        >
          <!-- Search input -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              ref="inputRef"
              v-model="query"
              type="text"
              :placeholder="placeholder"
              :class="inputClasses"
              @input="onSearch"
              @keydown="handleKeydown"
            />
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center gap-2">
              <kbd 
                v-if="showShortcut && !query"
                class="hidden sm:inline-flex px-2 py-0.5 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded"
              >
                ESC
              </kbd>
              <button
                v-if="query"
                @click="clearQuery"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Loading indicator -->
          <div v-if="loading" class="px-4 py-8 text-center">
            <div class="inline-block w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
            <p class="mt-2 text-sm text-gray-500">Searching...</p>
          </div>
          
          <!-- Results -->
          <div
            v-else-if="hasResults"
            ref="resultsRef"
            class="max-h-80 overflow-y-auto border-t border-gray-200 dark:border-gray-700"
          >
            <!-- Grouped results -->
            <template v-if="groupedResults">
              <div
                v-for="(items, group) in groupedResults"
                :key="group"
                class="border-b border-gray-200 dark:border-gray-700 last:border-0"
              >
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-800/50">
                  {{ group }}
                </div>
                <div
                  v-for="(item, index) in items"
                  :key="item.id || index"
                  :class="resultItemClasses(getGlobalIndex(group, index))"
                  @click="selectItem(item)"
                  @mouseenter="activeIndex = getGlobalIndex(group, index)"
                >
                  <div v-if="item.icon" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <component :is="item.icon" class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
                      {{ item.title }}
                    </div>
                    <div v-if="item.description" class="text-sm text-gray-500 truncate">
                      {{ item.description }}
                    </div>
                  </div>
                  <div v-if="item.shortcut" class="flex-shrink-0">
                    <kbd class="px-2 py-0.5 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                      {{ item.shortcut }}
                    </kbd>
                  </div>
                </div>
              </div>
            </template>
            
            <!-- Flat results -->
            <template v-else>
              <div
                v-for="(item, index) in filteredResults"
                :key="item.id || index"
                :class="resultItemClasses(index)"
                @click="selectItem(item)"
                @mouseenter="activeIndex = index"
              >
                <div v-if="item.icon" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                  <component :is="item.icon" class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ item.title }}
                  </div>
                  <div v-if="item.description" class="text-sm text-gray-500 truncate">
                    {{ item.description }}
                  </div>
                </div>
                <div v-if="item.shortcut" class="flex-shrink-0">
                  <kbd class="px-2 py-0.5 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                    {{ item.shortcut }}
                  </kbd>
                </div>
              </div>
            </template>
          </div>
          
          <!-- Empty state -->
          <div
            v-else-if="query && !loading"
            class="px-4 py-8 text-center border-t border-gray-200 dark:border-gray-700"
          >
            <svg class="mx-auto w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500">{{ emptyText }}</p>
          </div>
          
          <!-- Recent searches -->
          <div
            v-else-if="recentSearches.length > 0 && !query"
            class="border-t border-gray-200 dark:border-gray-700"
          >
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
              <span>Recent</span>
              <button @click="clearRecent" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                Clear
              </button>
            </div>
            <div
              v-for="(item, index) in recentSearches"
              :key="item"
              :class="resultItemClasses(index)"
              @click="useRecent(item)"
              @mouseenter="activeIndex = index"
            >
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="flex-1 text-gray-700 dark:text-gray-300">{{ item }}</span>
            </div>
          </div>
          
          <!-- Footer -->
          <div
            v-if="showFooter"
            class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between text-xs text-gray-500"
          >
            <div class="flex items-center gap-4">
              <span class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">↑</kbd>
                <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">↓</kbd>
                Navigate
              </span>
              <span class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">↵</kbd>
                Select
              </span>
            </div>
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // State
  modelValue: {
    type: Boolean,
    default: false
  },
  
  // Data
  results: {
    type: Array,
    default: () => []
  },
  groupBy: {
    type: String,
    default: ''
  },
  
  // Search
  placeholder: {
    type: String,
    default: 'Search...'
  },
  emptyText: {
    type: String,
    default: 'No results found'
  },
  
  // Behavior
  loading: {
    type: Boolean,
    default: false
  },
  debounce: {
    type: Number,
    default: 300
  },
  
  // Display
  width: {
    type: Number,
    default: 640
  },
  showShortcut: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: true
  },
  
  // Keyboard shortcut
  shortcutKey: {
    type: String,
    default: 'k'
  },
  shortcutModifier: {
    type: String,
    default: 'meta',
    validator: (v) => ['meta', 'ctrl', 'alt', 'shift'].includes(v)
  }
})

const emit = defineEmits(['update:modelValue', 'search', 'select', 'clear'])

const spotlightRef = ref(null)
const inputRef = ref(null)
const resultsRef = ref(null)
const query = ref('')
const activeIndex = ref(0)
const recentSearches = ref([])

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

// Filter results based on query
const filteredResults = computed(() => {
  if (!query.value) return props.results
  
  const q = query.value.toLowerCase()
  return props.results.filter(item => 
    item.title?.toLowerCase().includes(q) ||
    item.description?.toLowerCase().includes(q) ||
    item.keywords?.some(k => k.toLowerCase().includes(q))
  )
})

// Group results if groupBy is set
const groupedResults = computed(() => {
  if (!props.groupBy) return null
  
  const groups = {}
  filteredResults.value.forEach(item => {
    const group = item[props.groupBy] || 'Other'
    if (!groups[group]) groups[group] = []
    groups[group].push(item)
  })
  return Object.keys(groups).length > 0 ? groups : null
})

const hasResults = computed(() => filteredResults.value.length > 0)

const totalItems = computed(() => filteredResults.value.length)

// Get global index for grouped items
function getGlobalIndex(group, localIndex) {
  if (!groupedResults.value) return localIndex
  
  let globalIndex = 0
  for (const g of Object.keys(groupedResults.value)) {
    if (g === group) return globalIndex + localIndex
    globalIndex += groupedResults.value[g].length
  }
  return globalIndex
}

// Container classes
const containerClasses = computed(() => [
  'relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl',
  'ring-1 ring-gray-200 dark:ring-gray-700'
])

// Input classes
const inputClasses = computed(() => [
  'w-full pl-11 pr-12 py-4 text-lg',
  'bg-transparent border-0',
  'text-gray-900 dark:text-gray-100',
  'placeholder-gray-400',
  'focus:outline-none focus:ring-0'
])

// Result item classes
function resultItemClasses(index) {
  return [
    'flex items-center gap-3 px-4 py-3 cursor-pointer transition-colors',
    activeIndex.value === index
      ? 'bg-primary-50 dark:bg-primary-900/20'
      : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
  ]
}

// Debounced search
let debounceTimeout = null
function onSearch() {
  if (debounceTimeout) clearTimeout(debounceTimeout)
  
  debounceTimeout = setTimeout(() => {
    emit('search', query.value)
  }, props.debounce)
}

// Keyboard navigation
function handleKeydown(e) {
  switch (e.key) {
    case 'ArrowDown':
      e.preventDefault()
      activeIndex.value = Math.min(activeIndex.value + 1, totalItems.value - 1)
      scrollToActive()
      break
    case 'ArrowUp':
      e.preventDefault()
      activeIndex.value = Math.max(activeIndex.value - 1, 0)
      scrollToActive()
      break
    case 'Enter':
      e.preventDefault()
      if (filteredResults.value[activeIndex.value]) {
        selectItem(filteredResults.value[activeIndex.value])
      }
      break
    case 'Escape':
      close()
      break
  }
}

function scrollToActive() {
  nextTick(() => {
    const container = resultsRef.value
    const activeEl = container?.children[activeIndex.value]
    if (activeEl) {
      activeEl.scrollIntoView({ block: 'nearest' })
    }
  })
}

// Actions
function selectItem(item) {
  addToRecent(query.value)
  emit('select', item)
  if (item.action) item.action()
  close()
}

function clearQuery() {
  query.value = ''
  activeIndex.value = 0
  emit('clear')
}

function close() {
  isOpen.value = false
  query.value = ''
  activeIndex.value = 0
}

// Recent searches
function addToRecent(term) {
  if (!term) return
  
  recentSearches.value = [
    term,
    ...recentSearches.value.filter(s => s !== term)
  ].slice(0, 5)
  
  localStorage.setItem('spotlight-recent', JSON.stringify(recentSearches.value))
}

function useRecent(term) {
  query.value = term
  emit('search', term)
}

function clearRecent() {
  recentSearches.value = []
  localStorage.removeItem('spotlight-recent')
}

// Global keyboard shortcut
function handleGlobalKeydown(e) {
  const modifier = {
    meta: e.metaKey,
    ctrl: e.ctrlKey,
    alt: e.altKey,
    shift: e.shiftKey
  }
  
  if (modifier[props.shortcutModifier] && e.key.toLowerCase() === props.shortcutKey) {
    e.preventDefault()
    isOpen.value = !isOpen.value
  }
}

// Focus input when opened
watch(isOpen, (open) => {
  if (open) {
    nextTick(() => inputRef.value?.focus())
  }
})

// Reset active index when results change
watch(filteredResults, () => {
  activeIndex.value = 0
})

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleGlobalKeydown)
  
  // Load recent searches
  const saved = localStorage.getItem('spotlight-recent')
  if (saved) {
    recentSearches.value = JSON.parse(saved)
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleGlobalKeydown)
  if (debounceTimeout) clearTimeout(debounceTimeout)
})

// Expose for parent control
defineExpose({
  open: () => { isOpen.value = true },
  close,
  query,
  activeIndex,
  clearQuery,
  clearRecent
})
</script>
