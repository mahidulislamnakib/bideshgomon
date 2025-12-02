<template>
    <Head title="Help Center - BideshGomon Support" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50">
        <Header />
        
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 text-white overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
                <QuestionMarkCircleIcon class="h-16 w-16 mx-auto mb-6 text-blue-200" />
                <h1 class="text-4xl md:text-5xl font-bold mb-4">সাহায্য কেন্দ্র</h1>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    আপনার যেকোনো প্রশ্নের উত্তর খুঁজুন বা আমাদের সাথে যোগাযোগ করুন
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-6 w-6 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            @keyup.enter="searchHelp"
                            type="text"
                            placeholder="আপনার প্রশ্ন লিখুন..."
                            class="w-full pl-14 pr-4 py-4 text-lg rounded-2xl border-0 focus:ring-4 focus:ring-blue-300 text-gray-900 placeholder-gray-400 shadow-2xl"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Popular Categories -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">জনপ্রিয় বিষয়</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Visa & Immigration -->
                    <Link
                        :href="route('help.category', 'visa-immigration')"
                        class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-500"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-3 bg-blue-100 rounded-xl group-hover:bg-blue-500 transition-colors">
                                <DocumentTextIcon class="h-8 w-8 text-blue-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">ভিসা ও ইমিগ্রেশন</h3>
                                <p class="text-gray-600 mb-3">ভিসা আবেদন, প্রক্রিয়া এবং প্রয়োজনীয় ডকুমেন্ট সম্পর্কে জানুন</p>
                                <span class="inline-flex items-center text-blue-600 font-semibold text-sm">
                                    বিস্তারিত দেখুন
                                    <ArrowRightIcon class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- Jobs & Applications -->
                    <Link
                        :href="route('help.category', 'jobs')"
                        class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-green-500"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-3 bg-green-100 rounded-xl group-hover:bg-green-500 transition-colors">
                                <BriefcaseIcon class="h-8 w-8 text-green-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">চাকরি ও আবেদন</h3>
                                <p class="text-gray-600 mb-3">চাকরির খোঁজ, আবেদন প্রক্রিয়া এবং ইন্টারভিউ টিপস</p>
                                <span class="inline-flex items-center text-green-600 font-semibold text-sm">
                                    বিস্তারিত দেখুন
                                    <ArrowRightIcon class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- Account & Profile -->
                    <Link
                        :href="route('help.category', 'account')"
                        class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-purple-500"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-3 bg-purple-100 rounded-xl group-hover:bg-purple-500 transition-colors">
                                <UserCircleIcon class="h-8 w-8 text-purple-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">অ্যাকাউন্ট ও প্রোফাইল</h3>
                                <p class="text-gray-600 mb-3">একাউন্ট সেটআপ, প্রোফাইল সম্পূর্ণ করা এবং সিকিউরিটি</p>
                                <span class="inline-flex items-center text-purple-600 font-semibold text-sm">
                                    বিস্তারিত দেখুন
                                    <ArrowRightIcon class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- Payment & Wallet -->
                    <Link
                        :href="route('help.category', 'payment')"
                        class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-orange-500"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-3 bg-orange-100 rounded-xl group-hover:bg-orange-500 transition-colors">
                                <CreditCardIcon class="h-8 w-8 text-orange-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">পেমেন্ট ও ওয়ালেট</h3>
                                <p class="text-gray-600 mb-3">পেমেন্ট মেথড, ওয়ালেট রিচার্জ এবং রিফান্ড নীতি</p>
                                <span class="inline-flex items-center text-orange-600 font-semibold text-sm">
                                    বিস্তারিত দেখুন
                                    <ArrowRightIcon class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- Services & Booking -->
                    <Link
                        :href="route('help.category', 'services')"
                        class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-indigo-500"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-3 bg-indigo-100 rounded-xl group-hover:bg-indigo-500 transition-colors">
                                <SparklesIcon class="h-8 w-8 text-indigo-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">সেবা ও বুকিং</h3>
                                <p class="text-gray-600 mb-3">সেবা নির্বাচন, বুকিং এবং অ্যাপয়েন্টমেন্ট ম্যানেজমেন্ট</p>
                                <span class="inline-flex items-center text-indigo-600 font-semibold text-sm">
                                    বিস্তারিত দেখুন
                                    <ArrowRightIcon class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- Technical Support -->
                    <Link
                        :href="route('help.category', 'technical')"
                        class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-red-500"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-3 bg-red-100 rounded-xl group-hover:bg-red-500 transition-colors">
                                <WrenchScrewdriverIcon class="h-8 w-8 text-red-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">টেকনিক্যাল সাপোর্ট</h3>
                                <p class="text-gray-600 mb-3">ওয়েবসাইট সমস্যা, লগইন ইস্যু এবং টেকনিক্যাল হেল্প</p>
                                <span class="inline-flex items-center text-red-600 font-semibold text-sm">
                                    বিস্তারিত দেখুন
                                    <ArrowRightIcon class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <Link
                    :href="route('faqs.index')"
                    class="group bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-8 text-white shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1"
                >
                    <QuestionMarkCircleIcon class="h-12 w-12 mb-4" />
                    <h3 class="text-2xl font-bold mb-2">সচরাচর জিজ্ঞাসা (FAQ)</h3>
                    <p class="text-blue-100 mb-4">সবচেয়ে কমন প্রশ্নের উত্তর দেখুন</p>
                    <span class="inline-flex items-center font-semibold">
                        FAQ দেখুন
                        <ArrowRightIcon class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
                    </span>
                </Link>

                <Link
                    :href="route('support.create')"
                    class="group bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-8 text-white shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1"
                >
                    <ChatBubbleLeftRightIcon class="h-12 w-12 mb-4" />
                    <h3 class="text-2xl font-bold mb-2">টিকেট তৈরি করুন</h3>
                    <p class="text-green-100 mb-4">সমস্যা সমাধানের জন্য সাপোর্ট টিকেট খুলুন</p>
                    <span class="inline-flex items-center font-semibold">
                        টিকেট তৈরি করুন
                        <ArrowRightIcon class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
                    </span>
                </Link>

                <a
                    href="mailto:support@bideshgomon.com"
                    class="group bg-gradient-to-br from-purple-600 to-purple-700 rounded-2xl p-8 text-white shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1"
                >
                    <EnvelopeIcon class="h-12 w-12 mb-4" />
                    <h3 class="text-2xl font-bold mb-2">ইমেইল করুন</h3>
                    <p class="text-purple-100 mb-4">আমাদের সাথে সরাসরি যোগাযোগ করুন</p>
                    <span class="inline-flex items-center font-semibold">
                        support@bideshgomon.com
                        <ArrowRightIcon class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
                    </span>
                </a>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">যোগাযোগের তথ্য</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <PhoneIcon class="h-10 w-10 mx-auto mb-3 text-blue-600" />
                        <h3 class="font-semibold text-gray-900 mb-2">ফোন</h3>
                        <p class="text-gray-600">+880 1700-000000</p>
                        <p class="text-sm text-gray-500 mt-1">সকাল ৯টা - রাত ৮টা</p>
                    </div>
                    <div class="text-center">
                        <EnvelopeIcon class="h-10 w-10 mx-auto mb-3 text-green-600" />
                        <h3 class="font-semibold text-gray-900 mb-2">ইমেইল</h3>
                        <p class="text-gray-600">support@bideshgomon.com</p>
                        <p class="text-sm text-gray-500 mt-1">২৪ ঘন্টার মধ্যে উত্তর</p>
                    </div>
                    <div class="text-center">
                        <MapPinIcon class="h-10 w-10 mx-auto mb-3 text-purple-600" />
                        <h3 class="font-semibold text-gray-900 mb-2">অফিস</h3>
                        <p class="text-gray-600">ঢাকা, বাংলাদেশ</p>
                        <p class="text-sm text-gray-500 mt-1">সাপ্তাহিক ছুটি: শুক্রবার</p>
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
        hover: 'group-hover:bg-blue-500',
        text: 'text-blue-600',
        textHover: 'group-hover:text-blue-600',
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
        hover: 'group-hover:bg-indigo-500',
        text: 'text-indigo-600',
        textHover: 'group-hover:text-indigo-600',
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
