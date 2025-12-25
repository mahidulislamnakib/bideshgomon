<template>
  <div
    :class="[
      orientation === 'horizontal' ? 'w-full' : 'h-full',
      containerClasses
    ]"
    role="separator"
    :aria-orientation="orientation"
  >
    <!-- Horizontal layout -->
    <template v-if="orientation === 'horizontal'">
      <div class="flex items-center" :class="spacingClasses">
        <!-- Left line -->
        <div
          class="flex-grow border-t"
          :class="[lineClasses, leftLineClasses]"
        />
        
        <!-- Label/Icon -->
        <template v-if="$slots.default || label || icon">
          <div
            class="flex-shrink-0 px-4"
            :class="labelClasses"
          >
            <slot>
              <component
                v-if="icon"
                :is="icon"
                class="w-5 h-5"
                :class="iconClasses"
              />
              <span v-else-if="label">{{ label }}</span>
            </slot>
          </div>
        </template>
        
        <!-- Right line -->
        <div
          class="flex-grow border-t"
          :class="[lineClasses, rightLineClasses]"
        />
      </div>
    </template>

    <!-- Vertical layout -->
    <template v-else>
      <div class="flex flex-col items-center h-full" :class="spacingClasses">
        <!-- Top line -->
        <div
          class="flex-grow border-l"
          :class="[lineClasses, topLineClasses]"
        />
        
        <!-- Label/Icon -->
        <template v-if="$slots.default || label || icon">
          <div
            class="flex-shrink-0 py-4"
            :class="labelClasses"
          >
            <slot>
              <component
                v-if="icon"
                :is="icon"
                class="w-5 h-5"
                :class="iconClasses"
              />
              <span v-else-if="label" class="writing-mode-vertical">{{ label }}</span>
            </slot>
          </div>
        </template>
        
        <!-- Bottom line -->
        <div
          class="flex-grow border-l"
          :class="[lineClasses, bottomLineClasses]"
        />
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  orientation: {
    type: String,
    default: 'horizontal',
    validator: (value) => ['horizontal', 'vertical'].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  icon: {
    type: [Object, Function],
    default: null
  },
  variant: {
    type: String,
    default: 'solid',
    validator: (value) => ['solid', 'dashed', 'dotted'].includes(value)
  },
  color: {
    type: String,
    default: 'gray',
    validator: (value) => ['gray', 'primary', 'success', 'warning', 'error', 'light', 'dark'].includes(value)
  },
  thickness: {
    type: String,
    default: 'normal',
    validator: (value) => ['thin', 'normal', 'thick'].includes(value)
  },
  align: {
    type: String,
    default: 'center',
    validator: (value) => ['start', 'center', 'end'].includes(value)
  },
  spacing: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value)
  }
})

// Container classes
const containerClasses = computed(() => {
  return ''
})

// Spacing classes
const spacingClasses = computed(() => {
  const spacing = {
    none: '',
    sm: props.orientation === 'horizontal' ? 'my-2' : 'mx-2',
    md: props.orientation === 'horizontal' ? 'my-4' : 'mx-4',
    lg: props.orientation === 'horizontal' ? 'my-8' : 'mx-8'
  }
  return spacing[props.spacing]
})

// Line styling
const lineClasses = computed(() => {
  const variantClasses = {
    solid: 'border-solid',
    dashed: 'border-dashed',
    dotted: 'border-dotted'
  }
  
  const colorClasses = {
    gray: 'border-gray-200',
    primary: 'border-primary-300',
    success: 'border-green-300',
    warning: 'border-yellow-300',
    error: 'border-red-300',
    light: 'border-gray-100',
    dark: 'border-gray-400'
  }
  
  const thicknessClasses = {
    thin: '',
    normal: 'border-t-2',
    thick: 'border-t-4'
  }
  
  // Only add thickness for horizontal, border-l-X for vertical
  const thickClass = props.orientation === 'horizontal' 
    ? thicknessClasses[props.thickness]
    : props.thickness === 'normal' ? 'border-l-2' : props.thickness === 'thick' ? 'border-l-4' : ''
  
  return [variantClasses[props.variant], colorClasses[props.color], thickClass].join(' ')
})

// Alignment-based line classes
const leftLineClasses = computed(() => {
  if (props.align === 'start') return 'flex-grow-0 w-8'
  if (props.align === 'end') return 'flex-grow'
  return 'flex-grow'
})

const rightLineClasses = computed(() => {
  if (props.align === 'start') return 'flex-grow'
  if (props.align === 'end') return 'flex-grow-0 w-8'
  return 'flex-grow'
})

const topLineClasses = computed(() => {
  if (props.align === 'start') return 'flex-grow-0 h-8'
  if (props.align === 'end') return 'flex-grow'
  return 'flex-grow'
})

const bottomLineClasses = computed(() => {
  if (props.align === 'start') return 'flex-grow'
  if (props.align === 'end') return 'flex-grow-0 h-8'
  return 'flex-grow'
})

// Label styling
const labelClasses = computed(() => {
  const colorClasses = {
    gray: 'text-gray-500',
    primary: 'text-primary-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600',
    light: 'text-gray-400',
    dark: 'text-gray-700'
  }
  return `text-sm font-medium ${colorClasses[props.color]}`
})

// Icon styling
const iconClasses = computed(() => {
  const colorClasses = {
    gray: 'text-gray-400',
    primary: 'text-primary-500',
    success: 'text-green-500',
    warning: 'text-yellow-500',
    error: 'text-red-500',
    light: 'text-gray-300',
    dark: 'text-gray-600'
  }
  return colorClasses[props.color]
})
</script>

<style scoped>
.writing-mode-vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
}
</style>
