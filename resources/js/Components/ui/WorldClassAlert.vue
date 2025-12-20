<template>
  <transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      class="alert"
      :class="alertClasses"
      role="alert"
    >
      <!-- Icon -->
      <div class="flex-shrink-0">
        <component :is="iconComponent" class="w-5 h-5" :class="iconColorClass" />
      </div>
      
      <!-- Content -->
      <div class="flex-1">
        <h4 v-if="title" class="font-semibold mb-1" :class="titleColorClass">
          {{ title }}
        </h4>
        <div :class="messageColorClass">
          <slot>{{ message }}</slot>
        </div>
        
        <!-- Actions -->
        <div v-if="$slots.actions" class="mt-3 flex gap-2">
          <slot name="actions" />
        </div>
      </div>
      
      <!-- Dismiss Button -->
      <button
        v-if="dismissible"
        type="button"
        @click="handleDismiss"
        class="flex-shrink-0 hover:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg p-1 -mr-1"
        :class="dismissButtonClass"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </transition>
</template>

<script setup>
/**
 * World-Class Alert Component
 * Notifications, warnings, errors, success messages
 */

import { computed, ref, watch } from 'vue';
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  variant: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'warning', 'error', 'info'].includes(value),
  },
  
  title: {
    type: String,
    default: '',
  },
  
  message: {
    type: String,
    default: '',
  },
  
  modelValue: {
    type: Boolean,
    default: true,
  },
  
  dismissible: {
    type: Boolean,
    default: false,
  },
  
  autoDismiss: {
    type: [Boolean, Number],
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'dismiss']);

const show = ref(props.modelValue);

watch(() => props.modelValue, (newValue) => {
  show.value = newValue;
});

// Auto dismiss
if (props.autoDismiss) {
  const delay = typeof props.autoDismiss === 'number' ? props.autoDismiss : 5000;
  setTimeout(() => {
    handleDismiss();
  }, delay);
}

const variantConfig = {
  success: {
    bg: 'alert-success',
    icon: CheckCircleIcon,
    iconColor: 'text-success-600',
    titleColor: 'text-success-900',
    messageColor: 'text-success-800',
    dismissColor: 'focus-visible:ring-success-500',
  },
  warning: {
    bg: 'alert-warning',
    icon: ExclamationTriangleIcon,
    iconColor: 'text-warning-600',
    titleColor: 'text-warning-900',
    messageColor: 'text-warning-800',
    dismissColor: 'focus-visible:ring-warning-500',
  },
  error: {
    bg: 'alert-error',
    icon: XCircleIcon,
    iconColor: 'text-error-600',
    titleColor: 'text-error-900',
    messageColor: 'text-error-800',
    dismissColor: 'focus-visible:ring-error-500',
  },
  info: {
    bg: 'alert-info',
    icon: InformationCircleIcon,
    iconColor: 'text-info-600',
    titleColor: 'text-info-900',
    messageColor: 'text-info-800',
    dismissColor: 'focus-visible:ring-info-500',
  },
};

const alertClasses = computed(() => variantConfig[props.variant].bg);
const iconComponent = computed(() => variantConfig[props.variant].icon);
const iconColorClass = computed(() => variantConfig[props.variant].iconColor);
const titleColorClass = computed(() => variantConfig[props.variant].titleColor);
const messageColorClass = computed(() => variantConfig[props.variant].messageColor);
const dismissButtonClass = computed(() => variantConfig[props.variant].dismissColor);

const handleDismiss = () => {
  show.value = false;
  emit('update:modelValue', false);
  emit('dismiss');
};
</script>
