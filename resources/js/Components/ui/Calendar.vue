<template>
  <div class="w-full" :class="containerClasses">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold" :class="headerTextClasses">
        {{ monthYear }}
      </h2>
      <div class="flex items-center gap-1">
        <button
          type="button"
          class="p-2 rounded-lg transition-colors"
          :class="navButtonClasses"
          @click="previousMonth"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <button
          type="button"
          class="px-3 py-1 text-sm font-medium rounded-lg transition-colors"
          :class="todayButtonClasses"
          @click="goToToday"
        >
          Today
        </button>
        <button
          type="button"
          class="p-2 rounded-lg transition-colors"
          :class="navButtonClasses"
          @click="nextMonth"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Day headers -->
    <div class="grid grid-cols-7 mb-2">
      <div
        v-for="day in dayNames"
        :key="day"
        class="py-2 text-center text-xs font-medium uppercase"
        :class="dayHeaderClasses"
      >
        {{ day }}
      </div>
    </div>

    <!-- Calendar grid -->
    <div class="grid grid-cols-7 gap-px rounded-lg overflow-hidden" :class="gridBgClasses">
      <button
        v-for="(day, index) in calendarDays"
        :key="index"
        type="button"
        class="relative py-3 px-2 text-sm font-medium transition-colors focus:z-10 focus:outline-none focus:ring-2 focus:ring-inset"
        :class="getDayClasses(day)"
        :disabled="day.disabled"
        @click="selectDate(day)"
      >
        <time
          :datetime="day.date"
          class="mx-auto flex h-7 w-7 items-center justify-center rounded-full"
          :class="getDayInnerClasses(day)"
        >
          {{ day.dayNumber }}
        </time>
        
        <!-- Event indicators -->
        <div v-if="hasEvents(day)" class="absolute bottom-1 left-1/2 -translate-x-1/2 flex gap-0.5">
          <span
            v-for="(event, eventIndex) in getEventDots(day)"
            :key="eventIndex"
            class="w-1 h-1 rounded-full"
            :class="event.color || 'bg-primary-500'"
          />
        </div>
      </button>
    </div>

    <!-- Events list for selected date -->
    <div v-if="showEvents && selectedDateEvents.length > 0" class="mt-4">
      <h3 class="text-sm font-medium mb-2" :class="headerTextClasses">
        Events for {{ formatDate(selectedDate) }}
      </h3>
      <div class="space-y-2">
        <div
          v-for="event in selectedDateEvents"
          :key="event.id"
          class="p-3 rounded-lg"
          :class="eventCardClasses"
        >
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" :class="event.color || 'bg-primary-500'" />
            <span class="font-medium">{{ event.title }}</span>
          </div>
          <p v-if="event.description" class="mt-1 text-sm" :class="eventDescClasses">
            {{ event.description }}
          </p>
          <p v-if="event.time" class="mt-1 text-xs" :class="eventTimeClasses">
            {{ event.time }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: [Date, String],
    default: null
  },
  events: {
    type: Array,
    default: () => []
  },
  minDate: {
    type: [Date, String],
    default: null
  },
  maxDate: {
    type: [Date, String],
    default: null
  },
  disabledDates: {
    type: Array,
    default: () => []
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'flat'].includes(value)
  },
  showEvents: {
    type: Boolean,
    default: true
  },
  weekStartsOn: {
    type: Number,
    default: 0, // 0 = Sunday, 1 = Monday
    validator: (value) => value >= 0 && value <= 6
  }
})

const emit = defineEmits(['update:modelValue', 'month-change', 'date-click'])

// Current view month/year
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const selectedDate = ref(props.modelValue ? new Date(props.modelValue) : new Date())

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    selectedDate.value = new Date(newVal)
    currentMonth.value = selectedDate.value.getMonth()
    currentYear.value = selectedDate.value.getFullYear()
  }
})

// Day names based on week start
const dayNames = computed(() => {
  const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
  const rotated = [...days.slice(props.weekStartsOn), ...days.slice(0, props.weekStartsOn)]
  return rotated
})

// Month/Year display
const monthYear = computed(() => {
  const date = new Date(currentYear.value, currentMonth.value)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

// Generate calendar days
const calendarDays = computed(() => {
  const days = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  
  // Adjust for week start
  let startPadding = firstDay.getDay() - props.weekStartsOn
  if (startPadding < 0) startPadding += 7
  
  // Previous month days
  const prevMonth = new Date(currentYear.value, currentMonth.value, 0)
  for (let i = startPadding - 1; i >= 0; i--) {
    const dayNum = prevMonth.getDate() - i
    const date = new Date(currentYear.value, currentMonth.value - 1, dayNum)
    days.push({
      dayNumber: dayNum,
      date: formatDateISO(date),
      isCurrentMonth: false,
      isToday: isToday(date),
      isSelected: isSameDay(date, selectedDate.value),
      disabled: isDisabled(date)
    })
  }
  
  // Current month days
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const date = new Date(currentYear.value, currentMonth.value, i)
    days.push({
      dayNumber: i,
      date: formatDateISO(date),
      isCurrentMonth: true,
      isToday: isToday(date),
      isSelected: isSameDay(date, selectedDate.value),
      disabled: isDisabled(date)
    })
  }
  
  // Next month days to fill grid
  const remaining = 42 - days.length // 6 rows * 7 days
  for (let i = 1; i <= remaining; i++) {
    const date = new Date(currentYear.value, currentMonth.value + 1, i)
    days.push({
      dayNumber: i,
      date: formatDateISO(date),
      isCurrentMonth: false,
      isToday: isToday(date),
      isSelected: isSameDay(date, selectedDate.value),
      disabled: isDisabled(date)
    })
  }
  
  return days
})

// Events for selected date
const selectedDateEvents = computed(() => {
  if (!selectedDate.value) return []
  const dateStr = formatDateISO(selectedDate.value)
  return props.events.filter(e => e.date === dateStr)
})

// Navigation
function previousMonth() {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
  emit('month-change', { month: currentMonth.value, year: currentYear.value })
}

function nextMonth() {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
  emit('month-change', { month: currentMonth.value, year: currentYear.value })
}

function goToToday() {
  const today = new Date()
  currentMonth.value = today.getMonth()
  currentYear.value = today.getFullYear()
  selectDate({ date: formatDateISO(today), disabled: false })
}

function selectDate(day) {
  if (day.disabled) return
  selectedDate.value = new Date(day.date)
  emit('update:modelValue', day.date)
  emit('date-click', { date: day.date, events: props.events.filter(e => e.date === day.date) })
}

// Helpers
function formatDateISO(date) {
  return date.toISOString().split('T')[0]
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'long', 
    day: 'numeric' 
  })
}

function isToday(date) {
  const today = new Date()
  return isSameDay(date, today)
}

function isSameDay(date1, date2) {
  if (!date1 || !date2) return false
  return formatDateISO(new Date(date1)) === formatDateISO(new Date(date2))
}

function isDisabled(date) {
  const dateStr = formatDateISO(date)
  
  if (props.disabledDates.includes(dateStr)) return true
  
  if (props.minDate && date < new Date(props.minDate)) return true
  if (props.maxDate && date > new Date(props.maxDate)) return true
  
  return false
}

function hasEvents(day) {
  return props.events.some(e => e.date === day.date)
}

function getEventDots(day) {
  return props.events.filter(e => e.date === day.date).slice(0, 3)
}

// Styling
const containerClasses = computed(() => {
  const variants = {
    default: 'bg-white rounded-xl shadow-sm p-4',
    bordered: 'border border-gray-200 rounded-xl p-4',
    flat: 'p-4'
  }
  return variants[props.variant]
})

const headerTextClasses = 'text-gray-900'
const dayHeaderClasses = 'text-gray-500'
const gridBgClasses = 'bg-gray-200'

const navButtonClasses = 'text-gray-600 hover:bg-gray-100'
const todayButtonClasses = 'text-gray-600 hover:bg-gray-100'

const eventCardClasses = 'bg-gray-50'
const eventDescClasses = 'text-gray-600'
const eventTimeClasses = 'text-gray-500'

function getDayClasses(day) {
  const base = 'bg-white'
  
  if (day.disabled) {
    return `${base} text-gray-300 cursor-not-allowed`
  }
  
  if (!day.isCurrentMonth) {
    return `${base} text-gray-400 hover:bg-gray-50`
  }
  
  return `${base} text-gray-900 hover:bg-gray-50 focus:ring-primary-500`
}

function getDayInnerClasses(day) {
  if (day.isSelected) {
    return 'bg-primary-600 text-white font-semibold'
  }
  
  if (day.isToday) {
    return 'bg-primary-100 text-primary-700 font-semibold'
  }
  
  return ''
}
</script>
