<template>
  <div class="w-full">
    <!-- Password Input -->
    <div class="relative">
      <input
        :type="showPassword ? 'text' : 'password'"
        :value="modelValue"
        @input="handleInput"
        :placeholder="placeholder"
        :disabled="disabled"
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white pr-20"
        :class="{
          'border-red-500 focus:border-red-500 focus:ring-red-500': hasError,
          'opacity-60 cursor-not-allowed': disabled
        }"
      />
      
      <!-- Toggle visibility & Generate buttons -->
      <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-3">
        <!-- Generate random password -->
        <button
          v-if="showGenerator"
          type="button"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
          @click="generatePassword"
          title="Generate strong password"
        >
          <SparklesIcon class="w-5 h-5" />
        </button>
        
        <!-- Toggle visibility -->
        <button
          type="button"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
          @click="toggleVisibility"
          :title="showPassword ? 'Hide password' : 'Show password'"
        >
          <EyeSlashIcon v-if="showPassword" class="w-5 h-5" />
          <EyeIcon v-else class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Strength Meter Bar -->
    <div v-if="showMeter" class="mt-2">
      <div class="flex gap-1">
        <div
          v-for="i in 4"
          :key="i"
          class="h-1.5 flex-1 rounded-full transition-all duration-300"
          :class="getBarClass(i)"
        />
      </div>
      
      <!-- Strength Label -->
      <div class="flex justify-between items-center mt-1">
        <span 
          v-if="modelValue"
          class="text-xs font-medium"
          :class="strengthColorClass"
        >
          {{ strengthLabel }}
        </span>
        <span v-else class="text-xs text-gray-400">
          Enter a password
        </span>
        
        <!-- Score badge -->
        <span 
          v-if="showScore && modelValue"
          class="text-xs px-1.5 py-0.5 rounded-full"
          :class="scoreBadgeClass"
        >
          {{ score }}/4
        </span>
      </div>
    </div>

    <!-- Requirements List -->
    <div v-if="showRequirements && modelValue" class="mt-3 space-y-1">
      <div
        v-for="(req, key) in requirements"
        :key="key"
        class="flex items-center gap-2 text-xs"
      >
        <CheckCircleIcon
          v-if="req.met"
          class="w-4 h-4 text-green-500 flex-shrink-0"
        />
        <XCircleIcon
          v-else
          class="w-4 h-4 text-gray-300 dark:text-gray-600 flex-shrink-0"
        />
        <span :class="req.met ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
          {{ req.label }}
        </span>
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { 
  EyeIcon, 
  EyeSlashIcon, 
  SparklesIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Password value
  modelValue: {
    type: String,
    default: ''
  },
  // Placeholder text
  placeholder: {
    type: String,
    default: 'Enter password'
  },
  // Minimum length
  minLength: {
    type: Number,
    default: 8
  },
  // Show strength meter
  showMeter: {
    type: Boolean,
    default: true
  },
  // Show requirements checklist
  showRequirements: {
    type: Boolean,
    default: true
  },
  // Show numeric score
  showScore: {
    type: Boolean,
    default: false
  },
  // Show generate button
  showGenerator: {
    type: Boolean,
    default: true
  },
  // Disabled state
  disabled: {
    type: Boolean,
    default: false
  },
  // Error message
  error: {
    type: String,
    default: ''
  },
  // Require uppercase
  requireUppercase: {
    type: Boolean,
    default: true
  },
  // Require lowercase
  requireLowercase: {
    type: Boolean,
    default: true
  },
  // Require number
  requireNumber: {
    type: Boolean,
    default: true
  },
  // Require special character
  requireSpecial: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'strength-change'])

// Local state
const showPassword = ref(false)

// Has error
const hasError = computed(() => !!props.error)

// Check individual requirements
const requirements = computed(() => {
  const password = props.modelValue
  const reqs = {}
  
  reqs.length = {
    label: `At least ${props.minLength} characters`,
    met: password.length >= props.minLength
  }
  
  if (props.requireUppercase) {
    reqs.uppercase = {
      label: 'Contains uppercase letter',
      met: /[A-Z]/.test(password)
    }
  }
  
  if (props.requireLowercase) {
    reqs.lowercase = {
      label: 'Contains lowercase letter',
      met: /[a-z]/.test(password)
    }
  }
  
  if (props.requireNumber) {
    reqs.number = {
      label: 'Contains number',
      met: /[0-9]/.test(password)
    }
  }
  
  if (props.requireSpecial) {
    reqs.special = {
      label: 'Contains special character (!@#$%^&*)',
      met: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    }
  }
  
  return reqs
})

// Calculate strength score (0-4)
const score = computed(() => {
  if (!props.modelValue) return 0
  
  let strength = 0
  const password = props.modelValue
  
  // Length check
  if (password.length >= props.minLength) strength++
  if (password.length >= props.minLength + 4) strength++
  
  // Character variety
  let varieties = 0
  if (/[a-z]/.test(password)) varieties++
  if (/[A-Z]/.test(password)) varieties++
  if (/[0-9]/.test(password)) varieties++
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) varieties++
  
  if (varieties >= 3) strength++
  if (varieties >= 4) strength++
  
  // Cap at 4
  return Math.min(4, strength)
})

// Strength label
const strengthLabel = computed(() => {
  const labels = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong']
  return labels[score.value]
})

// Strength color class
const strengthColorClass = computed(() => {
  const colors = [
    'text-red-600 dark:text-red-400',
    'text-orange-600 dark:text-orange-400',
    'text-yellow-600 dark:text-yellow-400',
    'text-blue-600 dark:text-blue-400',
    'text-green-600 dark:text-green-400'
  ]
  return colors[score.value]
})

// Score badge class
const scoreBadgeClass = computed(() => {
  const classes = [
    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
  ]
  return classes[score.value]
})

// Get bar class based on position and score
function getBarClass(position) {
  if (!props.modelValue) {
    return 'bg-gray-200 dark:bg-gray-700'
  }
  
  if (position <= score.value) {
    const colors = [
      'bg-red-500',
      'bg-orange-500',
      'bg-yellow-500',
      'bg-green-500'
    ]
    return colors[score.value - 1] || 'bg-gray-200 dark:bg-gray-700'
  }
  
  return 'bg-gray-200 dark:bg-gray-700'
}

// Handle input
function handleInput(event) {
  emit('update:modelValue', event.target.value)
}

// Toggle password visibility
function toggleVisibility() {
  showPassword.value = !showPassword.value
}

// Generate strong password
function generatePassword() {
  const length = Math.max(16, props.minLength)
  const lowercase = 'abcdefghijklmnopqrstuvwxyz'
  const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
  const numbers = '0123456789'
  const special = '!@#$%^&*'
  
  const allChars = lowercase + uppercase + numbers + special
  
  // Ensure at least one of each type
  let password = ''
  password += lowercase[Math.floor(Math.random() * lowercase.length)]
  password += uppercase[Math.floor(Math.random() * uppercase.length)]
  password += numbers[Math.floor(Math.random() * numbers.length)]
  password += special[Math.floor(Math.random() * special.length)]
  
  // Fill rest randomly
  for (let i = password.length; i < length; i++) {
    password += allChars[Math.floor(Math.random() * allChars.length)]
  }
  
  // Shuffle
  password = password.split('').sort(() => Math.random() - 0.5).join('')
  
  emit('update:modelValue', password)
}

// Watch score changes
watch(score, (newScore) => {
  emit('strength-change', {
    score: newScore,
    label: strengthLabel.value,
    isValid: Object.values(requirements.value).every(r => r.met)
  })
})
</script>
