<template>
  <div :class="['keyboard-shortcuts rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg">
          <CommandLineIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ totalShortcuts }} shortcuts available
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search shortcuts..."
            class="pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white placeholder-gray-400 w-64"
          />
        </div>
      </div>
    </div>

    <!-- Platform Toggle -->
    <div class="flex items-center justify-center gap-2 p-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
      <button
        v-for="platform in platforms"
        :key="platform.id"
        type="button"
        :class="[
          'flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
          activePlatform === platform.id
            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
        ]"
        @click="activePlatform = platform.id"
      >
        <component :is="platform.icon" class="w-4 h-4" />
        {{ platform.label }}
      </button>
    </div>

    <!-- Shortcuts List -->
    <div class="overflow-auto" :style="{ maxHeight: height }">
      <div
        v-for="category in filteredCategories"
        :key="category.name"
        class="border-b border-gray-200 dark:border-gray-700 last:border-0"
      >
        <!-- Category Header -->
        <button
          type="button"
          class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          @click="toggleCategory(category.name)"
        >
          <div class="flex items-center gap-3">
            <component :is="category.icon" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
            <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
            <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">
              {{ category.shortcuts.length }}
            </span>
          </div>
          <ChevronDownIcon
            :class="[
              'w-5 h-5 text-gray-400 transition-transform',
              expandedCategories.includes(category.name) ? 'rotate-180' : ''
            ]"
          />
        </button>

        <!-- Shortcuts -->
        <Transition name="expand">
          <div v-if="expandedCategories.includes(category.name)">
            <div
              v-for="shortcut in category.shortcuts"
              :key="shortcut.id"
              class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group"
            >
              <div class="flex items-center gap-3">
                <component
                  v-if="shortcut.icon"
                  :is="shortcut.icon"
                  class="w-4 h-4 text-gray-400 dark:text-gray-500"
                />
                <div>
                  <span
                    class="text-sm text-gray-700 dark:text-gray-300"
                    v-html="highlightMatch(shortcut.label)"
                  />
                  <p v-if="shortcut.description" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    {{ shortcut.description }}
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-1">
                <kbd
                  v-for="(key, idx) in getKeys(shortcut)"
                  :key="idx"
                  :class="[
                    'inline-flex items-center justify-center min-w-[28px] h-7 px-2 text-xs font-medium rounded border shadow-sm',
                    'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600'
                  ]"
                >
                  {{ formatKey(key) }}
                </kbd>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Empty State -->
      <div v-if="filteredCategories.length === 0" class="py-12 text-center">
        <MagnifyingGlassIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
        <p class="text-gray-500 dark:text-gray-400 font-medium">No shortcuts found</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Try a different search term</p>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showFooter" class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
      <div class="flex items-center gap-4">
        <span class="flex items-center gap-1">
          Press
          <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded shadow-sm">?</kbd>
          to toggle shortcuts
        </span>
      </div>
      <button
        type="button"
        class="text-blue-600 dark:text-blue-400 hover:underline"
        @click="printShortcuts"
      >
        Print Cheatsheet
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  CommandLineIcon,
  MagnifyingGlassIcon,
  ChevronDownIcon,
  DocumentIcon,
  FolderIcon,
  PencilIcon,
  ArrowsPointingOutIcon,
  MagnifyingGlassPlusIcon,
  PlayIcon,
  Cog6ToothIcon,
  WindowIcon,
  ComputerDesktopIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'Keyboard Shortcuts' },
  shortcuts: { type: Array, default: () => [] },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true }
})

const emit = defineEmits(['print'])

const searchQuery = ref('')
const activePlatform = ref('mac')
const expandedCategories = ref(['General', 'Navigation', 'Editing'])

const platforms = [
  { id: 'mac', label: 'Mac', icon: ComputerDesktopIcon },
  { id: 'windows', label: 'Windows', icon: WindowIcon }
]

// Default shortcuts if none provided
const defaultShortcuts = [
  // General
  { id: 1, category: 'General', label: 'Command Palette', mac: ['⌘', 'K'], windows: ['Ctrl', 'K'], icon: CommandLineIcon },
  { id: 2, category: 'General', label: 'Quick Search', mac: ['⌘', 'P'], windows: ['Ctrl', 'P'], icon: MagnifyingGlassIcon },
  { id: 3, category: 'General', label: 'Settings', mac: ['⌘', ','], windows: ['Ctrl', ','], icon: Cog6ToothIcon },
  { id: 4, category: 'General', label: 'Toggle Sidebar', mac: ['⌘', 'B'], windows: ['Ctrl', 'B'] },
  { id: 5, category: 'General', label: 'Toggle Fullscreen', mac: ['⌃', '⌘', 'F'], windows: ['F11'], icon: ArrowsPointingOutIcon },

  // Navigation
  { id: 6, category: 'Navigation', label: 'Go to File', mac: ['⌘', 'P'], windows: ['Ctrl', 'P'], icon: DocumentIcon },
  { id: 7, category: 'Navigation', label: 'Go to Line', mac: ['⌃', 'G'], windows: ['Ctrl', 'G'] },
  { id: 8, category: 'Navigation', label: 'Go to Definition', mac: ['F12'], windows: ['F12'] },
  { id: 9, category: 'Navigation', label: 'Go Back', mac: ['⌃', '-'], windows: ['Alt', '←'] },
  { id: 10, category: 'Navigation', label: 'Go Forward', mac: ['⌃', '⇧', '-'], windows: ['Alt', '→'] },
  { id: 11, category: 'Navigation', label: 'Switch Tab', mac: ['⌘', 'Tab'], windows: ['Ctrl', 'Tab'] },

  // Editing
  { id: 12, category: 'Editing', label: 'Cut Line', mac: ['⌘', 'X'], windows: ['Ctrl', 'X'], icon: PencilIcon },
  { id: 13, category: 'Editing', label: 'Copy Line', mac: ['⌘', 'C'], windows: ['Ctrl', 'C'] },
  { id: 14, category: 'Editing', label: 'Delete Line', mac: ['⌘', '⇧', 'K'], windows: ['Ctrl', 'Shift', 'K'] },
  { id: 15, category: 'Editing', label: 'Move Line Up', mac: ['⌥', '↑'], windows: ['Alt', '↑'] },
  { id: 16, category: 'Editing', label: 'Move Line Down', mac: ['⌥', '↓'], windows: ['Alt', '↓'] },
  { id: 17, category: 'Editing', label: 'Duplicate Line', mac: ['⌥', '⇧', '↓'], windows: ['Alt', 'Shift', '↓'] },
  { id: 18, category: 'Editing', label: 'Undo', mac: ['⌘', 'Z'], windows: ['Ctrl', 'Z'] },
  { id: 19, category: 'Editing', label: 'Redo', mac: ['⌘', '⇧', 'Z'], windows: ['Ctrl', 'Y'] },

  // Selection
  { id: 20, category: 'Selection', label: 'Select All', mac: ['⌘', 'A'], windows: ['Ctrl', 'A'] },
  { id: 21, category: 'Selection', label: 'Select Word', mac: ['⌘', 'D'], windows: ['Ctrl', 'D'] },
  { id: 22, category: 'Selection', label: 'Select Line', mac: ['⌘', 'L'], windows: ['Ctrl', 'L'] },
  { id: 23, category: 'Selection', label: 'Multi-cursor', mac: ['⌥', 'Click'], windows: ['Alt', 'Click'] },
  { id: 24, category: 'Selection', label: 'Column Select', mac: ['⌥', '⇧', 'Drag'], windows: ['Alt', 'Shift', 'Drag'] },

  // Search & Replace
  { id: 25, category: 'Search & Replace', label: 'Find', mac: ['⌘', 'F'], windows: ['Ctrl', 'F'], icon: MagnifyingGlassPlusIcon },
  { id: 26, category: 'Search & Replace', label: 'Replace', mac: ['⌘', 'H'], windows: ['Ctrl', 'H'] },
  { id: 27, category: 'Search & Replace', label: 'Find in Files', mac: ['⌘', '⇧', 'F'], windows: ['Ctrl', 'Shift', 'F'] },
  { id: 28, category: 'Search & Replace', label: 'Find Next', mac: ['⌘', 'G'], windows: ['F3'] },
  { id: 29, category: 'Search & Replace', label: 'Find Previous', mac: ['⌘', '⇧', 'G'], windows: ['Shift', 'F3'] },

  // File Operations
  { id: 30, category: 'File Operations', label: 'New File', mac: ['⌘', 'N'], windows: ['Ctrl', 'N'], icon: FolderIcon },
  { id: 31, category: 'File Operations', label: 'Save', mac: ['⌘', 'S'], windows: ['Ctrl', 'S'] },
  { id: 32, category: 'File Operations', label: 'Save As', mac: ['⌘', '⇧', 'S'], windows: ['Ctrl', 'Shift', 'S'] },
  { id: 33, category: 'File Operations', label: 'Close Tab', mac: ['⌘', 'W'], windows: ['Ctrl', 'W'] },
  { id: 34, category: 'File Operations', label: 'Reopen Closed', mac: ['⌘', '⇧', 'T'], windows: ['Ctrl', 'Shift', 'T'] },

  // Debug
  { id: 35, category: 'Debug', label: 'Start/Continue', mac: ['F5'], windows: ['F5'], icon: PlayIcon },
  { id: 36, category: 'Debug', label: 'Step Over', mac: ['F10'], windows: ['F10'] },
  { id: 37, category: 'Debug', label: 'Step Into', mac: ['F11'], windows: ['F11'] },
  { id: 38, category: 'Debug', label: 'Step Out', mac: ['⇧', 'F11'], windows: ['Shift', 'F11'] },
  { id: 39, category: 'Debug', label: 'Toggle Breakpoint', mac: ['F9'], windows: ['F9'] }
]

const allShortcuts = computed(() => 
  props.shortcuts.length > 0 ? props.shortcuts : defaultShortcuts
)

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const totalShortcuts = computed(() => allShortcuts.value.length)

const getCategoryIcon = (name) => {
  const icons = {
    'General': CommandLineIcon,
    'Navigation': FolderIcon,
    'Editing': PencilIcon,
    'Selection': ArrowsPointingOutIcon,
    'Search & Replace': MagnifyingGlassPlusIcon,
    'File Operations': DocumentIcon,
    'Debug': PlayIcon
  }
  return icons[name] || CommandLineIcon
}

const filteredCategories = computed(() => {
  // Group shortcuts by category
  const grouped = {}
  
  allShortcuts.value.forEach(shortcut => {
    // Filter by search
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      if (!shortcut.label.toLowerCase().includes(query) &&
          !shortcut.category.toLowerCase().includes(query)) {
        return
      }
    }

    if (!grouped[shortcut.category]) {
      grouped[shortcut.category] = {
        name: shortcut.category,
        icon: getCategoryIcon(shortcut.category),
        shortcuts: []
      }
    }
    grouped[shortcut.category].shortcuts.push(shortcut)
  })

  return Object.values(grouped)
})

const getKeys = (shortcut) => {
  return activePlatform.value === 'mac' ? shortcut.mac : shortcut.windows
}

const formatKey = (key) => {
  const keyMap = {
    'Ctrl': '⌃',
    'Alt': '⌥',
    'Shift': '⇧',
    'Enter': '↵',
    'Backspace': '⌫',
    'Delete': '⌦',
    'Escape': 'Esc',
    'ArrowUp': '↑',
    'ArrowDown': '↓',
    'ArrowLeft': '←',
    'ArrowRight': '→',
    '↑': '↑',
    '↓': '↓',
    '←': '←',
    '→': '→'
  }
  return activePlatform.value === 'windows' ? key : (keyMap[key] || key)
}

const highlightMatch = (text) => {
  if (!searchQuery.value) return text
  const regex = new RegExp(`(${searchQuery.value})`, 'gi')
  return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-500/30 rounded px-0.5">$1</mark>')
}

const toggleCategory = (name) => {
  const index = expandedCategories.value.indexOf(name)
  if (index > -1) {
    expandedCategories.value.splice(index, 1)
  } else {
    expandedCategories.value.push(name)
  }
}

const printShortcuts = () => {
  emit('print', allShortcuts.value)
  window.print()
}
</script>

<style scoped>
.expand-enter-active,
.expand-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 1000px;
}

@media print {
  .keyboard-shortcuts {
    border: none;
    box-shadow: none;
  }
}
</style>
