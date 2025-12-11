<!-- Reusable Base Button Component -->
<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :href="tag === 'a' ? href : undefined"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <!-- Loading Spinner -->
    <span
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center"
    >
      <svg
        class="animate-spin h-5 w-5"
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
    </span>

    <!-- Content (hidden when loading) -->
    <span :class="{ 'opacity-0': loading }">
      <!-- Icon (Left) -->
      <component
        v-if="iconLeft"
        :is="iconLeft"
        :class="[
          'flex-shrink-0',
          sizeConfig[size].iconSize,
          $slots.default && 'mr-2',
        ]"
      />

      <!-- Default Slot -->
      <slot />

      <!-- Icon (Right) -->
      <component
        v-if="iconRight"
        :is="iconRight"
        :class="[
          'flex-shrink-0',
          sizeConfig[size].iconSize,
          $slots.default && 'ml-2',
        ]"
      />

      <!-- Badge -->
      <span
        v-if="badge"
        :class="[
          'ml-2 px-2 py-0.5 text-xs font-bold rounded-full',
          badgeClasses,
        ]"
      >
        {{ badge }}
      </span>
    </span>
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  // Variants
  variant: {
    type: String,
    default: 'primary',
    validator: (value) =>
      ['primary', 'secondary', 'success', 'danger', 'ghost', 'link'].includes(
        value
      ),
  },

  // Sizes
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value),
  },

  // Tag type
  tag: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'a', Link].includes(value),
  },

  // Button type
  type: {
    type: String,
    default: 'button',
  },

  // Link href
  href: String,

  // Icons
  iconLeft: Object,
  iconRight: Object,

  // Badge
  badge: [String, Number],

  // States
  disabled: Boolean,
  loading: Boolean,
  block: Boolean, // Full width

  // Styles
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'full'].includes(value),
  },
})

const emit = defineEmits(['click'])

// Size configurations
const sizeConfig = {
  xs: {
    padding: 'px-2.5 py-1.5',
    text: 'text-xs',
    iconSize: 'h-3.5 w-3.5',
  },
  sm: {
    padding: 'px-3 py-2',
    text: 'text-sm',
    iconSize: 'h-4 w-4',
  },
  md: {
    padding: 'px-4 py-2.5',
    text: 'text-sm',
    iconSize: 'h-5 w-5',
  },
  lg: {
    padding: 'px-5 py-3',
    text: 'text-base',
    iconSize: 'h-5 w-5',
  },
  xl: {
    padding: 'px-6 py-3.5',
    text: 'text-base',
    iconSize: 'h-6 w-6',
  },
}

// Variant styles
const variantConfig = {
  primary: {
    base: 'bg-blue-600 text-white shadow-sm',
    hover: 'hover:bg-blue-700',
    focus: 'focus:ring-blue-500',
    disabled: 'disabled:bg-blue-300',
  },
  secondary: {
    base: 'bg-white text-gray-700 border border-gray-300 shadow-sm',
    hover: 'hover:bg-gray-50',
    focus: 'focus:ring-gray-500',
    disabled: 'disabled:bg-gray-100 disabled:text-gray-400',
  },
  success: {
    base: 'bg-emerald-600 text-white shadow-sm',
    hover: 'hover:bg-emerald-700',
    focus: 'focus:ring-emerald-500',
    disabled: 'disabled:bg-emerald-300',
  },
  danger: {
    base: 'bg-red-600 text-white shadow-sm',
    hover: 'hover:bg-red-700',
    focus: 'focus:ring-red-500',
    disabled: 'disabled:bg-red-300',
  },
  ghost: {
    base: 'bg-transparent text-gray-700',
    hover: 'hover:bg-gray-100',
    focus: 'focus:ring-gray-500',
    disabled: 'disabled:text-gray-400',
  },
  link: {
    base: 'bg-transparent text-blue-600',
    hover: 'hover:text-blue-700 hover:underline',
    focus: 'focus:ring-blue-500',
    disabled: 'disabled:text-blue-300',
  },
}

// Rounded styles
const roundedConfig = {
  sm: 'rounded',
  md: 'rounded-lg',
  lg: 'rounded-xl',
  full: 'rounded-full',
}

// Badge styles based on variant
const badgeClasses = computed(() => {
  const badgeVariants = {
    primary: 'bg-blue-500 text-white',
    secondary: 'bg-gray-200 text-gray-700',
    success: 'bg-emerald-500 text-white',
    danger: 'bg-red-500 text-white',
    ghost: 'bg-gray-200 text-gray-700',
    link: 'bg-blue-100 text-blue-700',
  }
  return badgeVariants[props.variant]
})

// Combined button classes
const buttonClasses = computed(() => {
  const size = sizeConfig[props.size]
  const variant = variantConfig[props.variant]
  const rounded = roundedConfig[props.rounded]

  return [
    // Base styles
    'inline-flex items-center justify-center',
    'font-medium',
    'transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    'relative', // For loading spinner positioning

    // Size
    size.padding,
    size.text,

    // Variant
    variant.base,
    variant.hover,
    variant.focus,
    variant.disabled,

    // Rounded
    rounded,

    // States
    {
      'w-full': props.block,
      'cursor-not-allowed opacity-60': props.disabled || props.loading,
    },
  ]
})

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>
