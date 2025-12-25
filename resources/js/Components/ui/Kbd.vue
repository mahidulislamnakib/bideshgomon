<template>
  <kbd
    :class="[
      'inline-flex items-center justify-center font-sans font-medium',
      'rounded border shadow-sm',
      sizeClasses,
      variantClasses
    ]"
  >
    <!-- Modifier keys with symbols -->
    <template v-if="parsedKeys.length > 0">
      <template v-for="(key, index) in parsedKeys" :key="index">
        <span v-if="index > 0" class="mx-0.5 opacity-50">{{ separator }}</span>
        <span>{{ key }}</span>
      </template>
    </template>
    
    <!-- Slot for custom content -->
    <slot v-else />
  </kbd>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  keys: {
    type: [String, Array],
    default: ''
  },
  separator: {
    type: String,
    default: '+'
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'outline', 'solid', 'ghost'].includes(v)
  }
})

// Key symbol mappings
const keySymbols = {
  // Modifiers
  'cmd': '⌘',
  'command': '⌘',
  'meta': '⌘',
  'ctrl': '⌃',
  'control': '⌃',
  'alt': '⌥',
  'option': '⌥',
  'shift': '⇧',
  
  // Navigation
  'enter': '↵',
  'return': '↵',
  'tab': '⇥',
  'escape': 'Esc',
  'esc': 'Esc',
  'backspace': '⌫',
  'delete': '⌦',
  'space': '␣',
  
  // Arrows
  'up': '↑',
  'down': '↓',
  'left': '←',
  'right': '→',
  'arrowup': '↑',
  'arrowdown': '↓',
  'arrowleft': '←',
  'arrowright': '→',
  
  // Special
  'capslock': '⇪',
  'home': '↖',
  'end': '↘',
  'pageup': '⇞',
  'pagedown': '⇟'
}

const parsedKeys = computed(() => {
  if (!props.keys) return []
  
  const keysArray = Array.isArray(props.keys)
    ? props.keys
    : props.keys.split(/[+\s]+/).filter(Boolean)
  
  return keysArray.map(key => {
    const lowerKey = key.toLowerCase()
    return keySymbols[lowerKey] || key.toUpperCase()
  })
})

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'min-w-[18px] h-[18px] px-1 text-[10px]',
    sm: 'min-w-[22px] h-[22px] px-1.5 text-xs',
    md: 'min-w-[26px] h-[26px] px-2 text-sm',
    lg: 'min-w-[32px] h-[32px] px-2.5 text-base'
  }
  return sizes[props.size]
})

const variantClasses = computed(() => {
  const variants = {
    default: 'bg-gray-100 text-gray-700 border-gray-300',
    outline: 'bg-transparent text-gray-600 border-gray-400',
    solid: 'bg-gray-800 text-white border-gray-700',
    ghost: 'bg-transparent text-gray-500 border-transparent shadow-none'
  }
  return variants[props.variant]
})
</script>
