<template>
  <div
    :class="cardClasses"
    class="bg-white rounded-lg transition-all duration-200"
    @click="handleClick"
  >
    <!-- Header (Optional) -->
    <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200">
      <slot name="header">
        <h3 :class="titleClasses" class="font-semibold text-[#505050]">
          {{ title }}
        </h3>
      </slot>
    </div>
    
    <!-- Body -->
    <div :class="bodyPaddingClass">
      <slot />
    </div>
    
    <!-- Footer (Optional) -->
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'elevated', 'flat', 'hoverable'].includes(value)
  },
  padding: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  clickable: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
  if (props.clickable) {
    emit('click', event);
  }
};

const cardClasses = computed(() => {
  const classes = [];
  
  // Variant
  const variantClasses = {
    default: 'border border-gray-200 shadow-sm',
    bordered: 'border-2 border-gray-300',
    elevated: 'shadow-md',
    flat: 'border-0',
    hoverable: 'border border-gray-200 shadow-sm hover:shadow-md cursor-pointer'
  };
  classes.push(variantClasses[props.variant]);
  
  // Clickable
  if (props.clickable) {
    classes.push('cursor-pointer hover:shadow-md');
  }
  
  return classes.join(' ');
});

const bodyPaddingClass = computed(() => {
  const paddingClasses = {
    none: '',
    sm: 'p-3',
    md: 'p-6',
    lg: 'p-8',
    xl: 'p-10'
  };
  return paddingClasses[props.padding];
});

const titleClasses = computed(() => {
  return 'text-lg';
});
</script>
