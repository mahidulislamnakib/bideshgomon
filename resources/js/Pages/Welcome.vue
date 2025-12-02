<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
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
} from '@heroicons/vue/24/outline';
import {
    CheckBadgeIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const page = usePage();
const { homepageWidgets } = useSettings();

// Redirect authenticated users to dashboard
onMounted(() => {
    if (page.props.auth?.user) {
        router.visit('/dashboard');
    }
});

const features = [
    {
        icon: GlobeAltIcon,
        title: 'Visa Applications',
        description: 'Apply for visas to 50+ countries with smart form auto-fill',
    },
    {
        icon: BriefcaseIcon,
        title: 'Job Opportunities',
        description: 'Find and apply for jobs abroad with one-click applications',
    },
    {
        icon: PaperAirplaneIcon,
        title: 'Travel Services',
        description: 'Book flights, hotels, and travel insurance seamlessly',
    },
    {
        icon: DocumentTextIcon,
        title: 'Document Services',
        description: 'Generate CVs, translate documents, and manage applications',
    },
];

const benefits = [
    'Fill your profile once, use everywhere',
    'Smart AI-powered suggestions',
    'Track all applications in one place',
    'Get instant alerts for opportunities',
    'Secure document storage',
    '24/7 customer support',
];

const stats = [
    { value: '50K+', label: 'Happy Users', icon: UserGroupIcon },
    { value: '195', label: 'Countries', icon: MapPinIcon },
    { value: '10K+', label: 'Successful Visas', icon: CheckBadgeIcon },
    { value: '98%', label: 'Success Rate', icon: TrophyIcon },
];

const testimonials = [
    {
        name: 'Ahmed Rahman',
        role: 'Software Engineer',
        image: 'https://ui-avatars.com/api/?name=Ahmed+Rahman&background=10b981&color=fff',
        content: 'Bidesh Gomon made my visa application process so smooth! Got my work visa in just 2 weeks.',
        rating: 5,
    },
    {
        name: 'Fatima Khan',
        role: 'Student',
        image: 'https://ui-avatars.com/api/?name=Fatima+Khan&background=6366f1&color=fff',
        content: 'The platform helped me find the perfect university and handled all the paperwork. Highly recommended!',
        rating: 5,
    },
    {
        name: 'Kamal Hossain',
        role: 'Business Owner',
        image: 'https://ui-avatars.com/api/?name=Kamal+Hossain&background=f59e0b&color=fff',
        content: 'Professional service! They helped me expand my business internationally with proper documentation.',
        rating: 5,
    },
];
</script>

<template>
    <Head title="Welcome to Bidesh Gomon - Your Gateway to Global Opportunities" />

    <div class="min-h-screen bg-white">
        <!-- Header -->
        <Header :can-login="canLogin" :can-register="canRegister" />

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Go Abroad with Confidence
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    All-in-one platform for visa applications, job search, and travel booking
                </p>
                <div v-if="homepageWidgets.showHeroSearch" class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Link v-if="canRegister" :href="route('register')" class="w-full sm:w-auto px-8 py-3 bg-emerald-600 text-white text-center rounded-lg hover:bg-emerald-700 font-medium transition-colors">
                        Get Started Free
                    </Link>
                    <a href="#features" class="w-full sm:w-auto px-8 py-3 border border-gray-300 text-center rounded-lg hover:bg-gray-50 font-medium transition-colors">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div v-if="homepageWidgets.showStats" class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto mt-16">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <div class="text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ stat.value }}</div>
                    <div class="text-xs md:text-sm text-gray-600">{{ stat.label }}</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Everything You Need</h2>
                    <p class="text-base md:text-lg text-gray-600">From visa applications to job searches, we've got you covered</p>
                </div>
                
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="feature in features" :key="feature.title" class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                        <div class="flex-shrink-0 mb-4 w-8 h-8">
                            <component :is="feature.icon" class="w-full h-full text-emerald-600" />
                        </div>
                        <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                    <p class="text-base md:text-lg text-gray-600">Get started in 3 simple steps</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-xl p-8 text-center shadow-sm">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-6">1</div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-3">Create Profile</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Fill your profile once with details, education, and documents</p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-8 text-center shadow-sm">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-6">2</div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-3">Choose Service</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Browse visas, jobs, or travel. Auto-fill all forms</p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-8 text-center shadow-sm">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-6">3</div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-3">Track & Success</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Track applications and get real-time updates</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Bidesh Gomon?</h2>
                    <p class="text-base md:text-lg text-gray-600">We make your international journey seamless</p>
                </div>
                
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="space-y-4">
                        <div v-for="benefit in benefits" :key="benefit" class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-1 w-6 h-6">
                                <CheckCircleIcon class="w-full h-full text-emerald-600" />
                            </div>
                            <span class="text-base text-gray-700">{{ benefit }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="flex-shrink-0 mx-auto mb-3 w-10 h-10">
                                <ClockIcon class="w-full h-full text-emerald-600" />
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Fast</h4>
                            <p class="text-xs text-gray-600">Quick processing</p>
                        </div>
                        
                        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="flex-shrink-0 mx-auto mb-3 w-10 h-10">
                                <ShieldCheckIcon class="w-full h-full text-emerald-600" />
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Secure</h4>
                            <p class="text-xs text-gray-600">Bank-level security</p>
                        </div>
                        
                        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="flex-shrink-0 mx-auto mb-3 w-10 h-10">
                                <UserGroupIcon class="w-full h-full text-emerald-600" />
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Expert</h4>
                            <p class="text-xs text-gray-600">Professional guidance</p>
                        </div>
                        
                        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="flex-shrink-0 mx-auto mb-3 w-10 h-10">
                                <CurrencyDollarIcon class="w-full h-full text-emerald-600" />
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Affordable</h4>
                            <p class="text-xs text-gray-600">Best pricing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div v-if="homepageWidgets.showTestimonials" class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Loved by Thousands</h2>
                    <p class="text-base md:text-lg text-gray-600">See what our users say</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="testimonial in testimonials" :key="testimonial.name" class="bg-white rounded-xl p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <img :src="testimonial.image" :alt="testimonial.name" class="w-12 h-12 rounded-full flex-shrink-0" />
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 text-sm">{{ testimonial.name }}</div>
                                <div class="text-xs text-gray-600">{{ testimonial.role }}</div>
                            </div>
                        </div>
                        <div class="flex mb-3 gap-1">
                            <div v-for="i in testimonial.rating" :key="i" class="w-4 h-4">
                                <StarIcon class="w-full h-full text-yellow-400 fill-current" />
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed">"{{ testimonial.content }}"</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="bg-emerald-600 py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Ready to Start Your Journey?</h2>
                <p class="text-emerald-100 mb-8 text-base md:text-lg">Join thousands who trusted us</p>
                <Link v-if="canRegister" :href="route('register')" class="inline-block px-8 py-3 bg-white text-emerald-600 rounded-lg hover:bg-gray-50 font-semibold transition-colors">
                    Create Free Account
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <Footer />
    </div>
</template>
