<template>
  <div class="radar-chart" ref="containerRef">
    <!-- Header -->
    <div v-if="title" class="mb-4 text-center">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
      <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ subtitle }}</p>
    </div>

    <!-- SVG Chart -->
    <svg
      :width="size"
      :height="size"
      :viewBox="`0 0 ${size} ${size}`"
      class="mx-auto overflow-visible"
    >
      <g :transform="`translate(${size / 2}, ${size / 2})`">
        <!-- Background Grid -->
        <g class="radar-grid">
          <!-- Concentric polygons (levels) -->
          <polygon
            v-for="level in levels"
            :key="`level-${level}`"
            :points="getLevelPoints(level)"
            fill="none"
            :stroke="gridColor"
            stroke-width="1"
            class="transition-all duration-300"
          />
          
          <!-- Axis lines -->
          <line
            v-for="(axis, index) in axes"
            :key="`axis-${index}`"
            x1="0"
            y1="0"
            :x2="getAxisEndpoint(index).x"
            :y2="getAxisEndpoint(index).y"
            :stroke="gridColor"
            stroke-width="1"
          />
        </g>

        <!-- Data Areas -->
        <g class="radar-data">
          <template v-for="(dataset, dataIndex) in datasets" :key="`dataset-${dataIndex}`">
            <!-- Filled Area -->
            <polygon
              :points="getDataPoints(dataset)"
              :fill="dataset.backgroundColor || getDefaultColor(dataIndex, 0.2)"
              :stroke="dataset.borderColor || getDefaultColor(dataIndex)"
              :stroke-width="dataset.borderWidth || 2"
              class="transition-all duration-500 ease-out cursor-pointer"
              :class="{ 'opacity-50': hoveredDataset !== null && hoveredDataset !== dataIndex }"
              @mouseenter="hoveredDataset = dataIndex"
              @mouseleave="hoveredDataset = null"
            />
            
            <!-- Data Points -->
            <circle
              v-for="(value, valueIndex) in dataset.data"
              :key="`point-${dataIndex}-${valueIndex}`"
              :cx="getPointPosition(valueIndex, value).x"
              :cy="getPointPosition(valueIndex, value).y"
              :r="showPoints ? (hoveredPoint?.dataset === dataIndex && hoveredPoint?.point === valueIndex ? 6 : 4) : 0"
              :fill="dataset.pointBackgroundColor || dataset.borderColor || getDefaultColor(dataIndex)"
              stroke="white"
              stroke-width="2"
              class="transition-all duration-200 cursor-pointer"
              @mouseenter="handlePointHover(dataIndex, valueIndex, value)"
              @mouseleave="hoveredPoint = null"
            />
          </template>
        </g>

        <!-- Axis Labels -->
        <g class="radar-labels" v-if="showLabels">
          <text
            v-for="(axis, index) in axes"
            :key="`label-${index}`"
            :x="getLabelPosition(index).x"
            :y="getLabelPosition(index).y"
            :text-anchor="getLabelAnchor(index)"
            dominant-baseline="middle"
            class="text-xs fill-gray-600 dark:fill-gray-400 font-medium"
          >
            {{ axis }}
          </text>
        </g>

        <!-- Level Labels -->
        <g class="radar-level-labels" v-if="showLevelLabels">
          <text
            v-for="level in levels"
            :key="`level-label-${level}`"
            :x="5"
            :y="-((level / levels) * radius)"
            class="text-[10px] fill-gray-400 dark:fill-gray-500"
          >
            {{ Math.round((level / levels) * maxValue) }}
          </text>
        </g>
      </g>
    </svg>

    <!-- Tooltip -->
    <Transition name="tooltip">
      <div
        v-if="hoveredPoint && showTooltip"
        class="absolute z-10 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg pointer-events-none"
        :style="tooltipStyle"
      >
        <div class="font-medium">{{ datasets[hoveredPoint.dataset].label || `Dataset ${hoveredPoint.dataset + 1}` }}</div>
        <div class="text-gray-300">
          {{ axes[hoveredPoint.point] }}: <span class="font-semibold">{{ hoveredPoint.value }}</span>
        </div>
      </div>
    </Transition>

    <!-- Legend -->
    <div v-if="showLegend && datasets.length > 1" class="mt-4 flex flex-wrap justify-center gap-4">
      <div
        v-for="(dataset, index) in datasets"
        :key="`legend-${index}`"
        class="flex items-center gap-2 text-sm cursor-pointer transition-opacity"
        :class="{ 'opacity-50': hoveredDataset !== null && hoveredDataset !== index }"
        @mouseenter="hoveredDataset = index"
        @mouseleave="hoveredDataset = null"
      >
        <span
          class="w-3 h-3 rounded-full"
          :style="{ backgroundColor: dataset.borderColor || getDefaultColor(index) }"
        />
        <span class="text-gray-600 dark:text-gray-400">{{ dataset.label || `Dataset ${index + 1}` }}</span>
      </div>
    </div>

    <!-- Comparison Stats -->
    <div v-if="showComparison && datasets.length === 2" class="mt-4 grid grid-cols-2 gap-4 text-center">
      <div
        v-for="(dataset, index) in datasets"
        :key="`comparison-${index}`"
        class="p-3 rounded-lg"
        :style="{ backgroundColor: (dataset.borderColor || getDefaultColor(index)) + '20' }"
      >
        <div class="text-2xl font-bold" :style="{ color: dataset.borderColor || getDefaultColor(index) }">
          {{ calculateAverage(dataset.data) }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ dataset.label || `Dataset ${index + 1}` }} Avg
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  axes: { type: Array, required: true },
  datasets: { type: Array, required: true },
  size: { type: Number, default: 400 },
  levels: { type: Number, default: 5 },
  maxValue: { type: Number, default: 100 },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  showLabels: { type: Boolean, default: true },
  showLevelLabels: { type: Boolean, default: true },
  showPoints: { type: Boolean, default: true },
  showTooltip: { type: Boolean, default: true },
  showLegend: { type: Boolean, default: true },
  showComparison: { type: Boolean, default: false },
  gridColor: { type: String, default: '#E5E7EB' },
  colors: {
    type: Array,
    default: () => [
      '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#6366F1',
      '#EC4899', '#8B5CF6', '#14B8A6'
    ]
  },
  animated: { type: Boolean, default: true }
})

const emit = defineEmits(['pointClick', 'datasetClick'])

const containerRef = ref(null)
const hoveredDataset = ref(null)
const hoveredPoint = ref(null)

const radius = computed(() => props.size / 2 - 50)
const angleStep = computed(() => (2 * Math.PI) / props.axes.length)

const getDefaultColor = (index, alpha = 1) => {
  const color = props.colors[index % props.colors.length]
  if (alpha === 1) return color
  const r = parseInt(color.slice(1, 3), 16)
  const g = parseInt(color.slice(3, 5), 16)
  const b = parseInt(color.slice(5, 7), 16)
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

const getAxisEndpoint = (index) => {
  const angle = angleStep.value * index - Math.PI / 2
  return {
    x: Math.cos(angle) * radius.value,
    y: Math.sin(angle) * radius.value
  }
}

const getLevelPoints = (level) => {
  const levelRadius = (level / props.levels) * radius.value
  const points = []
  
  for (let i = 0; i < props.axes.length; i++) {
    const angle = angleStep.value * i - Math.PI / 2
    points.push(`${Math.cos(angle) * levelRadius},${Math.sin(angle) * levelRadius}`)
  }
  
  return points.join(' ')
}

const getPointPosition = (axisIndex, value) => {
  const normalizedValue = Math.min(value / props.maxValue, 1)
  const angle = angleStep.value * axisIndex - Math.PI / 2
  return {
    x: Math.cos(angle) * radius.value * normalizedValue,
    y: Math.sin(angle) * radius.value * normalizedValue
  }
}

const getDataPoints = (dataset) => {
  const points = dataset.data.map((value, index) => {
    const pos = getPointPosition(index, value)
    return `${pos.x},${pos.y}`
  })
  return points.join(' ')
}

const getLabelPosition = (index) => {
  const angle = angleStep.value * index - Math.PI / 2
  const labelRadius = radius.value + 20
  return {
    x: Math.cos(angle) * labelRadius,
    y: Math.sin(angle) * labelRadius
  }
}

const getLabelAnchor = (index) => {
  const angle = (angleStep.value * index) * (180 / Math.PI) - 90
  if (angle > -45 && angle < 45) return 'middle'
  if (angle >= 45 && angle <= 135) return 'start'
  if (angle >= -135 && angle <= -45) return 'end'
  return 'middle'
}

const handlePointHover = (dataset, point, value) => {
  hoveredPoint.value = { dataset, point, value }
}

const tooltipStyle = computed(() => {
  if (!hoveredPoint.value || !containerRef.value) return {}
  
  const pos = getPointPosition(hoveredPoint.value.point, hoveredPoint.value.value)
  const rect = containerRef.value.getBoundingClientRect()
  
  return {
    left: `${props.size / 2 + pos.x}px`,
    top: `${props.size / 2 + pos.y - 10}px`,
    transform: 'translate(-50%, -100%)'
  }
})

const calculateAverage = (data) => {
  if (!data?.length) return 0
  const sum = data.reduce((acc, val) => acc + val, 0)
  return Math.round(sum / data.length)
}
</script>

<style scoped>
.radar-chart {
  @apply relative;
}

.tooltip-enter-active,
.tooltip-leave-active {
  transition: all 0.15s ease;
}

.tooltip-enter-from,
.tooltip-leave-to {
  opacity: 0;
  transform: translate(-50%, -100%) translateY(5px);
}

/* Dark mode grid color */
:deep(.dark) .radar-grid polygon,
:deep(.dark) .radar-grid line {
  stroke: #374151;
}
</style>
