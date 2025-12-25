<template>
  <span :class="wrapperClass">
    <template v-if="highlightedParts.length > 0">
      <template v-for="(part, index) in highlightedParts" :key="index">
        <mark
          v-if="part.highlighted"
          :class="[
            'rounded-sm',
            colorClasses,
            { 'font-semibold': bold }
          ]"
          :style="customStyle"
        >{{ part.text }}</mark>
        <template v-else>{{ part.text }}</template>
      </template>
    </template>
    <slot v-else />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  query: {
    type: [String, Array, RegExp],
    default: ''
  },
  color: {
    type: String,
    default: 'yellow',
    validator: (v) => ['yellow', 'green', 'blue', 'red', 'purple', 'orange', 'cyan', 'pink', 'custom'].includes(v)
  },
  customColor: {
    type: String,
    default: ''
  },
  caseSensitive: {
    type: Boolean,
    default: false
  },
  wholeWord: {
    type: Boolean,
    default: false
  },
  bold: {
    type: Boolean,
    default: false
  },
  wrapperClass: {
    type: String,
    default: ''
  }
})

const colorClasses = computed(() => {
  if (props.color === 'custom') return ''
  
  const colors = {
    yellow: 'bg-yellow-200 text-yellow-900',
    green: 'bg-green-200 text-green-900',
    blue: 'bg-blue-200 text-blue-900',
    red: 'bg-red-200 text-red-900',
    purple: 'bg-purple-200 text-purple-900',
    orange: 'bg-orange-200 text-orange-900',
    cyan: 'bg-cyan-200 text-cyan-900',
    pink: 'bg-pink-200 text-pink-900'
  }
  return colors[props.color]
})

const customStyle = computed(() => {
  if (props.color === 'custom' && props.customColor) {
    return { backgroundColor: props.customColor }
  }
  return {}
})

// Build regex pattern from query
function buildPattern(query) {
  if (query instanceof RegExp) {
    return query
  }
  
  const queries = Array.isArray(query) ? query : [query]
  const escapedQueries = queries
    .filter(q => q && q.length > 0)
    .map(q => q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
  
  if (escapedQueries.length === 0) return null
  
  let pattern = escapedQueries.join('|')
  
  if (props.wholeWord) {
    pattern = `\\b(${pattern})\\b`
  } else {
    pattern = `(${pattern})`
  }
  
  const flags = props.caseSensitive ? 'g' : 'gi'
  return new RegExp(pattern, flags)
}

const highlightedParts = computed(() => {
  if (!props.text || !props.query) return []
  
  const pattern = buildPattern(props.query)
  if (!pattern) return [{ text: props.text, highlighted: false }]
  
  const parts = []
  let lastIndex = 0
  let match
  
  // Reset lastIndex for global regex
  pattern.lastIndex = 0
  
  while ((match = pattern.exec(props.text)) !== null) {
    // Add non-highlighted part before match
    if (match.index > lastIndex) {
      parts.push({
        text: props.text.slice(lastIndex, match.index),
        highlighted: false
      })
    }
    
    // Add highlighted match
    parts.push({
      text: match[0],
      highlighted: true
    })
    
    lastIndex = match.index + match[0].length
    
    // Prevent infinite loop for zero-length matches
    if (match[0].length === 0) {
      pattern.lastIndex++
    }
  }
  
  // Add remaining non-highlighted text
  if (lastIndex < props.text.length) {
    parts.push({
      text: props.text.slice(lastIndex),
      highlighted: false
    })
  }
  
  return parts
})
</script>
