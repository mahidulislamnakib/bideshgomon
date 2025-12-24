<template>
    <div class="bg-white rounded-full shadow-lg p-2 flex items-center gap-2">
        <input
            type="text"
            placeholder="Search services..."
            class="flex-1 px-4 py-2 border-0 focus:ring-0 rounded-full"
            v-model="searchQuery"
        />
        <button class="bg-growth-600 text-white p-3 rounded-full hover:bg-growth-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const searchQuery = ref('');
</script>
