<template>
  <div class="rating-input">
    <!-- Stars Variant -->
    <div v-if="variant === 'stars'" class="flex items-center gap-1">
      <button
        v-for="i in max"
        :key="i"
        type="button"
        :disabled="disabled || readonly"
        class="focus:outline-none transition-transform hover:scale-110 disabled:hover:scale-100"
        @click="setRating(i)"
        @mouseenter="hoverValue = i"
        @mouseleave="hoverValue = 0"
      >
        <component
          :is="getStarIcon(i)"
          :class="[
            'transition-colors',
            sizeClass,
            getStarColor(i)
          ]"
        />
      </button>
      <span v-if="showValue" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ displayValue }}
      </span>
    </div>

    <!-- Hearts Variant -->
    <div v-else-if="variant === 'hearts'" class="flex items-center gap-1">
      <button
        v-for="i in max"
        :key="i"
        type="button"
        :disabled="disabled || readonly"
        class="focus:outline-none transition-transform hover:scale-110 disabled:hover:scale-100"
        @click="setRating(i)"
        @mouseenter="hoverValue = i"
        @mouseleave="hoverValue = 0"
      >
        <HeartSolidIcon
          v-if="isActive(i)"
          :class="['transition-colors', sizeClass, 'text-red-500']"
        />
        <HeartOutlineIcon
          v-else
          :class="['transition-colors', sizeClass, 'text-gray-300 dark:text-gray-600']"
        />
      </button>
      <span v-if="showValue" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ displayValue }}
      </span>
    </div>

    <!-- Thumbs Variant -->
    <div v-else-if="variant === 'thumbs'" class="flex items-center gap-3">
      <button
        type="button"
        :disabled="disabled || readonly"
        :class="[
          'p-2 rounded-lg transition-all',
          modelValue === 1
            ? 'bg-green-100 dark:bg-green-900/30 text-green-600'
            : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
        ]"
        @click="setRating(modelValue === 1 ? 0 : 1)"
      >
        <HandThumbUpIcon :class="sizeClass" />
      </button>
      <button
        type="button"
        :disabled="disabled || readonly"
        :class="[
          'p-2 rounded-lg transition-all',
          modelValue === -1
            ? 'bg-red-100 dark:bg-red-900/30 text-red-600'
            : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
        ]"
        @click="setRating(modelValue === -1 ? 0 : -1)"
      >
        <HandThumbDownIcon :class="sizeClass" />
      </button>
    </div>

    <!-- Emoji Variant -->
    <div v-else-if="variant === 'emoji'" class="flex items-center gap-2">
      <button
        v-for="(emoji, index) in emojis"
        :key="index"
        type="button"
        :disabled="disabled || readonly"
        :class="[
          'text-2xl transition-all rounded-lg p-1',
          modelValue === index + 1
            ? 'scale-125 bg-yellow-100 dark:bg-yellow-900/30'
            : 'opacity-50 hover:opacity-100 hover:scale-110'
        ]"
        @click="setRating(index + 1)"
      >
        {{ emoji }}
      </button>
    </div>

    <!-- Numeric Variant -->
    <div v-else-if="variant === 'numeric'" class="flex items-center gap-1">
      <button
        v-for="i in max"
        :key="i"
        type="button"
        :disabled="disabled || readonly"
        :class="[
          'w-8 h-8 rounded-lg font-medium transition-all',
          isActive(i)
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
        ]"
        @click="setRating(i)"
      >
        {{ i }}
      </button>
      <span v-if="showValue" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ displayValue }}
      </span>
    </div>

    <!-- Slider Variant -->
    <div v-else-if="variant === 'slider'" class="flex items-center gap-4">
      <span class="text-sm text-gray-500">{{ min }}</span>
      <input
        type="range"
        :min="min"
        :max="max"
        :step="step"
        :value="modelValue"
        :disabled="disabled || readonly"
        class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600"
        @input="setRating(Number($event.target.value))"
      />
      <span class="text-sm text-gray-500">{{ max }}</span>
      <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-sm font-bold min-w-[40px] text-center">
        {{ modelValue }}
      </span>
    </div>

    <!-- Custom Icons Variant -->
    <div v-else class="flex items-center gap-1">
      <button
        v-for="i in max"
        :key="i"
        type="button"
        :disabled="disabled || readonly"
        class="focus:outline-none transition-transform hover:scale-110 disabled:hover:scale-100"
        @click="setRating(i)"
        @mouseenter="hoverValue = i"
        @mouseleave="hoverValue = 0"
      >
        <component
          :is="isActive(i) ? activeIcon : inactiveIcon"
          :class="[
            'transition-colors',
            sizeClass,
            isActive(i) ? activeColor : 'text-gray-300 dark:text-gray-600'
          ]"
        />
      </button>
    </div>

    <!-- Labels -->
    <div v-if="showLabels && labels.length" class="flex justify-between mt-1">
      <span
        v-for="(label, index) in labels"
        :key="index"
        class="text-xs text-gray-500"
      >
        {{ label }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  StarIcon as StarOutlineIcon,
  HeartIcon as HeartOutlineIcon,
  HandThumbUpIcon,
  HandThumbDownIcon
} from '@heroicons/vue/24/outline'
import {
  StarIcon as StarSolidIcon,
  HeartIcon as HeartSolidIcon
} from '@heroicons/vue/24/solid'

const props = defineProps({
  modelValue: { type: Number, default: 0 },
  max: { type: Number, default: 5 },
  min: { type: Number, default: 0 },
  step: { type: Number, default: 1 },
  variant: { type: String, default: 'stars' }, // stars, hearts, thumbs, emoji, numeric, slider, custom
  size: { type: String, default: 'md' }, // sm, md, lg, xl
  allowHalf: { type: Boolean, default: false },
  allowClear: { type: Boolean, default: true },
  showValue: { type: Boolean, default: false },
  showLabels: { type: Boolean, default: false },
  labels: { type: Array, default: () => [] },
  readonly: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  activeColor: { type: String, default: 'text-yellow-400' },
  activeIcon: { type: Object, default: () => StarSolidIcon },
  inactiveIcon: { type: Object, default: () => StarOutlineIcon },
  emojis: { type: Array, default: () => ['😞', '😕', '😐', '🙂', '😄'] }
})

const emit = defineEmits(['update:modelValue', 'change'])

const hoverValue = ref(0)

const sizeClass = computed(() => {
  const sizes = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-10 h-10'
  }
  return sizes[props.size] || sizes.md
})

const displayValue = computed(() => {
  if (props.variant === 'thumbs') {
    return props.modelValue === 1 ? 'Liked' : props.modelValue === -1 ? 'Disliked' : 'No rating'
  }
  return `${props.modelValue}/${props.max}`
})

const isActive = (index) => {
  const value = hoverValue.value || props.modelValue
  return index <= value
}

const getStarIcon = (index) => {
  const value = hoverValue.value || props.modelValue
  
  if (props.allowHalf) {
    if (index <= Math.floor(value)) return StarSolidIcon
    if (index - 0.5 <= value) return StarSolidIcon // Half star would need a custom icon
    return StarOutlineIcon
  }
  
  return index <= value ? StarSolidIcon : StarOutlineIcon
}

const getStarColor = (index) => {
  const value = hoverValue.value || props.modelValue
  return index <= value ? props.activeColor : 'text-gray-300 dark:text-gray-600'
}

const setRating = (value) => {
  if (props.disabled || props.readonly) return
  
  // Allow clearing if clicking same value
  const newValue = props.allowClear && value === props.modelValue ? 0 : value
  
  emit('update:modelValue', newValue)
  emit('change', newValue)
}
</script>
