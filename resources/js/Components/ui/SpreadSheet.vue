<template>
  <div :class="['spreadsheet', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
          <TableCellsIcon class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ rows }} rows × {{ cols }} columns
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          @click="addRow"
          title="Add Row"
        >
          <PlusIcon class="w-4 h-4" />
        </button>
        <button
          type="button"
          class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          @click="exportData"
          title="Export CSV"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
      <button
        v-for="action in toolbarActions"
        :key="action.name"
        type="button"
        class="p-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
        :title="action.label"
        @click="action.handler"
      >
        <component :is="action.icon" class="w-4 h-4" />
      </button>
      <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />
      <div class="flex-1 flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono min-w-12">
          {{ selectedCell ? `${getColumnLabel(selectedCell.col)}${selectedCell.row + 1}` : '' }}
        </span>
        <input
          ref="formulaInput"
          type="text"
          :value="selectedCell ? getCellValue(selectedCell.row, selectedCell.col) : ''"
          class="flex-1 px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded"
          placeholder="Enter value or formula..."
          @input="onFormulaInput($event.target.value)"
          @keydown.enter="moveToNextRow"
          @keydown.tab.prevent="moveToNextCol"
        />
      </div>
    </div>

    <!-- Spreadsheet Grid -->
    <div 
      ref="gridContainer"
      class="overflow-auto"
      :style="{ height }"
      @scroll="onScroll"
    >
      <table class="border-collapse min-w-full">
        <!-- Column Headers -->
        <thead class="sticky top-0 z-10">
          <tr>
            <th class="sticky left-0 z-20 w-12 min-w-12 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700" />
            <th
              v-for="col in cols"
              :key="`header-${col}`"
              :class="[
                'min-w-24 px-2 py-1.5 text-xs font-medium text-center border border-gray-200 dark:border-gray-700',
                selectedCell?.col === col - 1 || isColumnSelected(col - 1)
                  ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
              ]"
              @click="selectColumn(col - 1)"
              @contextmenu.prevent="showColumnMenu(col - 1, $event)"
            >
              {{ getColumnLabel(col - 1) }}
            </th>
          </tr>
        </thead>

        <!-- Data Rows -->
        <tbody>
          <tr v-for="row in rows" :key="`row-${row}`">
            <!-- Row Header -->
            <td
              :class="[
                'sticky left-0 z-10 w-12 min-w-12 px-2 py-1 text-xs font-medium text-center border border-gray-200 dark:border-gray-700',
                selectedCell?.row === row - 1 || isRowSelected(row - 1)
                  ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
              ]"
              @click="selectRow(row - 1)"
              @contextmenu.prevent="showRowMenu(row - 1, $event)"
            >
              {{ row }}
            </td>

            <!-- Data Cells -->
            <td
              v-for="col in cols"
              :key="`cell-${row}-${col}`"
              :class="[
                'relative min-w-24 border border-gray-200 dark:border-gray-700 p-0',
                getCellClasses(row - 1, col - 1)
              ]"
              @click="selectCell(row - 1, col - 1)"
              @dblclick="startEditing(row - 1, col - 1)"
            >
              <!-- Editing Mode -->
              <input
                v-if="isEditing(row - 1, col - 1)"
                ref="cellInput"
                type="text"
                :value="getCellValue(row - 1, col - 1)"
                class="absolute inset-0 w-full h-full px-2 py-1 text-sm bg-white dark:bg-gray-700 border-2 border-blue-500 outline-none"
                @input="onCellInput(row - 1, col - 1, $event.target.value)"
                @blur="stopEditing"
                @keydown.enter="commitAndMove('down')"
                @keydown.tab.prevent="commitAndMove('right')"
                @keydown.esc="cancelEditing"
              />
              
              <!-- Display Mode -->
              <div
                v-else
                class="px-2 py-1 text-sm truncate"
                :style="getCellStyle(row - 1, col - 1)"
              >
                {{ getDisplayValue(row - 1, col - 1) }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Context Menu -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-100"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="contextMenu.visible"
          class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl py-1 min-w-40"
          :style="{ left: `${contextMenu.x}px`, top: `${contextMenu.y}px` }"
          @click="contextMenu.visible = false"
        >
          <button
            v-for="item in contextMenu.items"
            :key="item.label"
            class="w-full px-4 py-2 text-sm text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
            @click="item.handler"
          >
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.label }}
          </button>
        </div>
      </Transition>
    </Teleport>

    <!-- Status Bar -->
    <div v-if="showStatusBar" class="flex items-center justify-between px-3 py-1.5 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
      <div class="flex items-center gap-4">
        <span v-if="selection.length > 1">{{ selection.length }} cells selected</span>
        <span v-else-if="selectedCell">Cell {{ getColumnLabel(selectedCell.col) }}{{ selectedCell.row + 1 }}</span>
      </div>
      <div class="flex items-center gap-4">
        <span v-if="selectionStats.sum !== null">Sum: {{ selectionStats.sum.toFixed(2) }}</span>
        <span v-if="selectionStats.avg !== null">Avg: {{ selectionStats.avg.toFixed(2) }}</span>
        <span v-if="selectionStats.count">Count: {{ selectionStats.count }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import {
  TableCellsIcon,
  PlusIcon,
  ArrowDownTrayIcon,
  TrashIcon,
  ClipboardDocumentIcon,
  ClipboardIcon,
  ArrowsUpDownIcon,
  ArrowsRightLeftIcon,
  BoldIcon
} from '@heroicons/vue/24/outline'
import { Teleport, Transition } from 'vue'

const props = defineProps({
  title: { type: String, default: 'Spreadsheet' },
  initialData: { type: Array, default: () => [] },
  initialRows: { type: Number, default: 20 },
  initialCols: { type: Number, default: 10 },
  height: { type: String, default: '400px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showStatusBar: { type: Boolean, default: true }
})

const emit = defineEmits(['change', 'cell-change', 'export'])

const gridContainer = ref(null)
const formulaInput = ref(null)
const cellInput = ref(null)

const data = reactive(new Map())
const cellStyles = reactive(new Map())
const rows = ref(props.initialRows)
const cols = ref(props.initialCols)
const selectedCell = ref(null)
const editingCell = ref(null)
const selection = ref([])
const clipboard = ref(null)

const contextMenu = reactive({
  visible: false,
  x: 0,
  y: 0,
  items: []
})

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
])

const toolbarActions = [
  { name: 'copy', label: 'Copy', icon: ClipboardDocumentIcon, handler: () => copySelection() },
  { name: 'paste', label: 'Paste', icon: ClipboardIcon, handler: () => pasteClipboard() },
  { name: 'delete', label: 'Delete', icon: TrashIcon, handler: () => deleteSelection() }
]

const selectionStats = computed(() => {
  const values = selection.value
    .map(s => parseFloat(getCellValue(s.row, s.col)))
    .filter(v => !isNaN(v))
  
  if (values.length === 0) return { sum: null, avg: null, count: 0 }
  
  const sum = values.reduce((a, b) => a + b, 0)
  return {
    sum,
    avg: sum / values.length,
    count: values.length
  }
})

const getCellKey = (row, col) => `${row},${col}`

const getCellValue = (row, col) => data.get(getCellKey(row, col)) || ''

const setCellValue = (row, col, value) => {
  const key = getCellKey(row, col)
  if (value === '') {
    data.delete(key)
  } else {
    data.set(key, value)
  }
  emit('cell-change', { row, col, value })
  emit('change', getDataArray())
}

const getDisplayValue = (row, col) => {
  const value = getCellValue(row, col)
  if (typeof value === 'string' && value.startsWith('=')) {
    return evaluateFormula(value, row, col)
  }
  return value
}

const evaluateFormula = (formula, row, col) => {
  try {
    const expr = formula.substring(1).toUpperCase()
    
    // SUM function
    const sumMatch = expr.match(/SUM\(([A-Z]+)(\d+):([A-Z]+)(\d+)\)/)
    if (sumMatch) {
      const [, startCol, startRow, endCol, endRow] = sumMatch
      let sum = 0
      for (let r = parseInt(startRow) - 1; r <= parseInt(endRow) - 1; r++) {
        for (let c = getColumnIndex(startCol); c <= getColumnIndex(endCol); c++) {
          const val = parseFloat(getCellValue(r, c))
          if (!isNaN(val)) sum += val
        }
      }
      return sum
    }
    
    // AVG function
    const avgMatch = expr.match(/AVG\(([A-Z]+)(\d+):([A-Z]+)(\d+)\)/)
    if (avgMatch) {
      const [, startCol, startRow, endCol, endRow] = avgMatch
      let sum = 0, count = 0
      for (let r = parseInt(startRow) - 1; r <= parseInt(endRow) - 1; r++) {
        for (let c = getColumnIndex(startCol); c <= getColumnIndex(endCol); c++) {
          const val = parseFloat(getCellValue(r, c))
          if (!isNaN(val)) { sum += val; count++ }
        }
      }
      return count > 0 ? (sum / count).toFixed(2) : 0
    }
    
    // Cell reference
    const cellRef = expr.match(/^([A-Z]+)(\d+)$/)
    if (cellRef) {
      const [, colLabel, rowNum] = cellRef
      return getDisplayValue(parseInt(rowNum) - 1, getColumnIndex(colLabel))
    }
    
    // Basic math
    let evalExpr = expr.replace(/([A-Z]+)(\d+)/g, (_, colLabel, rowNum) => {
      return getDisplayValue(parseInt(rowNum) - 1, getColumnIndex(colLabel)) || 0
    })
    return eval(evalExpr)
  } catch (e) {
    return '#ERROR'
  }
}

const getColumnLabel = (index) => {
  let label = ''
  let num = index
  while (num >= 0) {
    label = String.fromCharCode(65 + (num % 26)) + label
    num = Math.floor(num / 26) - 1
  }
  return label
}

const getColumnIndex = (label) => {
  let index = 0
  for (let i = 0; i < label.length; i++) {
    index = index * 26 + (label.charCodeAt(i) - 64)
  }
  return index - 1
}

const getCellClasses = (row, col) => {
  const isSelected = selectedCell.value?.row === row && selectedCell.value?.col === col
  const inSelection = selection.value.some(s => s.row === row && s.col === col)
  
  return [
    isSelected ? 'bg-blue-100 dark:bg-blue-900/30 ring-2 ring-blue-500 ring-inset' : '',
    inSelection && !isSelected ? 'bg-blue-50 dark:bg-blue-900/20' : '',
    !isSelected && !inSelection ? 'bg-white dark:bg-gray-900' : ''
  ]
}

const getCellStyle = (row, col) => {
  return cellStyles.get(getCellKey(row, col)) || {}
}

const selectCell = (row, col) => {
  selectedCell.value = { row, col }
  selection.value = [{ row, col }]
  if (editingCell.value) stopEditing()
}

const selectRow = (row) => {
  selection.value = []
  for (let c = 0; c < cols.value; c++) {
    selection.value.push({ row, col: c })
  }
  selectedCell.value = { row, col: 0 }
}

const selectColumn = (col) => {
  selection.value = []
  for (let r = 0; r < rows.value; r++) {
    selection.value.push({ row: r, col })
  }
  selectedCell.value = { row: 0, col }
}

const isRowSelected = (row) => selection.value.some(s => s.row === row)
const isColumnSelected = (col) => selection.value.some(s => s.col === col)

const startEditing = (row, col) => {
  editingCell.value = { row, col }
  nextTick(() => cellInput.value?.[0]?.focus())
}

const isEditing = (row, col) => editingCell.value?.row === row && editingCell.value?.col === col

const stopEditing = () => { editingCell.value = null }
const cancelEditing = () => { editingCell.value = null }

const onCellInput = (row, col, value) => {
  setCellValue(row, col, value)
}

const onFormulaInput = (value) => {
  if (selectedCell.value) {
    setCellValue(selectedCell.value.row, selectedCell.value.col, value)
  }
}

const commitAndMove = (direction) => {
  stopEditing()
  if (!selectedCell.value) return
  
  let { row, col } = selectedCell.value
  if (direction === 'down') row = Math.min(row + 1, rows.value - 1)
  else if (direction === 'right') col = Math.min(col + 1, cols.value - 1)
  
  selectCell(row, col)
}

const moveToNextRow = () => commitAndMove('down')
const moveToNextCol = () => commitAndMove('right')

const addRow = () => { rows.value++ }
const addColumn = () => { cols.value++ }

const deleteRow = (row) => {
  for (let c = 0; c < cols.value; c++) {
    data.delete(getCellKey(row, c))
  }
  rows.value = Math.max(1, rows.value - 1)
}

const deleteColumn = (col) => {
  for (let r = 0; r < rows.value; r++) {
    data.delete(getCellKey(r, col))
  }
  cols.value = Math.max(1, cols.value - 1)
}

const copySelection = () => {
  clipboard.value = selection.value.map(s => ({
    ...s,
    value: getCellValue(s.row, s.col)
  }))
}

const pasteClipboard = () => {
  if (!clipboard.value || !selectedCell.value) return
  
  const baseRow = selectedCell.value.row
  const baseCol = selectedCell.value.col
  const clipboardBaseRow = Math.min(...clipboard.value.map(c => c.row))
  const clipboardBaseCol = Math.min(...clipboard.value.map(c => c.col))
  
  clipboard.value.forEach(item => {
    const newRow = baseRow + (item.row - clipboardBaseRow)
    const newCol = baseCol + (item.col - clipboardBaseCol)
    if (newRow < rows.value && newCol < cols.value) {
      setCellValue(newRow, newCol, item.value)
    }
  })
}

const deleteSelection = () => {
  selection.value.forEach(s => setCellValue(s.row, s.col, ''))
}

const showRowMenu = (row, event) => {
  contextMenu.x = event.clientX
  contextMenu.y = event.clientY
  contextMenu.items = [
    { label: 'Insert Row Above', icon: PlusIcon, handler: () => {} },
    { label: 'Insert Row Below', icon: PlusIcon, handler: () => addRow() },
    { label: 'Delete Row', icon: TrashIcon, handler: () => deleteRow(row) }
  ]
  contextMenu.visible = true
}

const showColumnMenu = (col, event) => {
  contextMenu.x = event.clientX
  contextMenu.y = event.clientY
  contextMenu.items = [
    { label: 'Insert Column Left', icon: PlusIcon, handler: () => {} },
    { label: 'Insert Column Right', icon: PlusIcon, handler: () => addColumn() },
    { label: 'Delete Column', icon: TrashIcon, handler: () => deleteColumn(col) }
  ]
  contextMenu.visible = true
}

const getDataArray = () => {
  const result = []
  for (let r = 0; r < rows.value; r++) {
    const row = []
    for (let c = 0; c < cols.value; c++) {
      row.push(getCellValue(r, c))
    }
    result.push(row)
  }
  return result
}

const exportData = () => {
  const dataArray = getDataArray()
  const csv = dataArray.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n')
  emit('export', csv)
  
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'spreadsheet.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const onScroll = () => {}

const handleKeydown = (e) => {
  if (!selectedCell.value || editingCell.value) return
  
  const { row, col } = selectedCell.value
  
  switch (e.key) {
    case 'ArrowUp': selectCell(Math.max(0, row - 1), col); e.preventDefault(); break
    case 'ArrowDown': selectCell(Math.min(rows.value - 1, row + 1), col); e.preventDefault(); break
    case 'ArrowLeft': selectCell(row, Math.max(0, col - 1)); e.preventDefault(); break
    case 'ArrowRight': selectCell(row, Math.min(cols.value - 1, col + 1)); e.preventDefault(); break
    case 'Enter': startEditing(row, col); e.preventDefault(); break
    case 'Delete':
    case 'Backspace': deleteSelection(); e.preventDefault(); break
    case 'c': if (e.ctrlKey) { copySelection(); e.preventDefault() } break
    case 'v': if (e.ctrlKey) { pasteClipboard(); e.preventDefault() } break
  }
}

onMounted(() => {
  if (props.initialData.length > 0) {
    props.initialData.forEach((row, r) => {
      row.forEach((cell, c) => {
        if (cell) setCellValue(r, c, cell)
      })
    })
    rows.value = Math.max(rows.value, props.initialData.length)
    cols.value = Math.max(cols.value, Math.max(...props.initialData.map(r => r.length)))
  }
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>
