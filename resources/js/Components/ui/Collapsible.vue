<template>
  <div :class="containerClasses">
    <!-- Header (always visible) -->
    <button
      type="button"
      class="flex w-full items-center justify-between py-3 text-left transition-colors focus:outline-none"
      :class="headerClasses"
      :disabled="disabled"
      @click="toggle"
    >
      <!-- Leading icon -->
      <div class="flex items-center gap-3">
        <component
          v-if="icon"
          :is="icon"
          class="h-5 w-5 flex-shrink-0"
          :class="iconClasses"
        />
        
        <!-- Title -->
        <div>
          <span class="font-medium" :class="titleClasses">
            <slot name="title">{{ title }}</slot>
          </span>
          <p v-if="subtitle" class="text-sm mt-0.5" :class="subtitleClasses">
            {{ subtitle }}
          </p>
        </div>
      </div>

      <!-- Trailing content + Chevron -->
      <div class="flex items-center gap-3">
        <slot name="trailing" />
        
        <!-- Badge -->
        <span
          v-if="badge"
          class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
          :class="badgeClasses"
        >
          {{ badge }}
        </span>
        
        <!-- Chevron -->
        <component
          :is="isOpen ? ChevronUpIcon : ChevronDownIcon"
          class="h-5 w-5 flex-shrink-0 transition-transform duration-200"
          :class="[chevronClasses, { 'rotate-180': isOpen && animated }]"
        />
      </div>
    </button>

    <!-- Collapsible content -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-screen"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 max-h-screen"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-show="isOpen" class="overflow-hidden">
        <div :class="contentClasses">
          <slot />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: undefined // undefined = uncontrolled
  },
  defaultOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: [Object, Function],
    default: null
  },
  badge: {
    type: [String, Number],
    default: null
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'filled', 'ghost'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  animated: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'toggle'])

// Internal state for uncontrolled mode
const internalOpen = ref(props.defaultOpen)

// Computed open state
const isOpen = computed({
  get() {
    return props.modelValue !== undefined ? props.modelValue : internalOpen.value
  },
  set(value) {
    if (props.modelValue !== undefined) {
      emit('update:modelValue', value)
    } else {
      internalOpen.value = value
    }
    emit('toggle', value)
  }
})

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal !== undefined) {
    internalOpen.value = newVal
  }
})

// Toggle function
function toggle() {
  if (props.disabled) return
  isOpen.value = !isOpen.value
}

// Styling based on variant
const containerClasses = computed(() => {
  const variants = {
    default: '',
    bordered: 'border border-gray-200 rounded-lg',
    filled: 'bg-gray-50 rounded-lg',
    ghost: ''
  }
  return `${variants[props.variant]} ${props.disabled ? 'opacity-50' : ''}`
})

const headerClasses = computed(() => {
  const base = props.disabled ? 'cursor-not-allowed' : 'cursor-pointer'
  const variants = {
    default: `${base} hover:bg-gray-50 rounded-lg px-1`,
    bordered: `${base} hover:bg-gray-50 px-4`,
    filled: `${base} hover:bg-gray-100 px-4`,
    ghost: `${base} hover:text-primary-600`
  }
  return variants[props.variant]
})

const contentClasses = computed(() => {
  const variants = {
    default: 'pb-4 px-1',
    bordered: 'pb-4 px-4 border-t border-gray-100',
    filled: 'pb-4 px-4',
    ghost: 'pb-4'
  }
  return variants[props.variant]
})

const titleClasses = 'text-gray-900'
const subtitleClasses = 'text-gray-500'
const iconClasses = 'text-gray-400'
const chevronClasses = 'text-gray-400'

const badgeClasses = computed(() => {
  return 'bg-gray-100 text-gray-600'
})

// Expose methods
defineExpose({
  toggle,
  open: () => { isOpen.value = true },
  close: () => { isOpen.value = false },
  isOpen
})
</script>
