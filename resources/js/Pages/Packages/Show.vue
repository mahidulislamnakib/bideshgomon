<template>
    <AuthenticatedLayout>
        <Head :title="package.name" />

        <!-- Header -->
        <div class="bg-gradient-to-r from-sky-600 to-sky-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <Link
                    :href="route('packages.index')"
                    class="inline-flex items-center text-sky-100 hover:text-white mb-4"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Packages
                </Link>

                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-3 mb-2">
                            <h1 class="text-3xl font-bold">{{ package.name }}</h1>
                            <span
                                v-if="package.badge_text"
                                :class="getBadgeClass(package.badge_color)"
                                class="text-xs font-bold px-3 py-1 rounded-full"
                            >
                                {{ package.badge_text }}
                            </span>
                        </div>
                        <p class="text-sky-100 text-lg">{{ package.service_module.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Description -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ package.description }}</p>
                    </div>

                    <!-- Features -->
                    <div v-if="package.features && package.features.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Features</h2>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <li
                                v-for="(feature, index) in package.features"
                                :key="index"
                                class="flex items-start space-x-3"
                            >
                                <CheckCircleIcon class="h-6 w-6 text-green-500 flex-shrink-0 mt-0.5" />
                                <span class="text-gray-700">{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Inclusions -->
                    <div v-if="package.inclusions && package.inclusions.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">What's Included</h2>
                        <ul class="space-y-2">
                            <li
                                v-for="(item, index) in package.inclusions"
                                :key="index"
                                class="flex items-start space-x-3"
                            >
                                <PlusCircleIcon class="h-5 w-5 text-sky-500 flex-shrink-0 mt-0.5" />
                                <span class="text-gray-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Exclusions -->
                    <div v-if="package.exclusions && package.exclusions.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">What's Not Included</h2>
                        <ul class="space-y-2">
                            <li
                                v-for="(item, index) in package.exclusions"
                                :key="index"
                                class="flex items-start space-x-3"
                            >
                                <MinusCircleIcon class="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5" />
                                <span class="text-gray-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Specifications -->
                    <div v-if="package.specifications && Object.keys(package.specifications).length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Specifications</h2>
                        <dl class="grid grid-cols-1 gap-4">
                            <div
                                v-for="(value, key) in package.specifications"
                                :key="key"
                                class="border-b border-gray-200 pb-3 last:border-0"
                            >
                                <dt class="text-sm font-medium text-gray-500">{{ key }}</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ value }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Price Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                        <div class="text-center">
                            <!-- Price -->
                            <div class="text-4xl font-bold text-brand-red-600 mb-2">
                                ৳{{ package.price.toLocaleString() }}
                            </div>
                            <div v-if="package.price_unit !== 'fixed'" class="text-gray-600 mb-4">
                                per {{ package.price_unit.replace('_', ' ') }}
                            </div>

                            <!-- Discount -->
                            <div v-if="package.original_price" class="mb-4">
                                <span class="text-lg line-through text-gray-400">৳{{ package.original_price.toLocaleString() }}</span>
                                <span class="ml-2 inline-block bg-green-100 text-green-800 text-sm font-bold px-2 py-1 rounded">
                                    {{ package.discount_percentage }}% OFF
                                </span>
                            </div>

                            <!-- Availability Warning -->
                            <div v-if="package.spots_left !== null && package.spots_left <= 10" class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                                <ClockIcon class="h-5 w-5 text-red-600 mx-auto mb-1" />
                                <p class="text-sm text-red-800 font-medium">
                                    Only {{ package.spots_left }} spots left!
                                </p>
                            </div>

                            <!-- Availability Dates -->
                            <div v-if="package.available_from || package.available_until" class="text-sm text-gray-600 mb-4">
                                <CalendarIcon class="h-5 w-5 mx-auto mb-1 text-gray-400" />
                                <div v-if="package.available_from">
                                    From: {{ formatDate(package.available_from) }}
                                </div>
                                <div v-if="package.available_until">
                                    Until: {{ formatDate(package.available_until) }}
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <button
                                v-if="package.is_available"
                                class="w-full bg-brand-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-colors duration-200"
                            >
                                Book Now
                            </button>
                            <div v-else class="w-full bg-gray-200 text-gray-500 font-bold py-3 px-6 rounded-lg">
                                Not Available
                            </div>

                            <!-- Contact Link -->
                            <a
                                href="#"
                                class="block mt-4 text-sm text-brand-red-600 hover:text-sky-700 font-medium"
                            >
                                Have questions? Contact us
                            </a>
                        </div>
                    </div>

                    <!-- Service Info -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Service Details</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Service:</span>
                                <Link
                                    :href="route('services.show', package.service_module.slug)"
                                    class="text-brand-red-600 hover:text-sky-700 font-medium"
                                >
                                    {{ package.service_module.name }}
                                </Link>
                            </div>
                            <div v-if="package.max_bookings" class="flex justify-between">
                                <span class="text-gray-600">Total Capacity:</span>
                                <span class="font-medium text-gray-900">{{ package.max_bookings }} bookings</span>
                            </div>
                            <div v-if="package.current_bookings > 0" class="flex justify-between">
                                <span class="text-gray-600">Already Booked:</span>
                                <span class="font-medium text-gray-900">{{ package.current_bookings }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Packages -->
            <div v-if="relatedPackages.length > 0" class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Similar Packages</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="pkg in relatedPackages"
                        :key="pkg.id"
                        class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <span
                                v-if="pkg.badge_text"
                                :class="getBadgeClass(pkg.badge_color)"
                                class="inline-block text-xs font-bold px-2 py-1 rounded-full mb-2"
                            >
                                {{ pkg.badge_text }}
                            </span>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ pkg.name }}</h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ pkg.short_description }}</p>
                            
                            <div class="flex items-baseline justify-between mb-4">
                                <div class="text-2xl font-bold text-brand-red-600">
                                    ৳{{ pkg.price.toLocaleString() }}
                                </div>
                                <div v-if="pkg.original_price" class="text-sm line-through text-gray-400">
                                    ৳{{ pkg.original_price.toLocaleString() }}
                                </div>
                            </div>

                            <Link
                                :href="route('packages.show', pkg.slug)"
                                class="block text-center px-4 py-2 bg-sky-100 text-sky-700 font-medium rounded-md hover:bg-sky-200 transition-colors"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    CheckCircleIcon,
    PlusCircleIcon,
    MinusCircleIcon,
    ClockIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    package: Object,
    relatedPackages: Array,
});

function getBadgeClass(color) {
    const colors = {
        blue: 'bg-sky-100 text-sky-800',
        green: 'bg-green-100 text-green-800',
        purple: 'bg-purple-100 text-purple-800',
        red: 'bg-red-100 text-red-800',
        yellow: 'bg-yellow-100 text-yellow-800',
    };
    return colors[color] || colors.blue;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>
