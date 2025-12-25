<template>
  <div :class="containerClasses" class="relative overflow-hidden">
    <!-- Main carousel -->
    <div
      ref="carouselRef"
      class="relative"
      @mouseenter="pauseAutoplay"
      @mouseleave="resumeAutoplay"
    >
      <!-- Slides container -->
      <div
        class="flex transition-transform duration-500 ease-out"
        :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
      >
        <div
          v-for="(slide, index) in slides"
          :key="index"
          class="w-full flex-shrink-0"
          :class="slideClasses"
        >
          <slot name="slide" :slide="slide" :index="index" :active="index === currentIndex">
            <!-- Default slide content -->
            <div class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
              <img
                v-if="slide.image"
                :src="slide.image"
                :alt="slide.title || `Slide ${index + 1}`"
                class="w-full h-full object-cover"
              />
              <div
                v-if="slide.title || slide.description"
                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-6"
              >
                <h3 v-if="slide.title" class="text-xl font-bold text-white">
                  {{ slide.title }}
                </h3>
                <p v-if="slide.description" class="text-sm text-white/80 mt-1">
                  {{ slide.description }}
                </p>
              </div>
            </div>
          </slot>
        </div>
      </div>

      <!-- Navigation arrows -->
      <template v-if="showArrows && slides.length > 1">
        <button
          type="button"
          class="absolute left-2 top-1/2 -translate-y-1/2 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-lg hover:bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
          :class="{ 'opacity-50 cursor-not-allowed': !loop && currentIndex === 0 }"
          :disabled="!loop && currentIndex === 0"
          @click="prev"
        >
          <ChevronLeftIcon class="h-5 w-5 text-gray-700" />
        </button>
        <button
          type="button"
          class="absolute right-2 top-1/2 -translate-y-1/2 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-lg hover:bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
          :class="{ 'opacity-50 cursor-not-allowed': !loop && currentIndex === slides.length - 1 }"
          :disabled="!loop && currentIndex === slides.length - 1"
          @click="next"
        >
          <ChevronRightIcon class="h-5 w-5 text-gray-700" />
        </button>
      </template>
    </div>

    <!-- Indicators -->
    <div
      v-if="showIndicators && slides.length > 1"
      class="flex justify-center gap-2 mt-4"
    >
      <button
        v-for="(_, index) in slides"
        :key="index"
        type="button"
        class="transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-full"
        :class="indicatorClasses(index)"
        @click="goTo(index)"
      />
    </div>

    <!-- Thumbnails -->
    <div
      v-if="showThumbnails && slides.length > 1"
      class="flex gap-2 mt-4 overflow-x-auto pb-2"
    >
      <button
        v-for="(slide, index) in slides"
        :key="index"
        type="button"
        class="flex-shrink-0 rounded-lg overflow-hidden ring-2 transition-all focus:outline-none"
        :class="index === currentIndex ? 'ring-primary-500' : 'ring-transparent hover:ring-gray-300'"
        @click="goTo(index)"
      >
        <img
          :src="slide.thumbnail || slide.image"
          :alt="`Thumbnail ${index + 1}`"
          class="h-16 w-24 object-cover"
        />
      </button>
    </div>

    <!-- Progress bar (for autoplay) -->
    <div
      v-if="autoplay && showProgress"
      class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200"
    >
      <div
        class="h-full bg-primary-600 transition-all"
        :style="{ width: `${progress}%` }"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  slides: {
    type: Array,
    required: true
    // [{ image, thumbnail?, title?, description? }]
  },
  modelValue: {
    type: Number,
    default: 0
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  autoplayInterval: {
    type: Number,
    default: 5000 // ms
  },
  loop: {
    type: Boolean,
    default: true
  },
  showArrows: {
    type: Boolean,
    default: true
  },
  showIndicators: {
    type: Boolean,
    default: true
  },
  showThumbnails: {
    type: Boolean,
    default: false
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  indicatorStyle: {
    type: String,
    default: 'dots',
    validator: (value) => ['dots', 'bars', 'numbers'].includes(value)
  },
  pauseOnHover: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// State
const carouselRef = ref(null)
const currentIndex = ref(props.modelValue)
const progress = ref(0)
const isPaused = ref(false)
let autoplayTimer = null
let progressTimer = null

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  currentIndex.value = newVal
})

// Auto-play setup
onMounted(() => {
  if (props.autoplay) {
    startAutoplay()
  }
})

onUnmounted(() => {
  stopAutoplay()
})

// Navigation
function next() {
  if (currentIndex.value < props.slides.length - 1) {
    currentIndex.value++
  } else if (props.loop) {
    currentIndex.value = 0
  }
  emitChange()
  resetProgress()
}

function prev() {
  if (currentIndex.value > 0) {
    currentIndex.value--
  } else if (props.loop) {
    currentIndex.value = props.slides.length - 1
  }
  emitChange()
  resetProgress()
}

function goTo(index) {
  currentIndex.value = index
  emitChange()
  resetProgress()
}

function emitChange() {
  emit('update:modelValue', currentIndex.value)
  emit('change', currentIndex.value)
}

// Autoplay
function startAutoplay() {
  if (autoplayTimer) return
  
  autoplayTimer = setInterval(() => {
    if (!isPaused.value) {
      next()
    }
  }, props.autoplayInterval)
  
  startProgress()
}

function stopAutoplay() {
  if (autoplayTimer) {
    clearInterval(autoplayTimer)
    autoplayTimer = null
  }
  if (progressTimer) {
    clearInterval(progressTimer)
    progressTimer = null
  }
}

function pauseAutoplay() {
  if (props.pauseOnHover) {
    isPaused.value = true
  }
}

function resumeAutoplay() {
  isPaused.value = false
}

// Progress bar
function startProgress() {
  progress.value = 0
  const step = 100 / (props.autoplayInterval / 50)
  
  progressTimer = setInterval(() => {
    if (!isPaused.value) {
      progress.value = Math.min(100, progress.value + step)
    }
  }, 50)
}

function resetProgress() {
  progress.value = 0
}

// Indicator classes
function indicatorClasses(index) {
  const isActive = index === currentIndex.value
  
  if (props.indicatorStyle === 'dots') {
    return isActive
      ? 'h-2.5 w-2.5 bg-primary-600'
      : 'h-2 w-2 bg-gray-300 hover:bg-gray-400'
  }
  
  if (props.indicatorStyle === 'bars') {
    return isActive
      ? 'h-1 w-8 bg-primary-600'
      : 'h-1 w-8 bg-gray-300 hover:bg-gray-400'
  }
  
  // numbers
  return isActive
    ? 'h-6 w-6 bg-primary-600 text-white text-xs flex items-center justify-center'
    : 'h-6 w-6 bg-gray-200 text-gray-600 text-xs flex items-center justify-center hover:bg-gray-300'
}

// Slide classes
const slideClasses = computed(() => {
  return ''
})

// Container classes
const containerClasses = computed(() => {
  return ''
})

// Keyboard navigation
function handleKeydown(event) {
  if (event.key === 'ArrowLeft') {
    prev()
  } else if (event.key === 'ArrowRight') {
    next()
  }
}

// Expose methods
defineExpose({
  next,
  prev,
  goTo,
  pause: () => { isPaused.value = true },
  resume: () => { isPaused.value = false }
})
</script>
