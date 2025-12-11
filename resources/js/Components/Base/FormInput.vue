<!-- Modern Form Input Component with Validation -->
<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  type: { 
    type: String, 
    default: 'text',
    validator: (v) => ['text', 'email', 'password', 'number', 'tel', 'url', 'search', 'date', 'time'].includes(v)
  },
  placeholder: String,
  error: String,
  label: String,
  helpText: String,
  required: Boolean,
  disabled: Boolean,
  readonly: Boolean,
  icon: Object, // Heroicon component
  iconRight: Object, // Right icon
  iconClass: String,
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  maxlength: Number,
  min: Number,
  max: Number,
  step: Number,
  autocomplete: String,
});

const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'keyup', 'enter']);

const inputRef = ref(null);
const isFocused = ref(false);

const handleInput = (event) => {
  emit('update:modelValue', event.target.value);
};

const handleFocus = (event) => {
  isFocused.value = true;
  emit('focus', event);
};

const handleBlur = (event) => {
  isFocused.value = false;
  emit('blur', event);
};

const handleKeyup = (event) => {
  emit('keyup', event);
  if (event.key === 'Enter') {
    emit('enter', event);
  }
};

const focus = () => {
  inputRef.value?.focus();
};

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-5 py-3 text-base',
  };
  return sizes[props.size];
});

const iconLeftPadding = computed(() => {
  if (!props.icon) return '';
  const paddings = {
    sm: 'pl-9',
    md: 'pl-10',
    lg: 'pl-11',
  };
  return paddings[props.size];
});

const iconRightPadding = computed(() => {
  if (!props.iconRight) return '';
  const paddings = {
    sm: 'pr-9',
    md: 'pr-10',
    lg: 'pr-11',
  };
  return paddings[props.size];
});

const iconSizeClass = computed(() => {
  const sizes = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
  };
  return sizes[props.size];
});

const inputClasses = computed(() => [
  'w-full border rounded-lg transition-all duration-200 bg-white',
  'focus:outline-none focus:ring-2 focus:ring-offset-0',
  sizeClasses.value,
  iconLeftPadding.value,
  iconRightPadding.value,
  props.error 
    ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' 
    : isFocused.value
    ? 'border-red-500 ring-2 ring-red-500'
    : 'border-gray-300 focus:border-red-500 focus:ring-red-500',
  {
    'bg-gray-50 cursor-not-allowed': props.disabled,
    'bg-gray-50': props.readonly,
  }
]);

defineExpose({ focus });
</script>

<template>
  <div class="form-group">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Input Container -->
    <div class="relative">
      <!-- Left Icon -->
      <div v-if="icon" class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <component
          :is="icon"
          :class="[
            iconSizeClass,
            iconClass || (error ? 'text-red-400' : 'text-gray-400')
          ]"
        />
      </div>

      <!-- Input Field -->
      <input
        ref="inputRef"
        :type="type"
        :value="modelValue"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
        @keyup="handleKeyup"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :maxlength="maxlength"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="autocomplete"
        :class="inputClasses"
      />

      <!-- Right Icon -->
      <div v-if="iconRight" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <component
          :is="iconRight"
          :class="[
            iconSizeClass,
            iconClass || (error ? 'text-red-400' : 'text-gray-400')
          ]"
        />
      </div>
    </div>

    <!-- Help Text -->
    <p v-if="helpText && !error" class="mt-1.5 text-xs text-gray-500">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="error" class="mt-1.5 text-xs text-red-600 flex items-start gap-1">
      <svg class="h-4 w-4 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      <span>{{ error }}</span>
    </p>

    <!-- Character Count (if maxlength) -->
    <p v-if="maxlength && !error" class="mt-1.5 text-xs text-gray-500 text-right">
      {{ modelValue?.length || 0 }} / {{ maxlength }}
    </p>
  </div>
</template>

