<template>
  <Menu as="div" class="relative inline-block text-left" v-slot="{ open }">
    <!-- Trigger -->
    <MenuButton
      class="inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
      :class="[triggerClasses, { 'pointer-events-none opacity-50': disabled }]"
      :disabled="disabled"
    >
      <slot name="trigger" :open="open">
        <span>{{ label }}</span>
        <ChevronDownIcon
          class="ml-1.5 h-4 w-4 transition-transform duration-200"
          :class="{ 'rotate-180': open }"
        />
      </slot>
    </MenuButton>

    <!-- Dropdown panel -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <MenuItems
        class="absolute z-50 mt-2 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        :class="[widthClasses, alignmentClasses]"
      >
        <!-- Header slot -->
        <div v-if="$slots.header" class="px-4 py-3 border-b border-gray-100">
          <slot name="header" />
        </div>

        <!-- Items -->
        <div class="py-1" :class="maxHeightClasses">
          <template v-if="items && items.length">
            <template v-for="(item, index) in items" :key="item.id || index">
              <!-- Divider -->
              <div v-if="item.divider" class="my-1 border-t border-gray-100" />
              
              <!-- Group header -->
              <div
                v-else-if="item.header"
                class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                {{ item.header }}
              </div>
              
              <!-- Menu item -->
              <MenuItem v-else v-slot="{ active, close }" :disabled="item.disabled">
                <component
                  :is="item.href ? 'a' : item.to ? 'Link' : 'button'"
                  :href="item.href"
                  :to="item.to"
                  :type="!item.href && !item.to ? 'button' : undefined"
                  class="group flex w-full items-center px-4 py-2 text-sm transition-colors"
                  :class="[
                    active ? activeItemClasses : 'text-gray-700',
                    item.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                    item.danger ? 'text-red-600 hover:bg-red-50' : ''
                  ]"
                  @click="handleItemClick(item, close)"
                >
                  <!-- Icon -->
                  <component
                    v-if="item.icon"
                    :is="item.icon"
                    class="mr-3 h-5 w-5 flex-shrink-0"
                    :class="item.danger ? 'text-red-500' : active ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'"
                  />
                  
                  <!-- Label & Description -->
                  <div class="flex-1 text-left">
                    <div :class="{ 'font-medium': item.description }">{{ item.label }}</div>
                    <div v-if="item.description" class="text-xs text-gray-500">
                      {{ item.description }}
                    </div>
                  </div>
                  
                  <!-- Shortcut -->
                  <kbd
                    v-if="item.shortcut"
                    class="ml-3 text-xs text-gray-400 font-mono"
                  >
                    {{ item.shortcut }}
                  </kbd>
                  
                  <!-- Badge -->
                  <span
                    v-if="item.badge"
                    class="ml-3 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="item.badgeColor || 'bg-gray-100 text-gray-600'"
                  >
                    {{ item.badge }}
                  </span>
                  
                  <!-- Checkmark for selected items -->
                  <CheckIcon
                    v-if="item.selected"
                    class="ml-3 h-4 w-4 text-primary-600"
                  />
                </component>
              </MenuItem>
            </template>
          </template>
          
          <!-- Slot for custom content -->
          <slot />
        </div>

        <!-- Footer slot -->
        <div v-if="$slots.footer" class="px-4 py-3 border-t border-gray-100">
          <slot name="footer" />
        </div>
      </MenuItems>
    </Transition>
  </Menu>
</template>

<script setup>
import { computed } from 'vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon, CheckIcon } from '@heroicons/vue/20/solid'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  label: {
    type: String,
    default: 'Options'
  },
  items: {
    type: Array,
    default: () => []
    // [{ id, label, icon, href, to, shortcut, badge, badgeColor, description, disabled, danger, selected, divider, header }]
  },
  align: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'right', 'center'].includes(value)
  },
  width: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  maxHeight: {
    type: String,
    default: 'none',
    validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'ghost', 'minimal'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['select'])

// Handle item click
function handleItemClick(item, close) {
  if (item.disabled) return
  emit('select', item)
  if (item.onClick) {
    item.onClick(item)
  }
  if (!item.keepOpen) {
    close()
  }
}

// Trigger button styling
const triggerClasses = computed(() => {
  const variants = {
    default: 'px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-primary-500',
    outline: 'px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-primary-500',
    ghost: 'px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg focus:ring-primary-500',
    minimal: 'text-gray-600 hover:text-gray-900 focus:ring-primary-500'
  }
  return variants[props.variant]
})

// Width classes
const widthClasses = computed(() => {
  const widths = {
    xs: 'w-32',
    sm: 'w-40',
    md: 'w-56',
    lg: 'w-72',
    xl: 'w-96',
    full: 'w-full'
  }
  return widths[props.width]
})

// Alignment classes
const alignmentClasses = computed(() => {
  const alignments = {
    left: 'left-0',
    right: 'right-0',
    center: 'left-1/2 -translate-x-1/2'
  }
  return alignments[props.align]
})

// Max height classes
const maxHeightClasses = computed(() => {
  const heights = {
    none: '',
    sm: 'max-h-48 overflow-y-auto',
    md: 'max-h-64 overflow-y-auto',
    lg: 'max-h-96 overflow-y-auto'
  }
  return heights[props.maxHeight]
})

// Active item styling
const activeItemClasses = 'bg-gray-100 text-gray-900'
</script>
