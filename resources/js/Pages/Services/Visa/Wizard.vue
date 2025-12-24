<template>
    <AuthenticatedLayout>
        <Head :title="`Apply for ${country} ${visaType} Visa`" />

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link :href="route('visa.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Visa Types
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">{{ config?.country }} {{ visaTypeLabel }} Visa Application</h1>
                    <p v-if="config" class="mt-2 text-gray-600">Estimated time: {{ config.estimated_time }}</p>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <BaseSpinner class="mx-auto mb-4" />
                    <p class="text-gray-600">Loading visa requirements...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-red-900">Unable to Load Configuration</h3>
                            <p class="mt-1 text-red-700">{{ error }}</p>
                            <button @click="fetchConfig" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-growth-700">
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Wizard -->
                <div v-else-if="config" class="space-y-6">
                    <!-- Progress Stepper -->
                    <BaseStepper :steps="stepperSteps" :current-step="currentStep" @step-click="goToStep" />

                    <!-- Step Content -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                        <form @submit.prevent="handleNext">
                            <!-- Step 1-4: Dynamic Fields -->
                            <div v-if="currentStep <= 4">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ currentStepData.title }}</h2>
                                <p class="text-gray-600 mb-8">{{ currentStepData.description }}</p>

                                <div class="space-y-6">
                                    <div v-for="field in currentStepData.fields" :key="field.name" :class="field.type === 'radio' ? 'space-y-3' : ''">
                                        <!-- Text/Email/Tel/Date Input -->
                                        <div v-if="['text', 'email', 'tel', 'date', 'number'].includes(field.type)">
                                            <label :for="field.name" class="block text-sm font-medium text-gray-700 mb-2">
                                                {{ field.label }}
                                                <span v-if="field.required" class="text-red-500">*</span>
                                            </label>
                                            <input
                                                :id="field.name"
                                                v-model="form[field.name]"
                                                :type="field.type"
                                                :required="field.required"
                                                :placeholder="field.placeholder"
                                                :min="field.min"
                                                :max="field.max"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all"
                                            />
                                            <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600">{{ errors[field.name] }}</p>
                                        </div>

                                        <!-- Textarea -->
                                        <div v-else-if="field.type === 'textarea'">
                                            <label :for="field.name" class="block text-sm font-medium text-gray-700 mb-2">
                                                {{ field.label }}
                                                <span v-if="field.required" class="text-red-500">*</span>
                                            </label>
                                            <textarea
                                                :id="field.name"
                                                v-model="form[field.name]"
                                                :required="field.required"
                                                :placeholder="field.placeholder"
                                                :rows="field.rows || 4"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all"
                                            ></textarea>
                                            <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600">{{ errors[field.name] }}</p>
                                        </div>

                                        <!-- Select Dropdown -->
                                        <div v-else-if="field.type === 'select'">
                                            <label :for="field.name" class="block text-sm font-medium text-gray-700 mb-2">
                                                {{ field.label }}
                                                <span v-if="field.required" class="text-red-500">*</span>
                                            </label>
                                            <select
                                                :id="field.name"
                                                v-model="form[field.name]"
                                                :required="field.required"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all"
                                            >
                                                <option value="">Select {{ field.label }}</option>
                                                <option v-for="option in field.options" :key="option.value" :value="option.value">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                            <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600">{{ errors[field.name] }}</p>
                                        </div>

                                        <!-- Radio Buttons (for processing type) -->
                                        <div v-else-if="field.type === 'radio'">
                                            <label class="block text-sm font-medium text-gray-700 mb-4">
                                                {{ field.label }}
                                                <span v-if="field.required" class="text-red-500">*</span>
                                            </label>
                                            <div class="space-y-3">
                                                <label v-for="option in field.options" :key="option.value" class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-ocean-400 transition"
                                                    :class="form[field.name] === option.value ? 'border-ocean-500 bg-ocean-50' : 'border-gray-200'">
                                                    <input
                                                        type="radio"
                                                        :name="field.name"
                                                        :value="option.value"
                                                        v-model="form[field.name]"
                                                        :required="field.required"
                                                        class="mt-1 text-emerald-600 focus:ring-emerald-500"
                                                    />
                                                    <div class="ml-3 flex-1">
                                                        <div class="font-semibold text-gray-900">{{ option.label }}</div>
                                                        <div class="text-sm text-gray-600">{{ option.description }}</div>
                                                        <div v-if="option.price" class="text-sm font-medium text-emerald-600 mt-1">
                                                            {{ formatCurrency(option.price) }}
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <p v-if="errors[field.name]" class="mt-2 text-sm text-red-600">{{ errors[field.name] }}</p>
                                        </div>

                                        <!-- Checkbox -->
                                        <div v-else-if="field.type === 'checkbox'">
                                            <label class="flex items-start">
                                                <input
                                                    type="checkbox"
                                                    v-model="form[field.name]"
                                                    :required="field.required"
                                                    class="mt-1 text-emerald-600 focus:ring-emerald-500 rounded"
                                                />
                                                <span class="ml-3 text-sm text-gray-700">
                                                    {{ field.label }}
                                                    <span v-if="field.required" class="text-red-500">*</span>
                                                </span>
                                            </label>
                                            <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600">{{ errors[field.name] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5: Review -->
                            <div v-else>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">Review Your Application</h2>
                                <p class="text-gray-600 mb-8">Please review all information before submitting</p>

                                <div class="space-y-6">
                                    <!-- Personal Information -->
                                    <div class="border-b border-gray-200 pb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div v-for="field in config.steps[0].fields" :key="field.name">
                                                <dt class="text-sm text-gray-500">{{ field.label }}</dt>
                                                <dd class="text-base font-medium text-gray-900">{{ form[field.name] || '—' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Passport Details -->
                                    <div class="border-b border-gray-200 pb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Passport Details</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div v-for="field in config.steps[1].fields" :key="field.name">
                                                <dt class="text-sm text-gray-500">{{ field.label }}</dt>
                                                <dd class="text-base font-medium text-gray-900">{{ form[field.name] || '—' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Travel Information -->
                                    <div class="border-b border-gray-200 pb-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Travel Information</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div v-for="field in config.steps[2].fields" :key="field.name">
                                                <dt class="text-sm text-gray-500">{{ field.label }}</dt>
                                                <dd class="text-base font-medium text-gray-900">{{ formatFieldValue(field, form[field.name]) }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Processing & Fees -->
                                    <div class="bg-ocean-50 border border-ocean-200 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-ocean-900 mb-4">Fee Summary</h3>
                                        <dl class="space-y-3">
                                            <div class="flex justify-between">
                                                <dt class="text-gray-700">Government Fee</dt>
                                                <dd class="font-medium text-gray-900">{{ formatCurrency(config.fees.government_fee) }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-gray-700">Service Fee</dt>
                                                <dd class="font-medium text-gray-900">{{ formatCurrency(config.fees.service_fee) }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-gray-700">Processing Fee ({{ form.processing_type }})</dt>
                                                <dd class="font-medium text-gray-900">{{ formatCurrency(getProcessingFee()) }}</dd>
                                            </div>
                                            <div class="flex justify-between pt-3 border-t border-ocean-300">
                                                <dt class="text-lg font-bold text-ocean-900">Total Amount</dt>
                                                <dd class="text-lg font-bold text-ocean-900">{{ formatCurrency(getTotalAmount()) }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                                <button
                                    v-if="currentStep > 1"
                                    type="button"
                                    @click="goToPrevious"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium"
                                >
                                    Previous
                                </button>
                                <div v-else></div>

                                <button
                                    v-if="currentStep < 5"
                                    type="submit"
                                    class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium"
                                >
                                    Next Step
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    @click="submitApplication"
                                    :disabled="submitting"
                                    class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!submitting">Submit Application</span>
                                    <span v-else class="flex items-center">
                                        <BaseSpinner class="w-5 h-5 mr-2" />
                                        Submitting...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Help & Info Sidebar -->
                    <div v-if="config.important_notes || config.requirements" class="bg-amber-50 border border-amber-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-amber-900 mb-3">Important Information</h3>
                        <div v-if="config.important_notes" class="text-sm text-amber-800 mb-4 whitespace-pre-line">{{ config.important_notes }}</div>
                        
                        <div v-if="config.requirements.documents?.length" class="mt-4">
                            <h4 class="font-semibold text-amber-900 mb-2">Required Documents:</h4>
                            <ul class="list-disc list-inside text-sm text-amber-800 space-y-1">
                                <li v-for="doc in config.requirements.documents" :key="doc">{{ doc }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseStepper from '@/Components/Base/BaseStepper.vue'
import BaseSpinner from '@/Components/Base/BaseSpinner.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
    country: { type: String, required: true },
    visaType: { type: String, required: true },
})

const { formatCurrency } = useBangladeshFormat()

const loading = ref(true)
const error = ref(null)
const config = ref(null)
const currentStep = ref(1)
const submitting = ref(false)
const form = ref({})
const errors = ref({})

const visaTypeLabel = computed(() => {
    const labels = {
        tourist: 'Tourist',
        business: 'Business',
        student: 'Student',
        work: 'Work',
        medical: 'Medical',
        transit: 'Transit',
        family: 'Family'
    }
    return labels[props.visaType] || props.visaType
})

const stepperSteps = computed(() => {
    if (!config.value) return []
    return config.value.steps.map((step, index) => ({
        label: step.title,
        isComplete: index + 1 < currentStep.value,
        isCurrent: index + 1 === currentStep.value
    }))
})

const currentStepData = computed(() => {
    if (!config.value || currentStep.value > config.value.steps.length) return null
    return config.value.steps[currentStep.value - 1]
})

// Fetch wizard configuration from API
async function fetchConfig() {
    loading.value = true
    error.value = null

    try {
        const response = await fetch(`/api/visa/config?country=${encodeURIComponent(props.country)}&visa_type=${encodeURIComponent(props.visaType)}`)
        const data = await response.json()

        if (!response.ok) {
            throw new Error(data.error || 'Failed to load configuration')
        }

        config.value = data.config
        initializeForm()
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
}

// Initialize form with empty values
function initializeForm() {
    config.value.steps.forEach(step => {
        if (step.fields) {
            step.fields.forEach(field => {
                form.value[field.name] = field.type === 'checkbox' ? false : ''
            })
        }
    })
}

// Navigate to step
function goToStep(stepNumber) {
    if (stepNumber <= currentStep.value) {
        currentStep.value = stepNumber
        errors.value = {}
    }
}

// Go to previous step
function goToPrevious() {
    if (currentStep.value > 1) {
        currentStep.value--
        errors.value = {}
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
}

// Handle next step
function handleNext() {
    errors.value = {}
    
    // Validate current step
    const currentFields = currentStepData.value.fields || []
    let hasErrors = false

    currentFields.forEach(field => {
        if (field.required && !form.value[field.name]) {
            errors.value[field.name] = `${field.label} is required`
            hasErrors = true
        }
    })

    if (hasErrors) {
        return
    }

    // Move to next step
    if (currentStep.value < 5) {
        currentStep.value++
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
}

// Submit application
function submitApplication() {
    submitting.value = true

    // Prepare data
    const applicationData = {
        ...form.value,
        visa_type: props.visaType,
        destination_country: props.country,
        destination_country_code: config.value.country_code,
        service_fee: config.value.fees.service_fee,
        government_fee: config.value.fees.government_fee,
        processing_fee: getProcessingFee(),
        total_amount: getTotalAmount(),
    }

    // Submit via Inertia
    router.post(route('visa.store'), applicationData, {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (err) => {
            errors.value = err
            submitting.value = false
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }
    })
}

// Get processing fee based on selected type
function getProcessingFee() {
    if (!config.value || !form.value.processing_type) return 0
    
    const processingField = config.value.steps.find(s => s.id === 4)?.fields.find(f => f.name === 'processing_type')
    const selectedOption = processingField?.options.find(opt => opt.value === form.value.processing_type)
    
    return selectedOption?.price || 0
}

// Calculate total amount
function getTotalAmount() {
    return config.value.fees.government_fee + config.value.fees.service_fee + getProcessingFee()
}

// Format field value for review
function formatFieldValue(field, value) {
    if (!value) return '—'
    
    if (field.type === 'select') {
        const option = field.options?.find(opt => opt.value === value)
        return option?.label || value
    }
    
    if (field.type === 'date') {
        const { formatDate } = useBangladeshFormat();
        return formatDate(value)
    }
    
    return value
}

onMounted(() => {
    fetchConfig()
})
</script>
