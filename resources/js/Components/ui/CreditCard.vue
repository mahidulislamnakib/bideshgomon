<template>
  <div class="credit-card-wrapper perspective-1000">
    <div
      :class="[
        'credit-card relative w-full max-w-sm aspect-[1.586/1] rounded-2xl transition-transform duration-700 transform-style-3d cursor-pointer',
        isFlipped && 'rotate-y-180'
      ]"
      @click="flip"
    >
      <!-- Front Side -->
      <div
        :class="[
          'absolute inset-0 rounded-2xl p-6 backface-hidden overflow-hidden',
          cardGradient
        ]"
      >
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/3" />
          <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/3" />
        </div>

        <!-- Card Content -->
        <div class="relative h-full flex flex-col justify-between text-white">
          <!-- Top Row: Chip & Logo -->
          <div class="flex items-start justify-between">
            <!-- EMV Chip -->
            <div class="w-12 h-9 rounded bg-gradient-to-br from-yellow-300 to-yellow-500 relative overflow-hidden">
              <div class="absolute inset-0 grid grid-cols-3 grid-rows-3 gap-px p-1">
                <div v-for="i in 9" :key="i" class="bg-yellow-600/30 rounded-sm" />
              </div>
            </div>

            <!-- Card Brand Logo -->
            <div class="text-right">
              <component
                :is="brandLogo"
                v-if="brandLogo"
                class="h-8 w-auto"
              />
              <span v-else class="text-lg font-bold uppercase tracking-wider">
                {{ detectedBrand }}
              </span>
            </div>
          </div>

          <!-- Card Number -->
          <div class="space-y-1">
            <div class="flex gap-4 text-xl md:text-2xl font-mono tracking-wider">
              <span v-for="(group, index) in formattedNumber" :key="index">
                {{ group }}
              </span>
            </div>
          </div>

          <!-- Bottom Row: Name & Expiry -->
          <div class="flex justify-between items-end">
            <div>
              <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1">Card Holder</p>
              <p class="font-medium uppercase tracking-wide text-sm md:text-base">
                {{ cardHolder || 'YOUR NAME' }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1">Expires</p>
              <p class="font-medium tracking-wide">
                {{ formattedExpiry || 'MM/YY' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Contactless Icon -->
        <div class="absolute top-6 right-20">
          <svg class="w-8 h-8 text-white/50" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" opacity="0.3"/>
            <path d="M7.5 12c0-2.48 2.02-4.5 4.5-4.5v-2c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5v-2c-2.48 0-4.5-2.02-4.5-4.5z"/>
            <path d="M12 8.5c1.93 0 3.5 1.57 3.5 3.5s-1.57 3.5-3.5 3.5v2c3.04 0 5.5-2.46 5.5-5.5S15.04 6.5 12 6.5v2z"/>
          </svg>
        </div>
      </div>

      <!-- Back Side -->
      <div
        :class="[
          'absolute inset-0 rounded-2xl backface-hidden rotate-y-180 overflow-hidden',
          cardGradient
        ]"
      >
        <!-- Magnetic Stripe -->
        <div class="w-full h-12 bg-gray-900 mt-6" />

        <!-- Signature Strip & CVV -->
        <div class="px-6 mt-6">
          <div class="flex items-center gap-4">
            <div class="flex-1 h-10 bg-gray-100 rounded flex items-center justify-end px-3">
              <div class="w-16 h-6 bg-white rounded flex items-center justify-center italic text-gray-400 text-xs">
                Signature
              </div>
            </div>
            <div class="bg-white rounded px-3 py-2 text-center min-w-[60px]">
              <p class="text-[8px] uppercase text-gray-500 mb-0.5">CVV</p>
              <p class="font-mono text-gray-900 font-bold tracking-wider">
                {{ maskedCvv }}
              </p>
            </div>
          </div>
        </div>

        <!-- Info Text -->
        <div class="px-6 mt-6 text-white/70 text-[10px] space-y-2">
          <p>This card is property of {{ bankName }}. If found, please return to any branch.</p>
          <p>For assistance, call: {{ supportNumber }}</p>
        </div>

        <!-- Hologram -->
        <div class="absolute bottom-6 right-6 w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 via-pink-500 to-yellow-400 opacity-50 animate-pulse" />
      </div>
    </div>

    <!-- Interactive Form (Optional) -->
    <div v-if="editable" class="mt-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
        <input
          :value="modelValue.number"
          type="text"
          maxlength="19"
          placeholder="1234 5678 9012 3456"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
          @input="updateNumber($event.target.value)"
          @focus="isFlipped = false"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Card Holder</label>
        <input
          :value="modelValue.holder"
          type="text"
          placeholder="JOHN DOE"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
          @input="$emit('update:modelValue', { ...modelValue, holder: $event.target.value.toUpperCase() })"
          @focus="isFlipped = false"
        />
      </div>
      
      <div class="flex gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Expiry</label>
          <input
            :value="modelValue.expiry"
            type="text"
            maxlength="5"
            placeholder="MM/YY"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
            @input="updateExpiry($event.target.value)"
            @focus="isFlipped = false"
          />
        </div>
        <div class="w-24">
          <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
          <input
            :value="modelValue.cvv"
            type="text"
            maxlength="4"
            placeholder="123"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
            @input="updateCvv($event.target.value)"
            @focus="isFlipped = true"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      number: '',
      holder: '',
      expiry: '',
      cvv: ''
    })
  },
  editable: {
    type: Boolean,
    default: false
  },
  variant: {
    type: String,
    default: 'gradient-blue',
    validator: v => ['gradient-blue', 'gradient-purple', 'gradient-green', 'gradient-gold', 'gradient-dark', 'gradient-red'].includes(v)
  },
  bankName: {
    type: String,
    default: 'BideshGomon Bank'
  },
  supportNumber: {
    type: String,
    default: '+880-2-9999999'
  },
  brandLogo: {
    type: [Object, Function],
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'flip'])

const isFlipped = ref(false)

const cardGradient = computed(() => ({
  'gradient-blue': 'bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900',
  'gradient-purple': 'bg-gradient-to-br from-purple-600 via-purple-700 to-pink-800',
  'gradient-green': 'bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-800',
  'gradient-gold': 'bg-gradient-to-br from-amber-500 via-yellow-600 to-orange-700',
  'gradient-dark': 'bg-gradient-to-br from-gray-800 via-gray-900 to-black',
  'gradient-red': 'bg-gradient-to-br from-red-500 via-rose-600 to-pink-800'
})[props.variant])

const cardNumber = computed(() => props.modelValue.number || '')
const cardHolder = computed(() => props.modelValue.holder || '')
const cardExpiry = computed(() => props.modelValue.expiry || '')
const cardCvv = computed(() => props.modelValue.cvv || '')

const formattedNumber = computed(() => {
  const num = cardNumber.value.replace(/\s/g, '').padEnd(16, '•')
  return [
    num.slice(0, 4),
    num.slice(4, 8),
    num.slice(8, 12),
    num.slice(12, 16)
  ]
})

const formattedExpiry = computed(() => {
  return cardExpiry.value || ''
})

const maskedCvv = computed(() => {
  const cvv = cardCvv.value
  return cvv ? cvv.replace(/./g, '•') : '•••'
})

const detectedBrand = computed(() => {
  const num = cardNumber.value.replace(/\s/g, '')
  if (/^4/.test(num)) return 'VISA'
  if (/^5[1-5]/.test(num)) return 'MASTERCARD'
  if (/^3[47]/.test(num)) return 'AMEX'
  if (/^6(?:011|5)/.test(num)) return 'DISCOVER'
  return 'CARD'
})

const flip = () => {
  isFlipped.value = !isFlipped.value
  emit('flip', isFlipped.value)
}

const updateNumber = (value) => {
  // Format with spaces every 4 digits
  const cleaned = value.replace(/\D/g, '').slice(0, 16)
  const formatted = cleaned.replace(/(\d{4})(?=\d)/g, '$1 ')
  emit('update:modelValue', { ...props.modelValue, number: formatted })
}

const updateExpiry = (value) => {
  // Format as MM/YY
  const cleaned = value.replace(/\D/g, '').slice(0, 4)
  let formatted = cleaned
  if (cleaned.length >= 2) {
    formatted = cleaned.slice(0, 2) + '/' + cleaned.slice(2)
  }
  emit('update:modelValue', { ...props.modelValue, expiry: formatted })
}

const updateCvv = (value) => {
  const cleaned = value.replace(/\D/g, '').slice(0, 4)
  emit('update:modelValue', { ...props.modelValue, cvv: cleaned })
}

defineExpose({
  flip,
  isFlipped
})
</script>

<style scoped>
.perspective-1000 {
  perspective: 1000px;
}

.transform-style-3d {
  transform-style: preserve-3d;
}

.backface-hidden {
  backface-visibility: hidden;
}

.rotate-y-180 {
  transform: rotateY(180deg);
}
</style>
