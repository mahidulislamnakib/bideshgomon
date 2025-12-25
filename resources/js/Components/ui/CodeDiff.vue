<template>
  <div :class="['code-diff', containerClasses]">
    <!-- Header -->
    <div v-if="showHeader" :class="['flex items-center justify-between p-3 border-b', themeClasses.header]">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
          <MinusCircleIcon class="w-4 h-4 text-red-500" />
          <span :class="['text-sm', themeClasses.headerText]">{{ oldTitle }}</span>
        </div>
        <div class="flex items-center gap-2">
          <PlusCircleIcon class="w-4 h-4 text-green-500" />
          <span :class="['text-sm', themeClasses.headerText]">{{ newTitle }}</span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <!-- View Mode Toggle -->
        <div :class="['flex rounded-lg overflow-hidden border', themeClasses.toggleBorder]">
          <button
            @click="viewMode = 'split'"
            :class="[
              'px-3 py-1 text-sm transition-colors',
              viewMode === 'split' ? themeClasses.toggleActive : themeClasses.toggleInactive
            ]"
          >
            Split
          </button>
          <button
            @click="viewMode = 'unified'"
            :class="[
              'px-3 py-1 text-sm transition-colors',
              viewMode === 'unified' ? themeClasses.toggleActive : themeClasses.toggleInactive
            ]"
          >
            Unified
          </button>
        </div>
        <!-- Stats -->
        <div class="flex items-center gap-2 text-sm">
          <span class="text-green-500">+{{ stats.additions }}</span>
          <span class="text-red-500">-{{ stats.deletions }}</span>
        </div>
      </div>
    </div>

    <!-- Diff Content -->
    <div :class="['overflow-auto', themeClasses.content]" :style="{ maxHeight }">
      <!-- Split View -->
      <div v-if="viewMode === 'split'" class="flex">
        <!-- Old Code -->
        <div class="flex-1 overflow-x-auto">
          <table class="w-full">
            <tbody>
              <tr
                v-for="(line, i) in splitDiff.left"
                :key="`left-${i}`"
                :class="getLineClass(line.type, 'left')"
              >
                <td :class="['w-12 px-2 text-right select-none', themeClasses.lineNumber]">
                  {{ line.oldLine || '' }}
                </td>
                <td class="w-6 text-center select-none">
                  <span v-if="line.type === 'removed'" class="text-red-500">-</span>
                </td>
                <td class="px-2 whitespace-pre font-mono text-sm">
                  <span v-html="highlightSyntax(line.content)" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Divider -->
        <div :class="['w-px', themeClasses.divider]" />

        <!-- New Code -->
        <div class="flex-1 overflow-x-auto">
          <table class="w-full">
            <tbody>
              <tr
                v-for="(line, i) in splitDiff.right"
                :key="`right-${i}`"
                :class="getLineClass(line.type, 'right')"
              >
                <td :class="['w-12 px-2 text-right select-none', themeClasses.lineNumber]">
                  {{ line.newLine || '' }}
                </td>
                <td class="w-6 text-center select-none">
                  <span v-if="line.type === 'added'" class="text-green-500">+</span>
                </td>
                <td class="px-2 whitespace-pre font-mono text-sm">
                  <span v-html="highlightSyntax(line.content)" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unified View -->
      <div v-else>
        <table class="w-full">
          <tbody>
            <tr
              v-for="(line, i) in unifiedDiff"
              :key="`unified-${i}`"
              :class="getLineClass(line.type, 'unified')"
            >
              <td :class="['w-12 px-2 text-right select-none', themeClasses.lineNumber]">
                {{ line.oldLine || '' }}
              </td>
              <td :class="['w-12 px-2 text-right select-none', themeClasses.lineNumber]">
                {{ line.newLine || '' }}
              </td>
              <td class="w-6 text-center select-none">
                <span v-if="line.type === 'added'" class="text-green-500">+</span>
                <span v-else-if="line.type === 'removed'" class="text-red-500">-</span>
              </td>
              <td class="px-2 whitespace-pre font-mono text-sm">
                <span v-html="highlightSyntax(line.content)" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showFooter" :class="['flex items-center justify-between p-2 border-t text-xs', themeClasses.footer]">
      <div class="flex items-center gap-4">
        <span>{{ stats.total }} lines</span>
        <span class="text-green-500">{{ stats.additions }} additions</span>
        <span class="text-red-500">{{ stats.deletions }} deletions</span>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="expandAll"
          :class="['px-2 py-1 rounded transition-colors', themeClasses.footerBtn]"
        >
          Expand all
        </button>
        <button
          @click="collapseUnchanged"
          :class="['px-2 py-1 rounded transition-colors', themeClasses.footerBtn]"
        >
          Collapse unchanged
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  MinusCircleIcon,
  PlusCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  oldCode: {
    type: String,
    required: true
  },
  newCode: {
    type: String,
    required: true
  },
  oldTitle: {
    type: String,
    default: 'Original'
  },
  newTitle: {
    type: String,
    default: 'Modified'
  },
  language: {
    type: String,
    default: 'javascript'
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: true
  },
  maxHeight: {
    type: String,
    default: '500px'
  },
  contextLines: {
    type: Number,
    default: 3
  }
})

const viewMode = ref('split')
const collapsed = ref(new Set())

// Compute diff using LCS algorithm
const computeDiff = () => {
  const oldLines = props.oldCode.split('\n')
  const newLines = props.newCode.split('\n')
  
  // Build LCS matrix
  const m = oldLines.length
  const n = newLines.length
  const dp = Array(m + 1).fill(null).map(() => Array(n + 1).fill(0))
  
  for (let i = 1; i <= m; i++) {
    for (let j = 1; j <= n; j++) {
      if (oldLines[i - 1] === newLines[j - 1]) {
        dp[i][j] = dp[i - 1][j - 1] + 1
      } else {
        dp[i][j] = Math.max(dp[i - 1][j], dp[i][j - 1])
      }
    }
  }
  
  // Backtrack to find diff
  const diff = []
  let i = m, j = n
  
  while (i > 0 || j > 0) {
    if (i > 0 && j > 0 && oldLines[i - 1] === newLines[j - 1]) {
      diff.unshift({
        type: 'unchanged',
        content: oldLines[i - 1],
        oldLine: i,
        newLine: j
      })
      i--
      j--
    } else if (j > 0 && (i === 0 || dp[i][j - 1] >= dp[i - 1][j])) {
      diff.unshift({
        type: 'added',
        content: newLines[j - 1],
        oldLine: null,
        newLine: j
      })
      j--
    } else if (i > 0) {
      diff.unshift({
        type: 'removed',
        content: oldLines[i - 1],
        oldLine: i,
        newLine: null
      })
      i--
    }
  }
  
  return diff
}

const unifiedDiff = computed(() => computeDiff())

const splitDiff = computed(() => {
  const diff = computeDiff()
  const left = []
  const right = []
  
  let leftIdx = 0
  let rightIdx = 0
  
  for (const line of diff) {
    if (line.type === 'unchanged') {
      left.push({ ...line })
      right.push({ ...line })
    } else if (line.type === 'removed') {
      left.push({ ...line })
      right.push({ type: 'empty', content: '', oldLine: null, newLine: null })
    } else if (line.type === 'added') {
      left.push({ type: 'empty', content: '', oldLine: null, newLine: null })
      right.push({ ...line })
    }
  }
  
  // Compact consecutive empty lines
  const compactedLeft = []
  const compactedRight = []
  
  for (let i = 0; i < left.length; i++) {
    if (left[i].type === 'empty' && right[i].type === 'empty') continue
    compactedLeft.push(left[i])
    compactedRight.push(right[i])
  }
  
  return { left: compactedLeft, right: compactedRight }
})

const stats = computed(() => {
  const diff = unifiedDiff.value
  return {
    total: diff.length,
    additions: diff.filter(l => l.type === 'added').length,
    deletions: diff.filter(l => l.type === 'removed').length
  }
})

const getLineClass = (type, side) => {
  const base = 'transition-colors'
  
  if (props.theme === 'dark') {
    switch (type) {
      case 'added':
        return `${base} bg-green-900/30`
      case 'removed':
        return `${base} bg-red-900/30`
      case 'empty':
        return `${base} bg-gray-800/50`
      default:
        return `${base} hover:bg-gray-800/50`
    }
  }
  
  switch (type) {
    case 'added':
      return `${base} bg-green-50`
    case 'removed':
      return `${base} bg-red-50`
    case 'empty':
      return `${base} bg-gray-50`
    default:
      return `${base} hover:bg-gray-50`
  }
}

const highlightSyntax = (code) => {
  if (!code) return ''
  
  // Basic syntax highlighting
  let highlighted = code
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
  
  // Keywords
  const keywords = /\b(const|let|var|function|return|if|else|for|while|class|import|export|from|default|async|await|try|catch|throw|new|this|super|extends|static)\b/g
  highlighted = highlighted.replace(keywords, '<span class="text-purple-500">$1</span>')
  
  // Strings
  highlighted = highlighted.replace(/(["'`])(?:(?!\1)[^\\]|\\.)*\1/g, '<span class="text-green-600">$&</span>')
  
  // Numbers
  highlighted = highlighted.replace(/\b(\d+\.?\d*)\b/g, '<span class="text-orange-500">$1</span>')
  
  // Comments
  highlighted = highlighted.replace(/(\/\/.*$)/gm, '<span class="text-gray-500 italic">$1</span>')
  
  return highlighted
}

const expandAll = () => {
  collapsed.value.clear()
}

const collapseUnchanged = () => {
  // Mark unchanged sections for collapse
  const diff = unifiedDiff.value
  let start = -1
  
  for (let i = 0; i < diff.length; i++) {
    if (diff[i].type === 'unchanged') {
      if (start === -1) start = i
    } else {
      if (start !== -1 && i - start > props.contextLines * 2) {
        for (let j = start + props.contextLines; j < i - props.contextLines; j++) {
          collapsed.value.add(j)
        }
      }
      start = -1
    }
  }
}

const containerClasses = computed(() => {
  return [
    'rounded-lg border overflow-hidden',
    props.theme === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
  ]
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      header: 'bg-gray-800 border-gray-700',
      headerText: 'text-gray-300',
      toggleBorder: 'border-gray-600',
      toggleActive: 'bg-blue-600 text-white',
      toggleInactive: 'bg-gray-700 text-gray-300 hover:bg-gray-600',
      content: 'bg-gray-900',
      lineNumber: 'text-gray-600 bg-gray-800/50',
      divider: 'bg-gray-700',
      footer: 'bg-gray-800 border-gray-700 text-gray-400',
      footerBtn: 'hover:bg-gray-700 text-gray-400'
    }
  }
  return {
    header: 'bg-gray-50 border-gray-200',
    headerText: 'text-gray-700',
    toggleBorder: 'border-gray-300',
    toggleActive: 'bg-blue-500 text-white',
    toggleInactive: 'bg-white text-gray-600 hover:bg-gray-50',
    content: 'bg-white',
    lineNumber: 'text-gray-400 bg-gray-50',
    divider: 'bg-gray-200',
    footer: 'bg-gray-50 border-gray-200 text-gray-600',
    footerBtn: 'hover:bg-gray-200 text-gray-600'
  }
})

defineExpose({
  getStats: () => stats.value,
  setViewMode: (mode) => { viewMode.value = mode }
})
</script>
