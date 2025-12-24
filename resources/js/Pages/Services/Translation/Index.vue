<template>
    <AuthenticatedLayout>
        <Head title="Translation Services" />
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-white">Translation Services</h1>
                        <p class="text-sm text-white/80 mt-0.5">Fast, accurate, and certified translations</p>
                    </div>
                    <Link :href="route('translation.my-requests')" class="px-4 py-2 bg-white text-growth-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
                        My Requests
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="pair in languagePairs" :key="`${pair.from}-${pair.to}`"
                         class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ pair.from }} → {{ pair.to }}
                        </h3>
                        <p class="text-2xl font-bold text-growth-600 mb-4">৳{{ pair.price }}/page</p>
                        <Link :href="route('translation.create')" class="block w-full text-center px-4 py-2 bg-growth-600 text-white rounded-lg hover:bg-growth-700">
                            Request Translation
                        </Link>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <Link :href="route('translation.my-requests')" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        My Requests
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ languagePairs: Array });
</script>
