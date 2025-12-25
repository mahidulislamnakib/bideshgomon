<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-1.5" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Drop zone -->
    <div
      ref="dropZoneRef"
      class="relative rounded-lg border-2 border-dashed transition-colors"
      :class="dropZoneClasses"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
      @click="openFileDialog"
    >
      <input
        ref="fileInputRef"
        type="file"
        class="sr-only"
        :accept="accept"
        :multiple="multiple"
        :disabled="disabled"
        @change="handleFileSelect"
      />

      <div class="flex flex-col items-center justify-center py-8 px-4">
        <!-- Icon -->
        <div
          class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
          :class="isDragging ? 'bg-primary-100' : 'bg-gray-100'"
        >
          <CloudArrowUpIcon
            class="h-6 w-6"
            :class="isDragging ? 'text-primary-600' : 'text-gray-400'"
          />
        </div>

        <!-- Text -->
        <div class="mt-4 text-center">
          <p class="text-sm font-medium text-gray-900">
            <span class="text-primary-600 hover:text-primary-500 cursor-pointer">
              Click to upload
            </span>
            <span class="text-gray-500"> or drag and drop</span>
          </p>
          <p class="mt-1 text-xs text-gray-500">
            {{ acceptDescription || generateAcceptDescription() }}
          </p>
          <p v-if="maxSize" class="mt-0.5 text-xs text-gray-500">
            Max size: {{ formatFileSize(maxSize) }}
          </p>
        </div>
      </div>
    </div>

    <!-- File list -->
    <TransitionGroup
      v-if="files.length > 0"
      tag="ul"
      class="mt-3 space-y-2"
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <li
        v-for="(file, index) in files"
        :key="file.name + file.size"
        class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3"
      >
        <!-- File icon or preview -->
        <div class="flex-shrink-0">
          <img
            v-if="file.preview"
            :src="file.preview"
            :alt="file.name"
            class="h-10 w-10 rounded-lg object-cover"
          />
          <div
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100"
          >
            <DocumentIcon class="h-5 w-5 text-gray-400" />
          </div>
        </div>

        <!-- File info -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
          <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
        </div>

        <!-- Upload progress -->
        <div v-if="file.progress !== undefined && file.progress < 100" class="w-20">
          <div class="h-1.5 w-full rounded-full bg-gray-200">
            <div
              class="h-1.5 rounded-full bg-primary-600 transition-all"
              :style="{ width: `${file.progress}%` }"
            />
          </div>
          <p class="mt-0.5 text-xs text-gray-500 text-center">{{ file.progress }}%</p>
        </div>

        <!-- Status icon -->
        <div v-else-if="file.status === 'success'" class="flex-shrink-0">
          <CheckCircleIcon class="h-5 w-5 text-green-500" />
        </div>
        <div v-else-if="file.status === 'error'" class="flex-shrink-0">
          <ExclamationCircleIcon class="h-5 w-5 text-red-500" />
        </div>

        <!-- Remove button -->
        <button
          type="button"
          class="flex-shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 transition-colors"
          :disabled="disabled"
          @click.stop="removeFile(index)"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </li>
    </TransitionGroup>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-1.5 text-sm"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  CloudArrowUpIcon,
  DocumentIcon,
  XMarkIcon,
  CheckCircleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  accept: {
    type: String,
    default: '' // e.g., 'image/*,.pdf'
  },
  acceptDescription: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxSize: {
    type: Number,
    default: null // bytes
  },
  maxFiles: {
    type: Number,
    default: null
  },
  showPreviews: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'error'])

// Refs
const fileInputRef = ref(null)
const dropZoneRef = ref(null)
const isDragging = ref(false)
const files = ref([])

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (Array.isArray(newVal)) {
    files.value = newVal.map(f => ({
      ...f,
      preview: f.preview || (props.showPreviews && isImage(f) ? URL.createObjectURL(f) : null)
    }))
  }
}, { immediate: true })

// Open file dialog
function openFileDialog() {
  if (!props.disabled) {
    fileInputRef.value?.click()
  }
}

// Handle file selection
function handleFileSelect(event) {
  const selectedFiles = Array.from(event.target.files || [])
  addFiles(selectedFiles)
  // Reset input so same file can be selected again
  event.target.value = ''
}

// Handle drag events
function handleDragOver(event) {
  if (props.disabled) return
  isDragging.value = true
}

function handleDragLeave(event) {
  isDragging.value = false
}

function handleDrop(event) {
  if (props.disabled) return
  isDragging.value = false
  const droppedFiles = Array.from(event.dataTransfer?.files || [])
  addFiles(droppedFiles)
}

// Add files with validation
function addFiles(newFiles) {
  let validFiles = []

  for (const file of newFiles) {
    // Check max files
    if (props.maxFiles && files.value.length + validFiles.length >= props.maxFiles) {
      emit('error', { type: 'max-files', message: `Maximum ${props.maxFiles} files allowed` })
      break
    }

    // Check file size
    if (props.maxSize && file.size > props.maxSize) {
      emit('error', { type: 'max-size', message: `File "${file.name}" exceeds maximum size`, file })
      continue
    }

    // Check accept types
    if (props.accept && !isAccepted(file)) {
      emit('error', { type: 'invalid-type', message: `File "${file.name}" is not an accepted type`, file })
      continue
    }

    // Create preview for images
    const preview = props.showPreviews && isImage(file) ? URL.createObjectURL(file) : null

    validFiles.push({
      file,
      name: file.name,
      size: file.size,
      type: file.type,
      preview,
      status: 'pending'
    })
  }

  if (!props.multiple) {
    // Replace existing files
    files.value.forEach(f => f.preview && URL.revokeObjectURL(f.preview))
    files.value = validFiles.slice(0, 1)
  } else {
    files.value = [...files.value, ...validFiles]
  }

  emitUpdate()
}

// Remove file
function removeFile(index) {
  const file = files.value[index]
  if (file.preview) {
    URL.revokeObjectURL(file.preview)
  }
  files.value.splice(index, 1)
  emitUpdate()
}

// Emit update
function emitUpdate() {
  const rawFiles = files.value.map(f => f.file || f)
  emit('update:modelValue', rawFiles)
  emit('change', rawFiles)
}

// Check if file is image
function isImage(file) {
  const type = file.type || file.file?.type
  return type?.startsWith('image/')
}

// Check if file is accepted
function isAccepted(file) {
  if (!props.accept) return true
  
  const acceptedTypes = props.accept.split(',').map(t => t.trim())
  
  for (const accepted of acceptedTypes) {
    if (accepted.startsWith('.')) {
      // Extension check
      if (file.name.toLowerCase().endsWith(accepted.toLowerCase())) return true
    } else if (accepted.endsWith('/*')) {
      // Wildcard MIME type (e.g., image/*)
      const category = accepted.replace('/*', '')
      if (file.type.startsWith(category)) return true
    } else {
      // Exact MIME type
      if (file.type === accepted) return true
    }
  }
  
  return false
}

// Generate accept description
function generateAcceptDescription() {
  if (!props.accept) return 'Any file type'
  
  const types = props.accept.split(',').map(t => t.trim())
  const readable = types.map(t => {
    if (t === 'image/*') return 'Images'
    if (t === 'video/*') return 'Videos'
    if (t === 'audio/*') return 'Audio'
    if (t === 'application/pdf') return 'PDF'
    if (t.startsWith('.')) return t.toUpperCase()
    return t
  })
  
  return readable.join(', ')
}

// Format file size
function formatFileSize(bytes) {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0
  while (bytes >= 1024 && i < units.length - 1) {
    bytes /= 1024
    i++
  }
  return `${bytes.toFixed(i > 0 ? 1 : 0)} ${units[i]}`
}

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

const dropZoneClasses = computed(() => {
  if (props.disabled) {
    return 'border-gray-200 bg-gray-50 cursor-not-allowed'
  }
  if (isDragging.value) {
    return 'border-primary-400 bg-primary-50 cursor-pointer'
  }
  if (props.error) {
    return 'border-red-300 hover:border-red-400 cursor-pointer'
  }
  return 'border-gray-300 hover:border-gray-400 cursor-pointer'
})
</script>
