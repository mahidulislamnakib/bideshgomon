<template>
  <div :class="['heat-map', containerClasses]">
    <!-- Title -->
    <div v-if="title" class="mb-4">
      <h3 :class="['text-lg font-semibold', themeClasses.title]">{{ title }}</h3>
      <p v-if="subtitle" :class="['text-sm mt-1', themeClasses.subtitle]">{{ subtitle }}</p>
    </div>

    <!-- Controls -->
    <div v-if="showControls" class="flex flex-wrap items-center gap-4 mb-4">
      <!-- Color Scheme Selector -->
      <div class="flex items-center gap-2">
        <span :class="['text-sm', themeClasses.label]">Colors:</span>
        <select
          v-model="selectedScheme"
          :class="[
            'text-sm px-2 py-1 rounded border',
            themeClasses.select
          ]"
        >
          <option v-for="scheme in colorSchemes" :key="scheme.name" :value="scheme.name">
            {{ scheme.label }}
          </option>
        </select>
      </div>

      <!-- Value Display Toggle -->
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          v-model="showValues"
          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <span :class="['text-sm', themeClasses.label]">Show values</span>
      </label>
    </div>

    <!-- Heat Map Grid -->
    <div class="overflow-x-auto">
      <div class="inline-block min-w-full">
        <!-- Column Headers -->
        <div class="flex" :style="{ marginLeft: `${rowLabelWidth}px` }">
          <div
            v-for="(col, colIndex) in columns"
            :key="`col-${colIndex}`"
            :class="[
              'flex-shrink-0 text-center text-xs font-medium truncate',
              themeClasses.header
            ]"
            :style="{ width: `${cellSize}px`, height: `${headerHeight}px`, lineHeight: `${headerHeight}px` }"
            :title="col"
          >
            {{ col }}
          </div>
        </div>

        <!-- Rows -->
        <div
          v-for="(row, rowIndex) in rows"
          :key="`row-${rowIndex}`"
          class="flex items-center"
        >
          <!-- Row Label -->
          <div
            :class="[
              'flex-shrink-0 text-xs font-medium truncate pr-2',
              themeClasses.header
            ]"
            :style="{ width: `${rowLabelWidth}px`, height: `${cellSize}px`, lineHeight: `${cellSize}px` }"
            :title="row"
          >
            {{ row }}
          </div>

          <!-- Cells -->
          <div
            v-for="(col, colIndex) in columns"
            :key="`cell-${rowIndex}-${colIndex}`"
            :class="[
              'flex-shrink-0 flex items-center justify-center cursor-pointer transition-all duration-200',
              'hover:ring-2 hover:ring-offset-1',
              themeClasses.cellHover,
              cellRadius
            ]"
            :style="{
              width: `${cellSize}px`,
              height: `${cellSize}px`,
              backgroundColor: getCellColor(rowIndex, colIndex),
              margin: `${cellGap / 2}px`
            }"
            @click="handleCellClick(rowIndex, colIndex)"
            @mouseenter="hoveredCell = { row: rowIndex, col: colIndex }"
            @mouseleave="hoveredCell = null"
          >
            <span
              v-if="showValues"
              :class="[
                'text-xs font-medium',
                getTextColor(rowIndex, colIndex)
              ]"
            >
              {{ formatValue(getCellValue(rowIndex, colIndex)) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tooltip -->
    <Transition name="fade">
      <div
        v-if="hoveredCell && showTooltip"
        :class="[
          'fixed z-50 px-3 py-2 rounded-lg shadow-lg text-sm pointer-events-none',
          themeClasses.tooltip
        ]"
        :style="tooltipStyle"
      >
        <div class="font-semibold">{{ rows[hoveredCell.row] }} × {{ columns[hoveredCell.col] }}</div>
        <div class="mt-1">
          <span class="opacity-70">Value: </span>
          <span class="font-medium">{{ formatValue(getCellValue(hoveredCell.row, hoveredCell.col)) }}</span>
        </div>
      </div>
    </Transition>

    <!-- Legend -->
    <div v-if="showLegend" class="mt-4 flex items-center gap-2">
      <span :class="['text-xs', themeClasses.label]">{{ formatValue(minValue) }}</span>
      <div
        class="flex-1 h-3 rounded-full overflow-hidden"
        :style="{ background: legendGradient, maxWidth: '200px' }"
      />
      <span :class="['text-xs', themeClasses.label]">{{ formatValue(maxValue) }}</span>
    </div>

    <!-- Summary Stats -->
    <div v-if="showStats" class="mt-4 grid grid-cols-4 gap-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        :class="['text-center p-2 rounded', themeClasses.statBg]"
      >
        <div :class="['text-lg font-bold', themeClasses.statValue]">
          {{ formatValue(stat.value) }}
        </div>
        <div :class="['text-xs', themeClasses.statLabel]">{{ stat.label }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    required: true
    // 2D array: [[1,2,3], [4,5,6], ...]
  },
  rows: {
    type: Array,
    required: true
    // Row labels: ['Mon', 'Tue', ...]
  },
  columns: {
    type: Array,
    required: true
    // Column labels: ['12am', '1am', ...]
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  colorScheme: {
    type: String,
    default: 'blue',
    validator: v => ['blue', 'green', 'red', 'purple', 'orange', 'viridis', 'plasma'].includes(v)
  },
  cellSize: {
    type: Number,
    default: 32
  },
  cellGap: {
    type: Number,
    default: 2
  },
  cellRadius: {
    type: String,
    default: 'rounded',
    validator: v => ['rounded-none', 'rounded-sm', 'rounded', 'rounded-lg', 'rounded-full'].includes(v)
  },
  rowLabelWidth: {
    type: Number,
    default: 60
  },
  headerHeight: {
    type: Number,
    default: 24
  },
  showTooltip: {
    type: Boolean,
    default: true
  },
  showLegend: {
    type: Boolean,
    default: true
  },
  showStats: {
    type: Boolean,
    default: false
  },
  showControls: {
    type: Boolean,
    default: false
  },
  valueFormat: {
    type: Function,
    default: null
  },
  minColor: {
    type: String,
    default: null
  },
  maxColor: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['cell-click'])

const hoveredCell = ref(null)
const mousePosition = ref({ x: 0, y: 0 })
const showValues = ref(false)
const selectedScheme = ref(props.colorScheme)

const colorSchemes = [
  { name: 'blue', label: 'Blue', colors: ['#EFF6FF', '#3B82F6', '#1E3A8A'] },
  { name: 'green', label: 'Green', colors: ['#F0FDF4', '#22C55E', '#14532D'] },
  { name: 'red', label: 'Red', colors: ['#FEF2F2', '#EF4444', '#7F1D1D'] },
  { name: 'purple', label: 'Purple', colors: ['#FAF5FF', '#A855F7', '#581C87'] },
  { name: 'orange', label: 'Orange', colors: ['#FFF7ED', '#F97316', '#7C2D12'] },
  { name: 'viridis', label: 'Viridis', colors: ['#FDE725', '#21918C', '#440154'] },
  { name: 'plasma', label: 'Plasma', colors: ['#F0F921', '#CC4778', '#0D0887'] }
]

const currentScheme = computed(() => {
  return colorSchemes.find(s => s.name === selectedScheme.value) || colorSchemes[0]
})

const flatData = computed(() => {
  return props.data.flat().filter(v => v !== null && v !== undefined)
})

const minValue = computed(() => Math.min(...flatData.value))
const maxValue = computed(() => Math.max(...flatData.value))

const stats = computed(() => {
  const values = flatData.value
  const sum = values.reduce((a, b) => a + b, 0)
  const avg = sum / values.length
  return [
    { label: 'Min', value: minValue.value },
    { label: 'Max', value: maxValue.value },
    { label: 'Average', value: avg },
    { label: 'Total', value: sum }
  ]
})

const getCellValue = (rowIndex, colIndex) => {
  return props.data[rowIndex]?.[colIndex] ?? 0
}

const getCellColor = (rowIndex, colIndex) => {
  const value = getCellValue(rowIndex, colIndex)
  if (value === null || value === undefined) {
    return props.theme === 'dark' ? '#374151' : '#F3F4F6'
  }
  
  const normalized = maxValue.value === minValue.value
    ? 0.5
    : (value - minValue.value) / (maxValue.value - minValue.value)
  
  const colors = currentScheme.value.colors
  
  // Interpolate between colors
  if (normalized <= 0.5) {
    const t = normalized * 2
    return interpolateColor(colors[0], colors[1], t)
  } else {
    const t = (normalized - 0.5) * 2
    return interpolateColor(colors[1], colors[2], t)
  }
}

const interpolateColor = (color1, color2, t) => {
  const hex = (c) => parseInt(c, 16)
  const r1 = hex(color1.slice(1, 3))
  const g1 = hex(color1.slice(3, 5))
  const b1 = hex(color1.slice(5, 7))
  const r2 = hex(color2.slice(1, 3))
  const g2 = hex(color2.slice(3, 5))
  const b2 = hex(color2.slice(5, 7))
  
  const r = Math.round(r1 + (r2 - r1) * t)
  const g = Math.round(g1 + (g2 - g1) * t)
  const b = Math.round(b1 + (b2 - b1) * t)
  
  return `rgb(${r}, ${g}, ${b})`
}

const getTextColor = (rowIndex, colIndex) => {
  const value = getCellValue(rowIndex, colIndex)
  const normalized = maxValue.value === minValue.value
    ? 0.5
    : (value - minValue.value) / (maxValue.value - minValue.value)
  
  return normalized > 0.5 ? 'text-white' : 'text-gray-900'
}

const formatValue = (value) => {
  if (props.valueFormat) {
    return props.valueFormat(value)
  }
  if (Number.isInteger(value)) {
    return value.toLocaleString()
  }
  return value.toLocaleString(undefined, { maximumFractionDigits: 2 })
}

const handleCellClick = (rowIndex, colIndex) => {
  emit('cell-click', {
    row: rowIndex,
    col: colIndex,
    rowLabel: props.rows[rowIndex],
    colLabel: props.columns[colIndex],
    value: getCellValue(rowIndex, colIndex)
  })
}

const tooltipStyle = computed(() => {
  if (!hoveredCell.value) return {}
  return {
    left: `${mousePosition.value.x + 15}px`,
    top: `${mousePosition.value.y + 15}px`
  }
})

const legendGradient = computed(() => {
  const colors = currentScheme.value.colors
  return `linear-gradient(to right, ${colors.join(', ')})`
})

const containerClasses = computed(() => {
  return props.theme === 'dark' ? 'text-white' : 'text-gray-900'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      title: 'text-white',
      subtitle: 'text-gray-400',
      label: 'text-gray-400',
      header: 'text-gray-400',
      select: 'bg-gray-800 border-gray-600 text-white',
      cellHover: 'hover:ring-white/50',
      tooltip: 'bg-gray-800 text-white border border-gray-700',
      statBg: 'bg-gray-800',
      statValue: 'text-white',
      statLabel: 'text-gray-400'
    }
  }
  return {
    title: 'text-gray-900',
    subtitle: 'text-gray-500',
    label: 'text-gray-600',
    header: 'text-gray-600',
    select: 'bg-white border-gray-300 text-gray-900',
    cellHover: 'hover:ring-gray-400',
    tooltip: 'bg-white text-gray-900 border border-gray-200',
    statBg: 'bg-gray-100',
    statValue: 'text-gray-900',
    statLabel: 'text-gray-600'
  }
})

const handleMouseMove = (e) => {
  mousePosition.value = { x: e.clientX, y: e.clientY }
}

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
})

onUnmounted(() => {
  window.removeEventListener('mousemove', handleMouseMove)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
