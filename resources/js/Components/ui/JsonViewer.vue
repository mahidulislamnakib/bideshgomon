<template>
  <div :class="['json-viewer font-mono text-sm', containerClasses]">
    <!-- Toolbar -->
    <div
      v-if="showToolbar"
      :class="['flex items-center justify-between px-3 py-2 border-b', themeClasses.toolbar]"
    >
      <div class="flex items-center gap-2">
        <span :class="['text-xs font-medium', themeClasses.label]">JSON Viewer</span>
        <span v-if="itemCount" :class="['text-xs', themeClasses.muted]">
          ({{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }})
        </span>
      </div>
      
      <div class="flex items-center gap-1">
        <button
          type="button"
          :title="allExpanded ? 'Collapse All' : 'Expand All'"
          :class="['p-1.5 rounded transition-colors', themeClasses.button]"
          @click="toggleAll"
        >
          <ChevronDownIcon v-if="allExpanded" class="w-4 h-4" />
          <ChevronRightIcon v-else class="w-4 h-4" />
        </button>
        
        <button
          v-if="showCopyButton"
          type="button"
          title="Copy JSON"
          :class="['p-1.5 rounded transition-colors', themeClasses.button]"
          @click="copyToClipboard"
        >
          <ClipboardDocumentIcon v-if="!copied" class="w-4 h-4" />
          <ClipboardDocumentCheckIcon v-else class="w-4 h-4 text-green-500" />
        </button>
        
        <button
          v-if="showSearchButton"
          type="button"
          title="Search"
          :class="['p-1.5 rounded transition-colors', themeClasses.button]"
          @click="showSearch = !showSearch"
        >
          <MagnifyingGlassIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition-all duration-150"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="showSearch"
        :class="['px-3 py-2 border-b', themeClasses.search]"
      >
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search keys or values..."
            :class="['w-full pl-9 pr-3 py-1.5 rounded text-sm', themeClasses.searchInput]"
          />
        </div>
        <div v-if="searchQuery && searchResults.length > 0" class="mt-2 text-xs text-gray-500">
          {{ searchResults.length }} match{{ searchResults.length === 1 ? '' : 'es' }} found
        </div>
      </div>
    </Transition>

    <!-- JSON Content -->
    <div :class="['p-3 overflow-auto', maxHeight && `max-h-[${maxHeight}]`]">
      <JsonNode
        :data="data"
        :path="[]"
        :depth="0"
        :expanded-paths="expandedPaths"
        :search-query="searchQuery"
        :search-results="searchResults"
        :theme="theme"
        :show-data-types="showDataTypes"
        :show-array-index="showArrayIndex"
        :quote-keys="quoteKeys"
        :indent-size="indentSize"
        @toggle="handleToggle"
        @copy-path="copyPath"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, h, defineComponent } from 'vue'
import { Transition } from 'vue'
import {
  ChevronRightIcon,
  ChevronDownIcon,
  ClipboardDocumentIcon,
  ClipboardDocumentCheckIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  data: {
    type: [Object, Array, String, Number, Boolean, null],
    default: null
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
  showCopyButton: {
    type: Boolean,
    default: true
  },
  showSearchButton: {
    type: Boolean,
    default: true
  },
  showDataTypes: {
    type: Boolean,
    default: false
  },
  showArrayIndex: {
    type: Boolean,
    default: true
  },
  quoteKeys: {
    type: Boolean,
    default: false
  },
  expandDepth: {
    type: Number,
    default: 2
  },
  indentSize: {
    type: Number,
    default: 2
  },
  maxHeight: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['copy', 'toggle', 'path-click'])

const expandedPaths = ref(new Set())
const allExpanded = ref(false)
const copied = ref(false)
const showSearch = ref(false)
const searchQuery = ref('')

const containerClasses = computed(() => {
  return props.theme === 'dark'
    ? 'bg-gray-900 border border-gray-700 rounded-lg'
    : 'bg-white border border-gray-200 rounded-lg'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      toolbar: 'border-gray-700 bg-gray-800',
      label: 'text-gray-300',
      muted: 'text-gray-500',
      button: 'text-gray-400 hover:text-white hover:bg-gray-700',
      search: 'border-gray-700 bg-gray-800',
      searchInput: 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none'
    }
  }
  return {
    toolbar: 'border-gray-200 bg-gray-50',
    label: 'text-gray-700',
    muted: 'text-gray-500',
    button: 'text-gray-500 hover:text-gray-900 hover:bg-gray-200',
    search: 'border-gray-200 bg-gray-50',
    searchInput: 'bg-white border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none border'
  }
})

const itemCount = computed(() => {
  if (Array.isArray(props.data)) return props.data.length
  if (props.data && typeof props.data === 'object') return Object.keys(props.data).length
  return 0
})

const searchResults = computed(() => {
  if (!searchQuery.value) return []
  
  const results = []
  const query = searchQuery.value.toLowerCase()
  
  const search = (obj, path = []) => {
    if (obj === null || obj === undefined) return
    
    if (typeof obj === 'object') {
      const keys = Object.keys(obj)
      keys.forEach(key => {
        const newPath = [...path, key]
        const pathStr = newPath.join('.')
        
        // Check key match
        if (key.toLowerCase().includes(query)) {
          results.push(pathStr)
        }
        
        // Check value match for primitives
        const value = obj[key]
        if (typeof value !== 'object' && String(value).toLowerCase().includes(query)) {
          results.push(pathStr)
        }
        
        // Recurse
        search(value, newPath)
      })
    }
  }
  
  search(props.data)
  return results
})

const getAllPaths = (obj, path = [], paths = []) => {
  if (obj && typeof obj === 'object') {
    paths.push(path.join('.'))
    Object.keys(obj).forEach(key => {
      getAllPaths(obj[key], [...path, key], paths)
    })
  }
  return paths
}

const initializeExpanded = () => {
  const expand = (obj, path = [], depth = 0) => {
    if (depth >= props.expandDepth) return
    if (obj && typeof obj === 'object') {
      const pathStr = path.join('.') || 'root'
      expandedPaths.value.add(pathStr)
      Object.keys(obj).forEach(key => {
        expand(obj[key], [...path, key], depth + 1)
      })
    }
  }
  expand(props.data)
}

const handleToggle = (path) => {
  const pathStr = path.join('.') || 'root'
  if (expandedPaths.value.has(pathStr)) {
    expandedPaths.value.delete(pathStr)
  } else {
    expandedPaths.value.add(pathStr)
  }
  emit('toggle', path)
}

const toggleAll = () => {
  if (allExpanded.value) {
    expandedPaths.value.clear()
  } else {
    const allPaths = getAllPaths(props.data)
    allPaths.forEach(p => expandedPaths.value.add(p || 'root'))
  }
  allExpanded.value = !allExpanded.value
}

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(JSON.stringify(props.data, null, 2))
    copied.value = true
    emit('copy', props.data)
    setTimeout(() => copied.value = false, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const copyPath = async (path) => {
  try {
    const pathStr = path.length ? path.join('.') : 'root'
    await navigator.clipboard.writeText(pathStr)
    emit('path-click', path)
  } catch (err) {
    console.error('Failed to copy path:', err)
  }
}

watch(() => props.data, () => {
  expandedPaths.value.clear()
  initializeExpanded()
}, { immediate: true })

// JsonNode sub-component
const JsonNode = defineComponent({
  name: 'JsonNode',
  props: ['data', 'path', 'depth', 'expandedPaths', 'searchQuery', 'searchResults', 'theme', 'showDataTypes', 'showArrayIndex', 'quoteKeys', 'indentSize'],
  emits: ['toggle', 'copy-path'],
  setup(props, { emit }) {
    const isExpanded = computed(() => {
      const pathStr = props.path.join('.') || 'root'
      return props.expandedPaths.has(pathStr)
    })

    const isHighlighted = computed(() => {
      if (!props.searchQuery) return false
      const pathStr = props.path.join('.')
      return props.searchResults.includes(pathStr)
    })

    const colors = computed(() => {
      if (props.theme === 'dark') {
        return {
          key: 'text-purple-400',
          string: 'text-green-400',
          number: 'text-blue-400',
          boolean: 'text-orange-400',
          null: 'text-gray-500',
          bracket: 'text-gray-400',
          type: 'text-gray-600',
          highlight: 'bg-yellow-500/20'
        }
      }
      return {
        key: 'text-purple-700',
        string: 'text-green-700',
        number: 'text-blue-700',
        boolean: 'text-orange-700',
        null: 'text-gray-500',
        bracket: 'text-gray-600',
        type: 'text-gray-400',
        highlight: 'bg-yellow-200'
      }
    })

    const getType = (val) => {
      if (val === null) return 'null'
      if (Array.isArray(val)) return 'array'
      return typeof val
    }

    const renderValue = (value, key = null) => {
      const type = getType(value)
      const indent = `${props.indentSize * (props.depth + 1)}ch`

      if (type === 'object' || type === 'array') {
        const isArray = type === 'array'
        const items = isArray ? value : Object.entries(value)
        const isEmpty = items.length === 0
        const bracket = isArray ? ['[', ']'] : ['{', '}']

        return h('div', { class: 'relative' }, [
          // Collapsible header
          h('div', {
            class: ['flex items-start cursor-pointer hover:bg-gray-100/50 dark:hover:bg-gray-800/50 -ml-5 pl-5 rounded', isHighlighted.value && colors.value.highlight],
            onClick: () => emit('toggle', props.path)
          }, [
            h('span', { class: 'w-4 h-4 flex items-center justify-center mr-1 flex-shrink-0' }, [
              isExpanded.value
                ? h(ChevronDownIcon, { class: 'w-3 h-3' })
                : h(ChevronRightIcon, { class: 'w-3 h-3' })
            ]),
            key !== null && h('span', { class: colors.value.key }, [
              props.quoteKeys ? `"${key}"` : key,
              ': '
            ]),
            h('span', { class: colors.value.bracket }, bracket[0]),
            !isExpanded.value && !isEmpty && h('span', { class: 'text-gray-400' }, '...'),
            !isExpanded.value && h('span', { class: colors.value.bracket }, bracket[1]),
            props.showDataTypes && h('span', { class: `ml-2 text-xs ${colors.value.type}` }, 
              isArray ? `Array(${value.length})` : `Object(${Object.keys(value).length})`
            )
          ]),

          // Children
          isExpanded.value && h('div', { style: { paddingLeft: indent } }, [
            ...items.map((item, idx) => {
              const [childKey, childValue] = isArray ? [idx, item] : item
              const childPath = [...props.path, String(childKey)]
              
              return h(JsonNode, {
                key: childKey,
                data: childValue,
                path: childPath,
                depth: props.depth + 1,
                expandedPaths: props.expandedPaths,
                searchQuery: props.searchQuery,
                searchResults: props.searchResults,
                theme: props.theme,
                showDataTypes: props.showDataTypes,
                showArrayIndex: props.showArrayIndex,
                quoteKeys: props.quoteKeys,
                indentSize: props.indentSize,
                onToggle: (p) => emit('toggle', p),
                onCopyPath: (p) => emit('copy-path', p)
              })
            }),
            h('div', { class: colors.value.bracket }, bracket[1])
          ])
        ])
      }

      // Primitive values
      let valueClass = colors.value.null
      let displayValue = 'null'

      if (type === 'string') {
        valueClass = colors.value.string
        displayValue = `"${value}"`
      } else if (type === 'number') {
        valueClass = colors.value.number
        displayValue = String(value)
      } else if (type === 'boolean') {
        valueClass = colors.value.boolean
        displayValue = String(value)
      }

      return h('div', {
        class: ['flex items-center group', isHighlighted.value && colors.value.highlight]
      }, [
        h('span', { class: 'w-4 mr-1' }), // Spacer for alignment
        key !== null && h('span', { class: colors.value.key }, [
          props.showArrayIndex || typeof key !== 'number' 
            ? (props.quoteKeys ? `"${key}"` : key)
            : '',
          typeof key !== 'number' || props.showArrayIndex ? ': ' : ''
        ]),
        h('span', { class: valueClass }, displayValue),
        props.showDataTypes && h('span', { class: `ml-2 text-xs ${colors.value.type}` }, type)
      ])
    }

    return () => {
      if (props.depth === 0) {
        return renderValue(props.data)
      }
      
      const key = props.path[props.path.length - 1]
      return renderValue(props.data, key)
    }
  }
})

defineExpose({
  expandAll: () => { allExpanded.value = true; toggleAll() },
  collapseAll: () => { allExpanded.value = false; toggleAll() },
  copy: copyToClipboard
})
</script>
