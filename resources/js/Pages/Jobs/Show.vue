<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    MapPinIcon, 
    BriefcaseIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    ClockIcon,
    UserGroupIcon,
    AcademicCapIcon,
    XMarkIcon,
    ArrowLeftIcon,
    DocumentTextIcon,
    BuildingOfficeIcon,
    EnvelopeIcon,
    PhoneIcon,
    SparklesIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';
import { CheckBadgeIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    job: Object,
    hasApplied: Boolean,
    application: Object,
    relatedJobs: Array,
});

const showApplicationModal = ref(false);

const form = useForm({
    cover_letter: '',
    user_cv_id: null,
});

const applyForJob = () => {
    form.post(route('jobs.apply', props.job.id), {
        onSuccess: () => {
            showApplicationModal.value = false;
            form.reset();
        },
    });
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
        'internship': 'Internship',
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
</script>

<template>
    <Head :title="job.title" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30">
            
            <!-- Hero Header -->
            <div class="relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-growth-600 via-growth-700 to-emerald-800"></div>
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                    <!-- Back Link -->
                    <Link
                        :href="route('jobs.index')"
                        class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors mb-6 group"
                    >
                        <ArrowLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
                        <span class="font-medium">Back to Jobs</span>
                    </Link>

                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <div class="flex-1 text-white">
                            <!-- Featured Badge -->
                            <div v-if="job.is_featured" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold px-3 py-1.5 rounded-full mb-4">
                                <SparklesIcon class="h-4 w-4" />
                                FEATURED JOB
                            </div>

                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-3">{{ job.title }}</h1>
                            
                            <div class="flex items-center gap-2 text-xl text-white/90 mb-5">
                                <BuildingOfficeIcon class="h-6 w-6" />
                                <span class="font-medium">{{ job.company_name }}</span>
                            </div>

                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2">
                                    <MapPinIcon class="h-5 w-5" />
                                    <span>{{ job.city }}, {{ job.country?.name }}</span>
                                </div>
                                <div :class="['flex items-center gap-2 rounded-xl px-4 py-2', getJobTypeBadgeClass(job.job_type)]">
                                    <BriefcaseIcon class="h-5 w-5" />
                                    <span class="font-medium">{{ getJobTypeLabel(job.job_type) }}</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2">
                                    <CalendarIcon class="h-5 w-5" />
                                    <span>Posted {{ new Date(job.published_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <div class="flex-shrink-0">
                            <button
                                v-if="!hasApplied"
                                @click="showApplicationModal = true"
                                class="w-full lg:w-auto px-8 py-4 bg-white text-growth-700 font-bold text-lg rounded-xl hover:bg-gray-100 transition-all shadow-xl hover:shadow-2xl flex items-center justify-center gap-2"
                            >
                                <DocumentTextIcon class="h-6 w-6" />
                                Apply Now
                            </button>
                            <div v-else class="flex items-center gap-2 px-8 py-4 bg-green-100 text-green-700 rounded-xl font-bold text-lg border-2 border-green-200">
                                <CheckBadgeIcon class="h-6 w-6" />
                                Already Applied
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Salary Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Salary Range</p>
                                    <p class="text-4xl font-bold text-growth-600">{{ formatSalary(job) }}</p>
                                    <p class="text-sm text-gray-500 mt-1">per {{ job.salary_period }}</p>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-growth-100 flex items-center justify-center">
                                    <CurrencyDollarIcon class="h-10 w-10 text-growth-600" />
                                </div>
                            </div>

                            <div v-if="job.benefits && job.benefits.length > 0" class="mt-6 pt-6 border-t border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 mb-3">Benefits & Perks</p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="benefit in job.benefits"
                                        :key="benefit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-growth-50 text-growth-700 rounded-lg text-sm font-medium border border-growth-200"
                                    >
                                        <CheckCircleIcon class="h-4 w-4" />
                                        {{ benefit }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Job Description -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <DocumentTextIcon class="h-6 w-6 text-growth-600" />
                                Job Description
                            </h2>
                            <div class="prose prose-gray max-w-none">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ job.description }}</p>
                            </div>
                        </div>

                        <!-- Responsibilities -->
                        <div v-if="job.responsibilities" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <BriefcaseIcon class="h-6 w-6 text-growth-600" />
                                Responsibilities
                            </h2>
                            <div class="prose prose-gray max-w-none">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ job.responsibilities }}</p>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div v-if="job.requirements" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <AcademicCapIcon class="h-6 w-6 text-growth-600" />
                                Requirements
                            </h2>
                            <div class="prose prose-gray max-w-none">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ job.requirements }}</p>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div v-if="job.skills && job.skills.length > 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Required Skills</h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="skill in job.skills"
                                    :key="skill"
                                    class="px-4 py-2 bg-gradient-to-r from-growth-50 to-emerald-50 text-growth-700 rounded-xl font-medium border border-growth-200"
                                >
                                    {{ skill }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Job Details Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                                <h3 class="font-bold text-white text-lg">Job Details</h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div v-if="job.job_category">
                                    <p class="text-sm text-gray-500 mb-1">Category</p>
                                    <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-medium bg-growth-100 text-growth-700 border border-growth-200">
                                        {{ job.job_category.name }}
                                    </span>
                                </div>

                                <div v-if="job.positions_available">
                                    <p class="text-sm text-gray-500 mb-1">Positions Available</p>
                                    <div class="flex items-center gap-2 text-gray-900 font-medium">
                                        <UserGroupIcon class="h-5 w-5 text-growth-600" />
                                        {{ job.positions_available }} positions
                                    </div>
                                </div>

                                <div v-if="job.experience_years">
                                    <p class="text-sm text-gray-500 mb-1">Experience Required</p>
                                    <p class="text-gray-900 font-medium">{{ job.experience_years }} years</p>
                                </div>

                                <div v-if="job.education_level">
                                    <p class="text-sm text-gray-500 mb-1">Education Level</p>
                                    <div class="flex items-center gap-2 text-gray-900 font-medium">
                                        <AcademicCapIcon class="h-5 w-5 text-growth-600" />
                                        {{ job.education_level }}
                                    </div>
                                </div>

                                <div v-if="job.gender_preference">
                                    <p class="text-sm text-gray-500 mb-1">Gender Preference</p>
                                    <p class="text-gray-900 font-medium capitalize">{{ job.gender_preference }}</p>
                                </div>

                                <div v-if="job.age_min || job.age_max">
                                    <p class="text-sm text-gray-500 mb-1">Age Range</p>
                                    <p class="text-gray-900 font-medium">
                                        {{ job.age_min }}{{ job.age_max ? ` - ${job.age_max}` : '+' }} years
                                    </p>
                                </div>

                                <div v-if="job.application_deadline" class="bg-orange-50 rounded-xl p-4 border border-orange-200">
                                    <p class="text-sm text-orange-600 mb-1 font-medium">Application Deadline</p>
                                    <div class="flex items-center gap-2 text-orange-700 font-bold">
                                        <ClockIcon class="h-5 w-5" />
                                        {{ new Date(job.application_deadline).toLocaleDateString() }}
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-100">
                                    <p class="text-sm text-gray-500 mb-1">Application Fee</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ job.application_fee > 0 ? `৳${Number(job.application_fee).toLocaleString()}` : 'Free' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div v-if="job.contact_email || job.contact_phone" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4 text-lg">Contact Information</h3>
                            <div class="space-y-4">
                                <div v-if="job.contact_email">
                                    <p class="text-sm text-gray-500 mb-1">Email</p>
                                    <a 
                                        :href="`mailto:${job.contact_email}`" 
                                        class="flex items-center gap-2 text-growth-600 hover:text-growth-700 font-medium"
                                    >
                                        <EnvelopeIcon class="h-5 w-5" />
                                        {{ job.contact_email }}
                                    </a>
                                </div>
                                <div v-if="job.contact_phone">
                                    <p class="text-sm text-gray-500 mb-1">Phone</p>
                                    <a 
                                        :href="`tel:${job.contact_phone}`" 
                                        class="flex items-center gap-2 text-growth-600 hover:text-growth-700 font-medium"
                                    >
                                        <PhoneIcon class="h-5 w-5" />
                                        {{ job.contact_phone }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Related Jobs -->
                        <div v-if="relatedJobs?.length > 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4 text-lg">Related Jobs</h3>
                            <div class="space-y-3">
                                <Link
                                    v-for="relatedJob in relatedJobs"
                                    :key="relatedJob.id"
                                    :href="route('jobs.show', relatedJob.id)"
                                    class="block p-4 border-2 border-gray-100 rounded-xl hover:border-growth-300 hover:bg-growth-50/50 transition-all group"
                                >
                                    <p class="font-semibold text-gray-900 group-hover:text-growth-600 transition-colors mb-1">
                                        {{ relatedJob.title }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ relatedJob.company_name }}</p>
                                    <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                                        <MapPinIcon class="h-4 w-4" />
                                        {{ relatedJob.country?.name }}
                                    </p>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Modal -->
            <div v-if="showApplicationModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="showApplicationModal = false"></div>

                    <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl">
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-growth-600 to-growth-700 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                        <DocumentTextIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Apply for this Job</h3>
                                        <p class="text-growth-100 text-sm">{{ job.title }}</p>
                                    </div>
                                </div>
                                <button @click="showApplicationModal = false" class="text-white/80 hover:text-white transition-colors">
                                    <XMarkIcon class="h-6 w-6" />
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <form @submit.prevent="applyForJob" class="p-6">
                            <!-- Fee Notice -->
                            <div class="mb-6 p-4 rounded-xl" :class="job.application_fee > 0 ? 'bg-amber-50 border-2 border-amber-200' : 'bg-green-50 border-2 border-green-200'">
                                <div class="flex items-start gap-3">
                                    <CurrencyDollarIcon :class="['h-6 w-6 mt-0.5', job.application_fee > 0 ? 'text-amber-600' : 'text-green-600']" />
                                    <div>
                                        <p :class="['font-bold text-lg', job.application_fee > 0 ? 'text-amber-800' : 'text-green-800']">
                                            Application Fee: {{ job.application_fee > 0 ? `৳${Number(job.application_fee).toLocaleString()}` : 'Free' }}
                                        </p>
                                        <p v-if="job.application_fee > 0" class="text-sm text-amber-700 mt-1">
                                            This amount will be deducted from your wallet balance.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Cover Letter -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-900 mb-2">
                                    Cover Letter <span class="text-gray-400 font-normal">(Optional)</span>
                                </label>
                                <textarea
                                    v-model="form.cover_letter"
                                    rows="6"
                                    placeholder="Tell us why you're a great fit for this position..."
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-growth-500 text-gray-900 placeholder-gray-400 resize-none"
                                ></textarea>
                                <p v-if="form.errors.cover_letter" class="text-red-600 text-sm mt-2">
                                    {{ form.errors.cover_letter }}
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button
                                    type="button"
                                    @click="showApplicationModal = false"
                                    class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-growth-600 to-growth-700 text-white rounded-xl font-semibold hover:from-growth-700 hover:to-growth-800 transition-all shadow-lg disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    <svg v-if="form.processing" class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ form.processing ? 'Submitting...' : 'Submit Application' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
