<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-neutral-900/50 backdrop-blur-sm" />
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all"
              :class="sizeClasses"
              @click.stop
            >
              <!-- Close Button -->
              <button
                v-if="closeable"
                type="button"
                @click="close"
                class="absolute top-4 right-4 text-neutral-400 hover:text-neutral-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-lg p-1"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              
              <!-- Header -->
              <div v-if="$slots.header || title" class="px-6 pt-6 pb-4" :class="headerClass">
                <slot name="header">
                  <h3 class="text-xl font-semibold text-neutral-900">
                    {{ title }}
                  </h3>
                  <p v-if="subtitle" class="mt-1 text-sm text-neutral-600">
                    {{ subtitle }}
                  </p>
                </slot>
              </div>
              
              <!-- Body -->
              <div class="px-6 py-4" :class="bodyClass">
                <slot />
              </div>
              
              <!-- Footer -->
              <div v-if="$slots.footer" class="px-6 pb-6 pt-4 flex items-center justify-end gap-3" :class="footerClass">
                <slot name="footer" />
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
/**
 * World-Class Modal Component
 * Accessible, Animated, Flexible
 */

import { computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  
  title: {
    type: String,
    default: '',
  },
  
  subtitle: {
    type: String,
    default: '',
  },
  
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value),
  },
  
  closeable: {
    type: Boolean,
    default: true,
  },
  
  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },
  
  divided: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'update:show']);

const sizeConfig = {
  sm: 'w-full max-w-md',
  md: 'w-full max-w-lg',
  lg: 'w-full max-w-2xl',
  xl: 'w-full max-w-4xl',
  '2xl': 'w-full max-w-6xl',
  full: 'w-full max-w-7xl mx-4',
};

const sizeClasses = computed(() => sizeConfig[props.size]);
const headerClass = computed(() => props.divided ? 'border-b border-neutral-200' : '');
const bodyClass = computed(() => 'max-h-[calc(100vh-200px)] overflow-y-auto');
const footerClass = computed(() => props.divided ? 'border-t border-neutral-200' : '');

const close = () => {
  emit('close');
  emit('update:show', false);
};

const handleBackdropClick = () => {
  if (props.closeOnBackdrop && props.closeable) {
    close();
  }
};

// Handle escape key
const handleEscape = (e) => {
  if (e.key === 'Escape' && props.show && props.closeable) {
    close();
  }
};

// Lock body scroll when modal is open
watch(() => props.show, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

onMounted(() => {
  document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape);
  document.body.style.overflow = '';
});
</script>
