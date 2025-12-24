<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    GlobeAltIcon, UserGroupIcon, ShieldCheckIcon, HeartIcon,
    BuildingOffice2Icon, AcademicCapIcon, BriefcaseIcon, DocumentCheckIcon
} from '@heroicons/vue/24/outline';

const stats = [
    { label: 'Users Served', value: '50,000+' },
    { label: 'Visa Applications', value: '25,000+' },
    { label: 'Countries Covered', value: '150+' },
    { label: 'Success Rate', value: '98%' },
];

const values = [
    {
        icon: ShieldCheckIcon,
        title: 'Trust & Transparency',
        description: 'We believe in honest, transparent services with no hidden fees or false promises.'
    },
    {
        icon: UserGroupIcon,
        title: 'User-First Approach',
        description: 'Every feature we build is designed with our users\' needs and convenience in mind.'
    },
    {
        icon: GlobeAltIcon,
        title: 'Global Opportunities',
        description: 'We connect Bangladeshis to opportunities worldwide through comprehensive services.'
    },
    {
        icon: HeartIcon,
        title: 'Community Support',
        description: 'We support our community through every step of their international journey.'
    },
];

const services = [
    { icon: DocumentCheckIcon, name: 'Visa Services', description: 'Expert guidance for all visa types' },
    { icon: BriefcaseIcon, name: 'Job Placement', description: 'International career opportunities' },
    { icon: AcademicCapIcon, name: 'Education Support', description: 'Study abroad assistance' },
    { icon: BuildingOffice2Icon, name: 'Document Services', description: 'Complete documentation support' },
];

const team = [
    { name: 'Mohammad Rahman', role: 'Founder & CEO', image: null },
    { name: 'Fatima Akter', role: 'Head of Operations', image: null },
    { name: 'Kamal Hossain', role: 'Visa Specialist', image: null },
    { name: 'Nusrat Jahan', role: 'Customer Success', image: null },
];
</script>

<template>
    <Head title="About Us" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">About Bidesh Gomon</h1>
                    <p class="text-sm text-white/80 mt-0.5">
                        Your trusted partner for global opportunities
                    </p>
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

            <!-- Our Story -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Our Story</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Bidesh Gomon was founded in 2023 with a simple mission: to make international opportunities 
                            accessible to every Bangladeshi. We understood the challenges faced by those seeking to 
                            work, study, or travel abroad – complex visa processes, unreliable agencies, and lack 
                            of proper guidance.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Today, we've helped over 50,000 users navigate their international journeys successfully. 
                            Our platform brings together verified agencies, comprehensive visa information, job 
                            opportunities, and all the resources needed for a successful abroad experience.
                        </p>
                        <Link href="/services" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium">
                            Explore Our Services →
                        </Link>
                    </div>
                    <div class="bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 rounded-2xl p-8 h-64 flex items-center justify-center">
                        <GlobeAltIcon class="h-32 w-32 text-primary-600/50" />
                    </div>
                </div>
            </div>

            <!-- Our Values -->
            <div class="bg-white dark:bg-neutral-800 py-16">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Our Values</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="value in values" :key="value.title" class="text-center p-6">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl mb-4">
                                <component :is="value.icon" class="h-6 w-6 text-primary-600" />
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ value.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ value.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Overview -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">What We Offer</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="service in services" :key="service.name" 
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 hover:shadow-lg transition-shadow">
                        <component :is="service.icon" class="h-8 w-8 text-primary-600 mb-4" />
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ service.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ service.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Team -->
            <div class="bg-gray-100 dark:bg-neutral-800/50 py-16">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Our Team</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="member in team" :key="member.name" class="text-center">
                            <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">{{ member.name.charAt(0) }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ member.name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ member.role }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Ready to Start Your Journey?</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-xl mx-auto">
                    Join thousands of Bangladeshis who have successfully achieved their international dreams with Bidesh Gomon.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/services" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                        Explore Services
                    </Link>
                    <Link href="/contact" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                        Contact Us
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
