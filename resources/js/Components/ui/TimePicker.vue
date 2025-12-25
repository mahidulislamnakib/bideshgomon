<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium mb-1.5"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <Popover v-slot="{ open }" class="relative">
      <!-- Trigger -->
      <PopoverButton
        :id="inputId"
        :disabled="disabled"
        class="relative w-full cursor-pointer rounded-lg border-0 bg-white py-2.5 pl-3 pr-10 text-left text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-600 disabled:bg-gray-50 disabled:cursor-not-allowed"
        :class="{ 'ring-red-300 focus:ring-red-500': error }"
      >
        <span class="flex items-center gap-2">
          <ClockIcon class="h-5 w-5 text-gray-400" />
          <span :class="displayValue ? 'text-gray-900' : 'text-gray-400'">
            {{ displayValue || placeholder }}
          </span>
        </span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
          <ChevronDownIcon class="h-5 w-5 text-gray-400" />
        </span>
      </PopoverButton>

      <!-- Dropdown -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-1 opacity-0"
      >
        <PopoverPanel
          class="absolute left-0 z-50 mt-2 w-64 origin-top-left rounded-lg bg-white p-4 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        >
          <div class="space-y-4">
            <!-- Time display -->
            <div class="text-center text-3xl font-semibold text-gray-900 tabular-nums">
              {{ formatDisplayTime(selectedHour, selectedMinute) }}
            </div>

            <!-- Hour/Minute selectors -->
            <div class="flex gap-4">
              <!-- Hours -->
              <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-2 text-center">Hour</label>
                <div class="h-32 overflow-y-auto scrollbar-thin">
                  <button
                    v-for="hour in hours"
                    :key="hour"
                    type="button"
                    class="w-full py-1.5 text-center text-sm rounded-md transition-colors"
                    :class="hour === selectedHour ? 'bg-primary-600 text-white' : 'hover:bg-gray-100 text-gray-700'"
                    @click="selectHour(hour)"
                  >
                    {{ formatNumber(use24Hour ? hour : (hour === 0 ? 12 : hour > 12 ? hour - 12 : hour)) }}
                  </button>
                </div>
              </div>

              <!-- Minutes -->
              <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-2 text-center">Minute</label>
                <div class="h-32 overflow-y-auto scrollbar-thin">
                  <button
                    v-for="minute in minutes"
                    :key="minute"
                    type="button"
                    class="w-full py-1.5 text-center text-sm rounded-md transition-colors"
                    :class="minute === selectedMinute ? 'bg-primary-600 text-white' : 'hover:bg-gray-100 text-gray-700'"
                    @click="selectMinute(minute)"
                  >
                    {{ formatNumber(minute) }}
                  </button>
                </div>
              </div>

              <!-- AM/PM (12-hour mode) -->
              <div v-if="!use24Hour" class="w-16">
                <label class="block text-xs font-medium text-gray-500 mb-2 text-center">Period</label>
                <div class="space-y-1">
                  <button
                    type="button"
                    class="w-full py-2 text-center text-sm font-medium rounded-md transition-colors"
                    :class="selectedPeriod === 'AM' ? 'bg-primary-600 text-white' : 'hover:bg-gray-100 text-gray-700'"
                    @click="selectPeriod('AM')"
                  >
                    AM
                  </button>
                  <button
                    type="button"
                    class="w-full py-2 text-center text-sm font-medium rounded-md transition-colors"
                    :class="selectedPeriod === 'PM' ? 'bg-primary-600 text-white' : 'hover:bg-gray-100 text-gray-700'"
                    @click="selectPeriod('PM')"
                  >
                    PM
                  </button>
                </div>
              </div>
            </div>

            <!-- Quick select -->
            <div v-if="showQuickSelect" class="border-t border-gray-100 pt-3">
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="preset in presets"
                  :key="preset.label"
                  type="button"
                  class="px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors"
                  @click="applyPreset(preset)"
                >
                  {{ preset.label }}
                </button>
              </div>
            </div>

            <!-- Clear button -->
            <div v-if="clearable && modelValue" class="border-t border-gray-100 pt-3">
              <button
                type="button"
                class="w-full py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition-colors"
                @click="clearTime"
              >
                Clear
              </button>
            </div>
          </div>
        </PopoverPanel>
      </Transition>
    </Popover>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-1.5 text-sm"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { ClockIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: String, // "HH:mm" format
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select time'
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  use24Hour: {
    type: Boolean,
    default: false // Bangladesh uses 12-hour format
  },
  minuteStep: {
    type: Number,
    default: 5
  },
  minTime: {
    type: String, // "HH:mm"
    default: ''
  },
  maxTime: {
    type: String, // "HH:mm"
    default: ''
  },
  showQuickSelect: {
    type: Boolean,
    default: true
  },
  clearable: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// State
const inputId = `time-${Math.random().toString(36).substring(2, 9)}`
const selectedHour = ref(9)
const selectedMinute = ref(0)
const selectedPeriod = ref('AM')

// Parse initial value
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    const [h, m] = newVal.split(':').map(Number)
    selectedHour.value = h
    selectedMinute.value = m
    selectedPeriod.value = h >= 12 ? 'PM' : 'AM'
  }
}, { immediate: true })

// Generate hours array
const hours = computed(() => {
  return Array.from({ length: 24 }, (_, i) => i)
})

// Generate minutes array based on step
const minutes = computed(() => {
  const result = []
  for (let i = 0; i < 60; i += props.minuteStep) {
    result.push(i)
  }
  return result
})

// Presets for quick select
const presets = [
  { label: 'Morning', hour: 9, minute: 0 },
  { label: 'Noon', hour: 12, minute: 0 },
  { label: 'Afternoon', hour: 15, minute: 0 },
  { label: 'Evening', hour: 18, minute: 0 },
  { label: 'Night', hour: 21, minute: 0 }
]

// Display value
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  return formatDisplayTime(selectedHour.value, selectedMinute.value)
})

// Format time for display
function formatDisplayTime(hour, minute) {
  if (props.use24Hour) {
    return `${formatNumber(hour)}:${formatNumber(minute)}`
  }
  const h = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour
  const period = hour >= 12 ? 'PM' : 'AM'
  return `${formatNumber(h)}:${formatNumber(minute)} ${period}`
}

// Format number with leading zero
function formatNumber(n) {
  return n.toString().padStart(2, '0')
}

// Select hour
function selectHour(hour) {
  selectedHour.value = hour
  if (!props.use24Hour) {
    selectedPeriod.value = hour >= 12 ? 'PM' : 'AM'
  }
  emitValue()
}

// Select minute
function selectMinute(minute) {
  selectedMinute.value = minute
  emitValue()
}

// Select period (AM/PM)
function selectPeriod(period) {
  selectedPeriod.value = period
  // Adjust hour based on period
  if (period === 'AM' && selectedHour.value >= 12) {
    selectedHour.value -= 12
  } else if (period === 'PM' && selectedHour.value < 12) {
    selectedHour.value += 12
  }
  emitValue()
}

// Apply preset
function applyPreset(preset) {
  selectedHour.value = preset.hour
  selectedMinute.value = preset.minute
  selectedPeriod.value = preset.hour >= 12 ? 'PM' : 'AM'
  emitValue()
}

// Clear time
function clearTime() {
  selectedHour.value = 9
  selectedMinute.value = 0
  selectedPeriod.value = 'AM'
  emit('update:modelValue', '')
  emit('change', '')
}

// Emit value
function emitValue() {
  const value = `${formatNumber(selectedHour.value)}:${formatNumber(selectedMinute.value)}`
  emit('update:modelValue', value)
  emit('change', value)
}

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
