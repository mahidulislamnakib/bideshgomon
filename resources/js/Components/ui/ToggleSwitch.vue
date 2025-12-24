<template>
  <label
    class="inline-flex items-center gap-3"
    :class="{ 'cursor-not-allowed opacity-50': disabled, 'cursor-pointer': !disabled }"
  >
    <!-- Label (before) -->
    <span
      v-if="label && labelPosition === 'before'"
      class="text-sm font-medium"
      :class="labelClasses"
    >
      {{ label }}
    </span>

    <!-- Toggle Container -->
    <button
      type="button"
      role="switch"
      :aria-checked="modelValue"
      :aria-label="label || 'Toggle'"
      :disabled="disabled"
      class="relative inline-flex shrink-0 rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
      :class="[
        sizeClasses.track,
        modelValue ? activeTrackClass : inactiveTrackClass,
        disabled ? 'cursor-not-allowed' : 'cursor-pointer',
        'focus-visible:ring-blue-500'
      ]"
      @click="toggle"
    >
      <!-- Toggle Knob -->
      <span
        class="pointer-events-none inline-block rounded-full bg-white shadow-lg ring-0 transition-transform duration-200 ease-in-out"
        :class="[
          sizeClasses.knob,
          modelValue ? translateClass : 'translate-x-0.5'
        ]"
      >
        <!-- Icons in knob -->
        <span
          v-if="showIcons"
          class="absolute inset-0 flex items-center justify-center transition-opacity"
          :class="modelValue ? 'opacity-0' : 'opacity-100'"
        >
          <XMarkIcon :class="sizeClasses.icon" class="text-gray-400" />
        </span>
        <span
          v-if="showIcons"
          class="absolute inset-0 flex items-center justify-center transition-opacity"
          :class="modelValue ? 'opacity-100' : 'opacity-0'"
        >
          <CheckIcon :class="sizeClasses.icon" class="text-blue-600" />
        </span>
      </span>

      <!-- Track Icons -->
      <span v-if="showTrackIcons" class="absolute inset-0 flex items-center justify-between px-1">
        <CheckIcon
          :class="[sizeClasses.trackIcon, modelValue ? 'text-white' : 'text-transparent']"
        />
        <XMarkIcon
          :class="[sizeClasses.trackIcon, !modelValue ? 'text-gray-400' : 'text-transparent']"
        />
      </span>
    </button>

    <!-- Label (after) -->
    <span
      v-if="label && labelPosition === 'after'"
      class="text-sm font-medium"
      :class="labelClasses"
    >
      {{ label }}
    </span>

    <!-- Description -->
    <span v-if="description" class="text-sm text-gray-500 dark:text-gray-400">
      {{ description }}
    </span>
  </label>
</template>

<script setup>
import { computed } from 'vue'
import { CheckIcon, XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  // Toggle state
  modelValue: {
    type: Boolean,
    default: false
  },
  // Label text
  label: {
    type: String,
    default: ''
  },
  // Label position
  labelPosition: {
    type: String,
    default: 'after',
    validator: (v) => ['before', 'after'].includes(v)
  },
  // Description text
  description: {
    type: String,
    default: ''
  },
  // Size variant
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  // Color when active
  color: {
    type: String,
    default: 'blue',
    validator: (v) => ['blue', 'green', 'red', 'purple', 'orange', 'gray'].includes(v)
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Show icons inside knob
  showIcons: {
    type: Boolean,
    default: false
  },
  // Show icons on track
  showTrackIcons: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: {
      track: 'h-5 w-9',
      knob: 'h-4 w-4',
      icon: 'w-2.5 h-2.5',
      trackIcon: 'w-2.5 h-2.5'
    },
    md: {
      track: 'h-6 w-11',
      knob: 'h-5 w-5',
      icon: 'w-3 h-3',
      trackIcon: 'w-3 h-3'
    },
    lg: {
      track: 'h-7 w-14',
      knob: 'h-6 w-6',
      icon: 'w-3.5 h-3.5',
      trackIcon: 'w-3.5 h-3.5'
    }
  }
  return sizes[props.size]
})

// Translate class for knob position
const translateClass = computed(() => {
  const translates = {
    sm: 'translate-x-4',
    md: 'translate-x-5',
    lg: 'translate-x-7'
  }
  return translates[props.size]
})

// Active track color
const activeTrackClass = computed(() => {
  const colors = {
    blue: 'bg-blue-600 dark:bg-blue-500',
    green: 'bg-green-600 dark:bg-green-500',
    red: 'bg-red-600 dark:bg-red-500',
    purple: 'bg-purple-600 dark:bg-purple-500',
    orange: 'bg-orange-500 dark:bg-orange-400',
    gray: 'bg-gray-600 dark:bg-gray-500'
  }
  return colors[props.color]
})

// Inactive track color
const inactiveTrackClass = 'bg-gray-200 dark:bg-gray-700'

// Label classes
const labelClasses = computed(() => {
  return props.disabled
    ? 'text-gray-400 dark:text-gray-500'
    : 'text-gray-900 dark:text-white'
})

// Toggle function
function toggle() {
  if (props.disabled) return
  
  const newValue = !props.modelValue
  emit('update:modelValue', newValue)
  emit('change', newValue)
}
</script>
