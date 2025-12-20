<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="textareaId"
      class="block text-sm font-medium text-neutral-700 mb-1.5"
    >
      {{ label }}
      <span v-if="required" class="text-error-500 ml-0.5">*</span>
    </label>
    
    <!-- Helper Text (Above) -->
    <p v-if="helperText && helperPosition === 'above'" class="text-sm text-neutral-600 mb-2">
      {{ helperText }}
    </p>
    
    <!-- Textarea Wrapper -->
    <div class="relative">
      <textarea
        :id="textareaId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        :maxlength="maxlength"
        :class="textareaClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        class="touch-manipulation resize-none"
        :style="{ height: autoResize ? 'auto' : undefined }"
      ></textarea>
      
      <!-- Character Count -->
      <div
        v-if="maxlength || showCharacterCount"
        class="absolute bottom-2 right-2 text-xs px-2 py-1 bg-white rounded"
        :class="isNearLimit ? 'text-warning-600' : 'text-neutral-400'"
      >
        {{ characterCount }}{{ maxlength ? ` / ${maxlength}` : '' }}
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
 * World-Class Textarea Component
 * Auto-resize, Character count, Accessible
 */

import { computed, ref, watch, nextTick } from 'vue';

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
    default: '',
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
  
  readonly: {
    type: Boolean,
    default: false,
  },
  
  required: {
    type: Boolean,
    default: false,
  },
  
  rows: {
    type: [Number, String],
    default: 4,
  },
  
  maxlength: {
    type: [Number, String],
    default: undefined,
  },
  
  showCharacterCount: {
    type: Boolean,
    default: false,
  },
  
  autoResize: {
    type: Boolean,
    default: false,
  },
  
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const textareaId = computed(() => `textarea-${Math.random().toString(36).substr(2, 9)}`);
const isFocused = ref(false);
const textareaRef = ref(null);

const characterCount = computed(() => {
  return String(props.modelValue || '').length;
});

const isNearLimit = computed(() => {
  if (!props.maxlength) return false;
  return characterCount.value > props.maxlength * 0.9;
});

const sizeClasses = {
  sm: 'px-3 py-2 text-sm',
  md: 'px-4 py-2.5 text-base',
  lg: 'px-5 py-3 text-lg',
};

const textareaClasses = computed(() => {
  return [
    'w-full rounded-lg border bg-white transition-all duration-200',
    'focus:outline-none focus:ring-4 focus:ring-offset-0',
    'placeholder-neutral-400',
    
    sizeClasses[props.size],
    
    props.maxlength || props.showCharacterCount ? 'pb-8' : '',
    
    props.error 
      ? 'border-error-500 focus:border-error-500 focus:ring-error-100' 
      : 'border-neutral-300 focus:border-primary-500 focus:ring-primary-100',
    
    props.disabled 
      ? 'bg-neutral-100 cursor-not-allowed opacity-60' 
      : '',
    
    props.readonly 
      ? 'bg-neutral-50 cursor-default' 
      : '',
  ];
});

const handleInput = (event) => {
  emit('update:modelValue', event.target.value);
  
  if (props.autoResize) {
    nextTick(() => {
      if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
        textareaRef.value.style.height = textareaRef.value.scrollHeight + 'px';
      }
    });
  }
};

const handleBlur = (event) => {
  isFocused.value = false;
  emit('blur', event);
};

const handleFocus = (event) => {
  isFocused.value = true;
  emit('focus', event);
};

// Auto-resize on mount if enabled
watch(() => props.modelValue, () => {
  if (props.autoResize) {
    nextTick(() => {
      if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
        textareaRef.value.style.height = textareaRef.value.scrollHeight + 'px';
      }
    });
  }
}, { immediate: true });
</script>
