<template>
  <div :class="['json-editor rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg">
          <CodeBracketIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ isValid ? 'Valid JSON' : 'Invalid JSON' }}
            <span v-if="isValid"> • {{ itemCount }} items</span>
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
            viewMode === 'tree' 
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'tree'"
        >
          <Bars3BottomLeftIcon class="w-4 h-4 inline mr-1" />
          Tree
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
            viewMode === 'code' 
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'code'"
        >
          <CodeBracketIcon class="w-4 h-4 inline mr-1" />
          Code
        </button>
        <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Format JSON"
          @click="formatJson"
        >
          <SparklesIcon class="w-4 h-4" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Minify JSON"
          @click="minifyJson"
        >
          <ArrowsPointingInIcon class="w-4 h-4" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Copy to Clipboard"
          @click="copyToClipboard"
        >
          <ClipboardDocumentIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Tree View -->
    <div v-if="viewMode === 'tree'" class="overflow-auto" :style="{ maxHeight: height }">
      <div v-if="isValid" class="p-4">
        <JsonNode
          :data="parsedJson"
          :path="[]"
          :expanded-paths="expandedPaths"
          @toggle="togglePath"
          @edit="editValue"
          @delete="deleteKey"
          @add="addKey"
        />
      </div>
      <div v-else class="p-8 text-center">
        <ExclamationTriangleIcon class="w-12 h-12 mx-auto text-red-400 mb-3" />
        <p class="text-red-600 dark:text-red-400 font-medium">Invalid JSON</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ parseError }}</p>
      </div>
    </div>

    <!-- Code View -->
    <div v-else class="relative">
      <div class="flex">
        <!-- Line Numbers -->
        <div class="flex-shrink-0 px-3 py-4 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-mono text-right select-none border-r border-gray-200 dark:border-gray-700">
          <div v-for="n in lineCount" :key="n">{{ n }}</div>
        </div>
        <!-- Code Editor -->
        <textarea
          ref="codeEditorRef"
          v-model="codeContent"
          :class="[
            'flex-1 p-4 font-mono text-sm outline-none resize-none',
            theme === 'dark' ? 'bg-gray-900 text-gray-100' : 'bg-white text-gray-900'
          ]"
          :style="{ minHeight: height }"
          spellcheck="false"
          @input="onCodeInput"
          @keydown="handleCodeKeydown"
        />
      </div>
      <!-- Error Bar -->
      <div
        v-if="!isValid && codeContent"
        class="px-4 py-2 bg-red-50 dark:bg-red-900/20 border-t border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-xs"
      >
        <ExclamationTriangleIcon class="w-4 h-4 inline mr-1" />
        {{ parseError }}
      </div>
    </div>

    <!-- Status Bar -->
    <div v-if="showStatusBar" class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
      <div class="flex items-center gap-4">
        <span>Size: {{ byteSize }}</span>
        <span>Depth: {{ jsonDepth }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span :class="isValid ? 'text-green-500' : 'text-red-500'">
          {{ isValid ? '✓ Valid' : '✗ Invalid' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, h, defineComponent } from 'vue'
import {
  CodeBracketIcon,
  Bars3BottomLeftIcon,
  SparklesIcon,
  ArrowsPointingInIcon,
  ClipboardDocumentIcon,
  ExclamationTriangleIcon,
  ChevronRightIcon,
  ChevronDownIcon,
  PlusIcon,
  TrashIcon,
  PencilIcon
} from '@heroicons/vue/24/outline'

// Recursive JSON Node Component
const JsonNode = defineComponent({
  name: 'JsonNode',
  props: ['data', 'path', 'expandedPaths', 'keyName'],
  emits: ['toggle', 'edit', 'delete', 'add'],
  setup(props, { emit }) {
    const pathKey = computed(() => props.path.join('.'))
    const isExpanded = computed(() => props.expandedPaths.has(pathKey.value))
    const dataType = computed(() => {
      if (props.data === null) return 'null'
      if (Array.isArray(props.data)) return 'array'
      return typeof props.data
    })
    const isExpandable = computed(() => dataType.value === 'object' || dataType.value === 'array')
    const childCount = computed(() => {
      if (dataType.value === 'array') return props.data.length
      if (dataType.value === 'object') return Object.keys(props.data).length
      return 0
    })

    const toggle = () => emit('toggle', pathKey.value)
    const editThis = () => emit('edit', props.path, props.data)
    const deleteThis = () => emit('delete', props.path)
    const addChild = () => emit('add', props.path)

    const getTypeColor = (type) => {
      const colors = {
        string: 'text-green-600 dark:text-green-400',
        number: 'text-blue-600 dark:text-blue-400',
        boolean: 'text-purple-600 dark:text-purple-400',
        null: 'text-gray-400 dark:text-gray-500',
        object: 'text-gray-700 dark:text-gray-300',
        array: 'text-gray-700 dark:text-gray-300'
      }
      return colors[type] || 'text-gray-600'
    }

    const formatValue = (value, type) => {
      if (type === 'string') return `"${value}"`
      if (type === 'null') return 'null'
      if (type === 'boolean') return value ? 'true' : 'false'
      return String(value)
    }

    return () => {
      const children = []
      
      // Key name
      if (props.keyName !== undefined) {
        children.push(
          h('span', { class: 'text-gray-800 dark:text-gray-200 font-medium' }, `"${props.keyName}": `)
        )
      }

      if (isExpandable.value) {
        // Expandable node
        children.push(
          h('span', {
            class: 'cursor-pointer inline-flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded px-1',
            onClick: toggle
          }, [
            h(isExpanded.value ? ChevronDownIcon : ChevronRightIcon, { class: 'w-3 h-3 text-gray-400' }),
            h('span', { class: 'text-gray-500' }, dataType.value === 'array' ? '[' : '{'),
            !isExpanded.value && h('span', { class: 'text-gray-400 text-xs' }, ` ${childCount.value} items `),
            !isExpanded.value && h('span', { class: 'text-gray-500' }, dataType.value === 'array' ? ']' : '}')
          ])
        )

        // Children
        if (isExpanded.value) {
          const entries = dataType.value === 'array' 
            ? props.data.map((v, i) => [i, v])
            : Object.entries(props.data)

          children.push(
            h('div', { class: 'ml-4 border-l border-gray-200 dark:border-gray-700 pl-2' }, [
              ...entries.map(([key, value]) => 
                h(JsonNode, {
                  data: value,
                  path: [...props.path, key],
                  expandedPaths: props.expandedPaths,
                  keyName: dataType.value === 'object' ? key : undefined,
                  onToggle: (p) => emit('toggle', p),
                  onEdit: (p, v) => emit('edit', p, v),
                  onDelete: (p) => emit('delete', p),
                  onAdd: (p) => emit('add', p)
                })
              ),
              h('div', { class: 'flex items-center gap-1 mt-1' }, [
                h('button', {
                  class: 'p-1 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded',
                  title: 'Add item',
                  onClick: addChild
                }, [h(PlusIcon, { class: 'w-3 h-3' })])
              ])
            ]),
            h('span', { class: 'text-gray-500' }, dataType.value === 'array' ? ']' : '}')
          )
        }
      } else {
        // Leaf node
        children.push(
          h('span', { class: getTypeColor(dataType.value) }, formatValue(props.data, dataType.value))
        )
      }

      // Actions
      children.push(
        h('span', { class: 'ml-2 opacity-0 group-hover:opacity-100 inline-flex gap-1' }, [
          h('button', {
            class: 'p-1 text-gray-400 hover:text-blue-500 rounded',
            title: 'Edit',
            onClick: editThis
          }, [h(PencilIcon, { class: 'w-3 h-3' })]),
          props.path.length > 0 && h('button', {
            class: 'p-1 text-gray-400 hover:text-red-500 rounded',
            title: 'Delete',
            onClick: deleteThis
          }, [h(TrashIcon, { class: 'w-3 h-3' })])
        ])
      )

      return h('div', { class: 'group py-0.5 font-mono text-sm' }, children)
    }
  }
})

const props = defineProps({
  modelValue: { type: [Object, Array, String], default: () => ({}) },
  title: { type: String, default: 'JSON Editor' },
  height: { type: String, default: '400px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showStatusBar: { type: Boolean, default: true },
  readonly: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'change', 'error'])

const codeEditorRef = ref(null)
const viewMode = ref('tree')
const codeContent = ref('')
const expandedPaths = ref(new Set(['']))
const parseError = ref('')

const parsedJson = computed(() => {
  try {
    const data = typeof props.modelValue === 'string' 
      ? JSON.parse(props.modelValue) 
      : props.modelValue
    parseError.value = ''
    return data
  } catch (e) {
    parseError.value = e.message
    return null
  }
})

const isValid = computed(() => parsedJson.value !== null)

const itemCount = computed(() => {
  if (!isValid.value) return 0
  const count = (obj) => {
    if (Array.isArray(obj)) return obj.reduce((sum, item) => sum + count(item), obj.length)
    if (obj && typeof obj === 'object') return Object.values(obj).reduce((sum, v) => sum + count(v), Object.keys(obj).length)
    return 1
  }
  return count(parsedJson.value)
})

const lineCount = computed(() => codeContent.value.split('\n').length)

const byteSize = computed(() => {
  const bytes = new Blob([codeContent.value]).size
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
})

const jsonDepth = computed(() => {
  if (!isValid.value) return 0
  const getDepth = (obj) => {
    if (!obj || typeof obj !== 'object') return 0
    const values = Array.isArray(obj) ? obj : Object.values(obj)
    return 1 + Math.max(0, ...values.map(getDepth))
  }
  return getDepth(parsedJson.value)
})

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const updateCodeContent = () => {
  try {
    const data = typeof props.modelValue === 'string' 
      ? JSON.parse(props.modelValue) 
      : props.modelValue
    codeContent.value = JSON.stringify(data, null, 2)
  } catch {
    codeContent.value = typeof props.modelValue === 'string' ? props.modelValue : ''
  }
}

const togglePath = (path) => {
  if (expandedPaths.value.has(path)) {
    expandedPaths.value.delete(path)
  } else {
    expandedPaths.value.add(path)
  }
}

const editValue = (path, currentValue) => {
  const newValue = prompt('Enter new value (use JSON format for objects/arrays):', JSON.stringify(currentValue))
  if (newValue !== null) {
    try {
      const parsed = JSON.parse(newValue)
      updateValueAtPath(path, parsed)
    } catch {
      updateValueAtPath(path, newValue)
    }
  }
}

const deleteKey = (path) => {
  if (path.length === 0) return
  if (!confirm('Delete this item?')) return
  
  const data = JSON.parse(JSON.stringify(parsedJson.value))
  let current = data
  for (let i = 0; i < path.length - 1; i++) {
    current = current[path[i]]
  }
  
  const lastKey = path[path.length - 1]
  if (Array.isArray(current)) {
    current.splice(lastKey, 1)
  } else {
    delete current[lastKey]
  }
  
  emitUpdate(data)
}

const addKey = (path) => {
  const data = JSON.parse(JSON.stringify(parsedJson.value))
  let current = data
  for (const key of path) {
    current = current[key]
  }
  
  if (Array.isArray(current)) {
    current.push(null)
  } else {
    const key = prompt('Enter key name:')
    if (key) current[key] = null
  }
  
  emitUpdate(data)
}

const updateValueAtPath = (path, value) => {
  const data = JSON.parse(JSON.stringify(parsedJson.value))
  let current = data
  for (let i = 0; i < path.length - 1; i++) {
    current = current[path[i]]
  }
  current[path[path.length - 1]] = value
  emitUpdate(data)
}

const emitUpdate = (data) => {
  emit('update:modelValue', data)
  emit('change', data)
  updateCodeContent()
}

const onCodeInput = () => {
  try {
    const data = JSON.parse(codeContent.value)
    parseError.value = ''
    emit('update:modelValue', data)
    emit('change', data)
  } catch (e) {
    parseError.value = e.message
    emit('error', e.message)
  }
}

const handleCodeKeydown = (e) => {
  if (e.key === 'Tab') {
    e.preventDefault()
    const start = e.target.selectionStart
    const end = e.target.selectionEnd
    codeContent.value = codeContent.value.substring(0, start) + '  ' + codeContent.value.substring(end)
    nextTick(() => {
      e.target.selectionStart = e.target.selectionEnd = start + 2
    })
  }
}

const formatJson = () => {
  try {
    const data = JSON.parse(codeContent.value)
    codeContent.value = JSON.stringify(data, null, 2)
    parseError.value = ''
  } catch (e) {
    parseError.value = e.message
  }
}

const minifyJson = () => {
  try {
    const data = JSON.parse(codeContent.value)
    codeContent.value = JSON.stringify(data)
    parseError.value = ''
  } catch (e) {
    parseError.value = e.message
  }
}

const copyToClipboard = () => {
  navigator.clipboard.writeText(codeContent.value)
}

watch(() => props.modelValue, updateCodeContent, { immediate: true, deep: true })
</script>
