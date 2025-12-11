<script setup>
import { computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  showClose: {
    type: Boolean,
    default: true
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'open'])

const modalClasses = computed(() => {
  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-full mx-4'
  }
  
  return ['w-full', sizeClasses[props.size]].join(' ')
})

const handleBackdropClick = () => {
  if (props.closeOnBackdrop && !props.persistent) {
    closeModal()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.closeOnEscape && !props.persistent) {
    closeModal()
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
  emit('close')
}

const trapFocus = (event) => {
  const modal = event.currentTarget
  const focusableElements = modal.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  )
  const firstElement = focusableElements[0]
  const lastElement = focusableElements[focusableElements.length - 1]

  if (event.key === 'Tab') {
    if (event.shiftKey && document.activeElement === firstElement) {
      event.preventDefault()
      lastElement?.focus()
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      event.preventDefault()
      firstElement?.focus()
    }
  }
}

watch(() => props.modelValue, async (value) => {
  if (value) {
    emit('open')
    document.body.style.overflow = 'hidden'
    
    await nextTick()
    const modal = document.querySelector('[role="dialog"]')
    const firstFocusable = modal?.querySelector(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    firstFocusable?.focus()
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  if (props.closeOnEscape) {
    document.addEventListener('keydown', handleEscape)
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm"
        aria-labelledby="modal-title"
        aria-modal="true"
        role="dialog"
        @click.self="handleBackdropClick"
      >
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="modelValue"
              :class="modalClasses"
              class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all"
              @keydown="trapFocus"
            >
              <!-- Header -->
              <div
                v-if="title || $slots.header || showClose"
                class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4"
              >
                <slot name="header">
                  <h3
                    id="modal-title"
                    class="text-lg font-semibold text-gray-900 dark:text-white"
                  >
                    {{ title }}
                  </h3>
                </slot>
                
                <button
                  v-if="showClose"
                  type="button"
                  class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700"
                  aria-label="Close modal"
                  @click="closeModal"
                >
                  <XMarkIcon class="h-5 w-5" />
                </button>
              </div>

              <!-- Body -->
              <div class="px-6 py-4">
                <slot />
              </div>

              <!-- Footer -->
              <div
                v-if="$slots.footer"
                class="flex items-center justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-900/50"
              >
                <slot name="footer" :close="closeModal" />
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
