<template>
  <div class="tree-select relative" ref="containerRef">
    <!-- Trigger -->
    <button
      type="button"
      :class="[
        'w-full flex items-center justify-between gap-2 px-4 py-3 rounded-xl border transition-all',
        isOpen
          ? 'border-blue-500 ring-2 ring-blue-500/20'
          : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
        themeClasses
      ]"
      :disabled="disabled"
      @click="toggle"
    >
      <div class="flex items-center gap-2 flex-1 min-w-0">
        <FolderIcon v-if="multiple && selectedNodes.length === 0" class="w-5 h-5 text-gray-400 flex-shrink-0" />
        <template v-if="multiple && selectedNodes.length > 0">
          <div class="flex flex-wrap gap-1 flex-1">
            <span
              v-for="node in selectedNodes.slice(0, 3)"
              :key="node.id"
              class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md text-sm"
            >
              {{ node.label }}
              <XMarkIcon
                class="w-3 h-3 cursor-pointer hover:text-blue-800"
                @click.stop="deselectNode(node)"
              />
            </span>
            <span v-if="selectedNodes.length > 3" class="text-sm text-gray-500">
              +{{ selectedNodes.length - 3 }} more
            </span>
          </div>
        </template>
        <template v-else-if="!multiple && selectedNode">
          <component
            v-if="selectedNode.icon"
            :is="selectedNode.icon"
            class="w-5 h-5 text-gray-500 flex-shrink-0"
          />
          <span class="text-gray-900 dark:text-white truncate">{{ selectedNode.label }}</span>
        </template>
        <span v-else class="text-gray-400">{{ placeholder }}</span>
      </div>
      <ChevronDownIcon
        :class="['w-5 h-5 text-gray-400 transition-transform', isOpen && 'rotate-180']"
      />
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-1"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 w-full mt-2 rounded-xl border shadow-xl',
          themeClasses === 'bg-white' ? 'bg-white border-gray-200' : 'bg-gray-800 border-gray-700'
        ]"
      >
        <!-- Search -->
        <div v-if="searchable" class="p-2 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white placeholder-gray-400"
            />
          </div>
        </div>

        <!-- Tree -->
        <div class="p-2 max-h-72 overflow-auto">
          <TreeNode
            v-for="node in filteredNodes"
            :key="node.id"
            :node="node"
            :selected-ids="selectedIds"
            :expanded-ids="expandedIds"
            :multiple="multiple"
            :selectable-groups="selectableGroups"
            :level="0"
            @select="handleSelect"
            @toggle="toggleExpand"
          />
          <div v-if="filteredNodes.length === 0" class="py-8 text-center">
            <FolderIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500">No items found</p>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="showFooter && multiple" class="flex items-center justify-between p-2 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
            @click="clearSelection"
          >
            Clear all
          </button>
          <button
            type="button"
            class="text-sm font-medium text-blue-600 dark:text-blue-400 px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
            @click="isOpen = false"
          >
            Done
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount, h } from 'vue'
import {
  ChevronDownIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  FolderIcon,
  FolderOpenIcon,
  DocumentIcon,
  XMarkIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

// TreeNode Component
const TreeNode = {
  name: 'TreeNode',
  props: {
    node: { type: Object, required: true },
    selectedIds: { type: Array, required: true },
    expandedIds: { type: Array, required: true },
    multiple: { type: Boolean, default: false },
    selectableGroups: { type: Boolean, default: true },
    level: { type: Number, default: 0 }
  },
  emits: ['select', 'toggle'],
  setup(props, { emit }) {
    const hasChildren = computed(() => props.node.children && props.node.children.length > 0)
    const isExpanded = computed(() => props.expandedIds.includes(props.node.id))
    const isSelected = computed(() => props.selectedIds.includes(props.node.id))
    const isDisabled = computed(() => props.node.disabled || (!props.selectableGroups && hasChildren.value))
    
    return () => h('div', { class: 'tree-node' }, [
      h('div', {
        class: [
          'flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition-colors',
          isSelected.value ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700',
          isDisabled.value && 'opacity-50 cursor-not-allowed'
        ],
        style: { paddingLeft: `${props.level * 16 + 8}px` },
        onClick: () => {
          if (!isDisabled.value) emit('select', props.node)
        }
      }, [
        hasChildren.value
          ? h('button', {
              type: 'button',
              class: 'p-0.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded',
              onClick: (e) => { e.stopPropagation(); emit('toggle', props.node.id) }
            }, [h(isExpanded.value ? ChevronDownIcon : ChevronRightIcon, { class: 'w-4 h-4 text-gray-400' })])
          : h('span', { class: 'w-5' }),
        h(hasChildren.value 
          ? (isExpanded.value ? FolderOpenIcon : FolderIcon) 
          : (props.node.icon || DocumentIcon), 
          { class: ['w-5 h-5', isSelected.value ? 'text-blue-500' : 'text-gray-400'] }
        ),
        h('span', { class: ['flex-1 text-sm', isSelected.value ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300'] }, props.node.label),
        props.node.badge && h('span', { class: 'px-1.5 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 rounded' }, props.node.badge),
        props.multiple && isSelected.value && h(CheckIcon, { class: 'w-4 h-4 text-blue-500' })
      ]),
      hasChildren.value && isExpanded.value && h('div', { class: 'children' },
        props.node.children.map(child =>
          h(TreeNode, {
            key: child.id,
            node: child,
            selectedIds: props.selectedIds,
            expandedIds: props.expandedIds,
            multiple: props.multiple,
            selectableGroups: props.selectableGroups,
            level: props.level + 1,
            onSelect: (n) => emit('select', n),
            onToggle: (id) => emit('toggle', id)
          })
        )
      )
    ])
  }
}

const props = defineProps({
  modelValue: { type: [String, Number, Array], default: null },
  nodes: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Select item...' },
  searchPlaceholder: { type: String, default: 'Search...' },
  multiple: { type: Boolean, default: false },
  searchable: { type: Boolean, default: true },
  selectableGroups: { type: Boolean, default: true },
  expandAll: { type: Boolean, default: false },
  showFooter: { type: Boolean, default: true },
  disabled: { type: Boolean, default: false },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'change'])

const containerRef = ref(null)
const searchInputRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const expandedIds = ref([])

const themeClasses = computed(() =>
  props.theme === 'dark' ? 'bg-gray-800' : 'bg-white'
)

const selectedIds = computed(() => {
  if (props.multiple) return Array.isArray(props.modelValue) ? props.modelValue : []
  return props.modelValue ? [props.modelValue] : []
})

const flattenNodes = (nodes, result = []) => {
  nodes.forEach(node => {
    result.push(node)
    if (node.children) flattenNodes(node.children, result)
  })
  return result
}

const allNodes = computed(() => flattenNodes(props.nodes))

const selectedNode = computed(() =>
  !props.multiple && props.modelValue
    ? allNodes.value.find(n => n.id === props.modelValue)
    : null
)

const selectedNodes = computed(() =>
  props.multiple && Array.isArray(props.modelValue)
    ? props.modelValue.map(id => allNodes.value.find(n => n.id === id)).filter(Boolean)
    : []
)

const filterNodes = (nodes, query) => {
  return nodes.reduce((acc, node) => {
    const matches = node.label.toLowerCase().includes(query)
    const filteredChildren = node.children ? filterNodes(node.children, query) : []
    
    if (matches || filteredChildren.length > 0) {
      acc.push({
        ...node,
        children: filteredChildren.length > 0 ? filteredChildren : node.children
      })
    }
    return acc
  }, [])
}

const filteredNodes = computed(() =>
  searchQuery.value
    ? filterNodes(props.nodes, searchQuery.value.toLowerCase())
    : props.nodes
)

const toggle = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value && props.searchable) {
    nextTick(() => searchInputRef.value?.focus())
  }
}

const toggleExpand = (id) => {
  const index = expandedIds.value.indexOf(id)
  if (index > -1) {
    expandedIds.value.splice(index, 1)
  } else {
    expandedIds.value.push(id)
  }
}

const handleSelect = (node) => {
  if (props.multiple) {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const index = current.indexOf(node.id)
    if (index > -1) {
      current.splice(index, 1)
    } else {
      current.push(node.id)
    }
    emit('update:modelValue', current)
    emit('change', current)
  } else {
    emit('update:modelValue', node.id)
    emit('change', node.id)
    isOpen.value = false
  }
}

const deselectNode = (node) => {
  if (!props.multiple) return
  const current = [...props.modelValue].filter(id => id !== node.id)
  emit('update:modelValue', current)
  emit('change', current)
}

const clearSelection = () => {
  emit('update:modelValue', props.multiple ? [] : null)
  emit('change', props.multiple ? [] : null)
}

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

const initExpandedIds = (nodes, result = []) => {
  nodes.forEach(node => {
    if (props.expandAll && node.children) result.push(node.id)
    if (node.children) initExpandedIds(node.children, result)
  })
  return result
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  expandedIds.value = initExpandedIds(props.nodes)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(() => props.nodes, () => {
  if (props.expandAll) {
    expandedIds.value = initExpandedIds(props.nodes)
  }
})
</script>
