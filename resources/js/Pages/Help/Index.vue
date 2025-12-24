<template>
    <Head title="Help Center - Bidesh Gomon Support" />

    <div class="min-h-screen bg-gray-50 dark:bg-neutral-950">
        <Header />
        
        <!-- Compact Hero -->
        <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-xl md:text-2xl font-bold text-white">সাহায্য কেন্দ্র</h1>
                <p class="text-sm text-white/80 mt-0.5">আপনার যেকোনো প্রশ্নের উত্তর খুঁজুন</p>
                
                <!-- Search in Hero -->
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <input
                        v-model="searchQuery"
                        @keyup.enter="searchHelp"
                        type="text"
                        placeholder="আপনার প্রশ্ন লিখুন..."
                        class="w-48 md:w-64 px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50 placeholder-gray-500"
                    />
                    <Link
                        :href="route('help.faq')"
                        class="px-4 py-2 text-sm font-medium bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors"
                    >
                        FAQ
                    </Link>
                    <Link
                        :href="route('support.create')"
                        class="px-4 py-2 text-sm font-medium bg-white text-growth-600 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        যোগাযোগ করুন
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <!-- Popular Categories -->
            <div class="mb-12">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">জনপ্রিয় বিষয়</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Visa & Immigration -->
                    <Link
                        :href="route('help.category', 'visa-immigration')"
                        class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-growth-500 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-growth-100 dark:bg-growth-900/30 rounded-lg">
                                <DocumentTextIcon class="h-5 w-5 text-growth-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-growth-600 transition-colors">ভিসা ও ইমিগ্রেশন</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ভিসা আবেদন, প্রক্রিয়া এবং প্রয়োজনীয় ডকুমেন্ট</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Jobs & Applications -->
                    <Link
                        :href="route('help.category', 'jobs')"
                        class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-green-500 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <BriefcaseIcon class="h-5 w-5 text-green-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-green-600 transition-colors">চাকরি ও আবেদন</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">চাকরির খোঁজ, আবেদন প্রক্রিয়া এবং ইন্টারভিউ টিপস</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Account & Profile -->
                    <Link
                        :href="route('help.category', 'account')"
                        class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-purple-500 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                <UserCircleIcon class="h-5 w-5 text-purple-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-purple-600 transition-colors">অ্যাকাউন্ট ও প্রোফাইল</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">একাউন্ট সেটআপ, প্রোফাইল সম্পূর্ণ করা এবং সিকিউরিটি</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Payment & Wallet -->
                    <Link
                        :href="route('help.category', 'payment')"
                        class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-orange-500 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                                <CreditCardIcon class="h-5 w-5 text-orange-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-orange-600 transition-colors">পেমেন্ট ও ওয়ালেট</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">পেমেন্ট মেথড, ওয়ালেট রিচার্জ এবং রিফান্ড নীতি</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Services & Booking -->
                    <Link
                        :href="route('help.category', 'services')"
                        class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-indigo-500 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <SparklesIcon class="h-5 w-5 text-indigo-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">সেবা ও বুকিং</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">সেবা নির্বাচন, বুকিং এবং অ্যাপয়েন্টমেন্ট</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Technical Support -->
                    <Link
                        :href="route('help.category', 'technical')"
                        class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-red-500 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <WrenchScrewdriverIcon class="h-5 w-5 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-red-600 transition-colors">টেকনিক্যাল সাপোর্ট</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ওয়েবসাইট সমস্যা, লগইন ইস্যু এবং টেকনিক্যাল হেল্প</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
                <Link
                    :href="route('faqs.index')"
                    class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-growth-500 transition-colors"
                >
                    <QuestionMarkCircleIcon class="h-6 w-6 text-growth-600 mb-2" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">সাধারণ প্রশ্ন (FAQ)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">সচরাচর জিজ্ঞাসিত প্রশ্নের উত্তর</p>
                </Link>

                <Link
                    :href="route('support.create')"
                    class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-green-500 transition-colors"
                >
                    <ChatBubbleLeftRightIcon class="h-6 w-6 text-green-600 mb-2" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">টিকেট তৈরি করুন</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">সমস্যা সমাধানের জন্য সাপোর্ট টিকেট</p>
                </Link>

                <a
                    href="mailto:support@bideshgomon.com"
                    class="group bg-white dark:bg-neutral-800 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 hover:border-purple-500 transition-colors"
                >
                    <EnvelopeIcon class="h-6 w-6 text-purple-600 mb-2" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">ইমেইল করুন</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">support@bideshgomon.com</p>
                </a>
            </div>

            <!-- Contact Information -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-8">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">যোগাযোগের তথ্য</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <PhoneIcon class="h-6 w-6 mx-auto mb-2 text-growth-600" />
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">ফোন</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">+880 1700-000000</p>
                    </div>
                    <div class="text-center">
                        <EnvelopeIcon class="h-6 w-6 mx-auto mb-2 text-green-600" />
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">ইমেইল</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">support@bideshgomon.com</p>
                    </div>
                    <div class="text-center">
                        <MapPinIcon class="h-6 w-6 mx-auto mb-2 text-purple-600" />
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">অফিস</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ঢাকা, বাংলাদেশ</p>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';
import {
    QuestionMarkCircleIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon,
    BriefcaseIcon,
    UserCircleIcon,
    CreditCardIcon,
    SparklesIcon,
    WrenchScrewdriverIcon,
    ArrowRightIcon,
    ChatBubbleLeftRightIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
    totalFaqs: Number,
    totalTickets: Number,
});

const searchQuery = ref('');

const searchHelp = () => {
    if (searchQuery.value.trim()) {
        window.location.href = route('help.search', { q: searchQuery.value });
    }
};

// Icon mapping for categories
const categoryIcons = {
    visa: DocumentTextIcon,
    jobs: BriefcaseIcon,
    account: UserCircleIcon,
    payment: CreditCardIcon,
    services: SparklesIcon,
    technical: WrenchScrewdriverIcon,
};

// Color mapping for categories
const colorClasses = {
    blue: {
        bg: 'bg-blue-100',
        hover: 'group-hover:bg-growth-600',
        text: 'text-growth-600',
        textHover: 'group-hover:text-growth-600',
        textWhite: 'group-hover:text-white',
        border: 'hover:border-blue-500',
    },
    green: {
        bg: 'bg-green-100',
        hover: 'group-hover:bg-green-500',
        text: 'text-green-600',
        textHover: 'group-hover:text-green-600',
        textWhite: 'group-hover:text-white',
        border: 'hover:border-green-500',
    },
    purple: {
        bg: 'bg-purple-100',
        hover: 'group-hover:bg-purple-500',
        text: 'text-purple-600',
        textHover: 'group-hover:text-purple-600',
        textWhite: 'group-hover:text-white',
        border: 'hover:border-purple-500',
    },
    orange: {
        bg: 'bg-orange-100',
        hover: 'group-hover:bg-orange-500',
        text: 'text-orange-600',
        textHover: 'group-hover:text-orange-600',
        textWhite: 'group-hover:text-white',
        border: 'hover:border-orange-500',
    },
    indigo: {
        bg: 'bg-indigo-100',
        hover: 'group-hover:bg-growth-600',
        text: 'text-growth-600',
        textHover: 'group-hover:text-growth-600',
        textWhite: 'group-hover:text-white',
        border: 'hover:border-indigo-500',
    },
    red: {
        bg: 'bg-red-100',
        hover: 'group-hover:bg-red-500',
        text: 'text-red-600',
        textHover: 'group-hover:text-red-600',
        textWhite: 'group-hover:text-white',
        border: 'hover:border-red-500',
    },
};
</script>
