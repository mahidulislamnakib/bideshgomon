<template>
  <div class="inline-flex rounded-md shadow-sm">
    <!-- Primary action button -->
    <button
      type="button"
      :disabled="disabled || loading"
      :class="[
        'relative inline-flex items-center rounded-l-md px-4 py-2 text-sm font-semibold ring-1 ring-inset focus:z-10 transition-colors',
        colorClasses.primary,
        sizeClasses,
        { 'opacity-50 cursor-not-allowed': disabled || loading }
      ]"
      @click="$emit('click')"
    >
      <!-- Loading spinner -->
      <svg
        v-if="loading"
        class="animate-spin -ml-1 mr-2 h-4 w-4"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
      
      <!-- Icon slot -->
      <slot name="icon" />
      
      <!-- Label -->
      <span>{{ label }}</span>
    </button>

    <!-- Dropdown toggle button -->
    <Menu as="div" class="relative -ml-px block">
      <MenuButton
        :disabled="disabled || loading"
        :class="[
          'relative inline-flex items-center rounded-r-md px-2 py-2 ring-1 ring-inset focus:z-10 transition-colors',
          colorClasses.dropdown,
          sizeClasses,
          { 'opacity-50 cursor-not-allowed': disabled || loading }
        ]"
      >
        <span class="sr-only">Open options</span>
        <ChevronDownIcon class="h-5 w-5" aria-hidden="true" />
      </MenuButton>

      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <MenuItems
          :class="[
            'absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none',
            { '-top-2 transform -translate-y-full': dropUp }
          ]"
        >
          <div class="py-1">
            <MenuItem
              v-for="(action, index) in actions"
              :key="index"
              v-slot="{ active }"
              :disabled="action.disabled"
            >
              <button
                type="button"
                :class="[
                  'w-full text-left px-4 py-2 text-sm flex items-center gap-2',
                  active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                  action.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                  action.danger ? 'text-red-600' : ''
                ]"
                :disabled="action.disabled"
                @click="handleActionClick(action)"
              >
                <component
                  v-if="action.icon"
                  :is="action.icon"
                  class="h-4 w-4"
                  :class="action.danger ? 'text-red-500' : 'text-gray-400'"
                />
                <span>{{ action.label }}</span>
                <span v-if="action.shortcut" class="ml-auto text-xs text-gray-400">
                  {{ action.shortcut }}
                </span>
              </button>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  actions: {
    type: Array,
    default: () => []
    // Each action: { label, icon?, shortcut?, disabled?, danger?, value? }
  },
  color: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'success', 'danger', 'warning'].includes(v)
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  dropUp: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click', 'action'])

const colorClasses = computed(() => {
  const colors = {
    primary: {
      primary: 'bg-primary-600 text-white ring-primary-600 hover:bg-primary-700',
      dropdown: 'bg-primary-600 text-white ring-primary-600 hover:bg-primary-700'
    },
    secondary: {
      primary: 'bg-white text-gray-700 ring-gray-300 hover:bg-gray-50',
      dropdown: 'bg-white text-gray-500 ring-gray-300 hover:bg-gray-50'
    },
    success: {
      primary: 'bg-green-600 text-white ring-green-600 hover:bg-green-700',
      dropdown: 'bg-green-600 text-white ring-green-600 hover:bg-green-700'
    },
    danger: {
      primary: 'bg-red-600 text-white ring-red-600 hover:bg-red-700',
      dropdown: 'bg-red-600 text-white ring-red-600 hover:bg-red-700'
    },
    warning: {
      primary: 'bg-yellow-500 text-white ring-yellow-500 hover:bg-yellow-600',
      dropdown: 'bg-yellow-500 text-white ring-yellow-500 hover:bg-yellow-600'
    }
  }
  return colors[props.color]
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'py-1.5 text-xs',
    md: 'py-2 text-sm',
    lg: 'py-2.5 text-base'
  }
  return sizes[props.size]
})

function handleActionClick(action) {
  if (!action.disabled) {
    emit('action', action.value || action.label)
  }
}
</script>
