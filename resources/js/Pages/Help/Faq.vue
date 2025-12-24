<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const openFaq = ref(null);

const faqCategories = [
    {
        name: 'General',
        faqs: [
            { question: 'What is Bidesh Gomon?', answer: 'Bidesh Gomon is a comprehensive platform that helps Bangladeshis access international opportunities through visa services, job placements, and travel assistance.' },
            { question: 'How do I create an account?', answer: 'Click the "Register" button on the homepage, fill in your details, verify your email, and complete your profile to get started.' },
            { question: 'Is Bidesh Gomon free to use?', answer: 'Basic features are free. Premium features and services may have associated fees which are clearly displayed before any transaction.' },
            { question: 'How can I contact support?', answer: 'You can reach us through the Contact page, email support@bideshgomon.com, or call our helpline at +880 1712-345678.' },
        ]
    },
    {
        name: 'Visa Services',
        faqs: [
            { question: 'What types of visas can I apply for through Bidesh Gomon?', answer: 'We support tourist visas, work visas, student visas, business visas, and family visas for over 150 countries.' },
            { question: 'How long does visa processing take?', answer: 'Processing times vary by country and visa type. Typically 2-12 weeks. We provide estimated timelines for each application.' },
            { question: 'What documents do I need for a visa application?', answer: 'Required documents vary by visa type and destination. Our platform provides a complete checklist specific to your application.' },
            { question: 'Can I track my visa application status?', answer: 'Yes, you can track your application status in real-time through your dashboard.' },
        ]
    },
    {
        name: 'Jobs',
        faqs: [
            { question: 'How do I apply for jobs?', answer: 'Browse available jobs, create your profile and CV, then click "Apply" on jobs that match your skills. Employers will contact you if interested.' },
            { question: 'Are the job listings verified?', answer: 'Yes, we verify all employers and job listings before publishing. Look for the verification badge on listings.' },
            { question: 'Can I upload my own CV?', answer: 'Yes, you can upload a PDF CV or use our CV Builder to create a professional CV optimized for international employers.' },
            { question: 'How do I know if an employer is legitimate?', answer: 'Verified employers have a blue checkmark. Check company details, reviews, and never pay upfront fees for job applications.' },
        ]
    },
    {
        name: 'Payments',
        faqs: [
            { question: 'What payment methods do you accept?', answer: 'We accept bKash, Nagad, Rocket, credit/debit cards, and bank transfers.' },
            { question: 'Is my payment information secure?', answer: 'Yes, we use industry-standard SSL encryption and never store your complete payment details on our servers.' },
            { question: 'Can I get a refund?', answer: 'Refund policies vary by service. Check our Refund Policy page or contact support for specific cases.' },
            { question: 'How do I pay for services?', answer: 'After selecting a service, you\'ll be directed to our secure payment gateway where you can choose your preferred payment method.' },
        ]
    },
    {
        name: 'Account & Profile',
        faqs: [
            { question: 'How do I update my profile?', answer: 'Go to your Profile page from the dashboard and click "Edit" on any section you want to update.' },
            { question: 'How do I reset my password?', answer: 'Click "Forgot Password" on the login page, enter your email, and follow the reset link sent to you.' },
            { question: 'Can I delete my account?', answer: 'Yes, you can request account deletion from Settings. Note that this action is permanent and cannot be undone.' },
            { question: 'How do I verify my identity?', answer: 'Upload your NID/passport in the Profile section. Our team will verify within 24-48 hours.' },
        ]
    },
];

const toggleFaq = (categoryIndex, faqIndex) => {
    const key = `${categoryIndex}-${faqIndex}`;
    openFaq.value = openFaq.value === key ? null : key;
};

const isOpen = (categoryIndex, faqIndex) => {
    return openFaq.value === `${categoryIndex}-${faqIndex}`;
};

const filteredCategories = () => {
    if (!searchQuery.value) return faqCategories;
    
    return faqCategories.map(cat => ({
        ...cat,
        faqs: cat.faqs.filter(faq => 
            faq.question.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            faq.answer.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    })).filter(cat => cat.faqs.length > 0);
};
</script>

<template>
    <Head title="FAQs" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-12 sm:px-6">
                <div class="max-w-4xl mx-auto text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Frequently Asked Questions</h1>
                    <p class="text-white/90 mb-6">Find answers to common questions about our services</p>
                    
                    <!-- Search -->
                    <div class="max-w-xl mx-auto relative">
                        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search FAQs..."
                            class="w-full pl-11 pr-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-white/50"
                        />
                    </div>
                </div>
            </div>

            <!-- FAQs -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
                <div v-for="(category, catIndex) in filteredCategories()" :key="category.name" class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ category.name }}</h2>
                    
                    <div class="space-y-3">
                        <div v-for="(faq, faqIndex) in category.faqs" :key="faqIndex"
                            class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
                            <button 
                                @click="toggleFaq(catIndex, faqIndex)"
                                class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">{{ faq.question }}</span>
                                <ChevronDownIcon :class="[
                                    'h-5 w-5 text-gray-500 transition-transform',
                                    isOpen(catIndex, faqIndex) ? 'rotate-180' : ''
                                ]" />
                            </button>
                            <div v-show="isOpen(catIndex, faqIndex)" class="px-4 pb-4">
                                <p class="text-gray-600 dark:text-gray-400">{{ faq.answer }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredCategories().length === 0" class="text-center py-12">
                    <p class="text-gray-500">No FAQs match your search. Try different keywords or</p>
                    <Link href="/contact" class="text-primary-600 hover:text-primary-700 font-medium">contact us</Link>
                </div>

                <!-- Still Have Questions -->
                <div class="mt-12 bg-primary-50 dark:bg-primary-900/20 rounded-xl p-8 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Still have questions?</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Can't find what you're looking for? Contact our support team.</p>
                    <Link href="/contact" class="inline-flex items-center justify-center px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                        Contact Support
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
