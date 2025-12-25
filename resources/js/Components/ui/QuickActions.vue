<template>
  <div :class="['quick-actions', containerClasses]">
    <!-- Floating Action Button Style -->
    <div v-if="variant === 'fab'" class="relative">
      <button
        type="button"
        :class="[
          'w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300',
          isOpen ? 'rotate-45 bg-gray-700 dark:bg-gray-600' : 'bg-blue-600 hover:bg-blue-700',
          'text-white'
        ]"
        @click="toggleOpen"
      >
        <PlusIcon class="w-6 h-6" />
      </button>

      <Transition name="fab-actions">
        <div v-if="isOpen" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 space-y-2">
          <div
            v-for="(action, index) in actions"
            :key="action.id"
            :style="{ transitionDelay: `${index * 50}ms` }"
            class="flex items-center gap-3 animate-fade-up"
          >
            <span class="px-2 py-1 text-xs font-medium text-white bg-gray-800 rounded-lg shadow-lg whitespace-nowrap">
              {{ action.label }}
            </span>
            <button
              type="button"
              :class="[
                'w-10 h-10 rounded-full shadow-lg flex items-center justify-center text-white transition-transform hover:scale-110',
                action.color || 'bg-blue-500'
              ]"
              @click="handleAction(action)"
            >
              <component :is="action.icon" class="w-5 h-5" />
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Grid Style -->
    <div v-else-if="variant === 'grid'" class="grid gap-3" :style="{ gridTemplateColumns: `repeat(${columns}, 1fr)` }">
      <button
        v-for="action in actions"
        :key="action.id"
        type="button"
        :class="[
          'flex flex-col items-center justify-center gap-2 p-4 rounded-xl border transition-all duration-200',
          action.disabled
            ? 'opacity-50 cursor-not-allowed'
            : 'hover:shadow-lg hover:-translate-y-1',
          theme === 'dark'
            ? 'bg-gray-800 border-gray-700 hover:border-gray-600'
            : 'bg-white border-gray-200 hover:border-gray-300'
        ]"
        :disabled="action.disabled"
        @click="handleAction(action)"
      >
        <div :class="['p-3 rounded-xl', action.bgColor || 'bg-blue-100 dark:bg-blue-900/30']">
          <component :is="action.icon" :class="['w-6 h-6', action.iconColor || 'text-blue-600 dark:text-blue-400']" />
        </div>
        <span :class="['text-sm font-medium', theme === 'dark' ? 'text-white' : 'text-gray-900']">
          {{ action.label }}
        </span>
        <span v-if="action.description" class="text-xs text-gray-500 dark:text-gray-400 text-center">
          {{ action.description }}
        </span>
        <span
          v-if="action.badge"
          :class="[
            'px-2 py-0.5 text-xs font-medium rounded-full',
            action.badge.color || 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
          ]"
        >
          {{ action.badge.text }}
        </span>
      </button>
    </div>

    <!-- List Style -->
    <div v-else-if="variant === 'list'" class="space-y-1">
      <button
        v-for="action in actions"
        :key="action.id"
        type="button"
        :class="[
          'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors',
          action.disabled
            ? 'opacity-50 cursor-not-allowed'
            : theme === 'dark'
              ? 'hover:bg-gray-800'
              : 'hover:bg-gray-100'
        ]"
        :disabled="action.disabled"
        @click="handleAction(action)"
      >
        <div :class="['p-2 rounded-lg', action.bgColor || 'bg-blue-100 dark:bg-blue-900/30']">
          <component :is="action.icon" :class="['w-5 h-5', action.iconColor || 'text-blue-600 dark:text-blue-400']" />
        </div>
        <div class="flex-1 text-left">
          <p :class="['text-sm font-medium', theme === 'dark' ? 'text-white' : 'text-gray-900']">
            {{ action.label }}
          </p>
          <p v-if="action.description" class="text-xs text-gray-500 dark:text-gray-400">
            {{ action.description }}
          </p>
        </div>
        <span
          v-if="action.badge"
          :class="[
            'px-2 py-0.5 text-xs font-medium rounded-full',
            action.badge.color || 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
          ]"
        >
          {{ action.badge.text }}
        </span>
        <span v-if="action.shortcut" class="text-xs text-gray-400 dark:text-gray-500">
          {{ action.shortcut }}
        </span>
        <ChevronRightIcon v-if="action.hasSubmenu" class="w-4 h-4 text-gray-400" />
      </button>
    </div>

    <!-- Toolbar Style -->
    <div v-else-if="variant === 'toolbar'" class="flex items-center gap-1 p-1 rounded-xl bg-gray-100 dark:bg-gray-800">
      <button
        v-for="action in actions"
        :key="action.id"
        type="button"
        :class="[
          'relative flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
          action.active
            ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm'
            : action.disabled
              ? 'opacity-50 cursor-not-allowed text-gray-400'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-700/50'
        ]"
        :disabled="action.disabled"
        :title="action.label"
        @click="handleAction(action)"
      >
        <component :is="action.icon" class="w-4 h-4" />
        <span v-if="!iconOnly">{{ action.label }}</span>
        <span
          v-if="action.badge"
          class="absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center text-xs font-bold text-white bg-red-500 rounded-full"
        >
          {{ action.badge.text }}
        </span>
      </button>
    </div>

    <!-- Dropdown Style -->
    <div v-else class="relative">
      <button
        type="button"
        :class="[
          'flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors',
          theme === 'dark'
            ? 'bg-gray-800 text-white hover:bg-gray-700'
            : 'bg-white text-gray-900 hover:bg-gray-50 border border-gray-200'
        ]"
        @click="toggleOpen"
      >
        <Squares2X2Icon class="w-5 h-5" />
        <span>{{ triggerLabel }}</span>
        <ChevronDownIcon :class="['w-4 h-4 transition-transform', isOpen ? 'rotate-180' : '']" />
      </button>

      <Transition name="dropdown">
        <div
          v-if="isOpen"
          :class="[
            'absolute top-full left-0 mt-2 w-64 rounded-xl shadow-xl border overflow-hidden z-50',
            theme === 'dark' ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
          ]"
        >
          <div class="p-1">
            <button
              v-for="action in actions"
              :key="action.id"
              type="button"
              :class="[
                'w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-left',
                action.disabled
                  ? 'opacity-50 cursor-not-allowed'
                  : theme === 'dark'
                    ? 'hover:bg-gray-700'
                    : 'hover:bg-gray-100'
              ]"
              :disabled="action.disabled"
              @click="handleAction(action)"
            >
              <component :is="action.icon" :class="['w-5 h-5', action.iconColor || 'text-gray-500 dark:text-gray-400']" />
              <span :class="['text-sm', theme === 'dark' ? 'text-white' : 'text-gray-900']">{{ action.label }}</span>
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Backdrop for dropdown/fab -->
    <div
      v-if="isOpen && (variant === 'dropdown' || variant === 'fab')"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  PlusIcon,
  ChevronRightIcon,
  ChevronDownIcon,
  Squares2X2Icon,
  DocumentPlusIcon,
  FolderPlusIcon,
  ArrowUpTrayIcon,
  UserPlusIcon,
  PencilSquareIcon,
  TrashIcon,
  ShareIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  actions: { type: Array, default: () => [] },
  variant: { type: String, default: 'grid' }, // grid, list, toolbar, fab, dropdown
  columns: { type: Number, default: 3 },
  theme: { type: String, default: 'light' },
  iconOnly: { type: Boolean, default: false },
  triggerLabel: { type: String, default: 'Quick Actions' }
})

const emit = defineEmits(['action'])

const isOpen = ref(false)

const containerClasses = computed(() => {
  if (props.variant === 'fab') return 'fixed bottom-6 right-6 z-50'
  return ''
})

const defaultActions = [
  { id: 1, label: 'New Document', icon: DocumentPlusIcon, bgColor: 'bg-blue-100 dark:bg-blue-900/30', iconColor: 'text-blue-600 dark:text-blue-400' },
  { id: 2, label: 'New Folder', icon: FolderPlusIcon, bgColor: 'bg-yellow-100 dark:bg-yellow-900/30', iconColor: 'text-yellow-600 dark:text-yellow-400' },
  { id: 3, label: 'Upload File', icon: ArrowUpTrayIcon, bgColor: 'bg-green-100 dark:bg-green-900/30', iconColor: 'text-green-600 dark:text-green-400' },
  { id: 4, label: 'Invite User', icon: UserPlusIcon, bgColor: 'bg-purple-100 dark:bg-purple-900/30', iconColor: 'text-purple-600 dark:text-purple-400' },
  { id: 5, label: 'Edit', icon: PencilSquareIcon, bgColor: 'bg-indigo-100 dark:bg-indigo-900/30', iconColor: 'text-indigo-600 dark:text-indigo-400' },
  { id: 6, label: 'Share', icon: ShareIcon, bgColor: 'bg-pink-100 dark:bg-pink-900/30', iconColor: 'text-pink-600 dark:text-pink-400' }
]

const allActions = computed(() => 
  props.actions.length > 0 ? props.actions : defaultActions
)

const actions = computed(() => allActions.value)

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

const handleAction = (action) => {
  if (action.disabled) return
  emit('action', action)
  if (action.handler) action.handler()
  if (props.variant === 'fab' || props.variant === 'dropdown') {
    isOpen.value = false
  }
}
</script>

<style scoped>
.fab-actions-enter-active,
.fab-actions-leave-active {
  transition: all 0.3s ease;
}

.fab-actions-enter-from,
.fab-actions-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.animate-fade-up {
  animation: fadeUp 0.3s ease forwards;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
