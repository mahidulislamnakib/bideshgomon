<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useSettings } from '@/Composables/useSettings';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';
import {
    CheckCircleIcon,
    GlobeAltIcon,
    BriefcaseIcon,
    DocumentTextIcon,
    UserGroupIcon,
    ShieldCheckIcon,
    ClockIcon,
    CurrencyDollarIcon,
    StarIcon,
    MapPinIcon,
    PaperAirplaneIcon,
    TrophyIcon,
    MagnifyingGlassIcon,
    AcademicCapIcon,
    BuildingOfficeIcon,
    HomeIcon,
    HeartIcon,
    ChevronRightIcon,
    ArrowRightIcon,
    PlayCircleIcon,
} from '@heroicons/vue/24/outline';
import {
    CheckBadgeIcon,
    StarIcon as StarSolidIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const page = usePage();
const { homepageWidgets } = useSettings();
const searchQuery = ref('');

// Redirect authenticated users to dashboard
onMounted(() => {
    if (page.props.auth?.user) {
        router.visit('/dashboard');
    }
});

// Categories like Upwork's design
const categories = [
    { name: 'Student Visa', icon: AcademicCapIcon, count: '2.5K+', color: 'bg-blue-50 text-blue-600' },
    { name: 'Work Visa', icon: BriefcaseIcon, count: '1.8K+', color: 'bg-green-50 text-green-600' },
    { name: 'Tourist Visa', icon: GlobeAltIcon, count: '3.2K+', color: 'bg-purple-50 text-purple-600' },
    { name: 'Business Visa', icon: BuildingOfficeIcon, count: '890+', color: 'bg-orange-50 text-orange-600' },
    { name: 'Family Visa', icon: HomeIcon, count: '1.1K+', color: 'bg-pink-50 text-pink-600' },
    { name: 'Medical Visa', icon: HeartIcon, count: '450+', color: 'bg-red-50 text-red-600' },
];

// Popular destinations
const destinations = [
    { country: 'United States', flag: '🇺🇸', applications: '12.5K' },
    { country: 'United Kingdom', flag: '🇬🇧', applications: '9.8K' },
    { country: 'Canada', flag: '🇨🇦', applications: '8.2K' },
    { country: 'Australia', flag: '🇦🇺', applications: '6.5K' },
    { country: 'Germany', flag: '🇩🇪', applications: '4.1K' },
    { country: 'Japan', flag: '🇯🇵', applications: '3.8K' },
    { country: 'UAE', flag: '🇦🇪', applications: '7.2K' },
    { country: 'Malaysia', flag: '🇲🇾', applications: '5.9K' },
];

const features = [
    {
        icon: GlobeAltIcon,
        title: 'Visa Applications',
        description: 'Apply for visas to 50+ countries with smart form auto-fill and document management',
        badge: 'Most Popular',
    },
    {
        icon: BriefcaseIcon,
        title: 'Job Opportunities',
        description: 'Find and apply for jobs abroad with one-click applications and profile matching',
        badge: 'High Demand',
    },
    {
        icon: PaperAirplaneIcon,
        title: 'Travel Services',
        description: 'Book flights, hotels, and travel insurance seamlessly in one platform',
        badge: 'Best Rates',
    },
    {
        icon: DocumentTextIcon,
        title: 'Document Services',
        description: 'Generate CVs, translate documents, and manage all your applications efficiently',
        badge: 'Professional',
    },
];

const stats = [
    { value: '50K+', label: 'Successful Applications', icon: CheckBadgeIcon },
    { value: '195', label: 'Countries Covered', icon: GlobeAltIcon },
    { value: '98%', label: 'Success Rate', icon: TrophyIcon },
    { value: '24/7', label: 'Expert Support', icon: UserGroupIcon },
];

const testimonials = [
    {
        name: 'Ahmed Rahman',
        role: 'Software Engineer at Google',
        location: 'Now in California, USA',
        image: 'https://ui-avatars.com/api/?name=Ahmed+Rahman&background=14a800&color=fff&size=128',
        content: 'Bidesh Gomon made my H1B visa application seamless. The document management and tracking features saved me countless hours. Got my work visa approved in just 3 weeks!',
        rating: 5,
        visaType: 'Work Visa - USA',
    },
    {
        name: 'Fatima Begum',
        role: 'Masters Student at Oxford',
        location: 'Now in London, UK',
        image: 'https://ui-avatars.com/api/?name=Fatima+Begum&background=0077b5&color=fff&size=128',
        content: 'From university applications to visa processing, everything was handled professionally. The platform\'s AI suggestions helped me choose the perfect program for my career goals.',
        rating: 5,
        visaType: 'Student Visa - UK',
    },
    {
        name: 'Kamal Hossain',
        role: 'Business Owner',
        location: 'Expanded to Dubai, UAE',
        image: 'https://ui-avatars.com/api/?name=Kamal+Hossain&background=f59e0b&color=fff&size=128',
        content: 'Excellent service for business visa applications. They helped me expand my business internationally with proper documentation. The expert guidance was invaluable!',
        rating: 5,
        visaType: 'Business Visa - UAE',
    },
];

const trustedBy = [
    'BRAC University',
    'Grameenphone',
    'BRAC Bank',
    'Square Group',
    'Walton',
    'Robi',
];
</script>

<template>
    <Head title="Bidesh Gomon - Your Gateway to Global Opportunities" />

    <div class="min-h-screen bg-white">
        <!-- Header -->
        <Header :can-login="canLogin" :can-register="canRegister" />

        <!-- Hero Section - Upwork Style -->
        <section class="relative bg-gradient-to-br from-upwork-lightest via-white to-upwork-lightest overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-10 w-72 h-72 bg-upwork-green rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-upwork-green rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 lg:pt-32 pb-20">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-upwork-dark leading-tight mb-6">
                            Your Gateway to
                            <span class="text-upwork-green whitespace-nowrap">Global Opportunities</span>
                        </h1>
                        <p class="text-lg sm:text-xl text-gray-600 mb-8 max-w-xl mx-auto lg:mx-0">
                            Bangladesh's #1 visa and migration platform. Apply for visas, find jobs abroad, and manage all your travel documents in one place.
                        </p>

                        <!-- Search Bar - Upwork Style -->
                        <div v-if="homepageWidgets.showHeroSearch" class="relative max-w-xl mx-auto lg:mx-0 mb-8">
                            <div class="flex bg-white rounded-full shadow-lg border border-gray-200 overflow-hidden p-1.5">
                                <div class="flex-1 flex items-center px-4">
                                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 mr-3" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search visa types, countries, or services..."
                                        class="w-full py-3 text-gray-700 placeholder-gray-400 focus:outline-none"
                                    />
                                </div>
                                <button class="px-6 py-3 bg-upwork-green text-white font-semibold rounded-full hover:bg-upwork-green-dark transition-colors">
                                    Search
                                </button>
                            </div>
                            <!-- Popular Searches -->
                            <div class="flex flex-wrap gap-2 mt-4 justify-center lg:justify-start">
                                <span class="text-sm text-gray-500">Popular:</span>
                                <a href="#" class="text-sm text-upwork-green hover:text-upwork-green-dark font-medium">USA Work Visa</a>
                                <a href="#" class="text-sm text-upwork-green hover:text-upwork-green-dark font-medium">UK Student Visa</a>
                                <a href="#" class="text-sm text-upwork-green hover:text-upwork-green-dark font-medium">Canada PR</a>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link v-if="canRegister" :href="route('register')" class="upwork-btn-primary px-8 py-4 text-lg">
                                Get Started Free
                                <ArrowRightIcon class="w-5 h-5 ml-2 inline" />
                            </Link>
                            <a href="#how-it-works" class="upwork-btn-outline px-8 py-4 text-lg flex items-center justify-center">
                                <PlayCircleIcon class="w-5 h-5 mr-2" />
                                See How It Works
                            </a>
                        </div>
                    </div>

                    <!-- Right Side - Stats Card -->
                    <div class="hidden lg:block">
                        <div class="relative">
                            <!-- Main Image/Card -->
                            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                                <div class="grid grid-cols-2 gap-6">
                                    <div v-for="stat in stats" :key="stat.label" class="text-center p-6 bg-gray-50 rounded-2xl">
                                        <component :is="stat.icon" class="w-8 h-8 text-upwork-green mx-auto mb-3" />
                                        <div class="text-3xl font-bold text-upwork-dark">{{ stat.value }}</div>
                                        <div class="text-sm text-gray-600 mt-1">{{ stat.label }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating Badge -->
                            <div class="absolute -bottom-4 -left-4 bg-upwork-green text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2">
                                <CheckBadgeIcon class="w-5 h-5" />
                                <span class="text-sm font-semibold">Trusted by 50K+ Users</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trusted By Section -->
        <section class="py-8 bg-white border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-8">
                    <span class="text-sm text-gray-500 font-medium">Trusted by leading organizations</span>
                    <div class="flex flex-wrap items-center justify-center gap-8 opacity-60">
                        <span v-for="company in trustedBy" :key="company" class="text-lg font-semibold text-gray-400">
                            {{ company }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section - Upwork Style -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-upwork-dark mb-4">Browse by Visa Category</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Choose from a wide range of visa types to match your travel purpose</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div v-for="category in categories" :key="category.name"
                         class="group bg-white border border-gray-200 rounded-2xl p-4 text-center hover:border-upwork-green hover:shadow-lg transition-all cursor-pointer">
                        <div :class="[category.color, 'w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform']">
                            <component :is="category.icon" class="w-6 h-6" />
                        </div>
                        <h3 class="text-sm font-semibold text-upwork-dark mb-0.5 whitespace-nowrap">{{ category.name }}</h3>
                        <p class="text-xs text-gray-500">{{ category.count }} applications</p>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <Link href="#" class="text-upwork-green hover:text-upwork-green-dark font-semibold inline-flex items-center">
                        View all categories
                        <ChevronRightIcon class="w-5 h-5 ml-1" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Popular Destinations -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-4">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-upwork-dark mb-2">Popular Destinations</h2>
                        <p class="text-lg text-gray-600">Most popular countries among Bangladeshi applicants</p>
                    </div>
                    <Link href="#" class="upwork-btn-outline">
                        View All Countries
                    </Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                    <div v-for="destination in destinations" :key="destination.country"
                         class="bg-white rounded-xl p-3 text-center hover:shadow-md transition-shadow cursor-pointer border border-gray-100 hover:border-upwork-green">
                        <div class="text-3xl mb-2">{{ destination.flag }}</div>
                        <h3 class="font-semibold text-xs text-upwork-dark mb-1 whitespace-nowrap">{{ destination.country }}</h3>
                        <p class="text-xs text-gray-500">{{ destination.applications }} apps</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works - Upwork Style -->
        <section id="how-it-works" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="inline-block px-4 py-1.5 bg-upwork-lightest text-upwork-green text-sm font-semibold rounded-full mb-4">
                        Simple Process
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-upwork-dark mb-4">How Bidesh Gomon Works</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Get started in minutes and let us handle the complexity</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                    <!-- Step 1 -->
                    <div class="relative text-center">
                        <div class="w-16 h-16 bg-upwork-green text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6 shadow-lg">
                            1
                        </div>
                        <h3 class="text-xl font-bold text-upwork-dark mb-3">Create Your Profile</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Fill out your profile once with personal details, education, work experience, and documents. Takes only 5 minutes!
                        </p>
                        <!-- Connector Line -->
                        <div class="hidden md:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-gradient-to-r from-upwork-green to-upwork-light"></div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative text-center">
                        <div class="w-16 h-16 bg-upwork-green text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6 shadow-lg">
                            2
                        </div>
                        <h3 class="text-xl font-bold text-upwork-dark mb-3">Choose Your Service</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Browse visas, jobs, or travel services. Your profile auto-fills all forms — no repetitive data entry needed!
                        </p>
                        <!-- Connector Line -->
                        <div class="hidden md:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-gradient-to-r from-upwork-green to-upwork-light"></div>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-upwork-green text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6 shadow-lg">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-upwork-dark mb-3">Track & Succeed</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Track all applications in one dashboard. Get real-time updates and expert support throughout your journey.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section - Upwork Style Cards -->
        <section id="features" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="inline-block px-4 py-1.5 bg-upwork-lightest text-upwork-green text-sm font-semibold rounded-full mb-4">
                        Our Services
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-upwork-dark mb-4">Everything You Need in One Platform</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">From visa applications to job searches, we've got all your international needs covered</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="feature in features" :key="feature.title"
                         class="upwork-card upwork-card-hover p-8 relative group">
                        <!-- Badge -->
                        <div v-if="feature.badge" class="absolute top-4 right-4">
                            <span class="upwork-badge-success text-xs">{{ feature.badge }}</span>
                        </div>
                        <!-- Icon -->
                        <div class="w-14 h-14 bg-upwork-lightest rounded-xl flex items-center justify-center mb-6 group-hover:bg-upwork-green transition-colors">
                            <component :is="feature.icon" class="w-7 h-7 text-upwork-green group-hover:text-white transition-colors" />
                        </div>
                        <h3 class="text-xl font-bold text-upwork-dark mb-3">{{ feature.title }}</h3>
                        <p class="text-gray-600 mb-4">{{ feature.description }}</p>
                        <a href="#" class="text-upwork-green hover:text-upwork-green-dark font-semibold inline-flex items-center group">
                            Learn more
                            <ArrowRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials - Upwork Style -->
        <section v-if="homepageWidgets.showTestimonials" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="inline-block px-4 py-1.5 bg-upwork-lightest text-upwork-green text-sm font-semibold rounded-full mb-4">
                        Success Stories
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-upwork-dark mb-4">What Our Users Say</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Join thousands of satisfied applicants who achieved their international dreams</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="testimonial in testimonials" :key="testimonial.name"
                         class="upwork-card p-8 relative">
                        <!-- Quote Icon -->
                        <div class="absolute top-6 right-6 text-6xl text-upwork-lightest font-serif">"</div>

                        <!-- Rating -->
                        <div class="flex gap-1 mb-4">
                            <StarSolidIcon v-for="i in testimonial.rating" :key="i" class="w-5 h-5 text-yellow-400" />
                        </div>

                        <!-- Content -->
                        <p class="text-gray-600 leading-relaxed mb-6 relative z-10">{{ testimonial.content }}</p>

                        <!-- Visa Type Badge -->
                        <div class="mb-6">
                            <span class="upwork-badge-success">{{ testimonial.visaType }}</span>
                        </div>

                        <!-- User Info -->
                        <div class="flex items-center gap-4 pt-6 border-t border-gray-100">
                            <img :src="testimonial.image" :alt="testimonial.name" class="w-14 h-14 rounded-full" />
                            <div>
                                <div class="font-bold text-upwork-dark">{{ testimonial.name }}</div>
                                <div class="text-sm text-gray-600">{{ testimonial.role }}</div>
                                <div class="text-xs text-upwork-green font-medium">{{ testimonial.location }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section - Upwork Style -->
        <section class="py-20 bg-upwork-dark relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-upwork-green rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-upwork-green rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                    Ready to Start Your Global Journey?
                </h2>
                <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto">
                    Join 50,000+ Bangladeshi professionals who trusted Bidesh Gomon with their international dreams. Free to get started.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link v-if="canRegister" :href="route('register')" class="upwork-btn-primary px-10 py-4 text-lg">
                        Create Free Account
                        <ArrowRightIcon class="w-5 h-5 ml-2 inline" />
                    </Link>
                    <Link v-if="canLogin" :href="route('login')" class="px-10 py-4 text-lg font-semibold text-white border-2 border-white/30 rounded-full hover:bg-white/10 transition-colors">
                        Sign In
                    </Link>
                </div>
                <p class="mt-8 text-sm text-gray-400">
                    ✓ No credit card required &nbsp;&nbsp; ✓ Free profile creation &nbsp;&nbsp; ✓ 24/7 support
                </p>
            </div>
        </section>

        <!-- Footer -->
        <Footer />
    </div>
</template>
