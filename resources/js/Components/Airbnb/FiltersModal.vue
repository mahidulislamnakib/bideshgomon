<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="close"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold">Filters</h2>
                            <button @click="close" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Price Range</label>
                                <input type="range" min="0" max="100000" class="w-full" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-2">Service Type</label>
                                <select class="w-full rounded-lg border-gray-300">
                                    <option>All Types</option>
                                    <option>Visa</option>
                                    <option>Education</option>
                                    <option>Migration</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-2">
                            <button @click="close" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button @click="applyFilters" class="flex-1 px-4 py-2 bg-brand-red-600 text-white rounded-lg hover:bg-red-700">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
defineProps({
    show: Boolean
});

const emit = defineEmits(['close', 'apply']);

const close = () => emit('close');
const applyFilters = () => {
    emit('apply');
    close();
};
</script>

