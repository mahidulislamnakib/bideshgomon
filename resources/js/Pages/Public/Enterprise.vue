<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BuildingOffice2Icon, ShieldCheckIcon, CogIcon, ChartBarIcon,
    UserGroupIcon, DocumentCheckIcon, PhoneIcon, GlobeAltIcon
} from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/24/solid';

const features = [
    {
        icon: UserGroupIcon,
        title: 'Bulk Hiring Solutions',
        description: 'Recruit and process hundreds of workers simultaneously with our enterprise-grade platform.',
    },
    {
        icon: CogIcon,
        title: 'Custom Integrations',
        description: 'API access and custom integrations with your existing HR and recruitment systems.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Compliance Management',
        description: 'Stay compliant with international labor laws and immigration regulations.',
    },
    {
        icon: ChartBarIcon,
        title: 'Analytics & Reporting',
        description: 'Detailed analytics and custom reports for your recruitment operations.',
    },
];

const benefits = [
    'Dedicated account manager',
    'Priority processing',
    'Custom SLA agreements',
    'Bulk application discounts',
    'API access for integrations',
    'White-label options',
    'On-site training',
    '24/7 premium support',
];

const clients = [
    { name: 'Al Haramain Group', country: 'Saudi Arabia' },
    { name: 'Dubai Construction Co.', country: 'UAE' },
    { name: 'Singapore Healthcare', country: 'Singapore' },
    { name: 'Malaysia Hospitality', country: 'Malaysia' },
];
</script>

<template>
    <Head title="Enterprise Solutions" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <span class="inline-block px-2 py-0.5 bg-white/20 text-white text-xs font-medium rounded mb-2">Enterprise Solutions</span>
                            <h1 class="text-xl md:text-2xl font-bold text-white">Scale Your Recruitment Operations</h1>
                            <p class="text-sm text-white/80 mt-0.5">Enterprise-grade solutions for agencies and corporations</p>
                        </div>
                        <div class="flex gap-3">
                            <Link href="/contact?subject=Enterprise Demo" class="px-4 py-2 bg-white text-growth-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
                                Request Demo
                            </Link>
                            <Link href="/contact?subject=Enterprise Pricing" class="px-4 py-2 border border-white/50 text-white text-sm font-medium rounded-lg hover:bg-white/10 transition-colors">
                                Contact Sales
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Enterprise Features</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="feature in features" :key="feature.title" 
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                        <component :is="feature.icon" class="h-8 w-8 text-primary-600 mb-4" />
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ feature.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Benefits -->
            <div class="bg-white dark:bg-neutral-800 py-16">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Enterprise Benefits</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="benefit in benefits" :key="benefit" class="flex items-center gap-2">
                                    <CheckIcon class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ benefit }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 rounded-2xl p-8 h-64 flex items-center justify-center">
                            <BuildingOffice2Icon class="h-32 w-32 text-primary-600/50" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trusted By -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Trusted By Industry Leaders</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="client in clients" :key="client.name"
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 text-center">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-neutral-700 rounded-xl mx-auto mb-4 flex items-center justify-center">
                            <BuildingOffice2Icon class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ client.name }}</h3>
                        <p class="text-xs text-gray-500">{{ client.country }}</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="bg-gray-900 py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Ready to Transform Your Recruitment?</h2>
                    <p class="text-gray-400 mb-8 max-w-xl mx-auto">
                        Schedule a demo to see how Bidesh Gomon Enterprise can streamline your hiring process.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/contact?subject=Enterprise Demo" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                            Schedule Demo
                        </Link>
                        <a href="tel:+8801712345678" class="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-600 text-white font-medium rounded-lg hover:bg-gray-800 transition-colors">
                            <PhoneIcon class="h-5 w-5" />
                            Call Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
