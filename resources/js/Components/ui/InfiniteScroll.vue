<template>
  <div ref="containerRef" :class="containerClasses">
    <!-- Content slot -->
    <slot />

    <!-- Loading indicator -->
    <div
      v-if="loading"
      class="flex justify-center py-6"
    >
      <slot name="loading">
        <div class="flex items-center gap-3 text-gray-500">
          <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          <span class="text-sm">{{ loadingText }}</span>
        </div>
      </slot>
    </div>

    <!-- End of content -->
    <div
      v-else-if="finished"
      class="flex justify-center py-6"
    >
      <slot name="finished">
        <p class="text-sm text-gray-400">{{ finishedText }}</p>
      </slot>
    </div>

    <!-- Error state -->
    <div
      v-else-if="error"
      class="flex flex-col items-center gap-3 py-6"
    >
      <slot name="error" :retry="load">
        <p class="text-sm text-red-500">{{ error }}</p>
        <button
          type="button"
          class="text-sm font-medium text-primary-600 hover:text-primary-700"
          @click="load"
        >
          Try again
        </button>
      </slot>
    </div>

    <!-- Sentinel element for intersection observer -->
    <div ref="sentinelRef" class="h-px w-full" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  loading: {
    type: Boolean,
    default: false
  },
  finished: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  loadingText: {
    type: String,
    default: 'Loading more...'
  },
  finishedText: {
    type: String,
    default: 'No more items'
  },
  distance: {
    type: Number,
    default: 100 // pixels from bottom to trigger
  },
  immediate: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  direction: {
    type: String,
    default: 'bottom',
    validator: (value) => ['top', 'bottom'].includes(value)
  }
})

const emit = defineEmits(['load'])

// Refs
const containerRef = ref(null)
const sentinelRef = ref(null)
let observer = null

// Load trigger
function load() {
  if (props.loading || props.finished || props.disabled || props.error) return
  emit('load')
}

// Setup intersection observer
function setupObserver() {
  if (observer) {
    observer.disconnect()
  }

  if (!sentinelRef.value) return

  const options = {
    root: null, // viewport
    rootMargin: `${props.distance}px`,
    threshold: 0
  }

  observer = new IntersectionObserver((entries) => {
    const entry = entries[0]
    if (entry.isIntersecting) {
      load()
    }
  }, options)

  observer.observe(sentinelRef.value)
}

// Container classes
const containerClasses = computed(() => {
  return 'relative'
})

// Watch for changes that should reset observer
watch([() => props.finished, () => props.disabled], () => {
  if (!props.finished && !props.disabled) {
    setupObserver()
  }
})

// Lifecycle
onMounted(() => {
  setupObserver()
  
  // Immediate load if content doesn't fill viewport
  if (props.immediate) {
    setTimeout(() => {
      if (sentinelRef.value) {
        const rect = sentinelRef.value.getBoundingClientRect()
        const viewportHeight = window.innerHeight
        if (rect.top < viewportHeight + props.distance) {
          load()
        }
      }
    }, 100)
  }
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
  }
})

// Expose methods
defineExpose({
  load,
  reset: () => {
    setupObserver()
  }
})
</script>
