<template>
  <div class="inline-flex flex-col items-center gap-3">
    <!-- Avatar Preview -->
    <div 
      class="relative group"
      :class="sizeClasses.container"
    >
      <!-- Avatar Image/Placeholder -->
      <div
        class="overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
        :class="[sizeClasses.avatar, shapeClass]"
      >
        <!-- Preview Image -->
        <img
          v-if="previewUrl"
          :src="previewUrl"
          :alt="alt"
          class="w-full h-full object-cover"
        />
        
        <!-- Initials Fallback -->
        <span
          v-else-if="initials"
          class="font-semibold text-gray-600 dark:text-gray-400"
          :class="sizeClasses.text"
        >
          {{ initials }}
        </span>
        
        <!-- Icon Fallback -->
        <UserIcon
          v-else
          class="text-gray-400"
          :class="sizeClasses.icon"
        />
      </div>

      <!-- Hover Overlay -->
      <div
        v-if="!disabled && !readonly"
        class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
        :class="shapeClass"
        @click="openFileDialog"
      >
        <CameraIcon class="w-6 h-6 text-white" />
      </div>

      <!-- Loading Overlay -->
      <div
        v-if="isLoading"
        class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-900/80"
        :class="shapeClass"
      >
        <ArrowPathIcon class="w-6 h-6 text-blue-600 animate-spin" />
      </div>

      <!-- Remove Button -->
      <button
        v-if="previewUrl && removable && !disabled && !readonly"
        type="button"
        class="absolute -top-1 -right-1 p-1 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 transition-colors"
        @click.stop="removeImage"
        title="Remove image"
      >
        <XMarkIcon class="w-4 h-4" />
      </button>

      <!-- Status Badge -->
      <div
        v-if="status"
        class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900"
        :class="statusClass"
      />
    </div>

    <!-- Hidden File Input -->
    <input
      ref="fileInputRef"
      type="file"
      class="hidden"
      :accept="acceptString"
      @change="handleFileSelect"
    />

    <!-- Upload Button (optional) -->
    <button
      v-if="showButton && !disabled && !readonly"
      type="button"
      class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
      @click="openFileDialog"
    >
      {{ previewUrl ? 'Change photo' : 'Upload photo' }}
    </button>

    <!-- Error Message -->
    <p v-if="errorMessage" class="text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>

    <!-- Helper Text -->
    <p v-if="helperText && !errorMessage" class="text-xs text-gray-500 dark:text-gray-400 text-center">
      {{ helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  UserIcon,
  CameraIcon,
  XMarkIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Current image URL or File
  modelValue: {
    type: [String, File],
    default: null
  },
  // Name for initials fallback
  name: {
    type: String,
    default: ''
  },
  // Alt text
  alt: {
    type: String,
    default: 'Avatar'
  },
  // Size variant
  size: {
    type: String,
    default: 'lg',
    validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl'].includes(v)
  },
  // Shape
  shape: {
    type: String,
    default: 'circle',
    validator: (v) => ['circle', 'rounded', 'square'].includes(v)
  },
  // Accepted file types
  accept: {
    type: Array,
    default: () => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
  },
  // Maximum file size in bytes
  maxSize: {
    type: Number,
    default: 5 * 1024 * 1024 // 5MB
  },
  // Show upload button below
  showButton: {
    type: Boolean,
    default: true
  },
  // Allow removing image
  removable: {
    type: Boolean,
    default: true
  },
  // Status indicator (online, offline, busy, away)
  status: {
    type: String,
    default: null,
    validator: (v) => [null, 'online', 'offline', 'busy', 'away'].includes(v)
  },
  // Helper text
  helperText: {
    type: String,
    default: ''
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Read-only mode
  readonly: {
    type: Boolean,
    default: false
  },
  // Loading state
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'error', 'remove'])

// Refs
const fileInputRef = ref(null)
const errorMessage = ref('')
const localPreview = ref(null)

// Accept string for input
const acceptString = computed(() => props.accept.join(','))

// Preview URL (from modelValue or local preview)
const previewUrl = computed(() => {
  if (localPreview.value) return localPreview.value
  if (typeof props.modelValue === 'string') return props.modelValue
  if (props.modelValue instanceof File) {
    return URL.createObjectURL(props.modelValue)
  }
  return null
})

// Initials from name
const initials = computed(() => {
  if (!props.name) return ''
  const parts = props.name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return parts[0]?.substring(0, 2).toUpperCase() || ''
})

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: { container: '', avatar: 'w-12 h-12', icon: 'w-6 h-6', text: 'text-sm' },
    md: { container: '', avatar: 'w-16 h-16', icon: 'w-8 h-8', text: 'text-base' },
    lg: { container: '', avatar: 'w-24 h-24', icon: 'w-10 h-10', text: 'text-xl' },
    xl: { container: '', avatar: 'w-32 h-32', icon: 'w-12 h-12', text: 'text-2xl' },
    '2xl': { container: '', avatar: 'w-40 h-40', icon: 'w-16 h-16', text: 'text-3xl' }
  }
  return sizes[props.size]
})

// Shape class
const shapeClass = computed(() => {
  const shapes = {
    circle: 'rounded-full',
    rounded: 'rounded-xl',
    square: 'rounded-none'
  }
  return shapes[props.shape]
})

// Status class
const statusClass = computed(() => {
  const statuses = {
    online: 'bg-green-500',
    offline: 'bg-gray-400',
    busy: 'bg-red-500',
    away: 'bg-yellow-500'
  }
  return statuses[props.status] || ''
})

// Open file dialog
function openFileDialog() {
  if (props.disabled || props.readonly) return
  fileInputRef.value?.click()
}

// Handle file select
function handleFileSelect(event) {
  const file = event.target.files?.[0]
  if (!file) return
  
  errorMessage.value = ''
  
  // Validate file type
  if (!props.accept.includes(file.type)) {
    errorMessage.value = 'Invalid file type. Please upload an image.'
    emit('error', { type: 'invalid-type', file })
    event.target.value = ''
    return
  }
  
  // Validate file size
  if (file.size > props.maxSize) {
    const maxMB = (props.maxSize / (1024 * 1024)).toFixed(1)
    errorMessage.value = `File too large. Maximum size is ${maxMB}MB.`
    emit('error', { type: 'too-large', file, maxSize: props.maxSize })
    event.target.value = ''
    return
  }
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    localPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
  
  // Emit file
  emit('update:modelValue', file)
  emit('change', file)
  
  // Reset input
  event.target.value = ''
}

// Remove image
function removeImage() {
  localPreview.value = null
  errorMessage.value = ''
  emit('update:modelValue', null)
  emit('remove')
}

// Watch for external value changes
watch(() => props.modelValue, (val) => {
  if (!val) {
    localPreview.value = null
  }
})

// Expose methods
defineExpose({
  openFileDialog,
  removeImage
})
</script>
