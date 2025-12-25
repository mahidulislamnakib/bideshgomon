<template>
  <div 
    ref="dockRef"
    :class="containerClasses"
    :style="containerStyle"
    @mousemove="onMouseMove"
    @mouseleave="onMouseLeave"
  >
    <div :class="dockClasses">
      <TransitionGroup
        tag="div"
        :class="itemsClasses"
        name="dock-item"
      >
        <div
          v-for="(item, index) in items"
          :key="item.id || index"
          ref="itemRefs"
          :class="itemClasses"
          :style="getItemStyle(index)"
          @click="handleClick(item, index)"
          @mouseenter="hoveredIndex = index"
          @mouseleave="hoveredIndex = -1"
        >
          <!-- Icon -->
          <div :class="iconWrapperClasses">
            <component 
              v-if="item.icon" 
              :is="item.icon" 
              :class="iconClasses"
            />
            <img 
              v-else-if="item.image" 
              :src="item.image" 
              :alt="item.label"
              :class="iconClasses"
            />
            <span v-else :class="iconClasses">
              {{ item.label?.charAt(0)?.toUpperCase() || '?' }}
            </span>
            
            <!-- Badge -->
            <span 
              v-if="item.badge"
              :class="badgeClasses"
            >
              {{ item.badge }}
            </span>
            
            <!-- Active indicator -->
            <span 
              v-if="item.active"
              :class="activeIndicatorClasses"
            />
          </div>
          
          <!-- Tooltip -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-95"
          >
            <div
              v-if="showTooltips && hoveredIndex === index"
              :class="tooltipClasses"
            >
              {{ item.label }}
            </div>
          </Transition>
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // Items
  items: {
    type: Array,
    required: true,
    // Each item: { id, label, icon?, image?, badge?, active?, href?, action? }
  },
  
  // Position
  position: {
    type: String,
    default: 'bottom',
    validator: (v) => ['top', 'bottom', 'left', 'right'].includes(v)
  },
  
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  
  // Magnification
  magnification: {
    type: Number,
    default: 1.5
  },
  magnificationDistance: {
    type: Number,
    default: 150
  },
  
  // Features
  showTooltips: {
    type: Boolean,
    default: true
  },
  floating: {
    type: Boolean,
    default: true
  },
  
  // Styling
  blur: {
    type: Boolean,
    default: true
  },
  rounded: {
    type: Boolean,
    default: true
  },
  shadow: {
    type: Boolean,
    default: true
  },
  
  // Gap
  gap: {
    type: Number,
    default: 8
  }
})

const emit = defineEmits(['item-click', 'hover'])

const dockRef = ref(null)
const itemRefs = ref([])
const mousePosition = ref({ x: 0, y: 0 })
const hoveredIndex = ref(-1)
const isHovering = ref(false)

const isHorizontal = computed(() => ['top', 'bottom'].includes(props.position))

// Size config
const sizeConfig = computed(() => {
  const sizes = {
    sm: { icon: 32, wrapper: 48 },
    md: { icon: 40, wrapper: 56 },
    lg: { icon: 48, wrapper: 64 }
  }
  return sizes[props.size]
})

// Container classes
const containerClasses = computed(() => [
  'fixed z-50',
  props.position === 'bottom' && 'bottom-4 left-1/2 -translate-x-1/2',
  props.position === 'top' && 'top-4 left-1/2 -translate-x-1/2',
  props.position === 'left' && 'left-4 top-1/2 -translate-y-1/2',
  props.position === 'right' && 'right-4 top-1/2 -translate-y-1/2'
])

const containerStyle = computed(() => ({}))

// Dock classes
const dockClasses = computed(() => [
  'flex items-center justify-center',
  props.blur && 'backdrop-blur-xl',
  props.rounded && 'rounded-2xl',
  props.shadow && 'shadow-lg',
  'bg-white/80 dark:bg-gray-900/80',
  'border border-gray-200/50 dark:border-gray-700/50',
  'px-3 py-2'
])

// Items container classes
const itemsClasses = computed(() => [
  'flex items-end',
  isHorizontal.value ? 'flex-row' : 'flex-col',
  `gap-${Math.round(props.gap / 4)}`
])

// Item classes
const itemClasses = computed(() => [
  'relative flex flex-col items-center cursor-pointer transition-all duration-200',
  'hover:z-10'
])

// Icon wrapper classes
const iconWrapperClasses = computed(() => [
  'relative flex items-center justify-center',
  'rounded-xl transition-all duration-200',
  'bg-gray-100 dark:bg-gray-800',
  'hover:bg-gray-200 dark:hover:bg-gray-700'
])

// Icon classes
const iconClasses = computed(() => [
  'w-full h-full p-2',
  'text-gray-700 dark:text-gray-200'
])

// Badge classes
const badgeClasses = computed(() => [
  'absolute -top-1 -right-1',
  'min-w-[18px] h-[18px] px-1',
  'flex items-center justify-center',
  'text-xs font-bold text-white',
  'bg-red-500 rounded-full'
])

// Active indicator classes
const activeIndicatorClasses = computed(() => [
  'absolute -bottom-1.5 left-1/2 -translate-x-1/2',
  'w-1.5 h-1.5 rounded-full',
  'bg-gray-500 dark:bg-gray-400'
])

// Tooltip classes
const tooltipClasses = computed(() => [
  'absolute whitespace-nowrap',
  'px-3 py-1.5 text-sm font-medium',
  'bg-gray-900 dark:bg-gray-100',
  'text-white dark:text-gray-900',
  'rounded-lg shadow-lg',
  props.position === 'bottom' && 'bottom-full mb-2',
  props.position === 'top' && 'top-full mt-2',
  props.position === 'left' && 'left-full ml-2',
  props.position === 'right' && 'right-full mr-2'
])

// Calculate magnification for each item
function getItemStyle(index) {
  if (!isHovering.value) {
    return {
      width: `${sizeConfig.value.wrapper}px`,
      height: `${sizeConfig.value.wrapper}px`
    }
  }
  
  const itemEl = itemRefs.value[index]
  if (!itemEl) {
    return {
      width: `${sizeConfig.value.wrapper}px`,
      height: `${sizeConfig.value.wrapper}px`
    }
  }
  
  const rect = itemEl.getBoundingClientRect()
  const itemCenter = isHorizontal.value
    ? rect.left + rect.width / 2
    : rect.top + rect.height / 2
  
  const mousePos = isHorizontal.value ? mousePosition.value.x : mousePosition.value.y
  const distance = Math.abs(mousePos - itemCenter)
  
  // Calculate scale based on distance
  const scale = distance < props.magnificationDistance
    ? props.magnification - ((props.magnification - 1) * (distance / props.magnificationDistance))
    : 1
  
  const size = sizeConfig.value.wrapper * scale
  
  return {
    width: `${size}px`,
    height: `${size}px`
  }
}

// Mouse event handlers
function onMouseMove(e) {
  isHovering.value = true
  mousePosition.value = { x: e.clientX, y: e.clientY }
}

function onMouseLeave() {
  isHovering.value = false
  hoveredIndex.value = -1
}

// Item click handler
function handleClick(item, index) {
  if (item.href) {
    window.location.href = item.href
  } else if (item.action) {
    item.action()
  }
  emit('item-click', { item, index })
}

// Expose for parent control
defineExpose({
  hoveredIndex,
  isHovering
})
</script>

<style scoped>
.dock-item-enter-active,
.dock-item-leave-active {
  transition: all 0.3s ease;
}

.dock-item-enter-from,
.dock-item-leave-to {
  opacity: 0;
  transform: scale(0.5);
}

.dock-item-move {
  transition: transform 0.3s ease;
}
</style>
