<template>
  <div
    class="flex flex-col items-center justify-center text-center"
    :class="[paddingClasses, containerClasses]"
  >
    <!-- Icon -->
    <div
      v-if="icon || $slots.icon"
      class="mb-4"
      :class="iconContainerClasses"
    >
      <slot name="icon">
        <component
          :is="icon"
          :class="iconClasses"
          aria-hidden="true"
        />
      </slot>
    </div>

    <!-- Illustration -->
    <div v-if="illustration || $slots.illustration" class="mb-6">
      <slot name="illustration">
        <img
          :src="illustration"
          :alt="title || 'Empty state'"
          class="mx-auto"
          :class="illustrationClasses"
        />
      </slot>
    </div>

    <!-- Title -->
    <h3
      v-if="title"
      class="font-semibold"
      :class="titleClasses"
    >
      {{ title }}
    </h3>

    <!-- Description -->
    <p
      v-if="description"
      class="mt-1"
      :class="descriptionClasses"
    >
      {{ description }}
    </p>

    <!-- Custom content slot -->
    <div v-if="$slots.default" class="mt-4">
      <slot />
    </div>

    <!-- Actions -->
    <div
      v-if="$slots.actions || primaryAction || secondaryAction"
      class="mt-6 flex flex-wrap items-center justify-center gap-3"
    >
      <slot name="actions">
        <!-- Primary action -->
        <button
          v-if="primaryAction"
          type="button"
          class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="primaryButtonClasses"
          @click="$emit('primary-action')"
        >
          <component
            v-if="primaryAction.icon"
            :is="primaryAction.icon"
            class="h-5 w-5"
          />
          {{ primaryAction.label }}
        </button>

        <!-- Secondary action -->
        <button
          v-if="secondaryAction"
          type="button"
          class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="secondaryButtonClasses"
          @click="$emit('secondary-action')"
        >
          <component
            v-if="secondaryAction.icon"
            :is="secondaryAction.icon"
            class="h-5 w-5"
          />
          {{ secondaryAction.label }}
        </button>
      </slot>
    </div>

    <!-- Footer/Help text -->
    <div
      v-if="$slots.footer || helpText"
      class="mt-6"
      :class="footerClasses"
    >
      <slot name="footer">
        <p class="text-sm" :class="helpTextClasses">
          {{ helpText }}
        </p>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  icon: {
    type: [Object, Function],
    default: null
  },
  illustration: {
    type: String,
    default: ''
  },
  primaryAction: {
    type: Object,
    default: null
    // { label: 'Create', icon: PlusIcon }
  },
  secondaryAction: {
    type: Object,
    default: null
    // { label: 'Learn more', icon: ArrowRightIcon }
  },
  helpText: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'filled', 'dashed'].includes(value)
  },
  color: {
    type: String,
    default: 'gray',
    validator: (value) => ['gray', 'primary', 'success', 'warning', 'error'].includes(value)
  }
})

defineEmits(['primary-action', 'secondary-action'])

// Container styling
const containerClasses = computed(() => {
  const variants = {
    default: '',
    bordered: 'border border-gray-200 rounded-xl',
    filled: 'bg-gray-50 rounded-xl',
    dashed: 'border-2 border-dashed border-gray-300 rounded-xl'
  }
  return variants[props.variant]
})

// Padding based on size
const paddingClasses = computed(() => {
  const sizes = {
    sm: 'py-6 px-4',
    md: 'py-12 px-6',
    lg: 'py-16 px-8'
  }
  return sizes[props.size]
})

// Icon container styling
const iconContainerClasses = computed(() => {
  const colors = {
    gray: 'bg-gray-100 text-gray-400',
    primary: 'bg-primary-100 text-primary-500',
    success: 'bg-green-100 text-green-500',
    warning: 'bg-yellow-100 text-yellow-500',
    error: 'bg-red-100 text-red-500'
  }
  
  const sizes = {
    sm: 'p-2 rounded-lg',
    md: 'p-3 rounded-xl',
    lg: 'p-4 rounded-2xl'
  }
  
  return `${colors[props.color]} ${sizes[props.size]}`
})

// Icon sizing
const iconClasses = computed(() => {
  const sizes = {
    sm: 'h-6 w-6',
    md: 'h-10 w-10',
    lg: 'h-12 w-12'
  }
  return sizes[props.size]
})

// Illustration sizing
const illustrationClasses = computed(() => {
  const sizes = {
    sm: 'h-24',
    md: 'h-40',
    lg: 'h-56'
  }
  return sizes[props.size]
})

// Title styling
const titleClasses = computed(() => {
  const colors = {
    gray: 'text-gray-900',
    primary: 'text-primary-900',
    success: 'text-green-900',
    warning: 'text-yellow-900',
    error: 'text-red-900'
  }
  
  const sizes = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  }
  
  return `${colors[props.color]} ${sizes[props.size]}`
})

// Description styling
const descriptionClasses = computed(() => {
  const colors = {
    gray: 'text-gray-500',
    primary: 'text-primary-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600'
  }
  
  const sizes = {
    sm: 'text-xs max-w-xs',
    md: 'text-sm max-w-sm',
    lg: 'text-base max-w-md'
  }
  
  return `${colors[props.color]} ${sizes[props.size]}`
})

// Primary button styling
const primaryButtonClasses = computed(() => {
  const colors = {
    gray: 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-500',
    primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    warning: 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
    error: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500'
  }
  return colors[props.color]
})

// Secondary button styling
const secondaryButtonClasses = computed(() => {
  const colors = {
    gray: 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500',
    primary: 'bg-white text-primary-700 border border-primary-300 hover:bg-primary-50 focus:ring-primary-500',
    success: 'bg-white text-green-700 border border-green-300 hover:bg-green-50 focus:ring-green-500',
    warning: 'bg-white text-yellow-700 border border-yellow-300 hover:bg-yellow-50 focus:ring-yellow-500',
    error: 'bg-white text-red-700 border border-red-300 hover:bg-red-50 focus:ring-red-500'
  }
  return colors[props.color]
})

// Footer styling
const footerClasses = computed(() => {
  return 'border-t border-gray-200 pt-4 w-full max-w-sm'
})

// Help text styling
const helpTextClasses = computed(() => {
  const colors = {
    gray: 'text-gray-400',
    primary: 'text-primary-400',
    success: 'text-green-400',
    warning: 'text-yellow-400',
    error: 'text-red-400'
  }
  return colors[props.color]
})
</script>
