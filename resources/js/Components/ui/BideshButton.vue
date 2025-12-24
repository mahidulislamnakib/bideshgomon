<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    class="inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
    @click="handleClick"
  >
    <!-- Loading Spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    <!-- Icon (left) -->
    <span v-if="$slots.iconLeft" class="mr-2">
      <slot name="iconLeft" />
    </span>
    
    <!-- Content -->
    <slot />
    
    <!-- Icon (right) -->
    <span v-if="$slots.iconRight" class="ml-2">
      <slot name="iconRight" />
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'secondary', 'outline-primary', 'outline-success', 'outline-secondary', 'ghost', 'danger'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};

const buttonClasses = computed(() => {
  const classes = [];
  
  // Width
  if (props.fullWidth) {
    classes.push('w-full');
  }
  
  // Size
  const sizeClasses = {
    xs: 'px-2.5 py-1.5 text-xs',
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-2.5 text-base',
    xl: 'px-6 py-3 text-base'
  };
  classes.push(sizeClasses[props.size]);
  
  // Variant
  const variantClasses = {
    primary: 'bg-[#e4282b] text-white hover:bg-growth-700 focus:ring-[#e4282b] shadow-sm',
    success: 'bg-[#64ac44] text-white hover:bg-green-700 focus:ring-[#64ac44] shadow-sm',
    secondary: 'bg-[#505050] text-white hover:bg-gray-700 focus:ring-[#505050] shadow-sm',
    'outline-primary': 'border-2 border-[#e4282b] text-[#e4282b] hover:bg-[#e4282b] hover:text-white focus:ring-[#e4282b]',
    'outline-success': 'border-2 border-[#64ac44] text-[#64ac44] hover:bg-[#64ac44] hover:text-white focus:ring-[#64ac44]',
    'outline-secondary': 'border-2 border-[#505050] text-[#505050] hover:bg-[#505050] hover:text-white focus:ring-[#505050]',
    ghost: 'text-[#505050] hover:bg-gray-100 focus:ring-gray-300',
    danger: 'bg-red-600 text-white hover:bg-growth-700 focus:ring-red-500 shadow-sm'
  };
  classes.push(variantClasses[props.variant]);
  
  return classes.join(' ');
});
</script>
