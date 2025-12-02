<template>
    <div class="relative">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        
        <div class="relative">
            <!-- Country Code Dropdown -->
            <button
                type="button"
                @click="toggleDropdown"
                class="absolute left-0 top-0 h-full flex items-center gap-2 px-3 border-r border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors rounded-l-lg z-10"
                :class="{ 'ring-2 ring-blue-500': isDropdownOpen }"
            >
                <span class="text-xl">{{ selectedCountry.flag }}</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ selectedCountry.code }}
                </span>
                <svg 
                    class="w-4 h-4 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': isDropdownOpen }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Phone Number Input -->
            <input
                :id="id"
                ref="phoneInput"
                v-model="phoneNumber"
                type="tel"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @input="handleInput"
                @blur="$emit('blur')"
                @focus="$emit('focus')"
                class="block w-full pl-32 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-brand-red-600 focus:border-blue-500 dark:bg-gray-800 dark:text-white disabled:bg-gray-100 dark:disabled:bg-gray-700 disabled:cursor-not-allowed transition-colors"
                :class="{
                    'border-red-500 focus:ring-red-500': errorMessage,
                    'pr-10': showValidation && isValid
                }"
            />

            <!-- Valid Check Icon -->
            <div v-if="showValidation && isValid" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Country Dropdown -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="isDropdownOpen"
                    v-click-outside="closeDropdown"
                    class="absolute left-0 top-full mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-hidden"
                >
                    <!-- Search -->
                    <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                ref="searchInput"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search for countries"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-brand-red-600 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Country List -->
                    <div class="overflow-y-auto max-h-72 custom-scrollbar">
                        <button
                            v-for="country in filteredCountries"
                            :key="country.code"
                            type="button"
                            @click="selectCountry(country)"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                            :class="{
                                'bg-blue-50 dark:bg-blue-900/20': country.code === selectedCountry.code
                            }"
                        >
                            <span class="text-2xl">{{ country.flag }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ country.name }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ country.code }}
                                    </span>
                                </div>
                            </div>
                            <svg 
                                v-if="country.code === selectedCountry.code"
                                class="w-5 h-5 text-brand-red-600 dark:text-blue-400 flex-shrink-0" 
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>

                        <div v-if="filteredCountries.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No countries found
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Error Message -->
        <p v-if="errorMessage" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ errorMessage }}
        </p>

        <!-- Helper Text -->
        <p v-else-if="helperText" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ helperText }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    countryCode: {
        type: String,
        default: '+880'
    },
    countries: {
        type: Array,
        default: () => [
            { code: '+880', name: 'Bangladesh', flag: '🇧🇩' },
            { code: '+1', name: 'United States', flag: '🇺🇸' },
            { code: '+44', name: 'United Kingdom', flag: '🇬🇧' },
            { code: '+91', name: 'India', flag: '🇮🇳' },
            { code: '+86', name: 'China', flag: '🇨🇳' },
            { code: '+81', name: 'Japan', flag: '🇯🇵' },
            { code: '+49', name: 'Germany', flag: '🇩🇪' },
            { code: '+33', name: 'France', flag: '🇫🇷' },
            { code: '+971', name: 'UAE', flag: '🇦🇪' },
            { code: '+966', name: 'Saudi Arabia', flag: '🇸🇦' },
            { code: '+92', name: 'Pakistan', flag: '🇵🇰' },
            { code: '+93', name: 'Afghanistan', flag: '🇦🇫' },
            { code: '+61', name: 'Australia', flag: '🇦🇺' },
            { code: '+64', name: 'New Zealand', flag: '🇳🇿' },
            { code: '+27', name: 'South Africa', flag: '🇿🇦' },
            { code: '+234', name: 'Nigeria', flag: '🇳🇬' },
            { code: '+254', name: 'Kenya', flag: '🇰🇪' },
            { code: '+995', name: 'Georgia', flag: '🇬🇪' },
        ]
    },
    label: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: '+234'
    },
    errorMessage: {
        type: String,
        default: ''
    },
    helperText: {
        type: String,
        default: ''
    },
    id: {
        type: String,
        default: () => `phone-${Math.random().toString(36).substr(2, 9)}`
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    showValidation: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:modelValue', 'update:countryCode', 'blur', 'focus'])

const phoneInput = ref(null)
const searchInput = ref(null)
const isDropdownOpen = ref(false)
const searchQuery = ref('')
const phoneNumber = ref('')
const selectedCountry = ref({ code: props.countryCode, name: 'Bangladesh', flag: '🇧🇩' })

// Initialize selected country
onMounted(() => {
    const country = props.countries.find(c => c.code === props.countryCode)
    if (country) {
        selectedCountry.value = country
    }
    
    // Parse initial value
    if (props.modelValue) {
        // Remove country code from phone number if present
        const cleanPhone = props.modelValue.replace(props.countryCode, '').trim()
        phoneNumber.value = cleanPhone
    }
})

// Watch for external changes
watch(() => props.countryCode, (newCode) => {
    const country = props.countries.find(c => c.code === newCode)
    if (country) {
        selectedCountry.value = country
    }
})

watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        const cleanPhone = newValue.replace(selectedCountry.value.code, '').trim()
        if (cleanPhone !== phoneNumber.value) {
            phoneNumber.value = cleanPhone
        }
    }
})

const filteredCountries = computed(() => {
    if (!searchQuery.value) {
        return props.countries
    }
    
    const query = searchQuery.value.toLowerCase()
    return props.countries.filter(country => 
        country.name.toLowerCase().includes(query) ||
        country.code.includes(query)
    )
})

const isValid = computed(() => {
    return phoneNumber.value && phoneNumber.value.length >= 8
})

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value
    if (isDropdownOpen.value) {
        nextTick(() => {
            searchInput.value?.focus()
        })
    }
}

const closeDropdown = () => {
    isDropdownOpen.value = false
    searchQuery.value = ''
}

const selectCountry = (country) => {
    selectedCountry.value = country
    emit('update:countryCode', country.code)
    emitFullNumber()
    closeDropdown()
    phoneInput.value?.focus()
}

const handleInput = () => {
    // Remove non-numeric characters except + at start
    phoneNumber.value = phoneNumber.value.replace(/[^\d]/g, '')
    emitFullNumber()
}

const emitFullNumber = () => {
    if (phoneNumber.value) {
        const fullNumber = `${selectedCountry.value.code}${phoneNumber.value}`
        emit('update:modelValue', fullNumber)
    } else {
        emit('update:modelValue', '')
    }
}

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value()
            }
        }
        document.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent)
    }
}
</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}
</style>
