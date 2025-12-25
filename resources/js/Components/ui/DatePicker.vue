<template>
  <Popover v-slot="{ open }" class="relative">
    <PopoverButton
      class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
      :class="{ 'ring-2 ring-primary-500': open }"
      :disabled="disabled"
    >
      <CalendarIcon class="h-5 w-5 text-gray-400" />
      <span>{{ displayValue || placeholder }}</span>
      <ChevronDownIcon
        class="h-4 w-4 text-gray-400 transition-transform"
        :class="{ 'rotate-180': open }"
      />
    </PopoverButton>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <PopoverPanel
        class="absolute z-50 mt-2 w-auto rounded-xl bg-white p-4 shadow-xl ring-1 ring-black ring-opacity-5"
        :class="alignmentClasses"
      >
        <template #default="{ close }">
          <!-- Quick select buttons -->
          <div v-if="showQuickSelect" class="mb-4 flex flex-wrap gap-2">
            <button
              v-for="option in quickSelectOptions"
              :key="option.label"
              type="button"
              class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
              :class="isQuickSelectActive(option) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              @click="selectQuickOption(option, close)"
            >
              {{ option.label }}
            </button>
          </div>

          <!-- Calendar header -->
          <div class="flex items-center justify-between mb-4">
            <button
              type="button"
              class="p-1 rounded-lg hover:bg-gray-100 transition-colors"
              @click="previousMonth"
            >
              <ChevronLeftIcon class="h-5 w-5 text-gray-600" />
            </button>
            
            <div class="flex items-center gap-2">
              <select
                v-model="viewMonth"
                class="rounded-lg border-0 bg-transparent py-1 pl-2 pr-7 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary-500"
              >
                <option v-for="(month, index) in monthNames" :key="index" :value="index">
                  {{ month }}
                </option>
              </select>
              <select
                v-model="viewYear"
                class="rounded-lg border-0 bg-transparent py-1 pl-2 pr-7 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary-500"
              >
                <option v-for="year in yearRange" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>

            <button
              type="button"
              class="p-1 rounded-lg hover:bg-gray-100 transition-colors"
              @click="nextMonth"
            >
              <ChevronRightIcon class="h-5 w-5 text-gray-600" />
            </button>
          </div>

          <!-- Day names -->
          <div class="grid grid-cols-7 mb-2">
            <div
              v-for="day in dayNames"
              :key="day"
              class="py-2 text-center text-xs font-medium text-gray-500"
            >
              {{ day }}
            </div>
          </div>

          <!-- Calendar days -->
          <div class="grid grid-cols-7 gap-1">
            <button
              v-for="day in calendarDays"
              :key="day.date"
              type="button"
              class="relative h-9 w-9 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
              :class="getDayClasses(day)"
              :disabled="day.disabled"
              @click="selectDate(day, close)"
            >
              {{ day.dayNumber }}
              <span
                v-if="day.isToday"
                class="absolute bottom-1 left-1/2 -translate-x-1/2 h-1 w-1 rounded-full bg-primary-600"
              />
            </button>
          </div>

          <!-- Footer with today button -->
          <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
            <button
              type="button"
              class="text-sm font-medium text-primary-600 hover:text-primary-700"
              @click="goToToday"
            >
              Today
            </button>
            <button
              v-if="clearable && modelValue"
              type="button"
              class="text-sm font-medium text-gray-500 hover:text-gray-700"
              @click="clearDate(close)"
            >
              Clear
            </button>
          </div>
        </template>
      </PopoverPanel>
    </Transition>
  </Popover>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { CalendarIcon, ChevronLeftIcon, ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: [Date, String],
    default: null
  },
  placeholder: {
    type: String,
    default: 'Select date'
  },
  format: {
    type: String,
    default: 'DD/MM/YYYY' // Bangladesh format
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
  disabledDays: {
    type: Array,
    default: () => [] // 0 = Sunday, 6 = Saturday
  },
  clearable: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  showQuickSelect: {
    type: Boolean,
    default: true
  },
  align: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// View state
const viewMonth = ref(new Date().getMonth())
const viewYear = ref(new Date().getFullYear())

// Initialize view from modelValue
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    const date = new Date(newVal)
    viewMonth.value = date.getMonth()
    viewYear.value = date.getFullYear()
  }
}, { immediate: true })

// Month names
const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

// Day names (Bangladesh starts week on Saturday typically, using Sunday here)
const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

// Year range (10 years before and after)
const yearRange = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = currentYear - 10; i <= currentYear + 10; i++) {
    years.push(i)
  }
  return years
})

// Quick select options
const quickSelectOptions = [
  { label: 'Today', getValue: () => new Date() },
  { label: 'Tomorrow', getValue: () => { const d = new Date(); d.setDate(d.getDate() + 1); return d } },
  { label: 'In 1 Week', getValue: () => { const d = new Date(); d.setDate(d.getDate() + 7); return d } },
  { label: 'In 1 Month', getValue: () => { const d = new Date(); d.setMonth(d.getMonth() + 1); return d } }
]

// Generate calendar days
const calendarDays = computed(() => {
  const days = []
  const firstDay = new Date(viewYear.value, viewMonth.value, 1)
  const lastDay = new Date(viewYear.value, viewMonth.value + 1, 0)
  
  // Padding from previous month
  const startPadding = firstDay.getDay()
  const prevMonth = new Date(viewYear.value, viewMonth.value, 0)
  
  for (let i = startPadding - 1; i >= 0; i--) {
    const date = new Date(viewYear.value, viewMonth.value - 1, prevMonth.getDate() - i)
    days.push(createDayObject(date, false))
  }
  
  // Current month days
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const date = new Date(viewYear.value, viewMonth.value, i)
    days.push(createDayObject(date, true))
  }
  
  // Padding from next month
  const remaining = 42 - days.length
  for (let i = 1; i <= remaining; i++) {
    const date = new Date(viewYear.value, viewMonth.value + 1, i)
    days.push(createDayObject(date, false))
  }
  
  return days
})

// Create day object with all properties
function createDayObject(date, isCurrentMonth) {
  const dateStr = formatDateISO(date)
  return {
    date: dateStr,
    dayNumber: date.getDate(),
    isCurrentMonth,
    isToday: isToday(date),
    isSelected: isSelected(date),
    disabled: isDisabled(date)
  }
}

// Format date to ISO string
function formatDateISO(date) {
  return date.toISOString().split('T')[0]
}

// Check if date is today
function isToday(date) {
  const today = new Date()
  return formatDateISO(date) === formatDateISO(today)
}

// Check if date is selected
function isSelected(date) {
  if (!props.modelValue) return false
  return formatDateISO(date) === formatDateISO(new Date(props.modelValue))
}

// Check if date is disabled
function isDisabled(date) {
  const dateStr = formatDateISO(date)
  
  // Check disabled dates array
  if (props.disabledDates.includes(dateStr)) return true
  
  // Check disabled days of week
  if (props.disabledDays.includes(date.getDay())) return true
  
  // Check min/max
  if (props.minDate && date < new Date(props.minDate)) return true
  if (props.maxDate && date > new Date(props.maxDate)) return true
  
  return false
}

// Navigation
function previousMonth() {
  if (viewMonth.value === 0) {
    viewMonth.value = 11
    viewYear.value--
  } else {
    viewMonth.value--
  }
}

function nextMonth() {
  if (viewMonth.value === 11) {
    viewMonth.value = 0
    viewYear.value++
  } else {
    viewMonth.value++
  }
}

function goToToday() {
  const today = new Date()
  viewMonth.value = today.getMonth()
  viewYear.value = today.getFullYear()
}

// Selection
function selectDate(day, close) {
  if (day.disabled) return
  emit('update:modelValue', day.date)
  emit('change', day.date)
  close()
}

function clearDate(close) {
  emit('update:modelValue', null)
  emit('change', null)
  close()
}

function selectQuickOption(option, close) {
  const date = option.getValue()
  const dateStr = formatDateISO(date)
  emit('update:modelValue', dateStr)
  emit('change', dateStr)
  viewMonth.value = date.getMonth()
  viewYear.value = date.getFullYear()
  close()
}

function isQuickSelectActive(option) {
  if (!props.modelValue) return false
  const optionDate = formatDateISO(option.getValue())
  return formatDateISO(new Date(props.modelValue)) === optionDate
}

// Display value
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const date = new Date(props.modelValue)
  // DD/MM/YYYY format for Bangladesh
  const day = date.getDate().toString().padStart(2, '0')
  const month = (date.getMonth() + 1).toString().padStart(2, '0')
  const year = date.getFullYear()
  return `${day}/${month}/${year}`
})

// Day styling
function getDayClasses(day) {
  if (day.disabled) {
    return 'text-gray-300 cursor-not-allowed'
  }
  
  if (day.isSelected) {
    return 'bg-primary-600 text-white hover:bg-primary-700'
  }
  
  if (!day.isCurrentMonth) {
    return 'text-gray-400 hover:bg-gray-100'
  }
  
  return 'text-gray-900 hover:bg-gray-100'
}

// Alignment classes
const alignmentClasses = computed(() => {
  return props.align === 'right' ? 'right-0' : 'left-0'
})
</script>
