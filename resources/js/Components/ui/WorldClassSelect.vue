<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      class="block text-sm font-medium text-neutral-700 mb-1.5"
    >
      {{ label }}
      <span v-if="required" class="text-error-500 ml-0.5">*</span>
    </label>
    
    <!-- Helper Text (Above) -->
    <p v-if="helperText && helperPosition === 'above'" class="text-sm text-neutral-600 mb-2">
      {{ helperText }}
    </p>
    
    <!-- Select Wrapper -->
    <div class="relative">
      <!-- Leading Icon -->
      <div
        v-if="leadingIcon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component :is="leadingIcon" class="w-5 h-5 text-neutral-400" />
      </div>
      
      <!-- Select Element -->
      <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="selectClasses"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
        class="touch-manipulation"
      >
        <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
        
        <template v-if="grouped">
          <optgroup
            v-for="(group, groupIndex) in options"
            :key="groupIndex"
            :label="group.label"
          >
            <option
              v-for="option in group.options"
              :key="option[valueKey]"
              :value="option[valueKey]"
            >
              {{ option[labelKey] }}
            </option>
          </optgroup>
        </template>
        
        <template v-else>
          <option
            v-for="option in options"
            :key="option[valueKey]"
            :value="option[valueKey]"
          >
            {{ option[labelKey] }}
          </option>
        </template>
      </select>
      
      <!-- Chevron Icon -->
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
    
    <!-- Helper Text (Below) / Error Message -->
    <p
      v-if="error || (helperText && helperPosition === 'below')"
      :class="[
        'text-sm mt-1.5',
        error ? 'text-error-600' : 'text-neutral-600'
      ]"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
/**
 * World-Class Select Component
 * Accessible, Styled, Flexible
 */

import { computed, ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  
  label: {
    type: String,
    default: '',
  },
  
  placeholder: {
    type: String,
    default: 'Select an option',
  },
  
  options: {
    type: Array,
    required: true,
    // Array of objects: [{ value: 1, label: 'Option 1' }]
    // Or grouped: [{ label: 'Group 1', options: [...] }]
  },
  
  valueKey: {
    type: String,
    default: 'value',
  },
  
  labelKey: {
    type: String,
    default: 'label',
  },
  
  grouped: {
    type: Boolean,
    default: false,
  },
  
  helperText: {
    type: String,
    default: '',
  },
  
  helperPosition: {
    type: String,
    default: 'below',
    validator: (value) => ['above', 'below'].includes(value),
  },
  
  error: {
    type: String,
    default: '',
  },
  
  disabled: {
    type: Boolean,
    default: false,
  },
  
  required: {
    type: Boolean,
    default: false,
  },
  
  leadingIcon: {
    type: Object,
    default: null,
  },
  
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus']);

const selectId = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`);
const isFocused = ref(false);

const sizeClasses = {
  sm: 'px-3 py-2 text-sm',
  md: 'px-4 py-2.5 text-base',
  lg: 'px-5 py-3 text-lg',
};

const selectClasses = computed(() => {
  return [
    'w-full rounded-lg border bg-white transition-all duration-200',
    'focus:outline-none focus:ring-4 focus:ring-offset-0',
    'appearance-none cursor-pointer',
    
    sizeClasses[props.size],
    
    props.leadingIcon ? 'pl-10' : '',
    'pr-10', // Always add right padding for chevron
    
    props.error 
      ? 'border-error-500 focus:border-error-500 focus:ring-error-100' 
      : 'border-neutral-300 focus:border-primary-500 focus:ring-primary-100',
    
    props.disabled 
      ? 'bg-neutral-100 cursor-not-allowed opacity-60' 
      : '',
  ];
});

const handleChange = (event) => {
  const value = event.target.value;
  emit('update:modelValue', value);
  emit('change', value);
};

const handleBlur = (event) => {
  isFocused.value = false;
  emit('blur', event);
};

const handleFocus = (event) => {
  isFocused.value = true;
  emit('focus', event);
};
</script>
