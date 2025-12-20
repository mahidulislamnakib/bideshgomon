<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium text-neutral-700 mb-1.5"
    >
      {{ label }}
      <span v-if="required" class="text-error-500 ml-0.5">*</span>
    </label>
    
    <!-- Helper Text (Above) -->
    <p v-if="helperText && helperPosition === 'above'" class="text-sm text-neutral-600 mb-2">
      {{ helperText }}
    </p>
    
    <!-- Input Wrapper -->
    <div class="relative">
      <!-- Leading Icon/Addon -->
      <div
        v-if="leadingIcon || leadingAddon"
        class="absolute inset-y-0 left-0 flex items-center"
        :class="leadingIcon ? 'pl-3 pointer-events-none' : ''"
      >
        <component v-if="leadingIcon" :is="leadingIcon" class="w-5 h-5 text-neutral-400" />
        <span v-if="leadingAddon" class="text-neutral-500 text-sm px-3 border-r border-neutral-300">
          {{ leadingAddon }}
        </span>
      </div>
      
      <!-- Input Element -->
      <input
        :id="inputId"
        :type="computedType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="autocomplete"
        :maxlength="maxlength"
        :class="inputClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
        class="touch-manipulation"
      />
      
      <!-- Trailing Icon / Action Button -->
      <div
        v-if="trailingIcon || clearable || type === 'password' || loading"
        class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1"
      >
        <!-- Password Toggle -->
        <button
          v-if="type === 'password'"
          type="button"
          @click="togglePasswordVisibility"
          class="text-neutral-400 hover:text-neutral-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded p-1"
          tabindex="-1"
        >
          <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
          </svg>
        </button>
        
        <!-- Clear Button -->
        <button
          v-if="clearable && modelValue && !loading"
          type="button"
          @click="handleClear"
          class="text-neutral-400 hover:text-neutral-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded p-1"
          tabindex="-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        <!-- Loading Indicator -->
        <div v-if="loading" class="spinner"></div>
        
        <!-- Trailing Icon -->
        <component
          v-if="trailingIcon && !loading"
          :is="trailingIcon"
          class="w-5 h-5 text-neutral-400"
        />
      </div>
      
      <!-- Character Count -->
      <div v-if="maxlength" class="absolute -bottom-6 right-0 text-xs" :class="isNearLimit ? 'text-warning-600' : 'text-neutral-400'">
        {{ characterCount }} / {{ maxlength }}
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
 * World-Class Input Component - Enhanced Version
 * International UI/UX Standards
 * Accessible, Mobile-First, Feature-Rich
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
  
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'email', 'password', 'number', 'tel', 'url', 'search', 'date', 'time', 'datetime-local'].includes(value),
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
  
  loading: {
    type: Boolean,
    default: false,
  },
  
  leadingIcon: {
    type: Object,
    default: null,
  },
  
  trailingIcon: {
    type: Object,
    default: null,
  },
  
  leadingAddon: {
    type: String,
    default: '',
  },
  
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  
  min: {
    type: [Number, String],
    default: undefined,
  },
  
  max: {
    type: [Number, String],
    default: undefined,
  },
  
  step: {
    type: [Number, String],
    default: undefined,
  },
  
  maxlength: {
    type: [Number, String],
    default: undefined,
  },
  
  autocomplete: {
    type: String,
    default: 'off',
  },
  
  clearable: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'clear', 'keydown']);

const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`);
const isFocused = ref(false);
const showPassword = ref(false);

const computedType = computed(() => {
  if (props.type === 'password' && showPassword.value) {
    return 'text';
  }
  return props.type;
});

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

const inputClasses = computed(() => {
  return [
    'w-full rounded-lg border bg-white transition-all duration-200',
    'focus:outline-none focus:ring-4 focus:ring-offset-0',
    'placeholder-neutral-400',
    
    sizeClasses[props.size],
    
    props.leadingIcon || props.leadingAddon ? 'pl-10' : '',
    props.trailingIcon || props.clearable || props.type === 'password' || props.loading ? 'pr-10' : '',
    
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

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const handleInput = (event) => {
  const value = props.type === 'number' ? Number(event.target.value) : event.target.value;
  emit('update:modelValue', value);
};

const handleBlur = (event) => {
  isFocused.value = false;
  emit('blur', event);
};

const handleFocus = (event) => {
  isFocused.value = true;
  emit('focus', event);
};

const handleKeydown = (event) => {
  emit('keydown', event);
};

const handleClear = () => {
  emit('update:modelValue', '');
  emit('clear');
};
</script>
