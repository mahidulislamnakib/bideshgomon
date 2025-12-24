<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    BuildingOffice2Icon,
    ArrowLeftIcon,
    PhotoIcon,
    GlobeAltIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    name_bn: '',
    logo: null,
    website_url: '',
    partner_type: 'client',
    sort_order: 0,
    is_active: true
});

const previewUrl = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.partners.store'));
};
</script>

<template>
    <AdminLayout title="Create Partner">
        <Head title="Create Partner" />

        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Hero Header -->
            <div class="relative overflow-hidden rounded-2xl mx-4 mt-4 lg:mx-6 lg:mt-6" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 bg-grid-white/5"></div>
                <div class="relative px-6 py-8 sm:px-8 sm:py-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/10 rounded-xl backdrop-blur-sm">
                                <BuildingOffice2Icon class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white">Add New Partner</h1>
                                <p class="text-gray-300 mt-1">Create a new partner profile</p>
                            </div>
                        </div>
                        <Link :href="route('admin.partners.index')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-white/10 hover:bg-white/20 rounded-xl transition-colors backdrop-blur-sm">
                            <ArrowLeftIcon class="w-4 h-4" />
                            Back to Partners
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="max-w-3xl mx-auto px-4 py-8 lg:px-6">
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Partner Information</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fill in the details below to create a new partner</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Partner Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                required
                                placeholder="Enter partner name"
                                class="w-full px-4 py-3 bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                :class="{ 'border-red-500 dark:border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                        </div>

                        <!-- Name (Bengali) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Partner Name (Bengali)
                            </label>
                            <input 
                                v-model="form.name_bn" 
                                type="text" 
                                placeholder="পার্টনার নাম"
                                class="w-full px-4 py-3 bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                :class="{ 'border-red-500 dark:border-red-500': form.errors.name_bn }"
                            />
                            <p v-if="form.errors.name_bn" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.name_bn }}</p>
                        </div>

                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Logo <span class="text-red-500">*</span>
                            </label>
                            <div 
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition-colors"
                                :class="previewUrl 
                                    ? 'border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-900/20' 
                                    : 'border-gray-300 dark:border-neutral-600 hover:border-primary-400 dark:hover:border-primary-500'"
                            >
                                <div class="space-y-2 text-center">
                                    <div v-if="previewUrl" class="mb-4">
                                        <img :src="previewUrl" alt="Preview" class="mx-auto h-24 w-auto object-contain rounded-2xl"/>
                                    </div>
                                    <PhotoIcon v-else class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                        <label class="relative cursor-pointer rounded-xl font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 focus-within:outline-none">
                                            <span>{{ previewUrl ? 'Change file' : 'Upload a file' }}</span>
                                            <input 
                                                @change="handleFileChange" 
                                                type="file" 
                                                accept="image/*"
                                                class="sr-only"
                                            />
                                        </label>
                                        <p v-if="!previewUrl" class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WebP, SVG up to 2MB</p>
                                </div>
                            </div>
                            <p v-if="form.errors.logo" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.logo }}</p>
                        </div>

                        <!-- Website URL -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Website URL</label>
                            <input 
                                v-model="form.website_url" 
                                type="url" 
                                placeholder="https://example.com"
                                class="w-full px-4 py-3 bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                :class="{ 'border-red-500 dark:border-red-500': form.errors.website_url }"
                            />
                            <p v-if="form.errors.website_url" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.website_url }}</p>
                        </div>

                        <!-- Two Column Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Partner Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Partner Type</label>
                                <select 
                                    v-model="form.partner_type" 
                                    class="w-full px-4 py-3 bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    :class="{ 'border-red-500 dark:border-red-500': form.errors.partner_type }"
                                >
                                    <option value="client">Client</option>
                                    <option value="partner">Partner</option>
                                    <option value="sponsor">Sponsor</option>
                                    <option value="affiliate">Affiliate</option>
                                </select>
                                <p v-if="form.errors.partner_type" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.partner_type }}</p>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Display Order</label>
                                <input 
                                    v-model.number="form.sort_order" 
                                    type="number" 
                                    min="0"
                                    placeholder="0"
                                    class="w-full px-4 py-3 bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    :class="{ 'border-red-500 dark:border-red-500': form.errors.sort_order }"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower numbers appear first</p>
                                <p v-if="form.errors.sort_order" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.sort_order }}</p>
                            </div>
                        </div>

                        <!-- Status Toggle -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-neutral-700/50 rounded-xl">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Status</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Enable this partner to appear on the website</p>
                            </div>
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                :class="form.is_active ? 'bg-primary-600' : 'bg-gray-300 dark:bg-neutral-600'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800"
                            >
                                <span
                                    :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
                                    class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                >
                                    <span
                                        :class="form.is_active ? 'opacity-0 duration-100 ease-out' : 'opacity-100 duration-200 ease-in'"
                                        class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                                    >
                                        <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                                            <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span
                                        :class="form.is_active ? 'opacity-100 duration-200 ease-in' : 'opacity-0 duration-100 ease-out'"
                                        class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                                    >
                                        <CheckIcon class="h-3 w-3 text-primary-600" />
                                    </span>
                                </span>
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-6 border-t border-neutral-100 dark:border-neutral-700">
                            <Link 
                                :href="route('admin.partners.index')" 
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors"
                            >
                                Cancel
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <BuildingOffice2Icon v-else class="w-4 h-4" />
                                {{ form.processing ? 'Creating...' : 'Create Partner' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
