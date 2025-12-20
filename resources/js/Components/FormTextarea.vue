<script setup>
/**
 * FormTextarea - Standardized Textarea Component
 * BideshGomon Design System
 * 
 * Features:
 * - Consistent styling with FormInput
 * - Character count option
 * - Auto-resize option
 * - Label above textarea pattern
 * - Error states
 * - Accessible (WCAG compliant)
 */

import { computed, ref, onMounted, watch } from 'vue';
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
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
  readonly: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
  rows: {
    type: [Number, String],
    default: 4,
  },
  maxlength: {
    type: [Number, String],
    default: null,
  },
  showCount: {
    type: Boolean,
    default: false,
  },
  autoResize: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: () => `textarea-${Math.random().toString(36).substr(2, 9)}`,
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const textareaRef = ref(null);

// Character count
const characterCount = computed(() => {
  return props.modelValue?.length || 0;
});

const isNearLimit = computed(() => {
  if (!props.maxlength) return false;
  return characterCount.value >= parseInt(props.maxlength) * 0.9;
});

// Textarea classes
const textareaClasses = computed(() => {
  const classes = [
    'form-textarea',
    'w-full',
    'text-gray-900',
    'resize-vertical',
  ];
  
  // States
  if (props.error) classes.push('form-input-error');
  if (props.disabled) classes.push('opacity-70 cursor-not-allowed');
  if (props.autoResize) classes.push('resize-none overflow-hidden');
  
  return classes;
});

// Auto-resize functionality
const adjustHeight = () => {
  if (props.autoResize && textareaRef.value) {
    textareaRef.value.style.height = 'auto';
    textareaRef.value.style.height = textareaRef.value.scrollHeight + 'px';
  }
};

// Watch for value changes to adjust height
watch(() => props.modelValue, () => {
  if (props.autoResize) {
    adjustHeight();
  }
});

// Focus textarea on mount if autofocus
onMounted(() => {
  if (props.autofocus && textareaRef.value) {
    textareaRef.value.focus();
  }
  if (props.autoResize) {
    adjustHeight();
  }
});

// Expose methods
defineExpose({
  focus: () => textareaRef.value?.focus(),
  blur: () => textareaRef.value?.blur(),
});
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
    
    <!-- Textarea -->
    <textarea
      :id="id"
      ref="textareaRef"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :required="required"
      :rows="rows"
      :maxlength="maxlength"
      :class="textareaClasses"
      :aria-invalid="!!error"
      :aria-describedby="error ? `${id}-error` : helper ? `${id}-helper` : undefined"
      @input="emit('update:modelValue', $event.target.value); adjustHeight()"
      @blur="emit('blur', $event)"
      @focus="emit('focus', $event)"
    />
    
    <!-- Character Count -->
    <div v-if="showCount && maxlength" class="flex justify-end mt-1">
      <span
        class="text-xs"
        :class="isNearLimit ? 'text-amber-600' : 'text-gray-400'"
      >
        {{ characterCount }}/{{ maxlength }}
      </span>
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
