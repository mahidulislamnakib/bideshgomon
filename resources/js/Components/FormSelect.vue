<script setup>
/**
 * FormSelect - Standardized Select Component
 * BideshGomon Design System
 * 
 * Features:
 * - Consistent styling with FormInput
 * - Native select for accessibility
 * - Label above select pattern
 * - Error states
 * - Accessible (WCAG compliant)
 */

import { computed } from 'vue';
import { ChevronDownIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, Object],
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
    // Options should be: [{ value: '', label: '' }] or ['option1', 'option2']
  },
  valueKey: {
    type: String,
    default: 'value',
  },
  labelKey: {
    type: String,
    default: 'label',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Select an option',
  },
  error: {
    type: String,
    default: '',
  },
  helper: {
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
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  id: {
    type: String,
    default: () => `select-${Math.random().toString(36).substr(2, 9)}`,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

// Get option value and label (handles both object and primitive arrays)
const getOptionValue = (option) => {
  if (typeof option === 'object') {
    return option[props.valueKey];
  }
  return option;
};

const getOptionLabel = (option) => {
  if (typeof option === 'object') {
    return option[props.labelKey];
  }
  return option;
};

// Select classes
const selectClasses = computed(() => {
  const classes = [
    'form-select',
    'w-full',
    'text-gray-900',
    'appearance-none',
    'pr-10', // Space for chevron
  ];
  
  // Size
  if (props.size === 'sm') classes.push('form-input-sm');
  if (props.size === 'lg') classes.push('form-input-lg');
  
  // States
  if (props.error) classes.push('form-input-error');
  if (props.disabled) classes.push('opacity-70 cursor-not-allowed');
  
  // Placeholder styling (when no value selected)
  if (!props.modelValue) classes.push('text-gray-400');
  
  return classes;
});

const handleChange = (event) => {
  emit('update:modelValue', event.target.value);
  emit('change', event.target.value);
};
</script>

<template>
  <div class="form-group">
    <!-- Label -->
    <label
      v-if="label"
      :for="id"
      class="form-label"
      :class="{ 'form-label-required': required }"
    >
      {{ label }}
    </label>
    
    <!-- Select Wrapper -->
    <div class="form-input-wrapper">
      <select
        :id="id"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="selectClasses"
        :aria-invalid="!!error"
        :aria-describedby="error ? `${id}-error` : helper ? `${id}-helper` : undefined"
        @change="handleChange"
      >
        <!-- Placeholder Option -->
        <option v-if="placeholder" value="" disabled>
          {{ placeholder }}
        </option>
        
        <!-- Options -->
        <option
          v-for="(option, index) in options"
          :key="index"
          :value="getOptionValue(option)"
        >
          {{ getOptionLabel(option) }}
        </option>
      </select>
      
      <!-- Chevron Icon -->
      <div class="form-input-icon-right pointer-events-none">
        <ChevronDownIcon class="w-5 h-5 text-gray-400" />
      </div>
    </div>
    
    <!-- Error Message -->
    <p v-if="error" :id="`${id}-error`" class="form-error" role="alert">
      <ExclamationCircleIcon class="form-error-icon" />
      <span>{{ error }}</span>
    </p>
    
    <!-- Helper Text -->
    <p v-else-if="helper" :id="`${id}-helper`" class="form-helper">
      {{ helper }}
    </p>
  </div>
</template>
