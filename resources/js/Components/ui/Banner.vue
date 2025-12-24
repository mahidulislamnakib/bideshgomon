<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    leave-active-class="transition-all duration-200 ease-in"
    :enter-from-class="isTop ? 'opacity-0 -translate-y-full' : 'opacity-0 translate-y-full'"
    enter-to-class="opacity-100 translate-y-0"
    leave-from-class="opacity-100 translate-y-0"
    :leave-to-class="isTop ? 'opacity-0 -translate-y-full' : 'opacity-0 translate-y-full'"
  >
    <div
      v-if="visible"
      :class="[
        'relative overflow-hidden',
        sticky ? (isTop ? 'sticky top-0 z-50' : 'sticky bottom-0 z-50') : '',
        variantClasses.container
      ]"
      role="banner"
      :aria-label="title || 'Announcement'"
    >
      <!-- Gradient Background Pattern -->
      <div v-if="showPattern" class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" :class="variantClasses.pattern" />
      </div>

      <!-- Content Container -->
      <div class="relative px-4 py-3 sm:px-6 lg:px-8">
        <div :class="[
          'flex items-center justify-between gap-4',
          centered ? 'justify-center' : ''
        ]">
          <!-- Left Icon -->
          <div v-if="showIcon" class="flex-shrink-0">
            <component :is="iconComponent" :class="['w-5 h-5', variantClasses.icon]" />
          </div>

          <!-- Content -->
          <div :class="[
            'flex-1 min-w-0',
            centered ? 'text-center' : ''
          ]">
            <p :class="['text-sm font-medium', variantClasses.text]">
              <span v-if="title" class="font-semibold">{{ title }}</span>
              <span v-if="title && message" class="mx-1">—</span>
              <span><slot>{{ message }}</slot></span>
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 flex-shrink-0">
            <!-- CTA Button -->
            <a
              v-if="ctaText"
              :href="ctaLink"
              :class="[
                'inline-flex items-center gap-1 px-3 py-1.5 text-sm font-semibold rounded-full',
                'transition-all duration-200',
                variantClasses.cta
              ]"
              @click="handleCtaClick"
            >
              {{ ctaText }}
              <ArrowRightIcon class="w-4 h-4" />
            </a>

            <!-- Dismiss Button -->
            <button
              v-if="dismissible"
              type="button"
              :class="[
                'rounded-full p-1 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
                variantClasses.dismiss
              ]"
              @click="dismiss"
              aria-label="Dismiss banner"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Progress Bar (for auto-dismiss) -->
      <div
        v-if="duration && showProgress"
        class="absolute bottom-0 left-0 right-0 h-1"
      >
        <div
          class="h-full transition-all ease-linear"
          :class="variantClasses.progress"
          :style="{ width: `${progressWidth}%` }"
        />
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { XMarkIcon, ArrowRightIcon } from '@heroicons/vue/20/solid'
import {
  MegaphoneIcon,
  SparklesIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  GiftIcon,
  RocketLaunchIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Banner variant
  variant: {
    type: String,
    default: 'info',
    validator: (v) => ['info', 'success', 'warning', 'error', 'promo', 'announcement', 'gradient'].includes(v)
  },
  // Position
  position: {
    type: String,
    default: 'top',
    validator: (v) => ['top', 'bottom'].includes(v)
  },
  // Title text
  title: {
    type: String,
    default: ''
  },
  // Message text
  message: {
    type: String,
    default: ''
  },
  // Show icon
  showIcon: {
    type: Boolean,
    default: true
  },
  // Custom icon
  icon: {
    type: [Object, Function],
    default: null
  },
  // CTA button text
  ctaText: {
    type: String,
    default: ''
  },
  // CTA button link
  ctaLink: {
    type: String,
    default: '#'
  },
  // Dismissible
  dismissible: {
    type: Boolean,
    default: true
  },
  // Auto-dismiss duration (ms)
  duration: {
    type: Number,
    default: 0
  },
  // Show progress bar
  showProgress: {
    type: Boolean,
    default: true
  },
  // Sticky positioning
  sticky: {
    type: Boolean,
    default: false
  },
  // Center content
  centered: {
    type: Boolean,
    default: false
  },
  // Show background pattern
  showPattern: {
    type: Boolean,
    default: false
  },
  // Controlled visibility
  modelValue: {
    type: Boolean,
    default: true
  },
  // Remember dismissal in localStorage
  persistDismiss: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'dismiss', 'cta-click'])

const visible = ref(props.modelValue)
const progressWidth = ref(100)
let progressInterval = null
let dismissTimeout = null

const isTop = computed(() => props.position === 'top')

// Check persisted dismissal
onMounted(() => {
  if (props.persistDismiss) {
    const dismissed = localStorage.getItem(`banner-${props.persistDismiss}`)
    if (dismissed === 'true') {
      visible.value = false
      emit('update:modelValue', false)
      return
    }
  }
  if (props.duration && props.modelValue) {
    startAutoDismiss()
  }
})

watch(() => props.modelValue, (val) => {
  visible.value = val
  if (val && props.duration) {
    startAutoDismiss()
  }
})

// Icon component
const iconComponent = computed(() => {
  if (props.icon) return props.icon
  
  const icons = {
    info: InformationCircleIcon,
    success: SparklesIcon,
    warning: ExclamationTriangleIcon,
    error: ExclamationTriangleIcon,
    promo: GiftIcon,
    announcement: MegaphoneIcon,
    gradient: RocketLaunchIcon
  }
  return icons[props.variant]
})

// Variant classes
const variantClasses = computed(() => {
  const variants = {
    info: {
      container: 'bg-blue-600',
      text: 'text-white',
      icon: 'text-blue-200',
      cta: 'bg-white text-blue-600 hover:bg-blue-50',
      dismiss: 'text-blue-200 hover:text-white hover:bg-blue-500 focus:ring-white',
      progress: 'bg-blue-300',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_25%,rgba(255,255,255,0.2)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.2)_75%)] bg-[length:20px_20px]'
    },
    success: {
      container: 'bg-green-600',
      text: 'text-white',
      icon: 'text-green-200',
      cta: 'bg-white text-green-600 hover:bg-green-50',
      dismiss: 'text-green-200 hover:text-white hover:bg-green-500 focus:ring-white',
      progress: 'bg-green-300',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_25%,rgba(255,255,255,0.2)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.2)_75%)] bg-[length:20px_20px]'
    },
    warning: {
      container: 'bg-yellow-500',
      text: 'text-yellow-900',
      icon: 'text-yellow-800',
      cta: 'bg-yellow-900 text-white hover:bg-yellow-800',
      dismiss: 'text-yellow-800 hover:text-yellow-900 hover:bg-yellow-400 focus:ring-yellow-900',
      progress: 'bg-yellow-700',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(0,0,0,0.1)_25%,rgba(0,0,0,0.1)_50%,transparent_50%,transparent_75%,rgba(0,0,0,0.1)_75%)] bg-[length:20px_20px]'
    },
    error: {
      container: 'bg-red-600',
      text: 'text-white',
      icon: 'text-red-200',
      cta: 'bg-white text-red-600 hover:bg-red-50',
      dismiss: 'text-red-200 hover:text-white hover:bg-red-500 focus:ring-white',
      progress: 'bg-red-300',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_25%,rgba(255,255,255,0.2)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.2)_75%)] bg-[length:20px_20px]'
    },
    promo: {
      container: 'bg-purple-600',
      text: 'text-white',
      icon: 'text-purple-200',
      cta: 'bg-yellow-400 text-purple-900 hover:bg-yellow-300',
      dismiss: 'text-purple-200 hover:text-white hover:bg-purple-500 focus:ring-white',
      progress: 'bg-purple-300',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_25%,rgba(255,255,255,0.2)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.2)_75%)] bg-[length:20px_20px]'
    },
    announcement: {
      container: 'bg-indigo-600',
      text: 'text-white',
      icon: 'text-indigo-200',
      cta: 'bg-white text-indigo-600 hover:bg-indigo-50',
      dismiss: 'text-indigo-200 hover:text-white hover:bg-indigo-500 focus:ring-white',
      progress: 'bg-indigo-300',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_25%,rgba(255,255,255,0.2)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.2)_75%)] bg-[length:20px_20px]'
    },
    gradient: {
      container: 'bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500',
      text: 'text-white',
      icon: 'text-white/80',
      cta: 'bg-white text-purple-600 hover:bg-purple-50',
      dismiss: 'text-white/70 hover:text-white hover:bg-white/20 focus:ring-white',
      progress: 'bg-white/50',
      pattern: 'bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.1)_25%,rgba(255,255,255,0.1)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.1)_75%)] bg-[length:20px_20px]'
    }
  }
  return variants[props.variant]
})

function startAutoDismiss() {
  if (!props.duration) return
  
  progressWidth.value = 100
  const interval = 50
  const decrement = (interval / props.duration) * 100
  
  progressInterval = setInterval(() => {
    progressWidth.value = Math.max(0, progressWidth.value - decrement)
  }, interval)
  
  dismissTimeout = setTimeout(() => {
    dismiss()
  }, props.duration)
}

function clearTimers() {
  if (progressInterval) clearInterval(progressInterval)
  if (dismissTimeout) clearTimeout(dismissTimeout)
}

function dismiss() {
  clearTimers()
  visible.value = false
  emit('update:modelValue', false)
  emit('dismiss')
  
  if (props.persistDismiss) {
    localStorage.setItem(`banner-${props.persistDismiss}`, 'true')
  }
}

function handleCtaClick(e) {
  emit('cta-click', e)
}

onUnmounted(() => {
  clearTimers()
})
</script>
