<!-- Reusable Card Component -->
<template>
  <div
    :class="[
      'card',
      'bg-white dark:bg-gray-800',
      'border border-gray-200 dark:border-gray-700',
      'transition-all duration-200',
      roundedClass,
      shadowClass,
      paddingClass,
      hoverable && 'hover:shadow-lg hover:-translate-y-0.5 cursor-pointer',
      disabled && 'opacity-60 pointer-events-none',
      className,
    ]"
    @click="hoverable && $emit('click', $event)"
  >
    <!-- Header -->
    <div
      v-if="$slots.header || title || $slots.actions"
      :class="[
        'card-header',
        'flex items-center justify-between',
        'border-b border-gray-200 dark:border-gray-700',
        headerPadding,
      ]"
    >
      <slot name="header">
        <div class="flex items-center gap-3">
          <!-- Icon -->
          <div
            v-if="icon"
            :class="[
              'flex-shrink-0 p-2 rounded-lg',
              iconBgClass,
            ]"
          >
            <component :is="icon" class="h-5 w-5" />
          </div>

          <!-- Title & Description -->
          <div>
            <h3
              v-if="title"
              class="text-lg font-semibold text-gray-900 dark:text-white"
            >
              {{ title }}
            </h3>
            <p
              v-if="description"
              class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"
            >
              {{ description }}
            </p>
          </div>
        </div>
      </slot>

      <!-- Actions Slot -->
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Body -->
    <div :class="['card-body', bodyPadding, bodyClass]">
      <!-- Loading State -->
      <div
        v-if="loading"
        class="flex items-center justify-center py-12"
        role="status"
        aria-live="polite"
      >
        <svg
          class="animate-spin h-8 w-8 text-blue-600"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        <span class="sr-only">Loading content...</span>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="flex flex-col items-center justify-center py-12 text-center"
        role="alert"
      >
        <div
          class="rounded-full bg-red-100 dark:bg-red-900/20 p-3 mb-3"
        >
          <svg
            class="h-6 w-6 text-red-600 dark:text-red-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
          {{ errorTitle || 'Error loading content' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ error }}
        </p>
        <button
          v-if="onRetry"
          @click="onRetry"
          class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium"
        >
          Try again
        </button>
      </div>

      <!-- Content -->
      <slot v-else />
    </div>

    <!-- Footer -->
    <div
      v-if="$slots.footer"
      :class="[
        'card-footer',
        'border-t border-gray-200 dark:border-gray-700',
        footerPadding,
      ]"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  description: String,
  icon: Object,
  iconVariant: {
    type: String,
    default: 'blue',
    validator: (value) =>
      ['blue', 'green', 'purple', 'orange', 'red', 'gray'].includes(value),
  },

  // Spacing
  padding: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value),
  },

  // Appearance
  rounded: {
    type: String,
    default: 'lg',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value),
  },
  shadow: {
    type: String,
    default: 'sm',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value),
  },

  // States
  loading: Boolean,
  error: String,
  errorTitle: String,
  disabled: Boolean,
  hoverable: Boolean,

  // Callbacks
  onRetry: Function,

  // Custom classes
  className: String,
  bodyClass: String,
})

defineEmits(['click'])

// Padding configurations
const paddingConfig = {
  none: '',
  sm: 'p-4',
  md: 'p-6',
  lg: 'p-8',
  xl: 'p-10',
}

const paddingClass = computed(() => {
  return props.padding === 'none' ? '' : paddingConfig[props.padding]
})

const headerPadding = computed(() => {
  const config = {
    none: 'px-0 py-0',
    sm: 'px-4 py-3',
    md: 'px-6 py-4',
    lg: 'px-8 py-5',
    xl: 'px-10 py-6',
  }
  return config[props.padding]
})

const bodyPadding = computed(() => {
  const config = {
    none: 'p-0',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
    xl: 'p-10',
  }
  return config[props.padding]
})

const footerPadding = computed(() => headerPadding.value)

// Rounded configurations
const roundedClass = computed(() => {
  const config = {
    none: '',
    sm: 'rounded',
    md: 'rounded-lg',
    lg: 'rounded-xl',
    xl: 'rounded-2xl',
  }
  return config[props.rounded]
})

// Shadow configurations
const shadowClass = computed(() => {
  const config = {
    none: '',
    sm: 'shadow-sm',
    md: 'shadow-md',
    lg: 'shadow-lg',
    xl: 'shadow-xl',
  }
  return config[props.shadow]
})

// Icon background color
const iconBgClass = computed(() => {
  const variants = {
    blue: 'bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
    green:
      'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400',
    purple:
      'bg-violet-100 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400',
    orange:
      'bg-orange-100 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400',
    red: 'bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400',
    gray: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
  }
  return variants[props.iconVariant] || variants.blue
})
</script>
