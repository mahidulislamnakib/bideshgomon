<template>
  <RadioGroup :model-value="modelValue" @update:model-value="handleChange" :disabled="disabled">
    <!-- Label -->
    <RadioGroupLabel v-if="label" class="block text-sm font-medium mb-3" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </RadioGroupLabel>

    <!-- Description -->
    <p v-if="description" class="text-sm text-gray-500 mb-4">
      {{ description }}
    </p>

    <!-- Options -->
    <div :class="layoutClasses">
      <RadioGroupOption
        v-for="option in options"
        :key="option.value"
        v-slot="{ checked, active }"
        :value="option.value"
        :disabled="option.disabled"
        as="template"
      >
        <!-- Card variant -->
        <div
          v-if="variant === 'card'"
          class="relative flex cursor-pointer rounded-lg border p-4 transition-all focus:outline-none"
          :class="[
            checked ? 'border-primary-600 bg-primary-50 ring-2 ring-primary-600' : 'border-gray-200 bg-white hover:border-gray-300',
            option.disabled ? 'opacity-50 cursor-not-allowed' : '',
            active ? 'ring-2 ring-primary-600 ring-offset-2' : ''
          ]"
        >
          <div class="flex w-full items-start gap-3">
            <!-- Radio circle -->
            <div
              class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2 transition-colors"
              :class="checked ? 'border-primary-600 bg-primary-600' : 'border-gray-300 bg-white'"
            >
              <span v-if="checked" class="h-2 w-2 rounded-full bg-white" />
            </div>

            <!-- Content -->
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <RadioGroupLabel as="span" class="font-medium" :class="checked ? 'text-primary-900' : 'text-gray-900'">
                  {{ option.label }}
                </RadioGroupLabel>
                <span
                  v-if="option.badge"
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="option.badgeColor || 'bg-gray-100 text-gray-600'"
                >
                  {{ option.badge }}
                </span>
              </div>
              <RadioGroupDescription v-if="option.description" as="span" class="block text-sm mt-1" :class="checked ? 'text-primary-700' : 'text-gray-500'">
                {{ option.description }}
              </RadioGroupDescription>
            </div>

            <!-- Icon -->
            <component
              v-if="option.icon"
              :is="option.icon"
              class="h-6 w-6 flex-shrink-0"
              :class="checked ? 'text-primary-600' : 'text-gray-400'"
            />
          </div>
        </div>

        <!-- Simple variant -->
        <div
          v-else
          class="relative flex cursor-pointer items-start py-2"
          :class="option.disabled ? 'opacity-50 cursor-not-allowed' : ''"
        >
          <div class="flex h-5 items-center">
            <div
              class="flex h-4 w-4 items-center justify-center rounded-full border transition-colors"
              :class="checked ? 'border-primary-600 bg-primary-600' : 'border-gray-300 bg-white'"
            >
              <span v-if="checked" class="h-1.5 w-1.5 rounded-full bg-white" />
            </div>
          </div>
          <div class="ml-3 text-sm">
            <RadioGroupLabel
              as="span"
              class="font-medium"
              :class="checked ? 'text-primary-700' : 'text-gray-900'"
            >
              {{ option.label }}
            </RadioGroupLabel>
            <RadioGroupDescription
              v-if="option.description"
              as="span"
              class="block text-gray-500"
            >
              {{ option.description }}
            </RadioGroupDescription>
          </div>
        </div>
      </RadioGroupOption>
    </div>

    <!-- Error message -->
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </RadioGroup>
</template>

<script setup>
import { computed } from 'vue'
import {
  RadioGroup,
  RadioGroupLabel,
  RadioGroupDescription,
  RadioGroupOption
} from '@headlessui/vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  },
  options: {
    type: Array,
    required: true
    // [{ value, label, description?, icon?, badge?, badgeColor?, disabled? }]
  },
  label: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'simple',
    validator: (value) => ['simple', 'card'].includes(value)
  },
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal', 'grid'].includes(value)
  },
  columns: {
    type: Number,
    default: 2
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Handle change
function handleChange(value) {
  emit('update:modelValue', value)
  emit('change', value)
}

// Layout classes
const layoutClasses = computed(() => {
  if (props.variant === 'card') {
    const layouts = {
      vertical: 'space-y-3',
      horizontal: 'flex flex-wrap gap-3',
      grid: `grid gap-3 grid-cols-1 sm:grid-cols-${props.columns}`
    }
    return layouts[props.layout]
  }
  
  const layouts = {
    vertical: 'space-y-2',
    horizontal: 'flex flex-wrap gap-x-6 gap-y-2',
    grid: `grid gap-2 grid-cols-1 sm:grid-cols-${props.columns}`
  }
  return layouts[props.layout]
})

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})
</script>
