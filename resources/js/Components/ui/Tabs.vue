<template>
  <div :class="containerClass">
    <!-- Tab List -->
    <div
      role="tablist"
      class="flex"
      :class="tabListClasses"
    >
      <button
        v-for="(tab, index) in tabs"
        :key="tab.id || index"
        type="button"
        role="tab"
        :id="`tab-${tab.id || index}`"
        :aria-selected="isActive(index)"
        :aria-controls="`panel-${tab.id || index}`"
        :disabled="tab.disabled"
        class="relative flex items-center gap-2 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
        :class="getTabClasses(index, tab)"
        @click="selectTab(index)"
      >
        <!-- Leading Icon -->
        <component
          v-if="tab.icon"
          :is="tab.icon"
          class="w-5 h-5 flex-shrink-0"
        />

        <!-- Label -->
        <span>{{ tab.label }}</span>

        <!-- Badge -->
        <span
          v-if="tab.badge !== undefined"
          class="ml-1 px-1.5 py-0.5 text-xs font-medium rounded-full"
          :class="getBadgeClasses(index)"
        >
          {{ tab.badge }}
        </span>

        <!-- Close Button (if closable) -->
        <button
          v-if="tab.closable"
          type="button"
          class="ml-1 p-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          @click.stop="closeTab(index)"
          :aria-label="`Close ${tab.label}`"
        >
          <XMarkIcon class="w-3.5 h-3.5" />
        </button>

        <!-- Active Indicator (underline variant) -->
        <span
          v-if="variant === 'underline' && isActive(index)"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"
        />
      </button>

      <!-- Add Tab Button -->
      <button
        v-if="addable"
        type="button"
        class="flex items-center justify-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        @click="$emit('add')"
        title="Add new tab"
      >
        <PlusIcon class="w-5 h-5" />
      </button>
    </div>

    <!-- Tab Panels -->
    <div class="mt-4">
      <Transition
        :name="animated ? 'tab-fade' : ''"
        mode="out-in"
      >
        <div
          v-for="(tab, index) in tabs"
          v-show="isActive(index)"
          :key="tab.id || index"
          :id="`panel-${tab.id || index}`"
          role="tabpanel"
          :aria-labelledby="`tab-${tab.id || index}`"
          :tabindex="isActive(index) ? 0 : -1"
        >
          <!-- Custom slot for each tab -->
          <slot :name="`tab-${index}`" :tab="tab" :is-active="isActive(index)">
            <slot :name="`tab-${tab.id}`" :tab="tab" :is-active="isActive(index)">
              <!-- Default content from tab object -->
              <div v-if="tab.content">
                {{ tab.content }}
              </div>
            </slot>
          </slot>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { XMarkIcon, PlusIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  // Tab items array
  tabs: {
    type: Array,
    required: true,
    default: () => []
    // Each tab: { id?, label, icon?, badge?, content?, disabled?, closable? }
  },
  // Active tab index (v-model)
  modelValue: {
    type: Number,
    default: 0
  },
  // Visual variant
  variant: {
    type: String,
    default: 'underline',
    validator: (v) => ['underline', 'pills', 'bordered', 'lifted'].includes(v)
  },
  // Size variant
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  // Full width tabs
  fullWidth: {
    type: Boolean,
    default: false
  },
  // Centered tabs
  centered: {
    type: Boolean,
    default: false
  },
  // Enable animations
  animated: {
    type: Boolean,
    default: true
  },
  // Allow adding new tabs
  addable: {
    type: Boolean,
    default: false
  },
  // Vertical orientation
  vertical: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'close', 'add'])

// Check if tab is active
function isActive(index) {
  return props.modelValue === index
}

// Select tab
function selectTab(index) {
  if (props.tabs[index]?.disabled) return
  
  emit('update:modelValue', index)
  emit('change', { index, tab: props.tabs[index] })
}

// Close tab
function closeTab(index) {
  emit('close', { index, tab: props.tabs[index] })
}

// Watch for removed tabs (adjust active index)
watch(() => props.tabs.length, (newLen, oldLen) => {
  if (newLen < oldLen && props.modelValue >= newLen) {
    emit('update:modelValue', Math.max(0, newLen - 1))
  }
})

// Container class
const containerClass = computed(() => {
  if (props.vertical) {
    return 'flex gap-4'
  }
  return ''
})

// Tab list classes
const tabListClasses = computed(() => {
  const classes = []
  
  if (props.vertical) {
    classes.push('flex-col')
  }
  
  if (props.fullWidth && !props.vertical) {
    classes.push('w-full')
  }
  
  if (props.centered && !props.vertical) {
    classes.push('justify-center')
  }
  
  // Variant-specific list styles
  switch (props.variant) {
    case 'underline':
      classes.push('border-b border-gray-200 dark:border-gray-700 gap-4')
      break
    case 'pills':
      classes.push('gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg')
      break
    case 'bordered':
      classes.push('gap-0 border-b border-gray-200 dark:border-gray-700')
      break
    case 'lifted':
      classes.push('gap-1')
      break
  }
  
  return classes.join(' ')
})

// Get tab button classes
function getTabClasses(index, tab) {
  const classes = []
  const active = isActive(index)
  
  // Size
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-3 text-base'
  }
  classes.push(sizeClasses[props.size])
  
  // Full width
  if (props.fullWidth) {
    classes.push('flex-1 justify-center')
  }
  
  // Disabled
  if (tab.disabled) {
    classes.push('opacity-50 cursor-not-allowed')
    return classes.join(' ')
  }
  
  // Variant-specific tab styles
  switch (props.variant) {
    case 'underline':
      if (active) {
        classes.push('text-blue-600 dark:text-blue-400 font-medium')
      } else {
        classes.push('text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200')
      }
      break
      
    case 'pills':
      if (active) {
        classes.push('bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm rounded-md font-medium')
      } else {
        classes.push('text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-md')
      }
      break
      
    case 'bordered':
      if (active) {
        classes.push('bg-white dark:bg-gray-900 border border-b-white dark:border-b-gray-900 border-gray-200 dark:border-gray-700 -mb-px rounded-t-lg text-blue-600 dark:text-blue-400 font-medium')
      } else {
        classes.push('text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-t-lg')
      }
      break
      
    case 'lifted':
      if (active) {
        classes.push('bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 border-b-0 rounded-t-lg text-blue-600 dark:text-blue-400 font-medium relative z-10')
      } else {
        classes.push('text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-t-lg')
      }
      break
  }
  
  return classes.join(' ')
}

// Badge classes
function getBadgeClasses(index) {
  if (isActive(index)) {
    return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
  }
  return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
}
</script>

<style scoped>
.tab-fade-enter-active,
.tab-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.tab-fade-enter-from {
  opacity: 0;
  transform: translateY(4px);
}

.tab-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
