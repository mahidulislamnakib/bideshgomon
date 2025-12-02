<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <!-- Backdrop -->
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40" />
            </TransitionChild>

            <!-- Modal Container -->
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all">
                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                <button
                                    @click="clearFilters"
                                    class="text-sm font-semibold text-gray-700 hover:text-gray-900 underline"
                                >
                                    Clear all
                                </button>
                                <DialogTitle class="text-lg font-semibold text-gray-900">
                                    Filters
                                </DialogTitle>
                                <button
                                    @click="closeModal"
                                    class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                                >
                                    <XMarkIcon class="h-5 w-5 text-gray-700" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="max-h-[70vh] overflow-y-auto px-6 py-6">
                                <!-- Price Range -->
                                <div class="border-b border-gray-200 pb-8 mb-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Price Range</h3>
                                    <div class="space-y-6">
                                        <!-- Price Input -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Minimum
                                                </label>
                                                <input
                                                    v-model.number="filters.minPrice"
                                                    type="number"
                                                    placeholder="৳ 0"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Maximum
                                                </label>
                                                <input
                                                    v-model.number="filters.maxPrice"
                                                    type="number"
                                                    placeholder="৳ 500,000"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                                />
                                            </div>
                                        </div>

                                        <!-- Range Slider -->
                                        <div class="px-2">
                                            <input
                                                v-model.number="filters.maxPrice"
                                                type="range"
                                                :min="0"
                                                :max="500000"
                                                :step="5000"
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-slider"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Type -->
                                <div class="border-b border-gray-200 pb-8 mb-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Service Type</h3>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label
                                            v-for="type in serviceTypes"
                                            :key="type.value"
                                            class="flex items-center gap-3 px-4 py-3 border-2 rounded-xl cursor-pointer transition-all hover:border-gray-400"
                                            :class="filters.serviceTypes.includes(type.value) ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'"
                                        >
                                            <input
                                                v-model="filters.serviceTypes"
                                                type="checkbox"
                                                :value="type.value"
                                                class="w-5 h-5 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500"
                                            />
                                            <span class="text-sm font-medium text-gray-900">{{ type.label }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Processing Time -->
                                <div class="border-b border-gray-200 pb-8 mb-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Processing Time</h3>
                                    <div class="space-y-3">
                                        <label
                                            v-for="time in processingTimes"
                                            :key="time.value"
                                            class="flex items-center gap-3 cursor-pointer"
                                        >
                                            <input
                                                v-model="filters.processingTime"
                                                type="radio"
                                                :value="time.value"
                                                class="w-5 h-5 text-emerald-500 border-gray-300 focus:ring-emerald-500"
                                            />
                                            <span class="text-sm text-gray-900">{{ time.label }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Success Rate -->
                                <div class="border-b border-gray-200 pb-8 mb-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Minimum Success Rate</h3>
                                    <div class="flex items-center gap-4">
                                        <input
                                            v-model.number="filters.minSuccessRate"
                                            type="range"
                                            :min="0"
                                            :max="100"
                                            :step="5"
                                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-slider"
                                        />
                                        <span class="text-lg font-semibold text-emerald-600 min-w-[60px] text-right">
                                            {{ filters.minSuccessRate }}%
                                        </span>
                                    </div>
                                </div>

                                <!-- Verified Only -->
                                <div class="pb-4">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">Verified Partners Only</h3>
                                            <p class="text-sm text-gray-600">Show only government-verified agencies</p>
                                        </div>
                                        <div class="relative">
                                            <input
                                                v-model="filters.verifiedOnly"
                                                type="checkbox"
                                                class="sr-only peer"
                                            />
                                            <div
                                                class="w-14 h-8 bg-gray-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors cursor-pointer"
                                                @click="filters.verifiedOnly = !filters.verifiedOnly"
                                            ></div>
                                            <div
                                                class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full transition-transform peer-checked:translate-x-6 pointer-events-none shadow-md"
                                            ></div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                                <p class="text-sm text-gray-600">
                                    {{ resultsCount }} services found
                                </p>
                                <button
                                    @click="applyFilters"
                                    class="px-8 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl transition-colors"
                                >
                                    Show Results
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    initialFilters: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close', 'apply'])

const filters = ref({
    minPrice: props.initialFilters.minPrice || 0,
    maxPrice: props.initialFilters.maxPrice || 500000,
    serviceTypes: props.initialFilters.serviceTypes || [],
    processingTime: props.initialFilters.processingTime || '',
    minSuccessRate: props.initialFilters.minSuccessRate || 80,
    verifiedOnly: props.initialFilters.verifiedOnly || false,
})

const serviceTypes = [
    { value: 'visa', label: 'Visa Application' },
    { value: 'education', label: 'Study Abroad' },
    { value: 'work', label: 'Work Permit' },
    { value: 'tourist', label: 'Tourist Visa' },
    { value: 'migration', label: 'Migration' },
    { value: 'business', label: 'Business Visa' },
]

const processingTimes = [
    { value: 'any', label: 'Any time' },
    { value: 'fast', label: 'Less than 2 weeks' },
    { value: 'medium', label: '2-4 weeks' },
    { value: 'standard', label: '1-3 months' },
]

const resultsCount = computed(() => {
    // Mock calculation - replace with actual filtered count
    return 127
})

const clearFilters = () => {
    filters.value = {
        minPrice: 0,
        maxPrice: 500000,
        serviceTypes: [],
        processingTime: '',
        minSuccessRate: 80,
        verifiedOnly: false,
    }
}

const applyFilters = () => {
    emit('apply', filters.value)
    closeModal()
}

const closeModal = () => {
    emit('close')
}
</script>

<!-- Temporarily disabled for debugging
<style scoped>
/* Custom Range Slider */
.range-slider::-webkit-slider-thumb {
    appearance: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #10b981;
    cursor: pointer;
    border: 4px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.range-slider::-moz-range-thumb {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #10b981;
    cursor: pointer;
    border: 4px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.range-slider::-webkit-slider-runnable-track {
    background: linear-gradient(to right, #10b981 0%, #10b981 var(--value), #e5e7eb var(--value), #e5e7eb 100%);
}
</style>
-->
