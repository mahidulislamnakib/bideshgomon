<template>
  <div :class="['inline-flex', containerClasses]">
    <!-- Days -->
    <div v-if="showDays && days > 0" class="flex flex-col items-center">
      <div :class="digitClasses">
        <span :class="numberClasses">{{ formatNumber(days) }}</span>
      </div>
      <span v-if="showLabels" :class="labelClasses">{{ daysLabel }}</span>
    </div>

    <span v-if="showDays && days > 0" :class="separatorClasses">{{ separator }}</span>

    <!-- Hours -->
    <div v-if="showHours" class="flex flex-col items-center">
      <div :class="digitClasses">
        <span :class="numberClasses">{{ formatNumber(hours) }}</span>
      </div>
      <span v-if="showLabels" :class="labelClasses">{{ hoursLabel }}</span>
    </div>

    <span v-if="showHours && showMinutes" :class="separatorClasses">{{ separator }}</span>

    <!-- Minutes -->
    <div v-if="showMinutes" class="flex flex-col items-center">
      <div :class="digitClasses">
        <span :class="numberClasses">{{ formatNumber(minutes) }}</span>
      </div>
      <span v-if="showLabels" :class="labelClasses">{{ minutesLabel }}</span>
    </div>

    <span v-if="showMinutes && showSeconds" :class="separatorClasses">{{ separator }}</span>

    <!-- Seconds -->
    <div v-if="showSeconds" class="flex flex-col items-center">
      <div :class="digitClasses">
        <span
          :class="[numberClasses, animate ? 'animate-pulse-once' : '']"
          :key="seconds"
        >
          {{ formatNumber(seconds) }}
        </span>
      </div>
      <span v-if="showLabels" :class="labelClasses">{{ secondsLabel }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // Target date/time
  targetDate: {
    type: [Date, String, Number],
    required: true
  },
  // Count direction
  direction: {
    type: String,
    default: 'down',
    validator: (v) => ['down', 'up'].includes(v)
  },
  // Display options
  showDays: {
    type: Boolean,
    default: true
  },
  showHours: {
    type: Boolean,
    default: true
  },
  showMinutes: {
    type: Boolean,
    default: true
  },
  showSeconds: {
    type: Boolean,
    default: true
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  // Labels
  daysLabel: {
    type: String,
    default: 'Days'
  },
  hoursLabel: {
    type: String,
    default: 'Hours'
  },
  minutesLabel: {
    type: String,
    default: 'Minutes'
  },
  secondsLabel: {
    type: String,
    default: 'Seconds'
  },
  // Separator
  separator: {
    type: String,
    default: ':'
  },
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v)
  },
  // Variant
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'boxed', 'minimal', 'gradient'].includes(v)
  },
  // Color
  color: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'success', 'warning', 'danger', 'gray'].includes(v)
  },
  // Animate seconds
  animate: {
    type: Boolean,
    default: false
  },
  // Auto-start
  autoStart: {
    type: Boolean,
    default: true
  },
  // Pad numbers with zeros
  padNumbers: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['complete', 'tick'])

// State
const days = ref(0)
const hours = ref(0)
const minutes = ref(0)
const seconds = ref(0)
const isComplete = ref(false)
const isRunning = ref(false)
let intervalId = null

// Computed target timestamp
const targetTimestamp = computed(() => {
  if (props.targetDate instanceof Date) {
    return props.targetDate.getTime()
  }
  if (typeof props.targetDate === 'number') {
    return props.targetDate
  }
  return new Date(props.targetDate).getTime()
})

// Container classes
const containerClasses = computed(() => {
  const gaps = {
    sm: 'gap-2',
    md: 'gap-3',
    lg: 'gap-4',
    xl: 'gap-6'
  }
  return gaps[props.size]
})

// Digit container classes
const digitClasses = computed(() => {
  const base = 'flex items-center justify-center font-mono'
  
  const sizes = {
    sm: 'min-w-[2rem] h-8',
    md: 'min-w-[3rem] h-12',
    lg: 'min-w-[4rem] h-16',
    xl: 'min-w-[5rem] h-20'
  }

  const variants = {
    default: '',
    boxed: 'rounded-lg bg-gray-100 dark:bg-gray-800 shadow-inner',
    minimal: '',
    gradient: `rounded-lg bg-gradient-to-br ${colorGradients.value} text-white shadow-lg`
  }

  return `${base} ${sizes[props.size]} ${variants[props.variant]}`
})

// Number classes
const numberClasses = computed(() => {
  const sizes = {
    sm: 'text-lg',
    md: 'text-2xl',
    lg: 'text-4xl',
    xl: 'text-5xl'
  }

  const colors = {
    primary: 'text-primary-600 dark:text-primary-400',
    success: 'text-green-600 dark:text-green-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    danger: 'text-red-600 dark:text-red-400',
    gray: 'text-gray-700 dark:text-gray-300'
  }

  const colorClass = props.variant === 'gradient' ? 'text-white' : colors[props.color]

  return `font-bold tabular-nums ${sizes[props.size]} ${colorClass}`
})

// Label classes
const labelClasses = computed(() => {
  const sizes = {
    sm: 'text-[10px] mt-0.5',
    md: 'text-xs mt-1',
    lg: 'text-sm mt-1.5',
    xl: 'text-base mt-2'
  }
  return `uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium ${sizes[props.size]}`
})

// Separator classes
const separatorClasses = computed(() => {
  const sizes = {
    sm: 'text-lg',
    md: 'text-2xl',
    lg: 'text-4xl',
    xl: 'text-5xl'
  }
  return `font-bold text-gray-400 dark:text-gray-500 self-start mt-0 ${sizes[props.size]}`
})

// Color gradients for gradient variant
const colorGradients = computed(() => {
  const gradients = {
    primary: 'from-primary-500 to-primary-700',
    success: 'from-green-500 to-green-700',
    warning: 'from-yellow-500 to-orange-500',
    danger: 'from-red-500 to-red-700',
    gray: 'from-gray-600 to-gray-800'
  }
  return gradients[props.color]
})

// Format number with padding
function formatNumber(num) {
  if (props.padNumbers) {
    return String(num).padStart(2, '0')
  }
  return String(num)
}

// Calculate time difference
function calculate() {
  const now = Date.now()
  let diff = props.direction === 'down'
    ? targetTimestamp.value - now
    : now - targetTimestamp.value

  if (diff <= 0 && props.direction === 'down') {
    diff = 0
    isComplete.value = true
    stop()
    emit('complete')
  }

  const totalSeconds = Math.floor(Math.abs(diff) / 1000)
  
  days.value = Math.floor(totalSeconds / 86400)
  hours.value = Math.floor((totalSeconds % 86400) / 3600)
  minutes.value = Math.floor((totalSeconds % 3600) / 60)
  seconds.value = totalSeconds % 60

  emit('tick', {
    days: days.value,
    hours: hours.value,
    minutes: minutes.value,
    seconds: seconds.value,
    totalSeconds
  })
}

// Start countdown
function start() {
  if (isRunning.value) return
  
  isRunning.value = true
  calculate()
  intervalId = setInterval(calculate, 1000)
}

// Stop countdown
function stop() {
  isRunning.value = false
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

// Reset countdown
function reset() {
  stop()
  isComplete.value = false
  calculate()
}

// Watch for target date changes
watch(() => props.targetDate, () => {
  reset()
  if (props.autoStart) start()
})

// Lifecycle
onMounted(() => {
  if (props.autoStart) {
    start()
  } else {
    calculate()
  }
})

onUnmounted(() => {
  stop()
})

// Expose methods
defineExpose({
  start,
  stop,
  reset
})
</script>

<style scoped>
@keyframes pulse-once {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.animate-pulse-once {
  animation: pulse-once 0.3s ease-in-out;
}
</style>
