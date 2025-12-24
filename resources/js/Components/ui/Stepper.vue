<template>
  <nav :class="['relative', containerClasses]" aria-label="Progress">
    <!-- Horizontal Layout -->
    <ol v-if="!vertical" class="flex items-center">
      <li
        v-for="(step, index) in steps"
        :key="step.id || index"
        :class="[
          'relative',
          index !== steps.length - 1 ? 'flex-1' : ''
        ]"
      >
        <!-- Connector Line -->
        <div
          v-if="index !== steps.length - 1"
          class="absolute top-4 left-8 right-0 -translate-y-1/2"
          :class="[
            variant === 'dots' ? 'left-4' : 'left-8',
            sizeClasses.connector
          ]"
        >
          <div :class="['w-full', connectorClasses]">
            <div
              class="h-full transition-all duration-500"
              :class="[
                index < currentStep ? colorClasses.connectorComplete : colorClasses.connectorIncomplete
              ]"
              :style="{ width: getConnectorWidth(index) }"
            />
          </div>
        </div>

        <!-- Step -->
        <button
          type="button"
          :class="[
            'relative flex items-center gap-3 group',
            clickable ? 'cursor-pointer' : 'cursor-default'
          ]"
          :disabled="!clickable || (!allowSkip && index > currentStep)"
          @click="handleStepClick(index)"
        >
          <!-- Step Indicator -->
          <span
            :class="[
              'flex items-center justify-center rounded-full transition-all duration-300',
              sizeClasses.indicator,
              getIndicatorClasses(index)
            ]"
          >
            <!-- Completed Icon -->
            <CheckIcon
              v-if="index < currentStep"
              :class="['text-white', sizeClasses.icon]"
            />
            <!-- Current/Future Number or Icon -->
            <template v-else>
              <component
                v-if="step.icon"
                :is="step.icon"
                :class="[
                  sizeClasses.icon,
                  index === currentStep ? colorClasses.iconActive : 'text-gray-400'
                ]"
              />
              <span
                v-else
                :class="[
                  'font-semibold',
                  sizeClasses.number,
                  index === currentStep ? colorClasses.textActive : 'text-gray-500 dark:text-gray-400'
                ]"
              >
                {{ index + 1 }}
              </span>
            </template>

            <!-- Pulse Animation for Current Step -->
            <span
              v-if="index === currentStep && showPulse"
              class="absolute inset-0 rounded-full animate-ping opacity-25"
              :class="colorClasses.pulse"
            />
          </span>

          <!-- Step Content -->
          <div v-if="variant !== 'dots'" class="min-w-0">
            <p
              :class="[
                'font-medium transition-colors',
                sizeClasses.title,
                index <= currentStep ? colorClasses.textActive : 'text-gray-500 dark:text-gray-400'
              ]"
            >
              {{ step.title }}
            </p>
            <p
              v-if="step.description && showDescription"
              :class="[
                'mt-0.5 text-gray-500 dark:text-gray-400',
                sizeClasses.description
              ]"
            >
              {{ step.description }}
            </p>
          </div>
        </button>
      </li>
    </ol>

    <!-- Vertical Layout -->
    <ol v-else class="relative">
      <li
        v-for="(step, index) in steps"
        :key="step.id || index"
        :class="[
          'relative',
          index !== steps.length - 1 ? 'pb-8' : ''
        ]"
      >
        <!-- Connector Line -->
        <div
          v-if="index !== steps.length - 1"
          class="absolute left-4 top-8 bottom-0 -translate-x-1/2"
          :class="sizeClasses.verticalConnector"
        >
          <div :class="['h-full w-full rounded-full', connectorClasses]">
            <div
              class="w-full transition-all duration-500"
              :class="[
                index < currentStep ? colorClasses.connectorComplete : colorClasses.connectorIncomplete
              ]"
              :style="{ height: getConnectorWidth(index) }"
            />
          </div>
        </div>

        <!-- Step -->
        <button
          type="button"
          :class="[
            'relative flex items-start gap-4 group w-full text-left',
            clickable ? 'cursor-pointer' : 'cursor-default'
          ]"
          :disabled="!clickable || (!allowSkip && index > currentStep)"
          @click="handleStepClick(index)"
        >
          <!-- Step Indicator -->
          <span
            :class="[
              'relative flex items-center justify-center rounded-full transition-all duration-300 flex-shrink-0',
              sizeClasses.indicator,
              getIndicatorClasses(index)
            ]"
          >
            <CheckIcon
              v-if="index < currentStep"
              :class="['text-white', sizeClasses.icon]"
            />
            <template v-else>
              <component
                v-if="step.icon"
                :is="step.icon"
                :class="[
                  sizeClasses.icon,
                  index === currentStep ? colorClasses.iconActive : 'text-gray-400'
                ]"
              />
              <span
                v-else
                :class="[
                  'font-semibold',
                  sizeClasses.number,
                  index === currentStep ? colorClasses.textActive : 'text-gray-500 dark:text-gray-400'
                ]"
              >
                {{ index + 1 }}
              </span>
            </template>
          </span>

          <!-- Step Content -->
          <div class="flex-1 min-w-0 pt-0.5">
            <p
              :class="[
                'font-medium transition-colors',
                sizeClasses.title,
                index <= currentStep ? colorClasses.textActive : 'text-gray-500 dark:text-gray-400'
              ]"
            >
              {{ step.title }}
            </p>
            <p
              v-if="step.description && showDescription"
              :class="[
                'mt-1 text-gray-500 dark:text-gray-400',
                sizeClasses.description
              ]"
            >
              {{ step.description }}
            </p>

            <!-- Step Content Slot -->
            <div v-if="index === currentStep && $slots.content" class="mt-4">
              <slot name="content" :step="step" :index="index" />
            </div>
          </div>
        </button>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { CheckIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  // Steps array
  steps: {
    type: Array,
    required: true
    // { id?, title, description?, icon?, completed? }
  },
  // Current step index (0-based)
  currentStep: {
    type: Number,
    default: 0
  },
  // Layout
  vertical: {
    type: Boolean,
    default: false
  },
  // Variant
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'dots', 'simple'].includes(v)
  },
  // Size
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  // Color
  color: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'success', 'blue', 'purple', 'orange'].includes(v)
  },
  // Clickable steps
  clickable: {
    type: Boolean,
    default: false
  },
  // Allow skipping to future steps
  allowSkip: {
    type: Boolean,
    default: false
  },
  // Show description
  showDescription: {
    type: Boolean,
    default: true
  },
  // Show pulse animation on current step
  showPulse: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:currentStep', 'step-click'])

// Container classes
const containerClasses = computed(() => {
  if (props.vertical) {
    return ''
  }
  return 'overflow-hidden'
})

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: {
      indicator: 'w-6 h-6',
      icon: 'w-3 h-3',
      number: 'text-xs',
      title: 'text-sm',
      description: 'text-xs',
      connector: 'h-0.5',
      verticalConnector: 'w-0.5'
    },
    md: {
      indicator: 'w-8 h-8',
      icon: 'w-4 h-4',
      number: 'text-sm',
      title: 'text-sm',
      description: 'text-xs',
      connector: 'h-0.5',
      verticalConnector: 'w-0.5'
    },
    lg: {
      indicator: 'w-10 h-10',
      icon: 'w-5 h-5',
      number: 'text-base',
      title: 'text-base',
      description: 'text-sm',
      connector: 'h-1',
      verticalConnector: 'w-1'
    }
  }
  return sizes[props.size]
})

// Connector classes
const connectorClasses = computed(() => {
  return 'bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden'
})

// Color classes
const colorClasses = computed(() => {
  const colors = {
    primary: {
      indicatorComplete: 'bg-primary-600',
      indicatorActive: 'border-2 border-primary-600 bg-white dark:bg-gray-900',
      connectorComplete: 'bg-primary-600',
      connectorIncomplete: 'bg-gray-200 dark:bg-gray-700',
      textActive: 'text-gray-900 dark:text-white',
      iconActive: 'text-primary-600',
      pulse: 'bg-primary-500'
    },
    success: {
      indicatorComplete: 'bg-green-600',
      indicatorActive: 'border-2 border-green-600 bg-white dark:bg-gray-900',
      connectorComplete: 'bg-green-600',
      connectorIncomplete: 'bg-gray-200 dark:bg-gray-700',
      textActive: 'text-gray-900 dark:text-white',
      iconActive: 'text-green-600',
      pulse: 'bg-green-500'
    },
    blue: {
      indicatorComplete: 'bg-blue-600',
      indicatorActive: 'border-2 border-blue-600 bg-white dark:bg-gray-900',
      connectorComplete: 'bg-blue-600',
      connectorIncomplete: 'bg-gray-200 dark:bg-gray-700',
      textActive: 'text-gray-900 dark:text-white',
      iconActive: 'text-blue-600',
      pulse: 'bg-blue-500'
    },
    purple: {
      indicatorComplete: 'bg-purple-600',
      indicatorActive: 'border-2 border-purple-600 bg-white dark:bg-gray-900',
      connectorComplete: 'bg-purple-600',
      connectorIncomplete: 'bg-gray-200 dark:bg-gray-700',
      textActive: 'text-gray-900 dark:text-white',
      iconActive: 'text-purple-600',
      pulse: 'bg-purple-500'
    },
    orange: {
      indicatorComplete: 'bg-orange-600',
      indicatorActive: 'border-2 border-orange-600 bg-white dark:bg-gray-900',
      connectorComplete: 'bg-orange-600',
      connectorIncomplete: 'bg-gray-200 dark:bg-gray-700',
      textActive: 'text-gray-900 dark:text-white',
      iconActive: 'text-orange-600',
      pulse: 'bg-orange-500'
    }
  }
  return colors[props.color]
})

// Get indicator classes based on step state
function getIndicatorClasses(index) {
  if (index < props.currentStep) {
    return colorClasses.value.indicatorComplete
  }
  if (index === props.currentStep) {
    return colorClasses.value.indicatorActive
  }
  return 'border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'
}

// Get connector fill width
function getConnectorWidth(index) {
  if (index < props.currentStep) return '100%'
  if (index === props.currentStep) return '0%'
  return '0%'
}

// Handle step click
function handleStepClick(index) {
  if (!props.clickable) return
  if (!props.allowSkip && index > props.currentStep) return
  
  emit('update:currentStep', index)
  emit('step-click', { index, step: props.steps[index] })
}
</script>
