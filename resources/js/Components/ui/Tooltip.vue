<template>
  <Popover class="relative inline-block" v-slot="{ open }">
    <!-- Trigger -->
    <PopoverButton
      ref="buttonRef"
      as="template"
      @mouseenter="showOnHover && show()"
      @mouseleave="showOnHover && startHideDelay()"
      @focus="showOnHover && show()"
      @blur="showOnHover && hide()"
    >
      <slot>
        <span class="cursor-help">
          <InformationCircleIcon class="w-4 h-4 text-gray-400" />
        </span>
      </slot>
    </PopoverButton>

    <!-- Tooltip Panel -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <PopoverPanel
        v-show="isVisible"
        static
        :class="[
          'absolute z-50',
          positionClasses,
          'pointer-events-none'
        ]"
        @mouseenter="showOnHover && cancelHideDelay()"
        @mouseleave="showOnHover && startHideDelay()"
      >
        <div
          :class="[
            'relative px-3 py-2 text-sm rounded-lg shadow-lg',
            'max-w-xs',
            variantClasses.container,
            sizeClasses
          ]"
        >
          <!-- Arrow -->
          <div
            :class="[
              'absolute w-2 h-2 rotate-45',
              arrowClasses,
              variantClasses.arrow
            ]"
          />

          <!-- Content -->
          <div class="relative z-10">
            <!-- Title -->
            <div v-if="title" :class="['font-semibold mb-1', variantClasses.title]">
              {{ title }}
            </div>

            <!-- Message -->
            <div :class="variantClasses.content">
              <slot name="content">{{ content }}</slot>
            </div>

            <!-- Shortcut -->
            <div v-if="shortcut" class="mt-1.5 flex items-center gap-1">
              <kbd
                v-for="(key, index) in shortcut.split('+')"
                :key="index"
                :class="[
                  'px-1.5 py-0.5 text-xs font-medium rounded',
                  variantClasses.shortcut
                ]"
              >
                {{ formatKey(key) }}
              </kbd>
            </div>
          </div>
        </div>
      </PopoverPanel>
    </Transition>
  </Popover>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { InformationCircleIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  // Tooltip content
  content: {
    type: String,
    default: ''
  },
  // Tooltip title
  title: {
    type: String,
    default: ''
  },
  // Position
  position: {
    type: String,
    default: 'top',
    validator: (v) => ['top', 'bottom', 'left', 'right', 'top-start', 'top-end', 'bottom-start', 'bottom-end'].includes(v)
  },
  // Variant
  variant: {
    type: String,
    default: 'dark',
    validator: (v) => ['dark', 'light', 'primary', 'success', 'warning', 'error'].includes(v)
  },
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  // Show on hover (vs click)
  showOnHover: {
    type: Boolean,
    default: true
  },
  // Delay before showing (ms)
  showDelay: {
    type: Number,
    default: 200
  },
  // Delay before hiding (ms)
  hideDelay: {
    type: Number,
    default: 100
  },
  // Keyboard shortcut to display
  shortcut: {
    type: String,
    default: ''
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Controlled visibility
  modelValue: {
    type: Boolean,
    default: undefined
  }
})

const emit = defineEmits(['update:modelValue', 'show', 'hide'])

const buttonRef = ref(null)
const internalVisible = ref(false)
let showTimeout = null
let hideTimeout = null

// Visibility state (controlled or internal)
const isVisible = computed(() => {
  if (props.disabled) return false
  return props.modelValue !== undefined ? props.modelValue : internalVisible.value
})

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

// Arrow position classes
const arrowClasses = computed(() => {
  const arrows = {
    top: 'bottom-[-4px] left-1/2 -translate-x-1/2',
    bottom: 'top-[-4px] left-1/2 -translate-x-1/2',
    left: 'right-[-4px] top-1/2 -translate-y-1/2',
    right: 'left-[-4px] top-1/2 -translate-y-1/2',
    'top-start': 'bottom-[-4px] left-4',
    'top-end': 'bottom-[-4px] right-4',
    'bottom-start': 'top-[-4px] left-4',
    'bottom-end': 'top-[-4px] right-4'
  }
  return arrows[props.position]
})

// Variant classes
const variantClasses = computed(() => {
  const variants = {
    dark: {
      container: 'bg-gray-900 text-white',
      arrow: 'bg-gray-900',
      title: 'text-white',
      content: 'text-gray-300',
      shortcut: 'bg-gray-700 text-gray-300'
    },
    light: {
      container: 'bg-white text-gray-900 border border-gray-200 shadow-lg',
      arrow: 'bg-white border-l border-b border-gray-200',
      title: 'text-gray-900',
      content: 'text-gray-600',
      shortcut: 'bg-gray-100 text-gray-600'
    },
    primary: {
      container: 'bg-primary-600 text-white',
      arrow: 'bg-primary-600',
      title: 'text-white',
      content: 'text-primary-100',
      shortcut: 'bg-primary-700 text-primary-200'
    },
    success: {
      container: 'bg-green-600 text-white',
      arrow: 'bg-green-600',
      title: 'text-white',
      content: 'text-green-100',
      shortcut: 'bg-green-700 text-green-200'
    },
    warning: {
      container: 'bg-yellow-500 text-yellow-900',
      arrow: 'bg-yellow-500',
      title: 'text-yellow-900',
      content: 'text-yellow-800',
      shortcut: 'bg-yellow-600 text-yellow-100'
    },
    error: {
      container: 'bg-red-600 text-white',
      arrow: 'bg-red-600',
      title: 'text-white',
      content: 'text-red-100',
      shortcut: 'bg-red-700 text-red-200'
    }
  }
  return variants[props.variant]
})

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: 'text-xs py-1 px-2',
    md: 'text-sm py-2 px-3',
    lg: 'text-base py-3 px-4'
  }
  return sizes[props.size]
})

// Format keyboard key for display
function formatKey(key) {
  const keyMap = {
    ctrl: '⌃',
    cmd: '⌘',
    alt: '⌥',
    shift: '⇧',
    enter: '↵',
    esc: 'Esc',
    tab: '⇥',
    up: '↑',
    down: '↓',
    left: '←',
    right: '→'
  }
  return keyMap[key.toLowerCase()] || key.toUpperCase()
}

// Show tooltip
function show() {
  if (props.disabled) return
  
  cancelHideDelay()
  
  if (props.showDelay > 0) {
    showTimeout = setTimeout(() => {
      setVisible(true)
    }, props.showDelay)
  } else {
    setVisible(true)
  }
}

// Hide tooltip
function hide() {
  if (props.disabled) return
  
  cancelShowDelay()
  setVisible(false)
}

// Start hide delay
function startHideDelay() {
  cancelShowDelay()
  
  if (props.hideDelay > 0) {
    hideTimeout = setTimeout(() => {
      setVisible(false)
    }, props.hideDelay)
  } else {
    setVisible(false)
  }
}

// Cancel hide delay
function cancelHideDelay() {
  if (hideTimeout) {
    clearTimeout(hideTimeout)
    hideTimeout = null
  }
}

// Cancel show delay
function cancelShowDelay() {
  if (showTimeout) {
    clearTimeout(showTimeout)
    showTimeout = null
  }
}

// Set visibility
function setVisible(value) {
  internalVisible.value = value
  emit('update:modelValue', value)
  emit(value ? 'show' : 'hide')
}

// Watch for external visibility changes
watch(() => props.modelValue, (val) => {
  if (val !== undefined) {
    internalVisible.value = val
  }
})

// Cleanup
onUnmounted(() => {
  cancelShowDelay()
  cancelHideDelay()
})

// Expose methods
defineExpose({
  show,
  hide
})
</script>
