<template>
  <div :class="containerClasses">
    <!-- Action buttons -->
    <TransitionGroup
      :name="transitionName"
      tag="div"
      :class="actionsContainerClasses"
    >
      <template v-if="isOpen">
        <button
          v-for="(action, index) in actions"
          :key="action.id || index"
          type="button"
          :class="actionButtonClasses(action)"
          :style="actionButtonStyle(index)"
          :disabled="action.disabled"
          @click="handleAction(action)"
        >
          <component
            v-if="action.icon"
            :is="action.icon"
            :class="actionIconClasses"
          />
          
          <!-- Tooltip -->
          <span
            v-if="action.label && showLabels"
            :class="tooltipClasses"
          >
            {{ action.label }}
          </span>
        </button>
      </template>
    </TransitionGroup>
    
    <!-- Main FAB -->
    <button
      type="button"
      :class="fabClasses"
      :disabled="disabled"
      @click="toggle"
      @mouseenter="hoverOpen && open()"
      @mouseleave="hoverOpen && close()"
    >
      <Transition
        enter-active-class="transition-transform duration-200"
        enter-from-class="rotate-180 scale-0"
        enter-to-class="rotate-0 scale-100"
        leave-active-class="transition-transform duration-200"
        leave-from-class="rotate-0 scale-100"
        leave-to-class="rotate-180 scale-0"
        mode="out-in"
      >
        <component
          v-if="isOpen && closeIcon"
          :is="closeIcon"
          :key="'close'"
          :class="fabIconClasses"
        />
        <component
          v-else
          :is="icon"
          :key="'open'"
          :class="fabIconClasses"
        />
      </Transition>
    </button>
    
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen && showBackdrop"
        :class="backdropClasses"
        @click="close"
      />
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  // Actions
  actions: {
    type: Array,
    required: true
    // { id?, icon, label?, color?, disabled?, action? }
  },
  
  // Icons
  icon: {
    type: [Object, Function],
    default: () => PlusIcon
  },
  closeIcon: {
    type: [Object, Function],
    default: () => XMarkIcon
  },
  
  // Direction
  direction: {
    type: String,
    default: 'up',
    validator: (v) => ['up', 'down', 'left', 'right'].includes(v)
  },
  
  // Position
  position: {
    type: String,
    default: 'bottom-right',
    validator: (v) => ['bottom-right', 'bottom-left', 'top-right', 'top-left', 'center'].includes(v)
  },
  
  // Display
  showLabels: {
    type: Boolean,
    default: true
  },
  showBackdrop: {
    type: Boolean,
    default: false
  },
  
  // Behavior
  hoverOpen: {
    type: Boolean,
    default: false
  },
  closeOnAction: {
    type: Boolean,
    default: true
  },
  
  // Spacing
  spacing: {
    type: Number,
    default: 60
  },
  
  // Colors
  color: {
    type: String,
    default: 'primary'
  },
  
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['action', 'open', 'close'])

const isOpen = ref(false)

// Size config
const sizeConfig = computed(() => {
  const sizes = {
    sm: { fab: 'w-12 h-12', action: 'w-10 h-10', icon: 'w-5 h-5', actionIcon: 'w-4 h-4' },
    md: { fab: 'w-14 h-14', action: 'w-12 h-12', icon: 'w-6 h-6', actionIcon: 'w-5 h-5' },
    lg: { fab: 'w-16 h-16', action: 'w-14 h-14', icon: 'w-7 h-7', actionIcon: 'w-6 h-6' }
  }
  return sizes[props.size]
})

// Color config
const colorConfig = computed(() => {
  const colors = {
    primary: 'bg-primary-600 hover:bg-primary-700 text-white',
    secondary: 'bg-gray-600 hover:bg-gray-700 text-white',
    success: 'bg-green-600 hover:bg-green-700 text-white',
    danger: 'bg-red-600 hover:bg-red-700 text-white',
    warning: 'bg-amber-500 hover:bg-amber-600 text-white'
  }
  return colors[props.color] || colors.primary
})

// Transition name based on direction
const transitionName = computed(() => `speed-dial-${props.direction}`)

// Container classes
const containerClasses = computed(() => [
  'fixed z-50',
  props.position === 'bottom-right' && 'bottom-6 right-6',
  props.position === 'bottom-left' && 'bottom-6 left-6',
  props.position === 'top-right' && 'top-6 right-6',
  props.position === 'top-left' && 'top-6 left-6',
  props.position === 'center' && 'bottom-6 left-1/2 -translate-x-1/2'
])

// Actions container classes
const actionsContainerClasses = computed(() => [
  'absolute flex',
  props.direction === 'up' && 'flex-col-reverse bottom-full mb-3 left-1/2 -translate-x-1/2',
  props.direction === 'down' && 'flex-col top-full mt-3 left-1/2 -translate-x-1/2',
  props.direction === 'left' && 'flex-row-reverse right-full mr-3 top-1/2 -translate-y-1/2',
  props.direction === 'right' && 'flex-row left-full ml-3 top-1/2 -translate-y-1/2',
  ['up', 'down'].includes(props.direction) ? 'space-y-2' : 'space-x-2'
])

// FAB classes
const fabClasses = computed(() => [
  sizeConfig.value.fab,
  'rounded-full shadow-lg',
  colorConfig.value,
  'flex items-center justify-center',
  'transition-all duration-200',
  'hover:shadow-xl hover:scale-105',
  'focus:outline-none focus:ring-4 focus:ring-primary-500/30',
  isOpen.value && 'shadow-xl',
  props.disabled && 'opacity-50 cursor-not-allowed'
])

// FAB icon classes
const fabIconClasses = computed(() => [
  sizeConfig.value.icon
])

// Action button classes
function actionButtonClasses(action) {
  const actionColor = action.color 
    ? `bg-${action.color}-600 hover:bg-${action.color}-700 text-white`
    : 'bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200'
  
  return [
    sizeConfig.value.action,
    'rounded-full shadow-md',
    actionColor,
    'flex items-center justify-center',
    'transition-all duration-200',
    'hover:shadow-lg hover:scale-105',
    'focus:outline-none focus:ring-2 focus:ring-primary-500/50',
    'relative group',
    action.disabled && 'opacity-50 cursor-not-allowed'
  ]
}

// Action button style (for stagger effect)
function actionButtonStyle(index) {
  return {
    transitionDelay: `${index * 50}ms`
  }
}

// Action icon classes
const actionIconClasses = computed(() => [
  sizeConfig.value.actionIcon
])

// Tooltip classes
const tooltipClasses = computed(() => [
  'absolute whitespace-nowrap',
  'px-2 py-1 rounded',
  'bg-gray-900 dark:bg-gray-700 text-white text-sm',
  'opacity-0 group-hover:opacity-100',
  'transition-opacity duration-200',
  'pointer-events-none',
  props.direction === 'up' || props.direction === 'down' 
    ? 'right-full mr-2 top-1/2 -translate-y-1/2' 
    : 'bottom-full mb-2 left-1/2 -translate-x-1/2'
])

// Backdrop classes
const backdropClasses = computed(() => [
  'fixed inset-0 -z-10',
  'bg-black/20'
])

// Toggle
function toggle() {
  if (props.disabled) return
  isOpen.value ? close() : open()
}

// Open
function open() {
  if (props.disabled) return
  isOpen.value = true
  emit('open')
}

// Close
function close() {
  isOpen.value = false
  emit('close')
}

// Handle action click
function handleAction(action) {
  if (action.disabled) return
  
  emit('action', action)
  
  if (action.action && typeof action.action === 'function') {
    action.action()
  }
  
  if (props.closeOnAction) {
    close()
  }
}

// Expose
defineExpose({
  open,
  close,
  toggle,
  isOpen
})
</script>

<style scoped>
/* Up direction */
.speed-dial-up-enter-active,
.speed-dial-up-leave-active {
  transition: all 0.2s ease;
}
.speed-dial-up-enter-from,
.speed-dial-up-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.8);
}

/* Down direction */
.speed-dial-down-enter-active,
.speed-dial-down-leave-active {
  transition: all 0.2s ease;
}
.speed-dial-down-enter-from,
.speed-dial-down-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.8);
}

/* Left direction */
.speed-dial-left-enter-active,
.speed-dial-left-leave-active {
  transition: all 0.2s ease;
}
.speed-dial-left-enter-from,
.speed-dial-left-leave-to {
  opacity: 0;
  transform: translateX(20px) scale(0.8);
}

/* Right direction */
.speed-dial-right-enter-active,
.speed-dial-right-leave-active {
  transition: all 0.2s ease;
}
.speed-dial-right-enter-from,
.speed-dial-right-leave-to {
  opacity: 0;
  transform: translateX(-20px) scale(0.8);
}
</style>
