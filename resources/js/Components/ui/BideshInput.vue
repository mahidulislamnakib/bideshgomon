<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium text-[#505050] mb-2"
    >
      {{ label }}
      <span v-if="required" class="text-[#e4282b] ml-1">*</span>
    </label>
    
    <!-- Input Field -->
    <div class="relative">
      <!-- Icon (left) -->
      <div
        v-if="$slots.iconLeft"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"
      >
        <slot name="iconLeft" />
      </div>
      
      <!-- Input Element -->
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :class="inputClasses"
        class="block w-full rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      
      <!-- Icon (right) -->
      <div
        v-if="$slots.iconRight"
        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400"
      >
        <slot name="iconRight" />
      </div>
    </div>
    
    <!-- Helper Text / Error Message -->
    <p v-if="helperText && !error" class="mt-1.5 text-sm text-gray-500">
      {{ helperText }}
    </p>
    <p v-if="error" class="mt-1.5 text-sm text-[#e4282b] flex items-center">
      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      {{ error }}
    </p>
    <p v-if="success" class="mt-1.5 text-sm text-[#64ac44] flex items-center">
      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
      {{ success }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  success: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const inputId = computed(() => {
  return `input-${Math.random().toString(36).substr(2, 9)}`;
});

const inputClasses = computed(() => {
  const classes = [];
  
  // Size
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-4 py-3 text-base'
  };
  classes.push(sizeClasses[props.size]);
  
  // Icon padding
  if (props.$slots?.iconLeft) {
    classes.push('pl-10');
  }
  if (props.$slots?.iconRight) {
    classes.push('pr-10');
  }
  
  // State
  if (props.error) {
    classes.push('border-[#e4282b] focus:ring-[#e4282b] focus:border-[#e4282b]');
  } else if (props.success) {
    classes.push('border-[#64ac44] focus:ring-[#64ac44] focus:border-[#64ac44]');
  } else {
    classes.push('border-gray-300 focus:ring-[#e4282b] focus:border-[#e4282b]');
  }
  
  // Disabled/readonly
  if (props.disabled || props.readonly) {
    classes.push('bg-gray-50 text-gray-500 cursor-not-allowed');
  } else {
    classes.push('bg-white text-[#505050]');
  }
  
  return classes.join(' ');
});
</script>
