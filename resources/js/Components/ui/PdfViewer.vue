<template>
  <div :class="['pdf-viewer rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg">
          <DocumentTextIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Page {{ currentPage }} of {{ totalPages }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Print"
          @click="printDocument"
        >
          <PrinterIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Download"
          @click="downloadDocument"
        >
          <ArrowDownTrayIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Fullscreen"
          @click="toggleFullscreen"
        >
          <ArrowsPointingOutIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <!-- Navigation -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          :disabled="currentPage <= 1"
          :class="[
            'p-1.5 rounded transition-colors',
            currentPage <= 1
              ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          @click="goToPage(currentPage - 1)"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <div class="flex items-center gap-1">
          <input
            :value="currentPage"
            type="number"
            min="1"
            :max="totalPages"
            class="w-12 px-2 py-1 text-sm text-center bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded"
            @change="goToPage(parseInt($event.target.value))"
          />
          <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ totalPages }}</span>
        </div>
        <button
          type="button"
          :disabled="currentPage >= totalPages"
          :class="[
            'p-1.5 rounded transition-colors',
            currentPage >= totalPages
              ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          @click="goToPage(currentPage + 1)"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Zoom -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
          @click="zoomOut"
        >
          <MagnifyingGlassMinusIcon class="w-5 h-5" />
        </button>
        <select
          v-model="zoomLevel"
          class="px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded"
        >
          <option value="0.5">50%</option>
          <option value="0.75">75%</option>
          <option value="1">100%</option>
          <option value="1.25">125%</option>
          <option value="1.5">150%</option>
          <option value="2">200%</option>
          <option value="fit-width">Fit Width</option>
          <option value="fit-page">Fit Page</option>
        </select>
        <button
          type="button"
          class="p-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
          @click="zoomIn"
        >
          <MagnifyingGlassPlusIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- View Mode -->
      <div class="flex items-center gap-1">
        <button
          type="button"
          :class="[
            'p-1.5 rounded transition-colors',
            viewMode === 'single'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          title="Single Page"
          @click="viewMode = 'single'"
        >
          <DocumentIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          :class="[
            'p-1.5 rounded transition-colors',
            viewMode === 'continuous'
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          title="Continuous Scroll"
          @click="viewMode = 'continuous'"
        >
          <Bars3Icon class="w-5 h-5" />
        </button>
        <button
          type="button"
          :class="[
            'p-1.5 rounded transition-colors',
            showThumbnails
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          title="Toggle Thumbnails"
          @click="showThumbnails = !showThumbnails"
        >
          <Squares2X2Icon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="flex" :style="{ height }">
      <!-- Thumbnails Sidebar -->
      <div
        v-if="showThumbnails"
        class="w-40 flex-shrink-0 overflow-y-auto border-r border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-2 space-y-2"
      >
        <div
          v-for="page in totalPages"
          :key="page"
          :class="[
            'relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all',
            currentPage === page
              ? 'border-blue-500 ring-2 ring-blue-500/20'
              : 'border-gray-200 dark:border-gray-700 hover:border-blue-300'
          ]"
          @click="goToPage(page)"
        >
          <div class="aspect-[3/4] bg-white dark:bg-gray-700 flex items-center justify-center">
            <span class="text-2xl font-bold text-gray-300 dark:text-gray-600">{{ page }}</span>
          </div>
          <div class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-xs text-center py-0.5">
            {{ page }}
          </div>
        </div>
      </div>

      <!-- PDF Display -->
      <div 
        ref="viewerRef"
        class="flex-1 overflow-auto bg-gray-200 dark:bg-gray-900 p-4"
        @scroll="onScroll"
      >
        <div v-if="loading" class="flex items-center justify-center h-full">
          <div class="text-center">
            <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4" />
            <p class="text-gray-600 dark:text-gray-400">Loading PDF...</p>
          </div>
        </div>

        <div v-else-if="error" class="flex items-center justify-center h-full">
          <div class="text-center">
            <ExclamationCircleIcon class="w-16 h-16 mx-auto text-red-400 mb-4" />
            <p class="text-red-600 dark:text-red-400 font-medium">Failed to load PDF</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ error }}</p>
            <button
              type="button"
              class="mt-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
              @click="loadDocument"
            >
              Retry
            </button>
          </div>
        </div>

        <div v-else class="space-y-4">
          <!-- Single Page Mode -->
          <template v-if="viewMode === 'single'">
            <div
              class="mx-auto bg-white shadow-xl"
              :style="{ 
                width: `${pageWidth}px`,
                transform: `scale(${computedZoom})`,
                transformOrigin: 'top center'
              }"
            >
              <div class="aspect-[3/4] flex items-center justify-center text-gray-400">
                <div class="text-center">
                  <DocumentTextIcon class="w-24 h-24 mx-auto mb-4 opacity-50" />
                  <p class="text-lg">Page {{ currentPage }}</p>
                  <p class="text-sm opacity-75">PDF content would render here</p>
                </div>
              </div>
            </div>
          </template>

          <!-- Continuous Mode -->
          <template v-else>
            <div
              v-for="page in totalPages"
              :key="page"
              :ref="el => pageRefs[page] = el"
              class="mx-auto bg-white shadow-xl mb-4"
              :style="{ 
                width: `${pageWidth}px`,
                transform: `scale(${computedZoom})`,
                transformOrigin: 'top center'
              }"
            >
              <div class="aspect-[3/4] flex items-center justify-center text-gray-400">
                <div class="text-center">
                  <DocumentTextIcon class="w-24 h-24 mx-auto mb-4 opacity-50" />
                  <p class="text-lg">Page {{ page }}</p>
                  <p class="text-sm opacity-75">PDF content would render here</p>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showFooter" class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
      <div>{{ fileName }}</div>
      <div>{{ fileSize }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  DocumentTextIcon,
  DocumentIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  ArrowsPointingOutIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassPlusIcon,
  MagnifyingGlassMinusIcon,
  Bars3Icon,
  Squares2X2Icon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  src: { type: String, default: '' },
  title: { type: String, default: 'PDF Viewer' },
  fileName: { type: String, default: 'document.pdf' },
  fileSize: { type: String, default: '2.4 MB' },
  initialPage: { type: Number, default: 1 },
  height: { type: String, default: '600px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true }
})

const emit = defineEmits(['pageChange', 'zoomChange', 'load', 'error'])

const viewerRef = ref(null)
const pageRefs = ref({})
const currentPage = ref(props.initialPage)
const totalPages = ref(10) // Would come from PDF.js
const zoomLevel = ref('1')
const viewMode = ref('single')
const showThumbnails = ref(false)
const loading = ref(false)
const error = ref(null)
const pageWidth = ref(595) // A4 width in points

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const computedZoom = computed(() => {
  if (zoomLevel.value === 'fit-width' || zoomLevel.value === 'fit-page') {
    return 1
  }
  return parseFloat(zoomLevel.value)
})

const goToPage = (page) => {
  if (page < 1) page = 1
  if (page > totalPages.value) page = totalPages.value
  currentPage.value = page
  emit('pageChange', page)

  if (viewMode.value === 'continuous' && pageRefs.value[page]) {
    pageRefs.value[page].scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const zoomIn = () => {
  const levels = [0.5, 0.75, 1, 1.25, 1.5, 2]
  const current = parseFloat(zoomLevel.value) || 1
  const next = levels.find(l => l > current)
  if (next) {
    zoomLevel.value = String(next)
    emit('zoomChange', next)
  }
}

const zoomOut = () => {
  const levels = [0.5, 0.75, 1, 1.25, 1.5, 2]
  const current = parseFloat(zoomLevel.value) || 1
  const next = [...levels].reverse().find(l => l < current)
  if (next) {
    zoomLevel.value = String(next)
    emit('zoomChange', next)
  }
}

const onScroll = () => {
  if (viewMode.value !== 'continuous') return
  // Detect current page based on scroll position
}

const printDocument = () => {
  window.print()
}

const downloadDocument = () => {
  if (props.src) {
    const a = document.createElement('a')
    a.href = props.src
    a.download = props.fileName
    a.click()
  }
}

const toggleFullscreen = () => {
  if (document.fullscreenElement) {
    document.exitFullscreen()
  } else {
    viewerRef.value?.requestFullscreen()
  }
}

const loadDocument = () => {
  loading.value = true
  error.value = null
  
  // Simulate loading
  setTimeout(() => {
    loading.value = false
    emit('load', { totalPages: totalPages.value })
  }, 1000)
}

onMounted(() => {
  if (props.src) {
    loadDocument()
  }
})

watch(() => props.src, () => {
  if (props.src) loadDocument()
})
</script>
