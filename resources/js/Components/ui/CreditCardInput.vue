<template>
  <div :class="['credit-card-input', containerClasses]">
    <!-- Card Preview -->
    <div
      v-if="showPreview"
      :class="[
        'relative w-full max-w-sm h-56 rounded-2xl p-6 text-white overflow-hidden transition-transform duration-500 preserve-3d cursor-pointer mb-6',
        isFlipped ? 'rotate-y-180' : '',
        cardBrandClasses
      ]"
      @click="isFlipped = !isFlipped"
    >
      <!-- Front -->
      <div :class="['absolute inset-0 p-6 backface-hidden', cardBrandClasses]">
        <!-- Card Pattern -->
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 right-0 w-64 h-64 rounded-full bg-white transform translate-x-1/3 -translate-y-1/3" />
          <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full bg-white transform -translate-x-1/3 translate-y-1/3" />
        </div>

        <!-- Chip -->
        <div class="relative">
          <div class="w-12 h-9 rounded-md bg-gradient-to-br from-yellow-300 to-yellow-500 mb-6">
            <div class="w-full h-full grid grid-cols-3 gap-0.5 p-1">
              <div v-for="i in 6" :key="i" class="bg-yellow-600/30 rounded-sm" />
            </div>
          </div>
        </div>

        <!-- Card Number -->
        <div class="relative mb-6">
          <div class="text-xl sm:text-2xl tracking-widest font-mono">
            {{ displayCardNumber }}
          </div>
        </div>

        <!-- Bottom Row -->
        <div class="relative flex justify-between items-end">
          <div>
            <div class="text-[10px] uppercase opacity-70 mb-1">Card Holder</div>
            <div class="text-sm sm:text-base font-medium uppercase tracking-wide truncate max-w-[180px]">
              {{ cardHolder || 'YOUR NAME' }}
            </div>
          </div>
          <div class="text-right">
            <div class="text-[10px] uppercase opacity-70 mb-1">Expires</div>
            <div class="text-sm sm:text-base font-medium">
              {{ displayExpiry }}
            </div>
          </div>
        </div>

        <!-- Brand Logo -->
        <div class="absolute top-6 right-6">
          <component :is="brandLogo" class="h-10 w-auto" />
        </div>
      </div>

      <!-- Back -->
      <div :class="['absolute inset-0 backface-hidden rotate-y-180', cardBrandClasses]">
        <!-- Magnetic Strip -->
        <div class="w-full h-12 bg-gray-800 mt-6" />
        
        <!-- CVV Section -->
        <div class="px-6 mt-6">
          <div class="bg-white/90 rounded px-4 py-2">
            <div class="flex justify-end items-center">
              <span class="text-gray-600 text-sm mr-2">CVV</span>
              <span class="font-mono text-gray-900 text-lg tracking-widest">
                {{ cvv ? '•'.repeat(cvv.length) : '•••' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Brand Logo -->
        <div class="absolute bottom-6 right-6">
          <component :is="brandLogo" class="h-8 w-auto opacity-50" />
        </div>
      </div>
    </div>

    <!-- Input Fields -->
    <div class="space-y-4">
      <!-- Card Number -->
      <div>
        <label :class="['block text-sm font-medium mb-1.5', themeClasses.label]">
          Card Number
        </label>
        <div class="relative">
          <input
            ref="cardNumberRef"
            :value="displayCardNumberInput"
            type="text"
            inputmode="numeric"
            autocomplete="cc-number"
            placeholder="1234 5678 9012 3456"
            :class="[
              'w-full pl-4 pr-12 py-3 rounded-lg border transition-colors font-mono',
              themeClasses.input,
              errors.cardNumber && 'border-red-500 focus:border-red-500'
            ]"
            @input="handleCardNumber"
            @focus="focusedField = 'number'"
            @blur="focusedField = null"
          />
          <div class="absolute right-3 top-1/2 -translate-y-1/2">
            <component :is="brandLogo" v-if="detectedBrand" class="h-6 w-auto" />
            <CreditCardIcon v-else class="h-6 w-6 text-gray-400" />
          </div>
        </div>
        <p v-if="errors.cardNumber" class="mt-1 text-sm text-red-500">{{ errors.cardNumber }}</p>
      </div>

      <!-- Card Holder -->
      <div>
        <label :class="['block text-sm font-medium mb-1.5', themeClasses.label]">
          Card Holder Name
        </label>
        <input
          v-model="cardHolder"
          type="text"
          autocomplete="cc-name"
          placeholder="JOHN DOE"
          :class="[
            'w-full px-4 py-3 rounded-lg border transition-colors uppercase',
            themeClasses.input,
            errors.cardHolder && 'border-red-500 focus:border-red-500'
          ]"
          @focus="focusedField = 'holder'"
          @blur="focusedField = null"
        />
        <p v-if="errors.cardHolder" class="mt-1 text-sm text-red-500">{{ errors.cardHolder }}</p>
      </div>

      <!-- Expiry & CVV -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label :class="['block text-sm font-medium mb-1.5', themeClasses.label]">
            Expiry Date
          </label>
          <input
            ref="expiryRef"
            :value="expiry"
            type="text"
            inputmode="numeric"
            autocomplete="cc-exp"
            placeholder="MM/YY"
            maxlength="5"
            :class="[
              'w-full px-4 py-3 rounded-lg border transition-colors font-mono',
              themeClasses.input,
              errors.expiry && 'border-red-500 focus:border-red-500'
            ]"
            @input="handleExpiry"
            @focus="focusedField = 'expiry'"
            @blur="focusedField = null"
          />
          <p v-if="errors.expiry" class="mt-1 text-sm text-red-500">{{ errors.expiry }}</p>
        </div>

        <div>
          <label :class="['block text-sm font-medium mb-1.5', themeClasses.label]">
            CVV
          </label>
          <div class="relative">
            <input
              v-model="cvv"
              :type="showCvv ? 'text' : 'password'"
              inputmode="numeric"
              autocomplete="cc-csc"
              placeholder="•••"
              :maxlength="detectedBrand === 'amex' ? 4 : 3"
              :class="[
                'w-full px-4 py-3 rounded-lg border transition-colors font-mono',
                themeClasses.input,
                errors.cvv && 'border-red-500 focus:border-red-500'
              ]"
              @focus="focusedField = 'cvv'; isFlipped = true"
              @blur="focusedField = null; isFlipped = false"
              @input="handleCvv"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              @click="showCvv = !showCvv"
            >
              <EyeIcon v-if="!showCvv" class="h-5 w-5" />
              <EyeSlashIcon v-else class="h-5 w-5" />
            </button>
          </div>
          <p v-if="errors.cvv" class="mt-1 text-sm text-red-500">{{ errors.cvv }}</p>
        </div>
      </div>
    </div>

    <!-- Validation Status -->
    <div v-if="showValidation" class="mt-4 flex items-center gap-2">
      <CheckCircleIcon v-if="isValid" class="h-5 w-5 text-green-500" />
      <XCircleIcon v-else class="h-5 w-5 text-red-500" />
      <span :class="isValid ? 'text-green-600' : 'text-red-600'" class="text-sm">
        {{ isValid ? 'Card details are valid' : 'Please check your card details' }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, h } from 'vue'
import {
  CreditCardIcon,
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  showPreview: {
    type: Boolean,
    default: true
  },
  showValidation: {
    type: Boolean,
    default: true
  },
  validateOnBlur: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'valid', 'brand-detected'])

const cardNumberRef = ref(null)
const expiryRef = ref(null)

const cardNumber = ref('')
const cardHolder = ref('')
const expiry = ref('')
const cvv = ref('')
const focusedField = ref(null)
const isFlipped = ref(false)
const showCvv = ref(false)
const errors = ref({})

// Brand logos as simple SVG components
const VisaLogo = {
  render: () => h('svg', { viewBox: '0 0 48 16', fill: 'currentColor' }, [
    h('text', { x: 0, y: 14, 'font-size': '16', 'font-weight': 'bold', 'font-style': 'italic' }, 'VISA')
  ])
}

const MastercardLogo = {
  render: () => h('svg', { viewBox: '0 0 48 32' }, [
    h('circle', { cx: 16, cy: 16, r: 14, fill: '#EB001B' }),
    h('circle', { cx: 32, cy: 16, r: 14, fill: '#F79E1B' }),
    h('path', { d: 'M24 5.5a14 14 0 0 0 0 21 14 14 0 0 0 0-21z', fill: '#FF5F00' })
  ])
}

const AmexLogo = {
  render: () => h('svg', { viewBox: '0 0 48 16', fill: 'currentColor' }, [
    h('text', { x: 0, y: 13, 'font-size': '12', 'font-weight': 'bold' }, 'AMEX')
  ])
}

const DiscoverLogo = {
  render: () => h('svg', { viewBox: '0 0 60 16', fill: 'currentColor' }, [
    h('text', { x: 0, y: 13, 'font-size': '12', 'font-weight': 'bold' }, 'DISCOVER')
  ])
}

const DefaultLogo = {
  render: () => h(CreditCardIcon, { class: 'h-8 w-8' })
}

const containerClasses = computed(() => {
  return props.theme === 'dark' ? 'text-white' : 'text-gray-900'
})

const themeClasses = computed(() => {
  if (props.theme === 'dark') {
    return {
      label: 'text-gray-300',
      input: 'bg-gray-800 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none'
    }
  }
  return {
    label: 'text-gray-700',
    input: 'bg-white border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none'
  }
})

const detectedBrand = computed(() => {
  const num = cardNumber.value.replace(/\s/g, '')
  if (/^4/.test(num)) return 'visa'
  if (/^5[1-5]/.test(num) || /^2[2-7]/.test(num)) return 'mastercard'
  if (/^3[47]/.test(num)) return 'amex'
  if (/^6(?:011|5)/.test(num)) return 'discover'
  return null
})

const brandLogo = computed(() => {
  const logos = {
    visa: VisaLogo,
    mastercard: MastercardLogo,
    amex: AmexLogo,
    discover: DiscoverLogo
  }
  return logos[detectedBrand.value] || DefaultLogo
})

const cardBrandClasses = computed(() => {
  const classes = {
    visa: 'bg-gradient-to-br from-blue-600 to-blue-800',
    mastercard: 'bg-gradient-to-br from-gray-800 to-gray-900',
    amex: 'bg-gradient-to-br from-blue-400 to-blue-600',
    discover: 'bg-gradient-to-br from-orange-500 to-orange-700'
  }
  return classes[detectedBrand.value] || 'bg-gradient-to-br from-gray-600 to-gray-800'
})

const displayCardNumber = computed(() => {
  const num = cardNumber.value.replace(/\s/g, '').padEnd(16, '•')
  if (detectedBrand.value === 'amex') {
    return `${num.slice(0, 4)} ${num.slice(4, 10)} ${num.slice(10, 15)}`
  }
  return num.match(/.{1,4}/g)?.join(' ') || '•••• •••• •••• ••••'
})

const displayCardNumberInput = computed(() => {
  const num = cardNumber.value.replace(/\s/g, '')
  if (detectedBrand.value === 'amex') {
    return num.replace(/(\d{4})(\d{0,6})(\d{0,5})/, (_, a, b, c) => 
      [a, b, c].filter(Boolean).join(' ')
    )
  }
  return num.replace(/(\d{4})(?=\d)/g, '$1 ')
})

const displayExpiry = computed(() => {
  return expiry.value || 'MM/YY'
})

const isValid = computed(() => {
  return validateCard() && validateHolder() && validateExpiry() && validateCvv()
})

const validateCard = () => {
  const num = cardNumber.value.replace(/\s/g, '')
  if (!num) return false
  
  // Luhn algorithm
  let sum = 0
  let isEven = false
  for (let i = num.length - 1; i >= 0; i--) {
    let digit = parseInt(num[i], 10)
    if (isEven) {
      digit *= 2
      if (digit > 9) digit -= 9
    }
    sum += digit
    isEven = !isEven
  }
  
  const validLength = detectedBrand.value === 'amex' ? num.length === 15 : num.length === 16
  return sum % 10 === 0 && validLength
}

const validateHolder = () => {
  return cardHolder.value.trim().length >= 2
}

const validateExpiry = () => {
  const [month, year] = expiry.value.split('/')
  if (!month || !year) return false
  
  const m = parseInt(month, 10)
  const y = parseInt('20' + year, 10)
  
  if (m < 1 || m > 12) return false
  
  const now = new Date()
  const expDate = new Date(y, m)
  return expDate > now
}

const validateCvv = () => {
  const len = detectedBrand.value === 'amex' ? 4 : 3
  return cvv.value.length === len
}

const handleCardNumber = (e) => {
  let value = e.target.value.replace(/\D/g, '')
  const maxLen = detectedBrand.value === 'amex' ? 15 : 16
  value = value.slice(0, maxLen)
  cardNumber.value = value
  
  if (props.validateOnBlur) {
    errors.value.cardNumber = ''
  }
}

const handleExpiry = (e) => {
  let value = e.target.value.replace(/\D/g, '')
  if (value.length >= 2) {
    const month = parseInt(value.slice(0, 2), 10)
    if (month > 12) value = '12' + value.slice(2)
    if (month === 0) value = '01' + value.slice(2)
    value = value.slice(0, 2) + '/' + value.slice(2, 4)
  }
  expiry.value = value
}

const handleCvv = (e) => {
  cvv.value = e.target.value.replace(/\D/g, '')
}

watch([cardNumber, cardHolder, expiry, cvv], () => {
  const data = {
    cardNumber: cardNumber.value.replace(/\s/g, ''),
    cardHolder: cardHolder.value,
    expiry: expiry.value,
    cvv: cvv.value,
    brand: detectedBrand.value,
    isValid: isValid.value
  }
  emit('update:modelValue', data)
  emit('valid', isValid.value)
})

watch(detectedBrand, (brand) => {
  emit('brand-detected', brand)
})

watch(() => props.modelValue, (val) => {
  if (val) {
    if (val.cardNumber) cardNumber.value = val.cardNumber
    if (val.cardHolder) cardHolder.value = val.cardHolder
    if (val.expiry) expiry.value = val.expiry
    if (val.cvv) cvv.value = val.cvv
  }
}, { immediate: true })

defineExpose({
  validate: () => {
    errors.value = {}
    if (!validateCard()) errors.value.cardNumber = 'Invalid card number'
    if (!validateHolder()) errors.value.cardHolder = 'Name is required'
    if (!validateExpiry()) errors.value.expiry = 'Invalid expiry date'
    if (!validateCvv()) errors.value.cvv = 'Invalid CVV'
    return isValid.value
  },
  reset: () => {
    cardNumber.value = ''
    cardHolder.value = ''
    expiry.value = ''
    cvv.value = ''
    errors.value = {}
  },
  focus: () => cardNumberRef.value?.focus()
})
</script>

<style scoped>
.preserve-3d {
  transform-style: preserve-3d;
}

.backface-hidden {
  backface-visibility: hidden;
}

.rotate-y-180 {
  transform: rotateY(180deg);
}
</style>
