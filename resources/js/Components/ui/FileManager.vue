<template>
  <div :class="['file-manager rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg">
          <FolderIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ selectedItems.length > 0 ? `${selectedItems.length} selected` : `${files.length} items` }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Upload"
          @click="$refs.fileInput.click()"
        >
          <ArrowUpTrayIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="New Folder"
          @click="createFolder"
        >
          <FolderPlusIcon class="w-5 h-5" />
        </button>
        <div class="w-px h-6 bg-gray-300 dark:bg-gray-600" />
        <button
          type="button"
          :class="[
            'p-2 rounded-lg transition-colors',
            viewMode === 'grid'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'grid'"
        >
          <Squares2X2Icon class="w-5 h-5" />
        </button>
        <button
          type="button"
          :class="[
            'p-2 rounded-lg transition-colors',
            viewMode === 'list'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          @click="viewMode = 'list'"
        >
          <Bars3Icon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Breadcrumb -->
    <div class="flex items-center gap-1 px-4 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
      <button
        type="button"
        class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
        @click="navigateTo([])"
      >
        <HomeIcon class="w-4 h-4" />
      </button>
      <template v-for="(segment, index) in currentPath" :key="index">
        <ChevronRightIcon class="w-4 h-4 text-gray-400" />
        <button
          type="button"
          class="px-2 py-1 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors whitespace-nowrap"
          @click="navigateTo(currentPath.slice(0, index + 1))"
        >
          {{ segment }}
        </button>
      </template>
    </div>

    <!-- Toolbar -->
    <div v-if="selectedItems.length > 0" class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
      <span class="text-sm text-blue-600 dark:text-blue-400">{{ selectedItems.length }} selected</span>
      <div class="flex-1" />
      <button
        type="button"
        class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded transition-colors"
        @click="copySelected"
      >
        Copy
      </button>
      <button
        type="button"
        class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded transition-colors"
        @click="moveSelected"
      >
        Move
      </button>
      <button
        type="button"
        class="px-3 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 rounded transition-colors"
        @click="deleteSelected"
      >
        Delete
      </button>
      <button
        type="button"
        class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
        @click="selectedItems = []"
      >
        Clear
      </button>
    </div>

    <!-- Content -->
    <div class="overflow-auto" :style="{ height }">
      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="p-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
        <div
          v-for="item in currentFiles"
          :key="item.id"
          :class="[
            'group relative p-3 rounded-xl border-2 cursor-pointer transition-all',
            selectedItems.includes(item.id)
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
              : 'border-transparent hover:border-gray-200 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800'
          ]"
          @click="handleItemClick($event, item)"
          @dblclick="handleItemDblClick(item)"
          @contextmenu.prevent="showContextMenu($event, item)"
        >
          <div class="flex flex-col items-center">
            <div class="relative mb-2">
              <component
                :is="getFileIcon(item)"
                :class="['w-12 h-12', getFileIconColor(item)]"
              />
              <input
                v-if="selectedItems.includes(item.id)"
                type="checkbox"
                checked
                class="absolute -top-1 -right-1 w-4 h-4 rounded border-gray-300"
                @click.stop
              />
            </div>
            <span class="text-sm text-gray-700 dark:text-gray-300 text-center truncate w-full">
              {{ item.name }}
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500">
              {{ item.type === 'folder' ? `${item.items || 0} items` : formatSize(item.size) }}
            </span>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="currentFiles.length === 0" class="col-span-full py-12 text-center">
          <FolderIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
          <p class="text-gray-500 dark:text-gray-400">This folder is empty</p>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <div
          v-for="item in currentFiles"
          :key="item.id"
          :class="[
            'flex items-center gap-4 px-4 py-3 cursor-pointer transition-colors',
            selectedItems.includes(item.id)
              ? 'bg-blue-50 dark:bg-blue-900/20'
              : 'hover:bg-gray-50 dark:hover:bg-gray-800'
          ]"
          @click="handleItemClick($event, item)"
          @dblclick="handleItemDblClick(item)"
          @contextmenu.prevent="showContextMenu($event, item)"
        >
          <input
            type="checkbox"
            :checked="selectedItems.includes(item.id)"
            class="w-4 h-4 rounded border-gray-300"
            @click.stop="toggleSelection(item.id)"
          />
          <component
            :is="getFileIcon(item)"
            :class="['w-8 h-8 flex-shrink-0', getFileIconColor(item)]"
          />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ item.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.type === 'folder' ? 'Folder' : item.extension }}</p>
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 w-24 text-right">
            {{ item.type === 'folder' ? `${item.items || 0} items` : formatSize(item.size) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 w-32 text-right">
            {{ formatDate(item.modified) }}
          </div>
          <button
            type="button"
            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 opacity-0 group-hover:opacity-100 transition-opacity"
            @click.stop="showContextMenu($event, item)"
          >
            <EllipsisVerticalIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Empty State -->
        <div v-if="currentFiles.length === 0" class="py-12 text-center">
          <FolderIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
          <p class="text-gray-500 dark:text-gray-400">This folder is empty</p>
        </div>
      </div>
    </div>

    <!-- Context Menu -->
    <div
      v-if="contextMenu.visible"
      class="fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 min-w-48"
      :style="{ left: `${contextMenu.x}px`, top: `${contextMenu.y}px` }"
      @click="contextMenu.visible = false"
    >
      <button
        v-for="action in contextMenuActions"
        :key="action.id"
        type="button"
        :class="[
          'w-full px-4 py-2 text-left text-sm flex items-center gap-2 transition-colors',
          action.danger
            ? 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20'
            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
        ]"
        @click="action.handler(contextMenu.item)"
      >
        <component :is="action.icon" class="w-4 h-4" />
        {{ action.label }}
      </button>
    </div>

    <!-- Hidden File Input -->
    <input
      ref="fileInput"
      type="file"
      multiple
      class="hidden"
      @change="handleFileUpload"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  FolderIcon,
  FolderPlusIcon,
  DocumentIcon,
  DocumentTextIcon,
  PhotoIcon,
  FilmIcon,
  MusicalNoteIcon,
  ArchiveBoxIcon,
  CodeBracketIcon,
  ArrowUpTrayIcon,
  Squares2X2Icon,
  Bars3Icon,
  HomeIcon,
  ChevronRightIcon,
  EllipsisVerticalIcon,
  PencilIcon,
  DocumentDuplicateIcon,
  ArrowDownTrayIcon,
  TrashIcon,
  ArrowRightIcon,
  EyeIcon,
  ShareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'File Manager' },
  initialFiles: { type: Array, default: () => [] },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true }
})

const emit = defineEmits(['upload', 'delete', 'rename', 'move', 'copy', 'download', 'select', 'open'])

const viewMode = ref('grid')
const currentPath = ref([])
const selectedItems = ref([])
const contextMenu = ref({ visible: false, x: 0, y: 0, item: null })

const files = ref(props.initialFiles.length > 0 ? props.initialFiles : [
  { id: 1, name: 'Documents', type: 'folder', items: 12, modified: '2025-12-20' },
  { id: 2, name: 'Images', type: 'folder', items: 45, modified: '2025-12-19' },
  { id: 3, name: 'Videos', type: 'folder', items: 8, modified: '2025-12-18' },
  { id: 4, name: 'report.pdf', type: 'file', extension: 'pdf', size: 2456000, modified: '2025-12-20' },
  { id: 5, name: 'photo.jpg', type: 'file', extension: 'jpg', size: 1234000, modified: '2025-12-19' },
  { id: 6, name: 'video.mp4', type: 'file', extension: 'mp4', size: 45678000, modified: '2025-12-18' },
  { id: 7, name: 'music.mp3', type: 'file', extension: 'mp3', size: 4567000, modified: '2025-12-17' },
  { id: 8, name: 'archive.zip', type: 'file', extension: 'zip', size: 12345000, modified: '2025-12-16' },
  { id: 9, name: 'script.js', type: 'file', extension: 'js', size: 12000, modified: '2025-12-15' }
])

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const currentFiles = computed(() => {
  // In a real app, filter based on currentPath
  return files.value
})

const contextMenuActions = [
  { id: 'open', label: 'Open', icon: EyeIcon, handler: (item) => handleItemDblClick(item) },
  { id: 'rename', label: 'Rename', icon: PencilIcon, handler: (item) => renameItem(item) },
  { id: 'copy', label: 'Copy', icon: DocumentDuplicateIcon, handler: (item) => copyItem(item) },
  { id: 'move', label: 'Move', icon: ArrowRightIcon, handler: (item) => moveItem(item) },
  { id: 'download', label: 'Download', icon: ArrowDownTrayIcon, handler: (item) => downloadItem(item) },
  { id: 'share', label: 'Share', icon: ShareIcon, handler: (item) => shareItem(item) },
  { id: 'delete', label: 'Delete', icon: TrashIcon, handler: (item) => deleteItem(item), danger: true }
]

const getFileIcon = (item) => {
  if (item.type === 'folder') return FolderIcon
  const ext = item.extension?.toLowerCase()
  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) return PhotoIcon
  if (['mp4', 'mov', 'avi', 'mkv', 'webm'].includes(ext)) return FilmIcon
  if (['mp3', 'wav', 'flac', 'ogg'].includes(ext)) return MusicalNoteIcon
  if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return ArchiveBoxIcon
  if (['js', 'ts', 'vue', 'jsx', 'tsx', 'py', 'php', 'html', 'css'].includes(ext)) return CodeBracketIcon
  if (['txt', 'md', 'doc', 'docx', 'pdf'].includes(ext)) return DocumentTextIcon
  return DocumentIcon
}

const getFileIconColor = (item) => {
  if (item.type === 'folder') return 'text-yellow-500'
  const ext = item.extension?.toLowerCase()
  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) return 'text-green-500'
  if (['mp4', 'mov', 'avi', 'mkv', 'webm'].includes(ext)) return 'text-purple-500'
  if (['mp3', 'wav', 'flac', 'ogg'].includes(ext)) return 'text-pink-500'
  if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return 'text-orange-500'
  if (['js', 'ts', 'vue', 'jsx', 'tsx', 'py', 'php', 'html', 'css'].includes(ext)) return 'text-blue-500'
  return 'text-gray-500'
}

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0
  while (bytes >= 1024 && i < units.length - 1) {
    bytes /= 1024
    i++
  }
  return `${bytes.toFixed(1)} ${units[i]}`
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const navigateTo = (path) => {
  currentPath.value = path
  selectedItems.value = []
}

const handleItemClick = (event, item) => {
  if (event.ctrlKey || event.metaKey) {
    toggleSelection(item.id)
  } else if (event.shiftKey && selectedItems.value.length > 0) {
    // Range selection
    const lastSelected = selectedItems.value[selectedItems.value.length - 1]
    const lastIndex = currentFiles.value.findIndex(f => f.id === lastSelected)
    const currentIndex = currentFiles.value.findIndex(f => f.id === item.id)
    const [start, end] = [Math.min(lastIndex, currentIndex), Math.max(lastIndex, currentIndex)]
    const rangeIds = currentFiles.value.slice(start, end + 1).map(f => f.id)
    selectedItems.value = [...new Set([...selectedItems.value, ...rangeIds])]
  } else {
    selectedItems.value = [item.id]
  }
  emit('select', selectedItems.value)
}

const handleItemDblClick = (item) => {
  if (item.type === 'folder') {
    currentPath.value = [...currentPath.value, item.name]
    selectedItems.value = []
  } else {
    emit('open', item)
  }
}

const toggleSelection = (id) => {
  const index = selectedItems.value.indexOf(id)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(id)
  }
}

const showContextMenu = (event, item) => {
  contextMenu.value = {
    visible: true,
    x: event.clientX,
    y: event.clientY,
    item
  }
}

const createFolder = () => {
  const name = prompt('Enter folder name:')
  if (name) {
    files.value.push({
      id: Date.now(),
      name,
      type: 'folder',
      items: 0,
      modified: new Date().toISOString().split('T')[0]
    })
  }
}

const handleFileUpload = (event) => {
  const uploadedFiles = Array.from(event.target.files)
  uploadedFiles.forEach(file => {
    files.value.push({
      id: Date.now() + Math.random(),
      name: file.name,
      type: 'file',
      extension: file.name.split('.').pop(),
      size: file.size,
      modified: new Date().toISOString().split('T')[0]
    })
  })
  emit('upload', uploadedFiles)
}

const renameItem = (item) => {
  const newName = prompt('Enter new name:', item.name)
  if (newName && newName !== item.name) {
    item.name = newName
    emit('rename', { item, newName })
  }
}

const copyItem = (item) => { emit('copy', item) }
const moveItem = (item) => { emit('move', item) }
const downloadItem = (item) => { emit('download', item) }
const shareItem = (item) => { console.log('Share:', item) }

const deleteItem = (item) => {
  if (confirm(`Delete "${item.name}"?`)) {
    files.value = files.value.filter(f => f.id !== item.id)
    emit('delete', item)
  }
}

const copySelected = () => { emit('copy', selectedItems.value) }
const moveSelected = () => { emit('move', selectedItems.value) }
const deleteSelected = () => {
  if (confirm(`Delete ${selectedItems.value.length} items?`)) {
    files.value = files.value.filter(f => !selectedItems.value.includes(f.id))
    emit('delete', selectedItems.value)
    selectedItems.value = []
  }
}

// Close context menu on click outside
document.addEventListener('click', () => {
  contextMenu.value.visible = false
})
</script>
