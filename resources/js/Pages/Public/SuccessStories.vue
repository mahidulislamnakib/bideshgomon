<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    StarIcon, MapPinIcon, BriefcaseIcon, AcademicCapIcon, HomeIcon
} from '@heroicons/vue/24/solid';

const stories = [
    {
        id: 1,
        name: 'Mohammad Rahim',
        image: null,
        location: 'Now in Dubai, UAE',
        category: 'Job Placement',
        title: 'From Dhaka to Dubai: My Dream Job Journey',
        excerpt: 'After struggling for months to find a good opportunity abroad, I discovered Bidesh Gomon. Within 3 months, I landed a supervisor position at a leading construction company in Dubai.',
        rating: 5,
        date: '2025-11-15',
    },
    {
        id: 2,
        name: 'Fatima Khatun',
        image: null,
        location: 'Now in Singapore',
        category: 'Healthcare',
        title: 'Nursing Career in Singapore',
        excerpt: 'Bidesh Gomon helped me navigate the complex Singapore nursing registration process. Their team guided me through every step, from document preparation to interview coaching.',
        rating: 5,
        date: '2025-10-20',
    },
    {
        id: 3,
        name: 'Kamal Hossain',
        image: null,
        location: 'Now in Canada',
        category: 'Study Abroad',
        title: 'My Canadian University Dream',
        excerpt: 'I always dreamed of studying in Canada but didn\'t know where to start. Bidesh Gomon\'s education consultants helped me with university selection, application, and visa processing.',
        rating: 5,
        date: '2025-09-10',
    },
    {
        id: 4,
        name: 'Rashida Begum',
        image: null,
        location: 'Now in Saudi Arabia',
        category: 'Domestic Work',
        title: 'Supporting My Family from Abroad',
        excerpt: 'As a single mother, I needed a reliable way to find work abroad. Bidesh Gomon connected me with a verified agency and ensured all my documents were in order. Now I can support my children\'s education.',
        rating: 5,
        date: '2025-08-25',
    },
    {
        id: 5,
        name: 'Anwar Ali',
        image: null,
        location: 'Now in Malaysia',
        category: 'Manufacturing',
        title: 'Factory Supervisor in Malaysia',
        excerpt: 'The CV builder and job matching features helped me stand out from other candidates. I got multiple interview calls and landed a great position in a electronics manufacturing plant.',
        rating: 5,
        date: '2025-07-15',
    },
    {
        id: 6,
        name: 'Nusrat Jahan',
        image: null,
        location: 'Now in UK',
        category: 'Visa Success',
        title: 'UK Tourist Visa on First Attempt',
        excerpt: 'I was worried about my UK visa application after hearing about many rejections. Bidesh Gomon\'s visa experts reviewed my documents and coached me for the interview. Got approved on my first try!',
        rating: 5,
        date: '2025-06-30',
    },
];

const stats = [
    { value: '25,000+', label: 'Successful Placements' },
    { value: '98%', label: 'Visa Success Rate' },
    { value: '150+', label: 'Countries Covered' },
    { value: '4.9/5', label: 'Average Rating' },
];

const getCategoryIcon = (category) => {
    const icons = {
        'Job Placement': BriefcaseIcon,
        'Healthcare': BriefcaseIcon,
        'Study Abroad': AcademicCapIcon,
        'Domestic Work': HomeIcon,
        'Manufacturing': BriefcaseIcon,
        'Visa Success': StarIcon,
    };
    return icons[category] || BriefcaseIcon;
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-GB', {
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Success Stories" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Success Stories</h1>
                    <p class="text-sm text-white/80 mt-0.5">Real stories from people who achieved their dreams with us</p>
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

            <!-- Stories Grid -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article v-for="story in stories" :key="story.id"
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Header -->
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-bold text-white">{{ story.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ story.name }}</h3>
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <MapPinIcon class="h-4 w-4" />
                                        {{ story.location }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-medium rounded-full">
                                    <component :is="getCategoryIcon(story.category)" class="h-3 w-3" />
                                    {{ story.category }}
                                </span>
                                <span class="text-xs text-gray-500">{{ formatDate(story.date) }}</span>
                            </div>

                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ story.title }}</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{ story.excerpt }}</p>

                            <!-- Rating -->
                            <div class="flex items-center gap-1 mt-4">
                                <StarIcon v-for="i in story.rating" :key="i" class="h-4 w-4 text-yellow-400" />
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <!-- CTA -->
            <div class="bg-primary-600 py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Ready to Write Your Success Story?</h2>
                    <p class="text-white/90 mb-8 max-w-xl mx-auto">
                        Join thousands of Bangladeshis who have achieved their international dreams with Bidesh Gomon.
                    </p>
                    <Link href="/register" class="inline-flex items-center justify-center px-8 py-3 bg-white text-primary-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                        Start Your Journey
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
