<template>
  <div class="w-full">
    <!-- Dropzone Area -->
    <div
      ref="dropzoneRef"
      class="relative border-2 border-dashed rounded-lg transition-all duration-200"
      :class="dropzoneClasses"
      @dragenter.prevent="handleDragEnter"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
      @click="openFileDialog"
    >
      <!-- Hidden file input -->
      <input
        ref="fileInputRef"
        type="file"
        class="hidden"
        :accept="acceptString"
        :multiple="multiple"
        @change="handleFileSelect"
      />

      <!-- Default Content -->
      <div class="flex flex-col items-center justify-center py-8 px-4">
        <!-- Upload Icon -->
        <div
          class="mb-4 p-4 rounded-full transition-colors"
          :class="isDragging ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-800'"
        >
          <CloudArrowUpIcon 
            class="w-10 h-10 transition-colors"
            :class="isDragging ? 'text-blue-500' : 'text-gray-400'"
          />
        </div>

        <!-- Text -->
        <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
          <span class="font-semibold text-blue-600 dark:text-blue-400">Click to upload</span>
          or drag and drop
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-500">
          {{ acceptLabel || 'Any file type' }}
          <span v-if="maxSize"> (Max {{ formatFileSize(maxSize) }})</span>
        </p>

        <!-- Uploading Indicator -->
        <div v-if="isUploading" class="mt-4 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
          <ArrowPathIcon class="w-4 h-4 animate-spin" />
          <span>Uploading...</span>
        </div>
      </div>

      <!-- Disabled Overlay -->
      <div
        v-if="disabled"
        class="absolute inset-0 bg-gray-100/50 dark:bg-gray-900/50 rounded-lg cursor-not-allowed"
      />
    </div>

    <!-- Error Message -->
    <p v-if="errorMessage" class="mt-2 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>

    <!-- File List -->
    <TransitionGroup
      v-if="files.length > 0"
      tag="ul"
      name="file-list"
      class="mt-4 space-y-2"
    >
      <li
        v-for="file in files"
        :key="file.name + file.size"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <!-- File Icon -->
          <div 
            class="flex-shrink-0 p-2 rounded-lg"
            :class="getFileTypeColor(file.type)"
          >
            <component :is="getFileIcon(file.type)" class="w-5 h-5" />
          </div>

          <!-- File Info -->
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ file.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatFileSize(file.size) }}
            </p>
          </div>

          <!-- Progress (if uploading) -->
          <div v-if="file.progress !== undefined && file.progress < 100" class="w-24">
            <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div
                class="h-full bg-blue-500 rounded-full transition-all duration-300"
                :style="{ width: `${file.progress}%` }"
              />
            </div>
            <p class="text-xs text-gray-500 text-center mt-1">{{ file.progress }}%</p>
          </div>

          <!-- Success/Error Status -->
          <CheckCircleIcon
            v-if="file.status === 'success'"
            class="w-5 h-5 text-green-500 flex-shrink-0"
          />
          <ExclamationCircleIcon
            v-else-if="file.status === 'error'"
            class="w-5 h-5 text-red-500 flex-shrink-0"
            :title="file.error"
          />
        </div>

        <!-- Remove Button -->
        <button
          type="button"
          class="ml-3 p-1 text-gray-400 hover:text-red-500 focus:outline-none transition-colors"
          @click.stop="removeFile(file)"
          :disabled="file.progress !== undefined && file.progress < 100"
        >
          <XMarkIcon class="w-5 h-5" />
        </button>
      </li>
    </TransitionGroup>

    <!-- Summary -->
    <div v-if="files.length > 0" class="mt-3 flex items-center justify-between text-sm">
      <span class="text-gray-500 dark:text-gray-400">
        {{ files.length }} file{{ files.length !== 1 ? 's' : '' }} selected
        ({{ formatFileSize(totalSize) }})
      </span>
      <button
        type="button"
        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium"
        @click="clearFiles"
      >
        Clear all
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  CloudArrowUpIcon,
  ArrowPathIcon,
  XMarkIcon,
  DocumentIcon,
  PhotoIcon,
  FilmIcon,
  MusicalNoteIcon,
  DocumentTextIcon,
  ArchiveBoxIcon,
  CodeBracketIcon,
  CheckCircleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Accepted file types
  accept: {
    type: Array,
    default: () => [] // e.g., ['image/*', '.pdf', 'application/vnd.ms-excel']
  },
  // Accept label (human-readable)
  acceptLabel: {
    type: String,
    default: ''
  },
  // Maximum file size in bytes
  maxSize: {
    type: Number,
    default: null // null = no limit
  },
  // Maximum number of files
  maxFiles: {
    type: Number,
    default: null // null = no limit
  },
  // Allow multiple files
  multiple: {
    type: Boolean,
    default: true
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Is currently uploading
  isUploading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['files-added', 'file-removed', 'files-cleared', 'error'])

// Refs
const dropzoneRef = ref(null)
const fileInputRef = ref(null)

// State
const isDragging = ref(false)
const files = ref([])
const errorMessage = ref('')
const dragCounter = ref(0)

// Accept string for input
const acceptString = computed(() => props.accept.join(','))

// Total size of all files
const totalSize = computed(() => 
  files.value.reduce((sum, f) => sum + f.size, 0)
)

// Dropzone classes
const dropzoneClasses = computed(() => {
  if (props.disabled) {
    return 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed'
  }
  if (isDragging.value) {
    return 'border-blue-400 bg-blue-50 dark:border-blue-500 dark:bg-blue-900/20 cursor-copy'
  }
  return 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 cursor-pointer'
})

// Format file size
function formatFileSize(bytes) {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0
  while (bytes >= 1024 && i < units.length - 1) {
    bytes /= 1024
    i++
  }
  return `${bytes.toFixed(i === 0 ? 0 : 1)} ${units[i]}`
}

// Get file icon based on type
function getFileIcon(mimeType) {
  if (!mimeType) return DocumentIcon
  if (mimeType.startsWith('image/')) return PhotoIcon
  if (mimeType.startsWith('video/')) return FilmIcon
  if (mimeType.startsWith('audio/')) return MusicalNoteIcon
  if (mimeType.includes('pdf')) return DocumentTextIcon
  if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('7z')) return ArchiveBoxIcon
  if (mimeType.includes('javascript') || mimeType.includes('json') || mimeType.includes('xml')) return CodeBracketIcon
  return DocumentIcon
}

// Get file type color
function getFileTypeColor(mimeType) {
  if (!mimeType) return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
  if (mimeType.startsWith('image/')) return 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'
  if (mimeType.startsWith('video/')) return 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400'
  if (mimeType.startsWith('audio/')) return 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400'
  if (mimeType.includes('pdf')) return 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
  if (mimeType.includes('zip') || mimeType.includes('rar')) return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400'
  return 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
}

// Validate file
function validateFile(file) {
  // Check size
  if (props.maxSize && file.size > props.maxSize) {
    return `File "${file.name}" exceeds maximum size of ${formatFileSize(props.maxSize)}`
  }
  
  // Check type
  if (props.accept.length > 0) {
    const isAccepted = props.accept.some(accept => {
      if (accept.startsWith('.')) {
        return file.name.toLowerCase().endsWith(accept.toLowerCase())
      }
      if (accept.endsWith('/*')) {
        return file.type.startsWith(accept.replace('/*', '/'))
      }
      return file.type === accept
    })
    
    if (!isAccepted) {
      return `File type "${file.type || 'unknown'}" is not accepted`
    }
  }
  
  return null
}

// Process files
function processFiles(fileList) {
  if (props.disabled) return
  
  errorMessage.value = ''
  const newFiles = []
  const errors = []
  
  for (const file of fileList) {
    // Check max files limit
    if (props.maxFiles && (files.value.length + newFiles.length) >= props.maxFiles) {
      errors.push(`Maximum ${props.maxFiles} files allowed`)
      break
    }
    
    // Check for duplicates
    const isDuplicate = files.value.some(f => f.name === file.name && f.size === file.size)
    if (isDuplicate) {
      continue
    }
    
    // Validate
    const error = validateFile(file)
    if (error) {
      errors.push(error)
      continue
    }
    
    newFiles.push({
      name: file.name,
      size: file.size,
      type: file.type,
      file: file, // Original File object
      status: 'pending'
    })
  }
  
  if (errors.length > 0) {
    errorMessage.value = errors[0]
    emit('error', errors)
  }
  
  if (newFiles.length > 0) {
    if (props.multiple) {
      files.value = [...files.value, ...newFiles]
    } else {
      files.value = [newFiles[0]]
    }
    emit('files-added', newFiles.map(f => f.file))
  }
}

// Drag handlers
function handleDragEnter(e) {
  if (props.disabled) return
  dragCounter.value++
  isDragging.value = true
}

function handleDragOver(e) {
  if (props.disabled) return
  e.dataTransfer.dropEffect = 'copy'
}

function handleDragLeave(e) {
  if (props.disabled) return
  dragCounter.value--
  if (dragCounter.value === 0) {
    isDragging.value = false
  }
}

function handleDrop(e) {
  if (props.disabled) return
  isDragging.value = false
  dragCounter.value = 0
  
  const items = e.dataTransfer.files
  if (items.length > 0) {
    processFiles(items)
  }
}

// Open file dialog
function openFileDialog() {
  if (props.disabled) return
  fileInputRef.value?.click()
}

// Handle file select from input
function handleFileSelect(e) {
  const items = e.target.files
  if (items.length > 0) {
    processFiles(items)
  }
  // Reset input
  e.target.value = ''
}

// Remove single file
function removeFile(file) {
  const index = files.value.indexOf(file)
  if (index > -1) {
    files.value.splice(index, 1)
    emit('file-removed', file.file)
  }
}

// Clear all files
function clearFiles() {
  const allFiles = files.value.map(f => f.file)
  files.value = []
  emit('files-cleared', allFiles)
}

// Expose methods
defineExpose({
  files,
  clearFiles,
  openFileDialog
})
</script>

<style scoped>
.file-list-enter-active,
.file-list-leave-active {
  transition: all 0.3s ease;
}

.file-list-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.file-list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

.file-list-move {
  transition: transform 0.3s ease;
}
</style>
