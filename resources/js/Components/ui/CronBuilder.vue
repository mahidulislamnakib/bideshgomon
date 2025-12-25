<template>
  <div :class="['cron-builder', containerClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between mb-4">
      <h3 :class="['text-lg font-semibold', themeClasses.title]">Cron Expression Builder</h3>
      <div class="flex items-center gap-2">
        <button
          @click="resetToDefault"
          :class="['px-3 py-1.5 text-sm rounded-lg transition-colors', themeClasses.resetBtn]"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Expression Display -->
    <div :class="['p-4 rounded-lg mb-4 font-mono', themeClasses.expressionBg]">
      <div class="flex items-center justify-between">
        <div>
          <span :class="['text-xs uppercase tracking-wide', themeClasses.label]">Expression</span>
          <div :class="['text-xl font-bold mt-1', themeClasses.expression]">
            {{ cronExpression }}
          </div>
        </div>
        <button
          @click="copyExpression"
          :class="['p-2 rounded-lg transition-colors', themeClasses.copyBtn]"
          title="Copy expression"
        >
          <ClipboardDocumentIcon v-if="!copied" class="w-5 h-5" />
          <CheckIcon v-else class="w-5 h-5 text-green-500" />
        </button>
      </div>
      <div :class="['mt-2 text-sm', themeClasses.description]">
        {{ humanReadable }}
      </div>
    </div>

    <!-- Quick Presets -->
    <div v-if="showPresets" class="mb-6">
      <span :class="['text-xs uppercase tracking-wide', themeClasses.label]">Quick Presets</span>
      <div class="flex flex-wrap gap-2 mt-2">
        <button
          v-for="preset in presets"
          :key="preset.label"
          @click="applyPreset(preset)"
          :class="[
            'px-3 py-1.5 text-sm rounded-lg transition-colors',
            cronExpression === preset.value ? themeClasses.presetActive : themeClasses.preset
          ]"
        >
          {{ preset.label }}
        </button>
      </div>
    </div>

    <!-- Field Editors -->
    <div class="space-y-4">
      <!-- Minute -->
      <CronField
        v-model="minute"
        label="Minute"
        :min="0"
        :max="59"
        :theme="theme"
        :options="minuteOptions"
      />

      <!-- Hour -->
      <CronField
        v-model="hour"
        label="Hour"
        :min="0"
        :max="23"
        :theme="theme"
        :options="hourOptions"
      />

      <!-- Day of Month -->
      <CronField
        v-model="dayOfMonth"
        label="Day of Month"
        :min="1"
        :max="31"
        :theme="theme"
        :options="dayOfMonthOptions"
      />

      <!-- Month -->
      <CronField
        v-model="month"
        label="Month"
        :min="1"
        :max="12"
        :theme="theme"
        :options="monthOptions"
        :names="monthNames"
      />

      <!-- Day of Week -->
      <CronField
        v-model="dayOfWeek"
        label="Day of Week"
        :min="0"
        :max="6"
        :theme="theme"
        :options="dayOfWeekOptions"
        :names="dayNames"
      />
    </div>

    <!-- Next Run Times -->
    <div v-if="showNextRuns" class="mt-6">
      <span :class="['text-xs uppercase tracking-wide', themeClasses.label]">Next {{ nextRunCount }} Runs</span>
      <div :class="['mt-2 space-y-1', themeClasses.nextRuns]">
        <div
          v-for="(run, i) in nextRuns"
          :key="i"
          :class="['text-sm py-1 px-2 rounded', themeClasses.nextRunItem]"
        >
          <CalendarIcon class="w-4 h-4 inline-block mr-2 opacity-50" />
          {{ run }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, h } from 'vue'
import {
  ClipboardDocumentIcon,
  CheckIcon,
  CalendarIcon
} from '@heroicons/vue/24/outline'

// CronField subcomponent
const CronField = {
  props: ['modelValue', 'label', 'min', 'max', 'theme', 'options', 'names'],
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const localType = ref('every')
    const specificValues = ref([])
    const rangeStart = ref(props.min)
    const rangeEnd = ref(props.max)
    const stepValue = ref(1)

    const parseValue = () => {
      const val = props.modelValue
      if (val === '*') {
        localType.value = 'every'
      } else if (val.includes('/')) {
        localType.value = 'step'
        stepValue.value = parseInt(val.split('/')[1]) || 1
      } else if (val.includes('-')) {
        localType.value = 'range'
        const [start, end] = val.split('-')
        rangeStart.value = parseInt(start)
        rangeEnd.value = parseInt(end)
      } else if (val.includes(',')) {
        localType.value = 'specific'
        specificValues.value = val.split(',').map(Number)
      } else if (!isNaN(parseInt(val))) {
        localType.value = 'specific'
        specificValues.value = [parseInt(val)]
      }
    }

    parseValue()

    const updateValue = () => {
      let value = '*'
      switch (localType.value) {
        case 'every':
          value = '*'
          break
        case 'step':
          value = `*/${stepValue.value}`
          break
        case 'range':
          value = `${rangeStart.value}-${rangeEnd.value}`
          break
        case 'specific':
          value = specificValues.value.length ? specificValues.value.sort((a, b) => a - b).join(',') : '*'
          break
      }
      emit('update:modelValue', value)
    }

    const toggleSpecific = (val) => {
      const idx = specificValues.value.indexOf(val)
      if (idx > -1) {
        specificValues.value.splice(idx, 1)
      } else {
        specificValues.value.push(val)
      }
      updateValue()
    }

    const themeClasses = computed(() => {
      if (props.theme === 'dark') {
        return {
          container: 'bg-gray-800 border-gray-700',
          label: 'text-gray-300',
          select: 'bg-gray-700 border-gray-600 text-white',
          input: 'bg-gray-700 border-gray-600 text-white',
          chip: 'bg-gray-700 text-gray-300 hover:bg-gray-600',
          chipActive: 'bg-blue-600 text-white'
        }
      }
      return {
        container: 'bg-gray-50 border-gray-200',
        label: 'text-gray-700',
        select: 'bg-white border-gray-300 text-gray-900',
        input: 'bg-white border-gray-300 text-gray-900',
        chip: 'bg-gray-200 text-gray-700 hover:bg-gray-300',
        chipActive: 'bg-blue-500 text-white'
      }
    })

    const allValues = computed(() => {
      const vals = []
      for (let i = props.min; i <= props.max; i++) {
        vals.push({ value: i, label: props.names?.[i] || i.toString().padStart(2, '0') })
      }
      return vals
    })

    watch(localType, updateValue)
    watch(stepValue, updateValue)
    watch(rangeStart, updateValue)
    watch(rangeEnd, updateValue)

    return () => h('div', { class: ['p-3 rounded-lg border', themeClasses.value.container] }, [
      h('div', { class: 'flex items-center justify-between mb-2' }, [
        h('span', { class: ['text-sm font-medium', themeClasses.value.label] }, props.label),
        h('select', {
          value: localType.value,
          onChange: (e) => { localType.value = e.target.value },
          class: ['text-sm px-2 py-1 rounded border', themeClasses.value.select]
        }, [
          h('option', { value: 'every' }, 'Every'),
          h('option', { value: 'specific' }, 'Specific'),
          h('option', { value: 'range' }, 'Range'),
          h('option', { value: 'step' }, 'Step')
        ])
      ]),
      
      // Step input
      localType.value === 'step' && h('div', { class: 'flex items-center gap-2' }, [
        h('span', { class: ['text-sm', themeClasses.value.label] }, 'Every'),
        h('input', {
          type: 'number',
          value: stepValue.value,
          onInput: (e) => { stepValue.value = parseInt(e.target.value) || 1 },
          min: 1,
          max: props.max,
          class: ['w-16 px-2 py-1 text-sm rounded border', themeClasses.value.input]
        }),
        h('span', { class: ['text-sm', themeClasses.value.label] }, props.label.toLowerCase())
      ]),

      // Range inputs
      localType.value === 'range' && h('div', { class: 'flex items-center gap-2' }, [
        h('span', { class: ['text-sm', themeClasses.value.label] }, 'From'),
        h('input', {
          type: 'number',
          value: rangeStart.value,
          onInput: (e) => { rangeStart.value = parseInt(e.target.value) },
          min: props.min,
          max: props.max,
          class: ['w-16 px-2 py-1 text-sm rounded border', themeClasses.value.input]
        }),
        h('span', { class: ['text-sm', themeClasses.value.label] }, 'to'),
        h('input', {
          type: 'number',
          value: rangeEnd.value,
          onInput: (e) => { rangeEnd.value = parseInt(e.target.value) },
          min: props.min,
          max: props.max,
          class: ['w-16 px-2 py-1 text-sm rounded border', themeClasses.value.input]
        })
      ]),

      // Specific values
      localType.value === 'specific' && h('div', { class: 'flex flex-wrap gap-1 mt-2' }, 
        allValues.value.map(v => 
          h('button', {
            key: v.value,
            onClick: () => toggleSpecific(v.value),
            class: [
              'px-2 py-1 text-xs rounded transition-colors',
              specificValues.value.includes(v.value) ? themeClasses.value.chipActive : themeClasses.value.chip
            ]
          }, v.label)
        )
      )
    ])
  }
}

const props = defineProps({
  modelValue: {
    type: String,
    default: '* * * * *'
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
  showPresets: {
    type: Boolean,
    default: true
  },
  showNextRuns: {
    type: Boolean,
    default: true
  },
  nextRunCount: {
    type: Number,
    default: 5
  }
})

const emit = defineEmits(['update:modelValue'])

const minute = ref('*')
const hour = ref('*')
const dayOfMonth = ref('*')
const month = ref('*')
const dayOfWeek = ref('*')
const copied = ref(false)

const monthNames = {
  1: 'Jan', 2: 'Feb', 3: 'Mar', 4: 'Apr', 5: 'May', 6: 'Jun',
  7: 'Jul', 8: 'Aug', 9: 'Sep', 10: 'Oct', 11: 'Nov', 12: 'Dec'
}

const dayNames = {
  0: 'Sun', 1: 'Mon', 2: 'Tue', 3: 'Wed', 4: 'Thu', 5: 'Fri', 6: 'Sat'
}

const minuteOptions = []
const hourOptions = []
const dayOfMonthOptions = []
const monthOptions = []
const dayOfWeekOptions = []

const presets = [
  { label: 'Every minute', value: '* * * * *' },
  { label: 'Every hour', value: '0 * * * *' },
  { label: 'Every day at midnight', value: '0 0 * * *' },
  { label: 'Every day at noon', value: '0 12 * * *' },
  { label: 'Every Monday', value: '0 0 * * 1' },
  { label: 'Every weekday', value: '0 0 * * 1-5' },
  { label: 'Every month', value: '0 0 1 * *' },
  { label: 'Every year', value: '0 0 1 1 *' }
]

const cronExpression = computed(() => {
  return `${minute.value} ${hour.value} ${dayOfMonth.value} ${month.value} ${dayOfWeek.value}`
})

const humanReadable = computed(() => {
  const parts = []
  
  // Minute
  if (minute.value === '*') {
    parts.push('every minute')
  } else if (minute.value.includes('/')) {
    parts.push(`every ${minute.value.split('/')[1]} minutes`)
  } else if (minute.value.includes('-')) {
    parts.push(`minutes ${minute.value}`)
  } else {
    parts.push(`at minute ${minute.value}`)
  }

  // Hour
  if (hour.value !== '*') {
    if (hour.value.includes('/')) {
      parts.push(`every ${hour.value.split('/')[1]} hours`)
    } else if (hour.value.includes('-')) {
      parts.push(`hours ${hour.value}`)
    } else {
      parts.push(`at ${hour.value}:00`)
    }
  }

  // Day of month
  if (dayOfMonth.value !== '*') {
    parts.push(`on day ${dayOfMonth.value}`)
  }

  // Month
  if (month.value !== '*') {
    if (month.value.includes('-')) {
      const [start, end] = month.value.split('-')
      parts.push(`in ${monthNames[start]} to ${monthNames[end]}`)
    } else {
      const months = month.value.split(',').map(m => monthNames[parseInt(m)]).join(', ')
      parts.push(`in ${months}`)
    }
  }

  // Day of week
  if (dayOfWeek.value !== '*') {
    if (dayOfWeek.value.includes('-')) {
      const [start, end] = dayOfWeek.value.split('-')
      parts.push(`on ${dayNames[start]} to ${dayNames[end]}`)
    } else {
      const days = dayOfWeek.value.split(',').map(d => dayNames[parseInt(d)]).join(', ')
      parts.push(`on ${days}`)
    }
  }

  return parts.join(', ')
})

const nextRuns = computed(() => {
  const runs = []
  const now = new Date()
  let current = new Date(now)
  
  for (let i = 0; i < props.nextRunCount && runs.length < props.nextRunCount; i++) {
    current = getNextRun(current)
    if (current) {
      runs.push(current.toLocaleString())
      current = new Date(current.getTime() + 60000) // Add 1 minute
    } else {
      break
    }
    if (i > 1000) break // Safety limit
  }
  
  return runs
})

const getNextRun = (from) => {
  // Simplified next run calculation
  const date = new Date(from)
  date.setSeconds(0)
  date.setMilliseconds(0)
  
  for (let i = 0; i < 525600; i++) { // Max 1 year of minutes
    date.setMinutes(date.getMinutes() + 1)
    
    if (matchesCron(date)) {
      return date
    }
  }
  return null
}

const matchesCron = (date) => {
  return matchesField(minute.value, date.getMinutes(), 0, 59) &&
         matchesField(hour.value, date.getHours(), 0, 23) &&
         matchesField(dayOfMonth.value, date.getDate(), 1, 31) &&
         matchesField(month.value, date.getMonth() + 1, 1, 12) &&
         matchesField(dayOfWeek.value, date.getDay(), 0, 6)
}

const matchesField = (field, value, min, max) => {
  if (field === '*') return true
  
  if (field.includes('/')) {
    const step = parseInt(field.split('/')[1])
    return value % step === 0
  }
  
  if (field.includes('-')) {
    const [start, end] = field.split('-').map(Number)
    return value >= start && value <= end
  }
  
  if (field.includes(',')) {
    return field.split(',').map(Number).includes(value)
  }
  
  return parseInt(field) === value
}

const applyPreset = (preset) => {
  const [m, h, dom, mon, dow] = preset.value.split(' ')
  minute.value = m
  hour.value = h
  dayOfMonth.value = dom
  month.value = mon
  dayOfWeek.value = dow
}

const resetToDefault = () => {
  minute.value = '*'
  hour.value = '*'
  dayOfMonth.value = '*'
  month.value = '*'
  dayOfWeek.value = '*'
}

const copyExpression = async () => {
  await navigator.clipboard.writeText(cronExpression.value)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

// Parse initial value
const parseExpression = () => {
  const parts = props.modelValue.split(' ')
  if (parts.length >= 5) {
    minute.value = parts[0]
    hour.value = parts[1]
    dayOfMonth.value = parts[2]
    month.value = parts[3]
    dayOfWeek.value = parts[4]
  }
}

parseExpression()

watch(cronExpression, (val) => {
  emit('update:modelValue', val)
})

watch(() => props.modelValue, parseExpression)

const containerClasses = computed(() => {
  return props.theme === 'dark' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      title: 'text-white',
      label: 'text-gray-400',
      expressionBg: 'bg-gray-800',
      expression: 'text-blue-400',
      description: 'text-gray-400',
      copyBtn: 'text-gray-400 hover:bg-gray-700',
      resetBtn: 'text-gray-400 hover:bg-gray-700',
      preset: 'bg-gray-800 text-gray-300 hover:bg-gray-700',
      presetActive: 'bg-blue-600 text-white',
      nextRuns: 'text-gray-300',
      nextRunItem: 'bg-gray-800'
    }
  }
  return {
    title: 'text-gray-900',
    label: 'text-gray-500',
    expressionBg: 'bg-gray-100',
    expression: 'text-blue-600',
    description: 'text-gray-600',
    copyBtn: 'text-gray-500 hover:bg-gray-200',
    resetBtn: 'text-gray-600 hover:bg-gray-100',
    preset: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    presetActive: 'bg-blue-500 text-white',
    nextRuns: 'text-gray-700',
    nextRunItem: 'bg-gray-50'
  }
})

defineExpose({
  getExpression: () => cronExpression.value,
  setExpression: (expr) => {
    const parts = expr.split(' ')
    if (parts.length >= 5) {
      minute.value = parts[0]
      hour.value = parts[1]
      dayOfMonth.value = parts[2]
      month.value = parts[3]
      dayOfWeek.value = parts[4]
    }
  }
})
</script>
