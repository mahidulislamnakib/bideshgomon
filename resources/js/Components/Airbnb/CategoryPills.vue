<template>
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button
            v-for="category in categories"
            :key="category.id"
            @click="selectCategory(category.id)"
            :class="[
                'px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition-colors',
                selectedCategory === category.id
                    ? 'bg-growth-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
        >
            {{ category.name }}
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const categories = ref([
    { id: 'all', name: 'All Services' },
    { id: 'visa', name: 'Visa Services' },
    { id: 'education', name: 'Education' },
    { id: 'migration', name: 'Migration' },
]);

const selectedCategory = ref('all');

const selectCategory = (id) => {
    selectedCategory.value = id;
};
</script>
