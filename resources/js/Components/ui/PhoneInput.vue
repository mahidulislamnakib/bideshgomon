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

    <div class="relative flex">
      <!-- Country code selector -->
      <Menu as="div" class="relative">
        <MenuButton
          class="inline-flex items-center gap-1.5 rounded-l-lg border-0 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary-600"
          :class="{ 'ring-red-300': error }"
        >
          <span class="text-lg">{{ selectedCountry.flag }}</span>
          <span class="font-medium">{{ selectedCountry.dialCode }}</span>
          <ChevronDownIcon class="h-4 w-4 text-gray-400" />
        </MenuButton>

        <Transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <MenuItems
            class="absolute left-0 z-50 mt-1 max-h-60 w-64 overflow-auto rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
          >
            <!-- Search -->
            <div class="sticky top-0 z-10 bg-white px-3 py-2">
              <input
                v-model="countrySearch"
                type="text"
                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                placeholder="Search country..."
                @click.stop
              />
            </div>

            <MenuItem
              v-for="country in filteredCountries"
              :key="country.code"
              v-slot="{ active }"
            >
              <button
                type="button"
                class="flex w-full items-center gap-3 px-4 py-2 text-sm"
                :class="active ? 'bg-gray-100' : ''"
                @click="selectCountry(country)"
              >
                <span class="text-lg">{{ country.flag }}</span>
                <span class="flex-1 text-left">{{ country.name }}</span>
                <span class="text-gray-500">{{ country.dialCode }}</span>
              </button>
            </MenuItem>
          </MenuItems>
        </Transition>
      </Menu>

      <!-- Phone input -->
      <input
        :id="inputId"
        ref="inputRef"
        type="tel"
        :value="localNumber"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        class="block w-full rounded-r-lg border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500"
        :class="{ 'ring-red-300 focus:ring-red-500': error }"
        @input="handleInput"
        @blur="handleBlur"
      />

      <!-- Operator badge (Bangladesh specific) -->
      <div
        v-if="showOperator && detectedOperator"
        class="absolute right-3 top-1/2 -translate-y-1/2"
      >
        <span
          class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
          :class="operatorClasses"
        >
          {{ detectedOperator }}
        </span>
      </div>
    </div>

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
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '01XXX-XXXXXX'
  },
  defaultCountry: {
    type: String,
    default: 'BD' // Bangladesh default
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  showOperator: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'country-change', 'operator-detected'])

// Refs
const inputRef = ref(null)
const countrySearch = ref('')
const inputId = `phone-${Math.random().toString(36).substring(2, 9)}`

// Countries list with Bangladesh operators info
const countries = [
  { code: 'BD', name: 'Bangladesh', dialCode: '+880', flag: '🇧🇩' },
  { code: 'IN', name: 'India', dialCode: '+91', flag: '🇮🇳' },
  { code: 'PK', name: 'Pakistan', dialCode: '+92', flag: '🇵🇰' },
  { code: 'US', name: 'United States', dialCode: '+1', flag: '🇺🇸' },
  { code: 'GB', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧' },
  { code: 'AE', name: 'UAE', dialCode: '+971', flag: '🇦🇪' },
  { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', flag: '🇸🇦' },
  { code: 'MY', name: 'Malaysia', dialCode: '+60', flag: '🇲🇾' },
  { code: 'SG', name: 'Singapore', dialCode: '+65', flag: '🇸🇬' },
  { code: 'AU', name: 'Australia', dialCode: '+61', flag: '🇦🇺' },
  { code: 'CA', name: 'Canada', dialCode: '+1', flag: '🇨🇦' },
  { code: 'DE', name: 'Germany', dialCode: '+49', flag: '🇩🇪' },
  { code: 'FR', name: 'France', dialCode: '+33', flag: '🇫🇷' },
  { code: 'IT', name: 'Italy', dialCode: '+39', flag: '🇮🇹' },
  { code: 'JP', name: 'Japan', dialCode: '+81', flag: '🇯🇵' },
  { code: 'KR', name: 'South Korea', dialCode: '+82', flag: '🇰🇷' },
  { code: 'CN', name: 'China', dialCode: '+86', flag: '🇨🇳' },
  { code: 'NP', name: 'Nepal', dialCode: '+977', flag: '🇳🇵' },
  { code: 'QA', name: 'Qatar', dialCode: '+974', flag: '🇶🇦' },
  { code: 'KW', name: 'Kuwait', dialCode: '+965', flag: '🇰🇼' }
]

// Bangladesh mobile operators
const bdOperators = {
  '013': 'Grameenphone',
  '017': 'Grameenphone',
  '014': 'Banglalink',
  '019': 'Banglalink',
  '016': 'Robi',
  '018': 'Robi',
  '015': 'Teletalk',
  '011': 'Airtel (Robi)'
}

// Selected country
const selectedCountry = ref(countries.find(c => c.code === props.defaultCountry) || countries[0])
const localNumber = ref('')

// Parse initial value
watch(() => props.modelValue, (newVal) => {
  if (newVal && !localNumber.value) {
    // Try to extract country code and number
    for (const country of countries) {
      if (newVal.startsWith(country.dialCode)) {
        selectedCountry.value = country
        localNumber.value = newVal.replace(country.dialCode, '').trim()
        return
      }
    }
    localNumber.value = newVal
  }
}, { immediate: true })

// Filtered countries for search
const filteredCountries = computed(() => {
  if (!countrySearch.value) return countries
  const query = countrySearch.value.toLowerCase()
  return countries.filter(c => 
    c.name.toLowerCase().includes(query) ||
    c.dialCode.includes(query) ||
    c.code.toLowerCase().includes(query)
  )
})

// Detect Bangladesh operator
const detectedOperator = computed(() => {
  if (selectedCountry.value.code !== 'BD') return null
  const cleaned = localNumber.value.replace(/\D/g, '')
  if (cleaned.length >= 3) {
    const prefix = cleaned.startsWith('0') ? cleaned.substring(0, 3) : '0' + cleaned.substring(0, 2)
    return bdOperators[prefix] || null
  }
  return null
})

// Watch for operator changes
watch(detectedOperator, (operator) => {
  if (operator) {
    emit('operator-detected', operator)
  }
})

// Handle input
function handleInput(event) {
  let value = event.target.value
  
  // Format Bangladesh number (01XXX-XXXXXX)
  if (selectedCountry.value.code === 'BD') {
    value = formatBDNumber(value)
  }
  
  localNumber.value = value
  
  // Emit full number with country code
  const fullNumber = selectedCountry.value.dialCode + value.replace(/\D/g, '')
  emit('update:modelValue', fullNumber)
}

// Format Bangladesh phone number
function formatBDNumber(value) {
  const cleaned = value.replace(/\D/g, '')
  if (cleaned.length <= 5) {
    return cleaned
  }
  return cleaned.substring(0, 5) + '-' + cleaned.substring(5, 11)
}

// Handle blur
function handleBlur() {
  // Additional formatting on blur if needed
}

// Select country
function selectCountry(country) {
  selectedCountry.value = country
  countrySearch.value = ''
  emit('country-change', country)
  
  // Re-emit value with new country code
  const fullNumber = country.dialCode + localNumber.value.replace(/\D/g, '')
  emit('update:modelValue', fullNumber)
}

// Operator badge colors
const operatorClasses = computed(() => {
  const colors = {
    'Grameenphone': 'bg-green-100 text-green-700',
    'Banglalink': 'bg-orange-100 text-orange-700',
    'Robi': 'bg-red-100 text-red-700',
    'Teletalk': 'bg-blue-100 text-blue-700',
    'Airtel (Robi)': 'bg-red-100 text-red-700'
  }
  return colors[detectedOperator.value] || 'bg-gray-100 text-gray-700'
})

// Container classes
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60' : ''
})

// Label classes
const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

// Expose focus method
defineExpose({
  focus: () => inputRef.value?.focus()
})
</script>
