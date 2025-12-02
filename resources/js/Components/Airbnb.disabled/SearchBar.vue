<template>
    <!-- Main Search Bar - Airbnb Style -->
    <div class="relative">
        <!-- Compact Search (Default State) -->
        <button
            v-if="!isExpanded"
            @click="expandSearch"
            class="search-button group flex items-center gap-4 px-6 py-3 bg-white rounded-full shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer border border-gray-200"
        >
            <div class="flex items-center gap-3 flex-1">
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-700" />
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Where to?</p>
                    <p class="text-xs text-gray-500">Anywhere • Any week • Add guests</p>
                </div>
            </div>
            <div class="bg-emerald-500 p-2 rounded-full group-hover:bg-emerald-600 transition-colors">
                <MagnifyingGlassIcon class="h-4 w-4 text-white" />
            </div>
        </button>

        <!-- Expanded Search (Active State) -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isExpanded"
                v-click-outside="collapseSearch"
                class="search-expanded bg-white rounded-full shadow-xl border border-gray-200 p-2"
            >
                <div class="flex items-center divide-x divide-gray-200">
                    <!-- Destination -->
                    <div class="flex-1 px-6 py-3 hover:bg-gray-50 rounded-full transition-colors cursor-pointer" @click="focusField('destination')">
                        <label class="block text-xs font-semibold text-gray-900 mb-1">Where</label>
                        <input
                            ref="destinationInput"
                            v-model="searchForm.destination"
                            type="text"
                            placeholder="Search destinations"
                            class="w-full text-sm text-gray-700 placeholder-gray-400 bg-transparent border-0 focus:ring-0 focus:outline-none p-0"
                        />
                    </div>

                    <!-- Service Type -->
                    <div class="flex-1 px-6 py-3 hover:bg-gray-50 rounded-full transition-colors cursor-pointer" @click="focusField('service')">
                        <label class="block text-xs font-semibold text-gray-900 mb-1">Service</label>
                        <select
                            v-model="searchForm.service"
                            class="w-full text-sm text-gray-700 bg-transparent border-0 focus:ring-0 focus:outline-none p-0 cursor-pointer"
                        >
                            <option value="">All services</option>
                            <option value="visa">Visa Application</option>
                            <option value="education">Study Abroad</option>
                            <option value="work">Work Visa</option>
                            <option value="tourist">Tourist Visa</option>
                            <option value="migration">Migration</option>
                        </select>
                    </div>

                    <!-- Budget -->
                    <div class="flex-1 px-6 py-3 hover:bg-gray-50 rounded-full transition-colors cursor-pointer" @click="focusField('budget')">
                        <label class="block text-xs font-semibold text-gray-900 mb-1">Budget</label>
                        <input
                            v-model="searchForm.budget"
                            type="text"
                            placeholder="Add budget"
                            class="w-full text-sm text-gray-700 placeholder-gray-400 bg-transparent border-0 focus:ring-0 focus:outline-none p-0"
                        />
                    </div>

                    <!-- Search Button -->
                    <div class="pl-2">
                        <button
                            @click="handleSearch"
                            class="flex items-center gap-2 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full transition-colors font-semibold text-sm"
                        >
                            <MagnifyingGlassIcon class="h-5 w-5" />
                            <span>Search</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Search Suggestions Dropdown -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="isExpanded && showSuggestions && searchForm.destination.length > 0"
                class="absolute top-full left-0 right-0 mt-2 bg-white rounded-3xl shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto"
            >
                <div class="space-y-2">
                    <button
                        v-for="suggestion in filteredSuggestions"
                        :key="suggestion.id"
                        @click="selectSuggestion(suggestion)"
                        class="w-full flex items-center gap-4 p-3 hover:bg-gray-50 rounded-xl transition-colors text-left"
                    >
                        <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                            <MapPinIcon class="h-6 w-6 text-gray-500" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ suggestion.name }}</p>
                            <p class="text-xs text-gray-500">{{ suggestion.type }}</p>
                        </div>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, MapPinIcon } from '@heroicons/vue/24/outline'

const isExpanded = ref(false)
const showSuggestions = ref(false)
const destinationInput = ref(null)

const searchForm = ref({
    destination: '',
    service: '',
    budget: '',
})

// Popular destinations
const destinations = [
    { id: 1, name: 'United States', type: 'Country', icon: '🇺🇸' },
    { id: 2, name: 'Canada', type: 'Country', icon: '🇨🇦' },
    { id: 3, name: 'United Kingdom', type: 'Country', icon: '🇬🇧' },
    { id: 4, name: 'Australia', type: 'Country', icon: '🇦🇺' },
    { id: 5, name: 'Germany', type: 'Country', icon: '🇩🇪' },
    { id: 6, name: 'Dubai, UAE', type: 'City', icon: '🇦🇪' },
    { id: 7, name: 'Malaysia', type: 'Country', icon: '🇲🇾' },
    { id: 8, name: 'Singapore', type: 'City', icon: '🇸🇬' },
]

const filteredSuggestions = computed(() => {
    if (!searchForm.value.destination) return destinations
    
    return destinations.filter(dest =>
        dest.name.toLowerCase().includes(searchForm.value.destination.toLowerCase())
    )
})

const expandSearch = () => {
    isExpanded.value = true
    showSuggestions.value = true
    nextTick(() => {
        destinationInput.value?.focus()
    })
}

const collapseSearch = () => {
    isExpanded.value = false
    showSuggestions.value = false
}

const focusField = (field) => {
    if (field === 'destination') {
        destinationInput.value?.focus()
        showSuggestions.value = true
    }
}

const selectSuggestion = (suggestion) => {
    searchForm.value.destination = suggestion.name
    showSuggestions.value = false
}

const handleSearch = () => {
    router.get(route('services.search'), searchForm.value)
    collapseSearch()
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
    },
}
</script>

<!-- Temporarily disabled for debugging
<style scoped>
.search-button {
    min-width: 300px;
}

.search-expanded {
    min-width: 850px;
}

@media (max-width: 768px) {
    .search-button {
        min-width: 100%;
    }
    
    .search-expanded {
        min-width: 100%;
    }
    
    .search-expanded .flex {
        flex-direction: column;
    }
    
    .search-expanded .flex > div {
        width: 100%;
        border-left: none !important;
        border-right: none !important;
    }
}

/* Custom scrollbar for suggestions */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>
-->
