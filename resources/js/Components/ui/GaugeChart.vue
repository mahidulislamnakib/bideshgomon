<template>
  <div :class="['gauge-chart', containerClasses]">
    <!-- Label -->
    <div v-if="label" class="text-center mb-2">
      <span :class="['text-sm font-medium', themeClasses.label]">{{ label }}</span>
    </div>

    <!-- Gauge Container -->
    <div class="relative" :style="{ width: `${size}px`, height: `${size * 0.6}px` }">
      <svg
        :width="size"
        :height="size * 0.65"
        :viewBox="`0 0 ${size} ${size * 0.65}`"
        class="overflow-visible"
      >
        <!-- Background Arc -->
        <path
          :d="backgroundArc"
          fill="none"
          :stroke="themeClasses.trackColor"
          :stroke-width="strokeWidth"
          stroke-linecap="round"
        />

        <!-- Gradient Definition -->
        <defs>
          <linearGradient :id="`gauge-gradient-${uid}`" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop
              v-for="(stop, i) in gradientStops"
              :key="i"
              :offset="stop.offset"
              :stop-color="stop.color"
            />
          </linearGradient>
        </defs>

        <!-- Value Arc -->
        <path
          :d="valueArc"
          fill="none"
          :stroke="useGradient ? `url(#gauge-gradient-${uid})` : currentColor"
          :stroke-width="strokeWidth"
          stroke-linecap="round"
          class="transition-all duration-700 ease-out"
        />

        <!-- Tick Marks -->
        <g v-if="showTicks">
          <g v-for="(tick, i) in ticks" :key="i">
            <line
              :x1="tick.x1"
              :y1="tick.y1"
              :x2="tick.x2"
              :y2="tick.y2"
              :stroke="themeClasses.tickColor"
              :stroke-width="tick.major ? 2 : 1"
            />
            <text
              v-if="tick.major && showTickLabels"
              :x="tick.labelX"
              :y="tick.labelY"
              :class="['text-xs', themeClasses.tickLabel]"
              text-anchor="middle"
              dominant-baseline="middle"
              :fill="themeClasses.tickLabelColor"
            >
              {{ tick.value }}
            </text>
          </g>
        </g>

        <!-- Needle -->
        <g v-if="showNeedle" :transform="`rotate(${needleAngle}, ${centerX}, ${centerY})`">
          <polygon
            :points="needlePoints"
            :fill="needleColor"
            class="drop-shadow-md transition-transform duration-700 ease-out"
          />
          <circle
            :cx="centerX"
            :cy="centerY"
            :r="strokeWidth / 2"
            :fill="needleColor"
            class="drop-shadow-sm"
          />
        </g>

        <!-- Center Circle -->
        <circle
          v-if="showCenter"
          :cx="centerX"
          :cy="centerY"
          :r="innerRadius * 0.15"
          :fill="themeClasses.centerColor"
          class="drop-shadow-sm"
        />
      </svg>

      <!-- Center Value Display -->
      <div
        class="absolute inset-0 flex flex-col items-center justify-end pb-2"
        :style="{ paddingBottom: `${size * 0.05}px` }"
      >
        <div class="text-center">
          <span
            :class="['font-bold tabular-nums', themeClasses.value]"
            :style="{ fontSize: `${size * 0.15}px` }"
          >
            {{ formattedValue }}
          </span>
          <span
            v-if="unit"
            :class="['ml-1', themeClasses.unit]"
            :style="{ fontSize: `${size * 0.07}px` }"
          >
            {{ unit }}
          </span>
        </div>
        <div
          v-if="subtitle"
          :class="['mt-1', themeClasses.subtitle]"
          :style="{ fontSize: `${size * 0.055}px` }"
        >
          {{ subtitle }}
        </div>
      </div>
    </div>

    <!-- Segments Legend -->
    <div v-if="showLegend && segments.length" class="flex justify-center gap-4 mt-4">
      <div
        v-for="(seg, i) in segments"
        :key="i"
        class="flex items-center gap-1.5"
      >
        <div
          class="w-3 h-3 rounded-full"
          :style="{ backgroundColor: seg.color }"
        />
        <span :class="['text-xs', themeClasses.legend]">{{ seg.label }}</span>
      </div>
    </div>

    <!-- Min/Max Labels -->
    <div v-if="showMinMax" class="flex justify-between px-2 mt-2">
      <span :class="['text-xs', themeClasses.minMax]">{{ formatNumber(min) }}</span>
      <span :class="['text-xs', themeClasses.minMax]">{{ formatNumber(max) }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  value: {
    type: Number,
    default: 0
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: 100
  },
  size: {
    type: Number,
    default: 200
  },
  strokeWidth: {
    type: Number,
    default: 20
  },
  label: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  unit: {
    type: String,
    default: ''
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  color: {
    type: String,
    default: '#3B82F6'
  },
  segments: {
    type: Array,
    default: () => []
    // [{ from: 0, to: 30, color: '#22C55E', label: 'Low' }, ...]
  },
  useGradient: {
    type: Boolean,
    default: false
  },
  gradientColors: {
    type: Array,
    default: () => ['#22C55E', '#EAB308', '#EF4444']
  },
  showTicks: {
    type: Boolean,
    default: true
  },
  showTickLabels: {
    type: Boolean,
    default: true
  },
  tickCount: {
    type: Number,
    default: 5
  },
  showNeedle: {
    type: Boolean,
    default: true
  },
  needleColor: {
    type: String,
    default: '#1F2937'
  },
  showCenter: {
    type: Boolean,
    default: true
  },
  showLegend: {
    type: Boolean,
    default: false
  },
  showMinMax: {
    type: Boolean,
    default: false
  },
  decimalPlaces: {
    type: Number,
    default: 0
  },
  animated: {
    type: Boolean,
    default: true
  }
})

const uid = ref(Math.random().toString(36).substr(2, 9))

const startAngle = -180
const endAngle = 0
const angleRange = endAngle - startAngle

const centerX = computed(() => props.size / 2)
const centerY = computed(() => props.size * 0.55)
const radius = computed(() => (props.size - props.strokeWidth) / 2)
const innerRadius = computed(() => radius.value - props.strokeWidth / 2)

const normalizedValue = computed(() => {
  const clamped = Math.max(props.min, Math.min(props.max, props.value))
  return (clamped - props.min) / (props.max - props.min)
})

const currentAngle = computed(() => {
  return startAngle + normalizedValue.value * angleRange
})

const needleAngle = computed(() => {
  return startAngle + 90 + normalizedValue.value * angleRange
})

const polarToCartesian = (angle) => {
  const radian = (angle * Math.PI) / 180
  return {
    x: centerX.value + radius.value * Math.cos(radian),
    y: centerY.value + radius.value * Math.sin(radian)
  }
}

const describeArc = (startAng, endAng) => {
  const start = polarToCartesian(endAng)
  const end = polarToCartesian(startAng)
  const largeArcFlag = endAng - startAng <= 180 ? 0 : 1

  return [
    'M', start.x, start.y,
    'A', radius.value, radius.value, 0, largeArcFlag, 0, end.x, end.y
  ].join(' ')
}

const backgroundArc = computed(() => describeArc(startAngle, endAngle))

const valueArc = computed(() => {
  if (normalizedValue.value === 0) return ''
  return describeArc(startAngle, currentAngle.value)
})

const currentColor = computed(() => {
  if (props.segments.length) {
    for (const seg of props.segments) {
      if (props.value >= seg.from && props.value <= seg.to) {
        return seg.color
      }
    }
  }
  return props.color
})

const gradientStops = computed(() => {
  return props.gradientColors.map((color, i) => ({
    offset: `${(i / (props.gradientColors.length - 1)) * 100}%`,
    color
  }))
})

const needlePoints = computed(() => {
  const length = radius.value * 0.75
  const width = props.strokeWidth / 3
  return [
    `${centerX.value},${centerY.value - length}`,
    `${centerX.value - width},${centerY.value}`,
    `${centerX.value + width},${centerY.value}`
  ].join(' ')
})

const ticks = computed(() => {
  const result = []
  const tickLength = props.strokeWidth * 0.6
  const majorTickLength = props.strokeWidth * 0.9

  for (let i = 0; i <= props.tickCount; i++) {
    const isMajor = i === 0 || i === props.tickCount || i === Math.floor(props.tickCount / 2)
    const angle = startAngle + (i / props.tickCount) * angleRange
    const radian = (angle * Math.PI) / 180
    
    const outerR = radius.value + props.strokeWidth / 2 + 5
    const innerR = outerR - (isMajor ? majorTickLength : tickLength)
    const labelR = outerR + 12

    result.push({
      major: isMajor,
      x1: centerX.value + outerR * Math.cos(radian),
      y1: centerY.value + outerR * Math.sin(radian),
      x2: centerX.value + innerR * Math.cos(radian),
      y2: centerY.value + innerR * Math.sin(radian),
      labelX: centerX.value + labelR * Math.cos(radian),
      labelY: centerY.value + labelR * Math.sin(radian),
      value: Math.round(props.min + (i / props.tickCount) * (props.max - props.min))
    })
  }
  return result
})

const formattedValue = computed(() => {
  return formatNumber(props.value)
})

const formatNumber = (num) => {
  return num.toLocaleString('en-US', {
    minimumFractionDigits: props.decimalPlaces,
    maximumFractionDigits: props.decimalPlaces
  })
}

const containerClasses = computed(() => {
  return 'inline-flex flex-col items-center'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      label: 'text-gray-300',
      trackColor: '#374151',
      tickColor: '#4B5563',
      tickLabel: 'fill-gray-400',
      tickLabelColor: '#9CA3AF',
      centerColor: '#1F2937',
      value: 'text-white',
      unit: 'text-gray-400',
      subtitle: 'text-gray-500',
      legend: 'text-gray-400',
      minMax: 'text-gray-500'
    }
  }
  return {
    label: 'text-gray-700',
    trackColor: '#E5E7EB',
    tickColor: '#D1D5DB',
    tickLabel: 'fill-gray-500',
    tickLabelColor: '#6B7280',
    centerColor: '#F3F4F6',
    value: 'text-gray-900',
    unit: 'text-gray-500',
    subtitle: 'text-gray-500',
    legend: 'text-gray-600',
    minMax: 'text-gray-500'
  }
})
</script>
