<template>
    <Head title="International Phone Input Demo" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        International Phone Input Component
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Modern phone number input with country code selection and validation
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Demo Form -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                            Try It Out
                        </h2>

                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <!-- Basic Phone Input -->
                            <InternationalPhoneInput
                                v-model="form.phone1"
                                v-model:country-code="form.countryCode1"
                                label="Phone Number*"
                                placeholder="1712345678"
                                helper-text="Enter your phone number without country code"
                                :required="true"
                                :countries="countries"
                                :error-message="errors.phone1"
                            />

                            <!-- Alternative Phone (Optional) -->
                            <InternationalPhoneInput
                                v-model="form.phone2"
                                v-model:country-code="form.countryCode2"
                                label="Alternative Phone (Optional)"
                                placeholder="1712345678"
                                helper-text="Backup contact number"
                                :countries="countries"
                            />

                            <!-- WhatsApp Number -->
                            <InternationalPhoneInput
                                v-model="form.whatsapp"
                                v-model:country-code="form.countryCodeWhatsApp"
                                label="WhatsApp Number"
                                placeholder="1712345678"
                                helper-text="For WhatsApp communication"
                                :countries="countries"
                            />

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    class="px-6 py-3 bg-brand-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
                                >
                                    Submit Form
                                </button>
                                <button
                                    type="button"
                                    @click="resetForm"
                                    class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Output Display -->
                    <div class="space-y-6">
                        <!-- Current Values -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Current Values
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Phone 1:</span>
                                    <p class="mt-1 text-gray-900 dark:text-white font-mono">
                                        {{ form.phone1 || '(empty)' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Phone 2:</span>
                                    <p class="mt-1 text-gray-900 dark:text-white font-mono">
                                        {{ form.phone2 || '(empty)' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">WhatsApp:</span>
                                    <p class="mt-1 text-gray-900 dark:text-white font-mono">
                                        {{ form.whatsapp || '(empty)' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Features List -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                ✨ Features
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Country code dropdown with flags</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Searchable country list</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Real-time validation</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Bangladesh default with international support</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Dark mode support</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Clean, modern UI with smooth animations</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Submission Result -->
                        <div v-if="submittedData" class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6 border border-green-200 dark:border-green-800">
                            <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
                                ✓ Form Submitted Successfully
                            </h3>
                            <pre class="text-sm text-green-800 dark:text-green-200 overflow-x-auto">{{ JSON.stringify(submittedData, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InternationalPhoneInput from '@/Components/InternationalPhoneInput.vue'

const form = ref({
    phone1: '',
    countryCode1: '+880',
    phone2: '',
    countryCode2: '+880',
    whatsapp: '',
    countryCodeWhatsApp: '+880',
})

const errors = ref({
    phone1: ''
})

const submittedData = ref(null)

// Extended country list
const countries = [
    { code: '+880', name: 'Bangladesh', flag: '🇧🇩' },
    { code: '+93', name: 'Afghanistan', flag: '🇦🇫' },
    { code: '+971', name: 'UAE', flag: '🇦🇪' },
    { code: '+61', name: 'Australia', flag: '🇦🇺' },
    { code: '+1', name: 'Canada', flag: '🇨🇦' },
    { code: '+86', name: 'China', flag: '🇨🇳' },
    { code: '+20', name: 'Egypt', flag: '🇪🇬' },
    { code: '+33', name: 'France', flag: '🇫🇷' },
    { code: '+49', name: 'Germany', flag: '🇩🇪' },
    { code: '+995', name: 'Georgia', flag: '🇬🇪' },
    { code: '+91', name: 'India', flag: '🇮🇳' },
    { code: '+62', name: 'Indonesia', flag: '🇮🇩' },
    { code: '+81', name: 'Japan', flag: '🇯🇵' },
    { code: '+254', name: 'Kenya', flag: '🇰🇪' },
    { code: '+60', name: 'Malaysia', flag: '🇲🇾' },
    { code: '+52', name: 'Mexico', flag: '🇲🇽' },
    { code: '+977', name: 'Nepal', flag: '🇳🇵' },
    { code: '+64', name: 'New Zealand', flag: '🇳🇿' },
    { code: '+234', name: 'Nigeria', flag: '🇳🇬' },
    { code: '+92', name: 'Pakistan', flag: '🇵🇰' },
    { code: '+63', name: 'Philippines', flag: '🇵🇭' },
    { code: '+966', name: 'Saudi Arabia', flag: '🇸🇦' },
    { code: '+65', name: 'Singapore', flag: '🇸🇬' },
    { code: '+27', name: 'South Africa', flag: '🇿🇦' },
    { code: '+82', name: 'South Korea', flag: '🇰🇷' },
    { code: '+94', name: 'Sri Lanka', flag: '🇱🇰' },
    { code: '+46', name: 'Sweden', flag: '🇸🇪' },
    { code: '+41', name: 'Switzerland', flag: '🇨🇭' },
    { code: '+66', name: 'Thailand', flag: '🇹🇭' },
    { code: '+90', name: 'Turkey', flag: '🇹🇷' },
    { code: '+44', name: 'United Kingdom', flag: '🇬🇧' },
    { code: '+1', name: 'United States', flag: '🇺🇸' },
    { code: '+84', name: 'Vietnam', flag: '🇻🇳' },
]

const handleSubmit = () => {
    // Reset errors
    errors.value = {}
    
    // Validate
    if (!form.value.phone1) {
        errors.value.phone1 = 'Phone number is required'
        return
    }
    
    // Submit
    submittedData.value = {
        phone1: form.value.phone1,
        phone2: form.value.phone2 || null,
        whatsapp: form.value.whatsapp || null,
        timestamp: new Date().toISOString()
    }
    
    // Show success for 5 seconds
    setTimeout(() => {
        submittedData.value = null
    }, 5000)
}

const resetForm = () => {
    form.value = {
        phone1: '',
        countryCode1: '+880',
        phone2: '',
        countryCode2: '+880',
        whatsapp: '',
        countryCodeWhatsApp: '+880',
    }
    errors.value = {}
    submittedData.value = null
}
</script>
