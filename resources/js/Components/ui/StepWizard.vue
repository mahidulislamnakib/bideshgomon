<template>
    <div :class="['step-wizard', containerClass]">
        <!-- Step Indicators -->
        <nav
            :class="[
                'flex items-center mb-8',
                vertical ? 'flex-col items-start space-y-4' : 'justify-between'
            ]"
            aria-label="Progress"
        >
            <ol
                :class="[
                    'flex items-center',
                    vertical ? 'flex-col items-start space-y-4 w-full' : 'w-full'
                ]"
            >
                <li
                    v-for="(step, index) in steps"
                    :key="step.id || index"
                    :class="[
                        'relative flex items-center',
                        vertical ? 'w-full' : 'flex-1',
                        index === steps.length - 1 ? '' : (vertical ? '' : 'pr-8 sm:pr-20')
                    ]"
                >
                    <!-- Connector Line (horizontal) -->
                    <div
                        v-if="!vertical && index !== steps.length - 1"
                        :class="[
                            'absolute top-4 left-7 -right-3 h-0.5',
                            index < currentStep ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'
                        ]"
                        aria-hidden="true"
                    />
                    
                    <!-- Step Button -->
                    <button
                        type="button"
                        :disabled="!clickable || (linear && index > highestStep)"
                        @click="goToStep(index)"
                        :class="[
                            'group relative flex items-center',
                            vertical ? 'w-full' : '',
                            clickable && (!linear || index <= highestStep) ? 'cursor-pointer' : 'cursor-default'
                        ]"
                    >
                        <!-- Step Circle -->
                        <span
                            :class="[
                                'relative z-10 flex items-center justify-center rounded-full transition-all duration-200',
                                sizeClasses.circle,
                                getStepClasses(index)
                            ]"
                        >
                            <!-- Completed Check -->
                            <CheckIcon
                                v-if="index < currentStep"
                                :class="sizeClasses.icon"
                            />
                            <!-- Current/Future Number or Icon -->
                            <component
                                v-else-if="step.icon"
                                :is="step.icon"
                                :class="sizeClasses.icon"
                            />
                            <span v-else class="font-semibold">
                                {{ index + 1 }}
                            </span>
                        </span>
                        
                        <!-- Step Label -->
                        <span :class="['ml-3 flex flex-col', vertical ? 'flex-1' : '']">
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    index === currentStep 
                                        ? 'text-primary-600 dark:text-primary-400' 
                                        : index < currentStep 
                                            ? 'text-gray-900 dark:text-white' 
                                            : 'text-gray-500 dark:text-gray-400'
                                ]"
                            >
                                {{ step.title }}
                            </span>
                            <span
                                v-if="step.description"
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ step.description }}
                            </span>
                        </span>
                        
                        <!-- Error Indicator -->
                        <ExclamationCircleIcon
                            v-if="step.hasError"
                            class="ml-2 h-5 w-5 text-red-500"
                        />
                    </button>
                    
                    <!-- Connector Line (vertical) -->
                    <div
                        v-if="vertical && index !== steps.length - 1"
                        :class="[
                            'absolute left-4 top-8 bottom-0 w-0.5 -mb-4',
                            index < currentStep ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'
                        ]"
                        aria-hidden="true"
                    />
                </li>
            </ol>
        </nav>
        
        <!-- Step Content -->
        <div :class="['step-content', contentClass]">
            <TransitionGroup
                v-if="animated"
                :name="transitionName"
                tag="div"
                class="relative"
            >
                <div
                    v-for="(step, index) in steps"
                    v-show="index === currentStep"
                    :key="step.id || index"
                    class="step-panel"
                >
                    <slot :name="`step-${index + 1}`" :step="step" :index="index">
                        <slot :name="`step-${step.id}`" :step="step" :index="index">
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                Content for step {{ index + 1 }}
                            </div>
                        </slot>
                    </slot>
                </div>
            </TransitionGroup>
            
            <div v-else>
                <div
                    v-for="(step, index) in steps"
                    v-show="index === currentStep"
                    :key="step.id || index"
                    class="step-panel"
                >
                    <slot :name="`step-${index + 1}`" :step="step" :index="index">
                        <slot :name="`step-${step.id}`" :step="step" :index="index">
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                Content for step {{ index + 1 }}
                            </div>
                        </slot>
                    </slot>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div
            v-if="showNavigation"
            :class="[
                'flex items-center mt-8 pt-6 border-t border-gray-200 dark:border-gray-700',
                currentStep === 0 ? 'justify-end' : 'justify-between'
            ]"
        >
            <!-- Previous Button -->
            <button
                v-if="currentStep > 0"
                type="button"
                @click="prev"
                :disabled="prevDisabled"
                :class="[
                    'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium',
                    'border border-gray-300 dark:border-gray-600',
                    'text-gray-700 dark:text-gray-200',
                    'hover:bg-gray-50 dark:hover:bg-gray-800',
                    'disabled:opacity-50 disabled:cursor-not-allowed',
                    'transition-colors duration-200'
                ]"
            >
                <ArrowLeftIcon class="h-4 w-4" />
                {{ prevLabel }}
            </button>
            
            <div class="flex items-center gap-3">
                <!-- Skip Button (optional) -->
                <button
                    v-if="skippable && currentStep < steps.length - 1"
                    type="button"
                    @click="skip"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline-offset-2 hover:underline"
                >
                    {{ skipLabel }}
                </button>
                
                <!-- Next/Submit Button -->
                <button
                    type="button"
                    @click="isLastStep ? submit() : next()"
                    :disabled="nextDisabled || (isLastStep && submitDisabled)"
                    :class="[
                        'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium',
                        'bg-primary-600 text-white',
                        'hover:bg-primary-700',
                        'disabled:opacity-50 disabled:cursor-not-allowed',
                        'transition-colors duration-200'
                    ]"
                >
                    {{ isLastStep ? submitLabel : nextLabel }}
                    <ArrowRightIcon v-if="!isLastStep" class="h-4 w-4" />
                    <CheckIcon v-else class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { 
    CheckIcon, 
    ArrowLeftIcon, 
    ArrowRightIcon,
    ExclamationCircleIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    // Array of step objects: { id?, title, description?, icon?, hasError? }
    steps: {
        type: Array,
        required: true
    },
    // Current step index (v-model)
    modelValue: {
        type: Number,
        default: 0
    },
    // Vertical layout
    vertical: {
        type: Boolean,
        default: false
    },
    // Allow clicking on steps to navigate
    clickable: {
        type: Boolean,
        default: true
    },
    // Linear mode: can only go to completed steps
    linear: {
        type: Boolean,
        default: false
    },
    // Allow skipping steps
    skippable: {
        type: Boolean,
        default: false
    },
    // Show navigation buttons
    showNavigation: {
        type: Boolean,
        default: true
    },
    // Enable animations
    animated: {
        type: Boolean,
        default: true
    },
    // Size variant
    size: {
        type: String,
        default: 'md',
        validator: (val) => ['sm', 'md', 'lg'].includes(val)
    },
    // Button labels
    prevLabel: {
        type: String,
        default: 'Previous'
    },
    nextLabel: {
        type: String,
        default: 'Next'
    },
    skipLabel: {
        type: String,
        default: 'Skip this step'
    },
    submitLabel: {
        type: String,
        default: 'Submit'
    },
    // Disabled states
    prevDisabled: {
        type: Boolean,
        default: false
    },
    nextDisabled: {
        type: Boolean,
        default: false
    },
    submitDisabled: {
        type: Boolean,
        default: false
    },
    // CSS classes
    containerClass: {
        type: String,
        default: ''
    },
    contentClass: {
        type: String,
        default: ''
    }
});

const emit = defineEmits([
    'update:modelValue',
    'change',
    'next',
    'prev',
    'skip',
    'submit',
    'complete'
]);

const currentStep = ref(props.modelValue);
const highestStep = ref(props.modelValue);
const direction = ref('forward');

// Sync with v-model
watch(() => props.modelValue, (val) => {
    if (val !== currentStep.value) {
        direction.value = val > currentStep.value ? 'forward' : 'backward';
        currentStep.value = val;
        if (val > highestStep.value) {
            highestStep.value = val;
        }
    }
});

const isLastStep = computed(() => currentStep.value === props.steps.length - 1);
const isFirstStep = computed(() => currentStep.value === 0);

const sizeClasses = computed(() => {
    const sizes = {
        sm: { circle: 'h-8 w-8 text-sm', icon: 'h-4 w-4' },
        md: { circle: 'h-10 w-10 text-base', icon: 'h-5 w-5' },
        lg: { circle: 'h-12 w-12 text-lg', icon: 'h-6 w-6' }
    };
    return sizes[props.size] || sizes.md;
});

const transitionName = computed(() => {
    return direction.value === 'forward' ? 'slide-left' : 'slide-right';
});

function getStepClasses(index) {
    if (index < currentStep.value) {
        // Completed
        return 'bg-primary-600 text-white';
    } else if (index === currentStep.value) {
        // Current
        return 'bg-primary-600 text-white ring-4 ring-primary-100 dark:ring-primary-900';
    } else {
        // Future
        return 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400';
    }
}

function goToStep(index) {
    if (!props.clickable) return;
    if (props.linear && index > highestStep.value) return;
    
    direction.value = index > currentStep.value ? 'forward' : 'backward';
    currentStep.value = index;
    emit('update:modelValue', index);
    emit('change', { step: index, direction: direction.value });
}

function next() {
    if (currentStep.value < props.steps.length - 1) {
        const prevStep = currentStep.value;
        currentStep.value++;
        direction.value = 'forward';
        
        if (currentStep.value > highestStep.value) {
            highestStep.value = currentStep.value;
        }
        
        emit('update:modelValue', currentStep.value);
        emit('next', { from: prevStep, to: currentStep.value });
        emit('change', { step: currentStep.value, direction: 'forward' });
    }
}

function prev() {
    if (currentStep.value > 0) {
        const prevStep = currentStep.value;
        currentStep.value--;
        direction.value = 'backward';
        
        emit('update:modelValue', currentStep.value);
        emit('prev', { from: prevStep, to: currentStep.value });
        emit('change', { step: currentStep.value, direction: 'backward' });
    }
}

function skip() {
    emit('skip', currentStep.value);
    next();
}

function submit() {
    emit('submit');
    emit('complete');
}

// Expose methods
defineExpose({
    next,
    prev,
    goToStep,
    currentStep,
    isLastStep,
    isFirstStep
});
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

.slide-left-leave-active,
.slide-right-leave-active {
    position: absolute;
    width: 100%;
}
</style>
