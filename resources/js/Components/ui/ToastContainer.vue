<template>
  <Teleport to="body">
    <div
      :class="[
        'fixed z-[100] flex flex-col gap-3 pointer-events-none',
        positionClasses
      ]"
      aria-live="polite"
    >
      <TransitionGroup
        :name="transitionName"
        tag="div"
        class="flex flex-col gap-3"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto"
        >
          <div
            :class="[
              'relative flex items-start gap-3 w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5',
              'transform transition-all duration-300',
              variantClasses(toast.type).container
            ]"
            role="alert"
          >
            <!-- Icon -->
            <div class="flex-shrink-0 pt-3.5 pl-3.5">
              <component
                :is="getIcon(toast.type)"
                :class="['w-5 h-5', variantClasses(toast.type).icon]"
              />
            </div>

            <!-- Content -->
            <div class="flex-1 py-3 pr-3">
              <p
                v-if="toast.title"
                :class="['text-sm font-semibold', variantClasses(toast.type).title]"
              >
                {{ toast.title }}
              </p>
              <p :class="['text-sm', variantClasses(toast.type).message, toast.title ? 'mt-1' : '']">
                {{ toast.message }}
              </p>

              <!-- Actions -->
              <div v-if="toast.actions?.length" class="mt-3 flex items-center gap-3">
                <button
                  v-for="(action, index) in toast.actions"
                  :key="index"
                  type="button"
                  :class="[
                    'text-sm font-medium transition-colors',
                    index === 0 ? variantClasses(toast.type).primaryAction : variantClasses(toast.type).secondaryAction
                  ]"
                  @click="handleAction(toast, action)"
                >
                  {{ action.label }}
                </button>
              </div>
            </div>

            <!-- Close Button -->
            <button
              type="button"
              class="flex-shrink-0 p-2 rounded-lg transition-colors"
              :class="variantClasses(toast.type).close"
              @click="removeToast(toast.id)"
              aria-label="Dismiss"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>

            <!-- Progress Bar -->
            <div
              v-if="toast.duration && toast.showProgress"
              class="absolute bottom-0 left-0 right-0 h-1 overflow-hidden"
            >
              <div
                class="h-full transition-all ease-linear"
                :class="variantClasses(toast.type).progress"
                :style="{ width: `${toast.progress || 100}%` }"
              />
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/20/solid'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XCircleIcon,
  BellIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Position on screen
  position: {
    type: String,
    default: 'top-right',
    validator: (v) => ['top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right'].includes(v)
  },
  // Default duration (ms), 0 = no auto-dismiss
  defaultDuration: {
    type: Number,
    default: 5000
  },
  // Maximum toasts visible
  maxToasts: {
    type: Number,
    default: 5
  },
  // Show progress bar by default
  showProgress: {
    type: Boolean,
    default: true
  }
})

const toasts = ref([])
const timers = ref({})
const progressIntervals = ref({})
let toastId = 0

// Position classes
const positionClasses = computed(() => {
  const positions = {
    'top-left': 'top-4 left-4 items-start',
    'top-center': 'top-4 left-1/2 -translate-x-1/2 items-center',
    'top-right': 'top-4 right-4 items-end',
    'bottom-left': 'bottom-4 left-4 items-start',
    'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2 items-center',
    'bottom-right': 'bottom-4 right-4 items-end'
  }
  return positions[props.position]
})

// Transition name based on position
const transitionName = computed(() => {
  if (props.position.includes('left')) return 'toast-left'
  if (props.position.includes('right')) return 'toast-right'
  return 'toast-center'
})

// Get icon for toast type
function getIcon(type) {
  const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    default: BellIcon
  }
  return icons[type] || icons.default
}

// Variant classes
function variantClasses(type) {
  const variants = {
    success: {
      container: 'bg-white dark:bg-gray-800 border-l-4 border-green-500',
      icon: 'text-green-500',
      title: 'text-gray-900 dark:text-white',
      message: 'text-gray-600 dark:text-gray-300',
      close: 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
      progress: 'bg-green-500',
      primaryAction: 'text-green-600 hover:text-green-700 dark:text-green-400',
      secondaryAction: 'text-gray-600 hover:text-gray-700 dark:text-gray-400'
    },
    error: {
      container: 'bg-white dark:bg-gray-800 border-l-4 border-red-500',
      icon: 'text-red-500',
      title: 'text-gray-900 dark:text-white',
      message: 'text-gray-600 dark:text-gray-300',
      close: 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
      progress: 'bg-red-500',
      primaryAction: 'text-red-600 hover:text-red-700 dark:text-red-400',
      secondaryAction: 'text-gray-600 hover:text-gray-700 dark:text-gray-400'
    },
    warning: {
      container: 'bg-white dark:bg-gray-800 border-l-4 border-yellow-500',
      icon: 'text-yellow-500',
      title: 'text-gray-900 dark:text-white',
      message: 'text-gray-600 dark:text-gray-300',
      close: 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
      progress: 'bg-yellow-500',
      primaryAction: 'text-yellow-600 hover:text-yellow-700 dark:text-yellow-400',
      secondaryAction: 'text-gray-600 hover:text-gray-700 dark:text-gray-400'
    },
    info: {
      container: 'bg-white dark:bg-gray-800 border-l-4 border-blue-500',
      icon: 'text-blue-500',
      title: 'text-gray-900 dark:text-white',
      message: 'text-gray-600 dark:text-gray-300',
      close: 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
      progress: 'bg-blue-500',
      primaryAction: 'text-blue-600 hover:text-blue-700 dark:text-blue-400',
      secondaryAction: 'text-gray-600 hover:text-gray-700 dark:text-gray-400'
    },
    default: {
      container: 'bg-white dark:bg-gray-800 border-l-4 border-gray-500',
      icon: 'text-gray-500',
      title: 'text-gray-900 dark:text-white',
      message: 'text-gray-600 dark:text-gray-300',
      close: 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
      progress: 'bg-gray-500',
      primaryAction: 'text-gray-600 hover:text-gray-700 dark:text-gray-400',
      secondaryAction: 'text-gray-500 hover:text-gray-600 dark:text-gray-500'
    }
  }
  return variants[type] || variants.default
}

// Add toast
function addToast(options) {
  const id = ++toastId
  const duration = options.duration ?? props.defaultDuration
  
  const toast = {
    id,
    type: options.type || 'default',
    title: options.title || '',
    message: options.message || '',
    duration,
    showProgress: options.showProgress ?? props.showProgress,
    actions: options.actions || [],
    progress: 100
  }

  // Limit max toasts
  if (toasts.value.length >= props.maxToasts) {
    const oldest = toasts.value[0]
    removeToast(oldest.id)
  }

  toasts.value.push(toast)

  // Auto dismiss
  if (duration > 0) {
    // Progress animation
    if (toast.showProgress) {
      const interval = 50
      const decrement = (interval / duration) * 100
      progressIntervals.value[id] = setInterval(() => {
        const t = toasts.value.find(t => t.id === id)
        if (t) {
          t.progress = Math.max(0, t.progress - decrement)
        }
      }, interval)
    }

    timers.value[id] = setTimeout(() => {
      removeToast(id)
    }, duration)
  }

  return id
}

// Remove toast
function removeToast(id) {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
  
  if (timers.value[id]) {
    clearTimeout(timers.value[id])
    delete timers.value[id]
  }
  
  if (progressIntervals.value[id]) {
    clearInterval(progressIntervals.value[id])
    delete progressIntervals.value[id]
  }
}

// Handle action click
function handleAction(toast, action) {
  if (action.handler) {
    action.handler()
  }
  if (action.dismiss !== false) {
    removeToast(toast.id)
  }
}

// Clear all toasts
function clearAll() {
  toasts.value.forEach(t => removeToast(t.id))
}

// Convenience methods
function success(message, options = {}) {
  return addToast({ ...options, type: 'success', message })
}

function error(message, options = {}) {
  return addToast({ ...options, type: 'error', message })
}

function warning(message, options = {}) {
  return addToast({ ...options, type: 'warning', message })
}

function info(message, options = {}) {
  return addToast({ ...options, type: 'info', message })
}

// Cleanup
onUnmounted(() => {
  Object.values(timers.value).forEach(clearTimeout)
  Object.values(progressIntervals.value).forEach(clearInterval)
})

// Expose methods
defineExpose({
  addToast,
  removeToast,
  clearAll,
  success,
  error,
  warning,
  info
})
</script>

<style scoped>
/* Right slide */
.toast-right-enter-active,
.toast-right-leave-active {
  transition: all 0.3s ease;
}
.toast-right-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-right-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

/* Left slide */
.toast-left-enter-active,
.toast-left-leave-active {
  transition: all 0.3s ease;
}
.toast-left-enter-from {
  opacity: 0;
  transform: translateX(-100%);
}
.toast-left-leave-to {
  opacity: 0;
  transform: translateX(-100%);
}

/* Center slide */
.toast-center-enter-active,
.toast-center-leave-active {
  transition: all 0.3s ease;
}
.toast-center-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
.toast-center-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* Move transition for reordering */
.toast-right-move,
.toast-left-move,
.toast-center-move {
  transition: transform 0.3s ease;
}
</style>
