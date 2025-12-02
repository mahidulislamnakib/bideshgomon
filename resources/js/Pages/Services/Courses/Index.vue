<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, AcademicCapIcon, BanknotesIcon, CalendarIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    courses: Object,
    filters: Object,
    universities: Array,
    subjects: Array,
    levels: Array,
    studyModes: Array,
    stats: Object
});

// Filter state
const search = ref(props.filters.search || '');
const university = ref(props.filters.university_id || '');
const subject = ref(props.filters.subject || '');
const level = ref(props.filters.level || '');
const studyMode = ref(props.filters.study_mode || '');
const tuitionMax = ref(props.filters.tuition_max || '');
const durationMax = ref(props.filters.duration_max || '');
const scholarships = ref(props.filters.scholarships || false);
const sort = ref(props.filters.sort || 'name');
const direction = ref(props.filters.direction || 'asc');

// Apply filters
const applyFilters = () => {
    router.get(route('courses.index'), {
        search: search.value,
        university_id: university.value,
        subject: subject.value,
        level: level.value,
        study_mode: studyMode.value,
        tuition_max: tuitionMax.value,
        duration_max: durationMax.value,
        scholarships: scholarships.value ? 1 : 0,
        sort: sort.value,
        direction: direction.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    university.value = '';
    subject.value = '';
    level.value = '';
    studyMode.value = '';
    tuitionMax.value = '';
    durationMax.value = '';
    scholarships.value = false;
    sort.value = 'name';
    direction.value = 'asc';
    applyFilters();
};

// Watch for changes and apply filters
watch([search, university, subject, level, studyMode, tuitionMax, durationMax, scholarships, sort, direction], () => {
    applyFilters();
}, { debounce: 500 });
</script>

<template>
    <Head title="Course Catalog" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Course Catalog</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Browse courses from top universities worldwide</p>
                </div>

                <!-- Stats Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <AcademicCapIcon class="h-8 w-8 text-ocean-500" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Courses</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <UserGroupIcon class="h-8 w-8 text-ocean-500" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Universities</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.universities }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <BanknotesIcon class="h-8 w-8 text-ocean-500" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">With Scholarships</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.with_scholarships }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <CalendarIcon class="h-8 w-8 text-ocean-500" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Subjects</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.subjects }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Filters Sidebar -->
                    <div class="lg:w-1/4">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 sticky top-4">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h2>
                                <button @click="clearFilters" class="text-sm text-brand-red-600 hover:text-ocean-700">Clear All</button>
                            </div>

                            <!-- Search -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <input v-model="search" type="text" placeholder="Course name or code..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                </div>
                            </div>

                            <!-- University -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">University</label>
                                <select v-model="university" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">All Universities</option>
                                    <option v-for="uni in universities" :key="uni.id" :value="uni.id">{{ uni.name }}</option>
                                </select>
                            </div>

                            <!-- Subject -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject</label>
                                <select v-model="subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">All Subjects</option>
                                    <option v-for="subj in subjects" :key="subj" :value="subj">{{ subj }}</option>
                                </select>
                            </div>

                            <!-- Level -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Level</label>
                                <select v-model="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">All Levels</option>
                                    <option v-for="lvl in levels" :key="lvl" :value="lvl">{{ lvl }}</option>
                                </select>
                            </div>

                            <!-- Study Mode -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Study Mode</label>
                                <select v-model="studyMode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">All Modes</option>
                                    <option v-for="mode in studyModes" :key="mode" :value="mode">{{ mode }}</option>
                                </select>
                            </div>

                            <!-- Tuition Max -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Tuition (USD)</label>
                                <input v-model="tuitionMax" type="number" placeholder="50000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>

                            <!-- Duration Max -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Duration (months)</label>
                                <input v-model="durationMax" type="number" placeholder="48" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>

                            <!-- Scholarships -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input v-model="scholarships" type="checkbox" class="rounded border-gray-300 text-brand-red-600 focus:ring-brand-red-600" />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Scholarships Available</span>
                                </label>
                            </div>

                            <!-- Sort -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                                <select v-model="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="name">Course Name</option>
                                    <option value="tuition_fee">Tuition Fee</option>
                                    <option value="duration_months">Duration</option>
                                    <option value="average_rating">Rating</option>
                                    <option value="start_date">Start Date</option>
                                </select>
                            </div>

                            <!-- Direction -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order</label>
                                <select v-model="direction" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="asc">Ascending</option>
                                    <option value="desc">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Grid -->
                    <div class="lg:w-3/4">
                        <div v-if="courses.data.length === 0" class="text-center py-12">
                            <AcademicCapIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No courses found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters</p>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Link v-for="course in courses.data" :key="course.id" :href="route('courses.show', course.id)" class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <span class="inline-block px-2 py-1 text-xs font-semibold text-ocean-800 bg-ocean-100 rounded-full">{{ course.code }}</span>
                                        <span v-if="course.is_featured" class="inline-block ml-2 px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Featured</span>
                                    </div>
                                    <span v-if="course.scholarships_available" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                        <BanknotesIcon class="h-3 w-3 mr-1" />
                                        Scholarship
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ course.name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ course.university.name }}</p>

                                <div class="flex flex-wrap gap-2 mb-3">
                                    <span class="inline-block px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded">{{ course.subject }}</span>
                                    <span class="inline-block px-2 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded">{{ course.level }}</span>
                                    <span class="inline-block px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded">{{ course.study_mode }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    <div>
                                        <span class="font-medium">Duration:</span> {{ course.formatted_duration }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Tuition:</span> {{ course.formatted_tuition }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span :class="{
                                        'bg-green-100 text-green-800': course.availability_status === 'Open',
                                        'bg-yellow-100 text-yellow-800': course.availability_status === 'Limited Seats',
                                        'bg-red-100 text-red-800': course.availability_status === 'Full',
                                        'bg-gray-100 text-gray-800': course.availability_status === 'Inactive'
                                    }" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ course.availability_status }}
                                    </span>
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <span class="text-yellow-500">★</span>
                                        <span class="ml-1">{{ course.average_rating }} ({{ course.reviews_count }})</span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <!-- Pagination -->
                        <div v-if="courses.links.length > 3" class="mt-8 flex justify-center">
                            <nav class="flex items-center space-x-2">
                                <Link v-for="link in courses.links" :key="link.label" :href="link.url" :class="{
                                    'bg-brand-red-600 text-white': link.active,
                                    'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300': !link.active,
                                    'opacity-50 cursor-not-allowed': !link.url
                                }" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-ocean-50 dark:hover:bg-gray-700" v-html="link.label"></Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
