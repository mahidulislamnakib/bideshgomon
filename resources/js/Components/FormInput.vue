<script setup>
/**
 * FormInput - Standardized Input Component
 * BideshGomon Design System
 * 
 * Features:
 * - Consistent styling across all forms
 * - Icon support (left/right) with proper spacing
 * - Label above input pattern
 * - Error/success states
 * - Password visibility toggle
 * - Accessible (WCAG compliant)
 * - Mobile-first responsive
 * 
 * Usage:
 * <FormInput v-model="form.email" label="Email" type="email" />
 * <FormInput v-model="form.password" label="Password" type="password" />
 * <FormInput v-model="form.search" placeholder="Search..." :icon-left="MagnifyingGlassIcon" />
 */

import { computed, ref, onMounted } from 'vue';
import { EyeIcon, EyeSlashIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  type: {
    type: String,
    default: 'text',
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
  iconLeft: {
    type: [Object, Function],
    default: null,
  },
  iconRight: {
    type: [Object, Function],
    default: null,
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
  autocomplete: {
    type: String,
    default: 'off',
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substr(2, 9)}`,
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const inputRef = ref(null);
const showPassword = ref(false);

// Computed type for password toggle
const inputType = computed(() => {
  if (props.type === 'password' && showPassword.value) {
    return 'text';
  }
  return props.type;
});

// Toggle password visibility
const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

// Check if we have icons
const hasLeftIcon = computed(() => !!props.iconLeft);
const hasRightIcon = computed(() => !!props.iconRight || props.type === 'password');

// Input classes
const inputClasses = computed(() => {
  const classes = [
    'form-input',
    'w-full',
    'text-gray-900',
  ];
  
  // Size
  if (props.size === 'sm') classes.push('form-input-sm');
  if (props.size === 'lg') classes.push('form-input-lg');
  
  // Icons padding
  if (hasLeftIcon.value) classes.push('form-input-has-icon-left');
  if (hasRightIcon.value) classes.push('form-input-has-icon-right');
  
  // States
  if (props.error) classes.push('form-input-error');
  if (props.disabled) classes.push('opacity-70 cursor-not-allowed');
  
  return classes;
});

// Focus input on mount if autofocus
onMounted(() => {
  if (props.autofocus && inputRef.value) {
    inputRef.value.focus();
  }
});

// Expose focus method
defineExpose({
  focus: () => inputRef.value?.focus(),
  blur: () => inputRef.value?.blur(),
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
    
    <!-- Input Wrapper -->
    <div class="form-input-wrapper">
      <!-- Left Icon -->
      <div v-if="iconLeft" class="form-input-icon-left">
        <component :is="iconLeft" class="w-5 h-5" />
      </div>
      
      <!-- Input Element -->
      <input
        :id="id"
        ref="inputRef"
        :type="inputType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :autocomplete="autocomplete"
        :class="inputClasses"
        :aria-invalid="!!error"
        :aria-describedby="error ? `${id}-error` : helper ? `${id}-helper` : undefined"
        @input="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur', $event)"
        @focus="emit('focus', $event)"
      />
      
      <!-- Right Icon / Password Toggle -->
      <div v-if="hasRightIcon" class="form-input-icon-right" :class="{ clickable: type === 'password' }">
        <!-- Password Toggle -->
        <button
          v-if="type === 'password'"
          type="button"
          @click="togglePassword"
          class="focus:outline-none"
          tabindex="-1"
          :aria-label="showPassword ? 'Hide password' : 'Show password'"
        >
          <EyeSlashIcon v-if="showPassword" class="w-5 h-5 text-gray-400 hover:text-gray-600" />
          <EyeIcon v-else class="w-5 h-5 text-gray-400 hover:text-gray-600" />
        </button>
        
        <!-- Custom Right Icon -->
        <component v-else-if="iconRight" :is="iconRight" class="w-5 h-5" />
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
