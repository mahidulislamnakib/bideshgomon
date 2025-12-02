<template>
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
        <div class="aspect-video bg-gray-200 relative">
            <img v-if="service.image" :src="service.image" :alt="service.title" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                No Image
            </div>
        </div>
        <div class="p-4">
            <h3 class="font-semibold text-lg mb-1">{{ service.title }}</h3>
            <p class="text-gray-600 text-sm mb-3">{{ service.description }}</p>
            <div class="flex items-center justify-between">
                <span class="text-brand-red-600 font-bold">৳{{ service.price }}</span>
                <button class="bg-brand-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
                    View Details
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    service: {
        type: Object,
        required: true
    }
});
</script>
