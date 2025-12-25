<template>
  <div class="relative overflow-hidden" :style="containerStyle">
    <!-- Content -->
    <slot />

    <!-- Watermark overlay -->
    <div
      v-if="!disabled"
      class="absolute inset-0 pointer-events-none overflow-hidden"
      :style="overlayStyle"
    >
      <div
        ref="watermarkRef"
        class="absolute"
        :style="watermarkContainerStyle"
      >
        <!-- Tiled watermarks -->
        <div
          v-for="(row, rowIndex) in rows"
          :key="`row-${rowIndex}`"
          class="flex"
          :style="{ marginLeft: rowIndex % 2 === 1 ? `${gapX / 2}px` : '0' }"
        >
          <div
            v-for="(col, colIndex) in cols"
            :key="`col-${colIndex}`"
            class="flex-shrink-0 flex items-center justify-center"
            :style="itemStyle"
          >
            <!-- Text watermark -->
            <span
              v-if="!image"
              class="whitespace-nowrap select-none"
              :style="textStyle"
            >
              {{ content }}
            </span>
            
            <!-- Image watermark -->
            <img
              v-else
              :src="image"
              :alt="content || 'Watermark'"
              class="select-none"
              :style="imageStyle"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  content: {
    type: String,
    default: 'Watermark'
  },
  image: {
    type: String,
    default: ''
  },
  width: {
    type: Number,
    default: 120
  },
  height: {
    type: Number,
    default: 64
  },
  rotate: {
    type: Number,
    default: -22
  },
  gapX: {
    type: Number,
    default: 100
  },
  gapY: {
    type: Number,
    default: 100
  },
  fontSize: {
    type: Number,
    default: 14
  },
  fontWeight: {
    type: [Number, String],
    default: 400
  },
  fontFamily: {
    type: String,
    default: 'inherit'
  },
  color: {
    type: String,
    default: 'rgba(0, 0, 0, 0.1)'
  },
  zIndex: {
    type: Number,
    default: 10
  },
  fullPage: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

// Refs
const watermarkRef = ref(null)
const containerSize = ref({ width: 0, height: 0 })

// Calculate grid size
const rows = computed(() => {
  const totalHeight = props.fullPage ? window.innerHeight : containerSize.value.height
  return Math.ceil((totalHeight * 2) / (props.height + props.gapY)) + 2
})

const cols = computed(() => {
  const totalWidth = props.fullPage ? window.innerWidth : containerSize.value.width
  return Math.ceil((totalWidth * 2) / (props.width + props.gapX)) + 2
})

// Styles
const containerStyle = computed(() => {
  return props.fullPage ? {
    position: 'fixed',
    inset: 0
  } : {}
})

const overlayStyle = computed(() => {
  return {
    zIndex: props.zIndex
  }
})

const watermarkContainerStyle = computed(() => {
  const offset = Math.max(props.width, props.height)
  return {
    top: `-${offset}px`,
    left: `-${offset}px`,
    transform: `rotate(${props.rotate}deg)`,
    transformOrigin: 'center center'
  }
})

const itemStyle = computed(() => {
  return {
    width: `${props.width + props.gapX}px`,
    height: `${props.height + props.gapY}px`
  }
})

const textStyle = computed(() => {
  return {
    fontSize: `${props.fontSize}px`,
    fontWeight: props.fontWeight,
    fontFamily: props.fontFamily,
    color: props.color
  }
})

const imageStyle = computed(() => {
  return {
    width: `${props.width}px`,
    height: 'auto',
    opacity: 0.15
  }
})

// Resize observer
let resizeObserver = null

function updateSize() {
  if (watermarkRef.value?.parentElement) {
    const parent = watermarkRef.value.parentElement.parentElement
    if (parent) {
      containerSize.value = {
        width: parent.offsetWidth,
        height: parent.offsetHeight
      }
    }
  }
}

onMounted(() => {
  updateSize()
  
  resizeObserver = new ResizeObserver(updateSize)
  if (watermarkRef.value?.parentElement?.parentElement) {
    resizeObserver.observe(watermarkRef.value.parentElement.parentElement)
  }
  
  if (props.fullPage) {
    window.addEventListener('resize', updateSize)
  }
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
  if (props.fullPage) {
    window.removeEventListener('resize', updateSize)
  }
})
</script>
