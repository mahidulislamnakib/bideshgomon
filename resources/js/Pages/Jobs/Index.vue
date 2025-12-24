<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    MagnifyingGlassIcon, 
    MapPinIcon, 
    BriefcaseIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    FunnelIcon,
    XMarkIcon,
    SparklesIcon,
    ClockIcon,
    GlobeAltIcon,
    BuildingOfficeIcon,
    AdjustmentsHorizontalIcon
} from '@heroicons/vue/24/outline';
import { CheckBadgeIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    jobs: Object,
    countries: Array,
    categories: Array,
    jobTypes: Array,
    filters: Object,
    userApplications: Array,
});

const search = ref(props.filters?.search || '');
const selectedCountry = ref(props.filters?.country_id || '');
const selectedCategory = ref(props.filters?.job_category_id || '');
const selectedJobType = ref(props.filters?.job_type || '');
const salaryMin = ref(props.filters?.salary_min || '');
const salaryMax = ref(props.filters?.salary_max || '');
const showFilters = ref(false);

const applyFilters = () => {
    router.get('/jobs', {
        search: search.value,
        country_id: selectedCountry.value,
        job_category_id: selectedCategory.value,
        job_type: selectedJobType.value,
        salary_min: salaryMin.value,
        salary_max: salaryMax.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedCountry.value = '';
    selectedCategory.value = '';
    selectedJobType.value = '';
    salaryMin.value = '';
    salaryMax.value = '';
    router.get('/jobs');
};

const hasFilters = computed(() => {
    return search.value || selectedCountry.value || selectedCategory.value || selectedJobType.value || salaryMin.value || salaryMax.value;
});

const activeFilterCount = computed(() => {
    return [search.value, selectedCountry.value, selectedCategory.value, selectedJobType.value, salaryMin.value, salaryMax.value].filter(f => f).length;
});

const hasApplied = (jobId) => {
    return props.userApplications?.includes(jobId);
};

const formatSalary = (job) => {
    const symbol = job.salary_currency === 'BDT' ? '৳' : job.salary_currency;
    if (job.salary_max && job.salary_max > job.salary_min) {
        return `${symbol}${Number(job.salary_min).toLocaleString()} - ${symbol}${Number(job.salary_max).toLocaleString()}`;
    }
    return `${symbol}${Number(job.salary_min).toLocaleString()}`;
};

const getJobTypeLabel = (type) => {
    const labels = {
        'full_time': 'Full Time',
        'part_time': 'Part Time',
        'contract': 'Contract',
        'temporary': 'Temporary',
        'seasonal': 'Seasonal',
    };
    return labels[type] || type?.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) || 'N/A';
};

const getJobTypeBadgeClass = (type) => {
    const classes = {
        'full_time': 'bg-green-100 text-green-700 border-green-200',
        'part_time': 'bg-blue-100 text-blue-700 border-blue-200',
        'contract': 'bg-purple-100 text-purple-700 border-purple-200',
        'temporary': 'bg-orange-100 text-orange-700 border-orange-200',
        'seasonal': 'bg-amber-100 text-amber-700 border-amber-200',
    };
    return classes[type] || 'bg-gray-100 text-gray-700 border-gray-200';
};

// Quick stats
const stats = computed(() => [
    { label: 'Total Jobs', value: props.jobs?.total || 0, icon: BriefcaseIcon, color: 'bg-growth-100 text-growth-600' },
    { label: 'Countries', value: props.countries?.length || 0, icon: GlobeAltIcon, color: 'bg-blue-100 text-blue-600' },
    { label: 'Categories', value: props.categories?.length || 0, icon: BuildingOfficeIcon, color: 'bg-purple-100 text-purple-600' },
]);
</script>

<template>
    <Head title="Job Opportunities" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-950">
            
            <!-- Compact Hero with Search -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white">Job Opportunities</h1>
                            <p class="text-sm text-white/80 mt-0.5">Find your dream job abroad</p>
                        </div>
                        <Link
                            :href="route('jobs.my-applications')"
                            class="px-4 py-2 text-sm font-medium bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors"
                        >
                            My Applications
                        </Link>
                    </div>
                    
                    <!-- Search in Hero -->
                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search jobs, companies, skills..."
                            class="w-48 md:w-64 px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50 placeholder-gray-500"
                        />
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium bg-white text-growth-600 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            Search
                        </button>
                        <button
                            @click="showFilters = !showFilters"
                            class="px-4 py-2 text-sm font-medium bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors flex items-center gap-2"
                        >
                            <AdjustmentsHorizontalIcon class="h-4 w-4" />
                            Filters
                            <span v-if="activeFilterCount > 0" class="bg-white text-growth-600 px-1.5 py-0.5 rounded text-xs font-bold">
                                {{ activeFilterCount }}
                            </span>
                        </button>
                        <button
                            v-if="hasFilters"
                            @click="clearFilters"
                            class="px-3 py-2 text-sm font-medium text-white/80 hover:text-white transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Panel (Collapsible) -->
            <div v-if="showFilters" class="bg-white dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700 px-4 py-4 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <!-- Country Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                            <select
                                v-model="selectedCountry"
                                @change="applyFilters"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-growth-500 dark:bg-neutral-700"
                            >
                                <option value="">All Countries</option>
                                <option v-for="country in countries" :key="country.id" :value="country.id">
                                    {{ country.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select
                                v-model="selectedCategory"
                                @change="applyFilters"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-growth-500 dark:bg-neutral-700"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Job Type Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Job Type</label>
                            <select
                                v-model="selectedJobType"
                                @change="applyFilters"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-growth-500 dark:bg-neutral-700"
                            >
                                <option value="">All Types</option>
                                <option v-for="type in jobTypes" :key="type" :value="type">
                                    {{ getJobTypeLabel(type) }}
                                </option>
                            </select>
                        </div>

                        <!-- Min Salary -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Salary (৳)</label>
                            <input
                                v-model="salaryMin"
                                @change="applyFilters"
                                type="number"
                                min="0"
                                placeholder="e.g. 30000"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-growth-500 dark:bg-neutral-700"
                            />
                        </div>

                        <!-- Max Salary -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Salary (৳)</label>
                            <input
                                v-model="salaryMax"
                                @change="applyFilters"
                                type="number"
                                min="0"
                                placeholder="e.g. 80000"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-growth-500 dark:bg-neutral-700"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
                <!-- Results Count -->
                <p class="text-xs text-gray-500 dark:text-gray-400 py-4">
                    {{ jobs?.total || 0 }} jobs found
                    <span v-if="hasFilters"> (filtered)</span>
                </p>

                <!-- No Results -->
                <div v-if="!jobs?.data?.length" class="text-center py-12">
                    <BriefcaseIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No jobs found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or search terms.</p>
                    <button
                        v-if="hasFilters"
                        @click="clearFilters"
                        class="mt-4 px-4 py-2 text-sm font-medium text-growth-600 hover:text-growth-700"
                    >
                        <XMarkIcon class="h-5 w-5" />
                        Clear All Filters
                    </button>
                </div>

                <!-- Job Cards Grid -->
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="job in jobs.data"
                        :key="job.id"
                        :href="route('jobs.show', job.id)"
                        class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border-2 border-gray-100 hover:border-growth-200 transition-all duration-300 overflow-hidden"
                    >
                        <!-- Featured Badge -->
                        <div v-if="job.is_featured" class="bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2 flex items-center justify-center gap-2">
                            <SparklesIcon class="h-4 w-4 text-white" />
                            <span class="text-white text-xs font-bold tracking-wide uppercase">Featured</span>
                        </div>

                        <div class="p-6">
                            <!-- Title & Company -->
                            <div class="mb-4">
                                <h3 class="font-bold text-lg text-gray-900 group-hover:text-growth-600 transition-colors mb-1 line-clamp-2">
                                    {{ job.title }}
                                </h3>
                                <p class="text-sm font-medium text-gray-600 flex items-center gap-1">
                                    <BuildingOfficeIcon class="h-4 w-4" />
                                    {{ job.company_name }}
                                </p>
                            </div>

                            <!-- Location -->
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <MapPinIcon class="h-4 w-4 mr-1.5 text-growth-500" />
                                <span>{{ job.city }}, {{ job.country?.name }}</span>
                            </div>

                            <!-- Tags -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-if="job.job_category" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-growth-50 text-growth-700 border border-growth-200">
                                    {{ job.job_category.name }}
                                </span>
                                <span :class="['px-2.5 py-1 rounded-lg text-xs font-medium border', getJobTypeBadgeClass(job.job_type)]">
                                    {{ getJobTypeLabel(job.job_type) }}
                                </span>
                                <span v-if="job.experience_years" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ job.experience_years }} yrs exp
                                </span>
                            </div>

                            <!-- Salary -->
                            <div class="flex items-center mb-4">
                                <div class="flex items-center text-growth-600 font-bold text-lg">
                                    <CurrencyDollarIcon class="h-5 w-5 mr-1" />
                                    {{ formatSalary(job) }}
                                </div>
                                <span class="text-gray-400 text-sm ml-1">/{{ job.salary_period }}</span>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center text-xs text-gray-500">
                                    <CalendarIcon class="h-4 w-4 mr-1" />
                                    {{ new Date(job.published_at).toLocaleDateString() }}
                                </div>
                                
                                <div v-if="hasApplied(job.id)" class="flex items-center gap-1 text-green-600 text-xs font-semibold bg-green-50 px-2 py-1 rounded-lg">
                                    <CheckBadgeIcon class="h-4 w-4" />
                                    Applied
                                </div>
                                <div v-else-if="job.application_fee > 0" class="text-xs text-gray-600 font-medium">
                                    Fee: ৳{{ Number(job.application_fee).toLocaleString() }}
                                </div>
                                <div v-else class="text-xs text-green-600 font-semibold bg-green-50 px-2 py-1 rounded-lg">
                                    Free to Apply
                                </div>
                            </div>

                            <!-- Deadline Warning -->
                            <div v-if="job.application_deadline" class="mt-3 flex items-center text-xs text-orange-600 bg-orange-50 px-3 py-2 rounded-lg">
                                <ClockIcon class="h-4 w-4 mr-1.5" />
                                Deadline: {{ new Date(job.application_deadline).toLocaleDateString() }}
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="jobs?.links?.length > 3" class="mt-8 flex flex-wrap items-center justify-center gap-1">
                    <component
                        :is="link.url ? Link : 'span'"
                        v-for="link in jobs.links"
                        :key="link.label"
                        :href="link.url || undefined"
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                            link.active
                                ? 'bg-growth-600 text-white'
                                : link.url
                                ? 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 border border-gray-200 dark:border-neutral-700'
                                : 'text-gray-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
