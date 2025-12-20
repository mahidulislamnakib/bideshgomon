<template>
  <span :class="badgeClasses">
    <!-- Leading Icon -->
    <component v-if="leadingIcon" :is="leadingIcon" class="w-3.5 h-3.5" />
    
    <!-- Content -->
    <slot />
    
    <!-- Trailing Icon or Dismiss Button -->
    <component v-if="trailingIcon" :is="trailingIcon" class="w-3.5 h-3.5" />
    
    <button
      v-if="dismissible"
      type="button"
      @click="$emit('dismiss')"
      class="ml-1 hover:opacity-70 focus:outline-none"
    >
      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>
  </span>
</template>

<script setup>
/**
 * World-Class Badge Component
 * Status indicators, labels, tags
 */

import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'neutral',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error', 'info', 'neutral'].includes(value),
  },
  
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  
  rounded: {
    type: Boolean,
    default: true,
  },
  
  leadingIcon: {
    type: Object,
    default: null,
  },
  
  trailingIcon: {
    type: Object,
    default: null,
  },
  
  dismissible: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['dismiss']);

const variantClasses = {
  primary: 'bg-primary-100 text-primary-800 border-primary-200',
  secondary: 'bg-secondary-100 text-secondary-800 border-secondary-200',
  success: 'bg-success-100 text-success-800 border-success-200',
  warning: 'bg-warning-100 text-warning-800 border-warning-200',
  error: 'bg-error-100 text-error-800 border-error-200',
  info: 'bg-info-100 text-info-800 border-info-200',
  neutral: 'bg-neutral-100 text-neutral-800 border-neutral-200',
};

const sizeClasses = {
  sm: 'px-2 py-0.5 text-xs',
  md: 'px-2.5 py-1 text-xs',
  lg: 'px-3 py-1.5 text-sm',
};

const badgeClasses = computed(() => {
  return [
    'badge inline-flex items-center gap-1.5 font-medium border',
    variantClasses[props.variant],
    sizeClasses[props.size],
    props.rounded ? 'rounded-full' : 'rounded-md',
  ];
});
</script>
