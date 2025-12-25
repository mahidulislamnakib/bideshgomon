<template>
  <div
    class="fixed z-50"
    :class="positionClasses"
    :style="{ zIndex }"
  >
    <!-- Speed dial menu (expanded actions) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="isExpanded && actions.length > 0"
        class="absolute mb-3 flex flex-col-reverse gap-2"
        :class="menuPositionClasses"
      >
        <TransitionGroup
          tag="div"
          name="fab-action"
          class="flex flex-col-reverse gap-2"
        >
          <div
            v-for="(action, index) in actions"
            :key="action.id || index"
            class="flex items-center gap-3"
            :class="direction === 'left' ? 'flex-row-reverse' : ''"
          >
            <!-- Label tooltip -->
            <span
              v-if="action.label && showLabels"
              class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white rounded-lg shadow-lg whitespace-nowrap"
            >
              {{ action.label }}
            </span>

            <!-- Action button -->
            <button
              type="button"
              class="flex items-center justify-center rounded-full shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 hover:scale-110"
              :class="[
                actionSizeClasses,
                action.color ? '' : 'bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-500'
              ]"
              :style="action.color ? { backgroundColor: action.color, color: '#fff' } : {}"
              :title="action.label"
              :disabled="action.disabled"
              @click="handleActionClick(action)"
            >
              <component
                :is="action.icon"
                :class="actionIconSizeClasses"
              />
            </button>
          </div>
        </TransitionGroup>
      </div>
    </Transition>

    <!-- Main FAB button -->
    <button
      ref="fabRef"
      type="button"
      class="flex items-center justify-center rounded-full shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200"
      :class="[
        mainSizeClasses,
        colorClasses,
        { 'rotate-45': isExpanded && rotateOnExpand }
      ]"
      :disabled="disabled"
      @click="handleMainClick"
    >
      <slot name="icon">
        <component
          :is="icon"
          :class="mainIconSizeClasses"
        />
      </slot>
    </button>

    <!-- Backdrop -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isExpanded && showBackdrop"
        class="fixed inset-0 bg-black/20 -z-10"
        @click="collapse"
      />
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  icon: {
    type: [Object, Function],
    default: () => PlusIcon
  },
  actions: {
    type: Array,
    default: () => []
    // [{ id?, icon, label?, color?, disabled?, onClick? }]
  },
  position: {
    type: String,
    default: 'bottom-right',
    validator: (value) => ['bottom-right', 'bottom-left', 'bottom-center', 'top-right', 'top-left'].includes(value)
  },
  direction: {
    type: String,
    default: 'up',
    validator: (value) => ['up', 'down', 'left', 'right'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'white'].includes(value)
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  showBackdrop: {
    type: Boolean,
    default: true
  },
  rotateOnExpand: {
    type: Boolean,
    default: true
  },
  closeOnAction: {
    type: Boolean,
    default: true
  },
  zIndex: {
    type: Number,
    default: 50
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click', 'action', 'expand', 'collapse'])

// State
const fabRef = ref(null)
const isExpanded = ref(false)

// Toggle expanded state
function toggle() {
  if (isExpanded.value) {
    collapse()
  } else {
    expand()
  }
}

function expand() {
  if (props.actions.length > 0) {
    isExpanded.value = true
    emit('expand')
  }
}

function collapse() {
  isExpanded.value = false
  emit('collapse')
}

// Handle main button click
function handleMainClick() {
  if (props.actions.length > 0) {
    toggle()
  } else {
    emit('click')
  }
}

// Handle action click
function handleActionClick(action) {
  if (action.disabled) return
  
  emit('action', action)
  
  if (action.onClick) {
    action.onClick(action)
  }
  
  if (props.closeOnAction) {
    collapse()
  }
}

// Position classes
const positionClasses = computed(() => {
  const positions = {
    'bottom-right': 'bottom-6 right-6',
    'bottom-left': 'bottom-6 left-6',
    'bottom-center': 'bottom-6 left-1/2 -translate-x-1/2',
    'top-right': 'top-6 right-6',
    'top-left': 'top-6 left-6'
  }
  return positions[props.position]
})

// Menu position classes
const menuPositionClasses = computed(() => {
  if (props.direction === 'up' || props.position.startsWith('bottom')) {
    return 'bottom-full'
  }
  return 'top-full mt-3'
})

// Main button size classes
const mainSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-12 w-12',
    md: 'h-14 w-14',
    lg: 'h-16 w-16'
  }
  return sizes[props.size]
})

// Main icon size classes
const mainIconSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-5 w-5',
    md: 'h-6 w-6',
    lg: 'h-7 w-7'
  }
  return sizes[props.size]
})

// Action button size classes
const actionSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-10 w-10',
    md: 'h-12 w-12',
    lg: 'h-14 w-14'
  }
  return sizes[props.size]
})

// Action icon size classes
const actionIconSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6'
  }
  return sizes[props.size]
})

// Color classes
const colorClasses = computed(() => {
  const colors = {
    primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
    white: 'bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-500'
  }
  return colors[props.color]
})

// Expose methods
defineExpose({
  toggle,
  expand,
  collapse,
  isExpanded: () => isExpanded.value
})
</script>

<style scoped>
.fab-action-enter-active {
  transition: all 0.2s ease-out;
}
.fab-action-leave-active {
  transition: all 0.15s ease-in;
}
.fab-action-enter-from {
  opacity: 0;
  transform: scale(0.8) translateY(10px);
}
.fab-action-leave-to {
  opacity: 0;
  transform: scale(0.8) translateY(10px);
}
</style>
