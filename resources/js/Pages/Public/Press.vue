<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    NewspaperIcon, EnvelopeIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const pressReleases = [
    {
        id: 1,
        title: 'Bidesh Gomon Launches AI-Powered Visa Recommendation System',
        date: '2025-12-15',
        excerpt: 'Revolutionary AI technology helps users find the best visa options based on their profile and goals.',
    },
    {
        id: 2,
        title: 'Bidesh Gomon Partners with Top 50 Recruitment Agencies in Bangladesh',
        date: '2025-11-20',
        excerpt: 'Strategic partnerships expand job placement opportunities for Bangladeshi workers globally.',
    },
    {
        id: 3,
        title: 'Bidesh Gomon Reaches 50,000 Users Milestone',
        date: '2025-10-01',
        excerpt: 'Platform celebrates helping over 50,000 Bangladeshis access international opportunities.',
    },
    {
        id: 4,
        title: 'New Mobile App Launch for iOS and Android',
        date: '2025-09-15',
        excerpt: 'Users can now access all Bidesh Gomon services on-the-go with our new mobile applications.',
    },
];

const mediaContacts = {
    email: 'press@bideshgomon.com',
    phone: '+880 1712-345678',
};

const brandAssets = [
    { name: 'Logo Pack (PNG, SVG)', size: '2.4 MB' },
    { name: 'Brand Guidelines', size: '1.8 MB' },
    { name: 'Press Kit', size: '5.2 MB' },
];

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Press" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Press & Media</h1>
                    <p class="text-sm text-white/80 mt-0.5">News, updates, and resources for media professionals</p>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Press Releases -->
                    <div class="lg:col-span-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Press Releases</h2>
                        
                        <div class="space-y-6">
                            <article v-for="release in pressReleases" :key="release.id"
                                class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 hover:shadow-lg transition-shadow">
                                <div class="text-sm text-primary-600 font-medium mb-2">{{ formatDate(release.date) }}</div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ release.title }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">{{ release.excerpt }}</p>
                                <a href="#" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                                    Read Full Release →
                                </a>
                            </article>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Media Contact -->
                        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Media Contact</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                                    <a :href="`mailto:${mediaContacts.email}`" class="text-primary-600 hover:text-primary-700">
                                        {{ mediaContacts.email }}
                                    </a>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                                For press inquiries, interviews, or media partnerships, please contact our press team.
                            </p>
                        </div>

                        <!-- Brand Assets -->
                        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Brand Assets</h3>
                            <div class="space-y-3">
                                <a v-for="asset in brandAssets" :key="asset.name" href="#"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-neutral-700 rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-600 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <NewspaperIcon class="h-5 w-5 text-gray-400" />
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ asset.name }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ asset.size }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Company Facts -->
                        <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Facts</h3>
                            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li><strong>Founded:</strong> 2023</li>
                                <li><strong>Headquarters:</strong> Dhaka, Bangladesh</li>
                                <li><strong>Users:</strong> 50,000+</li>
                                <li><strong>Countries:</strong> 150+</li>
                                <li><strong>Team Size:</strong> 50+</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
