<template>
  <Teleport to="body">
    <TransitionRoot appear :show="isOpen" as="template">
      <Dialog as="div" class="relative z-[100]" @close="close">
        <!-- Backdrop -->
        <TransitionChild
          as="template"
          enter="ease-out duration-200"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-150"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <!-- Dialog Panel -->
        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-start justify-center p-4 pt-[15vh]">
            <TransitionChild
              as="template"
              enter="ease-out duration-200"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-150"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel
                class="w-full max-w-xl transform overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow-2xl transition-all"
              >
                <!-- Search Input -->
                <div class="relative">
                  <MagnifyingGlassIcon
                    class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400"
                  />
                  <input
                    ref="searchInput"
                    v-model="query"
                    type="text"
                    class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                    :placeholder="placeholder"
                    @keydown="handleKeydown"
                  />
                  <div class="absolute right-4 top-3 flex items-center gap-1">
                    <kbd class="hidden sm:inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 rounded">
                      ESC
                    </kbd>
                  </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700" />

                <!-- Results -->
                <div
                  v-if="filteredCommands.length > 0 || query"
                  class="max-h-[60vh] scroll-py-2 overflow-y-auto"
                >
                  <!-- No results -->
                  <div v-if="filteredCommands.length === 0 && query" class="py-14 px-6 text-center">
                    <FolderIcon class="mx-auto h-10 w-10 text-gray-400" />
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                      No results found for "{{ query }}"
                    </p>
                  </div>

                  <!-- Grouped Results -->
                  <template v-else>
                    <div
                      v-for="(group, groupIndex) in groupedCommands"
                      :key="group.name"
                      class="py-2"
                    >
                      <!-- Group Header -->
                      <h3
                        v-if="group.name"
                        class="px-4 pb-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        {{ group.name }}
                      </h3>

                      <!-- Group Items -->
                      <ul>
                        <li
                          v-for="(command, index) in group.items"
                          :key="command.id"
                          :ref="el => { if (getGlobalIndex(groupIndex, index) === selectedIndex) selectedRef = el }"
                        >
                          <button
                            type="button"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition-colors"
                            :class="[
                              getGlobalIndex(groupIndex, index) === selectedIndex
                                ? 'bg-primary-50 dark:bg-primary-900/20'
                                : 'hover:bg-gray-50 dark:hover:bg-gray-800'
                            ]"
                            @click="executeCommand(command)"
                            @mouseenter="selectedIndex = getGlobalIndex(groupIndex, index)"
                          >
                            <!-- Icon -->
                            <div
                              :class="[
                                'flex-shrink-0 rounded-lg p-2',
                                getGlobalIndex(groupIndex, index) === selectedIndex
                                  ? 'bg-primary-100 dark:bg-primary-800/30'
                                  : 'bg-gray-100 dark:bg-gray-800'
                              ]"
                            >
                              <component
                                :is="command.icon || CommandLineIcon"
                                :class="[
                                  'h-4 w-4',
                                  getGlobalIndex(groupIndex, index) === selectedIndex
                                    ? 'text-primary-600 dark:text-primary-400'
                                    : 'text-gray-500 dark:text-gray-400'
                                ]"
                              />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                              <p
                                :class="[
                                  'text-sm font-medium truncate',
                                  getGlobalIndex(groupIndex, index) === selectedIndex
                                    ? 'text-primary-900 dark:text-primary-100'
                                    : 'text-gray-900 dark:text-white'
                                ]"
                              >
                                {{ command.name }}
                              </p>
                              <p
                                v-if="command.description"
                                class="text-xs text-gray-500 dark:text-gray-400 truncate"
                              >
                                {{ command.description }}
                              </p>
                            </div>

                            <!-- Shortcut -->
                            <div v-if="command.shortcut" class="flex-shrink-0 flex items-center gap-1">
                              <kbd
                                v-for="(key, keyIndex) in command.shortcut.split('+')"
                                :key="keyIndex"
                                class="px-1.5 py-0.5 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 rounded"
                              >
                                {{ key }}
                              </kbd>
                            </div>

                            <!-- Badge -->
                            <span
                              v-if="command.badge"
                              :class="[
                                'flex-shrink-0 text-xs font-medium px-2 py-0.5 rounded-full',
                                command.badgeColor || 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                              ]"
                            >
                              {{ command.badge }}
                            </span>
                          </button>
                        </li>
                      </ul>
                    </div>
                  </template>
                </div>

                <!-- Recent / Quick Actions -->
                <div v-else class="py-3">
                  <!-- Recent -->
                  <div v-if="recentCommands.length > 0" class="mb-2">
                    <h3 class="px-4 pb-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                      <ClockIcon class="w-3.5 h-3.5" />
                      Recent
                    </h3>
                    <ul>
                      <li v-for="(command, index) in recentCommands.slice(0, 3)" :key="command.id">
                        <button
                          type="button"
                          class="flex w-full items-center gap-3 px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                          :class="[index === selectedIndex ? 'bg-primary-50 dark:bg-primary-900/20' : '']"
                          @click="executeCommand(command)"
                          @mouseenter="selectedIndex = index"
                        >
                          <component
                            :is="command.icon || CommandLineIcon"
                            class="h-4 w-4 text-gray-400"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">{{ command.name }}</span>
                        </button>
                      </li>
                    </ul>
                  </div>

                  <!-- Tip -->
                  <div class="px-4 py-2 text-xs text-gray-400">
                    <span class="font-medium">Tip:</span> Type to search commands, pages, or actions
                  </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-2 flex items-center justify-between text-xs text-gray-400">
                  <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                      <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">↑</kbd>
                      <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">↓</kbd>
                      to navigate
                    </span>
                    <span class="flex items-center gap-1">
                      <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">Enter</kbd>
                      to select
                    </span>
                  </div>
                  <span class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">Esc</kbd>
                    to close
                  </span>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot
} from '@headlessui/vue'
import {
  MagnifyingGlassIcon,
  CommandLineIcon,
  FolderIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Array of commands
  commands: {
    type: Array,
    default: () => []
    // Each command: { id, name, description?, group?, icon?, shortcut?, action?, href?, badge?, badgeColor? }
  },
  // Placeholder text
  placeholder: {
    type: String,
    default: 'Search commands, pages, or actions...'
  },
  // Keyboard shortcut to open (default: Ctrl+K / Cmd+K)
  shortcut: {
    type: String,
    default: 'ctrl+k'
  },
  // Show on mount
  defaultOpen: {
    type: Boolean,
    default: false
  },
  // Max recent commands to track
  maxRecent: {
    type: Number,
    default: 5
  },
  // LocalStorage key for recent commands
  storageKey: {
    type: String,
    default: 'command-palette-recent'
  }
})

const emit = defineEmits(['select', 'open', 'close'])

const isOpen = ref(props.defaultOpen)
const query = ref('')
const selectedIndex = ref(0)
const searchInput = ref(null)
const selectedRef = ref(null)
const recentCommands = ref([])

// Load recent commands from storage
onMounted(() => {
  const stored = localStorage.getItem(props.storageKey)
  if (stored) {
    try {
      const ids = JSON.parse(stored)
      recentCommands.value = ids
        .map(id => props.commands.find(c => c.id === id))
        .filter(Boolean)
    } catch (e) {
      // Ignore parse errors
    }
  }

  // Register global keyboard shortcut
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})

// Filter commands based on query
const filteredCommands = computed(() => {
  if (!query.value) return []

  const q = query.value.toLowerCase()
  return props.commands.filter(cmd => {
    const nameMatch = cmd.name?.toLowerCase().includes(q)
    const descMatch = cmd.description?.toLowerCase().includes(q)
    const groupMatch = cmd.group?.toLowerCase().includes(q)
    return nameMatch || descMatch || groupMatch
  })
})

// Group filtered commands
const groupedCommands = computed(() => {
  const groups = {}
  
  filteredCommands.value.forEach(cmd => {
    const groupName = cmd.group || ''
    if (!groups[groupName]) {
      groups[groupName] = []
    }
    groups[groupName].push(cmd)
  })

  return Object.entries(groups).map(([name, items]) => ({
    name,
    items
  }))
})

// Get global index for keyboard navigation
function getGlobalIndex(groupIndex, itemIndex) {
  let index = 0
  for (let i = 0; i < groupIndex; i++) {
    index += groupedCommands.value[i].items.length
  }
  return index + itemIndex
}

// Total filtered items count
const totalItems = computed(() => filteredCommands.value.length)

// Handle global keyboard shortcut
function handleGlobalKeydown(e) {
  const shortcutParts = props.shortcut.toLowerCase().split('+')
  const key = shortcutParts[shortcutParts.length - 1]
  const needsCtrl = shortcutParts.includes('ctrl') || shortcutParts.includes('cmd')
  const needsShift = shortcutParts.includes('shift')
  const needsAlt = shortcutParts.includes('alt')

  const ctrlPressed = e.ctrlKey || e.metaKey
  const matchesModifiers =
    (needsCtrl ? ctrlPressed : !ctrlPressed || e.key.toLowerCase() === key) &&
    (needsShift ? e.shiftKey : true) &&
    (needsAlt ? e.altKey : true)

  if (matchesModifiers && e.key.toLowerCase() === key) {
    e.preventDefault()
    toggle()
  }
}

// Handle keyboard navigation in dialog
function handleKeydown(e) {
  switch (e.key) {
    case 'ArrowDown':
      e.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, totalItems.value - 1)
      scrollToSelected()
      break
    case 'ArrowUp':
      e.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
      scrollToSelected()
      break
    case 'Enter':
      e.preventDefault()
      const command = filteredCommands.value[selectedIndex.value]
      if (command) {
        executeCommand(command)
      }
      break
    case 'Escape':
      close()
      break
  }
}

function scrollToSelected() {
  nextTick(() => {
    if (selectedRef.value) {
      selectedRef.value.scrollIntoView({ block: 'nearest' })
    }
  })
}

// Execute command
function executeCommand(command) {
  // Add to recent
  addToRecent(command)

  // Emit event
  emit('select', command)

  // Execute action
  if (command.action) {
    command.action()
  } else if (command.href) {
    window.location.href = command.href
  }

  close()
}

// Add command to recent list
function addToRecent(command) {
  const recent = recentCommands.value.filter(c => c.id !== command.id)
  recent.unshift(command)
  recentCommands.value = recent.slice(0, props.maxRecent)

  // Save to storage
  const ids = recentCommands.value.map(c => c.id)
  localStorage.setItem(props.storageKey, JSON.stringify(ids))
}

// Open/Close/Toggle
function open() {
  isOpen.value = true
  query.value = ''
  selectedIndex.value = 0
  emit('open')
  nextTick(() => {
    searchInput.value?.focus()
  })
}

function close() {
  isOpen.value = false
  emit('close')
}

function toggle() {
  if (isOpen.value) {
    close()
  } else {
    open()
  }
}

// Reset selection when query changes
watch(query, () => {
  selectedIndex.value = 0
})

// Expose methods
defineExpose({
  open,
  close,
  toggle
})
</script>
