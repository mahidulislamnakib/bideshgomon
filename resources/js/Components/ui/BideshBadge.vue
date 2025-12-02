<template>
  <span
    :class="badgeClasses"
    class="inline-flex items-center font-semibold rounded-full transition-all"
  >
    <!-- Icon (left) -->
    <span v-if="$slots.icon" class="mr-1">
      <slot name="icon" />
    </span>
    
    <!-- Content -->
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'warning', 'danger', 'info'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  outlined: {
    type: Boolean,
    default: false
  }
});

const badgeClasses = computed(() => {
  const classes = [];
  
  // Size
  const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs',
    lg: 'px-3 py-1.5 text-sm'
  };
  classes.push(sizeClasses[props.size]);
  
  // Variant (solid or outlined)
  if (props.outlined) {
    const outlineVariantClasses = {
      default: 'border border-gray-300 text-[#505050] bg-transparent',
      primary: 'border border-[#e4282b] text-[#e4282b] bg-transparent',
      success: 'border border-[#64ac44] text-[#64ac44] bg-transparent',
      warning: 'border border-yellow-500 text-yellow-700 bg-transparent',
      danger: 'border border-red-600 text-red-600 bg-transparent',
      info: 'border border-blue-500 text-brand-red-600 bg-transparent'
    };
    classes.push(outlineVariantClasses[props.variant]);
  } else {
    const solidVariantClasses = {
      default: 'bg-gray-100 text-[#505050]',
      primary: 'bg-[#e4282b] text-white',
      success: 'bg-[#64ac44] text-white',
      warning: 'bg-yellow-500 text-white',
      danger: 'bg-red-600 text-white',
      info: 'bg-brand-red-600 text-white'
    };
    classes.push(solidVariantClasses[props.variant]);
  }
  
  return classes.join(' ');
});
</script>
