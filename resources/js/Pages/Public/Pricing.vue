<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckIcon } from '@heroicons/vue/24/solid';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const { formatCurrency } = useBangladeshFormat();

const plans = [
    {
        name: 'Basic',
        description: 'For individuals starting their journey',
        price: 0,
        period: 'forever',
        featured: false,
        features: [
            'Browse all visa requirements',
            'Access job listings',
            'Basic profile',
            'Email support',
            'Community access',
        ],
        cta: 'Get Started',
        href: '/register',
    },
    {
        name: 'Premium',
        description: 'For serious job seekers and applicants',
        price: 999,
        period: 'month',
        featured: true,
        features: [
            'Everything in Basic',
            'Priority job applications',
            'CV Builder with AI',
            'Document verification',
            'Video consultation (2/month)',
            'Priority support',
            'Application tracking',
        ],
        cta: 'Start Premium',
        href: '/register?plan=premium',
    },
    {
        name: 'Enterprise',
        description: 'For agencies and businesses',
        price: 9999,
        period: 'month',
        featured: false,
        features: [
            'Everything in Premium',
            'Unlimited team members',
            'Bulk application processing',
            'API access',
            'Dedicated account manager',
            'Custom integrations',
            'SLA guarantees',
        ],
        cta: 'Contact Sales',
        href: '/contact?subject=Enterprise',
    },
];

const faqs = [
    {
        question: 'Can I cancel anytime?',
        answer: 'Yes, you can cancel your subscription at any time. Your access will continue until the end of your billing period.',
    },
    {
        question: 'What payment methods do you accept?',
        answer: 'We accept bKash, Nagad, credit/debit cards, and bank transfers for Bangladeshi users.',
    },
    {
        question: 'Is there a free trial?',
        answer: 'Yes, Premium plans come with a 7-day free trial. No credit card required to start.',
    },
    {
        question: 'Do you offer refunds?',
        answer: 'We offer a 30-day money-back guarantee if you\'re not satisfied with our Premium services.',
    },
];
</script>

<template>
    <Head title="Pricing" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Simple, Transparent Pricing</h1>
                    <p class="text-sm text-white/80 mt-0.5">Choose the plan that's right for you. No hidden fees.</p>
                </div>
            </div>

            <!-- Pricing Cards -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 pb-16">
                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="plan in plans" :key="plan.name"
                        :class="[
                            'bg-white dark:bg-neutral-800 rounded-xl border-2 p-6 relative',
                            plan.featured 
                                ? 'border-primary-500 shadow-xl scale-105 z-10' 
                                : 'border-gray-200 dark:border-neutral-700'
                        ]">
                        <!-- Featured Badge -->
                        <div v-if="plan.featured" class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="px-3 py-1 bg-primary-600 text-white text-xs font-semibold rounded-full">
                                Most Popular
                            </span>
                        </div>

                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ plan.name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ plan.description }}</p>
                            
                            <div class="mt-4">
                                <span class="text-4xl font-bold text-gray-900 dark:text-white">
                                    {{ plan.price === 0 ? 'Free' : formatCurrency(plan.price) }}
                                </span>
                                <span v-if="plan.price > 0" class="text-gray-600 dark:text-gray-400">/{{ plan.period }}</span>
                            </div>
                        </div>

                        <ul class="space-y-3 mb-6">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2">
                                <CheckIcon class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ feature }}</span>
                            </li>
                        </ul>

                        <Link 
                            :href="plan.href"
                            :class="[
                                'block w-full text-center py-3 rounded-lg font-medium transition-colors',
                                plan.featured
                                    ? 'bg-primary-600 text-white hover:bg-primary-700'
                                    : 'border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700'
                            ]"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- FAQs -->
            <div class="bg-white dark:bg-neutral-800 py-16">
                <div class="max-w-3xl mx-auto px-4 sm:px-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">Frequently Asked Questions</h2>
                    
                    <div class="space-y-6">
                        <div v-for="faq in faqs" :key="faq.question" class="border-b border-gray-200 dark:border-neutral-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ faq.question }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ faq.answer }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Need a Custom Plan?</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-xl mx-auto">
                    Contact our sales team for custom pricing and enterprise solutions tailored to your needs.
                </p>
                <Link 
                    href="/contact?subject=Custom Pricing"
                    class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Contact Sales
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
