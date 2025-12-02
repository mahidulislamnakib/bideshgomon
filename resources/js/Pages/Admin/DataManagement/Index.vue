<template>
    <Head title="Data Management" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Data Management</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage all pre-seeded reference data with CRUD operations and CSV import/export
                    </p>
                </div>

                <!-- Statistics Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-brand-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Geographic Data</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.geographic || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Professional Data</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.professional || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Content Data</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.content || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Management Sections -->
                <div class="space-y-8">
                    <!-- Geographic Data -->
                    <section>
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Geographic & Location Data</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <DataCard
                                title="Countries"
                                :count="60"
                                icon="globe"
                                description="Manage countries with ISO codes, flags, and regions"
                                :route="route('admin.data.countries.index')"
                                color="blue"
                            />
                            <DataCard
                                title="Cities"
                                :count="70"
                                icon="building"
                                description="Major cities with coordinates and timezones"
                                :route="route('admin.data.cities.index')"
                                color="blue"
                            />
                            <DataCard
                                title="Airports"
                                :count="55"
                                icon="plane"
                                description="International airports with IATA codes"
                                route="#"
                                color="blue"
                            />
                            <DataCard
                                title="Currencies"
                                :count="42"
                                icon="currency"
                                description="World currencies with BDT exchange rates"
                                :route="route('admin.data.currencies.index')"
                                color="blue"
                            />
                        </div>
                    </section>

                    <!-- Professional & Education Data -->
                    <section>
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Professional & Education Data</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <DataCard
                                title="Skills"
                                :count="100"
                                icon="star"
                                description="Professional skills database"
                                :route="route('admin.data.skills.index')"
                                color="green"
                            />
                            <DataCard
                                title="Skill Categories"
                                :count="15"
                                icon="folder"
                                description="Skill categorization hierarchy"
                                route="#"
                                color="green"
                            />
                            <DataCard
                                title="Job Categories"
                                :count="73"
                                icon="briefcase"
                                description="Hierarchical job classifications"
                                route="#"
                                color="green"
                            />
                            <DataCard
                                title="Degrees"
                                :count="40"
                                icon="academic"
                                description="Educational qualifications"
                                :route="route('admin.data.degrees.index')"
                                color="green"
                            />
                            <DataCard
                                title="Languages"
                                :count="35"
                                icon="language"
                                description="World languages with proficiency levels"
                                :route="route('admin.data.languages.index')"
                                color="green"
                            />
                            <DataCard
                                title="Language Tests"
                                :count="25"
                                icon="clipboard"
                                description="IELTS, TOEFL, and other test types"
                                route="#"
                                color="green"
                            />
                        </div>
                    </section>

                    <!-- System & Content Data -->
                    <section>
                        <h2 class="text-xl font-bold text-gray-900 mb-4">System & Content Data</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <DataCard
                                title="Service Categories"
                                :count="8"
                                icon="grid"
                                description="Main service classification"
                                route="#"
                                color="purple"
                            />
                            <DataCard
                                title="Blog Categories"
                                :count="10"
                                icon="book"
                                description="Blog content categories"
                                :route="route('admin.data.blog-categories.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Blog Tags"
                                :count="44"
                                icon="tag"
                                description="Content tagging system"
                                :route="route('admin.data.blog-tags.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Document Types"
                                :count="10"
                                icon="document"
                                description="Required document classifications"
                                :route="route('admin.data.document-types.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Bank Names"
                                :count="50"
                                icon="bank"
                                description="Bangladeshi bank directory"
                                :route="route('admin.data.bank-names.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Visa Types"
                                :count="20"
                                icon="passport"
                                description="Visa category classifications"
                                :route="route('admin.data.visa-types.index')"
                                color="purple"
                            />
                        </div>
                    </section>
                </div>

                <!-- Bulk Operations -->
                <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-brand-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Bulk Import/Export</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Each data section supports CSV bulk upload and export. Download templates, fill in your data, and import hundreds of records at once.
                            </p>
                            <div class="mt-4 flex gap-3">
                                <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    View Documentation
                                </a>
                                <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download All Templates
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataCard from '@/Components/Admin/DataManagement/DataCard.vue';
import { computed } from 'vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            geographic: 227,
            professional: 283,
            content: 72
        })
    }
});
</script>
