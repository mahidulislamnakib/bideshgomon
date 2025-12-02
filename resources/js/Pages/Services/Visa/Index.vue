<template>
    <AuthenticatedLayout>
        <Head title="Visa Services" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Visa Application Services</h1>
                    <p class="mt-2 text-gray-600">Professional visa assistance for multiple countries worldwide</p>
                </div>

                <!-- Visa Types -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Select Visa Type</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <button
                            v-for="(visa, type) in visaTypes"
                            :key="type"
                            @click="selectedVisaType = type"
                            class="bg-white rounded-lg shadow-sm border-2 p-6 hover:shadow-md transition-all cursor-pointer text-left"
                            :class="selectedVisaType === type ? 'border-ocean-500 bg-ocean-50' : 'border-gray-200 hover:border-ocean-300'"
                        >
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8" :class="selectedVisaType === type ? 'text-ocean-600' : 'text-indigo-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold" :class="selectedVisaType === type ? 'text-ocean-900' : 'text-gray-900'">{{ visa.name }}</h3>
                                    <p class="mt-2 text-sm text-gray-600">{{ visa.description }}</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Popular Countries -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900">Popular Destinations</h2>
                            <p v-if="selectedVisaType" class="text-sm text-gray-600 mt-1">
                                For <span class="font-semibold text-ocean-600">{{ visaTypes[selectedVisaType]?.name }}</span>
                            </p>
                        </div>
                        <Link
                            :href="route('visa.my-applications')"
                            class="inline-flex items-center px-4 py-2 bg-ocean-600 text-white text-sm font-medium rounded-lg hover:bg-ocean-700"
                        >
                            My Applications
                        </Link>
                    </div>
                    
                    <div v-if="!selectedVisaType" class="bg-amber-50 border border-amber-200 rounded-lg p-6 text-center">
                        <p class="text-amber-800">Please select a visa type above to see available destinations</p>
                    </div>
                    
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="country in popularCountries"
                            :key="country.code"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <span class="text-4xl mr-3">{{ country.flag }}</span>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ country.name }}</h3>
                                            <p class="text-sm text-gray-600">{{ country.code }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="text-sm text-gray-600 mb-1">Service Fee (from)</div>
                                    <div class="text-2xl font-bold text-ocean-600">৳{{ country.service_fee.toLocaleString() }}</div>
                                </div>

                                <div class="space-y-2">
                                    <button
                                        @click="showRequirements(country.name)"
                                        class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                                        View Requirements & Fees
                                    </button>
                                    <Link
                                        :href="route('visa.wizard', { country: country.name, type: selectedVisaType })"
                                        class="block w-full text-center px-4 py-2 bg-ocean-600 text-white text-sm font-medium rounded-lg hover:bg-ocean-700 transition-colors"
                                    >
                                        Apply Now
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-12 bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Why Choose Our Visa Services?</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <div class="flex items-center mb-3">
                                <svg class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">Expert Guidance</h3>
                            </div>
                            <p class="text-gray-600">Professional consultation from experienced visa consultants</p>
                        </div>
                        <div>
                            <div class="flex items-center mb-3">
                                <svg class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">Fast Processing</h3>
                            </div>
                            <p class="text-gray-600">Standard, express, and urgent processing options available</p>
                        </div>
                        <div>
                            <div class="flex items-center mb-3">
                                <svg class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">Secure Payment</h3>
                            </div>
                            <p class="text-gray-600">Multiple payment methods with secure transaction processing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requirements Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div
                        @click="closeModal"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        aria-hidden="true"
                    ></div>

                    <!-- Center modal -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                    >
                        <!-- Loading state -->
                        <div v-if="loadingRequirements" class="p-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-ocean-600"></div>
                            <p class="mt-4 text-gray-600">Loading visa requirements...</p>
                        </div>

                        <!-- Error state -->
                        <div v-else-if="requirementsError" class="p-12 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Unable to Load Requirements</h3>
                            <p class="text-gray-600">{{ requirementsError }}</p>
                            <button
                                @click="closeModal"
                                class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                            >
                                Close
                            </button>
                        </div>

                        <!-- Content -->
                        <div v-else-if="selectedRequirements" class="bg-white">
                            <!-- Header -->
                            <div class="bg-ocean-600 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-xl font-semibold text-white" id="modal-title">
                                            {{ selectedRequirements.country }} - {{ visaTypes[selectedVisaType]?.name }} Visa
                                        </h3>
                                        <p class="text-ocean-100 text-sm mt-1">{{ selectedRequirements.visa_category }}</p>
                                    </div>
                                    <button
                                        @click="closeModal"
                                        class="text-white hover:text-ocean-100"
                                    >
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                                <!-- Fees Section -->
                                <div class="mb-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-ocean-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Fees & Processing Time
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="text-sm text-gray-600 mb-1">Government Fee</div>
                                            <div class="text-2xl font-bold text-gray-900">৳{{ selectedRequirements.government_fee?.toLocaleString() || '0' }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="text-sm text-gray-600 mb-1">Service Fee</div>
                                            <div class="text-2xl font-bold text-gray-900">৳{{ selectedRequirements.service_fee?.toLocaleString() || '0' }}</div>
                                        </div>
                                        <div class="bg-ocean-50 rounded-lg p-4">
                                            <div class="text-sm text-ocean-700 mb-1">Total Fee</div>
                                            <div class="text-2xl font-bold text-ocean-600">৳{{ ((selectedRequirements.government_fee || 0) + (selectedRequirements.service_fee || 0)).toLocaleString() }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-3 gap-4">
                                        <div class="bg-green-50 rounded-lg p-3 text-center">
                                            <div class="text-xs text-green-700 mb-1">Standard</div>
                                            <div class="font-semibold text-green-900">{{ selectedRequirements.processing_days_standard }} days</div>
                                            <div class="text-xs text-green-600">+৳{{ selectedRequirements.processing_fee_standard?.toLocaleString() }}</div>
                                        </div>
                                        <div class="bg-yellow-50 rounded-lg p-3 text-center">
                                            <div class="text-xs text-yellow-700 mb-1">Express</div>
                                            <div class="font-semibold text-yellow-900">{{ selectedRequirements.processing_days_express }} days</div>
                                            <div class="text-xs text-yellow-600">+৳{{ selectedRequirements.processing_fee_express?.toLocaleString() }}</div>
                                        </div>
                                        <div class="bg-red-50 rounded-lg p-3 text-center">
                                            <div class="text-xs text-red-700 mb-1">Urgent</div>
                                            <div class="font-semibold text-red-900">{{ selectedRequirements.processing_days_urgent }} days</div>
                                            <div class="text-xs text-red-600">+৳{{ selectedRequirements.processing_fee_urgent?.toLocaleString() }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Required Documents -->
                                <div v-if="selectedRequirements.required_documents?.length" class="mb-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-ocean-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Required Documents
                                    </h4>
                                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <li v-for="(doc, index) in selectedRequirements.required_documents" :key="index" class="flex items-start">
                                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-gray-700">{{ doc }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- General Requirements -->
                                <div v-if="selectedRequirements.general_requirements" class="mb-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-ocean-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        General Requirements
                                    </h4>
                                    <p class="text-gray-700 whitespace-pre-line">{{ selectedRequirements.general_requirements }}</p>
                                </div>

                                <!-- Financial Requirements -->
                                <div v-if="selectedRequirements.min_bank_balance" class="mb-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-ocean-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Financial Requirements
                                    </h4>
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-amber-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="font-medium text-amber-900">Minimum Bank Balance: ৳{{ selectedRequirements.min_bank_balance?.toLocaleString() }}</p>
                                                <p class="text-sm text-amber-700 mt-1">Bank statement for last {{ selectedRequirements.bank_statement_months }} months required</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                    <div v-if="selectedRequirements.interview_required" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="font-medium text-blue-900">Interview Required</p>
                                                <p class="text-sm text-blue-700 mt-1">{{ selectedRequirements.interview_details || 'Embassy interview is mandatory' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="selectedRequirements.biometrics_required" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-purple-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="font-medium text-purple-900">Biometrics Required</p>
                                                <p class="text-sm text-purple-700 mt-1">{{ selectedRequirements.biometrics_details || 'Fingerprints and photo collection required' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Important Notes -->
                                <div v-if="selectedRequirements.important_notes" class="mb-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        Important Notes
                                    </h4>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <p class="text-red-800 whitespace-pre-line">{{ selectedRequirements.important_notes }}</p>
                                    </div>
                                </div>

                                <!-- Tips -->
                                <div v-if="selectedRequirements.tips_for_applicants?.length" class="mb-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        Tips for Applicants
                                    </h4>
                                    <ul class="space-y-2">
                                        <li v-for="(tip, index) in selectedRequirements.tips_for_applicants" :key="index" class="flex items-start">
                                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-gray-700">{{ tip }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <span class="font-medium">Processing Time:</span> {{ selectedRequirements.processing_time }}
                                </div>
                                <div class="flex space-x-3">
                                    <button
                                        @click="closeModal"
                                        type="button"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300"
                                    >
                                        Close
                                    </button>
                                    <Link
                                        :href="route('visa.wizard', { country: selectedRequirements.country, type: selectedVisaType })"
                                        class="px-6 py-2 bg-ocean-600 text-white text-sm font-medium rounded-lg hover:bg-ocean-700"
                                    >
                                        Apply Now
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

defineProps({
    visaTypes: Object,
    popularCountries: Array,
});

const selectedVisaType = ref('tourist') // Default to tourist
const showModal = ref(false)
const selectedRequirements = ref(null)
const loadingRequirements = ref(false)
const requirementsError = ref(null)

const showRequirements = async (countryName) => {
    showModal.value = true
    loadingRequirements.value = true
    requirementsError.value = null
    selectedRequirements.value = null

    try {
        const response = await axios.get('/api/visa/config', {
            params: {
                country: countryName,
                visa_type: selectedVisaType.value
            }
        })

        selectedRequirements.value = response.data
    } catch (error) {
        console.error('Error fetching visa requirements:', error)
        requirementsError.value = error.response?.data?.message || 'Unable to load visa requirements. Please try again later.'
    } finally {
        loadingRequirements.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    selectedRequirements.value = null
    requirementsError.value = null
}
</script>
