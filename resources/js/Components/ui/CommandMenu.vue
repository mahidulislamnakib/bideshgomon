<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh]"
        @click.self="close"
        @keydown="handleKeydown"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close" />

        <!-- Command Menu -->
        <div
          ref="menuRef"
          :class="[
            'relative w-full max-w-xl rounded-xl shadow-2xl overflow-hidden',
            themeClasses.container
          ]"
        >
          <!-- Search Input -->
          <div :class="['flex items-center gap-3 px-4 py-3 border-b', themeClasses.inputWrapper]">
            <MagnifyingGlassIcon :class="['w-5 h-5 flex-shrink-0', themeClasses.searchIcon]" />
            <input
              ref="inputRef"
              v-model="query"
              type="text"
              :placeholder="placeholder"
              :class="[
                'flex-1 bg-transparent border-none outline-none text-base',
                themeClasses.input
              ]"
              autocomplete="off"
              spellcheck="false"
            />
            <kbd
              v-if="showEscHint"
              :class="['text-xs px-1.5 py-0.5 rounded', themeClasses.kbd]"
            >
              ESC
            </kbd>
          </div>

          <!-- Results -->
          <div
            :class="['overflow-y-auto overscroll-contain', themeClasses.results]"
            :style="{ maxHeight: `${maxHeight}px` }"
          >
            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-8">
              <div class="animate-spin rounded-full h-6 w-6 border-2 border-current border-t-transparent" />
            </div>

            <!-- Groups -->
            <template v-else-if="groupedResults.length">
              <div
                v-for="(group, groupIndex) in groupedResults"
                :key="group.name"
                class="py-1"
              >
                <!-- Group Header -->
                <div
                  v-if="group.name"
                  :class="['px-4 py-2 text-xs font-semibold uppercase tracking-wider', themeClasses.groupHeader]"
                >
                  {{ group.name }}
                </div>

                <!-- Items -->
                <button
                  v-for="(item, itemIndex) in group.items"
                  :key="item.id"
                  :ref="(el) => setItemRef(el, groupIndex, itemIndex)"
                  :class="[
                    'w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors',
                    themeClasses.item,
                    isSelected(groupIndex, itemIndex) && themeClasses.itemSelected
                  ]"
                  @click="selectItem(item)"
                  @mouseenter="setSelected(groupIndex, itemIndex)"
                >
                  <!-- Icon -->
                  <div
                    v-if="item.icon"
                    :class="[
                      'flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center',
                      item.iconBg || themeClasses.iconBg
                    ]"
                  >
                    <component
                      :is="item.icon"
                      :class="['w-4 h-4', item.iconColor || themeClasses.iconColor]"
                    />
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <div :class="['text-sm font-medium', themeClasses.itemTitle]">
                      <span v-html="highlightMatch(item.title)" />
                    </div>
                    <div
                      v-if="item.description"
                      :class="['text-xs truncate mt-0.5', themeClasses.itemDescription]"
                    >
                      {{ item.description }}
                    </div>
                  </div>

                  <!-- Shortcut -->
                  <div v-if="item.shortcut" class="flex-shrink-0 flex items-center gap-1">
                    <kbd
                      v-for="(key, i) in parseShortcut(item.shortcut)"
                      :key="i"
                      :class="['text-xs px-1.5 py-0.5 rounded', themeClasses.kbd]"
                    >
                      {{ key }}
                    </kbd>
                  </div>

                  <!-- Badge -->
                  <span
                    v-if="item.badge"
                    :class="[
                      'flex-shrink-0 text-xs px-2 py-0.5 rounded-full',
                      item.badgeColor || themeClasses.badge
                    ]"
                  >
                    {{ item.badge }}
                  </span>
                </button>
              </div>
            </template>

            <!-- No Results -->
            <div v-else-if="query" class="py-8 text-center">
              <MagnifyingGlassIcon :class="['w-8 h-8 mx-auto mb-2', themeClasses.emptyIcon]" />
              <p :class="['text-sm', themeClasses.emptyText]">
                No results for "{{ query }}"
              </p>
            </div>

            <!-- Empty State -->
            <div v-else class="py-8 text-center">
              <CommandLineIcon :class="['w-8 h-8 mx-auto mb-2', themeClasses.emptyIcon]" />
              <p :class="['text-sm', themeClasses.emptyText]">
                Start typing to search...
              </p>
            </div>
          </div>

          <!-- Footer -->
          <div
            v-if="showFooter"
            :class="['flex items-center justify-between px-4 py-2 border-t text-xs', themeClasses.footer]"
          >
            <div class="flex items-center gap-4">
              <span class="flex items-center gap-1">
                <kbd :class="['px-1 py-0.5 rounded', themeClasses.kbd]">↑↓</kbd>
                <span :class="themeClasses.footerText">Navigate</span>
              </span>
              <span class="flex items-center gap-1">
                <kbd :class="['px-1 py-0.5 rounded', themeClasses.kbd]">↵</kbd>
                <span :class="themeClasses.footerText">Select</span>
              </span>
              <span class="flex items-center gap-1">
                <kbd :class="['px-1 py-0.5 rounded', themeClasses.kbd]">esc</kbd>
                <span :class="themeClasses.footerText">Close</span>
              </span>
            </div>
            <div v-if="totalResults > 0" :class="themeClasses.footerText">
              {{ totalResults }} result{{ totalResults !== 1 ? 's' : '' }}
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import {
  MagnifyingGlassIcon,
  CommandLineIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  items: {
    type: Array,
    default: () => []
    // [{ id, title, description, icon, shortcut, group, badge, action }]
  },
  groups: {
    type: Array,
    default: () => []
    // [{ name: 'Actions', priority: 1 }]
  },
  placeholder: {
    type: String,
    default: 'Type a command or search...'
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  maxHeight: {
    type: Number,
    default: 320
  },
  showFooter: {
    type: Boolean,
    default: true
  },
  showEscHint: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  hotkey: {
    type: String,
    default: 'k'
  },
  hotkeyModifier: {
    type: String,
    default: 'ctrl',
    validator: v => ['ctrl', 'meta', 'alt'].includes(v)
  },
  filterFn: {
    type: Function,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'select', 'search'])

const inputRef = ref(null)
const menuRef = ref(null)
const query = ref('')
const selectedGroup = ref(0)
const selectedItem = ref(0)
const itemRefs = ref({})

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const filteredItems = computed(() => {
  if (!query.value) return props.items
  
  if (props.filterFn) {
    return props.filterFn(props.items, query.value)
  }
  
  const q = query.value.toLowerCase()
  return props.items.filter(item => {
    const title = item.title?.toLowerCase() || ''
    const description = item.description?.toLowerCase() || ''
    return title.includes(q) || description.includes(q)
  })
})

const groupedResults = computed(() => {
  const groups = new Map()
  
  // Initialize groups from props
  props.groups.forEach(g => {
    groups.set(g.name, { ...g, items: [] })
  })
  
  // Add items to groups
  filteredItems.value.forEach(item => {
    const groupName = item.group || ''
    if (!groups.has(groupName)) {
      groups.set(groupName, { name: groupName, priority: 99, items: [] })
    }
    groups.get(groupName).items.push(item)
  })
  
  // Sort by priority and filter empty groups
  return Array.from(groups.values())
    .filter(g => g.items.length > 0)
    .sort((a, b) => (a.priority || 99) - (b.priority || 99))
})

const totalResults = computed(() => filteredItems.value.length)

const setItemRef = (el, groupIndex, itemIndex) => {
  if (el) {
    itemRefs.value[`${groupIndex}-${itemIndex}`] = el
  }
}

const isSelected = (groupIndex, itemIndex) => {
  return selectedGroup.value === groupIndex && selectedItem.value === itemIndex
}

const setSelected = (groupIndex, itemIndex) => {
  selectedGroup.value = groupIndex
  selectedItem.value = itemIndex
}

const open = () => {
  isOpen.value = true
}

const close = () => {
  isOpen.value = false
  query.value = ''
  selectedGroup.value = 0
  selectedItem.value = 0
}

const selectItem = (item) => {
  emit('select', item)
  if (item.action) {
    item.action(item)
  }
  close()
}

const selectCurrentItem = () => {
  const group = groupedResults.value[selectedGroup.value]
  if (group) {
    const item = group.items[selectedItem.value]
    if (item) {
      selectItem(item)
    }
  }
}

const navigateUp = () => {
  if (selectedItem.value > 0) {
    selectedItem.value--
  } else if (selectedGroup.value > 0) {
    selectedGroup.value--
    const prevGroup = groupedResults.value[selectedGroup.value]
    selectedItem.value = prevGroup ? prevGroup.items.length - 1 : 0
  }
  scrollToSelected()
}

const navigateDown = () => {
  const currentGroup = groupedResults.value[selectedGroup.value]
  if (!currentGroup) return
  
  if (selectedItem.value < currentGroup.items.length - 1) {
    selectedItem.value++
  } else if (selectedGroup.value < groupedResults.value.length - 1) {
    selectedGroup.value++
    selectedItem.value = 0
  }
  scrollToSelected()
}

const scrollToSelected = () => {
  nextTick(() => {
    const el = itemRefs.value[`${selectedGroup.value}-${selectedItem.value}`]
    if (el) {
      el.scrollIntoView({ block: 'nearest' })
    }
  })
}

const handleKeydown = (e) => {
  switch (e.key) {
    case 'ArrowUp':
      e.preventDefault()
      navigateUp()
      break
    case 'ArrowDown':
      e.preventDefault()
      navigateDown()
      break
    case 'Enter':
      e.preventDefault()
      selectCurrentItem()
      break
    case 'Escape':
      e.preventDefault()
      close()
      break
  }
}

const handleGlobalKeydown = (e) => {
  const modifier = props.hotkeyModifier === 'meta' ? e.metaKey : 
                   props.hotkeyModifier === 'alt' ? e.altKey : e.ctrlKey
  
  if (modifier && e.key.toLowerCase() === props.hotkey.toLowerCase()) {
    e.preventDefault()
    if (isOpen.value) {
      close()
    } else {
      open()
    }
  }
}

const highlightMatch = (text) => {
  if (!query.value || !text) return text
  
  const regex = new RegExp(`(${query.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi')
  return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 rounded px-0.5">$1</mark>')
}

const parseShortcut = (shortcut) => {
  return shortcut.split('+').map(key => {
    const keyMap = {
      'ctrl': '⌃',
      'cmd': '⌘',
      'meta': '⌘',
      'alt': '⌥',
      'shift': '⇧',
      'enter': '↵',
      'backspace': '⌫',
      'delete': '⌦',
      'escape': 'esc',
      'space': '␣',
      'up': '↑',
      'down': '↓',
      'left': '←',
      'right': '→'
    }
    return keyMap[key.toLowerCase()] || key.toUpperCase()
  })
}

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      container: 'bg-gray-900 border border-gray-700',
      inputWrapper: 'border-gray-700',
      searchIcon: 'text-gray-500',
      input: 'text-white placeholder-gray-500',
      kbd: 'bg-gray-800 text-gray-400 border border-gray-700',
      results: 'bg-gray-900',
      groupHeader: 'text-gray-500',
      item: 'text-gray-300 hover:bg-gray-800',
      itemSelected: 'bg-gray-800',
      itemTitle: 'text-gray-100',
      itemDescription: 'text-gray-500',
      iconBg: 'bg-gray-800',
      iconColor: 'text-gray-400',
      badge: 'bg-gray-700 text-gray-300',
      emptyIcon: 'text-gray-600',
      emptyText: 'text-gray-500',
      footer: 'border-gray-700 bg-gray-800/50',
      footerText: 'text-gray-500'
    }
  }
  return {
    container: 'bg-white border border-gray-200',
    inputWrapper: 'border-gray-200',
    searchIcon: 'text-gray-400',
    input: 'text-gray-900 placeholder-gray-400',
    kbd: 'bg-gray-100 text-gray-500 border border-gray-200',
    results: 'bg-white',
    groupHeader: 'text-gray-400',
    item: 'text-gray-700 hover:bg-gray-50',
    itemSelected: 'bg-gray-100',
    itemTitle: 'text-gray-900',
    itemDescription: 'text-gray-500',
    iconBg: 'bg-gray-100',
    iconColor: 'text-gray-500',
    badge: 'bg-gray-100 text-gray-600',
    emptyIcon: 'text-gray-300',
    emptyText: 'text-gray-500',
    footer: 'border-gray-200 bg-gray-50',
    footerText: 'text-gray-500'
  }
})

// Reset selection when query changes
watch(query, () => {
  selectedGroup.value = 0
  selectedItem.value = 0
  emit('search', query.value)
})

// Focus input when opened
watch(isOpen, (open) => {
  if (open) {
    nextTick(() => {
      inputRef.value?.focus()
    })
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleGlobalKeydown)
})

defineExpose({
  open,
  close,
  focus: () => inputRef.value?.focus()
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
