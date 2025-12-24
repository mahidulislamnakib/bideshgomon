<!-- Improved Navigation Item Component -->
<template>
  <component
    :is="(href && href !== '#' && !disabled) ? Link : 'div'"
    v-if="!children"
    :href="(href && href !== '#' && !disabled) ? href : undefined"
    :class="navItemClasses"
    :aria-current="active ? 'page' : undefined"
    :aria-disabled="disabled || !href || href === '#'"
    :title="collapsed ? label : undefined"
    @click="handleClick"
  >
    <!-- Icon -->
    <component
      :is="icon"
      :class="[
        'flex-shrink-0 transition-colors',
        iconSize,
        active ? activeIconColor : inactiveIconColor,
      ]"
      aria-hidden="true"
    />

    <!-- Label (hidden when collapsed) -->
    <span
      v-if="!collapsed"
      :class="['flex-1 truncate', labelClass]"
    >
      {{ label }}
    </span>

    <!-- Badge -->
    <span
      v-if="badge && !collapsed"
      :class="[
        'px-2 py-0.5 text-xs font-bold rounded-full',
        badgeClasses,
      ]"
      :aria-label="`${badge} ${badgeLabel || 'items'}`"
    >
      {{ badge }}
    </span>

    <!-- Keyboard Shortcut -->
    <kbd
      v-if="shortcut && !collapsed"
      class="hidden lg:inline-flex items-center px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded"
    >
      {{ shortcut }}
    </kbd>
  </component>

  <!-- Section (with children) -->
  <div v-else>
    <button
      type="button"
      :class="sectionButtonClasses"
      @click="toggleSection"
      :aria-expanded="isOpen"
      :aria-controls="`section-${uniqueId}`"
    >
      <!-- Icon -->
      <component
        :is="icon"
        :class="[
          'flex-shrink-0 transition-colors',
          iconSize,
          'text-gray-500 dark:text-gray-400',
        ]"
        aria-hidden="true"
      />

      <!-- Label -->
      <span
        v-if="!collapsed"
        class="flex-1 truncate text-left font-semibold"
      >
        {{ label }}
      </span>

      <!-- Chevron -->
      <ChevronDownIcon
        v-if="!collapsed"
        :class="[
          'h-4 w-4 transition-transform duration-200',
          isOpen ? 'rotate-0' : '-rotate-90',
        ]"
        aria-hidden="true"
      />
    </button>

    <!-- Children (collapsible) -->
    <Transition
      name="expand"
      @enter="onEnter"
      @after-enter="onAfterEnter"
      @leave="onLeave"
    >
      <div
        v-show="isOpen && !collapsed"
        :id="`section-${uniqueId}`"
        class="overflow-hidden"
      >
        <div class="space-y-1 mt-1">
          <NavItem
            v-for="child in children"
            :key="child.href"
            v-bind="child"
            :collapsed="collapsed"
            class="pl-11"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  // Navigation item props
  href: String,
  label: { type: String, required: true },
  icon: { type: Object, required: true },
  active: Boolean,
  disabled: Boolean,
  badge: [String, Number],
  badgeLabel: String,
  shortcut: String,

  // Section props
  children: Array,
  defaultOpen: { type: Boolean, default: false },

  // Layout
  collapsed: Boolean,
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },

  // Custom classes
  className: String,
})

const uniqueId = Math.random().toString(36).substring(7)
const isOpen = ref(props.defaultOpen)

const handleClick = (e) => {
  if (props.disabled || !props.href || props.href === '#') {
    e.preventDefault()
    e.stopPropagation()
    // Could emit an event here for "coming soon" modal
  }
}

const toggleSection = () => {
  isOpen.value = !isOpen.value
}

// Size configurations
const sizeConfig = {
  sm: {
    padding: 'px-2 py-1.5',
    iconSize: 'h-4 w-4',
    fontSize: 'text-xs',
  },
  md: {
    padding: 'px-3 py-2.5',
    iconSize: 'h-5 w-5',
    fontSize: 'text-sm',
  },
  lg: {
    padding: 'px-4 py-3',
    iconSize: 'h-6 w-6',
    fontSize: 'text-base',
  },
}

const iconSize = computed(() => sizeConfig[props.size].iconSize)
const fontSize = computed(() => sizeConfig[props.size].fontSize)

// Active/inactive colors
const activeIconColor = 'text-white'
const inactiveIconColor =
  'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300'

const labelClass = computed(() => fontSize.value)

// Navigation item classes
const navItemClasses = computed(() => [
  // Base
  'group flex items-center gap-3 rounded-lg',
  'transition-all duration-200',
  'font-medium',
  fontSize.value,

  // Padding
  props.collapsed ? 'justify-center p-2.5' : sizeConfig[props.size].padding,

  // States
  props.active
    ? 'bg-growth-600 text-white shadow-sm'
    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white',

  props.disabled && 'opacity-50 cursor-not-allowed pointer-events-none',

  // Custom
  props.className,
])

// Section button classes
const sectionButtonClasses = computed(() => [
  'group w-full flex items-center gap-3 rounded-lg',
  'transition-all duration-200',
  'text-xs font-bold uppercase tracking-wider',
  'text-gray-600 dark:text-gray-400',
  'hover:bg-gray-50 dark:hover:bg-gray-700/50',
  'hover:text-gray-900 dark:hover:text-gray-200',
  props.collapsed ? 'justify-center p-2' : 'px-3 py-2',
])

// Badge styles
const badgeClasses = computed(() => {
  if (props.active) {
    return 'bg-blue-500 text-white'
  }
  return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
})

// Expand/collapse transitions
const onEnter = (el) => {
  el.style.height = '0'
  el.offsetHeight // Force reflow
  el.style.height = el.scrollHeight + 'px'
}

const onAfterEnter = (el) => {
  el.style.height = 'auto'
}

const onLeave = (el) => {
  el.style.height = el.scrollHeight + 'px'
  el.offsetHeight // Force reflow
  el.style.height = '0'
}
</script>

