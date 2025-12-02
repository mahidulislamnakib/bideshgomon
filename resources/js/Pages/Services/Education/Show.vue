<template>
    <Head :title="university.name" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <div v-if="university.logo" class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-lg p-2">
                                <img :src="university.logo" :alt="university.name" class="h-full w-full object-contain" />
                            </div>
                            <div v-else class="flex-shrink-0">
                                <div class="h-16 w-16 bg-ocean-100 rounded-lg flex items-center justify-center">
                                    <AcademicCapIcon class="h-8 w-8 text-brand-red-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                    {{ university.name }}
                                </h2>
                                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                    <div class="mt-2 flex items-center text-sm text-gray-500">
                                        <MapPinIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                                        {{ university.city }}, {{ university.country?.name }}
                                    </div>
                                    <div v-if="university.established_year" class="mt-2 flex items-center text-sm text-gray-500">
                                        <CalendarIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                                        Established {{ university.established_year }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <a 
                            v-if="university.website" 
                            :href="university.website" 
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 border border-ocean-300 rounded-md shadow-sm text-sm font-medium text-ocean-700 bg-white hover:bg-ocean-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red-600"
                        >
                            <GlobeAltIcon class="-ml-1 mr-2 h-5 w-5" />
                            Visit Website
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Rankings & Stats -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Rankings & Statistics</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div v-if="university.world_ranking">
                                <dt class="text-sm font-medium text-gray-500">World Ranking</dt>
                                <dd class="mt-1 text-2xl font-semibold" :class="getRankingColor(university.world_ranking)">
                                    #{{ university.world_ranking }}
                                </dd>
                            </div>
                            <div v-if="university.country_ranking">
                                <dt class="text-sm font-medium text-gray-500">Country Ranking</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    #{{ university.country_ranking }}
                                </dd>
                            </div>
                            <div v-if="university.acceptance_rate">
                                <dt class="text-sm font-medium text-gray-500">Acceptance Rate</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ university.acceptance_rate }}%
                                </dd>
                            </div>
                            <div v-if="university.student_count">
                                <dt class="text-sm font-medium text-gray-500">Total Students</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ university.student_count.toLocaleString() }}
                                </dd>
                            </div>
                            <div v-if="university.international_students">
                                <dt class="text-sm font-medium text-gray-500">International Students</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ university.international_students.toLocaleString() }}
                                </dd>
                            </div>
                            <div v-if="university.type">
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                                        'bg-blue-100 text-blue-800': university.type === 'public',
                                        'bg-purple-100 text-purple-800': university.type === 'private'
                                    }">
                                        {{ university.type === 'public' ? 'Public' : 'Private' }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="university.description" class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">About</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ university.description }}</p>
                    </div>

                    <!-- Programs -->
                    <div v-if="university.programs && university.programs.length" class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Available Programs</h3>
                        <div class="flex flex-wrap gap-2">
                            <span 
                                v-for="program in university.programs" 
                                :key="program"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-ocean-100 text-ocean-800"
                            >
                                {{ program }}
                            </span>
                        </div>
                    </div>

                    <!-- Popular Majors -->
                    <div v-if="university.popular_majors && university.popular_majors.length" class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Popular Majors</h3>
                        <ul class="space-y-2">
                            <li 
                                v-for="major in university.popular_majors" 
                                :key="major"
                                class="flex items-center text-gray-700"
                            >
                                <CheckCircleIcon class="h-5 w-5 text-ocean-500 mr-2" />
                                {{ major }}
                            </li>
                        </ul>
                    </div>

                    <!-- Similar Universities -->
                    <div v-if="similarUniversities.length" class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Similar Universities</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Link
                                v-for="uni in similarUniversities"
                                :key="uni.id"
                                :href="route('education.show', uni.id)"
                                class="block p-4 border border-gray-200 rounded-lg hover:border-ocean-300 hover:shadow-md transition-all"
                            >
                                <div class="flex items-start">
                                    <div v-if="uni.logo" class="flex-shrink-0 h-12 w-12 bg-gray-100 rounded p-1">
                                        <img :src="uni.logo" :alt="uni.name" class="h-full w-full object-contain" />
                                    </div>
                                    <div v-else class="flex-shrink-0">
                                        <div class="h-12 w-12 bg-gray-100 rounded flex items-center justify-center">
                                            <AcademicCapIcon class="h-6 w-6 text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ uni.name }}</h4>
                                        <p class="text-sm text-gray-500">{{ uni.city }}, {{ uni.country?.name }}</p>
                                        <p v-if="uni.world_ranking" class="mt-1 text-xs font-medium" :class="getRankingColor(uni.world_ranking)">
                                            World Ranking: #{{ uni.world_ranking }}
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Info -->
                    <div class="bg-ocean-50 border border-ocean-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-ocean-900 mb-4">Quick Information</h3>
                        <dl class="space-y-3">
                            <div v-if="university.formatted_tuition">
                                <dt class="text-sm font-medium text-ocean-700 flex items-center">
                                    <CurrencyDollarIcon class="h-5 w-5 mr-1" />
                                    Annual Tuition
                                </dt>
                                <dd class="mt-1 text-base font-semibold text-ocean-900">
                                    {{ university.formatted_tuition }}
                                </dd>
                            </div>
                            <div v-if="university.application_fee">
                                <dt class="text-sm font-medium text-ocean-700 flex items-center">
                                    <DocumentTextIcon class="h-5 w-5 mr-1" />
                                    Application Fee
                                </dt>
                                <dd class="mt-1 text-base font-semibold text-ocean-900">
                                    ${{ university.application_fee.toLocaleString() }}
                                </dd>
                            </div>
                            <div v-if="university.scholarships_available !== null">
                                <dt class="text-sm font-medium text-ocean-700 flex items-center">
                                    <BanknotesIcon class="h-5 w-5 mr-1" />
                                    Scholarships
                                </dt>
                                <dd class="mt-1">
                                    <span v-if="university.scholarships_available" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Available
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Not Available
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            <a 
                                v-if="university.website" 
                                :href="university.website" 
                                target="_blank"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red-600"
                            >
                                Visit Official Website
                                <ArrowTopRightOnSquareIcon class="ml-2 h-4 w-4" />
                            </a>
                            <Link
                                :href="route('education.index')"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red-600"
                            >
                                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                                Back to Universities
                            </Link>
                        </div>
                    </div>

                    <!-- Featured Badge -->
                    <div v-if="university.is_featured" class="bg-gradient-to-r from-amber-50 to-amber-100 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <StarIcon class="h-6 w-6 text-amber-500 mr-2" />
                            <div>
                                <h4 class="text-sm font-medium text-amber-900">Featured University</h4>
                                <p class="text-xs text-amber-700 mt-1">Recognized for academic excellence</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    AcademicCapIcon,
    MapPinIcon,
    GlobeAltIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    BanknotesIcon,
    CheckCircleIcon,
    StarIcon,
    ArrowTopRightOnSquareIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    university: {
        type: Object,
        required: true
    },
    similarUniversities: {
        type: Array,
        default: () => []
    }
})

function getRankingColor(ranking) {
    if (ranking <= 10) return 'text-amber-600'
    if (ranking <= 50) return 'text-brand-red-600'
    if (ranking <= 100) return 'text-green-600'
    return 'text-gray-600'
}
</script>
