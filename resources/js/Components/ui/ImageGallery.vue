<template>
  <div>
    <!-- Grid Layout -->
    <div
      v-if="layout === 'grid'"
      class="grid gap-4"
      :class="gridColsClasses"
    >
      <div
        v-for="(image, index) in images"
        :key="image.id || index"
        class="relative group cursor-pointer overflow-hidden rounded-lg"
        :class="imageContainerClasses"
        @click="openLightbox(index)"
      >
        <img
          :src="image.thumbnail || image.src"
          :alt="image.alt || `Image ${index + 1}`"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
          loading="lazy"
        />
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
          <MagnifyingGlassPlusIcon class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </div>
        
        <!-- Caption -->
        <div
          v-if="showCaptions && image.caption"
          class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
        >
          <p class="text-white text-sm font-medium truncate">{{ image.caption }}</p>
        </div>
      </div>
    </div>

    <!-- Masonry Layout -->
    <div
      v-else-if="layout === 'masonry'"
      class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4"
    >
      <div
        v-for="(image, index) in images"
        :key="image.id || index"
        class="relative group cursor-pointer overflow-hidden rounded-lg break-inside-avoid"
        @click="openLightbox(index)"
      >
        <img
          :src="image.thumbnail || image.src"
          :alt="image.alt || `Image ${index + 1}`"
          class="w-full object-cover transition-transform duration-300 group-hover:scale-105"
          loading="lazy"
        />
        
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
          <MagnifyingGlassPlusIcon class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </div>
      </div>
    </div>

    <!-- Carousel Layout -->
    <div
      v-else-if="layout === 'carousel'"
      class="relative overflow-hidden rounded-lg"
    >
      <div
        class="flex transition-transform duration-300 ease-out"
        :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
      >
        <div
          v-for="(image, index) in images"
          :key="image.id || index"
          class="flex-shrink-0 w-full cursor-pointer"
          @click="openLightbox(index)"
        >
          <img
            :src="image.src"
            :alt="image.alt || `Image ${index + 1}`"
            class="w-full h-64 md:h-96 object-cover"
            loading="lazy"
          />
        </div>
      </div>
      
      <!-- Carousel Controls -->
      <button
        v-if="images.length > 1"
        type="button"
        class="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/90 hover:bg-white shadow-lg transition-colors"
        @click.stop="previousSlide"
      >
        <ChevronLeftIcon class="w-5 h-5 text-gray-900" />
      </button>
      <button
        v-if="images.length > 1"
        type="button"
        class="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/90 hover:bg-white shadow-lg transition-colors"
        @click.stop="nextSlide"
      >
        <ChevronRightIcon class="w-5 h-5 text-gray-900" />
      </button>
      
      <!-- Dots -->
      <div v-if="images.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
        <button
          v-for="(_, index) in images"
          :key="index"
          type="button"
          class="w-2 h-2 rounded-full transition-colors"
          :class="currentSlide === index ? 'bg-white' : 'bg-white/50 hover:bg-white/75'"
          @click.stop="currentSlide = index"
        />
      </div>
    </div>

    <!-- Lightbox -->
    <TransitionRoot :show="lightboxOpen" as="template">
      <Dialog class="relative z-50" @close="closeLightbox">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/90" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-hidden">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="relative max-w-7xl w-full">
                <!-- Close button -->
                <button
                  type="button"
                  class="absolute -top-12 right-0 p-2 text-white/70 hover:text-white transition-colors"
                  @click="closeLightbox"
                >
                  <XMarkIcon class="w-8 h-8" />
                </button>

                <!-- Main image -->
                <div class="relative">
                  <img
                    :src="currentImage?.src"
                    :alt="currentImage?.alt"
                    class="max-h-[80vh] mx-auto object-contain"
                  />
                  
                  <!-- Navigation -->
                  <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-black/50 hover:bg-black/70 text-white transition-colors"
                    @click="previousImage"
                  >
                    <ChevronLeftIcon class="w-6 h-6" />
                  </button>
                  <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-black/50 hover:bg-black/70 text-white transition-colors"
                    @click="nextImage"
                  >
                    <ChevronRightIcon class="w-6 h-6" />
                  </button>
                </div>

                <!-- Caption & Counter -->
                <div class="mt-4 text-center">
                  <p v-if="currentImage?.caption" class="text-white text-lg">
                    {{ currentImage.caption }}
                  </p>
                  <p class="text-white/60 text-sm mt-2">
                    {{ lightboxIndex + 1 }} / {{ images.length }}
                  </p>
                </div>

                <!-- Thumbnails -->
                <div v-if="showThumbnails && images.length > 1" class="mt-6 flex justify-center gap-2 overflow-x-auto pb-4">
                  <button
                    v-for="(image, index) in images"
                    :key="image.id || index"
                    type="button"
                    class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors"
                    :class="lightboxIndex === index ? 'border-white' : 'border-transparent opacity-60 hover:opacity-100'"
                    @click="lightboxIndex = index"
                  >
                    <img
                      :src="image.thumbnail || image.src"
                      :alt="image.alt"
                      class="w-full h-full object-cover"
                    />
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { ChevronLeftIcon, ChevronRightIcon, XMarkIcon, MagnifyingGlassPlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  images: {
    type: Array,
    required: true
    // [{ id, src, thumbnail, alt, caption }]
  },
  layout: {
    type: String,
    default: 'grid',
    validator: (value) => ['grid', 'masonry', 'carousel'].includes(value)
  },
  columns: {
    type: Number,
    default: 3,
    validator: (value) => value >= 1 && value <= 6
  },
  aspectRatio: {
    type: String,
    default: 'square',
    validator: (value) => ['square', 'video', 'portrait', 'auto'].includes(value)
  },
  showCaptions: {
    type: Boolean,
    default: true
  },
  showThumbnails: {
    type: Boolean,
    default: true
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  autoplayInterval: {
    type: Number,
    default: 5000
  }
})

const emit = defineEmits(['image-click', 'lightbox-open', 'lightbox-close'])

// State
const lightboxOpen = ref(false)
const lightboxIndex = ref(0)
const currentSlide = ref(0)
let autoplayTimer = null

// Current lightbox image
const currentImage = computed(() => props.images[lightboxIndex.value])

// Grid columns classes
const gridColsClasses = computed(() => {
  const cols = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
    5: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5',
    6: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-6'
  }
  return cols[props.columns]
})

// Image container aspect ratio
const imageContainerClasses = computed(() => {
  const ratios = {
    square: 'aspect-square',
    video: 'aspect-video',
    portrait: 'aspect-[3/4]',
    auto: ''
  }
  return ratios[props.aspectRatio]
})

// Lightbox controls
function openLightbox(index) {
  lightboxIndex.value = index
  lightboxOpen.value = true
  emit('image-click', { image: props.images[index], index })
  emit('lightbox-open', index)
  document.body.style.overflow = 'hidden'
}

function closeLightbox() {
  lightboxOpen.value = false
  emit('lightbox-close')
  document.body.style.overflow = ''
}

function previousImage() {
  lightboxIndex.value = lightboxIndex.value === 0 
    ? props.images.length - 1 
    : lightboxIndex.value - 1
}

function nextImage() {
  lightboxIndex.value = lightboxIndex.value === props.images.length - 1 
    ? 0 
    : lightboxIndex.value + 1
}

// Carousel controls
function previousSlide() {
  currentSlide.value = currentSlide.value === 0 
    ? props.images.length - 1 
    : currentSlide.value - 1
}

function nextSlide() {
  currentSlide.value = currentSlide.value === props.images.length - 1 
    ? 0 
    : currentSlide.value + 1
}

// Keyboard navigation
function handleKeydown(e) {
  if (!lightboxOpen.value) return
  
  if (e.key === 'ArrowLeft') previousImage()
  if (e.key === 'ArrowRight') nextImage()
  if (e.key === 'Escape') closeLightbox()
}

// Autoplay for carousel
function startAutoplay() {
  if (props.autoplay && props.layout === 'carousel') {
    autoplayTimer = setInterval(nextSlide, props.autoplayInterval)
  }
}

function stopAutoplay() {
  if (autoplayTimer) {
    clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  startAutoplay()
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  stopAutoplay()
})
</script>
