<script setup>
/**
 * KeyboardShortcutsModal Component
 * Displays available keyboard shortcuts in a modal dialog
 *
 * Usage:
 * <KeyboardShortcutsModal
 *   v-model:show="showHelp"
 *   :shortcuts="pageShortcuts"
 *   :global-shortcuts="globalShortcuts"
 * />
 */

import { computed, watch } from 'vue'
import { XMarkIcon, CommandLineIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  shortcuts: {
    type: Array,
    default: () => [],
  },
  globalShortcuts: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:show'])

const close = () => {
  emit('update:show', false)
}

// Close on escape
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

const isMac = computed(() => {
  return typeof navigator !== 'undefined' && /Mac|iPod|iPhone|iPad/.test(navigator.platform)
})

const formatKey = (key) => {
  return key
    .replace('ctrl+', isMac.value ? '⌘' : 'Ctrl+')
    .replace('alt+', isMac.value ? '⌥' : 'Alt+')
    .replace('shift+', '⇧')
    .replace('Escape', 'Esc')
    .toUpperCase()
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="close"
      >
        <Transition
          enter-active-class="transition-all duration-200"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-150"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="show"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden"
          >
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-xl">
                  <CommandLineIcon class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Keyboard Shortcuts</h2>
              </div>
              <button
                @click="close"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Content -->
            <div class="px-6 py-4 overflow-y-auto max-h-[60vh]">
              <!-- Page Shortcuts -->
              <div v-if="shortcuts.length > 0" class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                  Page Shortcuts
                </h3>
                <div class="space-y-2">
                  <div
                    v-for="shortcut in shortcuts"
                    :key="shortcut.key"
                    class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                  >
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ shortcut.description }}</span>
                    <div class="flex items-center gap-1">
                      <kbd
                        v-for="(part, index) in formatKey(shortcut.key).split(' ')"
                        :key="index"
                        class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm"
                      >
                        {{ part }}
                      </kbd>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Global Shortcuts -->
              <div v-if="globalShortcuts.length > 0">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                  Global Shortcuts
                </h3>
                <div class="space-y-2">
                  <div
                    v-for="shortcut in globalShortcuts"
                    :key="shortcut.key"
                    class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                  >
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ shortcut.description }}</span>
                    <div class="flex items-center gap-1">
                      <kbd
                        v-for="(part, index) in formatKey(shortcut.key).split(' ')"
                        :key="index"
                        class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm"
                      >
                        {{ part }}
                      </kbd>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Navigation Shortcuts -->
              <div class="mt-6">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                  Navigation
                </h3>
                <div class="grid grid-cols-2 gap-2">
                  <div class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Go to Dashboard</span>
                    <div class="flex gap-1">
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">G</kbd>
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">H</kbd>
                    </div>
                  </div>
                  <div class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Go to Users</span>
                    <div class="flex gap-1">
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">G</kbd>
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">U</kbd>
                    </div>
                  </div>
                  <div class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Go to Jobs</span>
                    <div class="flex gap-1">
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">G</kbd>
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">J</kbd>
                    </div>
                  </div>
                  <div class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Go to Visa</span>
                    <div class="flex gap-1">
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">G</kbd>
                      <kbd class="px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">V</kbd>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
              <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Press <kbd class="px-1.5 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 rounded">?</kbd> anytime to show this help
              </p>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
