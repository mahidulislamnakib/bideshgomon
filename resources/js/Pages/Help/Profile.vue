<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    UserCircleIcon, PhotoIcon, DocumentTextIcon, BriefcaseIcon,
    AcademicCapIcon, LanguageIcon, CheckBadgeIcon, LightBulbIcon
} from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/24/solid';

const tips = [
    {
        icon: PhotoIcon,
        title: 'Professional Photo',
        description: 'Upload a clear, professional headshot with good lighting. Avoid selfies, group photos, or casual pictures.',
        dos: [
            'Use a recent photo (within 6 months)',
            'Wear professional attire',
            'Use a plain background',
            'Ensure good lighting on your face',
        ],
        donts: [
            'Don\'t use filters or heavy editing',
            'Don\'t use blurry or cropped photos',
            'Don\'t include other people',
            'Don\'t wear sunglasses or hats',
        ],
    },
    {
        icon: UserCircleIcon,
        title: 'Complete Personal Information',
        description: 'Fill in all personal details accurately. Incomplete profiles are often overlooked by employers.',
        dos: [
            'Use your legal name as on passport',
            'Add your current address',
            'Include emergency contact',
            'Verify your phone and email',
        ],
        donts: [
            'Don\'t use nicknames',
            'Don\'t leave fields empty',
            'Don\'t use fake information',
            'Don\'t skip the summary section',
        ],
    },
    {
        icon: BriefcaseIcon,
        title: 'Work Experience',
        description: 'List your work experience in reverse chronological order with clear descriptions of your roles.',
        dos: [
            'Include specific achievements',
            'Use action verbs',
            'Add measurable results',
            'List relevant skills used',
        ],
        donts: [
            'Don\'t list every job you\'ve had',
            'Don\'t copy-paste job descriptions',
            'Don\'t leave gaps unexplained',
            'Don\'t exaggerate titles',
        ],
    },
    {
        icon: AcademicCapIcon,
        title: 'Education & Certifications',
        description: 'Include all relevant educational qualifications and professional certifications.',
        dos: [
            'List highest degree first',
            'Include graduation years',
            'Add relevant certifications',
            'Mention honors or awards',
        ],
        donts: [
            'Don\'t include high school if you have a degree',
            'Don\'t list irrelevant courses',
            'Don\'t forget to upload certificates',
            'Don\'t claim unfinished degrees',
        ],
    },
    {
        icon: LanguageIcon,
        title: 'Language Skills',
        description: 'Accurately represent your language abilities with proper proficiency levels.',
        dos: [
            'List all languages you speak',
            'Be honest about proficiency',
            'Include test scores (IELTS, TOEFL)',
            'Mention reading/writing abilities',
        ],
        donts: [
            'Don\'t overstate your abilities',
            'Don\'t forget your native language',
            'Don\'t skip language certificates',
            'Don\'t confuse conversational with fluent',
        ],
    },
    {
        icon: DocumentTextIcon,
        title: 'Documents',
        description: 'Upload all required documents in clear, readable format for faster processing.',
        dos: [
            'Scan documents clearly',
            'Use PDF format when possible',
            'Keep file sizes reasonable',
            'Label files properly',
        ],
        donts: [
            'Don\'t upload blurry images',
            'Don\'t submit expired documents',
            'Don\'t share passport scans publicly',
            'Don\'t use screenshots of documents',
        ],
    },
];

const completionBenefits = [
    'Appear higher in employer searches',
    'Get more relevant job matches',
    'Faster visa processing',
    'Better agency recommendations',
    'Access to premium opportunities',
];
</script>

<template>
    <Head title="Profile Tips" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-12 sm:px-6">
                <div class="max-w-4xl mx-auto text-center">
                    <LightBulbIcon class="h-12 w-12 text-yellow-300 mx-auto mb-4" />
                    <h1 class="text-3xl font-bold text-white mb-2">Profile Tips</h1>
                    <p class="text-white/90">Make your profile stand out to employers and agencies</p>
                </div>
            </div>

            <!-- Why Complete Profile -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-8">
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Why Complete Your Profile?</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div v-for="benefit in completionBenefits" :key="benefit" class="flex items-center gap-2">
                            <CheckIcon class="h-5 w-5 text-green-500 flex-shrink-0" />
                            <span class="text-gray-700 dark:text-gray-300">{{ benefit }}</span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <Link href="/profile/edit" class="inline-flex items-center justify-center px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                            Complete My Profile
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
                <div class="space-y-8">
                    <div v-for="tip in tips" :key="tip.title"
                        class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                                    <component :is="tip.icon" class="h-6 w-6 text-primary-600" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ tip.title }}</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ tip.description }}</p>
                                    
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Do's -->
                                        <div>
                                            <h4 class="text-sm font-semibold text-green-600 dark:text-green-400 mb-2">✓ Do's</h4>
                                            <ul class="space-y-1">
                                                <li v-for="item in tip.dos" :key="item" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <CheckIcon class="h-4 w-4 text-green-500 flex-shrink-0 mt-0.5" />
                                                    {{ item }}
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <!-- Don'ts -->
                                        <div>
                                            <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2">✗ Don'ts</h4>
                                            <ul class="space-y-1">
                                                <li v-for="item in tip.donts" :key="item" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <span class="text-red-500 flex-shrink-0">×</span>
                                                    {{ item }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Badge -->
                <div class="mt-12 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-8">
                    <div class="flex items-start gap-4">
                        <CheckBadgeIcon class="h-12 w-12 text-green-600 flex-shrink-0" />
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Get Verified</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Complete your profile and upload required documents to get a verified badge. 
                                Verified profiles get 3x more visibility and are preferred by employers.
                            </p>
                            <Link href="/profile/edit" class="inline-flex items-center justify-center px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                                Start Verification
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
