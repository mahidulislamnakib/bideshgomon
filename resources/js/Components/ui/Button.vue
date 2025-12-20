<template>
  <component
    :is="componentType"
    :href="href"
    :type="buttonType"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
    class="touch-manipulation no-tap-highlight"
  >
    <!-- Loading Spinner -->
    <span v-if="loading" class="spinner" :class="spinnerSizeClass"></span>
    
    <!-- Leading Icon -->
    <component v-if="leadingIcon && !loading" :is="leadingIcon" :class="iconSizeClass" />
    
    <!-- Default Slot (Text Content) -->
    <span v-if="$slots.default">
      <slot />
    </span>
    
    <!-- Trailing Icon -->
    <component v-if="trailingIcon && !loading" :is="trailingIcon" :class="iconSizeClass" />
  </component>
</template>

<script setup>
/**
 * World-Class Button Component
 * International UI/UX Standards
 * Mobile-First, Accessible, Consistent
 */

import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  // Variant
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'accent', 'ghost', 'outline', 'danger', 'success', 'warning'].includes(value),
  },
  
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value),
  },
  
  // Type (for button element)
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value),
  },
  
  // Link behavior
  href: {
    type: String,
    default: null,
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false,
  },
  
  loading: {
    type: Boolean,
    default: false,
  },
  
  // Icons
  leadingIcon: {
    type: Object,
    default: null,
  },
  
  trailingIcon: {
    type: Object,
    default: null,
  },
  
  // Full width
  fullWidth: {
    type: Boolean,
    default: false,
  },
  
  // Icon only (no text)
  iconOnly: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['click']);

// Determine component type
const componentType = computed(() => {
  if (props.href) {
    return Link;
  }
  return 'button';
});

const buttonType = computed(() => {
  if (props.href) return undefined;
  return props.type;
});

// Variant classes
const variantClasses = {
  primary: 'bg-primary-500 text-white hover:bg-primary-600 active:bg-primary-700 focus-visible:ring-primary-100 shadow-sm hover:shadow-md',
  secondary: 'bg-secondary-500 text-white hover:bg-secondary-600 active:bg-secondary-700 focus-visible:ring-secondary-100 shadow-sm hover:shadow-md',
  accent: 'bg-accent-500 text-white hover:bg-accent-600 active:bg-accent-700 focus-visible:ring-accent-100 shadow-sm hover:shadow-md',
  ghost: 'bg-transparent text-neutral-700 hover:bg-neutral-100 active:bg-neutral-200 focus-visible:ring-neutral-100',
  outline: 'bg-transparent border-2 border-neutral-300 text-neutral-700 hover:border-neutral-400 hover:bg-neutral-50 active:bg-neutral-100 focus-visible:ring-neutral-100',
  danger: 'bg-error-500 text-white hover:bg-error-600 active:bg-error-700 focus-visible:ring-error-100 shadow-sm hover:shadow-md',
  success: 'bg-success-500 text-white hover:bg-success-600 active:bg-success-700 focus-visible:ring-success-100 shadow-sm hover:shadow-md',
  warning: 'bg-warning-500 text-white hover:bg-warning-600 active:bg-warning-700 focus-visible:ring-warning-100 shadow-sm hover:shadow-md',
};

// Size classes
const sizeClasses = {
  xs: 'px-2.5 py-1.5 text-xs',
  sm: 'px-3 py-2 text-sm',
  md: 'px-4 py-2.5 text-sm',
  lg: 'px-5 py-3 text-base',
  xl: 'px-6 py-4 text-lg',
};

// Icon-only size classes
const iconOnlySizeClasses = {
  xs: 'p-1.5',
  sm: 'p-2',
  md: 'p-2.5',
  lg: 'p-3',
  xl: 'p-4',
};

// Icon size classes
const iconSizeClasses = {
  xs: 'w-3.5 h-3.5',
  sm: 'w-4 h-4',
  md: 'w-5 h-5',
  lg: 'w-5 h-5',
  xl: 'w-6 h-6',
};

// Spinner size classes
const spinnerSizeClasses = {
  xs: 'w-3 h-3 border',
  sm: 'w-3.5 h-3.5 border',
  md: 'w-4 h-4 border-2',
  lg: 'w-5 h-5 border-2',
  xl: 'w-6 h-6 border-2',
};

const buttonClasses = computed(() => {
  return [
    // Base button styles
    'inline-flex items-center justify-center gap-2 font-medium rounded-lg',
    'transition-all duration-200 focus:outline-none focus-visible:ring-4 focus-visible:ring-offset-2',
    'disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none',
    
    // Variant
    variantClasses[props.variant],
    
    // Size
    props.iconOnly ? iconOnlySizeClasses[props.size] : sizeClasses[props.size],
    
    // Full width
    props.fullWidth ? 'w-full' : '',
  ];
});

const iconSizeClass = computed(() => iconSizeClasses[props.size]);
const spinnerSizeClass = computed(() => spinnerSizeClasses[props.size]);

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};
</script>
