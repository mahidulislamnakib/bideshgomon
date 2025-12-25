<template>
  <div :class="['diff-viewer font-mono text-sm', { 'border rounded-lg overflow-hidden': bordered }]">
    <!-- Header -->
    <div v-if="showHeader" class="flex border-b bg-gray-50">
      <div class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border-r">
        <span class="text-red-600">−</span> {{ oldTitle }}
      </div>
      <div class="flex-1 px-4 py-2 text-sm font-medium text-gray-700">
        <span class="text-green-600">+</span> {{ newTitle }}
      </div>
    </div>

    <!-- Side by side view -->
    <div v-if="viewMode === 'split'" class="flex">
      <!-- Old content -->
      <div class="flex-1 border-r overflow-x-auto">
        <table class="w-full">
          <tbody>
            <tr
              v-for="(line, index) in diffLines"
              :key="'old-' + index"
              :class="getLineClass(line, 'old')"
            >
              <td v-if="showLineNumbers" class="w-12 px-2 py-0.5 text-right text-gray-400 select-none border-r bg-gray-50">
                {{ line.oldLineNumber || '' }}
              </td>
              <td class="px-3 py-0.5 whitespace-pre">
                <span v-if="line.type === 'removed'" class="text-red-600">−</span>
                <span v-else-if="line.type === 'unchanged'" class="text-gray-400">&nbsp;</span>
                <span v-else>&nbsp;</span>
                {{ line.type !== 'added' ? line.oldContent : '' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- New content -->
      <div class="flex-1 overflow-x-auto">
        <table class="w-full">
          <tbody>
            <tr
              v-for="(line, index) in diffLines"
              :key="'new-' + index"
              :class="getLineClass(line, 'new')"
            >
              <td v-if="showLineNumbers" class="w-12 px-2 py-0.5 text-right text-gray-400 select-none border-r bg-gray-50">
                {{ line.newLineNumber || '' }}
              </td>
              <td class="px-3 py-0.5 whitespace-pre">
                <span v-if="line.type === 'added'" class="text-green-600">+</span>
                <span v-else-if="line.type === 'unchanged'" class="text-gray-400">&nbsp;</span>
                <span v-else>&nbsp;</span>
                {{ line.type !== 'removed' ? line.newContent : '' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Unified view -->
    <div v-else class="overflow-x-auto">
      <table class="w-full">
        <tbody>
          <tr
            v-for="(line, index) in diffLines"
            :key="'unified-' + index"
            :class="getUnifiedLineClass(line)"
          >
            <td v-if="showLineNumbers" class="w-12 px-2 py-0.5 text-right text-gray-400 select-none border-r bg-gray-50">
              {{ line.oldLineNumber || '' }}
            </td>
            <td v-if="showLineNumbers" class="w-12 px-2 py-0.5 text-right text-gray-400 select-none border-r bg-gray-50">
              {{ line.newLineNumber || '' }}
            </td>
            <td class="w-6 px-1 py-0.5 text-center select-none">
              <span v-if="line.type === 'added'" class="text-green-600">+</span>
              <span v-else-if="line.type === 'removed'" class="text-red-600">−</span>
              <span v-else class="text-gray-300">&nbsp;</span>
            </td>
            <td class="px-3 py-0.5 whitespace-pre">
              {{ line.type === 'removed' ? line.oldContent : line.newContent }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Stats footer -->
    <div v-if="showStats" class="flex items-center gap-4 px-4 py-2 text-sm border-t bg-gray-50">
      <span class="text-green-600">+{{ stats.additions }} additions</span>
      <span class="text-red-600">−{{ stats.deletions }} deletions</span>
      <span class="text-gray-500">{{ stats.unchanged }} unchanged</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  oldContent: {
    type: String,
    default: ''
  },
  newContent: {
    type: String,
    default: ''
  },
  oldTitle: {
    type: String,
    default: 'Original'
  },
  newTitle: {
    type: String,
    default: 'Modified'
  },
  viewMode: {
    type: String,
    default: 'split',
    validator: (v) => ['split', 'unified'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showLineNumbers: {
    type: Boolean,
    default: true
  },
  showStats: {
    type: Boolean,
    default: true
  },
  bordered: {
    type: Boolean,
    default: true
  }
})

// Simple line-by-line diff algorithm
const diffLines = computed(() => {
  const oldLines = props.oldContent.split('\n')
  const newLines = props.newContent.split('\n')
  const result = []
  
  // Use Longest Common Subsequence for better diff
  const lcs = computeLCS(oldLines, newLines)
  
  let oldIndex = 0
  let newIndex = 0
  let oldLineNum = 1
  let newLineNum = 1
  
  for (const item of lcs) {
    // Add removed lines
    while (oldIndex < item.oldIndex) {
      result.push({
        type: 'removed',
        oldContent: oldLines[oldIndex],
        newContent: '',
        oldLineNumber: oldLineNum++,
        newLineNumber: null
      })
      oldIndex++
    }
    
    // Add added lines
    while (newIndex < item.newIndex) {
      result.push({
        type: 'added',
        oldContent: '',
        newContent: newLines[newIndex],
        oldLineNumber: null,
        newLineNumber: newLineNum++
      })
      newIndex++
    }
    
    // Add unchanged line
    result.push({
      type: 'unchanged',
      oldContent: oldLines[oldIndex],
      newContent: newLines[newIndex],
      oldLineNumber: oldLineNum++,
      newLineNumber: newLineNum++
    })
    
    oldIndex++
    newIndex++
  }
  
  // Add remaining removed lines
  while (oldIndex < oldLines.length) {
    result.push({
      type: 'removed',
      oldContent: oldLines[oldIndex],
      newContent: '',
      oldLineNumber: oldLineNum++,
      newLineNumber: null
    })
    oldIndex++
  }
  
  // Add remaining added lines
  while (newIndex < newLines.length) {
    result.push({
      type: 'added',
      oldContent: '',
      newContent: newLines[newIndex],
      oldLineNumber: null,
      newLineNumber: newLineNum++
    })
    newIndex++
  }
  
  return result
})

// Compute Longest Common Subsequence
function computeLCS(oldLines, newLines) {
  const m = oldLines.length
  const n = newLines.length
  const dp = Array(m + 1).fill(null).map(() => Array(n + 1).fill(0))
  
  // Build LCS table
  for (let i = 1; i <= m; i++) {
    for (let j = 1; j <= n; j++) {
      if (oldLines[i - 1] === newLines[j - 1]) {
        dp[i][j] = dp[i - 1][j - 1] + 1
      } else {
        dp[i][j] = Math.max(dp[i - 1][j], dp[i][j - 1])
      }
    }
  }
  
  // Backtrack to find LCS
  const lcs = []
  let i = m
  let j = n
  
  while (i > 0 && j > 0) {
    if (oldLines[i - 1] === newLines[j - 1]) {
      lcs.unshift({ oldIndex: i - 1, newIndex: j - 1 })
      i--
      j--
    } else if (dp[i - 1][j] > dp[i][j - 1]) {
      i--
    } else {
      j--
    }
  }
  
  return lcs
}

// Calculate diff stats
const stats = computed(() => {
  return diffLines.value.reduce(
    (acc, line) => {
      if (line.type === 'added') acc.additions++
      else if (line.type === 'removed') acc.deletions++
      else acc.unchanged++
      return acc
    },
    { additions: 0, deletions: 0, unchanged: 0 }
  )
})

// Get line class for split view
function getLineClass(line, side) {
  if (side === 'old' && line.type === 'removed') {
    return 'bg-red-50'
  }
  if (side === 'new' && line.type === 'added') {
    return 'bg-green-50'
  }
  if (line.type === 'unchanged') {
    return ''
  }
  return 'bg-gray-50'
}

// Get line class for unified view
function getUnifiedLineClass(line) {
  if (line.type === 'added') return 'bg-green-50'
  if (line.type === 'removed') return 'bg-red-50'
  return ''
}

defineExpose({
  stats,
  diffLines
})
</script>

<style scoped>
.diff-viewer table {
  border-collapse: collapse;
}

.diff-viewer tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}
</style>
