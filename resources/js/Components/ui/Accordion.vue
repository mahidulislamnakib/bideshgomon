<template>
  <div class="divide-y divide-gray-200 dark:divide-gray-700" :class="containerClass">
    <div
      v-for="(item, index) in items"
      :key="item.id || index"
      class="overflow-hidden"
    >
      <!-- Accordion Header -->
      <button
        type="button"
        class="w-full flex items-center justify-between gap-4 py-4 text-left transition-colors"
        :class="headerClasses"
        @click="toggle(index)"
        :aria-expanded="isOpen(index)"
        :aria-controls="`accordion-panel-${index}`"
      >
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <!-- Leading Icon -->
          <component
            v-if="item.icon"
            :is="item.icon"
            class="w-5 h-5 flex-shrink-0"
            :class="isOpen(index) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400'"
          />
          
          <!-- Title & Subtitle -->
          <div class="min-w-0">
            <h3 
              class="font-medium truncate"
              :class="isOpen(index) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white'"
            >
              {{ item.title }}
            </h3>
            <p v-if="item.subtitle && !isOpen(index)" class="text-sm text-gray-500 dark:text-gray-400 truncate">
              {{ item.subtitle }}
            </p>
          </div>
        </div>

        <!-- Badge (optional) -->
        <span
          v-if="item.badge"
          class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded-full"
          :class="getBadgeClass(item.badgeColor)"
        >
          {{ item.badge }}
        </span>

        <!-- Toggle Icon -->
        <ChevronDownIcon
          class="w-5 h-5 flex-shrink-0 transition-transform duration-200"
          :class="[
            isOpen(index) ? 'rotate-180 text-blue-600 dark:text-blue-400' : 'text-gray-400',
            iconPosition === 'left' ? 'order-first' : ''
          ]"
        />
      </button>

      <!-- Accordion Content -->
      <Transition
        enter-active-class="transition-all duration-200 ease-out"
        leave-active-class="transition-all duration-200 ease-in"
        enter-from-class="max-h-0 opacity-0"
        enter-to-class="max-h-[1000px] opacity-100"
        leave-from-class="max-h-[1000px] opacity-100"
        leave-to-class="max-h-0 opacity-0"
      >
        <div
          v-show="isOpen(index)"
          :id="`accordion-panel-${index}`"
          class="overflow-hidden"
        >
          <div class="pb-4" :class="contentPaddingClass">
            <!-- Slot for custom content -->
            <slot :name="`item-${index}`" :item="item" :is-open="isOpen(index)">
              <slot :name="`item-${item.id}`" :item="item" :is-open="isOpen(index)">
                <!-- Default content -->
                <p class="text-gray-600 dark:text-gray-400">
                  {{ item.content }}
                </p>
              </slot>
            </slot>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  // Accordion items array
  items: {
    type: Array,
    required: true,
    default: () => []
    // Each item: { id?, title, subtitle?, content?, icon?, badge?, badgeColor? }
  },
  // Allow multiple panels open
  multiple: {
    type: Boolean,
    default: false
  },
  // Default open panel indexes
  defaultOpen: {
    type: [Number, Array],
    default: null
  },
  // v-model for controlled state
  modelValue: {
    type: [Number, Array],
    default: undefined
  },
  // Variant style
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'bordered', 'separated', 'flush'].includes(v)
  },
  // Icon position
  iconPosition: {
    type: String,
    default: 'right',
    validator: (v) => ['left', 'right'].includes(v)
  },
  // Disable all
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Internal state for open panels
const internalOpen = ref([])

// Initialize open state
function initializeOpen() {
  if (props.modelValue !== undefined) {
    internalOpen.value = Array.isArray(props.modelValue) ? [...props.modelValue] : [props.modelValue]
  } else if (props.defaultOpen !== null) {
    internalOpen.value = Array.isArray(props.defaultOpen) ? [...props.defaultOpen] : [props.defaultOpen]
  }
}

initializeOpen()

// Watch for external changes
watch(() => props.modelValue, (val) => {
  if (val !== undefined) {
    internalOpen.value = Array.isArray(val) ? [...val] : [val]
  }
})

// Check if panel is open
function isOpen(index) {
  return internalOpen.value.includes(index)
}

// Toggle panel
function toggle(index) {
  if (props.disabled) return
  
  let newOpen
  
  if (isOpen(index)) {
    // Close
    newOpen = internalOpen.value.filter(i => i !== index)
  } else {
    // Open
    if (props.multiple) {
      newOpen = [...internalOpen.value, index]
    } else {
      newOpen = [index]
    }
  }
  
  internalOpen.value = newOpen
  
  // Emit events
  const emitValue = props.multiple ? newOpen : (newOpen[0] ?? null)
  emit('update:modelValue', emitValue)
  emit('change', { index, isOpen: isOpen(index), openPanels: newOpen })
}

// Container class based on variant
const containerClass = computed(() => {
  const variants = {
    default: 'border border-gray-200 dark:border-gray-700 rounded-lg',
    bordered: 'border-2 border-gray-300 dark:border-gray-600 rounded-xl',
    separated: 'space-y-2 divide-y-0',
    flush: ''
  }
  return variants[props.variant]
})

// Header classes
const headerClasses = computed(() => {
  const base = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900'
  
  if (props.disabled) {
    return `${base} cursor-not-allowed opacity-50`
  }
  
  const variants = {
    default: 'px-4 hover:bg-gray-50 dark:hover:bg-gray-800/50',
    bordered: 'px-4 hover:bg-gray-50 dark:hover:bg-gray-800/50',
    separated: 'px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
    flush: 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
  }
  
  return `${base} ${variants[props.variant]}`
})

// Content padding class
const contentPaddingClass = computed(() => {
  const variants = {
    default: 'px-4',
    bordered: 'px-4',
    separated: 'px-4',
    flush: ''
  }
  return variants[props.variant]
})

// Badge color classes
function getBadgeClass(color = 'gray') {
  const colors = {
    gray: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
  }
  return colors[color] || colors.gray
}

// Expose methods
defineExpose({
  toggle,
  isOpen,
  openAll: () => {
    internalOpen.value = props.items.map((_, i) => i)
    emit('update:modelValue', props.multiple ? internalOpen.value : internalOpen.value[0])
  },
  closeAll: () => {
    internalOpen.value = []
    emit('update:modelValue', props.multiple ? [] : null)
  }
})
</script>
