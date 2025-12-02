<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import Modal from '@/Components/Modal.vue'
import RhythmicCard from '@/Components/Rhythmic/RhythmicCard.vue'
import FlowButton from '@/Components/Rhythmic/FlowButton.vue'
import {
  PhoneIcon,
  CheckBadgeIcon,
  ClockIcon,
  XCircleIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const { formatPhone } = useBangladeshFormat()

const phoneNumbers = ref([])
const showModal = ref(false)
const modalMode = ref('add') // 'add' or 'edit'
const editingId = ref(null)
const isLoading = ref(false)
const error = ref(null)
const success = ref(null)
const showDeleteModal = ref(false)
const deleteId = ref(null)

// Verification state
const showVerifyModal = ref(false)
const verifyingPhone = ref(null)
const verificationCode = ref('')
const verificationErrors = ref({})
const isVerifying = ref(false)
const isSendingCode = ref(false)

const form = ref({
    country_code: '+880',
    phone_number: '',
    phone_type: 'mobile',
    label: '',
    is_primary: false
})

const errors = ref({})

// Phone type options
const phoneTypes = [
    { value: 'mobile', label: 'Mobile', icon: '📱' },
    { value: 'home', label: 'Home', icon: '🏠' },
    { value: 'work', label: 'Work', icon: '💼' },
    { value: 'whatsapp', label: 'WhatsApp', icon: '💬' },
    { value: 'fax', label: 'Fax', icon: '📠' },
    { value: 'other', label: 'Other', icon: '📞' },
]

// Country codes - will be loaded from database
const countryCodes = ref([{ code: '+880', name: 'Bangladesh', flag: '🇧🇩' }])
const isLoadingCountries = ref(false)

const primaryPhone = computed(() => {
    if (!Array.isArray(phoneNumbers.value)) return null
    return phoneNumbers.value.find(phone => phone.is_primary)
})

const fetchCountries = async () => {
    try {
        isLoadingCountries.value = true
        const response = await axios.get('/api/countries')
        countryCodes.value = response.data.map(country => ({
            code: country.phone_code || '+880',
            name: country.name,
            flag: country.flag_emoji || '🌐'
        }))
        // Sort: Bangladesh first, then alphabetically
        countryCodes.value.sort((a, b) => {
            if (a.name === 'Bangladesh') return -1
            if (b.name === 'Bangladesh') return 1
            return a.name.localeCompare(b.name)
        })
    } catch (err) {
        console.error('Failed to fetch countries:', err)
        // Fallback to Bangladesh
        countryCodes.value = [{ code: '+880', name: 'Bangladesh', flag: '🇧🇩' }]
    } finally {
        isLoadingCountries.value = false
    }
}

const fetchPhoneNumbers = async () => {
    try {
        isLoading.value = true
        error.value = null
        const response = await axios.get(route('api.profile.phone-numbers.index'))
        phoneNumbers.value = Array.isArray(response.data) ? response.data : []
    } catch (err) {
        error.value = 'Failed to load phone numbers'
        phoneNumbers.value = [] // Ensure it's always an array
        console.error('Failed to fetch phone numbers:', err)
        alert('Failed to load phone numbers. Please refresh the page.')
    } finally {
        isLoading.value = false
    }
}

const validateForm = () => {
    errors.value = {}
    
    if (!form.value.phone_number.trim()) {
        errors.value.phone_number = 'Phone number is required'
    }
    
    if (!form.value.label.trim()) {
        errors.value.label = 'Label is required (e.g., Mobile, WhatsApp)'
    }
    
    // Basic phone number validation (digits only, 10-15 characters)
    const phoneDigits = (form.value?.phone_number || '').replace(/\D/g, '')
    if (phoneDigits.length < 10 || phoneDigits.length > 15) {
        errors.value.phone_number = 'Phone number must be 10-15 digits'
    }
    
    return Object.keys(errors.value).length === 0
}

const savePhoneNumber = async () => {
    if (!validateForm()) {
        return
    }
    
    try {
        isLoading.value = true
        error.value = null
        success.value = null
        
        if (editingId.value) {
            await axios.put(
                route('api.profile.phone-numbers.update', editingId.value), 
                form.value
            )
            success.value = 'Phone number updated successfully'
        } else {
            await axios.post(
                route('api.profile.phone-numbers.store'), 
                form.value
            )
            success.value = 'Phone number added successfully'
        }
        
        await fetchPhoneNumbers()
        closeModal()
        
        // Clear success message after 3 seconds
        setTimeout(() => {
            success.value = null
        }, 3000)
    } catch (err) {
        if (err.response?.data?.errors) {
            errors.value = err.response.data.errors
        } else {
            error.value = err.response?.data?.message || 'Failed to save phone number'
        }
        console.error('Failed to save phone number:', err)
    } finally {
        isLoading.value = false
    }
}

const openAddModal = () => {
    modalMode.value = 'add'
    resetForm()
    showModal.value = true
}

const openEditModal = (phone) => {
    modalMode.value = 'edit'
    form.value = {
        country_code: phone.country_code,
        phone_number: phone.phone_number,
        phone_type: phone.phone_type || 'mobile',
        label: phone.label,
        is_primary: phone.is_primary
    }
    editingId.value = phone.id
    errors.value = {}
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    resetForm()
}

const openDeleteModal = (id) => {
    deleteId.value = id
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    deleteId.value = null
}

const confirmDelete = async () => {
    try {
        isLoading.value = true
        error.value = null
        await axios.delete(route('api.profile.phone-numbers.destroy', deleteId.value))
        success.value = 'Phone number deleted successfully'
        await fetchPhoneNumbers()
        closeDeleteModal()
        
        setTimeout(() => {
            success.value = null
        }, 3000)
    } catch (err) {
        error.value = 'Failed to delete phone number'
        console.error('Failed to delete phone number:', err)
    } finally {
        isLoading.value = false
    }
}

const resetForm = () => {
    form.value = {
        country_code: '+880',
        phone_number: '',
        phone_type: 'mobile',
        label: '',
        is_primary: false
    }
    editingId.value = null
    errors.value = {}
}

const getVerificationBadge = (phone) => {
    if (phone.is_verified) {
        return { icon: CheckBadgeIcon, text: 'Verified', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }
    } else if (phone.verification_pending) {
        return { icon: ClockIcon, text: 'Pending', class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }
    } else {
        return { icon: XCircleIcon, text: 'Unverified', class: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }
    }
}

const getCountryFlag = (countryCode) => {
    if (!Array.isArray(countryCodes.value)) return '📱'
    const country = countryCodes.value.find(c => c.code === countryCode)
    return country?.flag || '📱'
}

const getPhoneTypeIcon = (phoneType) => {
    const type = phoneTypes.find(t => t.value === phoneType)
    return type?.icon || '📞'
}

const sendVerificationCode = async (phone) => {
    try {
        isSendingCode.value = true
        error.value = null
        
        await axios.post(route('api.profile.phone-numbers.send-verification', phone.id))
        
        verifyingPhone.value = phone
        showVerifyModal.value = true
        success.value = 'Verification code sent to your phone'
        
        setTimeout(() => {
            success.value = null
        }, 3000)
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send verification code'
        console.error('Failed to send verification code:', err)
    } finally {
        isSendingCode.value = false
    }
}

const verifyPhoneNumber = async () => {
    if (!verificationCode.value || verificationCode.value.length !== 6) {
        verificationErrors.value = { code: 'Please enter a 6-digit code' }
        return
    }
    
    try {
        isVerifying.value = true
        verificationErrors.value = {}
        error.value = null
        
        await axios.post(
            route('api.profile.phone-numbers.verify', verifyingPhone.value.id),
            { code: verificationCode.value }
        )
        
        success.value = 'Phone number verified successfully!'
        await fetchPhoneNumbers()
        closeVerifyModal()
        
        setTimeout(() => {
            success.value = null
        }, 3000)
    } catch (err) {
        if (err.response?.data?.errors) {
            verificationErrors.value = err.response.data.errors
        } else {
            verificationErrors.value = { code: err.response?.data?.message || 'Verification failed' }
        }
        console.error('Failed to verify phone number:', err)
    } finally {
        isVerifying.value = false
    }
}

const resendVerificationCode = async () => {
    try {
        isSendingCode.value = true
        error.value = null
        
        await axios.post(route('api.profile.phone-numbers.resend-verification', verifyingPhone.value.id))
        
        success.value = 'Verification code resent successfully'
        verificationCode.value = ''
        
        setTimeout(() => {
            success.value = null
        }, 3000)
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to resend code'
        console.error('Failed to resend verification code:', err)
    } finally {
        isSendingCode.value = false
    }
}

const closeVerifyModal = () => {
    showVerifyModal.value = false
    verifyingPhone.value = null
    verificationCode.value = ''
    verificationErrors.value = {}
}

onMounted(() => {
    fetchCountries()
    fetchPhoneNumbers()
})
</script>

<template>
    <section class="space-y-rhythm-md">
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-rhythm-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-red-600 flex items-center justify-center shadow-sm">
                    <PhoneIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h2 class="font-display font-bold text-xl text-gray-800">Phone Numbers</h2>
                    <p class="text-xs text-gray-500">Manage contact numbers</p>
                </div>
            </div>
            <FlowButton
                @click="openAddModal"
                size="sm"
                variant="primary"
            >
                <template #icon-left>
                    <PlusIcon class="w-4 h-4" />
                </template>
                <span class="hidden sm:inline">Add Number</span>
                <span class="sm:hidden">Add</span>
            </FlowButton>
        </div>

        <!-- Alert Messages -->
        <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <XCircleIcon class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-red-800 dark:text-red-200">{{ error }}</span>
            </div>
        </div>

        <div v-if="success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <CheckBadgeIcon class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-green-800 dark:text-green-200">{{ success }}</span>
            </div>
        </div>

        <!-- Phone Numbers List -->
        <div v-if="phoneNumbers.length > 0" class="space-y-4">
            <div 
                v-for="phone in phoneNumbers" 
                :key="phone.id"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200"
            >
                <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-blue-600"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900/40 dark:to-blue-900/40 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <span class="text-2xl">{{ getCountryFlag(phone.country_code) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a 
                                    :href="`tel:${phone.country_code}${phone.phone_number}`"
                                    class="text-lg font-bold text-gray-900 dark:text-white hover:text-brand-red-600 dark:hover:text-indigo-400 transition-colors block"
                                >
                                    {{ phone.country_code }} {{ phone.phone_number }}
                                </a>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                                    <span>{{ getPhoneTypeIcon(phone.phone_type) }}</span>
                                    <span>{{ phone.label }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span 
                            v-if="phone.is_primary" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 dark:from-blue-900/30 dark:to-indigo-900/30 dark:text-blue-300 text-xs font-semibold rounded-lg shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Primary
                        </span>

                        <span 
                            :class="getVerificationBadge(phone).class"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm"
                        >
                            <component :is="getVerificationBadge(phone).icon" class="w-4 h-4" />
                            {{ getVerificationBadge(phone).text }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <button
                            v-if="!phone.is_verified"
                            @click="sendVerificationCode(phone)"
                            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-green-500 rounded-xl hover:from-green-700 hover:to-green-600 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5"
                            :disabled="isSendingCode || isLoading"
                            style="min-height: 48px"
                        >
                            <CheckBadgeIcon class="w-5 h-5" />
                            <span>Verify Phone</span>
                        </button>
                        <button
                            @click="openEditModal(phone)"
                            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-all duration-200 shadow-sm border border-indigo-200 dark:border-indigo-800"
                            :disabled="isLoading"
                            style="min-height: 48px"
                        >
                            <PencilIcon class="w-5 h-5" />
                            <span>Edit</span>
                        </button>
                        <button
                            @click="openDeleteModal(phone.id)"
                            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 transition-all duration-200 shadow-sm border border-red-200 dark:border-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isLoading || phoneNumbers.length === 1"
                            style="min-height: 48px"
                        >
                            <TrashIcon class="w-5 h-5" />
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
            <PhoneIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-600 dark:text-gray-400 font-medium">No phone numbers added yet</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Add your contact numbers for communication</p>
            <button
                @click="openAddModal"
                class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-brand-red-600 rounded-lg hover:bg-red-700 transition-all shadow-md"
                style="min-height: 44px"
            >
                <PlusIcon class="w-5 h-5" />
                Add Phone Number
            </button>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-brand-red-600 flex items-center justify-center">
                        <PhoneIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ modalMode === 'add' ? 'Add Phone Number' : 'Edit Phone Number' }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Enter contact details</p>
                    </div>
                </div>

                <form @submit.prevent="savePhoneNumber" class="space-y-4">
                    <!-- Country Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Country Code <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                v-model="form.country_code"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red-600 focus:border-indigo-500 appearance-none bg-white dark:bg-gray-800 shadow-sm hover:border-gray-400 transition-colors cursor-pointer"
                                style="font-size: 15px; min-height: 48px; padding-right: 40px;"
                            >
                                <option value="" disabled>Select country</option>
                                <option 
                                    v-for="country in countryCodes" 
                                    :key="country.code" 
                                    :value="country.code"
                                    class="py-2"
                                >
                                    {{ country.flag }} {{ country.code }} — {{ country.name }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <p v-if="isLoadingCountries" class="text-xs text-gray-500 mt-1">Loading countries...</p>
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.phone_number"
                            type="tel"
                            placeholder="1712345678"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red-600 focus:border-indigo-500 shadow-sm hover:border-gray-400 transition-colors"
                            :class="{ 'border-red-500': errors.phone_number }"
                            style="font-size: 16px; min-height: 48px"
                        />
                        <p v-if="errors.phone_number" class="text-xs text-red-600 dark:text-red-400 mt-1">
                            {{ errors.phone_number }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter phone number without country code</p>
                    </div>

                    <!-- Phone Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Phone Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                v-model="form.phone_type"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red-600 focus:border-indigo-500 appearance-none bg-white shadow-sm hover:border-gray-400 transition-colors cursor-pointer"
                                style="font-size: 15px; min-height: 48px; padding-right: 40px;"
                            >
                                <option v-for="type in phoneTypes" :key="type.value" :value="type.value" class="py-2">
                                    {{ type.icon }} {{ type.label }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <p v-if="errors.phone_type" class="text-xs text-red-600 dark:text-red-400 mt-1">
                            {{ errors.phone_type }}
                        </p>
                    </div>

                    <!-- Label -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Label <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.label"
                            type="text"
                            placeholder="e.g., Personal, Office, Emergency"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red-600 focus:border-indigo-500 shadow-sm hover:border-gray-400 transition-colors"
                            :class="{ 'border-red-500': errors.label }"
                            style="font-size: 16px; min-height: 48px"
                        />
                        <p v-if="errors.label" class="text-xs text-red-600 dark:text-red-400 mt-1">
                            {{ errors.label }}
                        </p>
                    </div>

                    <!-- Primary Checkbox -->
                    <label class="flex items-center gap-3 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl cursor-pointer border border-indigo-200 dark:border-indigo-800 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors" style="min-height: 56px">
                        <input
                            v-model="form.is_primary"
                            type="checkbox"
                            class="w-5 h-5 text-brand-red-600 border-gray-300 rounded focus:ring-brand-red-600 cursor-pointer"
                        />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Set as primary phone number</span>
                    </label>

                    <!-- Info Box -->
                    <div v-if="form.is_primary" class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Setting this as primary will automatically unset any other primary phone number.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="closeModal"
                            class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            style="min-height: 44px"
                            :disabled="isLoading"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-white bg-brand-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md"
                            style="min-height: 44px"
                            :disabled="isLoading"
                        >
                            <span v-if="isLoading">Saving...</span>
                            <span v-else>{{ modalMode === 'add' ? 'Add' : 'Update' }} Phone Number</span>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <TrashIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Delete Phone Number</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone</p>
                    </div>
                </div>

                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Are you sure you want to delete this phone number? This will permanently remove it from your profile.
                </p>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3">
                    <button
                        type="button"
                        @click="closeDeleteModal"
                        class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        style="min-height: 44px"
                        :disabled="isLoading"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="confirmDelete"
                        class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-red-700 bg-red-50 border-2 border-red-300 rounded-lg hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        style="min-height: 44px"
                        :disabled="isLoading"
                    >
                        <span v-if="isLoading">Deleting...</span>
                        <span v-else>Delete Phone Number</span>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Verification Modal -->
        <Modal :show="showVerifyModal" @close="closeVerifyModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-green-600 flex items-center justify-center border-2 border-green-700">
                        <CheckBadgeIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Verify Phone Number</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ verifyingPhone?.country_code }} {{ verifyingPhone?.phone_number }}
                        </p>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        We've sent a 6-digit verification code to your phone. Enter it below to verify your number.
                        <strong>Code expires in 10 minutes.</strong>
                    </p>
                </div>

                <form @submit.prevent="verifyPhoneNumber" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Verification Code <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="verificationCode"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            placeholder="Enter 6-digit code"
                            class="w-full px-4 py-3 text-center text-2xl font-bold tracking-widest border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600"
                            :class="{ 'border-red-500': verificationErrors.code }"
                            style="font-size: 24px; min-height: 60px; letter-spacing: 0.5em"
                            autofocus
                        />
                        <p v-if="verificationErrors.code" class="text-xs text-red-600 dark:text-red-400 mt-2">
                            {{ verificationErrors.code }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <button
                            type="button"
                            @click="resendVerificationCode"
                            class="text-sm font-medium text-brand-red-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 disabled:opacity-50 transition-colors"
                            :disabled="isSendingCode"
                        >
                            <span v-if="isSendingCode">Sending...</span>
                            <span v-else>Resend Code</span>
                        </button>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Didn't receive the code?
                        </span>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeVerifyModal"
                            class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            style="min-height: 44px"
                            :disabled="isVerifying"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="w-full sm:flex-1 px-6 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md"
                            style="min-height: 44px"
                            :disabled="isVerifying || !verificationCode || verificationCode.length !== 6"
                        >
                            <span v-if="isVerifying">Verifying...</span>
                            <span v-else>Verify Phone Number</span>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </section>
</template>
