<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-2" :class="labelClasses">
      {{ label }}
    </label>

    <!-- Search/Filter -->
    <div v-if="searchable" class="mb-3">
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="searchPlaceholder"
        class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
      />
    </div>

    <!-- Tree -->
    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
      <ul v-if="filteredNodes.length > 0" class="divide-y divide-gray-100">
        <TreeNode
          v-for="node in filteredNodes"
          :key="node.id"
          :node="node"
          :level="0"
          :expanded="expandedNodes"
          :selected="selectedNodes"
          :checked="checkedNodes"
          :selectable="selectable"
          :checkable="checkable"
          :show-icon="showIcon"
          :indent-size="indentSize"
          @toggle="toggleNode"
          @select="selectNode"
          @check="checkNode"
        />
      </ul>
      <div v-else class="py-8 text-center text-gray-500 text-sm">
        {{ emptyText }}
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
import { ref, computed, watch, h, defineComponent } from 'vue'
import { ChevronRightIcon, FolderIcon, DocumentIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  nodes: {
    type: Array,
    required: true
    // [{ id, label, icon?, children?, disabled? }]
  },
  modelValue: {
    type: [String, Number, Array],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  selectable: {
    type: Boolean,
    default: true
  },
  checkable: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  expandAll: {
    type: Boolean,
    default: false
  },
  showIcon: {
    type: Boolean,
    default: true
  },
  indentSize: {
    type: Number,
    default: 24
  },
  emptyText: {
    type: String,
    default: 'No items found'
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'select', 'check', 'expand'])

// State
const searchQuery = ref('')
const expandedNodes = ref(new Set())
const selectedNodes = ref(new Set())
const checkedNodes = ref(new Set())

// Initialize selected from modelValue
watch(() => props.modelValue, (newVal) => {
  if (props.checkable) {
    checkedNodes.value = new Set(Array.isArray(newVal) ? newVal : newVal ? [newVal] : [])
  } else {
    selectedNodes.value = new Set(Array.isArray(newVal) ? newVal : newVal ? [newVal] : [])
  }
}, { immediate: true })

// Expand all if prop is set
watch(() => props.expandAll, (expand) => {
  if (expand) {
    expandAllNodes(props.nodes)
  }
}, { immediate: true })

// Filter nodes based on search
const filteredNodes = computed(() => {
  if (!searchQuery.value) return props.nodes
  return filterNodes(props.nodes, searchQuery.value.toLowerCase())
})

// Filter nodes recursively
function filterNodes(nodes, query) {
  return nodes.reduce((acc, node) => {
    const matchesQuery = node.label.toLowerCase().includes(query)
    const filteredChildren = node.children ? filterNodes(node.children, query) : []
    
    if (matchesQuery || filteredChildren.length > 0) {
      acc.push({
        ...node,
        children: filteredChildren.length > 0 ? filteredChildren : node.children
      })
      // Auto-expand matching parents
      if (filteredChildren.length > 0) {
        expandedNodes.value.add(node.id)
      }
    }
    
    return acc
  }, [])
}

// Expand all nodes
function expandAllNodes(nodes) {
  nodes.forEach(node => {
    if (node.children && node.children.length > 0) {
      expandedNodes.value.add(node.id)
      expandAllNodes(node.children)
    }
  })
}

// Toggle node expansion
function toggleNode(nodeId) {
  if (expandedNodes.value.has(nodeId)) {
    expandedNodes.value.delete(nodeId)
  } else {
    expandedNodes.value.add(nodeId)
  }
  emit('expand', { id: nodeId, expanded: expandedNodes.value.has(nodeId) })
}

// Select node
function selectNode(node) {
  if (node.disabled || !props.selectable) return
  
  if (props.multiple) {
    if (selectedNodes.value.has(node.id)) {
      selectedNodes.value.delete(node.id)
    } else {
      selectedNodes.value.add(node.id)
    }
    emit('update:modelValue', Array.from(selectedNodes.value))
  } else {
    selectedNodes.value = new Set([node.id])
    emit('update:modelValue', node.id)
  }
  
  emit('select', node)
}

// Check node (for checkable mode)
function checkNode(node) {
  if (node.disabled || !props.checkable) return
  
  if (checkedNodes.value.has(node.id)) {
    checkedNodes.value.delete(node.id)
    // Uncheck all children
    uncheckChildren(node)
  } else {
    checkedNodes.value.add(node.id)
    // Check all children
    checkChildren(node)
  }
  
  emit('update:modelValue', Array.from(checkedNodes.value))
  emit('check', { node, checked: checkedNodes.value.has(node.id) })
}

// Check all children
function checkChildren(node) {
  if (node.children) {
    node.children.forEach(child => {
      checkedNodes.value.add(child.id)
      checkChildren(child)
    })
  }
}

// Uncheck all children
function uncheckChildren(node) {
  if (node.children) {
    node.children.forEach(child => {
      checkedNodes.value.delete(child.id)
      uncheckChildren(child)
    })
  }
}

// Container classes
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60 pointer-events-none' : ''
})

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

// TreeNode component (inline for simplicity)
const TreeNode = defineComponent({
  name: 'TreeNode',
  props: ['node', 'level', 'expanded', 'selected', 'checked', 'selectable', 'checkable', 'showIcon', 'indentSize'],
  emits: ['toggle', 'select', 'check'],
  setup(props, { emit }) {
    const hasChildren = computed(() => props.node.children && props.node.children.length > 0)
    const isExpanded = computed(() => props.expanded.has(props.node.id))
    const isSelected = computed(() => props.selected.has(props.node.id))
    const isChecked = computed(() => props.checked.has(props.node.id))
    
    return () => {
      const children = []
      
      // Main node
      children.push(
        h('li', { class: 'group' }, [
          h('div', {
            class: [
              'flex items-center gap-2 px-3 py-2 cursor-pointer transition-colors',
              isSelected.value ? 'bg-primary-50' : 'hover:bg-gray-50',
              props.node.disabled ? 'opacity-50 cursor-not-allowed' : ''
            ],
            style: { paddingLeft: `${props.level * props.indentSize + 12}px` },
            onClick: () => {
              if (props.selectable) emit('select', props.node)
            }
          }, [
            // Expand toggle
            h('button', {
              type: 'button',
              class: [
                'flex-shrink-0 w-5 h-5 flex items-center justify-center rounded transition-transform',
                hasChildren.value ? 'hover:bg-gray-200' : 'invisible'
              ],
              onClick: (e) => {
                e.stopPropagation()
                if (hasChildren.value) emit('toggle', props.node.id)
              }
            }, [
              h(ChevronRightIcon, {
                class: [
                  'h-4 w-4 text-gray-400 transition-transform',
                  isExpanded.value ? 'rotate-90' : ''
                ]
              })
            ]),
            
            // Checkbox
            props.checkable && h('input', {
              type: 'checkbox',
              checked: isChecked.value,
              class: 'h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500',
              onClick: (e) => {
                e.stopPropagation()
                emit('check', props.node)
              }
            }),
            
            // Icon
            props.showIcon && h(hasChildren.value ? FolderIcon : DocumentIcon, {
              class: 'h-5 w-5 flex-shrink-0 text-gray-400'
            }),
            
            // Label
            h('span', {
              class: [
                'flex-1 text-sm',
                isSelected.value ? 'font-medium text-primary-700' : 'text-gray-700'
              ]
            }, props.node.label),
            
            // Badge (if provided)
            props.node.badge && h('span', {
              class: 'px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600'
            }, props.node.badge)
          ])
        ])
      )
      
      // Children (if expanded)
      if (hasChildren.value && isExpanded.value) {
        children.push(
          h('ul', {}, 
            props.node.children.map(child => 
              h(TreeNode, {
                key: child.id,
                node: child,
                level: props.level + 1,
                expanded: props.expanded,
                selected: props.selected,
                checked: props.checked,
                selectable: props.selectable,
                checkable: props.checkable,
                showIcon: props.showIcon,
                indentSize: props.indentSize,
                onToggle: (id) => emit('toggle', id),
                onSelect: (node) => emit('select', node),
                onCheck: (node) => emit('check', node)
              })
            )
          )
        )
      }
      
      return children
    }
  }
})
</script>
