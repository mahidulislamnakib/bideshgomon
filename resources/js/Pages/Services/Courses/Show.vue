<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AcademicCapIcon, ClockIcon, BanknotesIcon, CalendarIcon, UserGroupIcon, CheckCircleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    course: Object,
    relatedCourses: Array
});

const { formatDate } = useBangladeshFormat();
</script>

<template>
    <Head :title="course.name" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link :href="route('courses.index')" class="inline-flex items-center text-growth-600 hover:text-ocean-700 mb-6">
                    <ArrowLeftIcon class="h-5 w-5 mr-2" />
                    Back to Courses
                </Link>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <!-- Header -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-block px-3 py-1 text-sm font-semibold text-ocean-800 bg-ocean-100 rounded-full">{{ course.code }}</span>
                                        <span v-if="course.is_featured" class="inline-block px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full">Featured</span>
                                    </div>
                                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ course.name }}</h1>
                                    <Link :href="route('universities.show', course.university.id)" class="text-lg text-growth-600 hover:text-ocean-700">{{ course.university.name }}</Link>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span class="inline-block px-3 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded">{{ course.subject }}</span>
                                <span class="inline-block px-3 py-1 text-sm font-medium text-purple-800 bg-purple-100 rounded">{{ course.level }}</span>
                                <span class="inline-block px-3 py-1 text-sm font-medium text-gray-800 bg-gray-100 rounded">{{ course.study_mode }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Course Overview</h2>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ course.description }}</p>
                        </div>

                        <!-- Course Details -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Course Details</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-start">
                                    <AcademicCapIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Degree Type</p>
                                        <p class="text-gray-900 dark:text-white">{{ course.degree_type }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <ClockIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Duration</p>
                                        <p class="text-gray-900 dark:text-white">{{ course.formatted_duration }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <UserGroupIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Language</p>
                                        <p class="text-gray-900 dark:text-white">{{ course.language }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <AcademicCapIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Credits</p>
                                        <p class="text-gray-900 dark:text-white">{{ course.credits }} Credits</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Financial Information</h2>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <BanknotesIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tuition Fee</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ course.formatted_tuition }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Per Year</p>
                                    </div>
                                </div>
                                <div v-if="course.scholarships_available" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600 mr-2" />
                                    <span class="text-sm font-medium text-green-800 dark:text-green-400">Scholarships available for eligible students</span>
                                </div>
                            </div>
                        </div>

                        <!-- Prerequisites -->
                        <div v-if="course.prerequisites && course.prerequisites.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Prerequisites</h2>
                            <ul class="space-y-2">
                                <li v-for="(prereq, index) in course.prerequisites" :key="index" class="flex items-start">
                                    <CheckCircleIcon class="h-5 w-5 text-ocean-500 mr-3 flex-shrink-0 mt-0.5" />
                                    <span class="text-gray-700 dark:text-gray-300">{{ prereq }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Learning Outcomes -->
                        <div v-if="course.learning_outcomes && course.learning_outcomes.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Learning Outcomes</h2>
                            <ul class="space-y-2">
                                <li v-for="(outcome, index) in course.learning_outcomes" :key="index" class="flex items-start">
                                    <CheckCircleIcon class="h-5 w-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" />
                                    <span class="text-gray-700 dark:text-gray-300">{{ outcome }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Enrollment Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Enrollment Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-start">
                                    <CalendarIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Start Date</p>
                                        <p class="text-gray-900 dark:text-white">{{ formatDate(course.start_date) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <CalendarIcon class="h-6 w-6 text-red-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Application Deadline</p>
                                        <p class="text-gray-900 dark:text-white">{{ formatDate(course.application_deadline) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <UserGroupIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Enrollment</p>
                                        <p class="text-gray-900 dark:text-white">{{ course.current_enrollment }} / {{ course.enrollment_capacity }} Students</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <CheckCircleIcon class="h-6 w-6 text-ocean-500 mr-3 flex-shrink-0 mt-1" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Availability</p>
                                        <span :class="{
                                            'bg-green-100 text-green-800': course.availability_status === 'Open',
                                            'bg-yellow-100 text-yellow-800': course.availability_status === 'Limited Seats',
                                            'bg-red-100 text-red-800': course.availability_status === 'Full',
                                            'bg-gray-100 text-gray-800': course.availability_status === 'Inactive'
                                        }" class="inline-block px-3 py-1 text-sm font-semibold rounded-full mt-1">
                                            {{ course.availability_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ratings -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Student Reviews</h2>
                            <div class="flex items-center">
                                <div class="flex items-center text-2xl">
                                    <span class="text-yellow-500 mr-2">★★★★★</span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ course.average_rating }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Based on {{ course.reviews_count }} reviews</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <!-- Quick Info -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6 sticky top-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Information</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Level:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ course.level }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Mode:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ course.study_mode }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Duration:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ course.formatted_duration }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                    <span :class="{
                                        'text-green-600': course.availability_status === 'Open',
                                        'text-yellow-600': course.availability_status === 'Limited Seats',
                                        'text-red-600': course.availability_status === 'Full',
                                        'text-gray-600': course.availability_status === 'Inactive'
                                    }" class="font-medium">{{ course.availability_status }}</span>
                                </div>
                            </div>

                            <div class="mt-6 space-y-3">
                                <button class="w-full bg-growth-600 text-white px-4 py-2 rounded-lg hover:bg-growth-700 transition-colors font-medium">
                                    Apply Now
                                </button>
                                <Link :href="route('courses.index')" class="block w-full text-center border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                                    Browse More Courses
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Courses -->
                <div v-if="relatedCourses.length > 0" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Related Courses</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link v-for="related in relatedCourses" :key="related.id" :href="route('courses.show', related.id)" class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-ocean-800 bg-ocean-100 rounded-full mb-3">{{ related.code }}</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ related.name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ related.university.name }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ related.formatted_duration }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ related.formatted_tuition }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
