<template>
  <div :class="['form-wizard', containerClasses]">
    <!-- Progress Header -->
    <div v-if="showProgress" :class="['px-6 py-4', themeClasses.header]">
      <!-- Step Indicators -->
      <div class="flex items-center justify-between relative">
        <!-- Progress Line -->
        <div class="absolute left-0 right-0 top-1/2 h-0.5 -translate-y-1/2 mx-8">
          <div :class="['h-full', themeClasses.progressBg]" />
          <div
            :class="['absolute inset-y-0 left-0 transition-all duration-500', themeClasses.progressFill]"
            :style="{ width: `${progressPercent}%` }"
          />
        </div>

        <!-- Steps -->
        <div
          v-for="(step, index) in steps"
          :key="index"
          :class="[
            'relative flex flex-col items-center cursor-pointer group z-10',
            !clickableSteps && 'cursor-default'
          ]"
          @click="clickableSteps && goToStep(index)"
        >
          <!-- Step Circle -->
          <div
            :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-all duration-300',
              getStepClasses(index)
            ]"
          >
            <CheckIcon v-if="isStepCompleted(index)" class="w-5 h-5" />
            <component v-else-if="step.icon" :is="step.icon" class="w-5 h-5" />
            <span v-else>{{ index + 1 }}</span>
          </div>

          <!-- Step Label -->
          <div
            v-if="showLabels"
            :class="[
              'mt-2 text-xs font-medium text-center max-w-20 truncate transition-colors',
              index === currentStep ? themeClasses.labelActive : themeClasses.label
            ]"
          >
            {{ step.title }}
          </div>
        </div>
      </div>

      <!-- Current Step Title -->
      <div v-if="showTitle" class="mt-6 text-center">
        <h2 :class="['text-xl font-semibold', themeClasses.title]">
          {{ steps[currentStep]?.title }}
        </h2>
        <p v-if="steps[currentStep]?.description" :class="['mt-1 text-sm', themeClasses.description]">
          {{ steps[currentStep]?.description }}
        </p>
      </div>
    </div>

    <!-- Step Content -->
    <div :class="['px-6 py-6', themeClasses.content]">
      <Transition
        :name="transitionName"
        mode="out-in"
      >
        <div :key="currentStep">
          <slot :name="`step-${currentStep + 1}`" :step="steps[currentStep]" :data="formData">
            <!-- Default slot content -->
            <div class="text-center text-gray-500 py-8">
              Step {{ currentStep + 1 }} content
            </div>
          </slot>
        </div>
      </Transition>
    </div>

    <!-- Navigation Footer -->
    <div
      v-if="showNavigation"
      :class="['px-6 py-4 flex items-center justify-between border-t', themeClasses.footer]"
    >
      <button
        type="button"
        :disabled="currentStep === 0 || loading"
        :class="[
          'px-5 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2',
          currentStep === 0 || loading
            ? 'opacity-50 cursor-not-allowed'
            : '',
          themeClasses.buttonSecondary
        ]"
        @click="prevStep"
      >
        <ChevronLeftIcon class="w-4 h-4" />
        {{ backLabel }}
      </button>

      <div class="flex items-center gap-3">
        <!-- Skip Button (optional) -->
        <button
          v-if="steps[currentStep]?.skippable && !isLastStep"
          type="button"
          :disabled="loading"
          :class="['px-4 py-2 text-sm transition-colors', themeClasses.skipButton]"
          @click="skipStep"
        >
          Skip
        </button>

        <!-- Next/Submit Button -->
        <button
          type="button"
          :disabled="!canProceed || loading"
          :class="[
            'px-5 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2',
            !canProceed || loading ? 'opacity-50 cursor-not-allowed' : '',
            themeClasses.buttonPrimary
          ]"
          @click="isLastStep ? submit() : nextStep()"
        >
          <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
          <template v-else>
            {{ isLastStep ? submitLabel : nextLabel }}
            <ChevronRightIcon v-if="!isLastStep" class="w-4 h-4" />
            <CheckIcon v-else class="w-4 h-4" />
          </template>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, provide } from 'vue'
import { CheckIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  steps: {
    type: Array,
    required: true,
    // Each step: { title, description?, icon?, skippable?, validation?: () => boolean }
  },
  modelValue: {
    type: Number,
    default: 0
  },
  formData: {
    type: Object,
    default: () => ({})
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  color: {
    type: String,
    default: 'blue',
    validator: v => ['blue', 'green', 'purple', 'orange', 'red'].includes(v)
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  showTitle: {
    type: Boolean,
    default: true
  },
  showNavigation: {
    type: Boolean,
    default: true
  },
  clickableSteps: {
    type: Boolean,
    default: false
  },
  linear: {
    type: Boolean,
    default: true
  },
  backLabel: {
    type: String,
    default: 'Back'
  },
  nextLabel: {
    type: String,
    default: 'Continue'
  },
  submitLabel: {
    type: String,
    default: 'Submit'
  },
  loading: {
    type: Boolean,
    default: false
  },
  transition: {
    type: String,
    default: 'slide',
    validator: v => ['slide', 'fade', 'none'].includes(v)
  }
})

const emit = defineEmits(['update:modelValue', 'next', 'prev', 'skip', 'submit', 'step-change', 'complete'])

const currentStep = ref(props.modelValue)
const completedSteps = ref(new Set())
const direction = ref('forward')

const containerClasses = computed(() => {
  return props.theme === 'dark'
    ? 'bg-gray-900 rounded-xl border border-gray-700'
    : 'bg-white rounded-xl border border-gray-200 shadow-sm'
})

const colorClasses = {
  blue: {
    fill: 'bg-blue-500',
    active: 'bg-blue-500 text-white shadow-lg shadow-blue-500/30',
    completed: 'bg-blue-500 text-white',
    buttonPrimary: 'bg-blue-500 text-white hover:bg-blue-600'
  },
  green: {
    fill: 'bg-green-500',
    active: 'bg-green-500 text-white shadow-lg shadow-green-500/30',
    completed: 'bg-green-500 text-white',
    buttonPrimary: 'bg-green-500 text-white hover:bg-green-600'
  },
  purple: {
    fill: 'bg-purple-500',
    active: 'bg-purple-500 text-white shadow-lg shadow-purple-500/30',
    completed: 'bg-purple-500 text-white',
    buttonPrimary: 'bg-purple-500 text-white hover:bg-purple-600'
  },
  orange: {
    fill: 'bg-orange-500',
    active: 'bg-orange-500 text-white shadow-lg shadow-orange-500/30',
    completed: 'bg-orange-500 text-white',
    buttonPrimary: 'bg-orange-500 text-white hover:bg-orange-600'
  },
  red: {
    fill: 'bg-red-500',
    active: 'bg-red-500 text-white shadow-lg shadow-red-500/30',
    completed: 'bg-red-500 text-white',
    buttonPrimary: 'bg-red-500 text-white hover:bg-red-600'
  }
}

const themeClasses = computed(() => {
  const colors = colorClasses[props.color]
  
  if (props.theme === 'dark') {
    return {
      header: 'border-b border-gray-700',
      progressBg: 'bg-gray-700',
      progressFill: colors.fill,
      stepInactive: 'bg-gray-700 text-gray-400',
      stepActive: colors.active,
      stepCompleted: colors.completed,
      label: 'text-gray-500',
      labelActive: 'text-white',
      title: 'text-white',
      description: 'text-gray-400',
      content: '',
      footer: 'border-gray-700',
      buttonPrimary: colors.buttonPrimary,
      buttonSecondary: 'text-gray-300 hover:bg-gray-800',
      skipButton: 'text-gray-400 hover:text-white'
    }
  }
  
  return {
    header: 'border-b border-gray-100',
    progressBg: 'bg-gray-200',
    progressFill: colors.fill,
    stepInactive: 'bg-gray-200 text-gray-500',
    stepActive: colors.active,
    stepCompleted: colors.completed,
    label: 'text-gray-500',
    labelActive: 'text-gray-900',
    title: 'text-gray-900',
    description: 'text-gray-500',
    content: '',
    footer: 'border-gray-200',
    buttonPrimary: colors.buttonPrimary,
    buttonSecondary: 'text-gray-600 hover:bg-gray-100',
    skipButton: 'text-gray-500 hover:text-gray-700'
  }
})

const transitionName = computed(() => {
  if (props.transition === 'none') return ''
  if (props.transition === 'fade') return 'fade'
  return direction.value === 'forward' ? 'slide-left' : 'slide-right'
})

const isLastStep = computed(() => currentStep.value === props.steps.length - 1)

const progressPercent = computed(() => {
  return (currentStep.value / (props.steps.length - 1)) * 100
})

const canProceed = computed(() => {
  const step = props.steps[currentStep.value]
  if (step?.validation && typeof step.validation === 'function') {
    return step.validation(props.formData)
  }
  return true
})

const isStepCompleted = (index) => {
  return completedSteps.value.has(index) || index < currentStep.value
}

const getStepClasses = (index) => {
  if (isStepCompleted(index)) return themeClasses.value.stepCompleted
  if (index === currentStep.value) return themeClasses.value.stepActive
  return themeClasses.value.stepInactive
}

const goToStep = (index) => {
  if (props.linear && index > currentStep.value && !isStepCompleted(index - 1)) return
  
  direction.value = index > currentStep.value ? 'forward' : 'backward'
  currentStep.value = index
  emit('update:modelValue', index)
  emit('step-change', { step: index, data: props.formData })
}

const nextStep = () => {
  if (currentStep.value < props.steps.length - 1 && canProceed.value) {
    completedSteps.value.add(currentStep.value)
    direction.value = 'forward'
    currentStep.value++
    emit('update:modelValue', currentStep.value)
    emit('next', { step: currentStep.value, data: props.formData })
    emit('step-change', { step: currentStep.value, data: props.formData })
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    direction.value = 'backward'
    currentStep.value--
    emit('update:modelValue', currentStep.value)
    emit('prev', { step: currentStep.value, data: props.formData })
    emit('step-change', { step: currentStep.value, data: props.formData })
  }
}

const skipStep = () => {
  emit('skip', { step: currentStep.value, data: props.formData })
  nextStep()
}

const submit = () => {
  if (canProceed.value) {
    completedSteps.value.add(currentStep.value)
    emit('submit', props.formData)
    emit('complete', props.formData)
  }
}

const reset = () => {
  currentStep.value = 0
  completedSteps.value.clear()
  emit('update:modelValue', 0)
}

watch(() => props.modelValue, (val) => {
  if (val !== currentStep.value) {
    direction.value = val > currentStep.value ? 'forward' : 'backward'
    currentStep.value = val
  }
})

// Provide wizard context to child components
provide('formWizard', {
  currentStep,
  nextStep,
  prevStep,
  goToStep,
  formData: props.formData
})

defineExpose({
  currentStep,
  nextStep,
  prevStep,
  goToStep,
  reset,
  submit,
  isStepCompleted
})
</script>

<style scoped>
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.3s ease;
}

.slide-left-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-left-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.slide-right-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.slide-right-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
