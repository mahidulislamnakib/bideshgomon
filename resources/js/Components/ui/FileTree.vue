<template>
  <div :class="['file-tree', containerClasses]">
    <!-- Toolbar -->
    <div v-if="showToolbar" :class="['flex items-center gap-2 p-2 border-b', themeClasses.toolbar]">
      <button
        @click="expandAll"
        :class="['p-1.5 rounded transition-colors', themeClasses.toolbarBtn]"
        title="Expand All"
      >
        <ChevronDoubleDownIcon class="w-4 h-4" />
      </button>
      <button
        @click="collapseAll"
        :class="['p-1.5 rounded transition-colors', themeClasses.toolbarBtn]"
        title="Collapse All"
      >
        <ChevronDoubleUpIcon class="w-4 h-4" />
      </button>
      <div class="flex-1" />
      <div v-if="showSearch" class="relative">
        <MagnifyingGlassIcon class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search files..."
          :class="['pl-8 pr-3 py-1 text-sm rounded border w-48', themeClasses.searchInput]"
        />
      </div>
    </div>

    <!-- Tree Container -->
    <div :class="['overflow-auto', themeClasses.container]" :style="{ maxHeight }">
      <FileTreeNode
        v-for="node in filteredItems"
        :key="node.id || node.name"
        :node="node"
        :level="0"
        :theme="theme"
        :expanded-ids="expandedIds"
        :selected-id="selectedId"
        :draggable="draggable"
        :show-icons="showIcons"
        :show-size="showSize"
        :indent-size="indentSize"
        @toggle="handleToggle"
        @select="handleSelect"
        @context-menu="handleContextMenu"
        @drop="handleDrop"
      />

      <!-- Empty State -->
      <div v-if="!filteredItems.length" class="p-4 text-center">
        <FolderIcon :class="['w-12 h-12 mx-auto mb-2', themeClasses.emptyIcon]" />
        <p :class="['text-sm', themeClasses.emptyText]">
          {{ searchQuery ? 'No matching files' : 'No files' }}
        </p>
      </div>
    </div>

    <!-- Context Menu -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="contextMenu.show"
          ref="contextMenuRef"
          :class="[
            'fixed z-50 min-w-[160px] py-1 rounded-lg shadow-lg border',
            themeClasses.contextMenu
          ]"
          :style="{ top: `${contextMenu.y}px`, left: `${contextMenu.x}px` }"
          @click.stop
        >
          <button
            v-for="action in contextMenuActions"
            :key="action.id"
            :class="[
              'w-full px-3 py-2 text-left text-sm flex items-center gap-2 transition-colors',
              themeClasses.contextMenuItem,
              action.danger && 'text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20'
            ]"
            @click="handleContextAction(action)"
          >
            <component :is="action.icon" class="w-4 h-4" />
            {{ action.label }}
          </button>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, h } from 'vue'
import {
  ChevronDoubleDownIcon,
  ChevronDoubleUpIcon,
  MagnifyingGlassIcon,
  FolderIcon,
  DocumentIcon,
  FolderPlusIcon,
  DocumentPlusIcon,
  PencilIcon,
  TrashIcon,
  DocumentDuplicateIcon,
  ClipboardIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

// FileTreeNode component defined inline
const FileTreeNode = {
  name: 'FileTreeNode',
  props: ['node', 'level', 'theme', 'expandedIds', 'selectedId', 'draggable', 'showIcons', 'showSize', 'indentSize'],
  emits: ['toggle', 'select', 'context-menu', 'drop'],
  setup(props, { emit }) {
    const isExpanded = computed(() => props.expandedIds.has(props.node.id || props.node.name))
    const isSelected = computed(() => props.selectedId === (props.node.id || props.node.name))
    const isFolder = computed(() => props.node.type === 'folder' || props.node.children)

    const themeClasses = computed(() => {
      if (props.theme === 'dark') {
        return {
          node: 'hover:bg-gray-800',
          nodeSelected: 'bg-blue-900/50 hover:bg-blue-900/50',
          text: 'text-gray-300',
          textSelected: 'text-blue-400',
          icon: 'text-gray-500',
          iconFolder: 'text-yellow-500',
          size: 'text-gray-600'
        }
      }
      return {
        node: 'hover:bg-gray-100',
        nodeSelected: 'bg-blue-50 hover:bg-blue-50',
        text: 'text-gray-700',
        textSelected: 'text-blue-600',
        icon: 'text-gray-400',
        iconFolder: 'text-yellow-500',
        size: 'text-gray-400'
      }
    })

    const fileIcon = computed(() => {
      if (isFolder.value) return FolderIcon
      
      const ext = props.node.name.split('.').pop()?.toLowerCase()
      const iconMap = {
        js: { color: 'text-yellow-500' },
        ts: { color: 'text-blue-500' },
        vue: { color: 'text-green-500' },
        json: { color: 'text-yellow-600' },
        md: { color: 'text-gray-500' },
        css: { color: 'text-blue-400' },
        html: { color: 'text-orange-500' },
        php: { color: 'text-purple-500' },
        py: { color: 'text-blue-600' }
      }
      
      return { icon: DocumentIcon, ...iconMap[ext] }
    })

    const formatSize = (bytes) => {
      if (!bytes) return ''
      const units = ['B', 'KB', 'MB', 'GB']
      let i = 0
      while (bytes >= 1024 && i < units.length - 1) {
        bytes /= 1024
        i++
      }
      return `${bytes.toFixed(i > 0 ? 1 : 0)} ${units[i]}`
    }

    const handleClick = () => {
      if (isFolder.value) {
        emit('toggle', props.node)
      }
      emit('select', props.node)
    }

    const handleContextMenu = (e) => {
      e.preventDefault()
      emit('context-menu', { event: e, node: props.node })
    }

    const handleDragStart = (e) => {
      e.dataTransfer.setData('application/json', JSON.stringify(props.node))
      e.dataTransfer.effectAllowed = 'move'
    }

    const handleDragOver = (e) => {
      if (isFolder.value) {
        e.preventDefault()
        e.dataTransfer.dropEffect = 'move'
      }
    }

    const handleDrop = (e) => {
      e.preventDefault()
      const data = JSON.parse(e.dataTransfer.getData('application/json'))
      emit('drop', { source: data, target: props.node })
    }

    return () => h('div', { class: 'select-none' }, [
      // Node row
      h('div', {
        class: [
          'flex items-center gap-1 px-2 py-1 cursor-pointer rounded transition-colors',
          themeClasses.value.node,
          isSelected.value && themeClasses.value.nodeSelected
        ],
        style: { paddingLeft: `${props.level * props.indentSize + 8}px` },
        draggable: props.draggable,
        onClick: handleClick,
        onContextmenu: handleContextMenu,
        onDragstart: handleDragStart,
        onDragover: handleDragOver,
        onDrop: handleDrop
      }, [
        // Expand icon
        isFolder.value && h('span', {
          class: ['transform transition-transform duration-200', isExpanded.value ? 'rotate-90' : '']
        }, [
          h('svg', { class: 'w-3 h-3', viewBox: '0 0 12 12', fill: 'currentColor' }, [
            h('path', { d: 'M4 2l4 4-4 4V2z' })
          ])
        ]),
        !isFolder.value && h('span', { class: 'w-3' }),

        // File/folder icon
        props.showIcons && h(fileIcon.value.icon || fileIcon.value, {
          class: ['w-4 h-4 flex-shrink-0', isFolder.value ? themeClasses.value.iconFolder : (fileIcon.value.color || themeClasses.value.icon)]
        }),

        // Name
        h('span', {
          class: ['flex-1 truncate text-sm', isSelected.value ? themeClasses.value.textSelected : themeClasses.value.text]
        }, props.node.name),

        // Size
        props.showSize && props.node.size && h('span', {
          class: ['text-xs', themeClasses.value.size]
        }, formatSize(props.node.size))
      ]),

      // Children
      isFolder.value && isExpanded.value && props.node.children?.map(child =>
        h(FileTreeNode, {
          key: child.id || child.name,
          node: child,
          level: props.level + 1,
          theme: props.theme,
          expandedIds: props.expandedIds,
          selectedId: props.selectedId,
          draggable: props.draggable,
          showIcons: props.showIcons,
          showSize: props.showSize,
          indentSize: props.indentSize,
          onToggle: (n) => emit('toggle', n),
          onSelect: (n) => emit('select', n),
          onContextMenu: (data) => emit('context-menu', data),
          onDrop: (data) => emit('drop', data)
        })
      )
    ])
  }
}

const props = defineProps({
  items: {
    type: Array,
    required: true
    // [{ name: 'src', type: 'folder', children: [...] }, { name: 'file.js', size: 1024 }]
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
  showSearch: {
    type: Boolean,
    default: true
  },
  showIcons: {
    type: Boolean,
    default: true
  },
  showSize: {
    type: Boolean,
    default: false
  },
  draggable: {
    type: Boolean,
    default: false
  },
  indentSize: {
    type: Number,
    default: 16
  },
  maxHeight: {
    type: String,
    default: '400px'
  },
  defaultExpanded: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['select', 'toggle', 'context-menu', 'action', 'drop'])

const searchQuery = ref('')
const expandedIds = ref(new Set(props.defaultExpanded))
const selectedId = ref(null)
const contextMenuRef = ref(null)
const contextMenu = ref({ show: false, x: 0, y: 0, node: null })

const contextMenuActions = computed(() => {
  const node = contextMenu.value.node
  if (!node) return []

  const isFolder = node.type === 'folder' || node.children

  return [
    isFolder && { id: 'new-file', label: 'New File', icon: DocumentPlusIcon },
    isFolder && { id: 'new-folder', label: 'New Folder', icon: FolderPlusIcon },
    { id: 'rename', label: 'Rename', icon: PencilIcon },
    { id: 'duplicate', label: 'Duplicate', icon: DocumentDuplicateIcon },
    { id: 'copy-path', label: 'Copy Path', icon: ClipboardIcon },
    !isFolder && { id: 'download', label: 'Download', icon: ArrowDownTrayIcon },
    { id: 'delete', label: 'Delete', icon: TrashIcon, danger: true }
  ].filter(Boolean)
})

const getAllIds = (items, ids = []) => {
  items.forEach(item => {
    const id = item.id || item.name
    ids.push(id)
    if (item.children) {
      getAllIds(item.children, ids)
    }
  })
  return ids
}

const filterItems = (items, query) => {
  if (!query) return items
  
  const q = query.toLowerCase()
  const result = []

  items.forEach(item => {
    const matches = item.name.toLowerCase().includes(q)
    const hasChildren = item.children?.length

    if (hasChildren) {
      const filteredChildren = filterItems(item.children, query)
      if (filteredChildren.length || matches) {
        result.push({ ...item, children: filteredChildren })
      }
    } else if (matches) {
      result.push(item)
    }
  })

  return result
}

const filteredItems = computed(() => {
  return filterItems(props.items, searchQuery.value)
})

const expandAll = () => {
  expandedIds.value = new Set(getAllIds(props.items))
}

const collapseAll = () => {
  expandedIds.value = new Set()
}

const handleToggle = (node) => {
  const id = node.id || node.name
  if (expandedIds.value.has(id)) {
    expandedIds.value.delete(id)
  } else {
    expandedIds.value.add(id)
  }
  expandedIds.value = new Set(expandedIds.value) // Trigger reactivity
  emit('toggle', node)
}

const handleSelect = (node) => {
  selectedId.value = node.id || node.name
  emit('select', node)
}

const handleContextMenu = ({ event, node }) => {
  contextMenu.value = {
    show: true,
    x: event.clientX,
    y: event.clientY,
    node
  }
  emit('context-menu', { event, node })
}

const handleContextAction = (action) => {
  emit('action', { action: action.id, node: contextMenu.value.node })
  contextMenu.value.show = false
}

const handleDrop = (data) => {
  emit('drop', data)
}

const closeContextMenu = (e) => {
  if (contextMenuRef.value && !contextMenuRef.value.contains(e.target)) {
    contextMenu.value.show = false
  }
}

const containerClasses = computed(() => {
  return props.theme === 'dark' ? 'bg-gray-900' : 'bg-white'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      toolbar: 'border-gray-700 bg-gray-800',
      toolbarBtn: 'text-gray-400 hover:bg-gray-700 hover:text-white',
      searchInput: 'bg-gray-900 border-gray-600 text-white placeholder-gray-500',
      container: 'bg-gray-900',
      emptyIcon: 'text-gray-600',
      emptyText: 'text-gray-500',
      contextMenu: 'bg-gray-800 border-gray-700',
      contextMenuItem: 'text-gray-300 hover:bg-gray-700'
    }
  }
  return {
    toolbar: 'border-gray-200 bg-gray-50',
    toolbarBtn: 'text-gray-500 hover:bg-gray-200 hover:text-gray-700',
    searchInput: 'bg-white border-gray-300 text-gray-900 placeholder-gray-400',
    container: 'bg-white',
    emptyIcon: 'text-gray-300',
    emptyText: 'text-gray-500',
    contextMenu: 'bg-white border-gray-200',
    contextMenuItem: 'text-gray-700 hover:bg-gray-100'
  }
})

// Expand folders containing search results
watch(searchQuery, (query) => {
  if (query) {
    expandAll()
  }
})

onMounted(() => {
  document.addEventListener('click', closeContextMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', closeContextMenu)
})

defineExpose({
  expandAll,
  collapseAll,
  getSelected: () => selectedId.value
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
