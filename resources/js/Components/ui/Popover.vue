<template>
  <HeadlessPopover class="relative" v-slot="{ open, close }">
    <!-- Trigger -->
    <PopoverButton
      as="template"
      @mouseenter="trigger === 'hover' && openPopover()"
      @mouseleave="trigger === 'hover' && startCloseDelay(close)"
    >
      <slot name="trigger" :open="open">
        <button
          type="button"
          class="inline-flex items-center gap-1 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none"
        >
          {{ triggerText }}
          <ChevronDownIcon
            :class="['w-4 h-4 transition-transform', open ? 'rotate-180' : '']"
          />
        </button>
      </slot>
    </PopoverButton>

    <!-- Panel -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <PopoverPanel
        :static="trigger === 'hover'"
        v-show="trigger === 'hover' ? isHoverOpen : true"
        :class="[
          'absolute z-50',
          positionClasses,
          widthClasses
        ]"
        @mouseenter="trigger === 'hover' && cancelCloseDelay()"
        @mouseleave="trigger === 'hover' && startCloseDelay(close)"
      >
        <div
          :class="[
            'overflow-hidden rounded-xl shadow-lg ring-1 ring-black/5',
            variantClasses.container
          ]"
        >
          <!-- Arrow -->
          <div
            v-if="showArrow"
            :class="[
              'absolute w-3 h-3 rotate-45',
              arrowClasses,
              variantClasses.arrow
            ]"
          />

          <!-- Header -->
          <div
            v-if="title || $slots.header"
            :class="[
              'relative z-10 px-4 py-3 border-b',
              variantClasses.header
            ]"
          >
            <slot name="header">
              <h3 :class="['text-sm font-semibold', variantClasses.title]">
                {{ title }}
              </h3>
              <p v-if="description" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ description }}
              </p>
            </slot>
          </div>

          <!-- Content -->
          <div :class="['relative z-10', contentPadding ? 'p-4' : '']">
            <slot :close="close" />
          </div>

          <!-- Footer -->
          <div
            v-if="$slots.footer"
            :class="[
              'relative z-10 px-4 py-3 border-t',
              variantClasses.footer
            ]"
          >
            <slot name="footer" :close="close" />
          </div>
        </div>
      </PopoverPanel>
    </Transition>
  </HeadlessPopover>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Popover as HeadlessPopover,
  PopoverButton,
  PopoverPanel
} from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  // Trigger text (if not using slot)
  triggerText: {
    type: String,
    default: 'Options'
  },
  // Title
  title: {
    type: String,
    default: ''
  },
  // Description
  description: {
    type: String,
    default: ''
  },
  // Position
  position: {
    type: String,
    default: 'bottom',
    validator: (v) => ['top', 'bottom', 'left', 'right', 'top-start', 'top-end', 'bottom-start', 'bottom-end'].includes(v)
  },
  // Width
  width: {
    type: String,
    default: 'md',
    validator: (v) => ['auto', 'xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(v)
  },
  // Variant
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'dark', 'primary'].includes(v)
  },
  // Trigger type
  trigger: {
    type: String,
    default: 'click',
    validator: (v) => ['click', 'hover'].includes(v)
  },
  // Close delay for hover trigger (ms)
  closeDelay: {
    type: Number,
    default: 150
  },
  // Show arrow
  showArrow: {
    type: Boolean,
    default: true
  },
  // Content padding
  contentPadding: {
    type: Boolean,
    default: true
  }
})

const isHoverOpen = ref(false)
let closeTimeout = null

// Position classes
const positionClasses = computed(() => {
  const positions = {
    top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
    left: 'right-full top-1/2 -translate-y-1/2 mr-2',
    right: 'left-full top-1/2 -translate-y-1/2 ml-2',
    'top-start': 'bottom-full left-0 mb-2',
    'top-end': 'bottom-full right-0 mb-2',
    'bottom-start': 'top-full left-0 mt-2',
    'bottom-end': 'top-full right-0 mt-2'
  }
  return positions[props.position]
})

// Arrow classes
const arrowClasses = computed(() => {
  const arrows = {
    top: 'bottom-[-6px] left-1/2 -translate-x-1/2',
    bottom: 'top-[-6px] left-1/2 -translate-x-1/2',
    left: 'right-[-6px] top-1/2 -translate-y-1/2',
    right: 'left-[-6px] top-1/2 -translate-y-1/2',
    'top-start': 'bottom-[-6px] left-4',
    'top-end': 'bottom-[-6px] right-4',
    'bottom-start': 'top-[-6px] left-4',
    'bottom-end': 'top-[-6px] right-4'
  }
  return arrows[props.position]
})

// Width classes
const widthClasses = computed(() => {
  const widths = {
    auto: 'w-auto',
    xs: 'w-48',
    sm: 'w-56',
    md: 'w-72',
    lg: 'w-96',
    xl: 'w-[28rem]',
    full: 'w-full'
  }
  return widths[props.width]
})

// Variant classes
const variantClasses = computed(() => {
  const variants = {
    default: {
      container: 'bg-white dark:bg-gray-800',
      arrow: 'bg-white dark:bg-gray-800',
      header: 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50',
      title: 'text-gray-900 dark:text-white',
      footer: 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50'
    },
    dark: {
      container: 'bg-gray-900',
      arrow: 'bg-gray-900',
      header: 'border-gray-700 bg-gray-800',
      title: 'text-white',
      footer: 'border-gray-700 bg-gray-800'
    },
    primary: {
      container: 'bg-primary-600',
      arrow: 'bg-primary-600',
      header: 'border-primary-500 bg-primary-700',
      title: 'text-white',
      footer: 'border-primary-500 bg-primary-700'
    }
  }
  return variants[props.variant]
})

function openPopover() {
  cancelCloseDelay()
  isHoverOpen.value = true
}

function startCloseDelay(close) {
  closeTimeout = setTimeout(() => {
    isHoverOpen.value = false
    if (close) close()
  }, props.closeDelay)
}

function cancelCloseDelay() {
  if (closeTimeout) {
    clearTimeout(closeTimeout)
    closeTimeout = null
  }
}
</script>
