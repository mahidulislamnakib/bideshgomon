<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    :enter-from-class="enterFromClasses"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
      :class="containerClasses"
    >
      <div class="p-4">
        <div class="flex items-start">
          <!-- Icon -->
          <div class="flex-shrink-0">
            <component
              :is="iconComponent"
              class="h-6 w-6"
              :class="iconClasses"
            />
          </div>

          <!-- Content -->
          <div class="ml-3 w-0 flex-1">
            <!-- Title -->
            <p class="text-sm font-medium" :class="titleClasses">
              {{ title }}
            </p>

            <!-- Message -->
            <p v-if="message" class="mt-1 text-sm" :class="messageClasses">
              {{ message }}
            </p>

            <!-- Actions -->
            <div v-if="actions && actions.length" class="mt-3 flex gap-3">
              <button
                v-for="action in actions"
                :key="action.label"
                type="button"
                class="text-sm font-medium transition-colors focus:outline-none"
                :class="action.primary ? primaryActionClasses : secondaryActionClasses"
                @click="handleAction(action)"
              >
                {{ action.label }}
              </button>
            </div>

            <!-- Custom content slot -->
            <div v-if="$slots.default" class="mt-2">
              <slot />
            </div>
          </div>

          <!-- Close button -->
          <div v-if="dismissible" class="ml-4 flex flex-shrink-0">
            <button
              type="button"
              class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
              :class="closeButtonClasses"
              @click="handleDismiss"
            >
              <span class="sr-only">Close</span>
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Progress bar for auto-dismiss -->
        <div
          v-if="duration && showProgress"
          class="absolute bottom-0 left-0 right-0 h-1 overflow-hidden rounded-b-lg"
        >
          <div
            class="h-full transition-all ease-linear"
            :class="progressClasses"
            :style="{ width: `${progress}%`, transitionDuration: `${duration}ms` }"
          />
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { XMarkIcon } from '@heroicons/vue/20/solid'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: true
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  actions: {
    type: Array,
    default: () => []
    // [{ label: 'Undo', primary: true, handler: () => {} }]
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  duration: {
    type: Number,
    default: null // null = no auto-dismiss
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => ['top-left', 'top-right', 'top-center', 'bottom-left', 'bottom-right', 'bottom-center'].includes(value)
  }
})

const emit = defineEmits(['dismiss', 'action'])

// Progress tracking
const progress = ref(100)
let progressTimer = null
let dismissTimer = null

// Watch show state to reset/start timers
watch(() => props.show, (newVal) => {
  if (newVal && props.duration) {
    startDismissTimer()
  } else {
    clearTimers()
  }
}, { immediate: true })

// Start auto-dismiss timer
function startDismissTimer() {
  clearTimers()
  
  if (!props.duration) return
  
  progress.value = 100
  
  // Animate progress bar
  requestAnimationFrame(() => {
    progress.value = 0
  })
  
  // Set dismiss timer
  dismissTimer = setTimeout(() => {
    handleDismiss()
  }, props.duration)
}

// Clear all timers
function clearTimers() {
  if (progressTimer) clearInterval(progressTimer)
  if (dismissTimer) clearTimeout(dismissTimer)
}

// Handle dismiss
function handleDismiss() {
  clearTimers()
  emit('dismiss')
}

// Handle action click
function handleAction(action) {
  if (action.handler) {
    action.handler()
  }
  emit('action', action)
  if (action.dismissOnClick !== false) {
    handleDismiss()
  }
}

// Cleanup on unmount
onUnmounted(() => {
  clearTimers()
})

// Icon component based on type
const iconComponent = computed(() => {
  const icons = {
    success: CheckCircleIcon,
    error: ExclamationCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon
  }
  return icons[props.type]
})

// Entry animation classes based on position
const enterFromClasses = computed(() => {
  if (props.position.includes('left')) {
    return 'translate-y-2 opacity-0 sm:-translate-x-full'
  }
  if (props.position.includes('right')) {
    return 'translate-y-2 opacity-0 sm:translate-x-full'
  }
  return 'translate-y-2 opacity-0'
})

// Container styling
const containerClasses = computed(() => {
  return 'bg-white relative'
})

// Icon styling
const iconClasses = computed(() => {
  const colors = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return colors[props.type]
})

// Title styling
const titleClasses = 'text-gray-900'

// Message styling
const messageClasses = 'text-gray-500'

// Close button styling
const closeButtonClasses = computed(() => {
  const colors = {
    success: 'text-gray-400 hover:text-gray-500 focus:ring-green-500',
    error: 'text-gray-400 hover:text-gray-500 focus:ring-red-500',
    warning: 'text-gray-400 hover:text-gray-500 focus:ring-yellow-500',
    info: 'text-gray-400 hover:text-gray-500 focus:ring-blue-500'
  }
  return colors[props.type]
})

// Action button styling
const primaryActionClasses = computed(() => {
  const colors = {
    success: 'text-green-600 hover:text-green-500',
    error: 'text-red-600 hover:text-red-500',
    warning: 'text-yellow-600 hover:text-yellow-500',
    info: 'text-blue-600 hover:text-blue-500'
  }
  return colors[props.type]
})

const secondaryActionClasses = 'text-gray-700 hover:text-gray-500'

// Progress bar styling
const progressClasses = computed(() => {
  const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500'
  }
  return colors[props.type]
})
</script>
