<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BriefcaseIcon, MapPinIcon, ClockIcon, CurrencyBangladeshiIcon,
    HeartIcon, AcademicCapIcon, HomeIcon, SparklesIcon
} from '@heroicons/vue/24/outline';

const benefits = [
    { icon: CurrencyBangladeshiIcon, title: 'Competitive Salary', description: 'Market-leading compensation packages' },
    { icon: HeartIcon, title: 'Health Insurance', description: 'Comprehensive health coverage for you and family' },
    { icon: AcademicCapIcon, title: 'Learning Budget', description: 'Annual budget for courses and conferences' },
    { icon: HomeIcon, title: 'Remote Friendly', description: 'Flexible work-from-home options' },
    { icon: ClockIcon, title: 'Flexible Hours', description: 'Work when you\'re most productive' },
    { icon: SparklesIcon, title: 'Career Growth', description: 'Clear paths for advancement' },
];

const openPositions = [
    {
        id: 1,
        title: 'Senior Software Engineer',
        department: 'Engineering',
        location: 'Dhaka, Bangladesh',
        type: 'Full-time',
        remote: true,
    },
    {
        id: 2,
        title: 'Product Designer',
        department: 'Design',
        location: 'Dhaka, Bangladesh',
        type: 'Full-time',
        remote: true,
    },
    {
        id: 3,
        title: 'Visa Specialist',
        department: 'Operations',
        location: 'Dhaka, Bangladesh',
        type: 'Full-time',
        remote: false,
    },
    {
        id: 4,
        title: 'Customer Success Manager',
        department: 'Support',
        location: 'Dhaka, Bangladesh',
        type: 'Full-time',
        remote: true,
    },
    {
        id: 5,
        title: 'Marketing Manager',
        department: 'Marketing',
        location: 'Dhaka, Bangladesh',
        type: 'Full-time',
        remote: true,
    },
];
</script>

<template>
    <Head title="Careers" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Join Our Team</h1>
                    <p class="text-sm text-white/80 mt-0.5">Help us connect Bangladeshis to opportunities worldwide</p>
                </div>
            </div>

            <!-- Benefits -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Why Work With Us</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="benefit in benefits" :key="benefit.title" 
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                        <component :is="benefit.icon" class="h-8 w-8 text-primary-600 mb-4" />
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ benefit.title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ benefit.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Open Positions -->
            <div class="bg-white dark:bg-neutral-800 py-16">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Open Positions</h2>
                    
                    <div class="space-y-4">
                        <div v-for="position in openPositions" :key="position.id"
                            class="bg-gray-50 dark:bg-neutral-700 rounded-xl p-6 hover:shadow-lg transition-shadow">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ position.title }}</h3>
                                    <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <BriefcaseIcon class="h-4 w-4" />
                                            {{ position.department }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <MapPinIcon class="h-4 w-4" />
                                            {{ position.location }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <ClockIcon class="h-4 w-4" />
                                            {{ position.type }}
                                        </span>
                                        <span v-if="position.remote" class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded-full">
                                            Remote OK
                                        </span>
                                    </div>
                                </div>
                                <a 
                                    href="mailto:careers@bideshgomon.com?subject=Application for {{ position.title }}"
                                    class="inline-flex items-center justify-center px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors whitespace-nowrap"
                                >
                                    Apply Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- No positions fallback -->
                    <div v-if="openPositions.length === 0" class="text-center py-12">
                        <BriefcaseIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No open positions</h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Check back later or send us your resume at careers@bideshgomon.com</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Don't See a Perfect Fit?</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-xl mx-auto">
                    We're always looking for talented people. Send us your resume and we'll keep you in mind for future opportunities.
                </p>
                <a 
                    href="mailto:careers@bideshgomon.com"
                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors"
                >
                    Send Your Resume
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
