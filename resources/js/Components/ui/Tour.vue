<template>
  <Teleport to="body">
    <!-- Backdrop overlay -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isActive"
        class="fixed inset-0 z-[9998]"
        :style="backdropStyle"
      />
    </Transition>

    <!-- Tooltip/Popover -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="isActive && currentStep"
        ref="tooltipRef"
        class="fixed z-[9999] w-80 bg-white rounded-xl shadow-2xl border border-gray-200"
        :style="tooltipStyle"
      >
        <!-- Arrow -->
        <div
          class="absolute w-3 h-3 bg-white border-gray-200 transform rotate-45"
          :class="arrowClasses"
        />

        <!-- Content -->
        <div class="relative p-4">
          <!-- Header -->
          <div class="flex items-start justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ currentStep.title }}
            </h3>
            <button
              v-if="showClose"
              type="button"
              class="text-gray-400 hover:text-gray-600 transition-colors"
              @click="close"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>

          <!-- Description -->
          <p class="text-sm text-gray-600 mb-4">
            {{ currentStep.description }}
          </p>

          <!-- Custom content slot -->
          <slot :step="currentStep" :index="currentIndex" />

          <!-- Footer -->
          <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <!-- Progress dots -->
            <div v-if="showProgress" class="flex items-center gap-1.5">
              <button
                v-for="(step, index) in steps"
                :key="index"
                type="button"
                class="w-2 h-2 rounded-full transition-colors"
                :class="index === currentIndex ? 'bg-primary-600' : 'bg-gray-300 hover:bg-gray-400'"
                @click="goTo(index)"
              />
            </div>
            <span v-else class="text-xs text-gray-500">
              {{ currentIndex + 1 }} / {{ steps.length }}
            </span>

            <!-- Navigation buttons -->
            <div class="flex items-center gap-2">
              <button
                v-if="currentIndex > 0"
                type="button"
                class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 transition-colors"
                @click="prev"
              >
                {{ prevLabel }}
              </button>
              <button
                v-if="showSkip && currentIndex < steps.length - 1"
                type="button"
                class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                @click="skip"
              >
                {{ skipLabel }}
              </button>
              <button
                type="button"
                class="px-4 py-1.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors"
                @click="next"
              >
                {{ currentIndex === steps.length - 1 ? finishLabel : nextLabel }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  steps: {
    type: Array,
    required: true
    // Each step: { target: string (selector), title, description, placement? }
  },
  startIndex: {
    type: Number,
    default: 0
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  showClose: {
    type: Boolean,
    default: true
  },
  showSkip: {
    type: Boolean,
    default: true
  },
  prevLabel: {
    type: String,
    default: 'Previous'
  },
  nextLabel: {
    type: String,
    default: 'Next'
  },
  skipLabel: {
    type: String,
    default: 'Skip'
  },
  finishLabel: {
    type: String,
    default: 'Finish'
  },
  highlightPadding: {
    type: Number,
    default: 8
  },
  scrollBehavior: {
    type: String,
    default: 'smooth',
    validator: (v) => ['smooth', 'auto'].includes(v)
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'finish', 'skip', 'close'])

const currentIndex = ref(props.startIndex)
const tooltipRef = ref(null)
const targetRect = ref(null)

const isActive = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const currentStep = computed(() => props.steps[currentIndex.value])

// Calculate backdrop with hole for target element
const backdropStyle = computed(() => {
  if (!targetRect.value) {
    return { backgroundColor: 'rgba(0, 0, 0, 0.5)' }
  }

  const { top, left, width, height } = targetRect.value
  const pad = props.highlightPadding

  return {
    background: `
      linear-gradient(to bottom, 
        rgba(0,0,0,0.5) ${top - pad}px, 
        transparent ${top - pad}px, 
        transparent ${top + height + pad}px, 
        rgba(0,0,0,0.5) ${top + height + pad}px
      ),
      linear-gradient(to right, 
        rgba(0,0,0,0.5) ${left - pad}px, 
        transparent ${left - pad}px, 
        transparent ${left + width + pad}px, 
        rgba(0,0,0,0.5) ${left + width + pad}px
      )
    `,
    backgroundBlendMode: 'darken'
  }
})

// Calculate tooltip position
const tooltipStyle = computed(() => {
  if (!targetRect.value) return { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }

  const { top, left, width, height } = targetRect.value
  const placement = currentStep.value?.placement || 'bottom'
  const gap = 16

  const positions = {
    top: {
      top: `${top - gap}px`,
      left: `${left + width / 2}px`,
      transform: 'translate(-50%, -100%)'
    },
    bottom: {
      top: `${top + height + gap}px`,
      left: `${left + width / 2}px`,
      transform: 'translate(-50%, 0)'
    },
    left: {
      top: `${top + height / 2}px`,
      left: `${left - gap}px`,
      transform: 'translate(-100%, -50%)'
    },
    right: {
      top: `${top + height / 2}px`,
      left: `${left + width + gap}px`,
      transform: 'translate(0, -50%)'
    }
  }

  return positions[placement]
})

// Arrow position classes
const arrowClasses = computed(() => {
  const placement = currentStep.value?.placement || 'bottom'
  const arrows = {
    top: 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 border-b border-r',
    bottom: 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 border-t border-l',
    left: 'right-0 top-1/2 translate-x-1/2 -translate-y-1/2 border-t border-r',
    right: 'left-0 top-1/2 -translate-x-1/2 -translate-y-1/2 border-b border-l'
  }
  return arrows[placement]
})

// Update target element position
async function updateTargetPosition() {
  if (!currentStep.value?.target) {
    targetRect.value = null
    return
  }

  const element = document.querySelector(currentStep.value.target)
  if (!element) {
    targetRect.value = null
    return
  }

  // Scroll element into view
  element.scrollIntoView({ behavior: props.scrollBehavior, block: 'center' })
  
  await nextTick()
  
  const rect = element.getBoundingClientRect()
  targetRect.value = {
    top: rect.top + window.scrollY,
    left: rect.left + window.scrollX,
    width: rect.width,
    height: rect.height
  }
}

function next() {
  if (currentIndex.value < props.steps.length - 1) {
    currentIndex.value++
    emit('change', currentIndex.value)
  } else {
    finish()
  }
}

function prev() {
  if (currentIndex.value > 0) {
    currentIndex.value--
    emit('change', currentIndex.value)
  }
}

function goTo(index) {
  if (index >= 0 && index < props.steps.length) {
    currentIndex.value = index
    emit('change', currentIndex.value)
  }
}

function skip() {
  emit('skip', currentIndex.value)
  close()
}

function finish() {
  emit('finish')
  close()
}

function close() {
  isActive.value = false
  emit('close')
}

// Watch for step changes
watch(currentIndex, updateTargetPosition)

// Watch for tour activation
watch(isActive, async (val) => {
  if (val) {
    currentIndex.value = props.startIndex
    await nextTick()
    updateTargetPosition()
  }
})

// Handle window resize
let resizeObserver = null

onMounted(() => {
  resizeObserver = new ResizeObserver(updateTargetPosition)
  resizeObserver.observe(document.body)
})

onUnmounted(() => {
  resizeObserver?.disconnect()
})

defineExpose({
  next,
  prev,
  goTo,
  skip,
  finish,
  close,
  currentIndex,
  currentStep
})
</script>
