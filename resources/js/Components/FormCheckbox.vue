<script setup>
/**
 * FormCheckbox - Standardized Checkbox Component
 * BideshGomon Design System
 * 
 * Features:
 * - Consistent styling with upwork-green
 * - Label inline with checkbox
 * - Description text support
 * - Error states
 * - Accessible (WCAG compliant)
 */

import { computed } from 'vue';
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: [Boolean, Array],
    default: false,
  },
  value: {
    type: [String, Number, Boolean],
    default: true,
  },
  label: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
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
  id: {
    type: String,
    default: () => `checkbox-${Math.random().toString(36).substr(2, 9)}`,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

// Handle both single boolean and array of values
const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(props.value);
  }
  return props.modelValue;
});

const handleChange = (event) => {
  const checked = event.target.checked;
  
  if (Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue];
    if (checked) {
      newValue.push(props.value);
    } else {
      const index = newValue.indexOf(props.value);
      if (index > -1) {
        newValue.splice(index, 1);
      }
    }
    emit('update:modelValue', newValue);
  } else {
    emit('update:modelValue', checked);
  }
  
  emit('change', checked);
};
</script>

<template>
  <div class="form-group">
    <div class="form-check">
      <input
        :id="id"
        type="checkbox"
        :checked="isChecked"
        :value="value"
        :disabled="disabled"
        :required="required"
        class="form-check-input"
        :class="{ 'form-input-error': error }"
        :aria-invalid="!!error"
        :aria-describedby="error ? `${id}-error` : description ? `${id}-desc` : undefined"
        @change="handleChange"
      />
      
      <div class="form-check-content">
        <label
          :for="id"
          class="form-check-label"
          :class="{ 'form-label-required': required, 'opacity-60': disabled }"
        >
          {{ label }}
        </label>
        
        <p v-if="description" :id="`${id}-desc`" class="form-check-description">
          {{ description }}
        </p>
      </div>
    </div>
    
    <!-- Error Message -->
    <p v-if="error" :id="`${id}-error`" class="form-error mt-1" role="alert">
      <ExclamationCircleIcon class="form-error-icon" />
      <span>{{ error }}</span>
    </p>
  </div>
</template>

<style scoped>
.form-check-input {
  @apply h-5 w-5 rounded border-gray-300 text-upwork-green focus:ring-upwork-green cursor-pointer flex-shrink-0;
}

.form-check-input:disabled {
  @apply cursor-not-allowed opacity-60;
}

.form-check-content {
  @apply flex flex-col;
}

.form-check-label {
  @apply text-sm font-medium text-gray-700 cursor-pointer select-none;
}

.form-check-description {
  @apply text-xs text-gray-500 mt-0.5;
}
</style>
