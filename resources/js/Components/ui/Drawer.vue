<template>
  <TransitionRoot as="template" :show="modelValue">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-in-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="pointer-events-none fixed inset-y-0 flex max-w-full"
            :class="positionClasses"
          >
            <TransitionChild
              as="template"
              :enter="transitionEnter"
              :enter-from="transitionEnterFrom"
              :enter-to="transitionEnterTo"
              :leave="transitionLeave"
              :leave-from="transitionLeaveFrom"
              :leave-to="transitionLeaveTo"
            >
              <DialogPanel
                class="pointer-events-auto relative"
                :class="[widthClasses, panelClasses]"
              >
                <!-- Close button -->
                <TransitionChild
                  v-if="showCloseButton"
                  as="template"
                  enter="ease-in-out duration-300"
                  enter-from="opacity-0"
                  enter-to="opacity-100"
                  leave="ease-in-out duration-300"
                  leave-from="opacity-100"
                  leave-to="opacity-0"
                >
                  <div
                    class="absolute top-0 flex pt-4"
                    :class="position === 'left' ? 'right-0 -mr-12 pl-2' : 'left-0 -ml-12 pr-2'"
                  >
                    <button
                      type="button"
                      class="relative rounded-md text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
                      @click="handleClose"
                    >
                      <span class="absolute -inset-2.5" />
                      <span class="sr-only">Close panel</span>
                      <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                    </button>
                  </div>
                </TransitionChild>

                <div class="flex h-full flex-col overflow-y-auto" :class="bgClasses">
                  <!-- Header -->
                  <div v-if="$slots.header || title" class="px-4 py-6 sm:px-6" :class="headerClasses">
                    <slot name="header">
                      <div class="flex items-start justify-between">
                        <DialogTitle class="text-base font-semibold leading-6" :class="titleClasses">
                          {{ title }}
                        </DialogTitle>
                        <div v-if="!showCloseButton" class="ml-3 flex h-7 items-center">
                          <button
                            type="button"
                            class="relative rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                            :class="closeButtonClasses"
                            @click="handleClose"
                          >
                            <span class="absolute -inset-2.5" />
                            <span class="sr-only">Close panel</span>
                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                          </button>
                        </div>
                      </div>
                      <p v-if="subtitle" class="mt-1 text-sm" :class="subtitleClasses">
                        {{ subtitle }}
                      </p>
                    </slot>
                  </div>

                  <!-- Content -->
                  <div class="relative flex-1 px-4 sm:px-6" :class="contentClasses">
                    <slot />
                  </div>

                  <!-- Footer -->
                  <div v-if="$slots.footer" class="flex-shrink-0 px-4 py-4 sm:px-6" :class="footerClasses">
                    <slot name="footer" />
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
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
  subtitle: {
    type: String,
    default: ''
  },
  position: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'right'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'dark', 'primary'].includes(value)
  },
  showCloseButton: {
    type: Boolean,
    default: false
  },
  closeOnOverlay: {
    type: Boolean,
    default: true
  },
  static: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

// Position classes
const positionClasses = computed(() => ({
  'left-0': props.position === 'left',
  'right-0': props.position === 'right'
}))

// Width classes based on size
const widthClasses = computed(() => {
  const sizes = {
    xs: 'w-screen max-w-xs',
    sm: 'w-screen max-w-sm',
    md: 'w-screen max-w-md',
    lg: 'w-screen max-w-lg',
    xl: 'w-screen max-w-xl',
    full: 'w-screen'
  }
  return sizes[props.size]
})

// Panel styling
const panelClasses = computed(() => {
  if (props.position === 'left') {
    return 'border-r border-gray-200'
  }
  return 'border-l border-gray-200'
})

// Background classes based on variant
const bgClasses = computed(() => {
  const variants = {
    default: 'bg-white',
    dark: 'bg-gray-900',
    primary: 'bg-primary-600'
  }
  return variants[props.variant]
})

// Header classes
const headerClasses = computed(() => {
  const variants = {
    default: 'bg-gray-50',
    dark: 'bg-gray-800',
    primary: 'bg-primary-700'
  }
  return variants[props.variant]
})

// Title classes
const titleClasses = computed(() => {
  const variants = {
    default: 'text-gray-900',
    dark: 'text-white',
    primary: 'text-white'
  }
  return variants[props.variant]
})

// Subtitle classes
const subtitleClasses = computed(() => {
  const variants = {
    default: 'text-gray-500',
    dark: 'text-gray-400',
    primary: 'text-primary-100'
  }
  return variants[props.variant]
})

// Close button classes
const closeButtonClasses = computed(() => {
  const variants = {
    default: 'text-gray-400 hover:text-gray-500 focus:ring-primary-500',
    dark: 'text-gray-400 hover:text-gray-300 focus:ring-gray-500',
    primary: 'text-primary-200 hover:text-white focus:ring-white'
  }
  return variants[props.variant]
})

// Content classes
const contentClasses = computed(() => {
  const variants = {
    default: 'text-gray-700',
    dark: 'text-gray-300',
    primary: 'text-white'
  }
  return variants[props.variant]
})

// Footer classes
const footerClasses = computed(() => {
  const variants = {
    default: 'border-t border-gray-200 bg-gray-50',
    dark: 'border-t border-gray-700 bg-gray-800',
    primary: 'border-t border-primary-500 bg-primary-700'
  }
  return variants[props.variant]
})

// Transition classes based on position
const transitionEnter = 'transform transition ease-in-out duration-300 sm:duration-500'
const transitionLeave = 'transform transition ease-in-out duration-300 sm:duration-500'

const transitionEnterFrom = computed(() => 
  props.position === 'left' ? '-translate-x-full' : 'translate-x-full'
)
const transitionEnterTo = 'translate-x-0'
const transitionLeaveFrom = 'translate-x-0'
const transitionLeaveTo = computed(() => 
  props.position === 'left' ? '-translate-x-full' : 'translate-x-full'
)

// Handle close
function handleClose() {
  if (props.static) return
  if (!props.closeOnOverlay) return
  emit('update:modelValue', false)
  emit('close')
}
</script>
