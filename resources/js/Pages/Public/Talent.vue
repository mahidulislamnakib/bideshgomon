<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    UserGroupIcon, MagnifyingGlassIcon, DocumentCheckIcon, 
    BriefcaseIcon, GlobeAltIcon, ShieldCheckIcon, ChartBarIcon
} from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/24/solid';

const features = [
    {
        icon: UserGroupIcon,
        title: 'Access Verified Talent',
        description: 'Browse profiles of pre-screened Bangladeshi workers with verified skills and experience.',
    },
    {
        icon: DocumentCheckIcon,
        title: 'Verified Documents',
        description: 'All candidates have verified passports, educational certificates, and work experience.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Multi-Country Ready',
        description: 'Workers with valid visas or ready for visa processing to your destination country.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Background Checked',
        description: 'Comprehensive background verification including police clearance and references.',
    },
];

const industries = [
    'Construction & Infrastructure',
    'Healthcare & Nursing',
    'Hospitality & Tourism',
    'Manufacturing',
    'IT & Technology',
    'Agriculture',
    'Domestic Work',
    'Retail & Sales',
];

const stats = [
    { value: '50,000+', label: 'Active Candidates' },
    { value: '500+', label: 'Partner Employers' },
    { value: '25+', label: 'Countries' },
    { value: '95%', label: 'Placement Success' },
];
</script>

<template>
    <Head title="Find Talent" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white">Find Skilled Bangladeshi Talent</h1>
                            <p class="text-sm text-white/80 mt-0.5">Pre-screened candidates with verified documents and backgrounds</p>
                        </div>
                        <div class="flex gap-3">
                            <Link href="/register?type=employer" class="px-4 py-2 bg-white text-growth-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
                                Post a Job
                            </Link>
                            <Link href="/contact?subject=Talent Search" class="px-4 py-2 border border-white/50 text-white text-sm font-medium rounded-lg hover:bg-white/10 transition-colors">
                                Contact Us
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8">
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div v-for="stat in stats" :key="stat.label" class="text-center">
                            <div class="text-2xl md:text-3xl font-bold text-primary-600">{{ stat.value }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ stat.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Why Hire Through Bidesh Gomon</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="feature in features" :key="feature.title" 
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl mb-4">
                            <component :is="feature.icon" class="h-6 w-6 text-primary-600" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ feature.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Industries -->
            <div class="bg-white dark:bg-neutral-800 py-16">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Industries We Serve</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-for="industry in industries" :key="industry"
                            class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-neutral-700 rounded-lg">
                            <CheckIcon class="h-5 w-5 text-green-500 flex-shrink-0" />
                            <span class="text-gray-900 dark:text-white">{{ industry }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">How It Works</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-primary-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">1</div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Post Your Requirements</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tell us about the positions you need to fill, skills required, and destination country.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-primary-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">2</div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Review Matched Candidates</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">We'll match you with pre-verified candidates that meet your requirements.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-primary-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">3</div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Hire & Onboard</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Interview, hire, and we'll assist with visa processing and documentation.</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="bg-primary-600 py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Ready to Find Your Next Team Member?</h2>
                    <p class="text-white/90 mb-8 max-w-xl mx-auto">
                        Join hundreds of employers who have successfully hired skilled Bangladeshi workers through Bidesh Gomon.
                    </p>
                    <Link href="/register?type=employer" class="inline-flex items-center justify-center px-8 py-3 bg-white text-primary-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                        Get Started Today
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
